<?php
/**
 * inc/rate_limiter.php
 * Per-IP rate limiting + per-IP daily token cap, stored in MySQL.
 *
 * Buckets:
 *   - 'hour'       : max 30 requests / hour / IP
 *   - 'day'        : max 200 requests / day / IP
 *   - 'tokens_day' : max 30,000 OpenAI tokens / day / IP (~15 chatbot turns)
 *
 * On limit hit, helpers throw RuntimeException with the error code as message.
 * Caller catches and turns into HTTP 429 + rc_error_response().
 */

require_once __DIR__ . '/db.php';

const RC_LIMIT_HOUR_MAX   = 30;
const RC_LIMIT_DAY_MAX    = 200;
const RC_LIMIT_TOKENS_MAX = 30000;

if (!function_exists('rc_rate_check')) {
    function rc_rate_check(string $ip): void {
        $pdo        = rc_pdo();
        $hourWindow = date('Y-m-d H:00:00');
        $dayWindow  = date('Y-m-d 00:00:00');

        // Read current counters (NULL-coalesced to 0 if no row yet)
        $sel = $pdo->prepare(
            "SELECT bucket, counter FROM rate_limits
             WHERE ip_address = ? AND
                   ((bucket='hour' AND window_start=?) OR (bucket='day' AND window_start=?))"
        );
        $sel->execute([$ip, $hourWindow, $dayWindow]);
        $counts = ['hour' => 0, 'day' => 0];
        foreach ($sel->fetchAll() as $r) {
            $counts[$r['bucket']] = (int)$r['counter'];
        }

        if ($counts['hour'] >= RC_LIMIT_HOUR_MAX) {
            throw new RuntimeException('RC-RATE-001');
        }
        if ($counts['day'] >= RC_LIMIT_DAY_MAX) {
            throw new RuntimeException('RC-RATE-002');
        }

        // Increment both buckets atomically (UPSERT)
        $ins = $pdo->prepare(
            "INSERT INTO rate_limits (ip_address, bucket, window_start, counter)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE counter = counter + 1"
        );
        $ins->execute([$ip, 'hour', $hourWindow]);
        $ins->execute([$ip, 'day',  $dayWindow]);

        rc_rate_gc();
    }
}

if (!function_exists('rc_token_check')) {
    function rc_token_check(string $ip): void {
        $pdo       = rc_pdo();
        $dayWindow = date('Y-m-d 00:00:00');
        $sel = $pdo->prepare(
            "SELECT counter FROM rate_limits
             WHERE ip_address=? AND bucket='tokens_day' AND window_start=?"
        );
        $sel->execute([$ip, $dayWindow]);
        $cur = (int)($sel->fetchColumn() ?: 0);
        if ($cur >= RC_LIMIT_TOKENS_MAX) {
            throw new RuntimeException('RC-RATE-003');
        }
    }
}

if (!function_exists('rc_token_add')) {
    function rc_token_add(string $ip, int $tokens): void {
        if ($tokens <= 0) return;
        $pdo       = rc_pdo();
        $dayWindow = date('Y-m-d 00:00:00');
        $ins = $pdo->prepare(
            "INSERT INTO rate_limits (ip_address, bucket, window_start, counter)
             VALUES (?, 'tokens_day', ?, ?)
             ON DUPLICATE KEY UPDATE counter = counter + VALUES(counter)"
        );
        $ins->execute([$ip, $dayWindow, $tokens]);
    }
}

if (!function_exists('rc_rate_gc')) {
    function rc_rate_gc(): void {
        // Opportunistic cleanup: 1% of calls drop rows older than 3 days
        if (random_int(1, 100) !== 1) return;
        try {
            rc_pdo()->exec("DELETE FROM rate_limits WHERE window_start < (NOW() - INTERVAL 3 DAY)");
        } catch (Throwable $e) {
            error_log('rc_rate_gc: ' . $e->getMessage());
        }
    }
}

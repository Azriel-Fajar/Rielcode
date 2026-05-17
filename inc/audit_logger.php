<?php
/**
 * inc/audit_logger.php
 * Append-only audit trail. Never throws — logging failure must not break the request.
 *
 * Usage:
 *   rc_audit('ADMIN_LOGIN_OK', $username);
 *   rc_audit('ADMIN_CRUD', $username, ['table'=>'orders','action'=>'delete','id'=>42]);
 *   rc_audit('RATE_LIMIT_HIT', $ip, ['bucket'=>'hour'], 'warn');
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/ip.php';

if (!function_exists('rc_audit')) {
    function rc_audit(
        string $eventCode,
        ?string $actor = null,
        array $meta = [],
        string $severity = 'info',
        ?string $refTable = null,
        ?int $refId = null,
        ?string $message = null
    ): void {
        try {
            $pdo  = rc_pdo();
            $stmt = $pdo->prepare(
                "INSERT INTO audit_log
                 (event_code, severity, actor, ip_address, ref_table, ref_id, message, meta)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                substr($eventCode, 0, 32),
                in_array($severity, ['info','warn','error'], true) ? $severity : 'info',
                $actor !== null ? substr($actor, 0, 120) : null,
                rc_client_ip(),
                $refTable !== null ? substr($refTable, 0, 64) : null,
                $refId,
                $message !== null ? substr($message, 0, 255) : null,
                $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (Throwable $e) {
            error_log('rc_audit failed: ' . $e->getMessage());
        }
    }
}

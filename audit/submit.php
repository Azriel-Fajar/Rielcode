<?php
/**
 * Free Website Audit - form submission (Phase 1).
 * Validates input, drops bots via honeypot, stores lead, redirects to result.
 * Audit itself is manual in Phase 1 (run /site-review, email report).
 */

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/ip.php';
require_once __DIR__ . '/../inc/rate_limiter.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Honeypot - silent drop if bot filled hidden field.
if (!empty($_POST['website'])) {
    header('Location: index.php?error=missing');
    exit;
}

$url   = trim($_POST['url']   ?? '');
$email = trim($_POST['email'] ?? '');

if ($url === '' || $email === '') {
    header('Location: index.php?error=missing');
    exit;
}
if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
    header('Location: index.php?error=url');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?error=email');
    exit;
}
if (strlen($url) > 500 || strlen($email) > 255) {
    header('Location: index.php?error=toolong');
    exit;
}

// Rate limit per IP (30/hour, 200/day) - blocks rapid duplicate submits + abuse.
try {
    rc_rate_check(rc_client_ip());
} catch (RuntimeException $e) {
    header('Location: index.php?error=ratelimit');
    exit;
}

$auditId = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

try {
    $pdo = rc_pdo();
    $stmt = $pdo->prepare(
        'INSERT INTO audit_leads (audit_id, url, email) VALUES (?, ?, ?)'
    );
    $stmt->execute([$auditId, $url, $email]);
} catch (Throwable $e) {
    error_log('[RC-AUDIT-001] submit.php: ' . $e->getMessage());
    header('Location: index.php?error=server');
    exit;
}

// Run the audit inline (pure PHP, no Node) and store the score.
require_once __DIR__ . '/inc/audit-engine.php';
try {
    $audit = rc_run_audit($url);
    if ($audit['ok']) {
        $upd = $pdo->prepare(
            'UPDATE audit_leads
                SET score = ?, checks_json = ?, load_time_ms = ?, status = "complete"
              WHERE audit_id = ?'
        );
        $upd->execute([
            $audit['score'],
            json_encode($audit['checks']),
            $audit['load_time_ms'],
            $auditId,
        ]);

        // Build PDF report and email it to the lead. Non-fatal on failure.
        try {
            require_once __DIR__ . '/inc/pdf-report.php';
            require_once __DIR__ . '/inc/send-report.php';
            $lead = [
                'url'          => $url,
                'email'        => $email,
                'score'        => $audit['score'],
                'load_time_ms' => $audit['load_time_ms'],
                'audit_id'     => $auditId,
            ];
            $pdf = rc_build_audit_pdf($lead, $audit['checks']);
            rc_send_audit_email($lead, $pdf);
        } catch (Throwable $e) {
            error_log('[RC-AUDIT-005] submit.php report: ' . $e->getMessage());
        }
    }
} catch (Throwable $e) {
    // Audit failure is non-fatal: lead is captured, result page falls back gracefully.
    error_log('[RC-AUDIT-003] submit.php audit: ' . $e->getMessage());
}

header('Location: result.php?id=' . urlencode($auditId));
exit;

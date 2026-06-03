<?php
/**
 * Free Website Audit - result page (Phase 2).
 * Shows real score, color band, top failing issues, and full check table.
 * Falls back to a "being prepared" state if the audit could not run.
 */
session_start();
$base = '../';

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/inc/audit-engine.php';

$auditId = trim($_GET['id'] ?? '');
$lead = null;

if ($auditId !== '') {
    try {
        $pdo = rc_pdo();
        $stmt = $pdo->prepare(
            'SELECT url, email, score, checks_json, load_time_ms, status
               FROM audit_leads WHERE audit_id = ? LIMIT 1'
        );
        $stmt->execute([$auditId]);
        $lead = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[RC-AUDIT-002] result.php: ' . $e->getMessage());
    }
}

if (!$lead) {
    http_response_code(404);
    $meta_title = "Audit Not Found | Rielcode";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $meta_title ?></title>
        <link rel="stylesheet" href="<?= $base ?>CSS/style.css">
        <link rel="stylesheet" href="<?= $base ?>CSS/redesign.css">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>IMG/Rielcode Logo Square Transparent Icon.png">
        <link rel="stylesheet" href="<?= $base ?>CSS/tailwind.css">
    </head>
    <body class="w-full m-0 p-0 rc-redesign">
        <?php include '../navbar.php'; ?>
        <section style="min-height:70vh;display:flex;align-items:center;padding:120px 0 80px">
            <div class="rc-container" style="text-align:center;max-width:560px;margin:0 auto">
                <span class="rc-eyebrow">not found</span>
                <h1 class="rc-h2">We couldn't find that audit</h1>
                <p class="rc-body-lg" style="margin:14px auto 24px;max-width:440px">
                    The audit link may be wrong or expired. Run a new free audit in seconds.
                </p>
                <a class="rc-btn" href="<?= $base ?>audit/">Run a free audit</a>
            </div>
        </section>
    </body>
    </html>
    <?php
    exit;
}

$hasScore = $lead['score'] !== null && $lead['checks_json'] !== null;
$score    = (int) $lead['score'];
$checks   = $hasScore ? json_decode($lead['checks_json'], true) : [];

// Band: 0-49 red, 50-74 amber, 75+ green.
if ($score >= 75)      { $band = '#1e8e5a'; $bandBg = 'rgba(30,142,90,.12)';  $bandLabel = 'Strong'; }
elseif ($score >= 50)  { $band = '#c08a1e'; $bandBg = 'rgba(192,138,30,.14)'; $bandLabel = 'Needs work'; }
else                   { $band = '#c0392b'; $bandBg = 'rgba(192,57,43,.12)';  $bandLabel = 'Critical'; }

// Top failing issues (max 3), in weight order.
$fails = [];
foreach ($checks as $c) {
    if (!$c['pass']) $fails[] = $c['issue'] ?? $c['label'];
}
$topFails = array_slice($fails, 0, 3);

$meta_title       = "Your Website Audit Result | Rielcode";
$meta_description = "Your free website audit score and top issues.";
$meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
$meta_url         = "https://rielcode.com/audit/";
$canonical        = "https://rielcode.com/audit/";

$waMsg = rawurlencode("Hello, here is my audit ID: {$auditId}. I'd like the full report and to discuss my site.");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../inc/seo.php'; ?>

    <link rel="stylesheet" href="<?= $base ?>CSS/style.css">
    <link rel="stylesheet" href="<?= $base ?>CSS/redesign.css">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>CSS/tailwind.css">

    <style>
        .rc-res { min-height: 80vh; padding: 120px 0 80px; }
        .rc-res__card { max-width: 700px; margin: 0 auto; }
        .rc-res__score {
            width: 150px; height: 150px; border-radius: 50%; margin: 8px auto 18px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 6px solid <?= $band ?>; background: <?= $bandBg ?>;
        }
        .rc-res__num { font-size: 2.8rem; font-weight: 800; line-height: 1; color: <?= $band ?>; }
        .rc-res__out { font-size: .8rem; opacity: .6; }
        .rc-res__bandlabel {
            display: inline-block; font-weight: 700; font-size: .82rem; letter-spacing: .04em;
            color: <?= $band ?>; background: <?= $bandBg ?>; padding: 6px 14px; border-radius: 999px;
        }
        .rc-res__issues { display: grid; gap: 12px; margin: 30px 0; text-align: left; }
        .rc-res__issue {
            border: 1px solid rgba(192,57,43,.25); border-left: 4px solid #c0392b;
            border-radius: 12px; padding: 14px 18px; background: rgba(192,57,43,.04);
            display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: .95rem;
        }
        .rc-res__issue i { color: #c0392b; font-size: 1.2rem; }
        .rc-res__details { margin: 26px 0; text-align: left; }
        .rc-res__details summary { cursor: pointer; font-weight: 600; padding: 10px 0; }
        .rc-res__table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .rc-res__table td { padding: 10px 12px; border-bottom: 1px solid rgba(0,0,0,.08); font-size: .9rem; }
        .rc-res__table td:last-child { text-align: right; white-space: nowrap; }
        .rc-pass { color: #1e8e5a; font-weight: 700; }
        .rc-fail { color: #c0392b; font-weight: 700; }
        .rc-res__id { font-family: monospace; font-size: .82rem; opacity: .7; word-break: break-all; }
        .rc-res__cta { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-top: 8px; }
    </style>
</head>

<body class="w-full m-0 p-0 rc-redesign">

    <?php include '../navbar.php'; ?>

    <section class="rc-res">
        <div class="rc-container rc-res__card" style="text-align:center">

        <?php if ($hasScore): ?>

            <span class="rc-eyebrow">audit complete</span>
            <h1 class="rc-h2">Your website scored</h1>

            <div class="rc-res__score">
                <span class="rc-res__num"><?= $score ?></span>
                <span class="rc-res__out">out of 100</span>
            </div>
            <span class="rc-res__bandlabel"><?= $bandLabel ?></span>

            <p class="rc-body-lg" style="margin:18px auto 0;max-width:520px">
                We audited <strong><?= htmlspecialchars($lead['url']) ?></strong>
                in <?= (int) $lead['load_time_ms'] ?>ms.
            </p>

            <?php if ($topFails): ?>
                <h3 class="rc-h3" style="margin:34px 0 0;text-align:left">Top issues found</h3>
                <div class="rc-res__issues">
                    <?php foreach ($topFails as $f): ?>
                        <div class="rc-res__issue"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($f) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="rc-body-lg" style="margin-top:24px;color:#1e8e5a;font-weight:600">
                    No major issues found. Your site covers the fundamentals well.
                </p>
            <?php endif; ?>

            <details class="rc-res__details">
                <summary>See full breakdown (<?= count($checks) ?> checks)</summary>
                <table class="rc-res__table">
                    <?php foreach ($checks as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['label']) ?></td>
                            <td><?= $c['pass']
                                ? '<span class="rc-pass">Pass</span>'
                                : '<span class="rc-fail">Fail</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </details>

            <p class="rc-body" style="margin-bottom:18px">
                <?= $topFails
                    ? 'Want these issues fixed? Rielcode can handle it. Send your audit ID to get started.'
                    : 'Want to push your site even further? Rielcode can help you stay ahead. Send your audit ID to start.' ?>
            </p>

        <?php else: ?>

            <span class="rc-eyebrow">audit received</span>
            <h1 class="rc-h2">We couldn't reach your site automatically</h1>
            <p class="rc-body-lg" style="margin:14px auto 0;max-width:520px">
                We received your request for <strong><?= htmlspecialchars($lead['url']) ?></strong>
                but couldn't load it for an instant score. We'll review it by hand and email
                <strong><?= htmlspecialchars($lead['email']) ?></strong> shortly. Double-check the URL is correct and public.
            </p>

        <?php endif; ?>

            <p style="margin:14px 0"><span class="rc-res__id">Audit ID: <?= htmlspecialchars($auditId) ?></span></p>

            <div class="rc-res__cta">
                <a class="rc-btn" href="https://wa.me/6281295536876?text=<?= $waMsg ?>" target="_blank" rel="noopener">
                    Get these fixed on WhatsApp
                </a>
                <a class="rc-btn rc-btn--ghost" href="<?= $base ?>">Back to Rielcode</a>
            </div>

        </div>
    </section>

</body>
</html>

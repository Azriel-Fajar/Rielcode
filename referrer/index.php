<?php
session_start();
require_once '../connection.php';

$code = trim($_GET['code'] ?? '');

$referrer = null;
if ($code !== '') {
    $stmt = $conn->prepare("SELECT id, name, code, commission_rate, status FROM referrers WHERE code = ? AND status = 'active'");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $referrer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$referrer) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Not Found | Rielcode</title>
        <link rel="stylesheet" href="../CSS/redesign.css">
        <meta name="robots" content="noindex, nofollow">
    </head>
    <body class="rc-redesign" style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
        <div style="text-align:center;padding:40px;">
            <h1 style="color:#f87171;font-size:2rem;margin-bottom:12px;">Invalid Code</h1>
            <p style="color:rgba(255,255,255,0.5);">This referral code is not valid or has been deactivated.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Fetch summary stats
$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_referrals,
        COALESCE(SUM(CASE WHEN status='paid' THEN commission_amount ELSE 0 END), 0) AS total_earned,
        COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END), 0) AS total_pending
     FROM referral_commissions
     WHERE referrer_id = ?"
);
$stmt->bind_param("i", $referrer['id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch commission rows
$stmt = $conn->prepare(
    "SELECT rc.commission_amount, rc.order_amount, rc.status, rc.created_at,
            o.package AS package_name, o.invoice_number
     FROM referral_commissions rc
     JOIN orders o ON o.id = rc.order_id
     WHERE rc.referrer_id = ?
     ORDER BY rc.created_at DESC"
);
$stmt->bind_param("i", $referrer['id']);
$stmt->execute();
$commissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrer Dashboard | Rielcode</title>
    <link rel="stylesheet" href="../CSS/redesign.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="robots" content="noindex, nofollow">
    <style>
        .ref-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 24px; }
        .ref-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .ref-stat { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 18px 20px; }
        .ref-stat__label { font-size: 0.72rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .ref-stat__value { font-size: 1.4rem; font-weight: 700; color: #fff; }
        .ref-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .ref-table th { text-align: left; padding: 10px 14px; color: rgba(255,255,255,0.4); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .ref-table td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.05); color: rgba(255,255,255,0.8); }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
        .badge--pending   { background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.35); color: #fbbf24; }
        .badge--paid      { background: rgba(34,197,94,0.12);  border: 1px solid rgba(34,197,94,0.35);  color: #4ade80; }
        .badge--cancelled { background: rgba(239,68,68,0.10);  border: 1px solid rgba(239,68,68,0.30);  color: #f87171; }
    </style>
</head>
<body class="rc-redesign">
    <div style="max-width:860px;margin:0 auto;padding:40px 20px;">

        <div style="margin-bottom:32px;display:flex;align-items:center;gap:16px;">
            <img src="../IMG/Rielcode Logo Transparent.png" alt="Rielcode" style="height:36px;">
        </div>

        <div class="ref-card" style="margin-bottom:28px;">
            <p style="font-size:0.72rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:4px;">Referrer Dashboard</p>
            <h1 style="font-size:1.6rem;font-weight:700;color:#fff;margin-bottom:4px;"><?= htmlspecialchars($referrer['name']) ?></h1>
            <p style="color:rgba(255,255,255,0.4);font-size:0.85rem;">
                Code: <span style="font-family:monospace;font-weight:600;color:#60a5fa;"><?= htmlspecialchars($referrer['code']) ?></span>
                &nbsp;&middot;&nbsp;
                Commission rate: <span style="color:#4ade80;"><?= number_format((float)$referrer['commission_rate'], 2) ?>%</span>
            </p>
        </div>

        <div class="ref-stat-grid">
            <div class="ref-stat">
                <div class="ref-stat__label">Total Referrals</div>
                <div class="ref-stat__value"><?= (int)$stats['total_referrals'] ?></div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat__label">Commission Earned</div>
                <div class="ref-stat__value" style="color:#4ade80;">Rp<?= number_format((float)$stats['total_earned'], 0, ',', '.') ?></div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat__label">Pending Payout</div>
                <div class="ref-stat__value" style="color:#fbbf24;">Rp<?= number_format((float)$stats['total_pending'], 0, ',', '.') ?></div>
            </div>
        </div>

        <h2 style="font-size:1rem;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:16px;">Commission History</h2>

        <?php if (empty($commissions)): ?>
            <p style="color:rgba(255,255,255,0.3);font-size:0.85rem;">No commissions yet. Share your referral code to get started.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="ref-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Package</th>
                        <th>Order Amount</th>
                        <th>Commission</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commissions as $com): ?>
                    <tr>
                        <td style="color:rgba(255,255,255,0.5);font-size:0.78rem;"><?= htmlspecialchars(substr($com['created_at'], 0, 10)) ?></td>
                        <td><?= htmlspecialchars($com['package_name']) ?></td>
                        <td>Rp<?= number_format((float)$com['order_amount'], 0, ',', '.') ?></td>
                        <td style="font-weight:600;color:#4ade80;">Rp<?= number_format((float)$com['commission_amount'], 0, ',', '.') ?></td>
                        <td><span class="badge badge--<?= htmlspecialchars($com['status']) ?>"><?= ucfirst($com['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <p style="margin-top:40px;font-size:0.72rem;color:rgba(255,255,255,0.2);text-align:center;">
            This dashboard is read-only. Contact Rielcode for any questions about your commissions.
        </p>
    </div>
</body>
</html>

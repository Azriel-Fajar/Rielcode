<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../inc/invoice_number.php';

$invNum = trim($_GET['n'] ?? '');
if (!$invNum) { http_response_code(404); die('Invoice not found.'); }

$stmt = $conn->prepare("
    SELECT p.*, o.order_name, o.email, o.phone_number, o.package, o.description, o.final_price, o.package_price, o.addons_total
    FROM order_payments p
    JOIN orders o ON o.id = p.order_id
    WHERE p.invoice_number = ?
");
$stmt->bind_param("s", $invNum);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$inv) { http_response_code(404); die('Invoice not found.'); }

// Auto-overdue
if ($inv['status'] === 'sent' && $inv['due_date'] < date('Y-m-d')) {
    $conn->query("UPDATE order_payments SET status='overdue' WHERE id=" . (int)$inv['id']);
    $inv['status'] = 'overdue';
}

$isPaid    = $inv['status'] === 'paid';
$isOverdue = $inv['status'] === 'overdue';
$stageLabel    = $inv['stage'] === 'deposit' ? 'Deposit (20%)' : 'Final (80%)';
$stageTagline  = $inv['stage'] === 'deposit'
    ? 'This is the 20% deposit. Project work begins once this payment is received.'
    : 'This is the final 80%. Admin credentials and project handover follow once cleared.';

$amountFmt = fmt_amount((float)$inv['amount'], $inv['currency']);
$pdfUrl = APP_URL . '/pdf.php?n=' . urlencode($invNum);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($inv['invoice_number']); ?> | Rielcode</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/CSS/pay.css" rel="stylesheet">
</head>
<body style="background:#0d0f11;">
<div class="invoice-public">
    <div class="invoice-header">
        <div>
            <img src="<?php echo APP_URL; ?>/IMG/logo.png" alt="Rielcode" style="height:36px;display:block;margin-bottom:4px;">
            <div style="color:rgba(255,255,255,0.4);font-size:13px;margin-top:4px;">rielcode.com</div>
        </div>
        <div style="text-align:right;">
            <code style="color:rgba(255,255,255,0.5);font-size:13px;"><?php echo htmlspecialchars($inv['invoice_number']); ?></code>
            <div>
                <?php if ($isPaid): ?>
                    <span class="badge badge-paid px-2 py-1 rounded mt-1">Paid</span>
                <?php elseif ($isOverdue): ?>
                    <span class="badge badge-overdue px-2 py-1 rounded mt-1">Overdue</span>
                <?php else: ?>
                    <span class="badge badge-sent px-2 py-1 rounded mt-1">Unpaid</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="stage-banner"><strong><?php echo $stageLabel; ?></strong> &mdash; <?php echo $stageTagline; ?></div>

    <div class="pay-card mb-3">
        <div class="row">
            <div class="col-6"><div style="color:rgba(255,255,255,0.4);font-size:12px;">Bill To</div><strong><?php echo htmlspecialchars($inv['order_name']); ?></strong><div style="color:rgba(255,255,255,0.4);font-size:13px;"><?php echo htmlspecialchars($inv['email']); ?></div></div>
            <div class="col-6" style="text-align:right;"><div style="color:rgba(255,255,255,0.4);font-size:12px;">Due Date</div><?php echo date('d M Y', strtotime($inv['due_date'])); ?></div>
        </div>
    </div>

    <div class="pay-card mb-3">
        <div class="section-title">Project</div>
        <strong><?php echo htmlspecialchars($inv['package']); ?></strong>
        <?php if ($inv['description']): ?>
            <div style="color:rgba(255,255,255,0.5);font-size:13px;margin-top:6px;"><?php echo nl2br(htmlspecialchars($inv['description'])); ?></div>
        <?php endif; ?>
        <div style="border-top:1px solid var(--border);margin-top:14px;padding-top:14px;color:var(--muted);font-size:13px;">
            Total project value: <strong style="color:var(--text);"><?php echo fmt_amount((float)$inv['final_price'], $inv['currency']); ?></strong>
        </div>
    </div>

    <div class="invoice-total-box">
        <div style="color:rgba(255,255,255,0.4);font-size:12px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Amount Due (<?php echo $stageLabel; ?>)</div>
        <div class="invoice-total-amount"><?php echo $amountFmt; ?></div>

        <?php if ($isPaid): ?>
            <div style="color:#22c55e;margin-top:16px;font-weight:600;">Payment received. Thank you!</div>
        <?php else: ?>
            <?php if ($inv['currency'] === 'IDR'): ?>
                <div class="qris-box">
                    <div class="label">Scan QRIS</div>
                    <img src="<?php echo APP_URL; ?>/IMG/QRIS.jpeg" alt="QRIS"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='none';">
                    <div style="color:rgba(255,255,255,0.4);font-size:11px;margin-top:8px;">GoPay, OVO, DANA, BCA, and all banking apps</div>
                    <div class="mt-2"><a href="<?php echo APP_URL; ?>/IMG/QRIS.jpeg" download="QRIS-Rielcode.jpeg" class="btn btn-outline-secondary btn-sm">Download QRIS</a></div>
                </div>
                <div class="payment-divider">or pay via bank transfer</div>
                <div class="bank-details-box">
                    <div class="label">Bank Transfer (Indonesia)</div>
                    <div class="value mt-2"><?php echo htmlspecialchars(BANK_NAME); ?></div>
                    <div style="font-size:1.3rem;font-weight:700;letter-spacing:2px;margin:6px 0;"><?php echo htmlspecialchars(BANK_ACCOUNT_NUMBER); ?></div>
                    <div style="color:rgba(255,255,255,0.6);font-size:14px;"><?php echo htmlspecialchars(BANK_ACCOUNT_NAME); ?></div>
                </div>
            <?php else: /* USD — PayPal */ ?>
                <div class="bank-details-box" style="text-align:center;">
                    <div class="label" style="margin-bottom:12px;">Pay via PayPal</div>
                    <a href="<?php echo htmlspecialchars(PAYPAL_ME . '/' . number_format((float)$inv['amount'], 2, '.', '')); ?>"
                       target="_blank" rel="noopener"
                       style="display:inline-block;background:#0070ba;color:#fff;font-weight:600;padding:12px 28px;border-radius:6px;text-decoration:none;font-size:15px;letter-spacing:.3px;">
                        Pay with PayPal
                    </a>
                    <div style="color:rgba(255,255,255,0.4);font-size:12px;margin-top:12px;">Amount pre-filled. After payment, send confirmation via WhatsApp.</div>
                </div>
            <?php endif; ?>

            <div style="color:rgba(255,255,255,0.5);font-size:12px;margin-top:14px;">
                After payment, please send proof via WhatsApp to confirm. Once verified, this invoice will be marked Paid.
            </div>
        <?php endif; ?>

        <div class="mt-4 no-print">
            <a href="<?php echo htmlspecialchars($pdfUrl); ?>" class="btn btn-outline-secondary btn-sm">Download PDF</a>
        </div>
    </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/inc/invoice_number.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$invNum = trim($_GET['n'] ?? '');
if (!$invNum) { http_response_code(404); die('Not found.'); }

$stmt = $conn->prepare("
    SELECT p.*, o.order_name, o.email, o.package, o.description, o.final_price
    FROM order_payments p
    JOIN orders o ON o.id = p.order_id
    WHERE p.invoice_number = ?
");
$stmt->bind_param("s", $invNum);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$inv) { http_response_code(404); die('Not found.'); }

$stageLabel   = $inv['stage'] === 'deposit' ? 'Deposit (20%)' : 'Final (80%)';
$stageNote    = $inv['stage'] === 'deposit'
    ? 'This is the 20% deposit. Project work begins once this payment is received.'
    : 'This is the final 80%. Admin credentials and project handover follow once cleared.';
$amountFmt    = fmt_amount((float)$inv['amount'], $inv['currency']);
$projectTotal = fmt_amount((float)$inv['final_price'], $inv['currency']);

$logoPath = __DIR__ . '/IMG/logo-dark.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; margin: 40px; }
  .header { width: 100%; margin-bottom: 30px; }
  .logo { font-size: 22px; font-weight: bold; color: #3a7bff; }
  .right { text-align: right; }
  .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 6px; }
  table.meta { width: 100%; margin-bottom: 20px; }
  table.meta td { padding: 4px 0; vertical-align: top; }
  .stage-banner { background: #eef3ff; border: 1px solid #c7d8ff; color: #1c3d8f; padding: 10px 14px; border-radius: 6px; margin-bottom: 20px; }
  .project-box { background: #f9f9f9; border: 1px solid #eee; border-radius: 6px; padding: 14px; margin-bottom: 20px; }
  .total-box { background: #f4f8ff; border: 1px solid #c7d8ff; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 24px; }
  .total-amount { font-size: 22px; font-weight: bold; color: #1c3d8f; }
  .bank-box { background: #fafafa; border: 1px solid #eee; border-radius: 6px; padding: 16px; }
  .bank-row { padding: 6px 0; border-bottom: 1px solid #eee; }
  .bank-row:last-child { border-bottom: none; }
  .bank-k { color: #777; font-size: 11px; }
  .bank-v { font-weight: bold; }
  .footer { margin-top: 40px; font-size: 10px; color: #aaa; text-align: center; }
</style>
</head>
<body>
<table class="header"><tr>
  <td><?php if ($logoBase64): ?><img src="<?php echo $logoBase64; ?>" alt="Rielcode" style="height:36px;display:block;margin-bottom:4px;"><?php else: ?><div class="logo">Rielcode</div><?php endif; ?><div style="color:#888;font-size:11px;">rielcode.com</div></td>
  <td class="right">
    <div style="color:#888;font-size:11px;">Invoice</div>
    <div style="font-weight:bold;font-size:13px;"><?php echo htmlspecialchars($inv['invoice_number']); ?></div>
    <div style="color:#888;font-size:11px;">Due: <?php echo date('d M Y', strtotime($inv['due_date'])); ?></div>
    <div style="color:#888;font-size:11px;">Status: <?php echo ucfirst($inv['status']); ?></div>
  </td>
</tr></table>

<div class="stage-banner"><strong><?php echo $stageLabel; ?></strong> &mdash; <?php echo $stageNote; ?></div>

<table class="meta"><tr>
  <td>
    <div class="section-title">Bill To</div>
    <strong><?php echo htmlspecialchars($inv['order_name']); ?></strong><br>
    <span style="color:#555;"><?php echo htmlspecialchars($inv['email']); ?></span>
  </td>
  <td class="right">
    <div class="section-title">Currency</div>
    <strong><?php echo $inv['currency']; ?></strong>
  </td>
</tr></table>

<div class="project-box">
  <div class="section-title">Project</div>
  <strong><?php echo htmlspecialchars($inv['package']); ?></strong>
  <?php if ($inv['description']): ?>
    <div style="color:#555;font-size:11px;margin-top:6px;"><?php echo nl2br(htmlspecialchars($inv['description'])); ?></div>
  <?php endif; ?>
  <div style="border-top:1px solid #eee;margin-top:10px;padding-top:10px;color:#777;font-size:11px;">
    Total project value: <strong style="color:#111;"><?php echo $projectTotal; ?></strong>
  </div>
</div>

<div class="total-box">
  <div class="section-title">Amount Due (<?php echo $stageLabel; ?>)</div>
  <div class="total-amount"><?php echo $amountFmt; ?></div>
</div>

<?php if ($inv['currency'] === 'IDR'): ?>
<div class="bank-box">
  <div class="section-title">Payment &mdash; QRIS or Bank Transfer (Indonesia)</div>
  <div style="color:#555;font-size:10px;margin-bottom:10px;">Scan QRIS with GoPay, OVO, DANA, BCA, or any banking app. Or transfer to:</div>
  <div class="bank-row"><span class="bank-k">Bank Name</span><br><span class="bank-v"><?php echo htmlspecialchars(BANK_NAME); ?></span></div>
  <div class="bank-row"><span class="bank-k">Account Number</span><br><span class="bank-v" style="font-size:14px;letter-spacing:1px;"><?php echo htmlspecialchars(BANK_ACCOUNT_NUMBER); ?></span></div>
  <div class="bank-row"><span class="bank-k">Account Name</span><br><span class="bank-v"><?php echo htmlspecialchars(BANK_ACCOUNT_NAME); ?></span></div>
</div>
<?php else: /* USD */ ?>
<div class="bank-box">
  <div class="section-title">Payment &mdash; International Wire Transfer (SWIFT)</div>
  <div class="bank-row"><span class="bank-k">Beneficiary Name</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_BENEFICIARY_NAME); ?></span></div>
  <div class="bank-row"><span class="bank-k">Beneficiary Address</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_BENEFICIARY_ADDRESS); ?></span></div>
  <div class="bank-row"><span class="bank-k">Bank Name</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_BANK_NAME); ?></span></div>
  <div class="bank-row"><span class="bank-k">Bank Address</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_BANK_ADDRESS); ?></span></div>
  <div class="bank-row"><span class="bank-k">Account Number / IBAN</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_ACCOUNT_NUMBER); ?></span></div>
  <div class="bank-row"><span class="bank-k">SWIFT / BIC</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_SWIFT_CODE); ?></span></div>
  <?php if (INTL_INTERMEDIARY_BANK): ?>
    <div class="bank-row"><span class="bank-k">Intermediary Bank</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_INTERMEDIARY_BANK); ?></span></div>
    <div class="bank-row"><span class="bank-k">Intermediary SWIFT</span><br><span class="bank-v"><?php echo htmlspecialchars(INTL_INTERMEDIARY_SWIFT); ?></span></div>
  <?php endif; ?>
  <div style="color:#777;font-size:10px;margin-top:10px;">Wire fees borne by sender. Reference the invoice number (<?php echo htmlspecialchars($inv['invoice_number']); ?>) in the wire message field.</div>
</div>
<?php endif; ?>

<div style="color:#777;font-size:10px;margin-top:14px;">After payment, send proof via WhatsApp to confirm. The invoice will then be marked Paid.</div>

<div class="footer">Generated by Rielcode &mdash; rielcode.com</div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', false);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($inv['invoice_number'] . '.pdf', ['Attachment' => true]);

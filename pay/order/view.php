<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/invoice_number.php';
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$currency = $order['invoice_currency'] ?: 'IDR';
$total    = (float)$order['final_price'];

$payRes = $conn->query("SELECT * FROM order_payments WHERE order_id={$order_id}");
$payments = ['deposit' => null, 'final' => null];
while ($p = $payRes->fetch_assoc()) $payments[$p['stage']] = $p;

$depPaid = $payments['deposit'] && $payments['deposit']['status'] === 'paid';

function stage_card(array $opts)
{
    $stage      = $opts['stage'];
    $stageLabel = $stage === 'deposit' ? 'Deposit (20%)' : 'Final (80%)';
    $stageColor = $stage === 'deposit' ? 'var(--primary)' : 'var(--success)';
    $amount     = $opts['amount'];
    $currency   = $opts['currency'];
    $row        = $opts['row'];
    $locked     = $opts['locked'] ?? false;
    $orderId    = $opts['order_id'];
    $clientPhone = $opts['client_phone'] ?? '';
    $waMessage  = $opts['wa_message'] ?? '';
    $orderName  = $opts['order_name'] ?? '';

    $amountFmt = fmt_amount($amount, $currency);
    $publicLink = '';
    $qrDataUri  = '';
    if ($row) {
        $publicLink = APP_URL . '/i/view.php?n=' . urlencode($row['invoice_number']);
        $qrDataUri  = (new QRCode(new QROptions(['outputType' => 'png', 'scale' => 5, 'imageBase64' => true])))->render($publicLink);
    }
?>
    <div class="stage-card <?php echo $locked ? 'locked' : ''; ?>">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5><?php echo $stageLabel; ?></h5>
                <?php if ($row): ?>
                    <code style="color:var(--muted);font-size:12px;"><?php echo htmlspecialchars($row['invoice_number']); ?></code>
                <?php endif; ?>
            </div>
            <?php if ($locked): ?>
                <span class="badge badge-locked px-2 py-1 rounded">Locked</span>
            <?php elseif ($row): ?>
                <span class="badge badge-<?php echo $row['status']; ?> px-2 py-1 rounded"><?php echo ucfirst($row['status']); ?></span>
            <?php else: ?>
                <span class="badge badge-not_sent px-2 py-1 rounded">Not Sent</span>
            <?php endif; ?>
        </div>
        <div class="stage-amount" style="color:<?php echo $stageColor; ?>"><?php echo $amountFmt; ?></div>

        <?php if ($locked): ?>
            <p class="stage-meta">Mark deposit as paid first. Final unlock once deposit cleared.</p>
        <?php elseif (!$row): ?>
            <p class="stage-meta">Generate a payment link to share with the client. <?php echo $stage === 'deposit' ? '<strong>Send after scope locked, before work begins.</strong>' : '<strong>Send after staging URL is live, before handover.</strong>'; ?></p>
            <form method="POST" action="<?php echo APP_URL; ?>/order/stage-action.php">
                <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                <input type="hidden" name="stage" value="<?php echo $stage; ?>">
                <input type="hidden" name="action" value="generate">
                <button type="submit" class="btn btn-primary w-100">Generate <?php echo $stageLabel; ?> Link</button>
            </form>
        <?php else: ?>
            <div class="stage-meta mb-2">Due: <?php echo date('d M Y', strtotime($row['due_date'])); ?></div>
            <div class="link-box mb-2"><?php echo htmlspecialchars($publicLink); ?></div>
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($publicLink); ?>');this.textContent='Copied!';">Copy Link</button>
                <a href="<?php echo htmlspecialchars($publicLink); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                <?php if ($clientPhone): ?>
                    <a href="https://wa.me/<?php echo urlencode($clientPhone); ?>?text=<?php echo urlencode($waMessage); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Send via WhatsApp</a>
                <?php endif; ?>
            </div>
            <div class="qr-wrap mb-3">
                <img src="<?php echo $qrDataUri; ?>" alt="QR" style="background:#fff;padding:8px;border-radius:6px;">
                <div><a href="<?php echo $qrDataUri; ?>" download="<?php echo $row['invoice_number']; ?>-qr.png" class="btn btn-sm btn-outline-secondary mt-2">Download QR</a></div>
            </div>

            <?php if ($row['status'] === 'draft'): ?>
                <form method="POST" action="<?php echo APP_URL; ?>/order/stage-action.php">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    <input type="hidden" name="stage" value="<?php echo $stage; ?>">
                    <input type="hidden" name="action" value="mark_sent">
                    <button type="submit" class="btn btn-primary w-100">Mark as Sent</button>
                </form>
            <?php elseif (in_array($row['status'], ['sent', 'overdue'])): ?>
                <form method="POST" action="<?php echo APP_URL; ?>/order/stage-action.php" class="rc-confirm-form">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    <input type="hidden" name="stage" value="<?php echo $stage; ?>">
                    <input type="hidden" name="action" value="mark_paid">
                    <button type="button" class="btn btn-success w-100 rc-confirm-btn"
                        data-title="Confirm Payment Received"
                        data-body="Mark <?php echo $stageLabel; ?> as paid for <?php echo htmlspecialchars($orderName, ENT_QUOTES); ?>? This cannot be undone."
                        data-confirm="Mark as Paid">Mark as Paid</button>
                </form>
            <?php else: ?>
                <div class="alert alert-success py-2 mb-0" style="background:rgba(34,197,94,0.10);border-color:rgba(34,197,94,0.3);color:var(--success);">Paid <?php echo date('d M Y', strtotime($row['paid_at'])); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php
}

$pageTitle = 'Order #' . $order_id . ' | Rielcode Pay';
require_once __DIR__ . '/../inc/header.php';

$depAmount = round($total * 0.20, 2);
$finAmount = round($total * 0.80, 2);

$depWaMsg = $payments['deposit']
    ? "Hi " . $order['order_name'] . ", here's the deposit invoice (20%) for your " . $order['package'] . " project: " . APP_URL . "/i/view.php?n=" . $payments['deposit']['invoice_number'] . "\n\nWork starts once the deposit is received. Thank you!"
    : '';
$finWaMsg = $payments['final']
    ? "Hi " . $order['order_name'] . ", staging is ready! Final invoice (80%) for your " . $order['package'] . ": " . APP_URL . "/i/view.php?n=" . $payments['final']['invoice_number'] . "\n\nOnce settled, I'll hand over credentials and push everything live."
    : '';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?php echo APP_URL; ?>/dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    <h4 class="mb-0">Order #<?php echo $order_id; ?></h4>
    <span class="badge badge-<?php echo strtolower(str_replace(' ', '_', $order['status'])); ?> px-2 py-1 rounded"><?php echo htmlspecialchars($order['status']); ?></span>
</div>

<?php if (($_GET['msg'] ?? '') === 'ok'): ?>
    <div class="alert alert-info">Updated.</div>
<?php endif; ?>

<div class="pay-card mb-4">
    <div class="section-title">Client &amp; Project</div>
    <div class="row">
        <div class="col-md-3">
            <div style="color:var(--muted);font-size:12px;">Client</div><strong><?php echo htmlspecialchars($order['order_name']); ?></strong>
            <div style="color:var(--muted);font-size:13px;"><?php echo htmlspecialchars($order['email']); ?></div>
        </div>
        <div class="col-md-3">
            <div style="color:var(--muted);font-size:12px;">Phone</div><?php echo htmlspecialchars($order['phone_number']); ?>
        </div>
        <div class="col-md-3">
            <div style="color:var(--muted);font-size:12px;">Package</div><?php echo htmlspecialchars($order['package']); ?>
        </div>
        <div class="col-md-3">
            <div style="color:var(--muted);font-size:12px;">Total</div><strong><?php echo fmt_amount($total, $currency); ?></strong>
        </div>
    </div>
    <div class="mt-3">
        <div style="color:var(--muted);font-size:12px;">Description</div>
        <?php echo nl2br(htmlspecialchars($order['description'])); ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <?php stage_card([
            'stage' => 'deposit',
            'amount' => $depAmount,
            'currency' => $currency,
            'row' => $payments['deposit'],
            'order_id' => $order_id,
            'order_name' => $order['order_name'],
            'client_phone' => preg_replace('/[^0-9]/', '', $order['phone_number']),
            'wa_message' => $depWaMsg,
        ]); ?>
    </div>
    <div class="col-md-6">
        <?php stage_card([
            'stage' => 'final',
            'amount' => $finAmount,
            'currency' => $currency,
            'row' => $payments['final'],
            'order_id' => $order_id,
            'locked' => !$depPaid,
            'order_name' => $order['order_name'],
            'client_phone' => preg_replace('/[^0-9]/', '', $order['phone_number']),
            'wa_message' => $finWaMsg,
        ]); ?>
    </div>
</div>

<div class="pay-card">
    <div class="section-title">Timing Reminder</div>
    <ul style="color:var(--muted);font-size:14px;margin:0;padding-left:18px;">
        <li><strong style="color:var(--text);">Deposit link</strong> — send right after scope is locked in <code>spec.md</code>. Before any design/code work begins.</li>
        <li><strong style="color:var(--text);">Final link</strong> — send when staging URL is live and client has previewed it. Before handing over admin credentials, source files, or pushing to client domain.</li>
        <li>Never deliver creds until the final stage is marked Paid.</li>
    </ul>
</div>

<div class="rc-modal-overlay" id="rcModal">
    <div class="rc-modal">
        <div class="rc-modal-icon">&#10003;</div>
        <div class="rc-modal-title" id="rcModalTitle"></div>
        <div class="rc-modal-body" id="rcModalBody"></div>
        <div class="rc-modal-actions">
            <button class="btn btn-outline-secondary" id="rcModalCancel">Cancel</button>
            <button class="btn btn-success" id="rcModalConfirm"></button>
        </div>
    </div>
</div>
<script>
(function() {
    var overlay = document.getElementById('rcModal');
    var pendingForm = null;

    document.querySelectorAll('.rc-confirm-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            pendingForm = btn.closest('.rc-confirm-form');
            document.getElementById('rcModalTitle').textContent = btn.dataset.title;
            document.getElementById('rcModalBody').textContent = btn.dataset.body;
            document.getElementById('rcModalConfirm').textContent = btn.dataset.confirm;
            overlay.classList.add('active');
        });
    });

    document.getElementById('rcModalCancel').addEventListener('click', function() {
        overlay.classList.remove('active');
        pendingForm = null;
    });

    document.getElementById('rcModalConfirm').addEventListener('click', function() {
        if (pendingForm) pendingForm.submit();
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) { overlay.classList.remove('active'); pendingForm = null; }
    });
})();
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
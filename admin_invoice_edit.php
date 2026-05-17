<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$configPath = '/home/rier5192/config.php';
$config = file_exists($configPath) ? require $configPath : require __DIR__ . '/config.php';
$dbHost = $config['DB_HOST'] ?? 'localhost';
$dbName = $config['DB_NAME'] ?? 'rielcode';
$dbUser = $config['DB_USER'] ?? 'root';
$dbPass = $config['DB_PASS'] ?? '';

require_once __DIR__ . '/inc/error_codes.php';
require_once __DIR__ . '/inc/audit_logger.php';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('[RC-DB-001] admin_invoice_edit: ' . $e->getMessage());
    include __DIR__ . '/inc/_db_down.php';
}

$rc_admin_user = $_SESSION['admin_username'] ?? 'admin';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: admin.php?table=invoices"); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    error_log("[RC-ORDER-001] admin_invoice_edit: order id=$id missing");
    header("Location: admin.php?table=invoices");
    exit;
}

$flash = null;

// Helpers
function rc_money($n, $cur) {
    return $cur === 'USD' ? '$' . number_format($n, 2) : 'Rp' . number_format($n, 0, ',', '.');
}

// Build default line items if none stored
function rc_default_lines(PDO $pdo, array $order): array {
    $lines = [['description' => $order['package'] . ' — base', 'qty' => 1, 'unit_price' => (float)$order['package_price'], 'total' => (float)$order['package_price']]];
    $stmt = $pdo->prepare("
        SELECT oa.quantity, oa.price_total, pa.name, pa.type
        FROM order_addons oa
        JOIN package_addons pa ON pa.id = oa.addon_id
        WHERE oa.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $lines[] = [
            'description' => $a['name'] . ($a['type'] !== 'one_time' ? ' (×' . (int)$a['quantity'] . ')' : ''),
            'qty' => (int)$a['quantity'],
            'unit_price' => (float)$a['price_total'] / max(1, (int)$a['quantity']),
            'total' => (float)$a['price_total'],
        ];
    }
    return $lines;
}

$lineItems = !empty($order['invoice_line_items']) ? json_decode($order['invoice_line_items'], true) : null;
if (!is_array($lineItems) || empty($lineItems)) $lineItems = rc_default_lines($pdo, $order);

// ─── Handle Save / Send ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceNumber = trim($_POST['invoice_number'] ?? '') ?: ('INV-' . date('Ymd') . '-' . $id);
    $currency      = $_POST['currency']     ?? 'IDR';
    $status        = $_POST['status']       ?? 'draft';
    $dueDate       = $_POST['due_date']     ?: null;
    $notes         = trim($_POST['notes']   ?? '');
    if (!in_array($currency, ['IDR','USD'], true)) $currency = 'IDR';
    if (!in_array($status, ['draft','sent','paid','void'], true)) $status = 'draft';

    // Reconstruct lines from POST arrays
    $descs   = $_POST['li_desc']   ?? [];
    $qtys    = $_POST['li_qty']    ?? [];
    $prices  = $_POST['li_price']  ?? [];
    $newLines = [];
    $total = 0;
    for ($i = 0; $i < count($descs); $i++) {
        $d = trim($descs[$i]);
        if ($d === '') continue;
        $q = max(1, (int)$qtys[$i]);
        $p = (float)str_replace([',','Rp','$',' '], '', $prices[$i]);
        $t = $q * $p;
        $newLines[] = ['description' => $d, 'qty' => $q, 'unit_price' => $p, 'total' => $t];
        $total += $t;
    }
    $lineItems = $newLines ?: $lineItems;

    $up = $pdo->prepare("UPDATE orders SET invoice_number=?, invoice_status=?, invoice_currency=?, invoice_due_date=?, invoice_notes=?, invoice_line_items=?, invoice_amount=? WHERE id=?");
    $up->execute([$invoiceNumber, $status, $currency, $dueDate, $notes, json_encode($lineItems), $total, $id]);

    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['action_save'])) {
        $flash = ['msg' => 'Invoice draft saved.', 'variant' => 'success'];
    }
    if (isset($_POST['action_generate']) || isset($_POST['action_send'])) {
        require_once __DIR__ . '/inc/generate_invoice.php';
        try {
            $res = rc_generate_invoice($pdo, $id);
            $flash = ['msg' => 'Invoice PDF generated: ' . basename($res['file']), 'variant' => 'success'];

            if (isset($_POST['action_send'])) {
                require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
                require_once __DIR__ . '/PHPMailer/src/SMTP.php';
                require_once __DIR__ . '/PHPMailer/src/Exception.php';
                include_once __DIR__ . '/smtp_config.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $SMTP_USER;
                    $mail->Password   = $SMTP_PASS;
                    $mail->SMTPSecure = $SMTP_SECURE;
                    $mail->Port       = $SMTP_PORT;
                    $mail->setFrom($SMTP_USER, 'Rielcode Team');
                    $mail->addAddress($order['email'], $order['order_name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Rielcode Invoice ' . $res['invoice_number'];
                    $mail->Body = '<div style="font-family:Poppins,Arial,sans-serif;color:#333;line-height:1.6;">'
                        . '<p>Hi <b>' . htmlspecialchars($order['order_name']) . '</b>,</p>'
                        . '<p>Please find your Rielcode invoice attached for the <b>' . htmlspecialchars($order['package']) . '</b> project.</p>'
                        . '<p><b>Total: ' . rc_money($res['amount'], $res['currency']) . '</b></p>'
                        . '<p>Payment can be sent via Indonesian bank transfer. Reply to this email if you have any questions.</p>'
                        . '<p>Warm regards,<br><b>The Rielcode Team</b></p></div>';
                    $mail->addAttachment($res['file']);
                    $mail->send();
                    $pdo->prepare("UPDATE orders SET invoice_status='sent', invoice_sent='sent' WHERE id=?")->execute([$id]);
                    $flash = ['msg' => 'Invoice sent to ' . $order['email'], 'variant' => 'success'];
                } catch (PHPMailer\PHPMailer\Exception $e) {
                    $pdo->prepare("UPDATE orders SET invoice_sent='failed' WHERE id=?")->execute([$id]);
                    $flash = ['msg' => 'Failed to send: ' . $e->getMessage(), 'variant' => 'error'];
                }
                // Reload
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?"); $stmt->execute([$id]); $order = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $flash = ['msg' => 'Generation failed: ' . $e->getMessage(), 'variant' => 'error'];
        }
    }

    $lineItems = !empty($order['invoice_line_items']) ? json_decode($order['invoice_line_items'], true) : $lineItems;
}

// Auto-send via ?send=1
if (isset($_GET['send']) && $_GET['send'] === '1') {
    require_once __DIR__ . '/inc/generate_invoice.php';
    try {
        $res = rc_generate_invoice($pdo, $id);
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        include_once __DIR__ . '/smtp_config.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->Port       = $SMTP_PORT;
        $mail->setFrom($SMTP_USER, 'Rielcode Team');
        $mail->addAddress($order['email'], $order['order_name']);
        $mail->isHTML(true);
        $mail->Subject = 'Your Rielcode Invoice ' . $res['invoice_number'];
        $mail->Body = '<p>Hi <b>' . htmlspecialchars($order['order_name']) . '</b>,</p><p>Please find your invoice attached.</p><p><b>Total: ' . rc_money($res['amount'], $res['currency']) . '</b></p>';
        $mail->addAttachment($res['file']);
        $mail->send();
        $pdo->prepare("UPDATE orders SET invoice_status='sent', invoice_sent='sent' WHERE id=?")->execute([$id]);
        $flash = ['msg' => 'Invoice sent.', 'variant' => 'success'];
    } catch (Exception $e) {
        $flash = ['msg' => 'Send failed: ' . $e->getMessage(), 'variant' => 'error'];
    }
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?"); $stmt->execute([$id]); $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

$currency = $order['invoice_currency'] ?? 'IDR';
$status   = $order['invoice_status']   ?? 'draft';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Invoice — <?= htmlspecialchars($order['invoice_number'] ?: ('Order #' . $id)) ?></title>
    <?php include __DIR__ . '/inc/admin_theme_head.php'; ?>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
    <meta name="robots" content="noindex,nofollow">
    <style>
        .inv-wrap { max-width: 1080px; margin: 32px auto; padding: 0 24px; }
        .inv-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 24px; gap:14px; flex-wrap:wrap; }
        .inv-head h1 { margin:0; font-size: 22px; }
        .inv-card { background: var(--bg-elev); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 18px; }
        .inv-grid { display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; }
        .inv-grid .full { grid-column: span 4; }
        table.lines { width:100%; border-collapse: collapse; }
        table.lines th { background: var(--bg-sunken); color: var(--text-subtle); font-family: var(--font-mono); font-size: 11px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; padding: 10px; text-align: left; border-bottom: 1px solid var(--border); }
        table.lines td { padding:8px 6px; border-top: 1px solid var(--border); }
        table.lines tbody tr:first-child td { border-top: 0; }
        .li-row-total { text-align: right; color: var(--ok); font-weight: 600; font-family: var(--font-mono); }
        .inv-actions { display:flex; gap: 10px; flex-wrap: wrap; }
        .inv-totals { text-align: right; padding-top: 14px; font-size: 18px; font-weight: 700; color: var(--text); }
        .inv-status { display:inline-block; padding: 3px 12px; border-radius: var(--radius-pill); font-size: 11px; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: 0.06em; background: var(--info-bg); color: var(--info); border: 1px solid var(--info-bd); }
        .inv-back { color: var(--text-muted); font-size: 13px; text-decoration: none; }
        .inv-back:hover { color: var(--text); }
        .inv-meta { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
    </style>
</head>
<body>
    <?php if ($flash): ?>
        <div class="rc-toast rc-toast--<?= htmlspecialchars($flash['variant']) ?>" data-auto-dismiss="3500" style="position:fixed;top:24px;right:24px;z-index:9600;">
            <span class="rc-toast__msg"><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <div class="inv-wrap">
        <div class="inv-head">
            <div>
                <a href="admin.php?table=invoices" class="inv-back">&larr; Back to Invoices</a>
                <h1 style="margin-top:6px;">Invoice for <?= htmlspecialchars($order['order_name']) ?></h1>
                <div class="inv-meta"><?= htmlspecialchars($order['email']) ?> · <?= htmlspecialchars($order['package']) ?></div>
            </div>
            <div>
                <span class="inv-status">Status: <?= htmlspecialchars($status) ?></span>
                <?php if (!empty($order['invoice_file'])): ?>
                    <a class="button" style="margin-left:8px;" href="<?= htmlspecialchars(ltrim(str_replace('../','', $order['invoice_file']),'/')) ?>" download>Download Current PDF</a>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST">
            <div class="inv-card">
                <div class="inv-grid">
                    <div>
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" value="<?= htmlspecialchars($order['invoice_number'] ?: 'INV-' . date('Ymd') . '-' . $id) ?>">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">
                            <?php foreach (['draft','sent','paid','void'] as $s): ?>
                                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Currency</label>
                        <select name="currency">
                            <option value="IDR" <?= $currency === 'IDR' ? 'selected' : '' ?>>IDR (Rp)</option>
                            <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                        </select>
                    </div>
                    <div>
                        <label>Due Date</label>
                        <input type="date" name="due_date" value="<?= htmlspecialchars($order['invoice_due_date'] ?? '') ?>">
                    </div>
                    <div class="full">
                        <label>Notes</label>
                        <textarea name="notes" rows="3"><?= htmlspecialchars($order['invoice_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="inv-card">
                <h3 style="margin:0 0 14px;">Line Items</h3>
                <table class="lines" id="lines-table">
                    <thead>
                        <tr>
                            <th style="width:50%;">Description</th>
                            <th style="width:12%;">Qty</th>
                            <th style="width:18%;">Unit Price</th>
                            <th style="width:18%;text-align:right;">Total</th>
                            <th style="width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        <?php foreach ($lineItems as $li): ?>
                            <tr class="li-row">
                                <td><input type="text" name="li_desc[]" value="<?= htmlspecialchars($li['description'] ?? '') ?>" required></td>
                                <td><input type="number" name="li_qty[]" value="<?= (int)($li['qty'] ?? 1) ?>" min="1" class="li-qty"></td>
                                <td><input type="number" step="0.01" name="li_price[]" value="<?= htmlspecialchars((string)($li['unit_price'] ?? 0)) ?>" class="li-price"></td>
                                <td class="li-row-total">—</td>
                                <td><button type="button" class="btn-secondary" style="padding:6px 10px;" onclick="this.closest('tr').remove();recalc();">×</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="btn-secondary" style="margin-top:12px;" onclick="addLine()">+ Add Line</button>

                <div class="inv-totals">
                    Total: <span id="grand-total" class="adm-mono" style="color:var(--ok);">—</span>
                </div>
            </div>

            <div class="inv-actions">
                <button type="submit" name="action_save" class="btn-secondary">Save Draft</button>
                <button type="submit" name="action_generate" class="btn-primary">Save &amp; Regenerate PDF</button>
                <button type="submit" name="action_send" class="btn-success">Save, Regenerate &amp; Send Email</button>
            </div>
        </form>
    </div>

    <script>
        const CURRENCY = <?= json_encode($currency) ?>;
        function fmt(n) {
            if (CURRENCY === 'USD') return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return 'Rp' + Math.round(n).toLocaleString('id-ID');
        }
        function recalc() {
            let total = 0;
            document.querySelectorAll('.li-row').forEach(row => {
                const q = parseFloat(row.querySelector('.li-qty').value) || 0;
                const p = parseFloat(row.querySelector('.li-price').value) || 0;
                const t = q * p;
                total += t;
                row.querySelector('.li-row-total').textContent = fmt(t);
            });
            document.getElementById('grand-total').textContent = fmt(total);
        }
        function addLine() {
            const tr = document.createElement('tr');
            tr.className = 'li-row';
            tr.innerHTML = '<td><input type="text" name="li_desc[]" required></td>'
                + '<td><input type="number" name="li_qty[]" value="1" min="1" class="li-qty" oninput="recalc()"></td>'
                + '<td><input type="number" step="0.01" name="li_price[]" value="0" class="li-price" oninput="recalc()"></td>'
                + '<td class="li-row-total">—</td>'
                + '<td><button type="button" class="btn-secondary" style="padding:6px 10px;" onclick="this.closest(\'tr\').remove();recalc();">×</button></td>';
            document.getElementById('lines-body').appendChild(tr);
            recalc();
        }
        document.addEventListener('input', e => {
            if (e.target.classList.contains('li-qty') || e.target.classList.contains('li-price')) recalc();
        });
        recalc();
    </script>
    <script src="JS/admin-ui.js" defer></script>
</body>
</html>

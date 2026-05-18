<?php
session_start();

require_once '../connection.php';
require_once '../inc/error_codes.php';
require_once '../inc/audit_logger.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

if (!isset($_SESSION['transaction'])) {
    header('Location: ../order-form/');
    exit;
}

$id = (int)$_SESSION['transaction'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$orderRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$orderRow) {
    error_log("[RC-ORDER-001] checkout: order id=$id missing");
    header("Location: ../order-form/?err=RC-ORDER-001");
    exit;
}

$order_name = htmlspecialchars($orderRow['order_name']);
$email      = htmlspecialchars($orderRow['email']);
$phone      = htmlspecialchars($orderRow['phone_number']);
$plan       = htmlspecialchars($orderRow['package']);
$owns_domain   = htmlspecialchars($orderRow['owns_domain'] ?? 'No');
$owns_hosting  = htmlspecialchars($orderRow['owns_hosting'] ?? 'No');

$customSpecs = null;
if ($plan === 'Custom Plan') {
    $customPreset    = $orderRow['custom_preset'] ?? 'blank';
    $copySourceUrl   = $orderRow['copy_source_url'] ?? '';
    $customConfigRaw = $orderRow['custom_config'] ?? null;
    $customCfg       = ($customConfigRaw && $customConfigRaw !== 'null') ? json_decode($customConfigRaw, true) : null;
    $customSpecs = [
        'preset'       => $customPreset,
        'copy_url'     => $copySourceUrl,
        'pages'        => $customCfg['pages']       ?? null,
        'maintenance'  => $customCfg['maintenance'] ?? null,
        'features'     => $customCfg['features']    ?? [],
    ];
}

$stmt = $conn->prepare("SELECT idr_price, orders FROM packages WHERE package_name = ?");
$stmt->bind_param("s", $plan);
$stmt->execute();
$priceRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$priceRow) {
    error_log("[RC-ORDER-002] checkout: package='$plan' not in packages table");
    header("Location: ../order-form/?err=RC-ORDER-002");
    exit;
}

$price          = (int)$priceRow['idr_price'];
$currentOrders  = (int)$priceRow['orders'];

if ($plan === 'Custom Plan' && isset($_SESSION['custom_total'])) {
    $package_price  = (int)$_SESSION['custom_total'];
    $discount_price = 0;
} else {
    $display_original = $price * 2;
    $discount_price   = $display_original * 0.5;
    $package_price    = $display_original - $discount_price;
}

$addonsResult = $conn->query("SELECT * FROM package_addons ORDER BY id ASC");
$availableAddons = [];
while ($row = $addonsResult->fetch_assoc()) {
    $availableAddons[] = $row;
}

$selectedAddonIds = $_SESSION['selected_addons'] ?? [];
$selectedAddons   = [];
$addons_total     = 0;

foreach ($availableAddons as $addon) {
    if (in_array($addon['id'], $selectedAddonIds)) {
        $qty = 1;
        if ($addon['type'] === 'per_page') {
            $qty = (int)($_SESSION['addon_qty'][$addon['id']] ?? 1);
            $qty = max(1, min($qty, 20));
        } elseif ($addon['type'] === 'monthly') {
            $qty = (int)($_SESSION['addon_qty'][$addon['id']] ?? 1);
            $qty = max(1, min($qty, 24));
        }
        $line_total = (int)$addon['price_idr'] * $qty;
        $addons_total += $line_total;
        $selectedAddons[] = [
            'addon'      => $addon,
            'qty'        => $qty,
            'line_total' => $line_total,
        ];
    }
}

$final_price = $package_price + $addons_total;
$isStudentPlan = ($plan === 'Student Plan');

$claimedFreePromo = (strpos($orderRow['description'] ?? '', '🎁 Claimed') !== false) ||
                    (strpos($orderRow['description'] ?? '', 'Free Hosting') !== false && strpos($orderRow['description'] ?? '', 'Promo') !== false);

$needsDomainWarning  = false;
$needsHostingWarning = false;
if (!$isStudentPlan && !$claimedFreePromo) {
    $needsDomainWarning  = ($owns_domain === 'No');
    $needsHostingWarning = ($owns_hosting === 'No');
}

// Referral code resolution — runs on both GET and POST so the error message can re-render
$referralError = '';
$referrer = null;
$rawCode = trim($_POST['referral_code'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $rawCode !== '') {
    $stmt = $conn->prepare("SELECT id, commission_rate FROM referrers WHERE code = ? AND status = 'active'");
    $stmt->bind_param("s", $rawCode);
    $stmt->execute();
    $refRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($refRow) {
        $referrer = $refRow;
    } else {
        $referralError = 'This code is invalid or inactive.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $orderRow['status'] !== 'On Progress' && $referralError === '') {

    $orders_after = $currentOrders + 1;
    $stmt = $conn->prepare("UPDATE packages SET orders = ? WHERE package_name = ?");
    $stmt->bind_param("is", $orders_after, $plan);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE orders SET status='On Progress', package_price=?, addons_total=?, final_price=? WHERE id=?");
    $stmt->bind_param("iiii", $package_price, $addons_total, $final_price, $id);
    $stmt->execute();
    $stmt->close();

    // Referral: store code on order and insert commission row if valid code was submitted
    if ($referrer !== null) {
        $stmtRef = $conn->prepare("UPDATE orders SET referral_code = ? WHERE id = ?");
        $stmtRef->bind_param("si", $rawCode, $id);
        $stmtRef->execute();
        $stmtRef->close();

        $commissionAmount = round($final_price * ($referrer['commission_rate'] / 100), 2);
        $stmtCom = $conn->prepare("INSERT INTO referral_commissions (referrer_id, order_id, order_amount, commission_amount) VALUES (?, ?, ?, ?)");
        $stmtCom->bind_param("iidd", $referrer['id'], $id, $final_price, $commissionAmount);
        $stmtCom->execute();
        $stmtCom->close();
    }

    if (!empty($selectedAddons)) {
        $stmtAddon = $conn->prepare("INSERT INTO order_addons (order_id, addon_id, quantity, price_total) VALUES (?,?,?,?)");
        foreach ($selectedAddons as $sa) {
            $stmtAddon->bind_param("iiii", $id, $sa['addon']['id'], $sa['qty'], $sa['line_total']);
            $stmtAddon->execute();
        }
        $stmtAddon->close();
    }

    // Reserve invoice number on order — actual PDF is generated later in admin
    $invoice_number = 'INV-' . date('Ymd') . '-' . $id;
    $stmt = $conn->prepare("UPDATE orders SET invoice_number=?, invoice_status='draft', invoice_amount=?, invoice_currency='IDR' WHERE id=?");
    $stmt->bind_param("sdi", $invoice_number, $final_price, $id);
    $stmt->execute();
    $stmt->close();

    // Generate access token for client progress portal (one per order, persists until delivered)
    $progress_token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("INSERT INTO order_access_tokens (order_id, token) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $progress_token);
    $stmt->execute();
    $stmt->close();
    $_SESSION['progress_token']    = $progress_token;
    $_SESSION['progress_order_id'] = $id;
    $progress_url = 'https://progress.rielcode.com/?t=' . $progress_token;

    // Send thank-you email only (no invoice attachment).
    include_once '../smtp_config.php';
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->Port       = $SMTP_PORT;
        $mail->setFrom($SMTP_USER, 'Rielcode Team');
        $mail->addAddress($email, $order_name);
        $mail->isHTML(true);
        $mail->Subject = "Thank you for your order, " . $order_name . "!";
        $mail->Body    = '
<div style="font-family:Poppins,Arial,sans-serif;color:#333;line-height:1.6;max-width:560px;">
    <p>Hi <b>' . htmlspecialchars($order_name) . '</b>,</p>
    <p>Thank you for choosing <b>Rielcode</b> for your <b>' . htmlspecialchars($plan) . '</b> project.</p>
    <p>We have received your order. Our team will reach out to you within <b>24 hours</b> to confirm the project details and send your invoice separately.</p>
    <p>You can <b>track your project progress</b> anytime via your private link:<br>
    <a href="' . htmlspecialchars($progress_url) . '" style="color:#3a7bff;font-weight:600;">' . htmlspecialchars($progress_url) . '</a></p>
    <p>In the meantime, feel free to reply to this email or message us on WhatsApp if you have any questions.</p>
    <br>
    <p>Warm regards,<br>
    <b>The Rielcode Team</b><br>
    <a href="https://rielcode.com" style="color:#1a73e8;">rielcode.com</a></p>
    <hr style="border:none;border-top:1px solid #ddd;margin:20px 0;">
    <p style="font-size:12px;color:#777;">
        This email was sent automatically by the Rielcode system.<br>
        If you don\'t recognize this email, you can safely ignore it.
    </p>
</div>';
        $mail->send();
        $stmt = $conn->prepare("UPDATE orders SET invoice_sent='pending' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        rc_audit('PAYMENT_PROOF_RECEIVED', $email, ['order_id' => $id, 'plan' => $plan]);
    } catch (MailException $e) {
        // Non-fatal — order is saved, only the confirmation email failed.
        error_log("[RC-PAY-003] checkout mail send: " . $e->getMessage());
        rc_audit('PAYMENT_EMAIL_FAIL', $email, ['order_id' => $id, 'err' => $e->getMessage()], 'error');
    }

    unset($_SESSION['selected_addons'], $_SESSION['addon_qty']);

    header('Location: ../checkout/success/');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Rielcode</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../CSS/redesign.css">
    <link rel="stylesheet" href="../CSS/checkout.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/tailwind.css">
</head>

<body class="rc-redesign">
    <div class="checkout-container p-6">
        <div class="checkout-heading flex flex-col items-center" style="gap:10px; margin-bottom:24px;">
            <span class="rc-eyebrow">checkout step 02</span>
            <h1 class="rc-h1 text-white font-bold">Checkout Confirmation</h1>
            <p class="rc-body" style="margin:0;">Review your order before confirming.</p>
        </div>

        <div class="info-container">
            <div class="img-container">
                <img src="../IMG/Rielcode Logo Transparent.png" alt="Rielcode">
            </div>

            <div class="personal-info">
                <div class="order-name">
                    <p>Billed to: <b><?= strtoupper($order_name) ?></b></p>
                    <span><?= $email ?></span>
                </div>
                <div class="package-type">
                    <p>Package type: <b><?= $plan ?></b></p>
                    <span><?= $phone ?></span>
                </div>
            </div>

            <?php if ($isStudentPlan): ?>
                <div class="landing-note">
                    ⚠ Note: Student Plan does not include free hosting or domain. Website design only.
                </div>
            <?php elseif ($claimedFreePromo): ?>
                <div class="landing-note" style="background: rgba(62,207,142,0.08); border-color: rgba(62,207,142,0.25); color: #3ecf8e;">
                    ✅ Free hosting &amp; .COM domain included via promo.
                </div>
            <?php elseif ($needsHostingWarning || $needsDomainWarning): ?>
                <div class="landing-note">
                    ⚠ Note: <?php
                        $warnings = [];
                        if ($needsHostingWarning) $warnings[] = 'hosting';
                        if ($needsDomainWarning)  $warnings[] = 'domain';
                        echo 'You indicated you don\'t have ' . implode(' or ', $warnings) . '. ';
                    ?>Rielcode will help set up free <?= implode(' &amp; ', $warnings) ?> based on your package.
                </div>
            <?php endif; ?>

            <div class="purchase-info">
                <h3>Purchase Information</h3>
                <div class="package-price">
                    <p>Package price<?= $plan !== 'Custom Plan' ? ' (before discount)' : '' ?></p>
                    <span>Rp<?= number_format($plan === 'Custom Plan' ? $package_price : $display_original, 0, ',', '.') ?></span>
                </div>
                <?php if ($customSpecs): ?>
                <div class="custom-specs" style="margin-top:14px;padding:12px 14px;background:rgba(58,123,255,0.07);border:1px solid rgba(58,123,255,0.2);border-radius:10px;">
                    <p style="font-size:13px;font-weight:600;color:#6fa3ff;margin:0 0 8px;">Custom Plan Specifications</p>
                    <ul style="margin:0;padding:0 0 0 16px;font-size:13px;color:rgba(255,255,255,0.8);line-height:1.8;">
                        <li>Type: <b><?= ucfirst(htmlspecialchars($customSpecs['preset'])) ?> Website</b></li>
                        <?php if ($customSpecs['preset'] === 'copy' && $customSpecs['copy_url']): ?>
                        <li>Reference URL: <b><?= htmlspecialchars($customSpecs['copy_url']) ?></b></li>
                        <?php endif; ?>
                        <?php if ($customSpecs['pages']): ?>
                        <li>Pages: <b><?= (int)$customSpecs['pages'] ?></b></li>
                        <?php endif; ?>
                        <?php if ($customSpecs['maintenance']): ?>
                        <li>Maintenance: <b><?= (int)$customSpecs['maintenance'] ?> month<?= (int)$customSpecs['maintenance'] > 1 ? 's' : '' ?></b></li>
                        <?php endif; ?>
                        <?php if (!empty($customSpecs['features'])): ?>
                        <li>Features: <b><?= htmlspecialchars(implode(', ', $customSpecs['features'])) ?></b></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($plan !== 'Custom Plan'): ?>
            <div class="discount-info">
                <h3>Discount</h3>
                <div class="discount-price">
                    <p>Grand Opening (<b>50%</b>)</p>
                    <span>-Rp<?= number_format($discount_price, 0, ',', '.') ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($selectedAddons)): ?>
                <div class="addons-info">
                    <h3>Add-ons</h3>
                    <?php foreach ($selectedAddons as $sa): ?>
                        <div class="addon-line">
                            <p>
                                <?= htmlspecialchars($sa['addon']['name']) ?>
                                <?php if ($sa['addon']['type'] === 'per_page'): ?>
                                    <b> ×<?= $sa['qty'] ?> pages</b>
                                <?php elseif ($sa['addon']['type'] === 'monthly'): ?>
                                    <b> ×<?= $sa['qty'] ?> month<?= $sa['qty'] > 1 ? 's' : '' ?></b>
                                <?php endif; ?>
                            </p>
                            <span>+Rp<?= number_format($sa['line_total'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="total-info">
                <h3>Total</h3>
                <div class="total-price">
                    <p>Grand Total</p>
                    <span>Rp<?= number_format($final_price, 0, ',', '.') ?></span>
                </div>
            </div>

            <form method="post" id="checkoutForm">
                <div style="margin-bottom:16px;">
                    <label for="referral_code" style="display:block;font-size:13px;color:rgba(255,255,255,0.6);margin-bottom:6px;">Referral Code (optional)</label>
                    <input
                        type="text"
                        name="referral_code"
                        id="referral_code"
                        class="referral-input"
                        placeholder="e.g. BUDI10"
                        value="<?= htmlspecialchars($rawCode) ?>"
                    >
                    <?php if ($referralError !== ''): ?>
                        <p style="color:#f87171;font-size:12px;margin-top:6px;"><?= htmlspecialchars($referralError) ?></p>
                    <?php else: ?>
                        <p style="color:#475569;font-size:12px;margin-top:6px;">Code applied at confirmation.</p>
                    <?php endif; ?>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        I agree to the <a href="../terms&conditions/">terms &amp; conditions</a>
                        as set out by the user agreement.
                    </label>
                </div>
                <button class="btn" id="checkoutBtn" type="submit">Confirm</button>
            </form>
        </div>
    </div>

    <script src="../JS/checkout.js"></script>

</body>

</html>
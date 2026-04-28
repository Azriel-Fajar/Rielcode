<?php
session_start();

require_once '../connection.php';
require_once '../dompdf/autoload.inc.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../PHPMailer/src/Exception.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

if (!isset($_SESSION['transaction'])) {
    header('Location: ../order-form/');
    exit;
}

$id = (int)$_SESSION['transaction'];

// --- Load order ---
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$orderRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$orderRow) die("Order not found.");

$order_name = htmlspecialchars($orderRow['order_name']);
$email      = htmlspecialchars($orderRow['email']);
$phone      = htmlspecialchars($orderRow['phone_number']);
$plan       = htmlspecialchars($orderRow['package']);
$owns_domain   = htmlspecialchars($orderRow['owns_domain'] ?? 'No');
$owns_hosting  = htmlspecialchars($orderRow['owns_hosting'] ?? 'No');

// --- Load package price ---
$stmt = $conn->prepare("SELECT idr_price, orders FROM packages WHERE package_name = ?");
$stmt->bind_param("s", $plan);
$stmt->execute();
$priceRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$priceRow) die("Package not found.");

$price          = (int)$priceRow['idr_price'];
$currentOrders  = (int)$priceRow['orders'];
$discount_pct   = 0.5;
$discount_price = $price * $discount_pct;
$package_price  = $price - $discount_price;

// --- Load available add-ons ---
$addonsResult = $conn->query("SELECT * FROM package_addons ORDER BY id ASC");
$availableAddons = [];
while ($row = $addonsResult->fetch_assoc()) {
    $availableAddons[] = $row;
}

// --- Load selected add-ons from session ---
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

// Detect if user claimed free hosting & domain promo
$claimedFreePromo = (strpos($orderRow['description'] ?? '', '🎁 Claimed') !== false) ||
                    (strpos($orderRow['description'] ?? '', 'Free Hosting') !== false && strpos($orderRow['description'] ?? '', 'Promo') !== false);

// Determine if hosting/domain warnings should show
$needsDomainWarning  = false;
$needsHostingWarning = false;
if (!$isStudentPlan && !$claimedFreePromo) {
    $needsDomainWarning  = ($owns_domain === 'No');
    $needsHostingWarning = ($owns_hosting === 'No');
}

// --- Handle Confirm submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $orderRow['status'] !== 'On Progress') {

    $orders_after = $currentOrders + 1;
    $stmt = $conn->prepare("UPDATE packages SET orders = ? WHERE package_name = ?");
    $stmt->bind_param("is", $orders_after, $plan);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE orders SET status='On Progress', package_price=?, addons_total=?, final_price=? WHERE id=?");
    $stmt->bind_param("iiii", $package_price, $addons_total, $final_price, $id);
    $stmt->execute();
    $stmt->close();

    if (!empty($selectedAddons)) {
        $stmtAddon = $conn->prepare("INSERT INTO order_addons (order_id, addon_id, quantity, price_total) VALUES (?,?,?,?)");
        foreach ($selectedAddons as $sa) {
            $stmtAddon->bind_param("iiii", $id, $sa['addon']['id'], $sa['qty'], $sa['line_total']);
            $stmtAddon->execute();
        }
        $stmtAddon->close();
    }

    $invoice_number = 'INV-' . date('Ymd') . '-' . $id;

    $bgPath = realpath('../IMG/invoice_bg.png');
    if (!$bgPath || !file_exists($bgPath)) die('Invoice background image not found.');
    $bgData = base64_encode(file_get_contents($bgPath));
    $bgExt  = strtolower(pathinfo($bgPath, PATHINFO_EXTENSION));

    $addonRowsHtml = '';
    foreach ($selectedAddons as $sa) {
        $addonLabel = htmlspecialchars($sa['addon']['name']);
        if ($sa['addon']['type'] === 'per_page') $addonLabel .= ' x' . $sa['qty'] . ' pages';
        if ($sa['addon']['type'] === 'monthly')  $addonLabel .= ' x' . $sa['qty'] . ' months';
        $addonRowsHtml .= '
            <tr>
                <td class="col-package" style="font-size:0.85rem; color:#555;">' . $addonLabel . '</td>
                <td class="col-price">Rp' . number_format($sa['addon']['price_idr'] * $sa['qty'], 0, ',', '.') . '</td>
                <td class="col-discount">—</td>
                <td class="col-total">Rp' . number_format($sa['line_total'], 0, ',', '.') . '</td>
            </tr>';
    }

    $totalRowHtml = '';
    if (!empty($selectedAddons)) {
        $totalRowHtml = '
            <tr style="border-top: 2px solid #333;">
                <td class="col-package" style="font-weight:900;">GRAND TOTAL</td>
                <td class="col-price"></td>
                <td class="col-discount"></td>
                <td class="col-total" style="font-weight:900;">Rp' . number_format($final_price, 0, ',', '.') . '</td>
            </tr>';
    }

    $invoiceHTML = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 0; padding: 0; }
body { font-family: "Poppins", sans-serif; margin: 0; background-color: #333; color: #1c1f22; }
.page {
    width: 210mm; height: 297mm; position: relative;
    background-image: url("data:image/' . $bgExt . ';base64,' . $bgData . '");
    background-size: cover; background-repeat: no-repeat; background-position: center; overflow: hidden;
}
#invoice-details { position: absolute; top: 300px; left: 68px; font-size: 16px; font-weight: bold; }
#billing-details { position: absolute; top: 196px; left: 366px; font-size: 1.1rem; font-weight: bold; line-height: 1.4; }
#table-row { position: absolute; top: 408px; left: 70px; width: 658px; font-size: 1.05rem; font-weight: bold; }
#table-row table { width: 100%; border-collapse: collapse; text-align: center; }
#table-row td { padding: 6px 4px; vertical-align: middle; }
.col-package  { width: 150px; text-align: left; }
.col-price    { width: 140px; }
.col-discount { width: 85px; }
.col-total    { width: 130px; }
</style>
</head>
<body>
<div class="page">
    <div id="invoice-details">
        <b>' . htmlspecialchars($invoice_number) . '</b><br/>
        Date : ' . date("d F Y") . '
    </div>
    <div id="billing-details">
        BILLING TO: <b>' . strtoupper($order_name) . '</b><br/><br/>
        ' . $email . '<br/>
        ' . $phone . '
    </div>
    <div id="table-row">
        <table>
            <tr>
                <td class="col-package">' . $plan . '</td>
                <td class="col-price">Rp' . number_format($price, 0, ',', '.') . '</td>
                <td class="col-discount">50%</td>
                <td class="col-total">Rp' . number_format($package_price, 0, ',', '.') . '</td>
            </tr>
            ' . $addonRowsHtml . $totalRowHtml . '
        </table>
    </div>
</div>
</body>
</html>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($invoiceHTML);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $invoiceDir = realpath(__DIR__ . '/..') . '/invoices';
    if (!is_dir($invoiceDir)) mkdir($invoiceDir, 0755, true);
    $invoice_file = $invoiceDir . '/' . $invoice_number . '.pdf';
    file_put_contents($invoice_file, $dompdf->output());

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
        $mail->Subject = "Your Rielcode Invoice - Thank you, " . $order_name . "!";
        $mail->Body    = '
<div style="font-family:Poppins,Arial,sans-serif;color:#333;line-height:1.6;">
    <p>Hi <b>' . htmlspecialchars($order_name) . '</b>,</p>
    <p>Thank you for trusting <b>Rielcode</b> with your project.<br>
    Please find your invoice attached as confirmation of your order.</p>
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
        $mail->addAttachment($invoice_file);
        $mail->send();

        $relativeFile = '../invoices/' . $invoice_number . '.pdf';
        $stmt = $conn->prepare("UPDATE orders SET invoice_sent='sent', invoice_number=?, invoice_file=? WHERE id=?");
        $stmt->bind_param("ssi", $invoice_number, $relativeFile, $id);
        $stmt->execute();
        $stmt->close();
    } catch (MailException $e) {
        $stmt = $conn->prepare("UPDATE orders SET invoice_sent='failed' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    unset($_SESSION['selected_addons'], $_SESSION['addon_qty']);

    header('Location: ../checkout/success/');
    exit;
}

// Build JS-safe data for AI summary
$summaryData = json_encode([
    'name'    => $orderRow['order_name'],
    'package' => $plan,
    'price'   => 'Rp' . number_format($final_price, 0, ',', '.'),
    'domain'  => $owns_domain === 'Yes' ? 'Have' : 'Don\'t have',
    'hosting' => $owns_hosting === 'Yes' ? 'Have' : 'Don\'t have',
]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Rielcode</title>
    <link rel="stylesheet" href="../CSS/checkout.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <div class="checkout-container p-6">
        <h1 class="text-white font-bold flex flex-col justify-start items-center">
            Checkout Confirmation
        </h1>

        <div class="info-container">
            <div class="img-container">
                <img src="../IMG/Rielcode Logo Transparent.png" alt="Rielcode Logo">
            </div>

            <!-- Personal Info -->
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

            <!-- Package-specific notes -->
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

            <!-- Purchase Info -->
            <div class="purchase-info">
                <h3>Purchase Information</h3>
                <div class="package-price">
                    <p>Package price (before discount)</p>
                    <span>Rp<?= number_format($price, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Discount -->
            <div class="discount-info">
                <h3>Discount</h3>
                <div class="discount-price">
                    <p>Grand Opening (<b>50%</b>)</p>
                    <span>-Rp<?= number_format($discount_price, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Add-ons -->
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

            <!-- Total -->
            <div class="total-info">
                <h3>Total</h3>
                <div class="total-price">
                    <p>Grand Total</p>
                    <span>Rp<?= number_format($final_price, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════
                 FEATURE 2 — AI Order Summary Card
            ════════════════════════════════════════════ -->
            <div class="ai-summary-card">
                <div class="ai-summary-header">RielBot Analysis</div>
                <div class="ai-summary-text loading" id="ai-summary-text">
                    Click the button below to generate a summary of your order…
                </div>
                <div class="ai-summary-warning hidden" id="ai-summary-warning"></div>
                <button class="ai-gen-btn" id="ai-gen-btn" onclick="rielGenerateSummary()">
                    <div class="spin"></div>
                    <span class="btn-txt">✦ Generate AI Summary of your order</span>
                </button>
            </div>

            <form method="post" id="checkoutForm">
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

    <!-- Feature 2 — AI Order Summary Script -->
    <script>
        (function() {
            const PROXY_URL = window.location.hostname === 'localhost' ?
                window.location.origin + '/proxy.php' :
                window.location.origin + '/proxy';

            const orderData = <?= $summaryData ?>;

            window.rielGenerateSummary = async function() {
                const btn = document.getElementById('ai-gen-btn');
                const box = document.getElementById('ai-summary-text');
                const warn = document.getElementById('ai-summary-warning');

                btn.classList.add('loading');
                box.classList.add('loading');
                box.textContent = 'Menganalisis pesananmu…';
                warn.classList.add('hidden');

                const prompt = `You are a Rielcode checkout assistant. Summarize the following order in two short, clear, and friendly sentences in English. Also mention one important point to note based on the following information.

Order data:
- Name: ${orderData.name}
- Package: ${orderData.package}
- Total: ${orderData.price}
- Domain: ${orderData.domain}
- Hosting: ${orderData.hosting}

Respond ONLY with valid JSON:
{"summary":"2-sentence summary","warning":"1 important point or empty string if none"}`;

                try {
                    const res = await fetch(PROXY_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: prompt
                        })
                    });
                    const data = await res.json();
                    const text = (data.reply || '{}').replace(/```json|```/g, '').trim();
                    const parsed = JSON.parse(text);

                    box.classList.remove('loading');
                    box.textContent = parsed.summary || 'Your order is ready to be confirmed.';

                    if (parsed.warning && parsed.warning.trim()) {
                        warn.textContent = parsed.warning;
                        warn.classList.remove('hidden');
                    }
                } catch (e) {
                    box.classList.remove('loading');
                    box.textContent = orderData.name + ' ordered ' + orderData.package +
                        ' for ' + orderData.price +
                        ' (50% discount). This package is ready to be processed after confirmation.';
                    if (orderData.hosting === 'Don\'t have') {
                        warn.textContent = 'You don\'t have hosting yet — Rielcode will help set up free hosting according to the selected package.';
                        warn.classList.remove('hidden');
                    }
                }

                btn.classList.remove('loading');
            };
        })();
    </script>
</body>

</html>
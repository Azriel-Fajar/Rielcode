<?php
include '../connection.php';
session_start();

// --- Determine pre-selected plan from URL ---
$aksiMap = [
    'landing'  => 'Student Plan',
    'starter'  => 'Starter Plan',
    'pro'      => 'Pro Plan',
    'business' => 'Premium Plan',
];
$aksi         = $_GET['aksi'] ?? '';
$defaultPlan  = $aksiMap[$aksi] ?? '';
$isLanding    = ($defaultPlan === 'Student Plan');

// --- Load add-ons from DB ---
$addonsResult   = $conn->query("SELECT * FROM package_addons ORDER BY id ASC");
$availableAddons = [];
while ($row = $addonsResult->fetch_assoc()) {
    $availableAddons[] = $row;
}

// ─── Handle: resume incomplete order ──────────────────────────────────────────
if (isset($_POST['continue'])) {
    header("Location: ../checkout/");
    exit;
}

if (isset($_POST['no'])) {
    if (isset($_SESSION['transaction'])) {
        $id      = $_SESSION['transaction'];
        $stmtDel = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();
    }
    session_destroy();
    header("Location: ../order-form/");
    exit;
}

// ─── Handle: new order submission ─────────────────────────────────────────────
if (isset($_POST['submit'])) {
    $order_name  = trim($_POST['nama']        ?? '');
    $email       = trim($_POST['email']       ?? '');
    $phone_number= trim($_POST['phone']       ?? '');
    $package     = trim($_POST['package']     ?? '');
    $domain      = $_POST['domain']           ?? 'No';
    $hosting     = $_POST['hosting']          ?? 'No';
    $description = trim($_POST['additional']  ?? '');

    // Promo flag
    if (isset($_POST['free_promo'])) {
        $promoText   = "🎁 Claimed Year-End Promo: Free Hosting & .COM Domain";
        $description = $description ? $description . "\n\n---\n" . $promoText : $promoText;
    }

    // Student Plan: no hosting/domain included
    if ($package === 'Student Plan') {
        $domain  = 'No';
        $hosting = 'No';
    }

    // Get package_id
    $stmtPkg = $conn->prepare("SELECT id FROM packages WHERE package_name = ?");
    $stmtPkg->bind_param("s", $package);
    $stmtPkg->execute();
    $pkgRow     = $stmtPkg->get_result()->fetch_assoc();
    $package_id = (int)($pkgRow['id'] ?? 0);
    $stmtPkg->close();

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders
            (order_name, email, package, package_id, owns_domain, owns_hosting, phone_number, description, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
    ");
    $stmt->bind_param("sssissss",
        $order_name, $email, $package, $package_id,
        $domain, $hosting, $phone_number, $description
    );
    $stmt->execute();
    $_SESSION['transaction'] = $conn->insert_id;
    $stmt->close();

    // Save selected add-ons to session
    $selectedAddonIds = [];
    $addonQty         = [];
    foreach ($availableAddons as $addon) {
        $key = 'addon_' . $addon['id'];
        if (isset($_POST[$key])) {
            $selectedAddonIds[] = (int)$addon['id'];
            if ($addon['type'] === 'per_page' || $addon['type'] === 'monthly') {
                $addonQty[$addon['id']] = max(1, (int)($_POST['addon_qty_' . $addon['id']] ?? 1));
            }
        }
    }
    $_SESSION['selected_addons'] = $selectedAddonIds;
    $_SESSION['addon_qty']       = $addonQty;

    header("Location: ../checkout/");
    exit;
}

// ─── Check for incomplete session ─────────────────────────────────────────────
$incompleteOrder = null;
if (isset($_SESSION['transaction']) && $_SESSION['transaction']) {
    $id       = (int)$_SESSION['transaction'];
    $resOrder = $conn->query("SELECT * FROM orders WHERE id = $id");
    $incompleteOrder = $resOrder ? $resOrder->fetch_assoc() : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form | Rielcode</title>
    <link rel="stylesheet" href="../CSS/order-form.css">
    <script defer src="../JS/order.js"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    /* ── Plan grid 2×2 ──────────────────────────────── */
    .plan-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }

    .plan-container input[type="radio"] { display: none; }

    .plan-container label {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 16px 18px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .plan-container label:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(58,124,255,0.3);
    }

    .plan-container input[type="radio"]:checked + label {
        background: rgba(58,124,255,0.08);
        border-color: #3a7cff;
        box-shadow: 0 0 0 1px rgba(58,124,255,0.25);
    }

    /* Landing checked = amber */
    #landing:checked + label {
        background: rgba(255,178,55,0.08);
        border-color: #ffb237;
        box-shadow: 0 0 0 1px rgba(255,178,55,0.25);
    }

    .plan-label-name {
        font-family: "Outfit", sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: #e2e8f0;
        margin: 0;
    }

    .plan-label-price {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.7rem;
        color: #475569;
        letter-spacing: 0.2px;
    }

    .plan-label-badge {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.6rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        margin-top: 2px;
    }

    .badge-landing  { background: rgba(255,178,55,0.12); color: #ffb237; }
    .badge-starter  { background: rgba(62,207,142,0.12); color: #3ecf8e; }
    .badge-pro      { background: rgba(58,124,255,0.12); color: #3a7cff; }
    .badge-business { background: rgba(163,91,255,0.12); color: #a35bff; }

    /* ── Landing page note ──────────────────────────── */
    .landing-notice {
        display: none;
        background: rgba(255,178,55,0.06);
        border: 1px solid rgba(255,178,55,0.22);
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-family: "JetBrains Mono", monospace;
        font-size: 0.68rem;
        color: #ffb237;
        line-height: 1.6;
    }

    /* ── Add-ons section ────────────────────────────── */
    .addons-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid rgba(255,255,255,0.07);
    }

    .addons-section h2 {
        font-family: "Syne", sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 6px;
        letter-spacing: -0.2px;
    }

    .addons-subtitle {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.68rem;
        color: #475569;
        margin-bottom: 16px;
        letter-spacing: 0.3px;
    }

    .addon-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .addon-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .addon-item:hover {
        background: rgba(163,91,255,0.05);
        border-color: rgba(163,91,255,0.2);
    }

    .addon-item.selected {
        background: rgba(163,91,255,0.07);
        border-color: rgba(163,91,255,0.35);
    }

    .addon-item input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        min-width: 18px;
        border: 1.5px solid rgba(255,255,255,0.18);
        border-radius: 5px;
        background: rgba(255,255,255,0.04);
        cursor: pointer;
        margin-top: 2px;
        transition: all 0.2s ease;
        position: relative;
        flex-shrink: 0;
    }

    .addon-item input[type="checkbox"]:checked {
        background: #a35bff;
        border-color: #a35bff;
    }

    .addon-item input[type="checkbox"]:checked::after {
        content: "✓";
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #fff;
        font-weight: 700;
        top: 1px;
        left: 2px;
    }

    .addon-text { flex: 1; }

    .addon-name {
        font-family: "Outfit", sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        color: #e2e8f0;
        margin-bottom: 2px;
    }

    .addon-desc {
        font-family: "Outfit", sans-serif;
        font-size: 0.75rem;
        color: #475569;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .addon-price {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.7rem;
        color: #a35bff;
        letter-spacing: 0.2px;
    }

    .addon-qty-row {
        display: none;
        margin-top: 8px;
        align-items: center;
        gap: 8px;
    }

    .addon-qty-row.visible { display: flex; }

    .addon-qty-row label {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.65rem;
        color: #475569;
    }

    .addon-qty-row input[type="number"] {
        width: 60px;
        padding: 5px 10px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #e2e8f0;
        font-family: "JetBrains Mono", monospace;
        font-size: 0.8rem;
        outline: none;
    }

    /* ── Live total display ─────────────────────────── */
    .addons-total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
        padding: 12px 18px;
        background: rgba(163,91,255,0.05);
        border: 1px solid rgba(163,91,255,0.15);
        border-radius: 12px;
    }

    .addons-total-label {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.7rem;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .addons-total-value {
        font-family: "JetBrains Mono", monospace;
        font-size: 0.95rem;
        font-weight: 700;
        color: #c084fc;
    }

    /* ── Domain/hosting hidden for Landing ─────────── */
    .domain-hosting-wrap.hidden { display: none; }

    @media (max-width: 600px) {
        .plan-container { grid-template-columns: 1fr 1fr; gap: 8px; }
        .addon-grid     { grid-template-columns: 1fr; }
        .plan-label-name { font-size: 0.82rem; }
    }
    </style>
</head>
<body class="w-100">
<script>
(function() {
    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
})();
</script>

<div class="background w-100">

    <?php if ($incompleteOrder): ?>
    <!-- ── Incomplete Order Popup ── -->
    <div class="popup-background">
        <div class="popup-container">
            <h3>Incomplete Order</h3>
            <div class="personal-info">
                <div class="order-name">
                    <p>Billed to: <b><?= strtoupper(htmlspecialchars($incompleteOrder['order_name'])) ?></b></p>
                    <span><?= htmlspecialchars($incompleteOrder['email']) ?></span>
                </div>
                <div class="package-type">
                    <p>Package type: <b><?= htmlspecialchars($incompleteOrder['package']) ?></b></p>
                    <span><?= htmlspecialchars($incompleteOrder['phone_number']) ?></span>
                </div>
            </div>
            <p>Do you wish to continue this order?</p>
            <form method="post">
                <button type="submit" name="continue" class="btn btn-glow">Yes</button>
                <button type="submit" name="no"       class="btn btn-glow">No</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <a href="../" class="btn">Back</a>

    <div class="form-container">
        <form method="post" id="orderForm">

            <!-- ── Title ── -->
            <div class="title d-flex justify-content-center align-items-center">
                <h1 class="text-white">Order Form</h1>
                <p>Fill out the form below to confirm your purchase</p>
            </div>

            <!-- ── Customer Information ── -->
            <div class="customer-info-container">
                <h2>Customer Information</h2>
                <input type="text"   name="nama"  id="nama"  placeholder="Name"                   required>
                <input type="email"  name="email" id="email" placeholder="Email address"           required>
                <input type="number" name="phone" id="phone" placeholder="ex. 081289328493" maxlength="13" required>
            </div>

            <!-- ── Package Detail ── -->
            <div class="package-details-container">
                <h2>Package Detail</h2>

                <!-- 2×2 plan grid -->
                <div class="plan-container">

                    <!-- Student Plan -->
                    <input type="radio" name="package" id="landing" value="Student Plan"
                           <?= $defaultPlan === 'Student Plan' ? 'checked' : '' ?> required>
                    <label for="landing">
                        <h3 class="plan-label-name">Student Plan</h3>
                        <span class="plan-label-price">$29.99 / Rp499.000</span>
                        <span class="plan-label-badge badge-landing">For Students</span>
                    </label>

                    <!-- Starter -->
                    <input type="radio" name="package" id="starter" value="Starter Plan"
                           <?= $defaultPlan === 'Starter Plan' ? 'checked' : '' ?> required>
                    <label for="starter">
                        <h3 class="plan-label-name">Starter Plan</h3>
                        <span class="plan-label-price">$59.99 / Rp999.000</span>
                        <span class="plan-label-badge badge-starter">Landing + Hosting</span>
                    </label>

                    <!-- Pro -->
                    <input type="radio" name="package" id="pro" value="Pro Plan"
                           <?= $defaultPlan === 'Pro Plan' ? 'checked' : '' ?> required>
                    <label for="pro">
                        <h3 class="plan-label-name">Pro Plan</h3>
                        <span class="plan-label-price">$119.99 / Rp1.999.000</span>
                        <span class="plan-label-badge badge-pro">Most Popular</span>
                    </label>

                    <!-- Business -->
                    <input type="radio" name="package" id="business" value="Premium Plan"
                           <?= $defaultPlan === 'Premium Plan' ? 'checked' : '' ?> required>
                    <label for="business">
                        <h3 class="plan-label-name">Premium Plan</h3>
                        <span class="plan-label-price">$239.99 / Rp3.999.000</span>
                        <span class="plan-label-badge badge-business">Best Value</span>
                    </label>

                </div>

                <!-- Student Plan note -->
                <div class="landing-notice" id="landingNotice">
                    ⚠ Note: The Student Plan does not include free hosting or domain.
                    This package covers website design only. Perfect for students &amp; personal projects.
                </div>

                <!-- Promo checkbox -->
                <div class="promo-check mt-3" id="promoCheckWrap">
                    <input type="checkbox" id="free_promo" name="free_promo" value="1">
                    <label for="free_promo">🎉 Claim Free Hosting &amp; .COM Domain</label>
                </div>

                <!-- Domain / Hosting -->
                <div class="domain-hosting-wrap" id="domainHostingWrap">
                    <h4>Do you have a domain?</h4>
                    <div class="domain-container">
                        <input type="radio" name="domain"  id="domain-yes"  value="Yes" required checked>
                        <label for="domain-yes">Yes</label>
                        <input type="radio" name="domain"  id="domain-no"   value="No"  required>
                        <label for="domain-no">No</label>
                    </div>

                    <h4>Do you have hosting?</h4>
                    <div class="domain-container">
                        <input type="radio" name="hosting" id="hosting-yes" value="Yes" required checked>
                        <label for="hosting-yes">Yes</label>
                        <input type="radio" name="hosting" id="hosting-no"  value="No"  required>
                        <label for="hosting-no">No</label>
                    </div>
                </div>

            </div><!-- /package-details-container -->

            <!-- ── Add-ons ── -->
            <?php if (!empty($availableAddons)): ?>
            <div class="addons-section" id="addonsSection">
                <h2>Add-ons <small style="font-size:0.7rem;color:#475569;font-family:'JetBrains Mono',monospace;font-weight:400;">(Optional)</small></h2>
                <p class="addons-subtitle">// customize your order with extra features</p>

                <div class="addon-grid">
                    <?php foreach ($availableAddons as $addon): ?>
                    <?php
                        $priceLabel = 'Rp' . number_format($addon['price_idr'], 0, ',', '.') . ' / $' . number_format($addon['price_usd'], 2);
                        if ($addon['type'] === 'per_page') $priceLabel .= ' per page';
                        if ($addon['type'] === 'monthly')  $priceLabel .= '/month';
                    ?>
                    <div class="addon-item" id="addon-item-<?= $addon['id'] ?>">
                        <input type="checkbox"
                               name="addon_<?= $addon['id'] ?>"
                               id="addon_<?= $addon['id'] ?>"
                               value="1"
                               data-price="<?= $addon['price_idr'] ?>"
                               data-type="<?= $addon['type'] ?>"
                               data-id="<?= $addon['id'] ?>"
                               onchange="updateAddonTotal(this)">
                        <div class="addon-text">
                            <div class="addon-name"><?= htmlspecialchars($addon['name']) ?></div>
                            <div class="addon-desc"><?= htmlspecialchars($addon['description']) ?></div>
                            <div class="addon-price"><?= $priceLabel ?></div>
                            <?php if ($addon['type'] === 'per_page'): ?>
                            <div class="addon-qty-row" id="qty-row-<?= $addon['id'] ?>">
                                <label for="addon_qty_<?= $addon['id'] ?>">Pages:</label>
                                <input type="number"
                                       name="addon_qty_<?= $addon['id'] ?>"
                                       id="addon_qty_<?= $addon['id'] ?>"
                                       min="1" max="20" value="1"
                                       onchange="updateAddonTotal()">
                            </div>
                            <?php elseif ($addon['type'] === 'monthly'): ?>
                            <div class="addon-qty-row" id="qty-row-<?= $addon['id'] ?>">
                                <label for="addon_qty_<?= $addon['id'] ?>">Months:</label>
                                <input type="number"
                                       name="addon_qty_<?= $addon['id'] ?>"
                                       id="addon_qty_<?= $addon['id'] ?>"
                                       min="1" max="24" value="1"
                                       onchange="updateAddonTotal()">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="addons-total-row">
                    <span class="addons-total-label">Add-ons Total</span>
                    <span class="addons-total-value" id="addonsTotalDisplay">Rp0</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Additional Information ── -->
            <div class="additional-contaianer">
                <h2>Additional Information</h2>
                <textarea name="additional" id="additional" rows="5"
                          placeholder="Anything else you want to tell us? (Optional)"></textarea>
            </div>

            <!-- ── Submit ── -->
            <div class="submit-container d-flex justify-content-center align-items-center">
                <button type="submit" name="submit" class="btn btn-glow">Checkout</button>
            </div>

        </form>
    </div>
</div>

<script>
// ── Landing page UI toggle ─────────────────────────────────
const plans           = document.querySelectorAll('input[name="package"]');
const landingNotice   = document.getElementById('landingNotice');
const promoWrap       = document.getElementById('promoCheckWrap');
const domainHostingWrap = document.getElementById('domainHostingWrap');
const domainRadios    = document.querySelectorAll('input[name="domain"]');
const hostingRadios   = document.querySelectorAll('input[name="hosting"]');

function updateLandingUI() {
    const selected = document.querySelector('input[name="package"]:checked');
    if (!selected) return;
    const isLanding = selected.value === 'Student Plan';

    landingNotice.style.display       = isLanding ? 'block' : 'none';
    promoWrap.style.display           = isLanding ? 'none'  : '';
    domainHostingWrap.classList.toggle('hidden', isLanding);

    if (isLanding) {
        domainRadios.forEach(r  => r.required = false);
        hostingRadios.forEach(r => r.required = false);
    } else {
        domainRadios.forEach(r  => r.required = true);
        hostingRadios.forEach(r => r.required = true);
    }
}

plans.forEach(p => p.addEventListener('change', updateLandingUI));
updateLandingUI(); // run on page load

// ── Add-ons dynamic total ──────────────────────────────────
function formatRp(n) {
    return 'Rp' + n.toLocaleString('id-ID');
}

function updateAddonTotal(changedCheckbox) {
    // Toggle .selected class and qty row
    if (changedCheckbox) {
        const item  = document.getElementById('addon-item-' + changedCheckbox.dataset.id);
        const qtyRow = document.getElementById('qty-row-' + changedCheckbox.dataset.id);
        item.classList.toggle('selected', changedCheckbox.checked);
        if (qtyRow) qtyRow.classList.toggle('visible', changedCheckbox.checked);
    }

    let total = 0;
    document.querySelectorAll('.addon-item input[type="checkbox"]:checked').forEach(cb => {
        const price = parseInt(cb.dataset.price) || 0;
        let qty = 1;
        if (cb.dataset.type === 'per_page' || cb.dataset.type === 'monthly') {
            const qtyInput = document.getElementById('addon_qty_' + cb.dataset.id);
            qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;
        }
        total += price * qty;
    });

    document.getElementById('addonsTotalDisplay').textContent = formatRp(total);
}

// Hide add-ons for Student Plan (no complex add-ons needed)
// Actually keep them visible — Student Plan users can still add extras
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>
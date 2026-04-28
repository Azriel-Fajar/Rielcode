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
    $phone_number = trim($_POST['phone']       ?? '');
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
    $stmt->bind_param(
        "sssissss",
        $order_name,
        $email,
        $package,
        $package_id,
        $domain,
        $hosting,
        $phone_number,
        $description
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="w-full">
    <script>
        (function() {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        })();
    </script>

    <div class="background w-full">

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
                        <button type="submit" name="no" class="btn btn-glow">No</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <a href="../" class="btn">Back</a>

        <div class="form-container">
            <form method="post" id="orderForm">

                <!-- ── Title ── -->
                <div class="title flex justify-center items-center">
                    <h1 class="text-white">Order Form</h1>
                    <p>Fill out the form below to confirm your purchase</p>
                </div>

                <!-- ── Customer Information ── -->
                <div class="customer-info-container">
                    <h2>Customer Information</h2>
                    <input type="text" name="nama" id="nama" placeholder="Name" required>
                    <input type="email" name="email" id="email" placeholder="Email address" required>
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
                            <input type="radio" name="domain" id="domain-yes" value="Yes" required checked>
                            <label for="domain-yes">Yes</label>
                            <input type="radio" name="domain" id="domain-no" value="No" required>
                            <label for="domain-no">No</label>
                        </div>

                        <h4>Do you have hosting?</h4>
                        <div class="domain-container">
                            <input type="radio" name="hosting" id="hosting-yes" value="Yes" required checked>
                            <label for="hosting-yes">Yes</label>
                            <input type="radio" name="hosting" id="hosting-no" value="No" required>
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
                <div class="submit-container flex justify-center items-center">
                    <button type="submit" name="submit" class="btn btn-glow">Checkout</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        // ── Landing page UI toggle ─────────────────────────────────
        const plans = document.querySelectorAll('input[name="package"]');
        const landingNotice = document.getElementById('landingNotice');
        const promoWrap = document.getElementById('promoCheckWrap');
        const domainHostingWrap = document.getElementById('domainHostingWrap');
        const domainRadios = document.querySelectorAll('input[name="domain"]');
        const hostingRadios = document.querySelectorAll('input[name="hosting"]');

        function updateLandingUI() {
            const selected = document.querySelector('input[name="package"]:checked');
            if (!selected) return;
            const isLanding = selected.value === 'Student Plan';

            landingNotice.style.display = isLanding ? 'block' : 'none';
            promoWrap.style.display = isLanding ? 'none' : '';
            domainHostingWrap.classList.toggle('hidden', isLanding);

            if (isLanding) {
                domainRadios.forEach(r => r.required = false);
                hostingRadios.forEach(r => r.required = false);
            } else {
                domainRadios.forEach(r => r.required = true);
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
                const item = document.getElementById('addon-item-' + changedCheckbox.dataset.id);
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
    </script>

</body>

</html>
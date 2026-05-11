<?php
session_start();
include '../connection.php';

// ─── Handle: resume incomplete order ──────────────────────────────────────────
if (isset($_POST['continue'])) {
    header("Location: ../checkout/");
    exit;
}

if (isset($_POST['no'])) {
    if (isset($_SESSION['transaction'])) {
        $id = $_SESSION['transaction'];
        $stmtDel = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();
    }
    session_destroy();
    header("Location: ./");
    exit;
}

// ─── Handle: new order submission ─────────────────────────────────────────────
if (isset($_POST['submit'])) {
    $order_name   = trim($_POST['nama']       ?? '');
    $email        = trim($_POST['email']      ?? '');
    $phone_number = trim($_POST['phone']      ?? '');
    $domain       = $_POST['domain']          ?? 'No';
    $hosting      = $_POST['hosting']         ?? 'No';
    $description  = trim($_POST['additional'] ?? '');
    $package      = 'Custom Plan';
    $customTotal     = max(500000, (int)($_POST['custom_total']    ?? 500000));
    $customPreset    = $_POST['custom_preset']    ?? 'blank';
    $copySourceUrl   = trim($_POST['copy_source_url'] ?? '');
    $customConfigRaw = trim($_POST['custom_config'] ?? '');
    $customConfig    = ($customConfigRaw !== '' && $customConfigRaw !== 'null') ? $customConfigRaw : null;
    if (!in_array($customPreset, ['blank','copy','ecommerce'], true)) $customPreset = 'blank';

    $stmtPkg = $conn->prepare("SELECT id FROM packages WHERE package_name = ?");
    $stmtPkg->bind_param("s", $package);
    $stmtPkg->execute();
    $pkgRow     = $stmtPkg->get_result()->fetch_assoc();
    $package_id = (int)($pkgRow['id'] ?? 0);
    $stmtPkg->close();

    $stmt = $conn->prepare("
        INSERT INTO orders
            (order_name, email, package, package_id, custom_preset, copy_source_url, custom_config, owns_domain, owns_hosting, phone_number, description, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
    ");
    $stmt->bind_param(
        "sssisssssss",
        $order_name, $email, $package, $package_id,
        $customPreset, $copySourceUrl, $customConfig,
        $domain, $hosting, $phone_number, $description
    );
    $stmt->execute();
    $_SESSION['transaction']  = $conn->insert_id;
    $_SESSION['custom_total'] = $customTotal;
    $stmt->close();

    header("Location: ../checkout/");
    exit;
}

// ─── Preset detection (from URL ?preset=blank|copy|ecommerce) ────────────────
$activePreset = $_GET['preset'] ?? 'blank';
if (!in_array($activePreset, ['blank','copy','ecommerce'], true)) $activePreset = 'blank';

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
    <title>Custom Plan | Rielcode</title>
    <meta name="description" content="Build your own custom website plan with Rielcode. Choose exactly what you need — pages, chatbot, CMS, login system, maintenance, and more.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="https://rielcode.com/custom-plan/">
    <meta property="og:title" content="Custom Plan | Rielcode">
    <meta property="og:description" content="Build your own web plan — only pay for what you need.">
    <meta property="og:url" content="https://rielcode.com/custom-plan/">
    <meta property="og:image" content="https://rielcode.com/IMG/Rielcode Logo Square.png">
    <meta property="og:site_name" content="Rielcode">
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="../CSS/redesign.css">
    <link rel="stylesheet" href="../CSS/custom-plan.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="rc-redesign">
    <script>
        (function () {
            const clean = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, clean);
        })();
    </script>

    <div class="cp-page">

        <?php if ($incompleteOrder): ?>
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
                        <button type="submit" name="continue" class="cp-popup-btn">Yes</button>
                        <button type="submit" name="no" class="cp-popup-btn">No</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <a href="../" class="back-btn">Back</a>

        <div class="cp-wrapper">

            <div class="cp-header">
                <div><span class="rc-eyebrow">custom plan</span></div>
                <h1 class="rc-h1">Build Your Own Website</h1>
                <p class="rc-body-lg">Pick a starting point, then customize. Your price updates live as you configure.</p>
            </div>

            <!-- ── Preset picker ──────────────────────────────────────── -->
            <div class="cp-presets">
                <a href="?preset=blank" class="cp-preset-card <?= $activePreset === 'blank' ? 'is-active' : '' ?>" data-preset="blank">
                    <div class="cp-preset-card__icon"><i class="bi bi-file-earmark"></i></div>
                    <div class="cp-preset-card__name">Blank</div>
                    <div class="cp-preset-card__price">from Rp500.000</div>
                    <div class="cp-preset-card__desc">Start from scratch, full control.</div>
                </a>
                <a href="?preset=copy" class="cp-preset-card <?= $activePreset === 'copy' ? 'is-active' : '' ?>" data-preset="copy">
                    <div class="cp-preset-card__icon"><i class="bi bi-files"></i></div>
                    <div class="cp-preset-card__name">Copy Website</div>
                    <div class="cp-preset-card__price">Rp1.000.000 – Rp3.000.000</div>
                    <div class="cp-preset-card__desc">Match a reference site's layout, your brand. Final price confirmed after review.</div>
                </a>
                <a href="?preset=ecommerce" class="cp-preset-card <?= $activePreset === 'ecommerce' ? 'is-active' : '' ?>" data-preset="ecommerce">
                    <div class="cp-preset-card__icon"><i class="bi bi-shop"></i></div>
                    <div class="cp-preset-card__name">E-Commerce</div>
                    <div class="cp-preset-card__price">from Rp1.000.000</div>
                    <div class="cp-preset-card__desc">3 pages included. Add products, cart &amp; checkout as needed.</div>
                </a>
            </div>

            <style>
                .cp-presets { display:grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin: 8px 0 28px; }
                .cp-preset-card { display:flex; flex-direction:column; gap:6px; padding: 18px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.03); color: inherit; text-decoration: none; transition: all .2s; }
                .cp-preset-card:hover { border-color: rgba(58,123,255,0.45); background: rgba(58,123,255,0.06); transform: translateY(-2px); }
                .cp-preset-card.is-active { border-color: #3a7bff; background: linear-gradient(135deg, rgba(58,123,255,0.15), rgba(163,91,255,0.10)); box-shadow: 0 0 0 1px rgba(58,123,255,0.4), 0 10px 28px rgba(58,123,255,0.18); }
                .cp-preset-card__icon { font-size: 22px; color: #6fa3ff; }
                .cp-preset-card__name { font-weight: 700; font-size: 16px; color: #fff; }
                .cp-preset-card__price { font-family: var(--font-mono, monospace); font-size: 13px; color: #4ade80; }
                .cp-preset-card__desc { font-size: 13px; color: rgba(255,255,255,0.6); }
                @media (max-width: 640px) { .cp-presets { grid-template-columns: 1fr; } }

                .cp-copy-url-row { display:none; margin: 12px 0 12px; padding: 14px; border-radius: 12px; background: rgba(255,149,0,0.06); border: 1px solid rgba(255,149,0,0.20); }
                .cp-copy-url-row.is-visible { display: block; }
                .cp-copy-url-row label { display:block; font-size: 13px; color: #ff9500; font-weight: 600; margin-bottom: 6px; }
                .cp-copy-url-row input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.25); color: #fff; font-size: 14px; }
                .cp-copy-url-row .cp-copy-url-hint { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 6px; }
            </style>

            <div class="cp-layout">

                <!-- ── LEFT: Options ─────────────────────────────── -->
                <div class="cp-options">

                    <!-- Base Package Info -->
                    <div class="cp-section cp-base-info">
                        <div class="cp-section-title">
                            <i class="bi bi-box-seam"></i> Base Package — <span id="base-price-label">Rp500.000</span>
                        </div>
                        <div class="cp-base-list" id="base-items-list">
                            <!-- filled by JS based on preset -->
                        </div>
                    </div>

                    <!-- Pages -->
                    <div class="cp-section">
                        <div class="cp-section-title">
                            <i class="bi bi-file-earmark-code"></i> Number of Pages
                        </div>
                        <div class="cp-stepper-row">
                            <button class="cp-stepper-btn" onclick="cpStep('pages', -1)">−</button>
                            <span class="cp-stepper-val" id="val-pages">1</span>
                            <button class="cp-stepper-btn" onclick="cpStep('pages', 1)">+</button>
                            <span class="cp-stepper-label">page(s) — <span class="cp-unit-price" id="pages-unit-price">Rp150.000 / page</span></span>
                        </div>
                        <div class="cp-preset-btns">
                            <span class="cp-preset-label">Quick:</span>
                            <button type="button" class="cp-preset-btn active" data-pages="1" onclick="cpSetPages(1)">1</button>
                            <button type="button" class="cp-preset-btn" data-pages="3" onclick="cpSetPages(3)">3</button>
                            <button type="button" class="cp-preset-btn" data-pages="5" onclick="cpSetPages(5)">5</button>
                            <button type="button" class="cp-preset-btn" data-pages="10" onclick="cpSetPages(10)">10</button>
                            <button type="button" class="cp-preset-btn" data-pages="20" onclick="cpSetPages(20)">20</button>
                        </div>
                    </div>

                    <!-- Maintenance -->
                    <div class="cp-section">
                        <div class="cp-section-title">
                            <i class="bi bi-tools"></i> Maintenance Support
                        </div>
                        <div class="cp-stepper-row">
                            <button class="cp-stepper-btn" onclick="cpStep('maintenance', -1)">−</button>
                            <span class="cp-stepper-val" id="val-maintenance">0</span>
                            <button class="cp-stepper-btn" onclick="cpStep('maintenance', 1)">+</button>
                            <span class="cp-stepper-label">month(s) — <span class="cp-unit-price" id="maintenance-unit-price">Rp300.000 / month</span></span>
                        </div>
                    </div>

                    <!-- Toggle Features -->
                    <div class="cp-section">
                        <div class="cp-section-title">
                            <i class="bi bi-toggles"></i> Features
                        </div>
                        <div class="cp-toggles">

                            <label class="cp-toggle-item" data-feature="priority">
                                <input type="checkbox" id="feat-priority" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">Priority Delivery</div>
                                    <div class="cp-toggle-desc">50% faster turnaround</div>
                                    <div class="cp-toggle-price" id="priority-price-label">+ Rp400.000</div>
                                </div>
                            </label>

                            <label class="cp-toggle-item" data-feature="chatbot">
                                <input type="checkbox" id="feat-chatbot" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">AI Chatbot</div>
                                    <div class="cp-toggle-desc">AI-powered chat widget on your site</div>
                                    <div class="cp-toggle-price" id="chatbot-price-label">+ Rp1.500.000</div>
                                </div>
                            </label>

                            <!-- CMS with difficulty selector -->
                            <div class="cp-toggle-item cp-toggle-item--expandable" data-feature="cms" id="cms-toggle-item">
                                <input type="checkbox" id="feat-cms" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">CMS / Admin Panel</div>
                                    <div class="cp-toggle-desc">Manage your content from a dashboard</div>
                                    <div class="cp-toggle-price" id="cms-price-label">+ Rp800.000 – Rp1.600.000</div>
                                    <div class="cp-difficulty-row" id="cms-difficulty" style="display:none;">
                                        <span class="cp-difficulty-label">Difficulty:</span>
                                        <div class="cp-difficulty-options">
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="cms-diff" value="800000" checked onchange="cpCalc()">
                                                <span class="cp-diff-name">Basic</span>
                                                <span class="cp-diff-info">Text &amp; image content editor</span>
                                                <span class="cp-diff-price" data-cms-price="800000">Rp800.000</span>
                                            </label>
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="cms-diff" value="1200000" onchange="cpCalc()">
                                                <span class="cp-diff-name">Standard</span>
                                                <span class="cp-diff-info">+ Media library &amp; user roles</span>
                                                <span class="cp-diff-price" data-cms-price="1200000">Rp1.200.000</span>
                                            </label>
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="cms-diff" value="1600000" onchange="cpCalc()">
                                                <span class="cp-diff-name">Advanced</span>
                                                <span class="cp-diff-info">+ Custom modules &amp; API access</span>
                                                <span class="cp-diff-price" data-cms-price="1600000">Rp1.600.000</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <label class="cp-toggle-item" data-feature="login">
                                <input type="checkbox" id="feat-login" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">Login / Member System</div>
                                    <div class="cp-toggle-desc">User registration, login &amp; profile pages</div>
                                    <div class="cp-toggle-price" id="login-price-label">+ Rp500.000</div>
                                </div>
                            </label>

                            <!-- E-Commerce with difficulty selector -->
                            <div class="cp-toggle-item cp-toggle-item--expandable" data-feature="ecom" id="ecom-toggle-item">
                                <input type="checkbox" id="feat-ecom" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">E-Commerce</div>
                                    <div class="cp-toggle-desc">Product catalog, cart &amp; order management</div>
                                    <div class="cp-toggle-price" id="ecom-price-label">+ Rp1.000.000 – Rp3.000.000</div>
                                    <div class="cp-difficulty-row" id="ecom-difficulty" style="display:none;">
                                        <span class="cp-difficulty-label">Difficulty:</span>
                                        <div class="cp-difficulty-options">
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="ecom-diff" value="1000000" checked onchange="cpCalc()">
                                                <span class="cp-diff-name">Basic</span>
                                                <span class="cp-diff-info">Product catalog, cart &amp; checkout</span>
                                                <span class="cp-diff-price" data-ecom-price="1000000">Rp1.000.000</span>
                                            </label>
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="ecom-diff" value="2000000" onchange="cpCalc()">
                                                <span class="cp-diff-name">Standard</span>
                                                <span class="cp-diff-info">+ Payment gateway &amp; order tracking</span>
                                                <span class="cp-diff-price" data-ecom-price="2000000">Rp2.000.000</span>
                                            </label>
                                            <label class="cp-diff-opt">
                                                <input type="radio" name="ecom-diff" value="3000000" onchange="cpCalc()">
                                                <span class="cp-diff-name">Advanced</span>
                                                <span class="cp-diff-info">+ Multi-category, coupons &amp; analytics</span>
                                                <span class="cp-diff-price" data-ecom-price="3000000">Rp3.000.000</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <label class="cp-toggle-item" data-feature="seo">
                                <input type="checkbox" id="feat-seo" onchange="cpCalc()">
                                <div class="cp-toggle-body">
                                    <div class="cp-toggle-name">Advanced SEO</div>
                                    <div class="cp-toggle-desc">Full meta, schema markup &amp; sitemap setup</div>
                                    <div class="cp-toggle-price" id="seo-price-label">+ Rp300.000</div>
                                </div>
                            </label>

                        </div>
                    </div>

                    <!-- Hosting & Domain note -->
                    <div class="cp-hosting-note" id="hosting-note">
                        <i class="bi bi-globe"></i>
                        <div>
                            <strong>Free Hosting &amp; .COM Domain</strong>
                            <span id="hosting-status">Spend at least Rp2.000.000 to unlock this bonus.</span>
                        </div>
                        <span class="cp-hosting-badge" id="hosting-badge">Locked</span>
                    </div>

                </div>

                <!-- ── RIGHT: Live Summary ────────────────────────── -->
                <div class="cp-summary">
                    <div class="cp-summary-inner">
                        <div class="cp-summary-title">
                            Your Custom Plan
                            <button type="button" class="cp-currency-toggle" id="currencyToggle" onclick="toggleCurrency()">$ USD</button>
                        </div>

                        <div class="cp-summary-lines" id="summary-lines">
                            <!-- filled by JS -->
                        </div>

                        <div class="cp-summary-divider"></div>

                        <div class="cp-summary-total-row">
                            <span>Total</span>
                            <span class="cp-summary-total" id="summary-total">Rp500.000</span>
                        </div>
                        <div id="plan-cap-badge" style="display:none;margin-top:8px;margin-bottom:8px;padding:6px 10px;background:rgba(58,123,255,0.12);border:1px solid rgba(58,123,255,0.3);border-radius:6px;font-size:12px;color:#6fa3ff;line-height:1.4;"></div>

                        <div class="cp-hosting-unlock" id="hosting-unlock-bar">
                            <!-- shown when hosting is unlocked -->
                        </div>

                        <p class="cp-order-note">Summary of your order above — fill in your details below to proceed.</p>
                    </div>
                </div>

            </div>

            <!-- ── ORDER FORM ───────────────────────────────────── -->
            <div class="cp-order-form" id="cp-order-form">
                <div class="cp-form-header">
                    <div class="cp-tag">// Your Details</div>
                    <h2>Complete Your Order</h2>
                    <p>Your plan is locked in above — just fill in your contact info.</p>
                </div>

                <form method="post" id="customOrderForm">
                    <input type="hidden" name="package" value="Custom Plan">
                    <input type="hidden" name="custom_total" id="form-custom-total" value="500000">
                    <input type="hidden" name="custom_preset" id="form-custom-preset" value="<?= htmlspecialchars($activePreset) ?>">
                    <input type="hidden" name="custom_config" id="form-custom-config" value="">

                    <div class="cp-copy-url-row <?= $activePreset === 'copy' ? 'is-visible' : '' ?>" id="cp-copy-url-row">
                        <label for="cf-copy-url">Reference website URL <span style="color:#f87171">*</span></label>
                        <input type="url" name="copy_source_url" id="cf-copy-url" placeholder="https://example.com" <?= $activePreset === 'copy' ? 'required' : '' ?>>
                        <div class="cp-copy-url-hint">We'll match the layout of this site with your branding and content.</div>
                    </div>

                    <div class="cp-form-grid">
                        <div class="cp-form-group">
                            <label for="cf-nama">Name</label>
                            <input type="text" name="nama" id="cf-nama" placeholder="Your full name" required>
                        </div>
                        <div class="cp-form-group">
                            <label for="cf-email">Email</label>
                            <input type="email" name="email" id="cf-email" placeholder="your@email.com" required>
                        </div>
                        <div class="cp-form-group">
                            <label for="cf-phone">Phone / WhatsApp</label>
                            <input type="number" name="phone" id="cf-phone" placeholder="e.g. 081289328493" required>
                        </div>
                    </div>

                    <div class="cp-form-dh" id="cp-form-dh">
                        <div class="cp-form-dh-group">
                            <h4>Do you have a domain?</h4>
                            <div class="cp-dh-radios">
                                <input type="radio" name="domain" id="domain-yes" value="Yes" checked>
                                <label for="domain-yes">Yes</label>
                                <input type="radio" name="domain" id="domain-no" value="No">
                                <label for="domain-no">No</label>
                            </div>
                        </div>
                        <div class="cp-form-dh-group">
                            <h4>Do you have hosting?</h4>
                            <div class="cp-dh-radios">
                                <input type="radio" name="hosting" id="hosting-yes" value="Yes" checked>
                                <label for="hosting-yes">Yes</label>
                                <input type="radio" name="hosting" id="hosting-no" value="No">
                                <label for="hosting-no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="cp-form-group cp-form-group--full">
                        <label for="cf-additional">Additional Notes <span class="cp-form-optional">(optional)</span></label>
                        <textarea name="additional" id="cf-additional" rows="4" placeholder="Any extra requirements or details about your project?"></textarea>
                    </div>

                    <div class="cp-form-submit-row">
                        <div class="cp-form-total-preview">
                            Order total: <strong id="form-total-display">Rp500.000</strong>
                        </div>
                        <button type="submit" name="submit" class="cp-submit-btn">Proceed to Checkout →</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
    (function () {

        const ACTIVE_PRESET = <?= json_encode($activePreset) ?>;

        // Per-preset base price (IDR). USD computed via ceil(IDR / 17000).
        const PRESET_BASE = { blank: 500000, copy: 1000000, ecommerce: 1000000 };
        const BASE = PRESET_BASE[ACTIVE_PRESET];

        const PRICES = {
            pages:       85000,
            maintenance: 300000,
            priority:    400000,
            chatbot:     1500000,
            login:       550000,
            seo:         300000,
        };
        const USD_RATE = 17000;
        let currency = 'IDR';

        const state = { pages: 1, maintenance: 0 };
        const HOSTING_MIN = 1000000;

        function fmt(n) {
            if (currency === 'USD') {
                return '$' + Math.ceil(n / USD_RATE).toLocaleString('en-US');
            }
            return 'Rp' + n.toLocaleString('id-ID');
        }

        function fmtRange(min, max) {
            return fmt(min) + ' – ' + fmt(max);
        }

        function getCmsDiff() {
            const sel = document.querySelector('input[name="cms-diff"]:checked');
            return sel ? parseInt(sel.value) : 800000;
        }

        function getEcomDiff() {
            const sel = document.querySelector('input[name="ecom-diff"]:checked');
            return sel ? parseInt(sel.value) : 1000000;
        }

        function getDiffLabel(name) {
            const sel = document.querySelector('input[name="' + name + '"]:checked');
            if (!sel) return '';
            const nameEl = sel.closest('label').querySelector('.cp-diff-name');
            return nameEl ? nameEl.textContent.trim() : sel.closest('label').textContent.trim();
        }

        document.getElementById('feat-cms').addEventListener('change', function () {
            document.getElementById('cms-difficulty').style.display = this.checked ? 'flex' : 'none';
        });
        document.getElementById('feat-ecom').addEventListener('change', function () {
            document.getElementById('ecom-difficulty').style.display = this.checked ? 'flex' : 'none';
        });

        ['cms-toggle-item', 'ecom-toggle-item'].forEach(function (id) {
            const item = document.getElementById(id);
            const cb = item.querySelector('input[type="checkbox"]');
            item.addEventListener('click', function (e) {
                if (e.target.tagName === 'INPUT' && e.target.type === 'checkbox') return;
                if (e.target.closest('.cp-difficulty-row')) return;
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change'));
            });
        });

        window.cpStep = function (key, delta) {
            const mins = { pages: 1, maintenance: 0 };
            const maxs = { pages: 50, maintenance: 24 };
            state[key] = Math.max(mins[key], Math.min(maxs[key], state[key] + delta));
            document.getElementById('val-' + key).textContent = state[key];
            if (key === 'pages') updatePresetBtns();
            cpCalc();
        };

        window.cpSetPages = function (n) {
            state.pages = n;
            document.getElementById('val-pages').textContent = n;
            updatePresetBtns();
            cpCalc();
        };

        function updatePresetBtns() {
            document.querySelectorAll('.cp-preset-btn').forEach(function (btn) {
                btn.classList.toggle('active', parseInt(btn.dataset.pages) === state.pages);
            });
        }

        window.toggleCurrency = function () {
            currency = currency === 'IDR' ? 'USD' : 'IDR';
            const btn = document.getElementById('currencyToggle');
            btn.textContent = currency === 'IDR' ? '$ USD' : 'Rp IDR';
            cpCalc();
        };

        function updateStaticPrices() {
            const baseEl = document.getElementById('base-price-label');
            if (baseEl) baseEl.textContent = fmt(BASE);

            const pagesUnit = document.getElementById('pages-unit-price');
            if (pagesUnit) pagesUnit.textContent = fmt(PRICES.pages) + ' / page';

            const maintUnit = document.getElementById('maintenance-unit-price');
            if (maintUnit) maintUnit.textContent = fmt(PRICES.maintenance) + ' / month';

            const simpleIds = [
                { id: 'priority-price-label', price: PRICES.priority },
                { id: 'chatbot-price-label',  price: PRICES.chatbot  },
                { id: 'login-price-label',    price: PRICES.login    },
                { id: 'seo-price-label',      price: PRICES.seo      },
            ];
            simpleIds.forEach(function (item) {
                const el = document.getElementById(item.id);
                if (el) el.textContent = '+ ' + fmt(item.price);
            });

            const cmsLabel = document.getElementById('cms-price-label');
            if (cmsLabel) cmsLabel.textContent = '+ ' + fmtRange(800000, 1600000);

            const ecomLabel = document.getElementById('ecom-price-label');
            if (ecomLabel) ecomLabel.textContent = '+ ' + fmtRange(1000000, 3000000);

            document.querySelectorAll('[data-cms-price]').forEach(function (el) {
                el.textContent = fmt(parseInt(el.dataset.cmsPrice));
            });

            document.querySelectorAll('[data-ecom-price]').forEach(function (el) {
                el.textContent = fmt(parseInt(el.dataset.ecomPrice));
            });
        }

        const CAP_MAX_SAVE = 300000;

        function getStandardCap(rawTotal) {
            const chatbotCb = document.getElementById('feat-chatbot');
            const cmsCb2    = document.getElementById('feat-cms');
            const seoCb     = document.getElementById('feat-seo');
            const loginCb   = document.getElementById('feat-login');
            const ecomCb2   = document.getElementById('feat-ecom');
            const chatbot   = chatbotCb && chatbotCb.checked;
            const cms       = cmsCb2    && cmsCb2.checked;
            const login     = loginCb   && loginCb.checked;
            const ecom      = ecomCb2   && ecomCb2.checked;

            // Premium: 6-10 pages, chatbot, cms; no login, no ecom
            if (state.pages >= 6 && state.pages <= 10 && chatbot && cms && !login && !ecom) {
                const cap = { idr: 4999000, label: 'Premium Plan' };
                if (rawTotal - cap.idr > CAP_MAX_SAVE) return null;
                return cap;
            }
            // Pro: 2-5 pages, cms; no chatbot, no login, no ecom
            if (state.pages >= 2 && state.pages <= 5 && cms && !chatbot && !login && !ecom) {
                const cap = { idr: 2499000, label: 'Pro Plan' };
                if (rawTotal - cap.idr > CAP_MAX_SAVE) return null;
                return cap;
            }
            return null;
        }

        window.cpCalc = function () {
            let total = BASE;
            const lines = [];

            lines.push({ label: 'Base price', amount: BASE });

            if (state.pages > 1) {
                const extra = (state.pages - 1) * PRICES.pages;
                total += extra;
                lines.push({ label: state.pages + ' pages (' + (state.pages - 1) + ' extra)', amount: extra });
            } else {
                lines.push({ label: '1 page (included)', amount: 0 });
            }

            if (state.maintenance > 0) {
                const m = state.maintenance * PRICES.maintenance;
                total += m;
                lines.push({ label: state.maintenance + ' month(s) maintenance', amount: m });
            }

            const simpleFeats = [
                { id: 'priority', label: 'Priority delivery' },
                { id: 'chatbot',  label: 'AI Chatbot' },
                { id: 'login',    label: 'Login / Member system' },
                { id: 'seo',      label: 'Advanced SEO' },
            ];

            simpleFeats.forEach(function (f) {
                const cb = document.getElementById('feat-' + f.id);
                if (cb && cb.checked) {
                    total += PRICES[f.id];
                    lines.push({ label: f.label, amount: PRICES[f.id] });
                }
            });

            const cmsCb = document.getElementById('feat-cms');
            if (cmsCb && cmsCb.checked) {
                const price = getCmsDiff();
                total += price;
                lines.push({ label: 'CMS / Admin Panel (' + getDiffLabel('cms-diff') + ')', amount: price });
            }

            const ecomCb = document.getElementById('feat-ecom');
            if (ecomCb && ecomCb.checked) {
                const price = getEcomDiff();
                total += price;
                lines.push({ label: 'E-Commerce (' + getDiffLabel('ecom-diff') + ')', amount: price });
            }

            const container = document.getElementById('summary-lines');
            container.innerHTML = lines.map(function (l) {
                return '<div class="cp-line' + (l.amount === 0 ? ' cp-line-free' : '') + '">'
                     + '<span>' + l.label + '</span>'
                     + '<span>' + (l.amount === 0 ? 'Free' : fmt(l.amount)) + '</span>'
                     + '</div>';
            }).join('');

            const cap = getStandardCap(total);
            const effective = cap ? Math.min(total, cap.idr) : total;

            const capBadge = document.getElementById('plan-cap-badge');
            const isCopyPreset = ACTIVE_PRESET === 'copy';

            if (isCopyPreset) {
                document.getElementById('summary-total').textContent = 'Rp1.000.000 – Rp3.000.000';
                capBadge.style.display = 'block';
                capBadge.textContent = 'Final price confirmed privately after we review your reference site.';
            } else if (cap && effective < total) {
                document.getElementById('summary-total').innerHTML =
                    '<span style="text-decoration:line-through;opacity:0.45;font-size:0.8em;">' + fmt(total) + '</span> ' + fmt(effective);
                capBadge.style.display = 'block';
                capBadge.textContent = 'Best price applied — matches ' + cap.label + ' (save ' + fmt(total - effective) + ')';
            } else {
                document.getElementById('summary-total').textContent = fmt(effective);
                capBadge.style.display = 'none';
            }

            const unlocked = isCopyPreset ? true : effective >= HOSTING_MIN;
            const badge  = document.getElementById('hosting-badge');
            const status = document.getElementById('hosting-status');
            const note   = document.getElementById('hosting-note');
            const bar    = document.getElementById('hosting-unlock-bar');
            const dh     = document.getElementById('cp-form-dh');

            if (unlocked) {
                badge.textContent  = 'Unlocked ✓';
                badge.className    = 'cp-hosting-badge unlocked';
                status.textContent = 'Free hosting & .COM domain included!';
                note.className     = 'cp-hosting-note unlocked';
                bar.innerHTML      = '<span class="cp-unlock-pill">🎉 Free Hosting &amp; .COM Domain included</span>';
                if (dh) dh.style.display = 'none';
            } else {
                const needed       = HOSTING_MIN - effective;
                badge.textContent  = 'Locked';
                badge.className    = 'cp-hosting-badge';
                status.textContent = 'Add ' + fmt(needed) + ' more to unlock free hosting & domain.';
                note.className     = 'cp-hosting-note';
                bar.innerHTML      = '';
                if (dh) dh.style.display = 'flex';
            }

            const allFeatures = [];
            simpleFeats.forEach(function (f) {
                const cb = document.getElementById('feat-' + f.id);
                if (cb && cb.checked) allFeatures.push(f.label);
            });
            if (cmsCb && cmsCb.checked) allFeatures.push('CMS / Admin Panel (' + getDiffLabel('cms-diff') + ')');
            if (ecomCb && ecomCb.checked) allFeatures.push('E-Commerce (' + getDiffLabel('ecom-diff') + ')');

            const desc = 'Custom Plan | Pages: ' + state.pages
                + ' | Maintenance: ' + state.maintenance + ' mo'
                + (allFeatures.length ? ' | Features: ' + allFeatures.join(', ') : '')
                + (unlocked ? ' | FREE Hosting & Domain' : '');

            // Sync to form hidden fields
            const formEffective = isCopyPreset ? 1000000 : effective;
            const hiddenTotal = document.getElementById('form-custom-total');
            if (hiddenTotal) hiddenTotal.value = formEffective;

            const configField = document.getElementById('form-custom-config');
            if (configField) {
                const cfg = {
                    pages:       state.pages,
                    maintenance: state.maintenance,
                    features:    allFeatures,
                };
                configField.value = JSON.stringify(cfg);
            }

            const totalDisplay = document.getElementById('form-total-display');
            if (totalDisplay) totalDisplay.textContent = isCopyPreset ? 'Rp1.000.000 – Rp3.000.000' : fmt(effective);

            const descField = document.getElementById('cf-additional');
            if (descField && !descField.dataset.userEdited) descField.placeholder = desc;

            const allIds = ['priority', 'chatbot', 'cms', 'login', 'ecom', 'seo'];
            allIds.forEach(function (id) {
                const cb   = document.getElementById('feat-' + id);
                const item = cb ? cb.closest('.cp-toggle-item') : null;
                if (item) item.classList.toggle('active', cb.checked);
            });

            updateStaticPrices();
        };

        window.scrollToOrderForm = function () {
            const form = document.getElementById('cp-order-form');
            if (form) {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(function () {
                    const first = form.querySelector('input[name="nama"]');
                    if (first) first.focus();
                }, 600);
            }
        };

        // Mark textarea as user-edited once they type
        const descField = document.getElementById('cf-additional');
        if (descField) {
            descField.addEventListener('input', function () {
                this.dataset.userEdited = '1';
            });
        }

        // ─── Apply preset defaults ────────────────────────────────────
        function applyPreset(preset) {
            if (preset === 'copy') {
                state.pages = 3;
                document.getElementById('val-pages').textContent = 3;
                updatePresetBtns();
                // expose URL field already handled server-side
            } else if (preset === 'ecommerce') {
                state.pages = 3;
                document.getElementById('val-pages').textContent = 3;
                updatePresetBtns();
            }
        }
        applyPreset(ACTIVE_PRESET);

        (function renderBaseItems() {
            const items = {
                blank: [
                    '1 custom-designed page',
                    'Mobile responsive layout',
                    'Contact form',
                    'Basic SEO setup',
                ],
                copy: [
                    'Layout matched to your reference site',
                    'Your branding, colors &amp; content applied',
                    'Mobile responsive layout',
                    'Contact form &amp; basic SEO setup',
                ],
                ecommerce: [
                    '3 custom-designed pages',
                    'Mobile responsive layout',
                    'Contact form &amp; basic SEO setup',
                    'Ready to add products, cart &amp; checkout',
                ],
            };
            const list = document.getElementById('base-items-list');
            if (!list) return;
            list.innerHTML = (items[ACTIVE_PRESET] || items.blank).map(function (t) {
                return '<span class="cp-base-item"><i class="bi bi-check2"></i> ' + t + '</span>';
            }).join('');
        })();

        cpCalc();
        updatePresetBtns();

    })();
    </script>

</body>
</html>

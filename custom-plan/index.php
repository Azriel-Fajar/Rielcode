<?php
session_start();
include '../connection.php';
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
    <link rel="stylesheet" href="../CSS/custom-plan.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <script>
        (function () {
            const clean = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, clean);
        })();
    </script>

    <a href="../" class="back-btn"><i class="bi bi-arrow-left"></i> Back</a>

    <div class="cp-wrapper">

        <div class="cp-header">
            <div class="cp-tag">// Custom Plan</div>
            <h1>Build Your Own Website</h1>
            <p>Pick exactly what you need — your price updates live as you configure.</p>
        </div>

        <div class="cp-layout">

            <!-- ── LEFT: Options ─────────────────────────────── -->
            <div class="cp-options">

                <!-- Pages -->
                <div class="cp-section">
                    <div class="cp-section-title">
                        <i class="bi bi-file-earmark-code"></i> Number of Pages
                    </div>
                    <div class="cp-stepper-row">
                        <button class="cp-stepper-btn" onclick="cpStep('pages', -1)">−</button>
                        <span class="cp-stepper-val" id="val-pages">1</span>
                        <button class="cp-stepper-btn" onclick="cpStep('pages', 1)">+</button>
                        <span class="cp-stepper-label">page(s) — <span class="cp-unit-price">Rp150.000 / page</span></span>
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
                        <span class="cp-stepper-label">month(s) — <span class="cp-unit-price">Rp300.000 / month</span></span>
                    </div>
                </div>

                <!-- Revisions -->
                <div class="cp-section">
                    <div class="cp-section-title">
                        <i class="bi bi-arrow-repeat"></i> Design Revisions
                    </div>
                    <div class="cp-stepper-row">
                        <button class="cp-stepper-btn" onclick="cpStep('revisions', -1)">−</button>
                        <span class="cp-stepper-val" id="val-revisions">1</span>
                        <button class="cp-stepper-btn" onclick="cpStep('revisions', 1)">+</button>
                        <span class="cp-stepper-label">round(s) — <span class="cp-unit-price">Rp200.000 / round</span></span>
                    </div>
                    <p class="cp-note">First revision is included in the base price.</p>
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
                                <div class="cp-toggle-price">+ Rp400.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="chatbot">
                            <input type="checkbox" id="feat-chatbot" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">AI Chatbot</div>
                                <div class="cp-toggle-desc">GPT-powered chat widget on your site</div>
                                <div class="cp-toggle-price">+ Rp500.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="cms">
                            <input type="checkbox" id="feat-cms" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">CMS / Admin Panel</div>
                                <div class="cp-toggle-desc">Manage your content from a dashboard</div>
                                <div class="cp-toggle-price">+ Rp600.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="login">
                            <input type="checkbox" id="feat-login" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">Login / Member System</div>
                                <div class="cp-toggle-desc">User registration, login &amp; profile pages</div>
                                <div class="cp-toggle-price">+ Rp500.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="ecom">
                            <input type="checkbox" id="feat-ecom" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">E-Commerce</div>
                                <div class="cp-toggle-desc">Product catalog, cart &amp; order management</div>
                                <div class="cp-toggle-price">+ Rp1.500.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="seo">
                            <input type="checkbox" id="feat-seo" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">Advanced SEO</div>
                                <div class="cp-toggle-desc">Full meta, schema markup &amp; sitemap setup</div>
                                <div class="cp-toggle-price">+ Rp300.000</div>
                            </div>
                        </label>

                        <label class="cp-toggle-item" data-feature="ui">
                            <input type="checkbox" id="feat-ui" onchange="cpCalc()">
                            <div class="cp-toggle-body">
                                <div class="cp-toggle-name">Custom UI/UX Design</div>
                                <div class="cp-toggle-desc">Tailored design mockups before development</div>
                                <div class="cp-toggle-price">+ Rp400.000</div>
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
                    <div class="cp-summary-title">Your Custom Plan</div>

                    <div class="cp-summary-lines" id="summary-lines">
                        <!-- filled by JS -->
                    </div>

                    <div class="cp-summary-divider"></div>

                    <div class="cp-summary-total-row">
                        <span>Total</span>
                        <span class="cp-summary-total" id="summary-total">Rp1.000.000</span>
                    </div>

                    <div class="cp-hosting-unlock" id="hosting-unlock-bar">
                        <!-- shown when hosting is unlocked -->
                    </div>

                    <a href="#" class="cp-order-btn" id="cp-order-btn">Order This Plan →</a>
                    <p class="cp-order-note">You'll complete your order details on the next step.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function () {

        const BASE = 1000000;

        const PRICES = {
            pages:       150000,
            maintenance: 300000,
            revisions:   200000,
            priority:    400000,
            chatbot:     500000,
            cms:         600000,
            login:       500000,
            ecom:        1500000,
            seo:         300000,
            ui:          400000,
        };

        const state = {
            pages:       1,
            maintenance: 0,
            revisions:   1,
        };

        const HOSTING_MIN = 2000000;

        function fmt(n) {
            return 'Rp' + n.toLocaleString('id-ID');
        }

        window.cpStep = function (key, delta) {
            const mins = { pages: 1, maintenance: 0, revisions: 1 };
            const maxs = { pages: 50, maintenance: 24, revisions: 20 };
            state[key] = Math.max(mins[key], Math.min(maxs[key], state[key] + delta));
            document.getElementById('val-' + key).textContent = state[key];
            cpCalc();
        };

        window.cpCalc = function () {
            let total = BASE;
            const lines = [];

            lines.push({ label: 'Base price', amount: BASE });

            // Pages (first page included in base)
            if (state.pages > 1) {
                const extra = (state.pages - 1) * PRICES.pages;
                total += extra;
                lines.push({ label: state.pages + ' pages (' + (state.pages - 1) + ' extra)', amount: extra });
            } else {
                lines.push({ label: '1 page (included)', amount: 0 });
            }

            // Maintenance
            if (state.maintenance > 0) {
                const m = state.maintenance * PRICES.maintenance;
                total += m;
                lines.push({ label: state.maintenance + ' month(s) maintenance', amount: m });
            }

            // Revisions (first round included)
            if (state.revisions > 1) {
                const r = (state.revisions - 1) * PRICES.revisions;
                total += r;
                lines.push({ label: state.revisions + ' revisions (' + (state.revisions - 1) + ' extra)', amount: r });
            } else {
                lines.push({ label: '1 revision (included)', amount: 0 });
            }

            // Toggle features
            const feats = [
                { id: 'priority', label: 'Priority delivery' },
                { id: 'chatbot',  label: 'AI Chatbot' },
                { id: 'cms',      label: 'CMS / Admin Panel' },
                { id: 'login',    label: 'Login / Member system' },
                { id: 'ecom',     label: 'E-Commerce' },
                { id: 'seo',      label: 'Advanced SEO' },
                { id: 'ui',       label: 'Custom UI/UX design' },
            ];

            feats.forEach(function (f) {
                const cb = document.getElementById('feat-' + f.id);
                if (cb && cb.checked) {
                    total += PRICES[f.id];
                    lines.push({ label: f.label, amount: PRICES[f.id] });
                }
            });

            // Render summary lines
            const container = document.getElementById('summary-lines');
            container.innerHTML = lines.map(function (l) {
                return '<div class="cp-line' + (l.amount === 0 ? ' cp-line-free' : '') + '">'
                     + '<span>' + l.label + '</span>'
                     + '<span>' + (l.amount === 0 ? 'Free' : fmt(l.amount)) + '</span>'
                     + '</div>';
            }).join('');

            document.getElementById('summary-total').textContent = fmt(total);

            // Hosting unlock
            const unlocked = total >= HOSTING_MIN;
            const badge = document.getElementById('hosting-badge');
            const status = document.getElementById('hosting-status');
            const note = document.getElementById('hosting-note');
            const bar = document.getElementById('hosting-unlock-bar');

            if (unlocked) {
                badge.textContent = 'Unlocked ✓';
                badge.className = 'cp-hosting-badge unlocked';
                status.textContent = 'Free hosting & .COM domain included!';
                note.className = 'cp-hosting-note unlocked';
                bar.innerHTML = '<span class="cp-unlock-pill">🎉 Free Hosting &amp; .COM Domain included</span>';
            } else {
                const needed = HOSTING_MIN - total;
                badge.textContent = 'Locked';
                badge.className = 'cp-hosting-badge';
                status.textContent = 'Add ' + fmt(needed) + ' more to unlock free hosting & domain.';
                note.className = 'cp-hosting-note';
                bar.innerHTML = '';
            }

            // Update order button href — pass total as query param for order-form awareness
            const features = feats
                .filter(function (f) { return document.getElementById('feat-' + f.id) && document.getElementById('feat-' + f.id).checked; })
                .map(function (f) { return f.label; })
                .join(', ');

            const desc = 'Custom Plan | Pages: ' + state.pages
                + ' | Maintenance: ' + state.maintenance + ' mo'
                + ' | Revisions: ' + state.revisions
                + (features ? ' | Features: ' + features : '')
                + (unlocked ? ' | FREE Hosting & Domain' : '');

            const btn = document.getElementById('cp-order-btn');
            btn.href = '../order-form/?aksi=custom&total=' + total + '&desc=' + encodeURIComponent(desc);

            // Highlight toggle items
            feats.forEach(function (f) {
                const cb = document.getElementById('feat-' + f.id);
                const item = cb ? cb.closest('.cp-toggle-item') : null;
                if (item) item.classList.toggle('active', cb.checked);
            });
        };

        cpCalc();

    })();
    </script>

</body>
</html>

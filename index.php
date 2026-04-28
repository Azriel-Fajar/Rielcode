<?php
session_start();
$meta_title       = "Rielcode | Crafting Modern Web Experiences";
$meta_description = "Rielcode is a modern web development studio that creates digital experiences for businesses, startups, and creators.";
$meta_keywords    = "web development, website Surakarta, desain web, rielcode, jasa website";
$meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
$meta_type        = "website";
$meta_url         = "https://rielcode.com/";
include 'inc/seo.php';
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rielcode — Modern Web Development Studio | Real Code. Real Results.</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/navbar.css">
    <link rel="stylesheet" href="CSS/package.css">
    <link rel="stylesheet" href="CSS/about.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="CSS/projects.css">
    <link rel="stylesheet" href="CSS/promo.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="IMG/Rielcode Logo Square Transparent Icon.png">

    <!-- Third-party CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- defer main JS -->
    <script defer src="JS/main.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
</head>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-B24L86THGN"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-B24L86THGN');
</script>

<!-- Strip query string from URL bar (no reload) -->
<script>
    (function () {
        var clean = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, clean);
    })();
</script>

<body class="w-full m-0 p-0">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero -->
    <section class="hero" id="hero" data-bg-image="IMG/bg.jpg">
        <div class="hero-container">
            <div class="text-container text-center">
                <h1 class="text-4xl lg:text-5xl font-bold">Real Code. Real Results.</h1>
                <p>Clean design, smart code, and real results — that's the Rielcode way.</p>
            </div>
            <div class="cta-container text-center">
                <a href="order-form/" class="btn btn-glow m-2">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Packages -->
    <?php include 'package.php'; ?>

    <!-- ═══════════════════════════════════════════
         FEATURE 4 — AI Price Estimator
    ════════════════════════════════════════════ -->
    <section class="estimator-section" id="estimator">
        <div class="section-header">
            <div class="section-tag">// Plan Finder</div>
            <h2 class="section-title">Find Your Perfect Plan</h2>
            <p class="section-desc">Drag the budget slider and select the features you need — AI will instantly recommend the best plan in real time.</p>
        </div>

        <div class="estimator-card">
            <div class="estimator-grid">

                <!-- Controls -->
                <div class="estimator-controls">
                    <div>
                        <div class="est-label">Your Budget (IDR)</div>
                        <div class="est-value" id="budget-label">IDR 1.500.000</div>
                        <input class="est-slider" type="range" id="budget-slider"
                               min="400000" max="4500000" step="100000" value="1500000"
                               oninput="rielEstimatorUpdate()">
                        <div class="est-slider-labels">
                            <span>IDR 400k</span><span>IDR 4.5jt</span>
                        </div>
                    </div>

                    <div>
                        <div class="est-label">Number of Pages</div>
                        <div class="est-chips" id="pages-chips">
                            <div class="est-chip on" data-group="pages" data-val="1" onclick="rielSelectChip(this)">1 page</div>
                            <div class="est-chip" data-group="pages" data-val="5" onclick="rielSelectChip(this)">2–5 pages</div>
                            <div class="est-chip" data-group="pages" data-val="10" onclick="rielSelectChip(this)">6–10 pages</div>
                        </div>
                    </div>

                    <div>
                        <div class="est-label">Features You Need</div>
                        <div class="est-chips">
                            <div class="est-chip" data-feature="cms" onclick="rielToggleFeature(this)">Admin Panel (CMS)</div>
                            <div class="est-chip" data-feature="ecom" onclick="rielToggleFeature(this)">E-Commerce</div>
                            <div class="est-chip" data-feature="seo" onclick="rielToggleFeature(this)">Advanced SEO</div>
                            <div class="est-chip" data-feature="support" onclick="rielToggleFeature(this)">Technical Support</div>
                        </div>
                    </div>
                </div>

                <!-- Result -->
                <div class="estimator-result">
                    <div class="est-rec-label">⬡ &nbsp;AI Recommendation</div>
                    <div class="est-plan-card pro" id="est-card">
                        <div class="est-plan-name" id="est-name">Pro Plan</div>
                        <div class="est-plan-price-row">
                            <span class="est-old" id="est-old">IDR 4jt</span>
                            <span class="est-new" id="est-new">IDR 1.999jt</span>
                        </div>
                        <div class="est-plan-why" id="est-why">Ideal for corporate websites with a few pages and basic CMS needs.</div>
                        <div class="est-features" id="est-feats">
                            <div class="est-feat">Up to 5 pages</div>
                            <div class="est-feat">Custom UI/UX design</div>
                            <div class="est-feat">CMS (Admin Panel)</div>
                            <div class="est-feat">Advanced SEO</div>
                        </div>
                        <a class="est-cta" id="est-cta" href="order-form?aksi=pro">Choose This Plan</a>
                    </div>
                    <div class="est-ai-note">Automated estimate based on your preferences</div>
                </div>

            </div>
        </div>
    </section>

    <!-- About -->
    <?php include 'about.php'; ?>

    <!-- Projects -->
    <?php include 'projects.php'; ?>

    <!-- Requirements -->
    <?php include 'requirement.php'; ?>

    <!-- Footer / Contact -->
    <?php include 'footer.php'; ?>

    <!-- Sticky Promo Bar -->
    <div class="promo-bar">
        🎉 <span>Rielcode</span>| Get FREE hosting &amp; .COM domain for your brand!
        <a href="order-form/" class="promo-btn">Claim Your Free Bonus 🚀</a>
    </div>
    <div class="promo-bar mobile">
        <a href="order-form/" class="promo-btn">🎉 Free Hosting &amp; .COM 🚀</a>
    </div>

    <!-- Third-party JS -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="JS/chatbot.js"></script>

    <!-- ═══════════════════════════════════════════
         FEATURE 4 — AI Price Estimator Logic (pure JS, no API call)
    ════════════════════════════════════════════ -->
    <script>
    (function () {
        const plans = {
            student: {
                cls: "student-plan",
                name: "Student Plan",
                old_idr: "IDR 998k",
                new_idr: "IDR 499k",
                why: "Cocok untuk portfolio atau tugas kuliah dengan budget minimal. Tanpa hosting & domain.",
                feats: ["1 halaman (single page)", "Responsive design", "Basic SEO setup", "WhatsApp integration"],
                href: "order-form?aksi=landing"
            },
            starter: {
                cls: "starter",
                name: "Starter Plan",
                old_idr: "IDR 2jt",
                new_idr: "IDR 999k",
                why: "Sempurna untuk landing page profesional dengan desain modern dan free hosting+domain.",
                feats: ["1 halaman responsive", "Free hosting & domain", "Basic SEO", "2x revisi"],
                href: "order-form?aksi=starter"
            },
            pro: {
                cls: "pro",
                name: "Pro Plan",
                old_idr: "IDR 4jt",
                new_idr: "IDR 1.999jt",
                why: "Pilihan terbaik untuk website perusahaan — CMS, advanced SEO, dan support 3 bulan.",
                feats: ["Up to 5 halaman", "CMS / Admin Panel", "Advanced SEO", "3 bulan technical support"],
                href: "order-form?aksi=pro"
            },
            premium: {
                cls: "premium",
                name: "Premium Plan",
                old_idr: "IDR 8jt",
                new_idr: "IDR 3.999jt",
                why: "Solusi lengkap dengan e-commerce, unlimited revisi, dan support 6 bulan.",
                feats: ["Up to 10 halaman + E-commerce", "Custom UI/UX", "Complete SEO", "6 bulan support + unlimited revisi"],
                href: "order-form?aksi=business"
            }
        };

        let selectedPages = "1";
        let selectedFeatures = new Set();

        window.rielSelectChip = function (el) {
            const group = el.dataset.group;
            document.querySelectorAll('[data-group="' + group + '"]').forEach(c => c.classList.remove("on"));
            el.classList.add("on");
            selectedPages = el.dataset.val;
            rielEstimatorUpdate();
        };

        window.rielToggleFeature = function (el) {
            const f = el.dataset.feature;
            if (selectedFeatures.has(f)) {
                selectedFeatures.delete(f);
                el.classList.remove("on");
            } else {
                selectedFeatures.add(f);
                el.classList.add("on");
            }
            rielEstimatorUpdate();
        };

        window.rielEstimatorUpdate = function () {
            const budget = parseInt(document.getElementById("budget-slider").value);
            const budgetLabel = budget >= 1000000
                ? "IDR " + (budget / 1000000).toFixed(1).replace(".0", "") + "jt"
                : "IDR " + (budget / 1000).toFixed(0) + "k";
            document.getElementById("budget-label").textContent = budgetLabel;

            const hasEcom = selectedFeatures.has("ecom");
            const hasCms  = selectedFeatures.has("cms");
            const pages   = parseInt(selectedPages);

            let rec = "student";
            if      (hasEcom || pages >= 10 || budget >= 3500000) rec = "premium";
            else if (hasCms  || pages >= 5  || budget >= 1500000) rec = "pro";
            else if (budget >= 800000 || pages >= 2)               rec = "starter";

            const p    = plans[rec];
            const card = document.getElementById("est-card");

            card.className = "est-plan-card " + p.cls;
            document.getElementById("est-name").textContent = p.name;
            document.getElementById("est-old").textContent  = p.old_idr;
            document.getElementById("est-new").textContent  = p.new_idr;
            document.getElementById("est-why").textContent  = p.why;
            document.getElementById("est-feats").innerHTML  = p.feats.map(f => '<div class="est-feat">' + f + '</div>').join("");
            const cta = document.getElementById("est-cta");
            cta.href      = p.href;
            cta.className = "est-cta";
        };

        // Init on load
        rielEstimatorUpdate();
    })();
    </script>
</body>
</html>
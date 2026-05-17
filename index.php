<?php
session_start();
$meta_title       = "Rielcode | Web Development Studio — Real Code. Real Results.";
$meta_description = "Rielcode builds modern, high-performance websites for businesses, startups, and creators in Indonesia. Clean design, smart code, real results. Packages from Rp499k.";
$meta_keywords    = "jasa pembuatan website, web development Indonesia, web design agency, website murah Indonesia, Rielcode";
$meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
$meta_type        = "website";
$meta_url         = "https://rielcode.com/";
$canonical        = "https://rielcode.com/";
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'inc/seo.php'; ?>

    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/redesign.css">
    <link rel="stylesheet" href="CSS/testimonials.css">

    <link rel="icon" type="image/png" sizes="32x32" href="IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="IMG/Rielcode Logo Square Transparent Icon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B24L86THGN"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-B24L86THGN');
    </script>

    <script>
        (function () {
            var clean = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, clean);
        })();
    </script>

    <link rel="stylesheet" href="CSS/tailwind.css">
    <script defer src="JS/main.js"></script>
    <script defer src="JS/redesign.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
</head>

<body class="w-full m-0 p-0 rc-redesign">

    <?php include 'navbar.php'; ?>

    <section class="rc-hero" id="hero">
        <div class="rc-container rc-hero__grid">
            <div class="rc-hero__copy">
                <span class="rc-eyebrow">real code, real results</span>
                <h1 class="rc-hero__title">
                    Real Code.<br>
                    <span class="rc-hero__accent">Real Results.</span>
                </h1>
                <p class="rc-hero__sub">
                    Clean design, smart code, and real results — that's the Rielcode way.
                    We build websites for businesses, startups, and creators who want craftsmanship, not templates.
                </p>
                <div class="rc-hero__ctas">
                    <a class="rc-btn" href="order-form/">
                        Get Started
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a class="rc-btn rc-btn-outline" href="#packages">View Packages</a>
                </div>
            </div>
            <div class="rc-hero__art">
                <div class="rc-window rc-hero__art-inner">
                    <div class="rc-window__bar">
                        <span class="rc-window__dot" style="background:#ff5f56"></span>
                        <span class="rc-window__dot" style="background:#ffbd2e"></span>
                        <span class="rc-window__dot" style="background:#27c93f"></span>
                        <span class="rc-window__title">~/rielcode/your-brand</span>
                    </div>
                    <pre class="rc-window__body"><code id="rc-term-code"></code></pre>
                </div>
                <div class="rc-hero__glow"></div>
            </div>
        </div>
    </section>

    <?php include 'package.php'; ?>

    <?php include 'about.php'; ?>

    <?php include 'projects.php'; ?>

    <?php include 'testimonials.php'; ?>

    <?php include 'requirement.php'; ?>

    <?php include 'footer.php'; ?>

    <div class="rc-promo rc-promo--bottom-bar" id="rc-promo" role="region" aria-label="Promotional offer">
        <div class="rc-promo__inner">
            <span class="rc-promo__icon" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </span>
            <div class="rc-promo__copy">
                <span class="rc-promo__full"><strong>Rielcode</strong> &mdash; Get FREE hosting &amp; .COM domain for your brand</span>
                <span class="rc-promo__compact"><strong>Free Hosting &amp; .COM</strong></span>
            </div>
            <a class="rc-promo__cta" href="order-form/">
                <span class="rc-promo__full">Claim Your Free Bonus</span>
                <span class="rc-promo__compact">Claim</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <button class="rc-promo__close" id="rc-promo-close" aria-label="Dismiss promotion" type="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/></svg>
            </button>
        </div>
    </div>

    <script defer src="JS/chatbot.js"></script>

</body>
</html>

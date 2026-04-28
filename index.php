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
    <link rel="stylesheet" href="CSS/navbar.css">
    <link rel="stylesheet" href="CSS/package.css">
    <link rel="stylesheet" href="CSS/about.css">
    <link rel="stylesheet" href="CSS/footer.css">
    <link rel="stylesheet" href="CSS/projects.css">
    <link rel="stylesheet" href="CSS/promo.css">
    <link rel="stylesheet" href="CSS/requirement.css">

    <link rel="icon" type="image/png" sizes="32x32" href="IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="IMG/Rielcode Logo Square Transparent Icon.png">

    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
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

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="JS/main.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
</head>

<body class="w-full m-0 p-0">

    <?php include 'navbar.php'; ?>

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

    <?php include 'package.php'; ?>

    <?php include 'about.php'; ?>

    <?php include 'projects.php'; ?>

    <?php include 'requirement.php'; ?>

    <?php include 'footer.php'; ?>

    <div class="promo-bar">
        🎉 <span>Rielcode</span>| Get FREE hosting &amp; .COM domain for your brand!
        <a href="order-form/" class="promo-btn">Claim Your Free Bonus 🚀</a>
    </div>
    <div class="promo-bar mobile">
        <a href="order-form/" class="promo-btn">🎉 Free Hosting &amp; .COM 🚀</a>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="JS/chatbot.js"></script>

</body>
</html>

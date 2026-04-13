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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">

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
        var clean = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, clean);
    })();
</script>

<body class="w-100 m-0 p-0">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero -->
    <section class="hero" id="hero" data-bg-image="IMG/bg.jpg">
        <div class="hero-container">
            <div class="text-container text-center">
                <h1 class="fs-1 fw-bold">Real Code. Real Results.</h1>
                <p>Clean design, smart code, and real results — that's the Rielcode way.</p>
            </div>
            <div class="cta-container text-center">
                <a href="order-form/" class="btn btn-glow m-2">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Packages -->
    <?php include 'package.php'; ?>

    <!-- About -->
    <?php include 'about.php'; ?>

    <!-- Projects -->
    <?php include 'projects.php'; ?>

    <!-- Requirements (new) -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="JS/chatbot.js"></script>
</body>
</html>
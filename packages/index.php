<?php
session_start();
$meta_title       = "All Packages — Rielcode Web Development Plans";
$meta_description = "Browse all Rielcode web development packages: Student, Starter, Pro, Premium, and Custom (with Copy Website and E-commerce presets). Transparent pricing in USD and IDR.";
$meta_keywords    = "web development packages Indonesia, harga website, paket website murah, Rielcode pricing";
$meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
$meta_type        = "website";
$meta_url         = "https://rielcode.com/packages/";
$canonical        = "https://rielcode.com/packages/";
include '../connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../inc/seo.php'; ?>

    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/redesign.css">

    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../IMG/Rielcode Logo Square Transparent Icon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script defer src="../JS/main.js"></script>
    <script defer src="../JS/redesign.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>

    <style>
        /* ── Page-level overrides ── */
        .pkg-hero { padding: 96px 0 64px; text-align: center; }
        .pkg-hero__eyebrow { margin-bottom: 12px; }
        .pkg-hero__title { margin: 0 auto 16px; max-width: 680px; }
        .pkg-hero__sub { margin: 0 auto; max-width: 560px; }

        /* ── Category tabs ── */
        .pkg-tabs { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin: 48px 0 0; }
        .pkg-tab {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: var(--radius-pill);
            font-family: var(--font-main); font-size: 14px; font-weight: 500;
            color: rgba(255,255,255,0.6);
            cursor: pointer; text-decoration: none;
            transition: all var(--transition-fast);
        }
        .pkg-tab:hover, .pkg-tab.is-active {
            background: rgba(58,123,255,0.12);
            border-color: rgba(58,123,255,0.35);
            color: #fff;
        }

        /* ── Section label ── */
        .pkg-section { padding: 72px 0 0; }
        .pkg-section__head { margin-bottom: 40px; }
        .pkg-section__label {
            display: inline-block;
            font-family: var(--font-mono); font-size: 12px; font-weight: 500;
            letter-spacing: 0.10em; text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 8px;
        }

        /* ── Grid ── */
        .pkg-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ── Card (color-coded variants) ── */
        .pkg-card {
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius-lg);
            display: flex; flex-direction: column;
            overflow: hidden;
            position: relative;
            transition: border-color var(--transition-fast), transform var(--transition-fast), box-shadow var(--transition-fast);
        }
        .pkg-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.35);
        }
        .pkg-card.is-popular {
            box-shadow: 0 0 0 1px rgba(58,123,255,0.18), 0 12px 32px rgba(0,0,0,0.3);
        }
        .pkg-card.is-popular:hover {
            box-shadow: 0 0 0 1px rgba(58,123,255,0.28), 0 20px 48px rgba(0,0,0,0.4), 0 0 24px rgba(58,123,255,0.18);
        }

        .pkg-card__top-bar { height: 4px; background: rgba(255,255,255,0.06); }

        /* Per-variant: top-bar gradient + tinted bg + colored price/check */
        .pkg-card--green  .pkg-card__top-bar { background: linear-gradient(90deg, #3ecf8e, #22c55e); }
        .pkg-card--green  { background: linear-gradient(180deg, rgba(62,207,142,0.10) 0%, rgba(62,207,142,0.02) 35%, rgba(255,255,255,0.025) 100%); border-color: rgba(62,207,142,0.22); }
        .pkg-card--green:hover { border-color: rgba(62,207,142,0.45); }
        .pkg-card--green  .pkg-price__amount { color: #4ade80; }
        .pkg-card--green  .pkg-features li svg { color: #4ade80; }

        .pkg-card--blue   .pkg-card__top-bar { background: linear-gradient(90deg, #3a7bff, #5b52f5); }
        .pkg-card--blue   { background: linear-gradient(180deg, rgba(58,123,255,0.10) 0%, rgba(58,123,255,0.02) 35%, rgba(255,255,255,0.025) 100%); border-color: rgba(58,123,255,0.22); }
        .pkg-card--blue:hover { border-color: rgba(58,123,255,0.45); }
        .pkg-card--blue   .pkg-price__amount { color: #6fa3ff; }
        .pkg-card--blue   .pkg-features li svg { color: #6fa3ff; }

        .pkg-card--purple .pkg-card__top-bar { background: linear-gradient(90deg, #a35bff, #7c3de0); }
        .pkg-card--purple { background: linear-gradient(180deg, rgba(163,91,255,0.10) 0%, rgba(163,91,255,0.02) 35%, rgba(255,255,255,0.025) 100%); border-color: rgba(163,91,255,0.22); }
        .pkg-card--purple:hover { border-color: rgba(163,91,255,0.45); }
        .pkg-card--purple .pkg-price__amount { color: #c084fc; }
        .pkg-card--purple .pkg-features li svg { color: #c084fc; }

        .pkg-card__inner { padding: 24px; display: flex; flex-direction: column; gap: 20px; flex: 1; }

        .pkg-card__badges { display: flex; gap: 6px; flex-wrap: wrap; min-height: 22px; }
        .pkg-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: var(--radius-pill);
            font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;
        }
        .pkg-badge--popular  { background: rgba(58,123,255,0.15); color: #6fa3ff; border: 1px solid rgba(58,123,255,0.3); }
        .pkg-badge--sale     { background: rgba(239,68,68,0.12);  color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
        .pkg-badge--student  { background: rgba(62,207,142,0.12); color: #4ade80; border: 1px solid rgba(62,207,142,0.25); }
        .pkg-badge--new      { background: rgba(0,212,255,0.12);  color: #38bdf8; border: 1px solid rgba(0,212,255,0.25); }

        .pkg-card__name { font-family: var(--font-main); font-weight: 700; font-size: 20px; color: #fff; margin: 0; }
        .pkg-card__blurb { font-size: 14px; line-height: 1.55; color: rgba(255,255,255,0.55); margin: 0; }

        .pkg-price__strike-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .pkg-price__strike { font-size: 15px; color: rgba(255,255,255,0.35); text-decoration: line-through; }
        .pkg-price__strike-idr { font-size: 13px; color: rgba(255,255,255,0.25); text-decoration: line-through; }
        .pkg-price__main { display: flex; align-items: baseline; gap: 8px; }
        .pkg-price__amount { font-family: var(--font-display); font-weight: 800; font-size: 32px; color: #fff; line-height: 1; }
        .pkg-price__cycle { font-size: 13px; color: rgba(255,255,255,0.45); }
        .pkg-price__idr { font-size: 14px; color: rgba(255,255,255,0.5); margin-top: 4px; }
        .pkg-price__save { font-size: 12px; color: #4ade80; font-weight: 600; margin-top: 2px; }

        .pkg-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .pkg-features li { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.4; }
        .pkg-features li svg { flex-shrink: 0; margin-top: 2px; color: #3ecf8e; }
        .pkg-features li.is-x svg { color: rgba(255,255,255,0.25); }

        .pkg-delivery { display: flex; align-items: center; gap: 8px; font-size: 13px; color: rgba(255,255,255,0.45); padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.06); }

        .pkg-card__cta { display: block; margin-top: auto; }

        /* ── CTA strip at bottom ── */
        .pkg-cta-strip { padding: 96px 0 120px; text-align: center; }
        .pkg-cta-strip__title { margin: 0 auto 16px; max-width: 480px; }
        .pkg-cta-strip__sub { margin: 0 auto 32px; max-width: 420px; }
        .pkg-cta-strip__btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

        /* ── Custom plan promo banner (replaces Copy/Ecom cards) ── */
        .pkg-promo-banner {
            display: flex; flex-wrap: wrap; gap: 24px; align-items: center; justify-content: space-between;
            padding: 28px 32px;
            background: linear-gradient(120deg, rgba(255,149,0,0.12) 0%, rgba(255,75,110,0.10) 50%, rgba(58,123,255,0.10) 100%);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: var(--radius-lg);
        }
        .pkg-promo-banner__txt { flex: 1; min-width: 260px; }
        .pkg-promo-banner__title { font-family: var(--font-main); font-weight: 700; font-size: 20px; color: #fff; margin: 0 0 8px; }
        .pkg-promo-banner__sub { font-size: 14px; color: rgba(255,255,255,0.7); margin: 0; }
        .pkg-promo-banner__btns { display: flex; gap: 10px; flex-wrap: wrap; }

        @media (max-width: 640px) {
            .pkg-hero { padding: 80px 0 48px; }
            .pkg-grid { grid-template-columns: 1fr; gap: 16px; }
            .pkg-card__inner { padding: 22px; gap: 18px; }
            .pkg-card__name { font-size: 18px; }
            .pkg-price__amount { font-size: 28px; }
            .pkg-features li { font-size: 13.5px; }
            .pkg-section { padding: 56px 0 0; }
            .pkg-promo-banner { padding: 22px; }
        }
        @media (max-width: 480px) {
            .pkg-card__inner { padding: 20px; gap: 16px; }
            .pkg-card__name { font-size: 17px; }
            .pkg-card__blurb { font-size: 13px; }
            .pkg-price__amount { font-size: 26px; }
            .pkg-price__strike { font-size: 13px; }
            .pkg-price__strike-idr { font-size: 12px; }
            .pkg-features li { font-size: 13px; gap: 8px; }
            .pkg-delivery { font-size: 12px; }
            .pkg-card__badges { gap: 4px; }
            .pkg-badge { padding: 2px 7px; font-size: 10px; }
        }
    </style>
</head>

<body class="w-full m-0 p-0 rc-redesign">

    <?php $base = '../'; include '../navbar.php'; ?>

    <!-- Hero -->
    <div class="pkg-hero rc-container">
        <div class="pkg-hero__eyebrow">
            <span class="rc-eyebrow">pricing</span>
        </div>
        <h1 class="rc-display-lg pkg-hero__title">All Packages &amp; Plans</h1>
        <p class="rc-body-lg pkg-hero__sub">Transparent pricing, no surprises. 50% off all packages during launch. Pick a plan or build your own.</p>

        <div class="pkg-tabs">
            <a class="pkg-tab is-active" href="#standard">Standard Plans</a>
            <a class="pkg-tab" href="#specialty">Specialty Plans</a>
            <a class="pkg-tab" href="#custom">Custom Plan</a>
        </div>
    </div>

    <!-- Standard Plans -->
    <section class="pkg-section" id="standard">
        <div class="rc-container">
            <div class="pkg-section__head">
                <span class="pkg-section__label">01 / Standard Plans</span>
                <h2 class="rc-h2">Website Development Tiers</h2>
                <p class="rc-body" style="margin:8px 0 0;max-width:520px">From personal projects to full-scale platforms. All plans include responsive design and clean code.</p>
            </div>

            <div class="pkg-grid">

                <!-- Student -->
                <div class="pkg-card pkg-card--green">
                    <div class="pkg-card__top-bar"></div>
                    <div class="pkg-card__inner">
                        <div class="pkg-card__badges">
                            <span class="pkg-badge pkg-badge--student">For Students</span>
                            <span class="pkg-badge pkg-badge--sale">50% OFF</span>
                        </div>
                        <div>
                            <h3 class="pkg-card__name">Student Plan</h3>
                            <p class="pkg-card__blurb">Personal or academic projects. Does not include free hosting or domain.</p>
                        </div>
                        <div>
                            <div class="pkg-price__strike-row">
                                <span class="pkg-price__strike">$59</span>
                                <span class="pkg-price__strike-idr">IDR 998k</span>
                            </div>
                            <div class="pkg-price__main">
                                <span class="pkg-price__amount">$30</span>
                                <span class="pkg-price__cycle">one-time</span>
                            </div>
                            <div class="pkg-price__idr">IDR 499k</div>
                            <div class="pkg-price__save">Save $29 / IDR 499k</div>
                        </div>
                        <ul class="pkg-features">
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>1 page website (single page)</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Responsive design</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Contact form + CTA section</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Basic</strong> SEO setup</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Social media + WhatsApp integration</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>1x</strong> minor revision</span></li>
                        </ul>
                        <div class="pkg-delivery">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Delivery in <strong>2&ndash;3 days</strong>
                        </div>
                        <a class="rc-btn pkg-card__cta" href="../order-form?aksi=landing">
                            Choose Plan
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Starter -->
                <div class="pkg-card pkg-card--blue">
                    <div class="pkg-card__top-bar"></div>
                    <div class="pkg-card__inner">
                        <div class="pkg-card__badges">
                            <span class="pkg-badge pkg-badge--sale">50% OFF</span>
                        </div>
                        <div>
                            <h3 class="pkg-card__name">Starter Plan</h3>
                            <p class="pkg-card__blurb">A clean, focused landing page to launch your idea. Includes free hosting and .COM domain.</p>
                        </div>
                        <div>
                            <div class="pkg-price__strike-row">
                                <span class="pkg-price__strike">$118</span>
                                <span class="pkg-price__strike-idr">IDR 1.998jt</span>
                            </div>
                            <div class="pkg-price__main">
                                <span class="pkg-price__amount">$59</span>
                                <span class="pkg-price__cycle">one-time</span>
                            </div>
                            <div class="pkg-price__idr">IDR 999k</div>
                            <div class="pkg-price__save">Save $59 / IDR 999k</div>
                        </div>
                        <ul class="pkg-features">
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Landing page website (1 page)</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Responsive layout</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Modern &amp; clean design</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Social media links integration</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Basic</strong> SEO setup</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>1</strong> design revision</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Free hosting &amp; .COM domain</span></li>
                        </ul>
                        <div class="pkg-delivery">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Delivery in <strong>3&ndash;5 days</strong>
                        </div>
                        <a class="rc-btn pkg-card__cta" href="../order-form?aksi=starter">
                            Choose Plan
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Pro -->
                <div class="pkg-card pkg-card--blue is-popular">
                    <div class="pkg-card__top-bar"></div>
                    <div class="pkg-card__inner">
                        <div class="pkg-card__badges">
                            <span class="pkg-badge pkg-badge--popular">Most Popular</span>
                            <span class="pkg-badge pkg-badge--sale">50% OFF</span>
                        </div>
                        <div>
                            <h3 class="pkg-card__name">Pro Plan</h3>
                            <p class="pkg-card__blurb">A multi-page business site built to grow. CMS included.</p>
                        </div>
                        <div>
                            <div class="pkg-price__strike-row">
                                <span class="pkg-price__strike">$296</span>
                                <span class="pkg-price__strike-idr">IDR 4.998jt</span>
                            </div>
                            <div class="pkg-price__main">
                                <span class="pkg-price__amount">$148</span>
                                <span class="pkg-price__cycle">one-time</span>
                            </div>
                            <div class="pkg-price__idr">IDR 2.499jt</div>
                            <div class="pkg-price__save">Save $148 / IDR 2.499jt</div>
                        </div>
                        <ul class="pkg-features">
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>All in Starter</strong>, plus:</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Up to 5 custom pages</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Custom UI/UX</strong> design</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Content Management System (CMS)</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Advanced</strong> SEO setup</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>2</strong> design revisions</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>1 month</strong> technical support</span></li>
                        </ul>
                        <div class="pkg-delivery">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Delivery in <strong>7&ndash;10 days</strong>
                        </div>
                        <a class="rc-btn pkg-card__cta" href="../order-form?aksi=pro">
                            Choose Plan
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Premium -->
                <div class="pkg-card pkg-card--purple">
                    <div class="pkg-card__top-bar"></div>
                    <div class="pkg-card__inner">
                        <div class="pkg-card__badges">
                            <span class="pkg-badge pkg-badge--sale">50% OFF</span>
                        </div>
                        <div>
                            <h3 class="pkg-card__name">Premium Plan</h3>
                            <p class="pkg-card__blurb">Full-scale platform with custom admin panel and AI chatbot integration.</p>
                        </div>
                        <div>
                            <div class="pkg-price__strike-row">
                                <span class="pkg-price__strike">$589</span>
                                <span class="pkg-price__strike-idr">IDR 9.998jt</span>
                            </div>
                            <div class="pkg-price__main">
                                <span class="pkg-price__amount">$295</span>
                                <span class="pkg-price__cycle">one-time</span>
                            </div>
                            <div class="pkg-price__idr">IDR 4.999jt</div>
                            <div class="pkg-price__save">Save $295 / IDR 4.999jt</div>
                        </div>
                        <ul class="pkg-features">
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>All in Pro</strong>, plus:</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Up to 10 custom pages</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>AI Chatbot Integration</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Advanced custom-coded Admin Panel</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Complete</strong> SEO setup</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>5</strong> design revisions</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>2 months</strong> technical support</span></li>
                            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Performance optimization</span></li>
                        </ul>
                        <div class="pkg-delivery">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Delivery in <strong>10&ndash;14 days</strong>
                        </div>
                        <a class="rc-btn pkg-card__cta" href="../order-form?aksi=business">
                            Choose Plan
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Specialty (Copy Website / E-Commerce -> moved into Custom Plan presets) -->
    <section class="pkg-section" id="specialty">
        <div class="rc-container">
            <div class="pkg-section__head">
                <span class="pkg-section__label">02 / Specialty Builds</span>
                <h2 class="rc-h2">Need a Copy Website or E-Commerce?</h2>
                <p class="rc-body" style="margin:8px 0 0;max-width:520px">Both are now configurable presets inside the Custom Plan. Pick a starting point and customize.</p>
            </div>

            <div class="pkg-promo-banner">
                <div class="pkg-promo-banner__txt">
                    <h3 class="pkg-promo-banner__title">Start with a preset, then make it yours</h3>
                    <p class="pkg-promo-banner__sub">Copy an existing site (from IDR 1jt / $60) or launch an online store (from IDR 1.5jt / $90). Adjust pages, add-ons, and timeline freely.</p>
                </div>
                <div class="pkg-promo-banner__btns">
                    <a class="rc-btn" href="../custom-plan/?preset=copy">
                        Copy Website Preset
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a class="rc-btn rc-btn-outline" href="../custom-plan/?preset=ecommerce">
                        E-Commerce Preset
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Plan -->
    <section class="pkg-section" id="custom">
        <div class="rc-container">
            <div class="pkg-section__head">
                <span class="pkg-section__label">03 / Custom Plan</span>
                <h2 class="rc-h2">Build Your Own Plan</h2>
                <p class="rc-body" style="margin:8px 0 0;max-width:520px">Only pay for what you actually need. Mix and match features to build the exact project scope.</p>
            </div>

            <div class="pkg-grid" style="grid-template-columns: 1fr;">
                <div class="pkg-card" style="border-color: rgba(58,123,255,0.25);">
                    <div class="pkg-card__top-bar" style="background: linear-gradient(90deg, #3a7bff, #a35bff, #3ecf8e);"></div>
                    <div class="pkg-card__inner" style="flex-direction: row; flex-wrap: wrap; gap: 32px; padding: 32px;">
                        <div style="flex: 1; min-width: 240px; display: flex; flex-direction: column; gap: 16px;">
                            <div class="pkg-card__badges">
                                <span class="pkg-badge" style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.7);border:1px solid rgba(255,255,255,0.12);">Custom</span>
                            </div>
                            <div>
                                <h3 class="pkg-card__name">Custom Plan</h3>
                                <p class="pkg-card__blurb">No template. You define the scope, we build exactly that. Priced by project after a quick consult.</p>
                            </div>
                            <div>
                                <div class="pkg-price__main">
                                    <span class="pkg-price__amount" style="font-size:22px;">from</span>
                                    <span class="pkg-price__amount" style="font-size:30px;">IDR 500k</span>
                                </div>
                                <div class="pkg-price__idr">~ USD 30 · final price quoted after scope</div>
                            </div>
                            <a class="rc-btn" href="../custom-plan/" style="align-self:flex-start;">
                                Build Your Plan
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                        <div style="flex: 1; min-width: 240px;">
                            <p style="font-size:13px;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:0.08em;margin:0 0 16px;font-family:var(--font-mono);">Available add-ons</p>
                            <ul class="pkg-features">
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>Copy Website preset</strong> &mdash; clone a reference site (from IDR 1jt)</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span><strong>E-Commerce preset</strong> &mdash; catalog + cart + checkout (from IDR 1.5jt)</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Any number of pages</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>AI chatbot integration</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Login / member system</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>CMS / Admin Panel</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Maintenance (per month)</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Priority delivery</span></li>
                                <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Free hosting &amp; domain <strong>from IDR 1jt</strong></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Strip -->
    <div class="pkg-cta-strip rc-container">
        <h2 class="rc-h2 pkg-cta-strip__title">Not sure which plan?</h2>
        <p class="rc-body pkg-cta-strip__sub">Tell us about your project and we'll recommend the right fit, no commitment.</p>
        <div class="pkg-cta-strip__btns">
            <a class="rc-btn" href="../order-form/">
                Start a Project
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a class="rc-btn rc-btn-outline" href="https://wa.me/6281295536876?text=Hello%2C%20I%27m%20looking%20at%20your%20packages%20and%20need%20help%20choosing%20the%20right%20plan." target="_blank" rel="noopener">
                Ask on WhatsApp
            </a>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script defer src="../JS/chatbot.js"></script>

</body>
</html>

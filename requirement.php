<?php
/* ----------  SEO – REQUIREMENT PAGE  ---------- */
$meta_title       = 'Client Requirements | What to Prepare Before Ordering – Rielcode';
$meta_description = 'Before ordering a website from Rielcode, make sure you have your brand logo, product photos, business description, and preferred site structure ready. Here is the full checklist.';
$meta_keywords    = 'persyaratan klien, requirement website, persiapan pembuatan website, checklist order website, Rielcode';
$meta_image       = 'https://rielcode.com/IMG/requirement-og.png';
$meta_url         = 'https://rielcode.com/#requirements';
$meta_robots      = 'index, follow';
$canonical        = 'https://rielcode.com/#requirements';
$og_type          = 'website';
$twitter_card     = 'summary_large_image';

if (file_exists(__DIR__ . '/inc/seo.php')) include 'inc/seo.php';
?>

<head>
    <link rel="stylesheet" href="CSS/requirement.css">
</head>

<section class="requirements-section section-padding" id="requirements">
    <div class="container mx-auto">

        <!-- Section header -->
        <div class="text-center mb-12">
            <h2 class="font-bold text-5xl text-white">Client Requirements</h2>
            <p class="lead text-white/50">
                Everything you need to prepare before placing your order — so we can start building right away.
            </p>
        </div>

        <!-- Intro card -->
        <div class="req-intro-card mb-5">
            <div class="req-intro-icon">📋</div>
            <div>
                <h3 class="req-intro-title">Why Preparation Matters</h3>
                <p class="req-intro-text">
                    Having your assets and information ready from the start lets us skip the back-and-forth and
                    deliver your website faster, with fewer revisions. The checklist below covers everything our
                    team will need to build a site that truly represents your brand.
                </p>
            </div>
        </div>

        <!-- Requirements grid -->
        <div class="req-grid">

            <!-- 1. Brand Logo -->
            <div class="req-card">
                <div class="req-card-icon">🎨</div>
                <h4 class="req-card-title">Brand Logo</h4>
                <p class="req-card-desc">
                    Provide your logo in a high-resolution format (PNG with transparent background preferred,
                    SVG or AI also accepted). If you don't have one yet, let us know — we can discuss options.
                </p>
                <div class="req-tag">Required</div>
            </div>

            <!-- 2. Product / Visual Content -->
            <div class="req-card">
                <div class="req-card-icon">📸</div>
                <h4 class="req-card-title">Product Photos &amp; Visuals</h4>
                <p class="req-card-desc">
                    Supply photos of your products, team, office, or anything relevant to your business.
                    High-quality images (min. 1000 px wide) make a huge difference in how professional the
                    final result looks.
                </p>
                <div class="req-tag">Recommended</div>
            </div>

            <!-- 3. Business Description -->
            <div class="req-card">
                <div class="req-card-icon">📝</div>
                <h4 class="req-card-title">Business Description</h4>
                <p class="req-card-desc">
                    A short write-up about your company or project: what you do, who you serve, and what makes
                    you different. This becomes the copy on your homepage and About page.
                </p>
                <div class="req-tag">Required</div>
            </div>

            <!-- 4. Site Structure -->
            <div class="req-card">
                <div class="req-card-icon">🗂️</div>
                <h4 class="req-card-title">Desired Page Structure</h4>
                <p class="req-card-desc">
                    List the pages you want (e.g., Home, About, Services, Gallery, Contact). For Pro and
                    Business plans, a rough sitemap or reference website helps us plan the layout efficiently.
                </p>
                <div class="req-tag">Required</div>
            </div>

            <!-- 5. Contact Info -->
            <div class="req-card">
                <div class="req-card-icon">📞</div>
                <h4 class="req-card-title">Contact Information</h4>
                <p class="req-card-desc">
                    Phone/WhatsApp number, email address, physical address (if any), and your social media
                    handles that should appear on the website.
                </p>
                <div class="req-tag">Required</div>
            </div>

            <!-- 6. Color Palette & Style -->
            <div class="req-card">
                <div class="req-card-icon">🖌️</div>
                <h4 class="req-card-title">Brand Colors &amp; Style Preference</h4>
                <p class="req-card-desc">
                    Share your primary brand colors (hex codes or references) and the overall vibe you are
                    going for — minimalist, bold, corporate, playful, etc. Reference sites are also very
                    helpful.
                </p>
                <div class="req-tag">Recommended</div>
            </div>

            <!-- 7. Domain & Hosting -->
            <div class="req-card">
                <div class="req-card-icon">🌐</div>
                <h4 class="req-card-title">Domain &amp; Hosting Status</h4>
                <p class="req-card-desc">
                    Let us know whether you already own a domain name and hosting, or if you need us to set
                    them up for you. This affects the deployment plan and timeline.
                </p>
                <div class="req-tag">Required</div>
            </div>

            <!-- 8. Additional Content -->
            <div class="req-card">
                <div class="req-card-icon">➕</div>
                <h4 class="req-card-title">Additional Content</h4>
                <p class="req-card-desc">
                    Anything else that should appear on the site: testimonials, team bios, FAQs, a product
                    catalogue, price lists, blog posts, certificates, or awards. The more you provide, the
                    richer your site will be.
                </p>
                <div class="req-tag">Optional</div>
            </div>

        </div><!-- /.req-grid -->

        <!-- CTA -->
        <div class="text-center mt-12">
            <p class="text-white/50 mb-6">
                Got everything ready? Great — let's build something amazing together.
            </p>
            <a href="order-form/" class="btn btn-glow px-12 font-semibold">Start Your Project 🚀</a>
        </div>

    </div>
</section>
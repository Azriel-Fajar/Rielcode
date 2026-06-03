<?php
session_start();
$base = '../';
$meta_title       = "Free Website Audit | Rielcode";
$meta_description = "Get a free website audit in seconds. Score your site on mobile, SEO, speed, and conversion. Built by Rielcode.";
$meta_keywords    = "free website audit, website checker, site score, Rielcode audit tool";
$meta_image       = "https://rielcode.com/IMG/Rielcode Logo.png";
$meta_type        = "website";
$meta_url         = "https://rielcode.com/audit/";
$canonical        = "https://rielcode.com/audit/";

$err = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../inc/seo.php'; ?>

    <link rel="stylesheet" href="<?= $base ?>CSS/style.css">
    <link rel="stylesheet" href="<?= $base ?>CSS/redesign.css">

    <link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>CSS/tailwind.css">
    <script defer src="<?= $base ?>JS/redesign.js"></script>

    <style>
        .rc-audit { min-height: 78vh; display: flex; align-items: center; padding: 120px 0 80px; }
        .rc-audit__card { max-width: 620px; margin: 0 auto; text-align: center; }
        .rc-audit__form { display: flex; flex-direction: column; gap: 14px; margin-top: 32px; text-align: left; }
        .rc-audit__field label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; opacity: .85; }
        .rc-audit__field input {
            width: 100%; padding: 14px 16px; border-radius: 12px;
            border: 1px solid rgba(0,0,0,.14); font-size: 1rem; background: #fff;
            color: #111;
        }
        .rc-audit__field input::placeholder { color: #888; }
        .rc-audit__field input:focus { outline: none; border-color: var(--rc-accent, #6c5ce7); }
        .rc-audit__submit { margin-top: 8px; justify-content: center; width: 100%; }
        .rc-audit__err { color: #c0392b; font-size: .85rem; margin-top: 10px; }
        .rc-audit__note { font-size: .8rem; opacity: .6; margin-top: 16px; }
    </style>
</head>

<body class="w-full m-0 p-0 rc-redesign">

    <?php include '../navbar.php'; ?>

    <section class="rc-audit">
        <div class="rc-container rc-audit__card">
            <span class="rc-eyebrow">free tool</span>
            <h1 class="rc-h2">Is your website costing you customers?</h1>
            <p class="rc-body-lg" style="margin:14px auto 0;max-width:520px">
                Get a free audit in seconds. We check mobile, SEO, speed, and conversion, then send a full report.
            </p>

            <form class="rc-audit__form" action="submit.php" method="POST" autocomplete="off">
                <!-- honeypot -->
                <input type="text" name="website" tabindex="-1" autocomplete="off"
                       style="position:absolute;left:-9999px" aria-hidden="true">

                <div class="rc-audit__field">
                    <label for="url">Your website URL</label>
                    <input type="url" id="url" name="url" placeholder="https://yoursite.com" maxlength="500" required>
                </div>
                <div class="rc-audit__field">
                    <label for="email">Where do we send the report?</label>
                    <input type="email" id="email" name="email" placeholder="you@email.com" required>
                </div>

                <button type="submit" class="rc-btn rc-audit__submit">
                    Run my free audit
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>

                <?php if ($err): ?>
                    <p class="rc-audit__err">
                        <?php
                        echo match ($err) {
                            'missing'   => 'Please fill in both your URL and email.',
                            'url'       => 'That URL does not look valid. Include https://',
                            'email'     => 'That email does not look valid.',
                            'toolong'   => 'That URL is too long. Please check and try again.',
                            'ratelimit' => 'Too many audits from your network. Please try again later.',
                            default     => 'Something went wrong. Please try again.',
                        };
                        ?>
                    </p>
                <?php endif; ?>
            </form>

            <p class="rc-audit__note">No spam. One report. Unsubscribe anytime.</p>
        </div>
    </section>

</body>
</html>

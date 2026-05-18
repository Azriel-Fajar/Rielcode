<?php
// session_start() MUST come before any output so we can read the token revealed by checkout.
session_start();

if (!isset($_SESSION['transaction'])) {
    header('Location: ../../checkout/');
    exit;
}

$progress_token    = $_SESSION['progress_token']    ?? null;
$progress_order_id = $_SESSION['progress_order_id'] ?? null;

// Status was already set to 'On Progress' by checkout/index.php. Done — clear session.
session_destroy();

$progress_url = $progress_token
    ? 'https://progress.rielcode.com/?t=' . $progress_token
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Successful | Rielcode</title>

    <link rel="stylesheet" href="../../CSS/redesign.css">
    <link rel="stylesheet" href="../../CSS/success.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../CSS/tailwind.css">
    <style>
        .progress-reveal {
            margin: 24px auto 28px;
            padding: 22px;
            border: 1px solid rgba(58,123,255,0.32);
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(58,123,255,0.10), rgba(58,123,255,0.04));
            max-width: 560px;
            text-align: left;
            color: #fff;
            font-family: 'Poppins', Arial, sans-serif;
        }
        .progress-reveal__label {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6fa3ff;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .progress-reveal h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 6px;
            color: #fff;
        }
        .progress-reveal p {
            font-size: 13.5px;
            opacity: 0.85;
            margin: 0 0 14px;
            line-height: 1.55;
        }
        .progress-reveal__urlbox {
            display: flex;
            gap: 8px;
            align-items: center;
            background: rgba(0,0,0,0.28);
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .progress-reveal__urlbox input {
            flex: 1;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 12.5px;
            outline: none;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            cursor: default;
            user-select: all;
        }
        .progress-reveal__copy {
            background: #3a7bff;
            border: none;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .progress-reveal__copy:hover { background: #5590ff; }
        .progress-reveal__open {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            margin-bottom: 14px;
            background: #fff;
            color: #0a0a0a;
            font-weight: 600;
            font-size: 13.5px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: transform 0.15s;
        }
        .progress-reveal__open:hover { transform: translateY(-1px); }
        .progress-reveal__warn {
            margin-top: 12px;
            font-size: 12px;
            color: #ffb86b;
            opacity: 0.95;
        }
    </style>
</head>
<body class="rc-redesign">
    <div class="popup">
        <div class="circle" role="img" aria-label="Order confirmed">
            <div class="checkmark"></div>
        </div>
        <span class="rc-eyebrow" style="margin-bottom:8px;">checkout complete</span>
        <h2 class="rc-h2">Order Completed!</h2>
        <p class="rc-body">Thank you for your purchase. Your order has been successfully processed.</p>

        <?php if ($progress_url): ?>
        <div class="progress-reveal">
            <span class="progress-reveal__label">your private project link</span>
            <h3>Track your project progress</h3>
            <p>This link is unique to your order. Use it anytime to see your current stage, latest updates from the team, and your preview site when it's ready.</p>
            <div class="progress-reveal__urlbox">
                <input id="progressUrl" type="text" readonly value="<?= htmlspecialchars($progress_url) ?>">
                <button type="button" class="progress-reveal__copy" id="progressCopyBtn">Copy</button>
            </div>
            <a class="progress-reveal__open" href="<?= htmlspecialchars($progress_url) ?>">
                Open Progress Portal
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
            </a>
            <p class="progress-reveal__warn">Save this link — we've also sent it to your email. Anyone with this link can view your project status.</p>
        </div>
        <script>
            (function(){
                var btn = document.getElementById('progressCopyBtn');
                var inp = document.getElementById('progressUrl');
                if (!btn || !inp) return;
                btn.addEventListener('click', function(){
                    inp.select();
                    inp.setSelectionRange(0, 9999);
                    var done = function(){ btn.textContent = 'Copied'; setTimeout(function(){ btn.textContent = 'Copy'; }, 1800); };
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(inp.value).then(done).catch(function(){ document.execCommand('copy'); done(); });
                    } else {
                        document.execCommand('copy');
                        done();
                    }
                });
            })();
        </script>
        <?php endif; ?>

        <a href="/" class="btn" style="margin-top:18px;"><span class="btn-arrow" aria-hidden="true">←</span> Back to Home</a>
    </div>

</body>
</html>

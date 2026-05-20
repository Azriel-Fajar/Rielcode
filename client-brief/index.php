<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../inc/error_codes.php';
require_once __DIR__ . '/../inc/audit_logger.php';
require_once __DIR__ . '/../inc/rate_limiter.php';
require_once __DIR__ . '/../inc/ip.php';

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

const RC_BRIEF_FIELD_MAX = 5000;

function rc_brief_fail_back(string $code): void {
    header("Location: ../?err=$code");
    exit;
}

function rc_brief_lookup_order(mysqli $conn, string $token): ?array {
    if ($token === '' || strlen($token) > 128) return null;
    $stmt = $conn->prepare(
        "SELECT o.id, o.order_name, o.email, o.phone_number, o.package
         FROM order_access_tokens t
         JOIN orders o ON o.id = t.order_id
         WHERE t.token = ? AND t.deactivated_at IS NULL
         LIMIT 1"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$token = trim($_GET['t'] ?? $_POST['t'] ?? '');
$order = rc_brief_lookup_order($conn, $token);

if (!$order) {
    error_log("[RC-BRIEF-001] client-brief: invalid token");
    rc_brief_fail_back('RC-BRIEF-001');
}

$errorCode = $_GET['err'] ?? '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        rc_rate_check(rc_client_ip());
    } catch (RuntimeException $e) {
        header("Location: ./?t=" . urlencode($token) . "&err=" . urlencode($e->getMessage()));
        exit;
    }

    $fields = [
        'business'    => trim($_POST['business']    ?? ''),
        'goals'       => trim($_POST['goals']       ?? ''),
        'audience'    => trim($_POST['audience']    ?? ''),
        'success'     => trim($_POST['success']     ?? ''),
        'brand_style' => trim($_POST['brand_style'] ?? ''),
    ];

    foreach ($fields as $k => $v) {
        if ($v === '' || strlen($v) > RC_BRIEF_FIELD_MAX) {
            header("Location: ./?t=" . urlencode($token) . "&err=RC-BRIEF-002");
            exit;
        }
        $fields[$k] = strip_tags($v);
    }

    // Send single HTML email to Azriel.
    include_once __DIR__ . '/../smtp_config.php';
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->SMTPSecure = $SMTP_SECURE;
        $mail->Port       = $SMTP_PORT;
        $mail->setFrom($SMTP_USER, 'Rielcode Brief Form');
        $mail->addAddress($SMTP_USER, 'Azriel');
        $mail->addReplyTo($order['email'], $order['order_name']);
        $mail->isHTML(true);
        $mail->Subject = "New Client Brief — " . $order['order_name'] . " (" . $order['package'] . ")";

        $q = function($label, $val) {
            return '<p style="margin:0 0 6px;font-weight:600;color:#1a73e8;">' . htmlspecialchars($label) . '</p>'
                 . '<p style="margin:0 0 18px;white-space:pre-wrap;">' . nl2br(htmlspecialchars($val)) . '</p>';
        };

        $mail->Body = '
<div style="font-family:Poppins,Arial,sans-serif;color:#222;line-height:1.55;max-width:640px;">
    <h2 style="margin:0 0 4px;">New Client Brief</h2>
    <p style="margin:0 0 18px;color:#555;">Order #' . (int)$order['id'] . ' — ' . htmlspecialchars($order['package']) . '</p>

    <div style="background:#f6f7f9;border-radius:8px;padding:14px 18px;margin-bottom:22px;">
        <p style="margin:0;"><b>Client:</b> ' . htmlspecialchars($order['order_name']) . '</p>
        <p style="margin:4px 0 0;"><b>Email:</b> ' . htmlspecialchars($order['email']) . '</p>
        <p style="margin:4px 0 0;"><b>Phone:</b> ' . htmlspecialchars($order['phone_number']) . '</p>
    </div>

    ' . $q('1. Business', $fields['business']) . '
    ' . $q('2. Goals', $fields['goals']) . '
    ' . $q('3. Target audience', $fields['audience']) . '
    ' . $q('4. Success in 6 months', $fields['success']) . '
    ' . $q('5. Brand style & inspiration', $fields['brand_style']) . '

    <hr style="border:none;border-top:1px solid #ddd;margin:24px 0 12px;">
    <p style="margin:0;color:#888;font-size:12px;">Submitted via rielcode.com/client-brief/ at ' . date('Y-m-d H:i') . '</p>
</div>';

        $mail->AltBody =
            "New Client Brief — Order #{$order['id']} ({$order['package']})\n" .
            "Client: {$order['order_name']} <{$order['email']}> / {$order['phone_number']}\n\n" .
            "1. Business\n{$fields['business']}\n\n" .
            "2. Goals\n{$fields['goals']}\n\n" .
            "3. Target audience\n{$fields['audience']}\n\n" .
            "4. Success in 6 months\n{$fields['success']}\n\n" .
            "5. Brand style & inspiration\n{$fields['brand_style']}\n";

        $mail->send();
    } catch (MailException $e) {
        error_log("[RC-BRIEF-003] client-brief mail send failed: " . $mail->ErrorInfo);
        rc_audit('CLIENT_BRIEF_MAIL_FAIL', $order['email'], ['order_id' => (int)$order['id']], 'error');
        header("Location: ./?t=" . urlencode($token) . "&err=RC-BRIEF-003");
        exit;
    }

    rc_audit('CLIENT_BRIEF_SUBMITTED', $order['email'], ['order_id' => (int)$order['id']]);
    header("Location: ./thanks.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Brief | Rielcode</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" sizes="32x32" href="../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="../CSS/tailwind.css">
    <link rel="stylesheet" href="../CSS/redesign.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0b0d12; color: #e7e9ee; font-family: 'Outfit', system-ui, sans-serif; margin: 0; }
        .brief-wrap { max-width: 720px; margin: 0 auto; padding: 56px 22px 80px; }
        .brief-eyebrow { font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: .12em; color: #8ab4ff; text-transform: uppercase; }
        .brief-title { font-size: 38px; line-height: 1.1; margin: 8px 0 12px; font-weight: 700; }
        .brief-sub { color: #a8adba; margin: 0 0 28px; }
        .brief-order { background: #141822; border: 1px solid #232838; border-radius: 10px; padding: 14px 18px; margin-bottom: 32px; display: flex; gap: 18px; flex-wrap: wrap; font-size: 14px; }
        .brief-order b { color: #fff; }
        .brief-q { margin-bottom: 26px; }
        .brief-q label { display: block; font-weight: 600; margin-bottom: 8px; color: #fff; }
        .brief-q .hint { display: block; font-size: 13px; color: #8a90a0; margin-bottom: 10px; }
        .brief-q textarea { width: 100%; background: #141822; border: 1px solid #2a3043; border-radius: 8px; padding: 12px 14px; color: #e7e9ee; font-family: inherit; font-size: 15px; line-height: 1.55; resize: vertical; min-height: 110px; box-sizing: border-box; }
        .brief-q textarea:focus { outline: none; border-color: #3a7bff; box-shadow: 0 0 0 3px rgba(58,123,255,.18); }
        .brief-submit { background: #3a7bff; color: #fff; border: 0; border-radius: 8px; padding: 14px 28px; font-size: 16px; font-weight: 600; cursor: pointer; font-family: inherit; }
        .brief-submit:hover { background: #2c66e0; }
        .brief-err { background: #3a1722; border: 1px solid #6b2235; color: #ffb3c1; padding: 12px 16px; border-radius: 8px; margin-bottom: 22px; font-size: 14px; }
    </style>
</head>
<body>
<div class="brief-wrap">
    <span class="brief-eyebrow">// step 1 — discovery</span>
    <h1 class="brief-title">Tell me about your project.</h1>
    <p class="brief-sub">Five short questions. Answer in your own words — no character limits, no perfect wording needed. The clearer you are, the better the site I build.</p>

    <div class="brief-order">
        <span><b>Client:</b> <?= htmlspecialchars($order['order_name']) ?></span>
        <span><b>Package:</b> <?= htmlspecialchars($order['package']) ?></span>
        <span><b>Order #:</b> <?= (int)$order['id'] ?></span>
    </div>

    <?php if ($errorCode): ?>
        <div class="brief-err">
            <?= htmlspecialchars(rc_user_msg($errorCode)) ?> (ref: <?= htmlspecialchars($errorCode) ?>)
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="hidden" name="t" value="<?= htmlspecialchars($token) ?>">

        <div class="brief-q">
            <label for="business">1. What does your business do?</label>
            <span class="hint">One paragraph. What you sell, who you sell to, what makes you you.</span>
            <textarea id="business" name="business" required maxlength="5000"></textarea>
        </div>

        <div class="brief-q">
            <label for="goals">2. What do you want this website to achieve?</label>
            <span class="hint">More leads, sales, credibility, bookings, downloads — pick whichever fits.</span>
            <textarea id="goals" name="goals" required maxlength="5000"></textarea>
        </div>

        <div class="brief-q">
            <label for="audience">3. Who is this website for?</label>
            <span class="hint">Age, role, location, what they care about, what they're trying to solve.</span>
            <textarea id="audience" name="audience" required maxlength="5000"></textarea>
        </div>

        <div class="brief-q">
            <label for="success">4. Six months from launch, what would make you say "this website worked"?</label>
            <span class="hint">Be specific if you can — number of leads, sales, sign-ups, anything measurable.</span>
            <textarea id="success" name="success" required maxlength="5000"></textarea>
        </div>

        <div class="brief-q">
            <label for="brand_style">5. Brand style &amp; inspiration</label>
            <span class="hint">Brand colors, fonts, logo files, and 1–3 reference websites you love (paste URLs).</span>
            <textarea id="brand_style" name="brand_style" required maxlength="5000"></textarea>
        </div>

        <button type="submit" class="brief-submit">Submit brief</button>
    </form>
</div>
</body>
</html>

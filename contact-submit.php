<?php
/**
 * Rielcode — Contact form submission endpoint.
 *
 * Receives POST from Astro /contact page (src/pages/contact.astro).
 * Track 1C decision: prefer PHP endpoint over Formspree because cPanel host
 * already runs PHP and PHPMailer is wired up project-wide.
 *
 * TODO (Track 2 / Track 1D follow-up):
 *  - Wire to PHPMailer + smtp_config.php to actually deliver to hello@rielcode.com
 *  - Persist to DB (contact_messages table) for admin panel review
 *  - Add CSRF token once Astro sets a same-origin cookie
 *  - Add rate limiting (per-IP, e.g. 5/hour)
 *  - Replace plain redirects with branded success / error pages
 *
 * Current behavior: validates input, honeypots bots, redirects back to /contact
 * with a query flag so the UI can show a confirmation banner.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// Honeypot — silent drop if bot filled the hidden "website" field.
if (!empty($_POST['website'])) {
    header('Location: /contact?sent=1');
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    header('Location: /contact?error=missing');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /contact?error=email');
    exit;
}
if (strlen($message) > 5000) {
    header('Location: /contact?error=too-long');
    exit;
}

// TODO: replace this log-only stub with PHPMailer delivery + DB insert.
$logLine = sprintf(
    "[%s] %s <%s> :: %s%s",
    date('c'),
    str_replace(["\r", "\n"], ' ', $name),
    str_replace(["\r", "\n"], ' ', $email),
    str_replace(["\r", "\n"], ' ', $message),
    PHP_EOL
);
@file_put_contents(__DIR__ . '/contact-submissions.log', $logLine, FILE_APPEND | LOCK_EX);

header('Location: /contact?sent=1');
exit;

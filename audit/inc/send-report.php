<?php
/**
 * audit/inc/send-report.php
 * Emails the audit PDF to the lead via SMTP (PHPMailer + smtp_config.php).
 *
 * rc_send_audit_email(array $lead, string $pdfBytes): bool
 */

require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

if (!function_exists('rc_send_audit_email')) {

    function rc_send_audit_email(array $lead, string $pdfBytes): bool {
        include __DIR__ . '/../../smtp_config.php';

        $score = (int) ($lead['score'] ?? 0);
        $url   = $lead['url'] ?? '';
        $email = $lead['email'] ?? '';
        $aid   = $lead['audit_id'] ?? '';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = $SMTP_USER;
            $mail->Password   = $SMTP_PASS;
            $mail->SMTPSecure = $SMTP_SECURE;
            $mail->Port       = $SMTP_PORT;

            $mail->setFrom($SMTP_USER, 'Rielcode Audit');
            $mail->addAddress($email);
            $mail->addBCC($SMTP_USER); // copy to self for follow-up
            $mail->isHTML(true);

            $mail->Subject = "Your Website Audit is Ready — {$score}/100";

            $safeUrl = htmlspecialchars($url);
            $wa = 'https://wa.me/6281295536876?text=' . rawurlencode("Hello, my audit ID is {$aid}. I'd like to discuss my site.");

            $mail->Body = "
                <div style='font-family:Arial,sans-serif;color:#1a1a1a;max-width:520px'>
                    <h2 style='color:#6c5ce7'>Your free website audit is ready</h2>
                    <p>We audited <strong>{$safeUrl}</strong> and scored it <strong>{$score}/100</strong>.</p>
                    <p>The full breakdown, with the issues to fix first, is in the attached PDF.</p>
                    <p>Want them fixed without lifting a finger?
                       <a href='{$wa}' style='color:#6c5ce7'>Message us on WhatsApp</a>
                       and quote audit ID <strong>{$aid}</strong>.</p>
                    <p style='color:#888;font-size:12px;margin-top:24px'>Rielcode &middot; Real Code. Real Results.</p>
                </div>";
            $mail->AltBody = "Your website audit for {$url} scored {$score}/100. "
                . "Full report attached. Reply or message wa.me/6281295536876 (audit ID {$aid}) to get the issues fixed.";

            $mail->addStringAttachment($pdfBytes, "rielcode-audit-{$score}-100.pdf", 'base64', 'application/pdf');

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('[RC-AUDIT-004] send-report: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

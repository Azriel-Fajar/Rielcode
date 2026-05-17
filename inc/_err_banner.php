<?php
/**
 * inc/_err_banner.php
 * Renders a friendly error banner when ?err=RC-XXX-NNN is present.
 * Include near the top of public pages that may receive redirects with error refs.
 */

require_once __DIR__ . '/error_codes.php';

$rc_err_code = isset($_GET['err']) ? trim($_GET['err']) : '';
if ($rc_err_code === '' || !isset(RC_ERRORS[$rc_err_code])) return;

$rc_err_msg = htmlspecialchars(rc_user_msg($rc_err_code), ENT_QUOTES, 'UTF-8');
?>
<div class="rc-err-banner" role="alert" style="
    max-width: 720px;
    margin: 16px auto;
    padding: 12px 16px;
    border-radius: 8px;
    background: #fff4f4;
    border: 1px solid #f5b5b5;
    color: #8a1f1f;
    font-family: system-ui, sans-serif;
    font-size: 14px;">
    <strong>⚠️ </strong><?= $rc_err_msg ?>
</div>

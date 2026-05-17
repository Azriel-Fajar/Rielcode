<?php
/**
 * inc/_db_down.php
 * Rendered when MySQL is unreachable. Never leaks the raw driver error.
 */

http_response_code(503);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Service unavailable — Rielcode</title>
<meta name="robots" content="noindex">
<style>
    body { font-family: system-ui, sans-serif; background:#f7f7f8; color:#222; margin:0; padding:0; }
    .wrap { max-width: 560px; margin: 12vh auto; padding: 32px; background:#fff; border-radius:12px; box-shadow:0 6px 24px rgba(0,0,0,0.06); text-align:center; }
    h1 { font-size: 22px; margin: 0 0 12px; }
    p  { color:#555; line-height:1.6; }
    code { background:#f2f2f4; padding:2px 6px; border-radius:4px; font-size:13px; }
    a { color:#0a66c2; text-decoration: none; }
</style>
</head>
<body>
<div class="wrap">
    <h1>Service temporarily unavailable</h1>
    <p>We're having trouble reaching the database right now. Please try again in a few minutes.</p>
    <p>If the problem persists, contact <a href="mailto:support@rielcode.com">support@rielcode.com</a> and include this reference:</p>
    <p><code>RC-DB-001</code></p>
</div>
</body>
</html>
<?php exit;

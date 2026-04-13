<?php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $SMTP_HOST = 'smtp.gmail.com';
    $SMTP_USER = 'afw1407@gmail.com'; // email testing
    $SMTP_PASS = 'lxmx kqex encm kwes';             // app password Gmail
    $SMTP_PORT = 587;
    $SMTP_SECURE = 'tls';
} else {
    $SMTP_HOST = 'mail.rielcode.com';
    $SMTP_USER = 'info@rielcode.com';
    $SMTP_PASS = 'rielinfo1407';
    $SMTP_PORT = 587;
    $SMTP_SECURE = 'tls';
}

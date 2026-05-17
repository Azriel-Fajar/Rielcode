<?php
session_start();
require_once __DIR__ . '/inc/audit_logger.php';

$who = $_SESSION['admin_username'] ?? ($_SESSION['admin_logged_in'] ?? false ? 'admin' : 'guest');
rc_audit('ADMIN_LOGOUT', $who);

session_destroy();
header("Location: admin_login.php");
exit;

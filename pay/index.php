<?php
require_once __DIR__ . '/config.php';
session_start();
header('Location: ' . APP_URL . (empty($_SESSION['admin_logged_in']) ? '/login.php' : '/dashboard.php'));
exit;

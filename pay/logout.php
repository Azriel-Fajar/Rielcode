<?php
session_start();
session_destroy();
require_once __DIR__ . '/config.php';
header('Location: ' . APP_URL . '/login.php');
exit;

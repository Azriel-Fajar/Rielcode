<?php
/**
 * connection.php
 * Provides a mysqli $conn for legacy code.
 * Credentials are loaded from the external config file (outside public_html).
 *
 * Production config path : /home/rier5192/config.php
 * Local fallback          : config.php (same directory)
 */

$_rcConfigPath = '/home/rier5192/config.php';
if (file_exists($_rcConfigPath)) {
    $_rcCfg  = require $_rcConfigPath;
} else {
    // Local dev fallback
    $_rcCfg  = require __DIR__ . '/config.php';
}

$conn = mysqli_connect(
    $_rcCfg['DB_HOST'] ?? 'localhost',
    $_rcCfg['DB_USER'] ?? 'root',
    $_rcCfg['DB_PASS'] ?? '',
    $_rcCfg['DB_NAME'] ?? 'rielcode'
);

if (mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}

// Clean up temporary variables so they don't pollute the global scope
unset($_rcConfigPath, $_rcCfg);
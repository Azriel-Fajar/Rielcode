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
    // Log full driver error for debugging; never leak it to the client.
    error_log('[RC-DB-001] connection.php: ' . mysqli_connect_error());

    $wantsJson = (
        (isset($_SERVER['HTTP_ACCEPT'])      && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE'])     && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    );

    if ($wantsJson) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode([
            'ok'    => false,
            'code'  => 'RC-DB-001',
            'reply' => 'Service temporarily unavailable. (ref: RC-DB-001)',
        ]);
        exit;
    }

    include __DIR__ . '/inc/_db_down.php';
}

// Clean up temporary variables so they don't pollute the global scope
unset($_rcConfigPath, $_rcCfg);
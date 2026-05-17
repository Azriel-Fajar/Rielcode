<?php
/**
 * inc/db.php
 * Shared PDO singleton. Reuses the same config loader as connection.php
 * (production /home/rier5192/config.php, fallback local config.php).
 *
 * Legacy mysqli callers keep using connection.php. New code uses rc_pdo().
 */

if (!function_exists('rc_pdo')) {
    function rc_pdo(): PDO {
        static $pdo = null;
        if ($pdo instanceof PDO) return $pdo;

        $prodPath  = '/home/rier5192/config.php';
        $localPath = dirname(__DIR__) . '/config.php';
        $cfg = file_exists($prodPath) ? require $prodPath
             : (file_exists($localPath) ? require $localPath : []);

        $host = $cfg['DB_HOST'] ?? 'localhost';
        $name = $cfg['DB_NAME'] ?? 'rielcode';
        $user = $cfg['DB_USER'] ?? 'root';
        $pass = $cfg['DB_PASS'] ?? '';

        $dsn  = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        $pdo  = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    }
}

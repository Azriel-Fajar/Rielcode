<?php
session_start();
ob_start();

// Path for cPanel config (outside public_html)
$configPath = '/home/rier5192/config.php';

if (file_exists($configPath)) {
    // Use config from cPanel
    $config = require $configPath;

    $apiKey = $config['OPENAI_API_KEY'] ?? '';
    $dbHost = $config['DB_HOST'] ?? 'localhost';
    $dbName = $config['DB_NAME'] ?? 'rielcode';
    $dbUser = $config['DB_USER'] ?? 'root';
    $dbPass = $config['DB_PASS'] ?? '';
} else {
    // Localhost fallback
    $config = require __DIR__ . '/config.php'; // config.php in same folder

    $apiKey = $config['OPENAI_API_KEY'] ?? '';
    $dbHost = $config['DB_HOST'] ?? 'localhost';
    $dbName = $config['DB_NAME'] ?? 'rielcode';
    $dbUser = $config['DB_USER'] ?? 'root';
    $dbPass = $config['DB_PASS'] ?? '';
}

require_once __DIR__ . '/inc/error_codes.php';
require_once __DIR__ . '/inc/audit_logger.php';

// Connect to MySQL
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('[RC-DB-001] admin_login: ' . $e->getMessage());
    include __DIR__ . '/inc/_db_down.php';
}


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $admin['username'];
        rc_audit('ADMIN_LOGIN_OK', $admin['username']);

        $redirect = 'admin.php';
        header("Location: $redirect");
        exit;
    }

    $error = rc_user_msg('RC-AUTH-001');
    rc_audit('ADMIN_LOGIN_FAIL', $username, ['reason' => $admin ? 'bad_password' : 'unknown_user'], 'warn');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login - RielBot</title>
    <?php include __DIR__ . '/inc/admin_theme_head.php'; ?>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
    <meta name="robots" content="noindex,nofollow">
</head>

<body class="login-page">
    <div class="login-container">
        <img src="IMG/Rielcode Logo Square Transparent Icon.png" alt="Rielcode" class="login-logo-img">
        <div class="login-logo">Rielcode Admin</div>
        <div class="login-subtitle">// SECURE ACCESS</div>
        <h2>Admin Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <a href="https://rielcode.com" class="back-to-site">← Back to Website</a>
    </div>
</body>


</html>
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

// Connect to MySQL
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8",
        $dbUser,
        $dbPass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Setelah login berhasil
        if ($admin && password_verify($_POST['password'], $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;

            // Redirect relatif (aman di localhost & live)
            $redirect = 'admin.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username.";
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login - RielBot</title>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
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
        <p class="error"><?= htmlspecialchars($error) ?></p>
        <a href="https://rielcode.com" class="back-to-site">← Back to Website</a>
    </div>
</body>


</html>
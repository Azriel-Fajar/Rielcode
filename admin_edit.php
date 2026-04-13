<?php
session_start();

// --- Check admin login ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// --- Config loader ---
$configPath = '/home/rier5192/config.php';
if (file_exists($configPath)) {
    $config = require $configPath;
    $dbHost = $config['DB_HOST'];
    $dbName = $config['DB_NAME'];
    $dbUser = $config['DB_USER'];
    $dbPass = $config['DB_PASS'];
} else {
    $config = require __DIR__ . '/config.php';
    $dbHost = $config['DB_HOST'] ?? 'localhost';
    $dbName = $config['DB_NAME'] ?? 'rielcode';
    $dbUser = $config['DB_USER'] ?? 'root';
    $dbPass = $config['DB_PASS'] ?? '';
}

// --- Connect to DB ---
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// --- Determine table and id ---
$table = $_GET['table'] ?? 'orders';
$id = $_GET['id'] ?? null;

// --- Initialize form variables ---
$formData = [];
$errors = [];

// --- Handle Edit: Fetch existing data ---
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id=?");
    $stmt->execute([$id]);
    $formData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$formData) {
        die("Data not found.");
    }
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($table === 'orders') {
        $order_name = $_POST['order_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $package = $_POST['package'] ?? '';
        $owns_domain = $_POST['owns_domain'] ?? 'No';
        $owns_hosting = $_POST['owns_hosting'] ?? 'No';
        $phone_number = $_POST['phone_number'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'Pending';

        // --- Validation ---
        if (!$order_name) $errors[] = "Order Name is required";
        if (!$email) $errors[] = "Email is required";

        if (empty($errors)) {
            if ($id) {
                // Update existing order
                $stmt = $pdo->prepare("UPDATE orders SET order_name=?, email=?, package=?, owns_domain=?, owns_hosting=?, phone_number=?, description=?, status=? WHERE id=?");
                $stmt->execute([$order_name, $email, $package, $owns_domain, $owns_hosting, $phone_number, $description, $status, $id]);
            } else {
                // Insert new order
                $stmt = $pdo->prepare("INSERT INTO orders (order_name,email,package,owns_domain,owns_hosting,phone_number,description,status,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([$order_name, $email, $package, $owns_domain, $owns_hosting, $phone_number, $description, $status]);
            }
            header("Location: admin.php?table=orders");
            exit;
        }
    } elseif ($table === 'packages') {
        $package_name = $_POST['package_name'] ?? '';
        $idr_price = $_POST['idr_price'] ?? 0;
        $us_price = $_POST['us_price'] ?? 0;

        if (!$package_name) $errors[] = "Package Name is required";

        if (empty($errors)) {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE packages SET package_name=?, idr_price=?, us_price=? WHERE id=?");
                $stmt->execute([$package_name, $idr_price, $us_price, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO packages (package_name,idr_price,us_price,orders) VALUES (?,?,?,0)");
                $stmt->execute([$package_name, $idr_price, $us_price]);
            }
            header("Location: admin.php?table=packages");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $id ? 'Edit' : 'Add' ?> <?= ucfirst($table) ?></title>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar-toggle" id="sidebarToggle">☰ Menu</div>

    <div class="wrapper">
        <div class="sidebar">
            <h2>
                <img src="IMG/Rielcode Logo Square Transparent Icon.png" alt="Rielcode" class="sidebar-logo">
                RielBot Admin
            </h2>
            <a href="admin.php?table=chat_logs">Chat Logs</a>
            <a href="admin.php?table=orders">Orders</a>
            <a href="admin.php?table=packages">Packages</a>
            <a href="admin_logout.php">Logout</a>
        </div>
        <div class="main-content">
            <h1><?= $id ? 'Edit' : 'Add' ?> <?= ucfirst($table) ?></h1>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
            <form method="POST">
                <?php if ($table === 'orders'): ?>
                    <div>
                        <label>Order Name</label>
                        <input type="text" name="order_name" value="<?= htmlspecialchars($formData['order_name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label>Package</label>
                        <input type="text" name="package" value="<?= htmlspecialchars($formData['package'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div>
                            <label>Owns Domain</label>
                            <select name="owns_domain">
                                <option value="Yes" <?= ($formData['owns_domain'] ?? '') == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                <option value="No"  <?= ($formData['owns_domain'] ?? '') == 'No'  ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div>
                            <label>Owns Hosting</label>
                            <select name="owns_hosting">
                                <option value="Yes" <?= ($formData['owns_hosting'] ?? '') == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                <option value="No"  <?= ($formData['owns_hosting'] ?? '') == 'No'  ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" value="<?= htmlspecialchars($formData['phone_number'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Description</label>
                        <textarea name="description"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="Pending"     <?= ($formData['status'] ?? '') == 'Pending'     ? 'selected' : '' ?>>Pending</option>
                            <option value="On Progress" <?= ($formData['status'] ?? '') == 'On Progress' ? 'selected' : '' ?>>On Progress</option>
                            <option value="Completed"   <?= ($formData['status'] ?? '') == 'Completed'   ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                <?php elseif ($table === 'packages'): ?>
                    <div>
                        <label>Package Name</label>
                        <input type="text" name="package_name" value="<?= htmlspecialchars($formData['package_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-row">
                        <div>
                            <label>IDR Price</label>
                            <input type="number" name="idr_price" value="<?= htmlspecialchars($formData['idr_price'] ?? 0) ?>">
                        </div>
                        <div>
                            <label>USD Price</label>
                            <input type="number" name="us_price" value="<?= htmlspecialchars($formData['us_price'] ?? 0) ?>">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-actions">
                    <button type="submit" class="button"><?= $id ? 'Update' : 'Add' ?></button>
                    <a href="admin.php?table=<?= $table ?>" class="button delete">Cancel</a>
                </div>
            </form>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const toggle  = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        });

        overlay.addEventListener('click', closeSidebar);
    })();
    </script>
</body>
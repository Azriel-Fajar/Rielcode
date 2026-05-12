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
} else {
    $config = require __DIR__ . '/config.php';
}
$dbHost = $config['DB_HOST'] ?? 'localhost';
$dbName = $config['DB_NAME'] ?? 'rielcode';
$dbUser = $config['DB_USER'] ?? 'root';
$dbPass = $config['DB_PASS'] ?? '';

// --- Connect to DB (PDO only — removed mixed mysqli usage) ---
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . htmlspecialchars($e->getMessage()));
}

// Whitelist $table to prevent SQL injection via GET parameter
$allowedTables = ['chat_logs', 'orders', 'packages', 'testimonials', 'projects', 'invoices', 'referrers', 'commissions'];
$table = in_array($_GET['table'] ?? '', $allowedTables) ? $_GET['table'] : 'chat_logs';

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// --- Flash helper ---
function rc_flash($msg, $variant = 'success') {
    $_SESSION['rc_flash'] = ['msg' => $msg, 'variant' => $variant];
}

// --- Testimonials: delete invite token ---
if ($table === 'testimonials' && $action === 'del_token') {
    $tid = isset($_GET['token_id']) ? (int)$_GET['token_id'] : 0;
    if ($tid) {
        $pdo->prepare("DELETE FROM testimonial_invites WHERE id=? AND used_at IS NULL")->execute([$tid]);
        rc_flash('Token deleted.');
    }
    header("Location: admin.php?table=testimonials");
    exit;
}

// --- Testimonials: approve / reject / delete ---
if ($table === 'testimonials' && $action !== '' && $id) {
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE testimonials SET status='approved', reviewed_at=NOW() WHERE id=?");
        $stmt->execute([$id]);
        rc_flash('Testimonial approved.');
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE testimonials SET status='rejected', reviewed_at=NOW() WHERE id=?");
        $stmt->execute([$id]);
        rc_flash('Testimonial rejected.');
    } elseif ($action === 'delete') {
        // Unlink any invite that referenced this testimonial
        $pdo->prepare("UPDATE testimonial_invites SET testimonial_id=NULL WHERE testimonial_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
        rc_flash('Testimonial deleted.');
    }
    header("Location: admin.php?table=testimonials");
    exit;
}

// --- Testimonials: generate invite token ---
if ($table === 'testimonials' && $action === 'gen_token') {
    $label = trim($_POST['label'] ?? '');
    $token = bin2hex(random_bytes(32));
    $stmt  = $pdo->prepare("INSERT INTO testimonial_invites (token, label) VALUES (?, ?)");
    $stmt->execute([$token, $label === '' ? null : $label]);
    rc_flash('Invite link generated.');
    header("Location: admin.php?table=testimonials&new_token=" . urlencode($token));
    exit;
}

// --- Handle Delete (orders, packages — NOT referrers/projects which have their own handlers) ---
if ($action === 'delete' && $id && !in_array($table, ['chat_logs', 'referrers', 'projects', 'testimonials'], true)) {
    if ($table === 'orders') {
        // BUG FIX: use PDO for the file lookup instead of raw mysqli
        $stmt = $pdo->prepare("SELECT invoice_file FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['invoice_file'])) {
            $filePath = ltrim(str_replace('../', '', $row['invoice_file']), '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $pdo->prepare("DELETE FROM referral_commissions WHERE order_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        // Recalculate order counts
        $pdo->exec("UPDATE packages p SET orders = (SELECT COUNT(*) FROM orders o WHERE o.package_id = p.id)");
        rc_flash('Order deleted.');
    } elseif ($table === 'packages') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE package_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$id]);
            rc_flash('Package deleted.');
        } else {
            rc_flash('Cannot delete package — has existing orders.', 'error');
        }
    } elseif ($table === 'chat_logs') {
        $stmt = $pdo->prepare("DELETE FROM chat_logs WHERE id = ?");
        $stmt->execute([$id]);
        rc_flash('Chat log deleted.');
    }
    header("Location: admin.php?table=$table");
    exit;
}

// --- Projects: add / update / delete ---
if ($table === 'projects' && $_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add','update'], true)) {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $url         = trim($_POST['url']         ?? '');
    $tags        = trim($_POST['tags']        ?? '');
    $layout      = $_POST['layout']           ?? 'side';
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_visible  = isset($_POST['is_visible']) ? 1 : 0;
    if (!in_array($layout, ['featured','side'], true)) $layout = 'side';

    $imagePath = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/IMG/projects';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
            $safe = preg_replace('/[^a-z0-9_-]/i', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
            $filename = $safe . '_' . time() . '.' . $ext;
            $target = $uploadDir . '/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = 'IMG/projects/' . $filename;
            }
        }
    }

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, image_path, url, tags, layout, sort_order, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $imagePath, $url, $tags, $layout, $sort_order, $is_visible]);
        rc_flash('Project added.');
    } else {
        $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, image_path=?, url=?, tags=?, layout=?, sort_order=?, is_visible=? WHERE id=?");
        $stmt->execute([$title, $description, $imagePath, $url, $tags, $layout, $sort_order, $is_visible, $id]);
        rc_flash('Project updated.');
    }
    header("Location: admin.php?table=projects");
    exit;
}
if ($table === 'projects' && $action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    rc_flash('Project deleted.');
    header("Location: admin.php?table=projects");
    exit;
}

// --- Referrers: add ---
if ($table === 'referrers' && $_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $rName  = trim($_POST['name']            ?? '');
    $rPhone = trim($_POST['phone']           ?? '');
    $rCode  = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($_POST['code'] ?? '')));
    $rRate  = min(100, max(0, (float)($_POST['commission_rate'] ?? 10)));
    if ($rName !== '' && $rPhone !== '' && $rCode !== '') {
        $stmt = $pdo->prepare("INSERT INTO referrers (name, phone, code, commission_rate) VALUES (?, ?, ?, ?)");
        $stmt->execute([$rName, $rPhone, $rCode, $rRate]);
        rc_flash('Referrer added.');
    } else {
        rc_flash('Name, phone, and code are required.', 'error');
    }
    header("Location: admin.php?table=referrers");
    exit;
}

// --- Referrers: toggle active/inactive ---
if ($table === 'referrers' && $action === 'toggle' && $id) {
    $stmt = $pdo->prepare("SELECT status FROM referrers WHERE id = ?");
    $stmt->execute([$id]);
    $cur = $stmt->fetchColumn();
    $next = ($cur === 'active') ? 'inactive' : 'active';
    $pdo->prepare("UPDATE referrers SET status = ? WHERE id = ?")->execute([$next, $id]);
    rc_flash('Referrer status updated.');
    header("Location: admin.php?table=referrers");
    exit;
}

// --- Referrers: delete (only if no commissions linked) ---
if ($table === 'referrers' && $action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM referral_commissions WHERE referrer_id = ?");
    $stmt->execute([$id]);
    if ((int)$stmt->fetchColumn() > 0) {
        rc_flash('Cannot delete — referrer has linked commissions.', 'error');
    } else {
        $pdo->prepare("DELETE FROM referrers WHERE id = ?")->execute([$id]);
        rc_flash('Referrer deleted.');
    }
    header("Location: admin.php?table=referrers");
    exit;
}

// --- Commissions: mark paid ---
if ($table === 'commissions' && $action === 'paid' && $id) {
    $pdo->prepare("UPDATE referral_commissions SET status='paid', paid_at=NOW() WHERE id = ?")->execute([$id]);
    rc_flash('Commission marked as paid.');
    header("Location: admin.php?table=commissions");
    exit;
}

// --- Commissions: cancel ---
if ($table === 'commissions' && $action === 'cancel' && $id) {
    $pdo->prepare("UPDATE referral_commissions SET status='cancelled' WHERE id = ?")->execute([$id]);
    rc_flash('Commission cancelled.');
    header("Location: admin.php?table=commissions");
    exit;
}

// --- Invoices: handled below (CRUD via dedicated routes in invoices admin block) ---

// --- Pagination ---
$items_per_page = 20;
$page           = max(1, (int)($_GET['page'] ?? 1));
$offset         = ($page - 1) * $items_per_page;
$filterReferrerId = 0;
$filterStatus     = '';

// `invoices` is a virtual view over `orders`
if ($table === 'invoices') {
    $countTable = 'orders';
    $total_items = (int)$pdo->query("SELECT COUNT(*) FROM `$countTable`")->fetchColumn();
} elseif ($table === 'commissions') {
    $filterReferrerId = isset($_GET['referrer_id']) ? (int)$_GET['referrer_id'] : 0;
    $filterStatus      = in_array($_GET['status'] ?? '', ['pending','paid','cancelled'], true) ? $_GET['status'] : '';
    $cntWhere = [];
    $cntParams = [];
    if ($filterReferrerId) { $cntWhere[] = 'referrer_id = :ref_id'; $cntParams[':ref_id'] = $filterReferrerId; }
    if ($filterStatus !== '') { $cntWhere[] = 'status = :status'; $cntParams[':status'] = $filterStatus; }
    $cntSql = 'SELECT COUNT(*) FROM referral_commissions' . ($cntWhere ? ' WHERE ' . implode(' AND ', $cntWhere) : '');
    $cntStmt = $pdo->prepare($cntSql);
    foreach ($cntParams as $k => $v) $cntStmt->bindValue($k, $v);
    $cntStmt->execute();
    $total_items = (int)$cntStmt->fetchColumn();
} else {
    $total_items = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$total_pages = max(1, (int)ceil($total_items / $items_per_page));

// Projects edit-mode preload
$editProject = null;
if ($table === 'projects' && $action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $editProject = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- Fetch paged data ---
switch ($table) {
    case 'orders':
        $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $columns = ['id', 'order_name', 'email', 'package', 'owns_domain', 'owns_hosting', 'phone_number', 'description', 'status', 'invoice_file'];
        break;
    case 'packages':
        $stmt = $pdo->prepare("SELECT * FROM packages ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $columns = ['id', 'package_name', 'idr_price', 'us_price', 'orders'];
        break;
    case 'testimonials':
        // pending first, then by submitted_at desc
        $stmt = $pdo->prepare(
            "SELECT id, client_name, business_name, role_title, rating, project_url,
                    problem_before, solution_after, recommendation, headline,
                    client_email, consent_given, status, submitted_at, reviewed_at, ip_address
             FROM testimonials
             ORDER BY FIELD(status,'pending','approved','rejected'), submitted_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $columns = ['id', 'client_name', 'business_name', 'rating', 'status', 'submitted_at'];
        break;
    case 'projects':
        $stmt = $pdo->prepare("SELECT * FROM projects ORDER BY layout DESC, sort_order ASC, id DESC LIMIT :limit OFFSET :offset");
        $columns = ['id','title','layout','sort_order','is_visible'];
        break;
    case 'invoices':
        $stmt = $pdo->prepare("SELECT id, order_name, email, package, final_price, invoice_number, invoice_status, invoice_amount, invoice_currency, invoice_sent, invoice_file, created_at FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $columns = ['id','order_name','package','invoice_number','invoice_status','invoice_amount'];
        break;
    case 'referrers':
        $stmt = $pdo->prepare(
            "SELECT r.id, r.name, r.phone, r.code, r.commission_rate, r.status,
                    COALESCE(SUM(CASE WHEN rc.status='paid' THEN rc.commission_amount ELSE 0 END), 0) AS total_earned
             FROM referrers r
             LEFT JOIN referral_commissions rc ON rc.referrer_id = r.id
             GROUP BY r.id
             ORDER BY r.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $columns = ['id','name','phone','code','commission_rate','status','total_earned'];
        break;
    case 'commissions':
        $where = [];
        $params = [];
        if ($filterReferrerId) { $where[] = 'rc.referrer_id = :ref_id'; $params[':ref_id'] = $filterReferrerId; }
        if ($filterStatus !== '') { $where[] = 'rc.status = :status'; $params[':status'] = $filterStatus; }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $stmt = $pdo->prepare(
            "SELECT rc.id, r.name AS referrer_name, o.invoice_number, rc.order_amount,
                    rc.commission_amount, rc.status, rc.created_at, rc.paid_at
             FROM referral_commissions rc
             JOIN referrers r ON r.id = rc.referrer_id
             JOIN orders o ON o.id = rc.order_id
             $whereSql
             ORDER BY rc.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $columns = ['id','referrer_name','invoice_number','order_amount','commission_amount','status','created_at'];
        break;
    case 'chat_logs':
    default:
        $stmt = $pdo->prepare("SELECT id, LEFT(user_message, 120) AS user_message, LEFT(bot_reply, 120) AS bot_reply, tag, created_at FROM chat_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $columns = ['id', 'user_message', 'bot_reply', 'tag', 'created_at'];
        break;
}
$stmt->bindValue(':limit',  $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,         PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch invite tokens for testimonials tab
$invites = [];
$newToken = null;
if ($table === 'testimonials') {
    $invites = $pdo->query(
        "SELECT id, token, label, created_at, used_at, testimonial_id
         FROM testimonial_invites
         ORDER BY created_at DESC
         LIMIT 50"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Pick up newly generated token from redirect param
    if (!empty($_GET['new_token'])) {
        $newToken = $_GET['new_token'];
    }
}

// Referrers list for commissions filter dropdown
$allReferrers = [];
if ($table === 'commissions') {
    $allReferrers = $pdo->query("SELECT id, name FROM referrers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
}

// Build testimonial base URL — localhost in dev, production domain otherwise
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);
$testimonialBaseUrl = $isLocalhost
    ? 'http://localhost/rielcode-testimonials'
    : 'https://testimonials.rielcode.com';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RielBot Admin Panel</title>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
    <meta name="robots" content="noindex,nofollow">
</head>

<body>
    <?php
    if (!empty($_SESSION['rc_flash'])):
        $flash = $_SESSION['rc_flash'];
        unset($_SESSION['rc_flash']);
    ?>
        <div class="rc-toast rc-toast--<?= htmlspecialchars($flash['variant']) ?>"
             data-auto-dismiss="3000"
             style="position:fixed;top:24px;right:24px;z-index:9600;">
            <span class="rc-toast__icon" aria-hidden="true"></span>
            <span class="rc-toast__msg"><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
    <?php endif; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar-toggle" id="sidebarToggle">☰ Menu</div>

    <div class="wrapper">
        <div class="sidebar">
            <h2>
                <img src="IMG/Rielcode Logo Square Transparent Icon.png" alt="Rielcode" class="sidebar-logo">
                RielBot Admin
            </h2>
            <a href="admin.php?table=chat_logs" class="<?= $table === 'chat_logs'    ? 'active' : '' ?>">Chat Logs</a>
            <a href="admin.php?table=orders" class="<?= $table === 'orders'        ? 'active' : '' ?>">Orders</a>
            <a href="admin.php?table=invoices" class="<?= $table === 'invoices'    ? 'active' : '' ?>">Invoices</a>
            <a href="admin.php?table=packages" class="<?= $table === 'packages'    ? 'active' : '' ?>">Packages</a>
            <a href="admin.php?table=projects" class="<?= $table === 'projects'    ? 'active' : '' ?>">Projects</a>
            <a href="admin.php?table=testimonials" class="<?= $table === 'testimonials' ? 'active' : '' ?>">Testimonials</a>
            <a href="admin.php?table=referrers" class="<?= $table === 'referrers' ? 'active' : '' ?>">Referrers</a>
            <a href="admin.php?table=commissions" class="<?= $table === 'commissions' ? 'active' : '' ?>">Commissions</a>
            <a href="admin_logout.php">Logout</a>
        </div>

        <div class="main-content">
            <h1><?= $table === 'testimonials' ? 'Testimonials' : ucfirst(str_replace('_', ' ', $table)) ?></h1>

            <?php if ($table === 'referrers'): ?>

                <!-- ====== ADD REFERRER FORM ====== -->
                <form method="post" action="admin.php?table=referrers&action=add" style="margin-bottom:24px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Name</label>
                        <input type="text" name="name" required placeholder="Budi Santoso" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Phone (WhatsApp)</label>
                        <input type="text" name="phone" required placeholder="081234567890" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Code</label>
                        <input type="text" name="code" required placeholder="BUDI10" maxlength="20" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Rate (%)</label>
                        <input type="number" name="commission_rate" value="10" min="0" max="100" step="0.01" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;width:80px;">
                    </div>
                    <button type="submit" class="button add" style="margin-bottom:0;">Add Referrer</button>
                </form>

                <!-- ====== REFERRERS TABLE ====== -->
                <?php if (empty($logs)): ?>
                    <p style="color:#475569;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">No referrers yet.</p>
                <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Code</th>
                                <th>Rate</th>
                                <th>Status</th>
                                <th>Total Earned (Paid)</th>
                                <th>Dashboard URL</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['phone']) ?>" target="_blank" style="color:#25d366;">
                                        <?= htmlspecialchars($row['phone']) ?>
                                    </a>
                                </td>
                                <td style="font-family:'JetBrains Mono',monospace;font-weight:600;color:#60a5fa;"><?= htmlspecialchars($row['code']) ?></td>
                                <td><?= number_format((float)$row['commission_rate'], 2) ?>%</td>
                                <td>
                                    <?php $active = $row['status'] === 'active'; ?>
                                    <span style="<?= $active ? 'background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;' : 'background:rgba(239,68,68,0.10);border:1px solid rgba(239,68,68,0.30);color:#f87171;' ?>padding:3px 10px;border-radius:20px;font-size:0.72rem;font-family:'JetBrains Mono',monospace;font-weight:600;">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>Rp<?= number_format((float)$row['total_earned'], 0, ',', '.') ?></td>
                                <td style="font-size:0.72rem;">
                                    <?php
                                    $isLocalDev = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost','127.0.0.1']);
                                    $baseUrl = $isLocalDev ? 'http://localhost/Rielcode/referrer/' : 'https://rielcode.com/referrer/';
                                    $dashUrl = $baseUrl . '?code=' . urlencode($row['code']);
                                    ?>
                                    <a href="<?= htmlspecialchars($dashUrl) ?>" target="_blank" style="color:#60a5fa;"><?= htmlspecialchars($dashUrl) ?></a>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="admin.php?table=referrers&action=toggle&id=<?= $row['id'] ?>"
                                           class="button edit" style="font-size:0.72rem;padding:5px 10px;">
                                            <?= $row['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </a>
                                        <a href="admin.php?table=referrers&action=delete&id=<?= $row['id'] ?>"
                                           data-confirm="Delete this referrer? Only possible if they have no linked commissions."
                                           data-confirm-variant="danger"
                                           data-confirm-title="Delete referrer"
                                           data-confirm-label="Delete"
                                           class="button delete" style="font-size:0.72rem;padding:5px 10px;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php elseif ($table === 'commissions'): ?>

                <!-- ====== COMMISSIONS FILTER ====== -->
                <form method="get" action="admin.php" style="margin-bottom:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                    <input type="hidden" name="table" value="commissions">
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Referrer</label>
                        <select name="referrer_id" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;">
                            <option value="">All</option>
                            <?php foreach ($allReferrers as $ref): ?>
                                <option value="<?= $ref['id'] ?>" <?= $filterReferrerId === $ref['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ref['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.72rem;color:#475569;margin-bottom:4px;">Status</label>
                        <select name="status" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:0.82rem;">
                            <option value="">All</option>
                            <option value="pending"   <?= $filterStatus === 'pending'   ? 'selected' : '' ?>>Pending</option>
                            <option value="paid"      <?= $filterStatus === 'paid'      ? 'selected' : '' ?>>Paid</option>
                            <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="button" style="margin-bottom:0;padding:7px 14px;">Filter</button>
                </form>

                <!-- ====== COMMISSIONS TABLE ====== -->
                <?php if (empty($logs)): ?>
                    <p style="color:#475569;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">No commissions yet.</p>
                <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Referrer</th>
                                <th>Invoice</th>
                                <th>Order Amount</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Paid At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $row): ?>
                            <?php
                            $comColors = [
                                'pending'   => 'background:rgba(251,191,36,0.12);border:1px solid rgba(251,191,36,0.35);color:#fbbf24;',
                                'paid'      => 'background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;',
                                'cancelled' => 'background:rgba(239,68,68,0.10);border:1px solid rgba(239,68,68,0.30);color:#f87171;',
                            ];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['referrer_name']) ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;color:#60a5fa;"><?= htmlspecialchars($row['invoice_number'] ?? '—') ?></td>
                                <td>Rp<?= number_format((float)$row['order_amount'], 0, ',', '.') ?></td>
                                <td style="font-weight:600;color:#4ade80;">Rp<?= number_format((float)$row['commission_amount'], 0, ',', '.') ?></td>
                                <td>
                                    <span style="<?= $comColors[$row['status']] ?? '' ?>padding:3px 10px;border-radius:20px;font-size:0.72rem;font-family:'JetBrains Mono',monospace;font-weight:600;">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:0.78rem;color:#475569;"><?= htmlspecialchars(substr($row['created_at'], 0, 16)) ?></td>
                                <td style="font-size:0.78rem;color:#475569;"><?= $row['paid_at'] ? htmlspecialchars(substr($row['paid_at'], 0, 16)) : '—' ?></td>
                                <td>
                                    <div class="table-actions">
                                        <?php if ($row['status'] === 'pending'): ?>
                                        <a href="admin.php?table=commissions&action=paid&id=<?= $row['id'] ?>"
                                           data-confirm="Mark this commission as paid?"
                                           data-confirm-title="Mark Paid"
                                           data-confirm-label="Mark Paid"
                                           class="button" style="font-size:0.72rem;padding:5px 10px;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;">Mark Paid</a>
                                        <a href="admin.php?table=commissions&action=cancel&id=<?= $row['id'] ?>"
                                           data-confirm="Cancel this commission?"
                                           data-confirm-variant="danger"
                                           data-confirm-title="Cancel commission"
                                           data-confirm-label="Cancel"
                                           class="button delete" style="font-size:0.72rem;padding:5px 10px;">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ($table === 'packages'): ?>
                <a href="admin_edit.php?table=packages" class="button add">Add Package</a>
            <?php endif; ?>

            <?php if ($table === 'testimonials'): ?>

                <!-- ====== TESTIMONIALS TABLE ====== -->
                <?php if (empty($logs)): ?>
                    <p style="color:#475569;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">No testimonials yet.</p>
                <?php else: ?>
                <div class="table-container" style="margin-bottom:32px;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Client</th>
                                <th>Business</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $total_items - $offset; foreach ($logs as $row): ?>
                            <tr>
                                <td><?= $no-- ?></td>
                                <td style="white-space:normal;">
                                    <div style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($row['client_name']) ?></div>
                                    <div style="font-size:0.75rem;color:#475569;"><?= htmlspecialchars($row['role_title']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['business_name']) ?></td>
                                <td>
                                    <span style="color:#ffc73a;letter-spacing:1px;"><?= str_repeat('★', (int)$row['rating']) . str_repeat('☆', 5 - (int)$row['rating']) ?></span>
                                    <span style="color:#475569;font-size:0.75rem;margin-left:4px;"><?= (int)$row['rating'] ?>/5</span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending'  => 'background:rgba(251,191,36,0.12);border:1px solid rgba(251,191,36,0.35);color:#fbbf24;',
                                        'approved' => 'background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;',
                                        'rejected' => 'background:rgba(239,68,68,0.10);border:1px solid rgba(239,68,68,0.30);color:#f87171;',
                                    ];
                                    $s = $row['status'];
                                    $style = $statusColors[$s] ?? '';
                                    ?>
                                    <span style="<?= $style ?>padding:3px 10px;border-radius:20px;font-size:0.72rem;font-family:'JetBrains Mono',monospace;font-weight:600;letter-spacing:0.5px;">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </td>
                                <td style="font-size:0.78rem;color:#475569;white-space:nowrap;"><?= htmlspecialchars(substr($row['submitted_at'], 0, 16)) ?></td>
                                <td>
                                    <div class="table-actions" style="flex-wrap:wrap;gap:5px;">
                                        <button class="button edit" style="font-size:0.72rem;padding:5px 10px;"
                                            onclick="toggleTestimonial(<?= $row['id'] ?>)">Full</button>
                                        <?php if ($row['status'] !== 'approved'): ?>
                                            <a href="?table=testimonials&action=approve&id=<?= $row['id'] ?>"
                                               data-confirm="Approve this testimonial?"
                                               data-confirm-title="Approve testimonial"
                                               data-confirm-label="Approve"
                                               class="button"
                                               style="font-size:0.72rem;padding:5px 10px;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;">Approve</a>
                                        <?php endif; ?>
                                        <?php if ($row['status'] !== 'rejected'): ?>
                                            <a href="?table=testimonials&action=reject&id=<?= $row['id'] ?>"
                                               data-confirm="Reject this testimonial?"
                                               data-confirm-title="Reject testimonial"
                                               data-confirm-label="Reject"
                                               class="button"
                                               style="font-size:0.72rem;padding:5px 10px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#f87171;">Reject</a>
                                        <?php endif; ?>
                                        <a href="?table=testimonials&action=delete&id=<?= $row['id'] ?>"
                                           data-confirm="Permanently delete this testimonial? This cannot be undone."
                                           data-confirm-variant="danger"
                                           data-confirm-title="Delete testimonial"
                                           data-confirm-label="Delete"
                                           class="button delete"
                                           style="font-size:0.72rem;padding:5px 10px;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expandable full detail row -->
                            <tr class="testi-detail" id="testi-detail-<?= $row['id'] ?>" style="display:none;">
                                <td colspan="7" style="background:rgba(255,255,255,0.02);padding:20px 24px;white-space:normal;">
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px 24px;max-width:860px;">
                                        <div>
                                            <div style="font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#3a7cff;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Problem Before</div>
                                            <p style="color:#94a3b8;font-size:0.82rem;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars($row['problem_before'])) ?></p>
                                        </div>
                                        <div>
                                            <div style="font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#3a7cff;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Solution / What Was Built</div>
                                            <p style="color:#94a3b8;font-size:0.82rem;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars($row['solution_after'])) ?></p>
                                        </div>
                                        <div>
                                            <div style="font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#3a7cff;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Recommendation</div>
                                            <p style="color:#94a3b8;font-size:0.82rem;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars($row['recommendation'])) ?></p>
                                        </div>
                                        <div>
                                            <div style="font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#3a7cff;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Meta</div>
                                            <div style="font-size:0.78rem;color:#475569;line-height:1.8;">
                                                <div>Project: <a href="<?= htmlspecialchars($row['project_url']) ?>" target="_blank" style="color:#60a5fa;"><?= htmlspecialchars($row['project_url']) ?></a></div>
                                                <?php if ($row['headline']): ?>
                                                <div>Headline: <em style="color:#94a3b8;">"<?= htmlspecialchars($row['headline']) ?>"</em></div>
                                                <?php endif; ?>
                                                <?php if ($row['client_email']): ?>
                                                <div>Email: <?= htmlspecialchars($row['client_email']) ?></div>
                                                <?php endif; ?>
                                                <div>Consent: <?= $row['consent_given'] ? '<span style="color:#4ade80;">Yes</span>' : '<span style="color:#f87171;">No</span>' ?></div>
                                                <?php if ($row['reviewed_at']): ?>
                                                <div>Reviewed: <?= htmlspecialchars(substr($row['reviewed_at'], 0, 16)) ?></div>
                                                <?php endif; ?>
                                                <div>IP: <?= htmlspecialchars($row['ip_address'] ?? 'unknown') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Pagination -->
                <div class="pagination" style="margin-bottom:48px;">
                    <?php if ($page > 1): ?>
                        <a href="?table=testimonials&page=<?= $page - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?table=testimonials&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?table=testimonials&page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>

                <!-- ====== INVITE TOKEN GENERATOR ====== -->
                <div style="margin-bottom:16px;">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:0.65rem;color:#3a7cff;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:12px;">Invite Token Generator</div>
                    <form method="POST" action="admin.php?table=testimonials&action=gen_token"
                          style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:20px;">
                        <div>
                            <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#475569;letter-spacing:0.8px;text-transform:uppercase;margin-bottom:6px;">Label (optional)</label>
                            <input type="text" name="label" maxlength="200"
                                   placeholder="e.g. Azriel - Rielcode CEO"
                                   style="padding:10px 14px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);border-radius:10px;color:#e2e8f0;font-family:'Outfit',sans-serif;font-size:0.85rem;outline:none;width:280px;">
                        </div>
                        <button type="submit" class="button add" style="margin-bottom:0;height:40px;">Generate Link</button>
                    </form>

                    <?php if (!empty($newToken)): ?>
                    <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <span style="font-family:'JetBrains Mono',monospace;font-size:0.7rem;color:#4ade80;letter-spacing:0.5px;">New link generated:</span>
                        <code id="newTokenUrl" style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;color:#e2e8f0;background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;word-break:break-all;"><?= htmlspecialchars($testimonialBaseUrl) ?>/?t=<?= htmlspecialchars($newToken) ?></code>
                        <button onclick="copyToken()" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;padding:5px 12px;border-radius:8px;cursor:pointer;font-size:0.72rem;font-family:'Outfit',sans-serif;" id="copyTokenBtn">Copy</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Invite token list -->
                <?php if (!empty($invites)): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Token (partial)</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invites as $inv): ?>
                            <tr>
                                <td style="color:#e2e8f0;"><?= htmlspecialchars($inv['label'] ?? '—') ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;"><?= htmlspecialchars(substr($inv['token'], 0, 12)) ?>…</td>
                                <td style="font-size:0.78rem;color:#475569;"><?= htmlspecialchars(substr($inv['created_at'], 0, 16)) ?></td>
                                <td>
                                    <?php if ($inv['used_at']): ?>
                                        <span style="background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;padding:3px 10px;border-radius:20px;font-size:0.7rem;font-family:'JetBrains Mono',monospace;">Used</span>
                                    <?php else: ?>
                                        <span style="background:rgba(251,191,36,0.12);border:1px solid rgba(251,191,36,0.35);color:#fbbf24;padding:3px 10px;border-radius:20px;font-size:0.7rem;font-family:'JetBrains Mono',monospace;">Unused</span>
                                    <?php endif; ?>
                                </td>
                                <td style="display:flex;gap:6px;align-items:center;">
                                    <?php if (!$inv['used_at']): ?>
                                    <button onclick="copyInvite('<?= htmlspecialchars($testimonialBaseUrl) ?>/?t=<?= htmlspecialchars($inv['token']) ?>', this)"
                                            style="background:rgba(58,124,255,0.1);border:1px solid rgba(58,124,255,0.3);color:#60a5fa;padding:5px 12px;border-radius:8px;cursor:pointer;font-size:0.72rem;font-family:'Outfit',sans-serif;font-weight:600;">Copy Link</button>
                                    <a href="admin.php?table=testimonials&action=del_token&token_id=<?= (int)$inv['id'] ?>"
                                       data-confirm="Delete this unused token?"
                                       data-confirm-variant="danger"
                                       data-confirm-title="Delete token"
                                       data-confirm-label="Delete"
                                       style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:5px 12px;border-radius:8px;cursor:pointer;font-size:0.72rem;font-family:'Outfit',sans-serif;font-weight:600;text-decoration:none;">Delete</a>
                                    <?php else: ?>
                                        <span style="color:#1e293b;font-family:'JetBrains Mono',monospace;font-size:0.75rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php elseif ($table === 'projects'): ?>

                <!-- ====== PROJECTS CRUD ====== -->
                <div style="margin-bottom:28px;padding:20px;background:rgba(58,124,255,0.05);border:1px solid rgba(58,124,255,0.2);border-radius:12px;">
                    <h3 style="margin:0 0 14px;color:#e2e8f0;font-size:1.05rem;"><?= $editProject ? 'Edit Project' : 'Add New Project' ?></h3>
                    <form method="POST" enctype="multipart/form-data"
                          action="admin.php?table=projects&action=<?= $editProject ? 'update&id=' . (int)$editProject['id'] : 'add' ?>"
                          class="projects-form"
                          style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProject['image_path'] ?? '') ?>">

                        <div style="grid-column:span 2;">
                            <label>Title</label>
                            <input type="text" name="title" required maxlength="150"
                                   value="<?= htmlspecialchars($editProject['title'] ?? '') ?>">
                        </div>

                        <div style="grid-column:span 2;">
                            <label>Description</label>
                            <textarea name="description" rows="4" required><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label>Project URL</label>
                            <input type="url" name="url"
                                   value="<?= htmlspecialchars($editProject['url'] ?? '') ?>"
                                   placeholder="https://example.com">
                        </div>

                        <div>
                            <label>Tags (comma-separated)</label>
                            <input type="text" name="tags"
                                   value="<?= htmlspecialchars($editProject['tags'] ?? '') ?>"
                                   placeholder="Enterprise, Construction">
                        </div>

                        <div>
                            <label>Layout</label>
                            <select name="layout">
                                <option value="featured" <?= ($editProject['layout'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured (large hero, 1 slot)</option>
                                <option value="side"     <?= ($editProject['layout'] ?? 'side') === 'side' ? 'selected' : '' ?>>Side tile (2 slots)</option>
                            </select>
                        </div>

                        <div>
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" value="<?= (int)($editProject['sort_order'] ?? 0) ?>">
                        </div>

                        <div style="grid-column:span 2;">
                            <label>Project Image</label>
                            <input type="file" name="image" accept="image/*">
                            <?php if (!empty($editProject['image_path'])): ?>
                                <div style="margin-top:8px;font-size:0.75rem;color:#475569;">
                                    Current: <code style="color:#94a3b8;"><?= htmlspecialchars($editProject['image_path']) ?></code>
                                    <img src="<?= htmlspecialchars($editProject['image_path']) ?>" alt="" style="max-width:120px;max-height:80px;display:block;margin-top:6px;border-radius:6px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="grid-column:span 2;display:flex;align-items:center;gap:10px;">
                            <input type="checkbox" name="is_visible" id="is_visible" value="1"
                                   style="width:auto;accent-color:#3a7cff;cursor:pointer;"
                                   <?= ($editProject === null || (int)($editProject['is_visible'] ?? 1) === 1) ? 'checked' : '' ?>>
                            <label for="is_visible" style="text-transform:none;font-size:0.875rem;color:#e2e8f0;letter-spacing:0;cursor:pointer;">Visible on public site</label>
                        </div>

                        <div style="grid-column:span 2;display:flex;gap:10px;align-items:center;">
                            <button type="submit" class="button add" style="margin-bottom:0;"><?= $editProject ? 'Save Changes' : 'Add Project' ?></button>
                            <?php if ($editProject): ?>
                                <a href="admin.php?table=projects" class="button edit">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if (empty($logs)): ?>
                    <p style="color:#475569;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">No projects yet — add one above.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Layout</th>
                                    <th>Order</th>
                                    <th>Visible</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; foreach ($logs as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <?php if (!empty($row['image_path'])): ?>
                                                <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="" style="width:60px;height:42px;object-fit:cover;border-radius:6px;">
                                            <?php else: ?>
                                                <span style="color:#475569;font-size:0.75rem;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space:normal;color:#e2e8f0;font-weight:600;"><?= htmlspecialchars($row['title']) ?></td>
                                        <td>
                                            <span style="<?= $row['layout'] === 'featured' ? 'background:rgba(58,124,255,0.15);color:#60a5fa;border:1px solid rgba(58,124,255,0.35);' : 'background:rgba(62,207,142,0.12);color:#4ade80;border:1px solid rgba(62,207,142,0.35);' ?>padding:3px 10px;border-radius:20px;font-size:0.7rem;font-family:'JetBrains Mono',monospace;">
                                                <?= htmlspecialchars($row['layout']) ?>
                                            </span>
                                        </td>
                                        <td><?= (int)$row['sort_order'] ?></td>
                                        <td><?= (int)$row['is_visible'] ? '<span style="color:#4ade80;">●</span> Yes' : '<span style="color:#f87171;">●</span> No' ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="?table=projects&action=edit&id=<?= $row['id'] ?>" class="button edit">Edit</a>
                                                <a href="?table=projects&action=delete&id=<?= $row['id'] ?>"
                                                   data-confirm="Delete this project permanently?"
                                                   data-confirm-variant="danger"
                                                   data-confirm-title="Delete project"
                                                   data-confirm-label="Delete"
                                                   class="button delete">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?table=projects&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($table === 'invoices'): ?>

                <!-- ====== INVOICES MANAGEMENT ====== -->
                <p style="color:#94a3b8;margin-bottom:20px;">Generate, edit, download, and send invoices for orders. Click an order to manage its invoice.</p>
                <?php if (empty($logs)): ?>
                    <p style="color:#475569;font-family:'JetBrains Mono',monospace;font-size:0.8rem;">No orders yet.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Order</th>
                                    <th>Package</th>
                                    <th>Invoice #</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $total_items - $offset; foreach ($logs as $row): ?>
                                    <?php
                                    $invStatus = $row['invoice_status'] ?? 'draft';
                                    $statusStyles = [
                                        'draft' => 'background:rgba(148,163,184,0.15);color:#94a3b8;border:1px solid rgba(148,163,184,0.35);',
                                        'sent'  => 'background:rgba(58,124,255,0.15);color:#60a5fa;border:1px solid rgba(58,124,255,0.35);',
                                        'paid'  => 'background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.35);',
                                        'void'  => 'background:rgba(239,68,68,0.10);color:#f87171;border:1px solid rgba(239,68,68,0.30);',
                                    ];
                                    $amount = $row['invoice_amount'] !== null ? (float)$row['invoice_amount'] : (float)$row['final_price'];
                                    $cur = $row['invoice_currency'] ?? 'IDR';
                                    $amtStr = $cur === 'USD' ? '$' . number_format($amount, 2) : 'Rp' . number_format($amount, 0, ',', '.');
                                    ?>
                                    <tr>
                                        <td><?= $no-- ?></td>
                                        <td style="white-space:normal;">
                                            <div style="font-weight:600;color:#e2e8f0;"><?= htmlspecialchars($row['order_name']) ?></div>
                                            <div style="font-size:0.75rem;color:#475569;"><?= htmlspecialchars($row['email']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['package']) ?></td>
                                        <td style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;color:#94a3b8;"><?= htmlspecialchars($row['invoice_number'] ?: '—') ?></td>
                                        <td>
                                            <span style="<?= $statusStyles[$invStatus] ?? '' ?>padding:3px 10px;border-radius:20px;font-size:0.72rem;font-family:'JetBrains Mono',monospace;font-weight:600;text-transform:uppercase;">
                                                <?= htmlspecialchars($invStatus) ?>
                                            </span>
                                        </td>
                                        <td style="font-family:'JetBrains Mono',monospace;font-size:0.82rem;color:#e2e8f0;"><?= $amtStr ?></td>
                                        <td style="font-size:0.78rem;color:#475569;white-space:nowrap;"><?= htmlspecialchars(substr($row['created_at'], 0, 16)) ?></td>
                                        <td>
                                            <div class="table-actions" style="flex-wrap:wrap;gap:5px;">
                                                <a href="admin_invoice_edit.php?id=<?= $row['id'] ?>" class="button edit" style="font-size:0.72rem;padding:5px 10px;">Edit Invoice</a>
                                                <?php if (!empty($row['invoice_file'])): ?>
                                                    <a href="<?= htmlspecialchars(ltrim(str_replace('../','', $row['invoice_file']), '/')) ?>"
                                                       download class="button" style="font-size:0.72rem;padding:5px 10px;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.35);color:#4ade80;">Download</a>
                                                <?php endif; ?>
                                                <a href="admin_invoice_edit.php?id=<?= $row['id'] ?>&send=1"
                                                   data-confirm="Regenerate PDF and send invoice email?"
                                                   data-confirm-title="Send invoice"
                                                   data-confirm-label="Send"
                                                   class="button" style="font-size:0.72rem;padding:5px 10px;background:rgba(58,124,255,0.12);border:1px solid rgba(58,124,255,0.35);color:#60a5fa;">Send</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?table=invoices&page=<?= $page - 1 ?>">&laquo; Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?table=invoices&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?table=invoices&page=<?= $page + 1 ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php elseif (!in_array($table, ['referrers', 'commissions'], true)): ?>
            <!-- ====== ORIGINAL TABLES (chat_logs / orders / packages) ====== -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($columns as $index => $col): ?>
                                <th><?= $index === 0 ? 'No' : ucfirst(str_replace('_', ' ', $col)) ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = in_array($table, ['chat_logs', 'orders'])
                            ? $total_items - $offset
                            : $offset + 1;

                        foreach ($logs as $row):
                        ?>
                            <tr>
                                <?php foreach ($columns as $index => $col): ?>
                                    <?php if ($index === 0): ?>
                                        <td><?= in_array($table, ['chat_logs', 'orders']) ? $no-- : $no++ ?></td>
                                    <?php else: ?>
                                        <td>
                                            <?php
                                            if ($table === 'packages' && $col === 'idr_price') {
                                                echo 'IDR ' . number_format($row[$col], 0, ',', '.');
                                            } elseif ($table === 'packages' && $col === 'us_price') {
                                                echo '$' . number_format($row[$col], 2, '.', ',');
                                            } elseif ($table === 'orders' && $col === 'invoice_file') {
                                                if ($row['status'] === 'Pending' || empty($row[$col])) {
                                                    echo "No File";
                                                } else {
                                                    $filePath = ltrim(str_replace('../', '', $row[$col]), '/');
                                                    echo '<a href="' . htmlspecialchars($filePath) . '" download="Invoice" class="button">Download</a>';
                                                }
                                            } elseif ($table === 'chat_logs' && in_array($col, ['user_message', 'bot_reply'])) {
                                                $uid     = 'msg-' . $row['id'] . '-' . $col;
                                                $preview = htmlspecialchars(mb_substr((string)$row[$col], 0, 40));
                                                $full    = htmlspecialchars((string)$row[$col]);
                                                $label   = $col === 'user_message' ? '👤' : '🤖';
                                                echo '
                                                <div class="expandable-cell">
                                                    <span class="msg-label">' . $label . '</span>
                                                    <span class="msg-preview" id="' . $uid . '-preview">' . $preview . (mb_strlen((string)$row[$col]) > 40 ? '…' : '') . '</span>
                                                    <span class="msg-full" id="' . $uid . '-full" style="display:none;">' . $full . '</span>
                                                    ' . (mb_strlen((string)$row[$col]) > 40 ? '<button class="expand-btn" onclick="toggleMsg(\'' . $uid . '\', this)">▼ More</button>' : '') . '
                                                </div>';
                                            } else {
                                                echo htmlspecialchars((string)$row[$col]);
                                            }
                                            ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td>
                                    <?php if ($table === 'chat_logs'): ?>
                                        <span class="no-actions">—</span>
                                    <?php else: ?>
                                        <div class="table-actions">
                                            <a href="admin_edit.php?table=<?= $table ?>&id=<?= $row['id'] ?>" class="button edit">Edit</a>
                                            <?php if ($table === 'packages'): ?>
                                                <a href="?table=<?= $table ?>&action=delete&id=<?= $row['id'] ?>"
                                                    data-confirm="Are you sure you want to delete this record?"
                                                    data-confirm-variant="danger"
                                                    data-confirm-title="Delete record"
                                                    data-confirm-label="Delete"
                                                    class="button delete">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?table=<?= $table ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?table=<?= $table ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?table=<?= $table ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        (function() {
            const toggle = document.getElementById('sidebarToggle');
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

            toggle.addEventListener('click', function() {
                sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
            });

            overlay.addEventListener('click', closeSidebar);
        })();

        function toggleMsg(uid, btn) {
            const preview = document.getElementById(uid + '-preview');
            const full = document.getElementById(uid + '-full');
            const isOpen = full.style.display !== 'none';
            preview.style.display = isOpen ? 'inline' : 'none';
            full.style.display = isOpen ? 'none' : 'inline';
            btn.textContent = isOpen ? '▼ More' : '▲ Less';
        }

        function toggleTestimonial(id) {
            const row = document.getElementById('testi-detail-' + id);
            if (!row) return;
            const isOpen = row.style.display !== 'none';
            row.style.display = isOpen ? 'none' : 'table-row';
            const btn = row.previousElementSibling.querySelector('button.button.edit');
            if (btn) btn.textContent = isOpen ? 'Full' : 'Hide';
        }

        function copyToken() {
            const el = document.getElementById('newTokenUrl');
            if (!el) return;
            navigator.clipboard.writeText(el.textContent.trim()).then(() => {
                const btn = document.getElementById('copyTokenBtn');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 2000);
            });
        }

        function copyInvite(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy Link', 2000);
            });
        }
    </script>
    <style>
        .expandable-cell {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 320px;
        }

        .msg-label {
            font-size: 0.65rem;
            font-family: 'JetBrains Mono', monospace;
            color: #475569;
            letter-spacing: 0.5px;
        }

        .msg-preview,
        .msg-full {
            font-size: 0.82rem;
            color: #94a3b8;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg-full {
            color: #e2e8f0;
        }

        .expand-btn {
            align-self: flex-start;
            background: none;
            border: 1px solid rgba(58, 124, 255, 0.25);
            border-radius: 6px;
            color: #3a7cff;
            font-size: 0.68rem;
            font-family: 'JetBrains Mono', monospace;
            padding: 2px 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 2px;
        }

        .expand-btn:hover {
            background: rgba(58, 124, 255, 0.08);
            border-color: #3a7cff;
        }

        .no-actions {
            color: #334155;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
        }
    </style>
    <script src="JS/admin-ui.js" defer></script>
</body>

</html>
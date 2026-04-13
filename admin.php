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

// BUG FIX: whitelist $table to prevent SQL injection via GET parameter
$allowedTables = ['chat_logs', 'orders', 'packages'];
$table = in_array($_GET['table'] ?? '', $allowedTables) ? $_GET['table'] : 'chat_logs';

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// --- Handle Delete (orders & packages only) ---
if ($action === 'delete' && $id && $table !== 'chat_logs') {
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
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        // Recalculate order counts
        $pdo->exec("UPDATE packages p SET orders = (SELECT COUNT(*) FROM orders o WHERE o.package_id = p.id)");

    } elseif ($table === 'packages') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE package_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$id]);
        }
    } elseif ($table === 'chat_logs') {
        $stmt = $pdo->prepare("DELETE FROM chat_logs WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php?table=$table");
    exit;
}

// --- Pagination ---
$items_per_page = 10;
$page           = max(1, (int)($_GET['page'] ?? 1));
$offset         = ($page - 1) * $items_per_page;

// BUG FIX: table name is now whitelisted above, safe to interpolate
$total_items = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
$total_pages = max(1, (int)ceil($total_items / $items_per_page));

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
    case 'chat_logs':
    default:
        $stmt = $pdo->prepare("SELECT * FROM chat_logs ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $columns = ['id', 'user_message', 'bot_reply', 'tag', 'created_at'];
        break;
}
$stmt->bindValue(':limit',  $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,         PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar-toggle" id="sidebarToggle">☰ Menu</div>

    <div class="wrapper">
        <div class="sidebar">
            <h2>
                <img src="IMG/Rielcode Logo Square Transparent Icon.png" alt="Rielcode" class="sidebar-logo">
                RielBot Admin
            </h2>
            <a href="admin.php?table=chat_logs" class="<?= $table === 'chat_logs' ? 'active' : '' ?>">Chat Logs</a>
            <a href="admin.php?table=orders"    class="<?= $table === 'orders'    ? 'active' : '' ?>">Orders</a>
            <a href="admin.php?table=packages"  class="<?= $table === 'packages'  ? 'active' : '' ?>">Packages</a>
            <a href="admin_logout.php">Logout</a>
        </div>

        <div class="main-content">
            <h1><?= ucfirst(str_replace('_', ' ', $table)) ?></h1>

            <?php if ($table === 'packages'): ?>
                <a href="admin_edit.php?table=packages" class="button add">Add Package</a>
            <?php endif; ?>

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
                            ? $total_items - $offset   // descending numbering
                            : $offset + 1;             // ascending numbering

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
                                                $preview = htmlspecialchars(mb_substr((string)$row[$col], 0, 60));
                                                $full    = htmlspecialchars((string)$row[$col]);
                                                $label   = $col === 'user_message' ? '👤' : '🤖';
                                                echo '
                                                <div class="expandable-cell">
                                                    <span class="msg-label">' . $label . '</span>
                                                    <span class="msg-preview" id="' . $uid . '-preview">' . $preview . (mb_strlen((string)$row[$col]) > 60 ? '…' : '') . '</span>
                                                    <span class="msg-full" id="' . $uid . '-full" style="display:none;">' . $full . '</span>
                                                    ' . (mb_strlen((string)$row[$col]) > 60 ? '<button class="expand-btn" onclick="toggleMsg(\'' . $uid . '\', this)">▼ More</button>' : '') . '
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
                                        <a href="?table=<?= $table ?>&action=delete&id=<?= $row['id'] ?>"
                                           onclick="return confirm('Are you sure you want to delete this record?');"
                                           class="button delete">Delete</a>
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

    function toggleMsg(uid, btn) {
        const preview = document.getElementById(uid + '-preview');
        const full    = document.getElementById(uid + '-full');
        const isOpen  = full.style.display !== 'none';
        preview.style.display = isOpen ? 'inline' : 'none';
        full.style.display    = isOpen ? 'none'   : 'inline';
        btn.textContent       = isOpen ? '▼ More' : '▲ Less';
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
    .msg-preview, .msg-full {
        font-size: 0.82rem;
        color: #94a3b8;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .msg-full { color: #e2e8f0; }
    .expand-btn {
        align-self: flex-start;
        background: none;
        border: 1px solid rgba(58,124,255,0.25);
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
        background: rgba(58,124,255,0.08);
        border-color: #3a7cff;
    }
    .no-actions {
        color: #334155;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
    }
    </style>
</body>
</html>
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

// --- Connect to DB ---
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . htmlspecialchars($e->getMessage()));
}

// BUG FIX: whitelist $table
$allowedTables = ['orders', 'packages'];
$table = in_array($_GET['table'] ?? '', $allowedTables) ? $_GET['table'] : 'packages';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = $_GET['action'] ?? '';
$errors = [];
$success = '';

// --- Handle Delete from edit view ---
if ($action === 'delete' && $id) {
    if ($table === 'orders') {
        $stmt = $pdo->prepare("SELECT invoice_file FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['invoice_file'])) {
            $filePath = ltrim(str_replace('../', '', $row['invoice_file']), '/');
            if (file_exists($filePath)) unlink($filePath);
        }
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->exec("UPDATE packages p SET orders = (SELECT COUNT(*) FROM orders o WHERE o.package_id = p.id)");
    } elseif ($table === 'packages') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE package_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $errors[] = "Cannot delete package — it has existing orders.";
        }
    }
    if (empty($errors)) {
        header("Location: admin.php?table=$table");
        exit;
    }
}

// --- Handle POST save ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($table === 'packages') {
        $pkg_name  = trim($_POST['package_name'] ?? '');
        $idr_price = (int)($_POST['idr_price'] ?? 0);
        $us_price  = (float)($_POST['us_price'] ?? 0);

        if (empty($pkg_name)) $errors[] = "Package name is required.";
        if ($idr_price <= 0)  $errors[] = "IDR price must be greater than 0.";
        if ($us_price <= 0)   $errors[] = "USD price must be greater than 0.";

        if (empty($errors)) {
            if ($id) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE packages SET package_name = ?, idr_price = ?, us_price = ? WHERE id = ?");
                $stmt->execute([$pkg_name, $idr_price, $us_price, $id]);
                $success = "Package updated successfully.";
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO packages (package_name, idr_price, us_price, orders) VALUES (?, ?, ?, 0)");
                $stmt->execute([$pkg_name, $idr_price, $us_price]);
                $id = (int)$pdo->lastInsertId();
                $success = "Package created successfully.";
            }
        }
    } elseif ($table === 'orders') {
        $status = trim($_POST['status'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $allowedStatuses = ['Pending', 'On Progress', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowedStatuses)) $errors[] = "Invalid status.";

        if (empty($errors) && $id) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, description = ? WHERE id = ?");
            $stmt->execute([$status, $description, $id]);
            $success = "Order updated successfully.";
        }
    }
}

// --- Load existing data ---
$data = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        header("Location: admin.php?table=$table");
        exit;
    }
}

$pageTitle = $id ? 'Edit' : 'Add';
$pageTitle .= ' ' . ($table === 'packages' ? 'Package' : 'Order');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | RielBot Admin</title>
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
            <a href="admin.php?table=chat_logs">Chat Logs</a>
            <a href="admin.php?table=orders" class="<?= $table === 'orders' ? 'active' : '' ?>">Orders</a>
            <a href="admin.php?table=packages" class="<?= $table === 'packages' ? 'active' : '' ?>">Packages</a>
            <a href="admin_logout.php">Logout</a>
        </div>

        <div class="main-content">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>

            <a href="admin.php?table=<?= $table ?>" class="button edit" style="margin-bottom: 20px; display: inline-flex;">← Back to <?= ucfirst($table) ?></a>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $err): ?>
                        <div><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="errors" style="background: rgba(62,207,142,0.08); border-color: rgba(62,207,142,0.25); color: #3ecf8e;">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- ══════════════════════════════════════════
                 PACKAGE EDIT FORM
            ══════════════════════════════════════════ -->
            <?php if ($table === 'packages'): ?>
                <div class="form-card">
                    <form method="post">
                        <div>
                            <label for="package_name">Package Name</label>
                            <input type="text" id="package_name" name="package_name"
                                   value="<?= htmlspecialchars($data['package_name'] ?? '') ?>"
                                   placeholder="e.g. Pro Plan" required>
                        </div>
                        <div class="form-row">
                            <div>
                                <label for="idr_price">IDR Price</label>
                                <input type="number" id="idr_price" name="idr_price"
                                       value="<?= htmlspecialchars($data['idr_price'] ?? '') ?>"
                                       placeholder="e.g. 1999000" required min="1">
                            </div>
                            <div>
                                <label for="us_price">USD Price</label>
                                <input type="number" id="us_price" name="us_price" step="0.01"
                                       value="<?= htmlspecialchars($data['us_price'] ?? '') ?>"
                                       placeholder="e.g. 119.99" required min="0.01">
                            </div>
                        </div>

                        <?php if ($data): ?>
                            <div>
                                <label>Total Orders</label>
                                <input type="text" value="<?= (int)($data['orders'] ?? 0) ?>" disabled
                                       style="color: #64748b; cursor: not-allowed;">
                            </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" class="button" style="background: linear-gradient(135deg, #3a7cff, #5b52f5); color: #fff; box-shadow: 0 4px 14px rgba(58,124,255,0.3);">
                                <?= $id ? 'Save Changes' : 'Create Package' ?>
                            </button>
                            <a href="admin.php?table=packages" class="button edit">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- ══════════════════════════════════════════
                 ORDER EDIT / DETAIL VIEW
            ══════════════════════════════════════════ -->
            <?php if ($table === 'orders' && $data): ?>
                <div class="form-card" style="max-width: 720px;">

                    <!-- Order info (read-only) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                        <div>
                            <label>Order Name</label>
                            <input type="text" value="<?= htmlspecialchars($data['order_name']) ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="text" value="<?= htmlspecialchars($data['email']) ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="text" value="<?= htmlspecialchars($data['phone_number']) ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Package</label>
                            <input type="text" value="<?= htmlspecialchars($data['package']) ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Owns Domain</label>
                            <input type="text" value="<?= htmlspecialchars($data['owns_domain'] ?? 'N/A') ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Owns Hosting</label>
                            <input type="text" value="<?= htmlspecialchars($data['owns_hosting'] ?? 'N/A') ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Package Price</label>
                            <input type="text" value="IDR <?= number_format((int)($data['package_price'] ?? 0), 0, ',', '.') ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Final Price</label>
                            <input type="text" value="IDR <?= number_format((int)($data['final_price'] ?? 0), 0, ',', '.') ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                        <div>
                            <label>Invoice</label>
                            <?php if (!empty($data['invoice_file']) && $data['status'] !== 'Pending'): ?>
                                <?php $filePath = ltrim(str_replace('../', '', $data['invoice_file']), '/'); ?>
                                <a href="<?= htmlspecialchars($filePath) ?>" download class="button edit" style="display: inline-flex; margin-top: 6px;">Download Invoice</a>
                            <?php else: ?>
                                <input type="text" value="No invoice" disabled style="color: #64748b; cursor: not-allowed;">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label>Created At</label>
                            <input type="text" value="<?= htmlspecialchars($data['created_at'] ?? '') ?>" disabled style="color: #64748b; cursor: not-allowed;">
                        </div>
                    </div>

                    <!-- Editable fields -->
                    <form method="post">
                        <div class="form-row">
                            <div>
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    <?php
                                    $statuses = ['Pending', 'On Progress', 'Completed', 'Cancelled'];
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?= $st ?>" <?= ($data['status'] ?? '') === $st ? 'selected' : '' ?>>
                                            <?= $st ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="description">Description / Notes</label>
                            <textarea id="description" name="description" rows="4"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button" style="background: linear-gradient(135deg, #3a7cff, #5b52f5); color: #fff; box-shadow: 0 4px 14px rgba(58,124,255,0.3);">
                                Save Changes
                            </button>
                            <a href="admin.php?table=orders" class="button edit">Cancel</a>
                            <a href="admin_edit.php?table=orders&id=<?= $data['id'] ?>&action=delete"
                               onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');"
                               class="button delete" style="margin-left: auto;">
                                Delete Order
                            </a>
                        </div>
                    </form>
                </div>

                <!-- ═══════════════════════════════════════════
                     AI Reply Draft Section
                ════════════════════════════════════════════ -->
                <div style="margin-top: 32px;">
                    <h2 style="font-family: 'JetBrains Mono', monospace; font-size: 0.68rem; color: #3a7cff; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 16px;">
                        ⬡ AI Draft Reply
                    </h2>

                    <div class="form-card" style="max-width: 720px;">
                        <!-- Order context strip -->
                        <div class="ai-reply-context" id="ai-reply-context">
                            <strong>#<?= $data['id'] ?></strong> — <?= htmlspecialchars($data['order_name']) ?>
                            | <strong><?= htmlspecialchars($data['package']) ?></strong>
                            | <?= htmlspecialchars($data['status']) ?>
                            | <?= htmlspecialchars($data['email']) ?>
                        </div>

                        <!-- Tab switcher -->
                        <div class="reply-tabs" style="margin-top: 16px;">
                            <div class="reply-tab active" onclick="rielSwitchTab('whatsapp', this)">💬 WhatsApp</div>
                            <div class="reply-tab" onclick="rielSwitchTab('email', this)">✉ Email</div>
                            <div class="reply-tab" onclick="rielSwitchTab('followup', this)">🔔 Follow-up</div>
                        </div>

                        <!-- Draft text box -->
                        <div class="reply-box empty" id="ai-reply-box" style="margin-top: 12px;">
                            Klik "Generate Draft" untuk generate pesan…
                        </div>

                        <!-- Action buttons -->
                        <div class="reply-actions" style="margin-top: 12px;">
                            <button class="ai-copy-btn" onclick="rielCopyReply()">📋 Copy</button>
                            <button class="ai-wa-btn" onclick="rielOpenWA()">📲 WhatsApp</button>
                            <button class="ai-gen-reply-btn" id="ai-gen-reply-btn" onclick="rielGenerateReply()">
                                <div class="spin"></div>
                                <span class="btn-txt">⬡ Generate Draft</span>
                            </button>
                        </div>
                        <p class="ai-reply-note" style="margin-top: 8px;">⬡ &nbsp;Draft disesuaikan dengan data order — edit sebelum kirim</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    /* ── Sidebar toggle ── */
    (function () {
        const toggle  = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar()  { sidebar.classList.add('active');    overlay.classList.add('active'); }
        function closeSidebar() { sidebar.classList.remove('active'); overlay.classList.remove('active'); }

        toggle.addEventListener('click', () => sidebar.classList.contains('active') ? closeSidebar() : openSidebar());
        overlay.addEventListener('click', closeSidebar);
    })();

    <?php if ($table === 'orders' && $data): ?>
    /* ═══════════════════════════════════════════
       AI Reply Draft Logic (improved)
    ══════════════════════════════════════════ */
    const isLocal = ["localhost", "127.0.0.1"].includes(window.location.hostname);
    const PROXY_URL = isLocal
        ? window.location.origin + '/' + window.location.pathname.split('/').filter(Boolean)[0] + '/proxy.php'
        : window.location.origin + '/proxy';

    const orderData = <?= json_encode([
        'id'          => $data['id'],
        'name'        => $data['order_name'],
        'package'     => $data['package'],
        'status'      => $data['status'],
        'email'       => $data['email'],
        'phone'       => $data['phone_number'],
        'domain'      => $data['owns_domain'] ?? 'N/A',
        'hosting'     => $data['owns_hosting'] ?? 'N/A',
        'description' => $data['description'],
        'price'       => 'IDR ' . number_format((int)($data['final_price'] ?? 0), 0, ',', '.'),
    ]) ?>;

    let currentTab  = 'whatsapp';
    let replyDrafts = {};

    function rielSwitchTab(tab, el) {
        document.querySelectorAll('.reply-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        currentTab = tab;

        const box = document.getElementById('ai-reply-box');
        if (replyDrafts[tab]) {
            box.classList.remove('empty');
            box.textContent = replyDrafts[tab];
        } else {
            box.classList.add('empty');
            box.textContent = 'Klik "Generate Draft" untuk generate pesan…';
        }
    }

    async function rielGenerateReply() {
        const btn = document.getElementById('ai-gen-reply-btn');
        const box = document.getElementById('ai-reply-box');

        btn.classList.add('loading');
        box.classList.remove('empty');
        box.textContent = 'Generating draft…';

        const o = orderData;
        const prompt = `Kamu adalah admin tim Rielcode yang sedang membalas klien. Buat draft balasan profesional berdasarkan data order berikut.

Data order:
- ID Order: #${o.id}
- Nama klien: ${o.name}
- Paket: ${o.package}
- Harga: ${o.price}
- Status: ${o.status}
- Email: ${o.email}
- Domain: ${o.domain}
- Hosting: ${o.hosting}
- Kebutuhan klien: ${o.description || 'Tidak ada catatan khusus'}

Buat 3 versi balasan:
1. WhatsApp: casual, pakai emoji, max 5 baris. Sapa nama klien. Sebutkan paket dan harga.
2. Email: formal, baris pertama HARUS format "Subject: [judul email]". Max 8 baris. Include info paket dan harga.
3. Follow-up: reminder 3 hari kemudian, friendly, max 4 baris. Sebutkan paket yang diorder.

PENTING: Sesuaikan nada balasan berdasarkan status:
- Pending: konfirmasi pesanan masuk, jelaskan langkah selanjutnya
- On Progress: update progress pengerjaan
- Completed: selesai, minta feedback
- Cancelled: konfirmasi pembatalan, tawarkan bantuan di masa depan

Balas HANYA dengan JSON valid:
{"whatsapp":"teks WA","email":"teks email","followup":"teks follow-up"}`;

        try {
            const res = await fetch(PROXY_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: prompt })
            });
            const data = await res.json();
            const text = (data.reply || '{}').replace(/```json|```/g, '').trim();
            const parsed = JSON.parse(text);
            replyDrafts = {
                whatsapp: parsed.whatsapp || '',
                email:    parsed.email    || '',
                followup: parsed.followup || ''
            };
        } catch (e) {
            // Improved contextual fallbacks
            const statusMsg = {
                'Pending':     'Kami sudah terima pesananmu. Tim kami akan segera memproses pesanan ini.',
                'On Progress': 'Pesananmu sedang dalam proses pengerjaan oleh tim kami.',
                'Completed':   'Pesananmu sudah selesai! Kami harap kamu puas dengan hasilnya.',
                'Cancelled':   'Pesananmu telah dibatalkan. Kami memahami keputusanmu.'
            };
            const sMsg = statusMsg[o.status] || statusMsg['Pending'];

            replyDrafts = {
                whatsapp: `Halo ${o.name}! 👋\n\nTerima kasih sudah order ${o.package} (${o.price}) di Rielcode! 🎉\n\n${sMsg}\n\nAda pertanyaan? Chat aja di sini ya! 😊\n\n— Tim Rielcode`,
                email:    `Subject: ${o.status === 'Completed' ? 'Pesanan Selesai' : 'Konfirmasi Order'} ${o.package} — Rielcode\n\nYth. ${o.name},\n\nTerima kasih telah mempercayakan pembuatan website kepada Rielcode.\n\nPesanan: ${o.package} (${o.price})\n${sMsg}\n\nHormat kami,\nTim Rielcode\ninfo@rielcode.com`,
                followup: `Halo ${o.name}! 😊\n\nHanya ingin follow-up terkait pesanan ${o.package} (${o.price}) kamu.\n\n${sMsg}\n\nJangan ragu untuk chat ya! 🚀\n\n— Rielcode Team`
            };
        }

        box.textContent = replyDrafts[currentTab] || '';
        box.classList.remove('empty');
        btn.classList.remove('loading');
    }

    function rielCopyReply() {
        const box = document.getElementById('ai-reply-box');
        if (!box.classList.contains('empty') && box.textContent.trim()) {
            navigator.clipboard.writeText(box.textContent).then(() => {
                const btn = document.querySelector('.ai-copy-btn');
                const orig = btn.textContent;
                btn.textContent = '✓ Tersalin!';
                setTimeout(() => btn.textContent = orig, 1800);
            });
        }
    }

    function rielOpenWA() {
        let phone = (orderData.phone || '').replace(/\D/g, '');
        // Normalize Indonesian numbers: 08xxx → 628xxx
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }
        // If no country code prefix, assume Indonesia
        if (phone.length > 0 && !phone.startsWith('62')) {
            phone = '62' + phone;
        }
        const text = replyDrafts.whatsapp || '';
        if (phone) {
            const url = text
                ? 'https://wa.me/' + phone + '?text=' + encodeURIComponent(text)
                : 'https://wa.me/' + phone;
            window.open(url, '_blank');
        } else {
            alert('Nomor telepon tidak tersedia untuk order ini.');
        }
    }
    <?php endif; ?>
    </script>
</body>
</html>
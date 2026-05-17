<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/error_codes.php';

try {
    $pdo = rc_pdo();
} catch (Throwable $e) {
    error_log('[RC-DB-001] admin_audit: ' . $e->getMessage());
    include __DIR__ . '/inc/_db_down.php';
}

// --- Filters ---
$fEvent    = trim($_GET['event']    ?? '');
$fSeverity = trim($_GET['severity'] ?? '');
$fActor    = trim($_GET['actor']    ?? '');
$fFrom     = trim($_GET['from']     ?? '');
$fTo       = trim($_GET['to']       ?? '');
$page      = max(1, (int)($_GET['p'] ?? 1));
$perPage   = 50;
$offset    = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($fEvent !== '')    { $where[] = 'event_code LIKE ?';  $params[] = $fEvent . '%'; }
if ($fSeverity !== '' && in_array($fSeverity, ['info','warn','error'], true)) {
    $where[] = 'severity = ?'; $params[] = $fSeverity;
}
if ($fActor !== '')    { $where[] = 'actor LIKE ?';       $params[] = '%' . $fActor . '%'; }
if ($fFrom !== '')     { $where[] = 'created_at >= ?';    $params[] = $fFrom . ' 00:00:00'; }
if ($fTo !== '')       { $where[] = 'created_at <= ?';    $params[] = $fTo   . ' 23:59:59'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// --- CSV export ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->prepare("SELECT id, event_code, severity, actor, ip_address, ref_table, ref_id, message, meta, created_at
                           FROM audit_log $whereSql ORDER BY id DESC LIMIT 5000");
    $stmt->execute($params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit_log_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','event_code','severity','actor','ip_address','ref_table','ref_id','message','meta','created_at']);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, $r);
    fclose($out);
    exit;
}

// --- Count + page ---
$cnt = $pdo->prepare("SELECT COUNT(*) FROM audit_log $whereSql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM audit_log $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pages = max(1, (int)ceil($total / $perPage));

function rc_qs(array $overrides = []): string {
    $q = array_merge($_GET, $overrides);
    return http_build_query($q);
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log — RielBot Admin</title>
    <?php include __DIR__ . '/inc/admin_theme_head.php'; ?>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
    <meta name="robots" content="noindex,nofollow">
    <style>
        .audit-count {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-subtle);
            letter-spacing: 0.05em;
            margin-left: 6px;
        }

        .audit-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            align-items: end;
            background: var(--bg-elev);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px;
            margin-bottom: 18px;
        }
        .audit-filters label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 0;
        }
        .audit-actions {
            display: flex;
            gap: 8px;
            grid-column: 1 / -1;
            flex-wrap: wrap;
        }
        .audit-actions .button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--accent-fg);
        }
        .audit-actions .button.primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
        }
        .audit-actions .button.ghost {
            background: transparent;
            border-color: var(--border);
            color: var(--text-muted);
        }
        .audit-actions .button.ghost:hover {
            background: var(--bg-hover);
            color: var(--text);
            border-color: var(--border-strong);
        }

        table.audit { width: 100%; }
        table.audit td {
            white-space: normal;
            max-width: none;
            vertical-align: top;
        }
        table.audit td code {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text);
            background: var(--bg-sunken);
            border: 1px solid var(--border);
            padding: 2px 8px;
            border-radius: var(--radius);
        }
        table.audit td.time {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
        }
        table.audit td.id {
            font-family: var(--font-mono);
            color: var(--text-subtle);
            font-size: 12px;
        }

        .sev-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 10px;
            border-radius: var(--radius-pill);
            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .sev-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .sev-badge.sev-info {
            background: var(--info-bg);
            border-color: var(--info-bd);
            color: var(--info);
        }
        .sev-badge.sev-warn {
            background: var(--warn-bg);
            border-color: var(--warn-bd);
            color: var(--warn);
        }
        .sev-badge.sev-error {
            background: var(--err-bg);
            border-color: var(--err-bd);
            color: var(--err);
        }

        .meta-cell details {
            font-family: var(--font-mono);
            font-size: 12px;
        }
        .meta-cell summary {
            cursor: pointer;
            color: var(--link);
            user-select: none;
            padding: 2px 8px;
            border-radius: var(--radius);
            display: inline-block;
            background: var(--bg-sunken);
            border: 1px solid var(--border);
            transition: background var(--dur-fade) var(--ease-out), border-color var(--dur-fade) var(--ease-out);
        }
        .meta-cell summary:hover {
            background: var(--bg-hover);
            border-color: var(--border-strong);
        }
        .meta-cell pre {
            background: var(--bg-sunken);
            border: 1px solid var(--border);
            padding: 10px 12px;
            border-radius: var(--radius);
            margin-top: 8px;
            max-width: 480px;
            overflow: auto;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .audit-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-subtle);
            font-family: var(--font-mono);
            font-size: 13px;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php $sidebar_active = 'audit'; include __DIR__ . '/inc/admin_sidebar.php'; ?>

        <div class="main-content">
            <h1>Audit Log <span class="audit-count">(<?= number_format($total) ?> events)</span></h1>

            <form method="get" class="audit-filters">
                <label>Event code prefix
                    <input type="text" name="event" value="<?= htmlspecialchars($fEvent) ?>" placeholder="e.g. ADMIN_">
                </label>
                <label>Severity
                    <select name="severity">
                        <option value="">All</option>
                        <option value="info"  <?= $fSeverity==='info'?'selected':'' ?>>info</option>
                        <option value="warn"  <?= $fSeverity==='warn'?'selected':'' ?>>warn</option>
                        <option value="error" <?= $fSeverity==='error'?'selected':'' ?>>error</option>
                    </select>
                </label>
                <label>Actor
                    <input type="text" name="actor" value="<?= htmlspecialchars($fActor) ?>" placeholder="username/IP">
                </label>
                <label>From
                    <input type="date" name="from" value="<?= htmlspecialchars($fFrom) ?>">
                </label>
                <label>To
                    <input type="date" name="to" value="<?= htmlspecialchars($fTo) ?>">
                </label>
                <div class="audit-actions">
                    <button type="submit" class="button primary">Filter</button>
                    <a class="button ghost" href="admin_audit.php?<?= rc_qs(['export'=>'csv']) ?>">Export CSV</a>
                    <a class="button ghost" href="admin_audit.php">Reset</a>
                </div>
            </form>

            <div class="table-container">
                <table class="audit">
                    <thead>
                        <tr>
                            <th>ID</th><th>When</th><th>Event</th><th>Sev</th>
                            <th>Actor</th><th>IP</th><th>Ref</th><th>Meta</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="8" class="audit-empty">No entries match these filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="id"><?= (int)$r['id'] ?></td>
                            <td class="time"><?= htmlspecialchars($r['created_at']) ?></td>
                            <td><code><?= htmlspecialchars($r['event_code']) ?></code></td>
                            <td><span class="sev-badge sev-<?= htmlspecialchars($r['severity']) ?>"><?= htmlspecialchars($r['severity']) ?></span></td>
                            <td><?= htmlspecialchars((string)$r['actor']) ?></td>
                            <td><?= htmlspecialchars((string)$r['ip_address']) ?></td>
                            <td>
                                <?= htmlspecialchars((string)$r['ref_table']) ?><?= $r['ref_id'] ? '#'.(int)$r['ref_id'] : '' ?>
                                <?php if ($r['message']): ?><br><small style="color:var(--text-subtle);"><?= htmlspecialchars($r['message']) ?></small><?php endif; ?>
                            </td>
                            <td class="meta-cell">
                                <?php if ($r['meta']): ?>
                                    <details><summary>view</summary><pre><?= htmlspecialchars($r['meta']) ?></pre></details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php
                $start = max(1, $page - 3);
                $end   = min($pages, $page + 3);
                if ($start > 1) echo '<a href="?'.rc_qs(['p'=>1]).'">« 1</a>';
                for ($i = $start; $i <= $end; $i++) {
                    $cls = $i === $page ? 'active' : '';
                    echo '<a class="'.$cls.'" href="?'.rc_qs(['p'=>$i]).'">'.$i.'</a>';
                }
                if ($end < $pages) echo '<a href="?'.rc_qs(['p'=>$pages]).'">'.$pages.' »</a>';
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="JS/admin-ui.js" defer></script>
</body>
</html>

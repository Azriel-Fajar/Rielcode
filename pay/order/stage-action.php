<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/invoice_number.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }

$order_id = (int)($_POST['order_id'] ?? 0);
$stage    = $_POST['stage']  ?? '';
$action   = $_POST['action'] ?? '';
if (!$order_id || !in_array($stage, ['deposit','final']) || !in_array($action, ['generate','mark_sent','mark_paid','regenerate'])) {
    http_response_code(400); exit('Bad request.');
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { http_response_code(404); exit('Order not found.'); }

// Server-side timing guard: final stage cannot be touched until deposit is paid.
if ($stage === 'final') {
    $r = $conn->query("SELECT status FROM order_payments WHERE order_id={$order_id} AND stage='deposit'");
    $depRow = $r->fetch_assoc();
    if (!$depRow || $depRow['status'] !== 'paid') {
        http_response_code(403);
        exit('Final stage locked: deposit not yet paid.');
    }
}

$currency = $order['invoice_currency'] ?: 'IDR';
$total    = (float)$order['final_price'];
$amount   = $stage === 'deposit' ? round($total * 0.20, 2) : round($total * 0.80, 2);
$dueDays  = $stage === 'deposit' ? 3 : 7;

if ($action === 'generate') {
    // Create row if missing.
    $exists = $conn->query("SELECT id FROM order_payments WHERE order_id={$order_id} AND stage='{$stage}'")->fetch_assoc();
    if (!$exists) {
        $invNum  = generate_invoice_number($conn, $stage);
        $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));
        $stmt = $conn->prepare("INSERT INTO order_payments
            (order_id, stage, invoice_number, amount, currency, status, due_date)
            VALUES (?,?,?,?,?, 'draft', ?)");
        $stmt->bind_param("issdss", $order_id, $stage, $invNum, $amount, $currency, $dueDate);
        $stmt->execute();
        $stmt->close();
    }
} elseif ($action === 'mark_sent') {
    $stmt = $conn->prepare("UPDATE order_payments SET status='sent', sent_at=NOW() WHERE order_id=? AND stage=? AND status IN ('draft','overdue')");
    $stmt->bind_param("is", $order_id, $stage);
    $stmt->execute();
    $stmt->close();

    // When deposit is sent, order should move to On Progress.
    if ($stage === 'deposit' && $order['status'] === 'Pending') {
        // do nothing automatic — user controls main status. Optionally we could nudge.
    }
} elseif ($action === 'mark_paid') {
    $stmt = $conn->prepare("UPDATE order_payments SET status='paid', paid_at=NOW() WHERE order_id=? AND stage=?");
    $stmt->bind_param("is", $order_id, $stage);
    $stmt->execute();
    $stmt->close();

    // Cascade order.status
    if ($stage === 'deposit' && $order['status'] === 'Pending') {
        $conn->query("UPDATE orders SET status='On Progress' WHERE id={$order_id}");
    } elseif ($stage === 'final') {
        $conn->query("UPDATE orders SET status='Completed' WHERE id={$order_id}");
    }
}

header('Location: ' . APP_URL . '/order/view.php?id=' . $order_id . '&msg=ok');
exit;

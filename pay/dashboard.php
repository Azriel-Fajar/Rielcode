<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/invoice_number.php';

$conn->query("UPDATE order_payments SET status='overdue' WHERE status='sent' AND due_date < CURDATE()");

$statsRow = $conn->query("
    SELECT
      COUNT(*) AS total_orders,
      SUM(status='Completed') AS done_count,
      SUM(status IN ('Pending','On Progress','Staging Ready')) AS active_count
    FROM orders
")->fetch_assoc();

$payStats = $conn->query("
    SELECT
      SUM(CASE WHEN stage='deposit' AND status='paid' THEN 1 ELSE 0 END) AS deposits_paid,
      SUM(CASE WHEN stage='final'   AND status='paid' THEN 1 ELSE 0 END) AS finals_paid,
      SUM(CASE WHEN status='paid' AND currency='IDR' THEN amount ELSE 0 END) AS revenue_idr,
      SUM(CASE WHEN status='paid' AND currency='USD' THEN amount ELSE 0 END) AS revenue_usd,
      SUM(status='overdue') AS overdue
    FROM order_payments
")->fetch_assoc();

$orders = $conn->query("
    SELECT o.id, o.order_name, o.email, o.package, o.final_price, o.invoice_currency, o.status,
           o.created_at,
           dp.status AS dep_status, dp.invoice_number AS dep_inv,
           fp.status AS fin_status, fp.invoice_number AS fin_inv
    FROM orders o
    LEFT JOIN order_payments dp ON dp.order_id = o.id AND dp.stage='deposit'
    LEFT JOIN order_payments fp ON fp.order_id = o.id AND fp.stage='final'
    ORDER BY o.created_at DESC
");

$pageTitle = 'Dashboard | Rielcode Pay';
require_once __DIR__ . '/inc/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Orders &amp; Payments</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="stat-box"><div class="stat-label">Active Orders</div><div class="stat-value"><?php echo (int)$statsRow['active_count']; ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-box"><div class="stat-label">Deposits Paid</div><div class="stat-value" style="color:var(--primary)"><?php echo (int)$payStats['deposits_paid']; ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-box"><div class="stat-label">Finals Paid</div><div class="stat-value" style="color:var(--success)"><?php echo (int)$payStats['finals_paid']; ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-box"><div class="stat-label">Overdue</div><div class="stat-value" style="color:var(--danger)"><?php echo (int)$payStats['overdue']; ?></div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="stat-box"><div class="stat-label">Revenue Collected (IDR)</div><div class="stat-value">Rp <?php echo number_format((float)$payStats['revenue_idr'], 0, ',', '.'); ?></div></div></div>
    <div class="col-md-6"><div class="stat-box"><div class="stat-label">Revenue Collected (USD)</div><div class="stat-value">$<?php echo number_format((float)$payStats['revenue_usd'], 2); ?></div></div></div>
</div>

<div class="pay-card p-0 overflow-hidden">
    <table class="table-pay w-100">
        <thead>
            <tr>
                <th>Order</th>
                <th>Client</th>
                <th>Package</th>
                <th>Total</th>
                <th>Order Status</th>
                <th>Deposit (20%)</th>
                <th>Final (80%)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php while ($o = $orders->fetch_assoc()):
            $total = fmt_amount((float)$o['final_price'], $o['invoice_currency']);
            $dep   = $o['dep_status'] ?: 'not_sent';
            $fin   = $o['fin_status'] ?: 'not_sent';
        ?>
            <tr>
                <td><code>#<?php echo (int)$o['id']; ?></code></td>
                <td><?php echo htmlspecialchars($o['order_name']); ?><div style="color:var(--muted);font-size:12px;"><?php echo htmlspecialchars($o['email']); ?></div></td>
                <td><?php echo htmlspecialchars($o['package']); ?></td>
                <td><?php echo $total; ?></td>
                <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '_', $o['status'])); ?> px-2 py-1 rounded"><?php echo htmlspecialchars($o['status']); ?></span></td>
                <td><span class="badge badge-<?php echo $dep; ?> px-2 py-1 rounded"><?php echo ucfirst(str_replace('_',' ',$dep)); ?></span></td>
                <td><span class="badge badge-<?php echo $fin; ?> px-2 py-1 rounded"><?php echo ucfirst(str_replace('_',' ',$fin)); ?></span></td>
                <td><a href="<?php echo APP_URL; ?>/order/view.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm btn-outline-secondary">Manage</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

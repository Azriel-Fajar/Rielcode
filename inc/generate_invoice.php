<?php
// Invoice PDF generator (used by admin invoice flow).
// Reads orders.invoice_line_items JSON if present, otherwise builds from package + addons.
require_once __DIR__ . '/../dompdf/autoload.inc.php';
use Dompdf\Dompdf;

if (!function_exists('rc_generate_invoice')) {
/**
 * @param PDO $pdo
 * @param int $orderId
 * @return array{file:string, invoice_number:string, amount:float, currency:string}
 */
function rc_generate_invoice(PDO $pdo, int $orderId): array {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) throw new RuntimeException("Order $orderId not found.");

    $invoiceNumber = $order['invoice_number'] ?: ('INV-' . date('Ymd') . '-' . $orderId);
    $currency      = $order['invoice_currency'] ?: 'IDR';
    $dueDate       = $order['invoice_due_date'] ?: null;
    $notes         = $order['invoice_notes']    ?: '';

    // Build line items: prefer stored JSON, else derive from package + order_addons
    $lineItems = [];
    if (!empty($order['invoice_line_items'])) {
        $decoded = json_decode($order['invoice_line_items'], true);
        if (is_array($decoded)) $lineItems = $decoded;
    }
    if (empty($lineItems)) {
        $lineItems[] = [
            'description' => $order['package'] . ' — base',
            'qty'         => 1,
            'unit_price'  => (float)$order['package_price'],
            'total'       => (float)$order['package_price'],
        ];
        $stmtA = $pdo->prepare("
            SELECT oa.quantity, oa.price_total, pa.name, pa.type
            FROM order_addons oa
            JOIN package_addons pa ON pa.id = oa.addon_id
            WHERE oa.order_id = ?
        ");
        $stmtA->execute([$orderId]);
        foreach ($stmtA->fetchAll(PDO::FETCH_ASSOC) as $a) {
            $lineItems[] = [
                'description' => $a['name'] . ($a['type'] !== 'one_time' ? ' (×' . (int)$a['quantity'] . ')' : ''),
                'qty'         => (int)$a['quantity'],
                'unit_price'  => (float)$a['price_total'] / max(1, (int)$a['quantity']),
                'total'       => (float)$a['price_total'],
            ];
        }
    }

    $amount = 0.0;
    foreach ($lineItems as $li) $amount += (float)($li['total'] ?? 0);
    if ($order['invoice_amount'] !== null && (float)$order['invoice_amount'] > 0) {
        $amount = (float)$order['invoice_amount'];
    }

    // Render PDF
    $fmt = function($n) use ($currency) {
        return $currency === 'USD'
            ? '$' . number_format($n, 2)
            : 'Rp' . number_format($n, 0, ',', '.');
    };

    $rowsHtml = '';
    foreach ($lineItems as $li) {
        $rowsHtml .= '<tr>'
            . '<td style="padding:8px 6px;text-align:left;">' . htmlspecialchars($li['description'] ?? '') . '</td>'
            . '<td style="padding:8px 6px;text-align:center;">' . (int)($li['qty'] ?? 1) . '</td>'
            . '<td style="padding:8px 6px;text-align:right;">' . $fmt((float)($li['unit_price'] ?? 0)) . '</td>'
            . '<td style="padding:8px 6px;text-align:right;font-weight:600;">' . $fmt((float)($li['total'] ?? 0)) . '</td>'
            . '</tr>';
    }

    $logoPath = realpath(__DIR__ . '/../IMG/Rielcode Logo Transparent.png');
    $logoTag = '';
    if ($logoPath && file_exists($logoPath)) {
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoTag = '<img src="data:image/' . $ext . ';base64,' . $logoData . '" style="max-height:50px;">';
    }

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { margin: 28mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1c1f22; font-size: 11pt; }
        h1 { font-size: 26pt; margin: 0; color: #3a7bff; letter-spacing: 1px; }
        .meta { color: #555; font-size: 10pt; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.lines th { background: #1c1f22; color: #fff; padding: 10px 6px; text-align: left; font-size: 10pt; letter-spacing: 0.5px; }
        table.lines tbody tr:nth-child(even) { background: #f7f8fa; }
        .totals { margin-top: 24px; width: 40%; margin-left: 60%; }
        .totals td { padding: 6px 8px; }
        .totals .grand { font-weight: 800; font-size: 14pt; border-top: 2px solid #1c1f22; }
        .notes { margin-top: 28px; padding: 14px; background: #f7f8fa; border-left: 3px solid #3a7bff; font-size: 10pt; color: #555; }
        .header-row { width:100%; }
    </style></head><body>
        <table class="header-row"><tr>
            <td>' . $logoTag . '<br><div style="font-size:9pt;color:#888;margin-top:6px;">rielcode.com</div></td>
            <td style="text-align:right;">
                <h1>INVOICE</h1>
                <div class="meta"><b>' . htmlspecialchars($invoiceNumber) . '</b></div>
                <div class="meta">Date: ' . date('d F Y') . '</div>'
                . ($dueDate ? '<div class="meta">Due: ' . htmlspecialchars(date('d F Y', strtotime($dueDate))) . '</div>' : '')
            . '</td>
        </tr></table>

        <div style="margin-top:24px;">
            <div style="font-size:9pt;color:#888;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px;">Billed to</div>
            <div style="font-weight:700;font-size:13pt;">' . htmlspecialchars($order['order_name']) . '</div>
            <div style="color:#555;">' . htmlspecialchars($order['email']) . '</div>
            <div style="color:#555;">' . htmlspecialchars($order['phone_number']) . '</div>
        </div>

        <table class="lines">
            <thead><tr>
                <th style="width:50%;">Description</th>
                <th style="width:10%;text-align:center;">Qty</th>
                <th style="width:20%;text-align:right;">Unit Price</th>
                <th style="width:20%;text-align:right;">Total</th>
            </tr></thead>
            <tbody>' . $rowsHtml . '</tbody>
        </table>

        <table class="totals">
            <tr class="grand">
                <td>GRAND TOTAL</td>
                <td style="text-align:right;">' . $fmt($amount) . '</td>
            </tr>
        </table>

        ' . ($notes ? '<div class="notes">' . nl2br(htmlspecialchars($notes)) . '</div>' : '') . '

        <div style="margin-top:40px;font-size:9pt;color:#888;text-align:center;">
            Thank you for choosing Rielcode. Payment can be sent via Indonesian bank transfer or as agreed.
        </div>
    </body></html>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $invoiceDir = realpath(__DIR__ . '/..') . '/invoices';
    if (!is_dir($invoiceDir)) mkdir($invoiceDir, 0755, true);
    $invoiceFile = $invoiceDir . '/' . $invoiceNumber . '.pdf';
    file_put_contents($invoiceFile, $dompdf->output());

    // Save back
    $relative = '../invoices/' . $invoiceNumber . '.pdf';
    $up = $pdo->prepare("UPDATE orders SET invoice_number=?, invoice_file=?, invoice_amount=? WHERE id=?");
    $up->execute([$invoiceNumber, $relative, $amount, $orderId]);

    return [
        'file'           => $invoiceFile,
        'relative'       => $relative,
        'invoice_number' => $invoiceNumber,
        'amount'         => $amount,
        'currency'       => $currency,
    ];
}
}

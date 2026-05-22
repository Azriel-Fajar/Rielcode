<?php
function generate_invoice_number(mysqli $conn, string $stage): string {
    $suffix = $stage === 'deposit' ? 'D' : 'F';
    $year = (int)date('Y');
    $stmt = $conn->prepare("INSERT INTO invoice_counter (year, last_number) VALUES (?, 1)
                            ON DUPLICATE KEY UPDATE last_number = last_number + 1");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT last_number FROM invoice_counter WHERE year = ?");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return sprintf('INV-%d-%03d-%s', $year, (int)$row['last_number'], $suffix);
}

function fmt_amount(float $amount, string $currency): string {
    return $currency === 'IDR'
        ? 'Rp ' . number_format($amount, 0, ',', '.')
        : '$' . number_format($amount, 2);
}

<?php
// BUG FIX: session_start() MUST come before any include to avoid "headers already sent"
session_start();

// Guard: if no transaction in session, send user back to checkout
if (!isset($_SESSION['transaction'])) {
    header('Location: ../../checkout/');
    exit; // BUG FIX: missing exit — without this, the page would continue rendering
}

// BUG FIX: removed the raw mysqli_query with unescaped $id.
// The status was already set to 'On Progress' by checkout/index.php before redirecting here.
// This second UPDATE was redundant AND vulnerable to injection.
// We only need to destroy the session now.
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Successful | Rielcode</title>

    <link rel="stylesheet" href="../../CSS/success.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../../IMG/Rielcode Logo Square Transparent Icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
</head>
<body>
    <div class="popup">
        <div class="circle">
            <div class="checkmark"></div>
        </div>
        <h2>Order Completed!</h2>
        <p>Thank you for your purchase. Your order has been successfully processed.</p>
        <a href="/" class="btn">Back to Home</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
</body>
</html>
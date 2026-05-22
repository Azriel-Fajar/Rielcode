<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Rielcode Pay'); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/CSS/pay.css" rel="stylesheet">
</head>
<body>
<?php if (!empty($_SESSION['admin_logged_in'])): ?>
<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/dashboard.php">
            <img src="<?php echo APP_URL; ?>/IMG/logo.png" alt="Rielcode" style="height:28px;vertical-align:middle;"> Pay
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <a href="<?php echo APP_URL; ?>/dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
            <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="main-content">

<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/audit_logger.php';

try {
    $pdo = rc_pdo();
} catch (Throwable $e) {
    error_log('[RC-DB-001] admin_account: ' . $e->getMessage());
    include __DIR__ . '/inc/_db_down.php';
}

$currentUsername = $_SESSION['admin_username'] ?? '';
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newUsername     = trim($_POST['new_username'] ?? '');
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Verify current password first
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$currentUsername]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($currentPassword, $admin['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
        rc_audit('ADMIN_ACCOUNT_VERIFY_FAIL', $currentUsername, [], 'warn');
    } else {
        // Validate new username
        if ($newUsername === '') {
            $errors[] = 'New username cannot be empty.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $newUsername)) {
            $errors[] = 'Username must be 3–32 chars (letters, digits, _ . -).';
        } elseif ($newUsername !== $currentUsername) {
            $chk = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id <> ?");
            $chk->execute([$newUsername, $admin['id']]);
            if ($chk->fetch()) $errors[] = 'That username is already taken.';
        }

        // Validate new password (optional — only if user supplied one)
        $changePassword = ($newPassword !== '' || $confirmPassword !== '');
        if ($changePassword) {
            if (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }
        }

        if (!$errors) {
            if ($changePassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE admins SET username = ?, password_hash = ? WHERE id = ?");
                $upd->execute([$newUsername, $hash, $admin['id']]);
            } else {
                $upd = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $upd->execute([$newUsername, $admin['id']]);
            }
            $_SESSION['admin_username'] = $newUsername;
            $currentUsername = $newUsername;
            rc_audit('ADMIN_ACCOUNT_UPDATE', $newUsername, [
                'changed_username' => $admin['username'] !== $newUsername,
                'changed_password' => $changePassword,
            ]);
            $success = 'Account updated successfully.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account — RielBot Admin</title>
    <?php include __DIR__ . '/inc/admin_theme_head.php'; ?>
    <link rel="stylesheet" href="CSS/admin_style.css">
    <link rel="icon" href="IMG/Rielcode Logo Square Transparent Icon.png" type="image/png">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
    <div class="wrapper">
        <?php $sidebar_active = 'account'; include __DIR__ . '/inc/admin_sidebar.php'; ?>

        <div class="main-content">
            <h1>Account Settings</h1>
            <p class="adm-muted" style="max-width:560px;">
                Change your admin username or password. You must enter your current password to confirm any change.
            </p>

            <?php if ($success): ?>
                <div class="rc-toast rc-toast--success is-open" data-auto-dismiss="3500" style="position:relative;top:auto;right:auto;margin:0 0 16px;">
                    <span class="rc-toast__icon"></span>
                    <span class="rc-toast__msg"><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="error">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="form-card" autocomplete="off" style="max-width:520px;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Current Password <span style="color:var(--err);">*</span></label>
                    <input type="password" name="current_password" required autocomplete="current-password">
                </div>

                <hr style="border:none;border-top:1px solid var(--border);margin:18px 0;">

                <div class="form-group" style="margin-bottom:14px;">
                    <label>Username</label>
                    <input type="text" name="new_username" value="<?= htmlspecialchars($currentUsername) ?>" required>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label>New Password <span class="adm-subtle" style="text-transform:none;letter-spacing:0;">(leave blank to keep current)</span></label>
                    <input type="password" name="new_password" autocomplete="new-password" minlength="8">
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" autocomplete="new-password" minlength="8">
                </div>

                <button type="submit" class="button--primary">Save Changes</button>
            </form>
        </div>
    </div>
    <script src="JS/admin-ui.js" defer></script>
</body>
</html>

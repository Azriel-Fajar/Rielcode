<?php
/**
 * Shared admin sidebar partial.
 *
 * Expected vars (optional, default empty):
 *   $sidebar_active — one of: 'chat_logs','orders','invoices','packages',
 *                     'projects','testimonials','referrers','commissions',
 *                     'audit','edit','invoice_edit'
 */
$active = $sidebar_active ?? '';
function rc_sb_cls($name, $active) {
    return $name === $active ? 'active' : '';
}
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<button class="sidebar-toggle" id="sidebarToggle" type="button">☰ Menu</button>

<button type="button" class="theme-toggle theme-toggle--fixed" id="rc-theme-toggle" aria-label="Cycle theme" title="Cycle theme">
    <span class="theme-toggle__label">System</span>
</button>

<aside class="sidebar" id="rc-sidebar">
    <h2>
        <img src="IMG/Rielcode Logo Square Transparent Icon.png" alt="Rielcode" class="sidebar-logo">
        RielBot Admin
    </h2>
    <a href="admin.php?table=chat_logs"    class="<?= rc_sb_cls('chat_logs', $active) ?>">Chat Logs</a>
    <a href="admin.php?table=orders"       class="<?= rc_sb_cls('orders', $active) ?>">Orders</a>
    <a href="admin.php?table=invoices"     class="<?= rc_sb_cls('invoices', $active) ?>">Invoices</a>
    <a href="admin.php?table=packages"     class="<?= rc_sb_cls('packages', $active) ?>">Packages</a>
    <a href="admin.php?table=projects"     class="<?= rc_sb_cls('projects', $active) ?>">Projects</a>
    <a href="admin.php?table=testimonials" class="<?= rc_sb_cls('testimonials', $active) ?>">Testimonials</a>
    <a href="admin.php?table=referrers"    class="<?= rc_sb_cls('referrers', $active) ?>">Referrers</a>
    <a href="admin.php?table=commissions"  class="<?= rc_sb_cls('commissions', $active) ?>">Commissions</a>
    <a href="admin_audit.php"              class="<?= rc_sb_cls('audit', $active) ?>">Audit Log</a>
    <a href="admin_account.php"            class="<?= rc_sb_cls('account', $active) ?>">Account</a>

    <div class="sidebar__spacer"></div>
    <a href="admin_logout.php" class="sidebar__logout">Logout</a>
</aside>
<?php
/**
 * Snippet to put in <head> BEFORE the stylesheet to prevent FOUC.
 * Include via: <?php include __DIR__ . '/inc/admin_theme_head.php'; ?>
 */
?>

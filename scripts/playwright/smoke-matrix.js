// Headed smoke matrix: visits each route, asserts no fatal/SQL/uncaught traces,
// asserts a sentinel selector renders. Logs in as test_claude for admin routes.
const { chromium } = require('playwright');

const BASE = 'http://localhost/Rielcode';

const publicRoutes = [
  { path: '/',                          sentinel: 'nav, footer',         label: 'home' },
  { path: '/about.php',                 sentinel: 'h1, h2, footer',      label: 'about' },
  { path: '/package.php',               sentinel: 'h1, h2, footer',      label: 'packages' },
  { path: '/projects.php',              sentinel: 'h1, h2, footer',      label: 'projects' },
  { path: '/requirement.php',           sentinel: 'form, h1, h2',        label: 'requirement' },
  { path: '/order-form/',               sentinel: 'form, h1, h2',        label: 'order-form' },
  { path: '/checkout/',                 sentinel: 'body',                label: 'checkout (may redirect with ?err)' },
  { path: '/terms&conditions/',         sentinel: 'h1, h2, body',        label: 'terms' },
  { path: '/admin_login.php',           sentinel: 'form input[name="username"]', label: 'admin-login' },
  { path: '/referrer/?code=invalidxyz', sentinel: 'body',                label: 'referrer (invalid code)' },
];

const adminRoutes = [
  { path: '/admin.php',                 sentinel: '.sidebar, nav, table', label: 'admin-home' },
  { path: '/admin.php?table=orders',    sentinel: 'table, .sidebar',      label: 'admin-orders' },
  { path: '/admin.php?table=invoices',  sentinel: 'table, .sidebar',      label: 'admin-invoices (POST-FIX)' },
  { path: '/admin.php?table=chat_logs', sentinel: 'table, .sidebar',      label: 'admin-chat-logs' },
  { path: '/admin.php?table=packages',  sentinel: 'table, .sidebar',      label: 'admin-packages' },
  { path: '/admin.php?table=projects',  sentinel: 'table, .sidebar',      label: 'admin-projects' },
  { path: '/admin.php?table=testimonials', sentinel: 'table, .sidebar',   label: 'admin-testimonials' },
  { path: '/admin.php?table=referrers', sentinel: 'table, .sidebar',      label: 'admin-referrers' },
  { path: '/admin.php?table=commissions', sentinel: 'table, .sidebar',    label: 'admin-commissions' },
  { path: '/admin_audit.php',           sentinel: 'table, h1, h2',        label: 'admin-audit' },
];

const fatalRegex = /Fatal error|Uncaught|SQLSTATE|Parse error|Warning:.*on line|Notice:.*on line/i;

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 100 });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 }, ignoreHTTPSErrors: true });
  const page = await ctx.newPage();

  // Capture page errors / console errors per navigation
  const errs = [];
  page.on('pageerror', e => errs.push(`pageerror: ${e.message}`));
  page.on('response', r => { if (r.status() >= 500) errs.push(`5xx: ${r.url()} ${r.status()}`); });

  const results = [];

  async function visit(route, group) {
    errs.length = 0;
    let status = 'ERR', detail = '';
    try {
      const resp = await page.goto(BASE + route.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
      const code = resp ? resp.status() : 0;
      const html = await page.content();
      const fatalHit = html.match(fatalRegex);
      const sentinelOk = await page.locator(route.sentinel).first().isVisible().catch(() => false);

      if (fatalHit) { status = 'FAIL'; detail = `PHP trace: ${fatalHit[0]}`; }
      else if (code >= 500) { status = 'FAIL'; detail = `HTTP ${code}`; }
      else if (!sentinelOk) { status = 'WARN'; detail = `sentinel "${route.sentinel}" not visible (HTTP ${code})`; }
      else { status = 'PASS'; detail = `HTTP ${code}`; }
    } catch (e) {
      status = 'FAIL'; detail = `navigation: ${e.message}`;
    }
    if (errs.length) detail += ' | ' + errs.slice(0, 3).join('; ');
    results.push({ group, path: route.path, label: route.label, status, detail });
    console.log(`[smoke] [${group}] ${status.padEnd(4)} ${route.path.padEnd(40)} ${route.label} — ${detail}`);
  }

  console.log('\n[smoke] ===== PUBLIC ROUTES =====');
  for (const r of publicRoutes) await visit(r, 'public');

  console.log('\n[smoke] ===== LOGIN AS test_claude =====');
  await page.goto(BASE + '/admin_login.php', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="username"]', 'test_claude');
  await page.fill('input[name="password"]', 'TestPass123!');
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.click('button[type="submit"]'),
  ]);
  const afterLogin = page.url();
  console.log(`[smoke] post-login URL: ${afterLogin}`);
  if (!afterLogin.includes('admin.php') || afterLogin.includes('admin_login')) {
    console.log('[smoke] LOGIN FAILED — aborting admin matrix');
    await browser.close();
    process.exit(2);
  }

  console.log('\n[smoke] ===== ADMIN ROUTES =====');
  for (const r of adminRoutes) await visit(r, 'admin');

  // Logout
  await page.goto(BASE + '/admin_logout.php', { waitUntil: 'domcontentloaded' }).catch(() => {});

  const fails = results.filter(r => r.status === 'FAIL');
  const warns = results.filter(r => r.status === 'WARN');
  const pass  = results.filter(r => r.status === 'PASS');
  console.log('\n[smoke] ===== SUMMARY =====');
  console.log(`[smoke] PASS=${pass.length}  WARN=${warns.length}  FAIL=${fails.length}  TOTAL=${results.length}`);
  if (fails.length) {
    console.log('[smoke] FAILURES:');
    fails.forEach(r => console.log(`  - ${r.path} :: ${r.detail}`));
  }
  if (warns.length) {
    console.log('[smoke] WARNINGS:');
    warns.forEach(r => console.log(`  - ${r.path} :: ${r.detail}`));
  }

  await page.waitForTimeout(2000);
  await browser.close();
  process.exit(fails.length ? 1 : 0);
})();

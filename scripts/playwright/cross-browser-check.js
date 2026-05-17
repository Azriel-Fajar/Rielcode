// Cross-browser smoke matrix for Rielcode.
// Tests core flows in chromium, firefox, webkit (+ msedge/chrome if installed).
// Chatbot flow intentionally SKIPPED — burns ~1.5k OpenAI tokens per browser run.
// Run: node scripts/playwright/cross-browser-check.js
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://localhost/Rielcode/';
const BROWSERS = ['chromium', 'firefox', 'webkit', 'msedge'];
const SHOT_DIR = path.join(__dirname, '..', '..', 'screenshots', 'cross-browser');
const HEADLESS = process.env.HEADLESS === '1';
const SLOW_MO  = parseInt(process.env.SLOW_MO || '80', 10);

const FLOWS = [
  {
    name: 'home-renders',
    fn: async (page) => {
      await page.goto(BASE_URL, { waitUntil: 'domcontentloaded', timeout: 20000 });
      if ((await page.locator('h1, h2').count()) === 0) throw new Error('no h1/h2');
      const txt = (await page.locator('body').innerText()).trim();
      if (txt.length < 100) throw new Error('body text too short: ' + txt.length);
    },
  },
  {
    name: 'no-console-errors',
    fn: async (page) => {
      const errors = [];
      page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
      page.on('console', (m) => {
        if (m.type() === 'error') errors.push('console.error: ' + m.text());
      });
      await page.goto(BASE_URL, { waitUntil: 'networkidle', timeout: 20000 });
      await page.waitForTimeout(1500);
      const real = errors.filter(e => !/favicon|gtag|google-analytics|analytics\.js|net::ERR_FAILED.*tawk/i.test(e));
      if (real.length > 0) throw new Error('errors: ' + real.slice(0, 2).join(' | '));
    },
  },
  {
    name: 'about-page-loads',
    fn: async (page) => {
      const resp = await page.goto(BASE_URL + 'about.php', { waitUntil: 'domcontentloaded', timeout: 20000 });
      if (!resp || resp.status() !== 200) throw new Error('HTTP ' + (resp ? resp.status() : 'null'));
      if ((await page.locator('h1, h2').count()) === 0) throw new Error('no headings');
    },
  },
  {
    name: 'order-form-loads',
    fn: async (page) => {
      const resp = await page.goto(BASE_URL + 'order-form/', { waitUntil: 'domcontentloaded', timeout: 20000 });
      if (!resp || resp.status() !== 200) throw new Error('HTTP ' + (resp ? resp.status() : 'null'));
      if ((await page.locator('form, input').count()) === 0) throw new Error('no form fields');
    },
  },
  {
    name: 'admin-login-renders',
    fn: async (page) => {
      const resp = await page.goto(BASE_URL + 'admin_login.php', { waitUntil: 'domcontentloaded', timeout: 20000 });
      if (!resp || resp.status() !== 200) throw new Error('HTTP ' + (resp ? resp.status() : 'null'));
      if ((await page.locator('input[name="username"]').count()) === 0) throw new Error('no username input');
      if ((await page.locator('input[name="password"]').count()) === 0) throw new Error('no password input');
    },
  },
  {
    name: 'chatbot-icon-visible',
    fn: async (page) => {
      // Visibility only — does NOT send a message (no OpenAI tokens burned).
      await page.goto(BASE_URL, { waitUntil: 'domcontentloaded', timeout: 20000 });
      await page.waitForSelector('#chatbot-icon', { state: 'visible', timeout: 8000 });
    },
  },
];

async function runBrowser(browserName) {
  const playwright = require('playwright');
  let browser;
  const launchOpts = { headless: HEADLESS, slowMo: SLOW_MO };
  try {
    if (browserName === 'msedge' || browserName === 'chrome') {
      browser = await playwright.chromium.launch({ ...launchOpts, channel: browserName });
    } else {
      browser = await playwright[browserName].launch(launchOpts);
    }
  } catch (e) {
    return { browser: browserName, results: [], skipped: 'launch failed: ' + (e.message || '').split('\n')[0].slice(0, 120) };
  }

  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();
  const results = [];

  for (const flow of FLOWS) {
    const t0 = Date.now();
    try {
      await flow.fn(page);
      results.push({ flow: flow.name, status: 'PASS', ms: Date.now() - t0 });
    } catch (e) {
      const shotDir = path.join(SHOT_DIR, browserName);
      fs.mkdirSync(shotDir, { recursive: true });
      const shot = path.join(shotDir, `${flow.name}.png`);
      try { await page.screenshot({ path: shot, fullPage: false }); } catch (_) {}
      results.push({
        flow: flow.name,
        status: 'FAIL',
        ms: Date.now() - t0,
        err: (e.message || String(e)).split('\n')[0].slice(0, 160),
        shot,
      });
    }
  }

  await browser.close();
  return { browser: browserName, results };
}

(async () => {
  console.log(`[cross-browser] BASE=${BASE_URL}  BROWSERS=${BROWSERS.join(',')}  HEADLESS=${HEADLESS}`);
  const all = [];
  for (const b of BROWSERS) {
    console.log(`\n[cross-browser] === ${b} ===`);
    const r = await runBrowser(b);
    all.push(r);
    if (r.skipped) { console.log(`  SKIPPED: ${r.skipped}`); continue; }
    for (const x of r.results) {
      console.log(`  ${x.status}  ${x.flow.padEnd(28)} ${x.ms}ms${x.err ? '  — ' + x.err : ''}`);
      if (x.shot && x.status === 'FAIL') console.log(`        shot: ${x.shot}`);
    }
  }

  console.log('\n[cross-browser] ===== MATRIX =====');
  const flowNames = FLOWS.map(f => f.name);
  console.log('Flow'.padEnd(28) + BROWSERS.map(b => b.padEnd(10)).join(''));
  for (const fn of flowNames) {
    let row = fn.padEnd(28);
    for (const b of BROWSERS) {
      const r = all.find(x => x.browser === b);
      if (r.skipped) { row += 'SKIP'.padEnd(10); continue; }
      const cell = r.results.find(x => x.flow === fn);
      row += (cell ? cell.status : '?').padEnd(10);
    }
    console.log(row);
  }

  const anyFail = all.some(r => r.results && r.results.some(x => x.status === 'FAIL'));
  console.log(`\n[cross-browser] OVERALL: ${anyFail ? 'FAIL' : 'PASS'}`);
  process.exit(anyFail ? 1 : 0);
})();

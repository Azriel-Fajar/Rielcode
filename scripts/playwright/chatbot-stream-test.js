// Headed Playwright check: chatbot streaming renders progressively.
// Pass: ≥3 distinct growing samples of bot reply text before completion,
// AND sessionStorage.rc_stream_ok !== "0" at the end.
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 250 });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();

  console.log('[stream-test] navigating to http://localhost/Rielcode/');
  await page.goto('http://localhost/Rielcode/', { waitUntil: 'networkidle' });

  // Open chatbot
  await page.waitForSelector('#chatbot-icon', { timeout: 10000 });
  await page.click('#chatbot-icon');
  await page.waitForSelector('#user-input', { state: 'visible' });

  const prompt = 'Tell me in detail about all four Rielcode packages, what each includes, and what kind of business each one suits best. Be thorough.';
  await page.fill('#user-input', prompt);

  // Count existing bot messages so we can find the new one
  const baseBotCount = await page.locator('.message.bot').count();
  console.log('[stream-test] base bot-message count:', baseBotCount);

  await page.click('#send-btn');

  // Wait for the new bot message bubble to appear
  await page.waitForFunction(
    (n) => document.querySelectorAll('.message.bot').length > n,
    baseBotCount,
    { timeout: 8000 }
  );

  // Sample text length of the latest bot message every 150ms for up to 30s
  const samples = [];
  const start = Date.now();
  let lastLen = -1;
  let stableTicks = 0;
  while (Date.now() - start < 30000) {
    const len = await page.evaluate(() => {
      const nodes = document.querySelectorAll('.message.bot');
      const el = nodes[nodes.length - 1];
      return el ? el.textContent.length : 0;
    });
    if (len !== lastLen) {
      samples.push({ t: Date.now() - start, len });
      lastLen = len;
      stableTicks = 0;
    } else {
      stableTicks++;
    }
    if (stableTicks > 10 && len > 0) break; // stable for ~1.5s → done
    await page.waitForTimeout(150);
  }

  const finalText = await page.evaluate(() => {
    const nodes = document.querySelectorAll('.message.bot');
    return nodes[nodes.length - 1]?.textContent || '';
  });
  const streamFlag = await page.evaluate(() => sessionStorage.getItem('rc_stream_ok'));

  // Screenshot full chat window
  await page.locator('#chatbot-container').screenshot({ path: 'c:/xampp/htdocs/Rielcode/screenshots/chatbot-stream-final.png' }).catch(() => {});

  console.log('\n[stream-test] ===== RESULT =====');
  console.log('[stream-test] distinct length samples:', samples.length);
  console.log('[stream-test] samples (t_ms, len):', samples.map(s => `(${s.t},${s.len})`).join(' '));
  console.log('[stream-test] final text length:', finalText.length);
  console.log('[stream-test] final text preview:', finalText.slice(0, 200).replace(/\s+/g, ' '));
  console.log('[stream-test] sessionStorage.rc_stream_ok =', streamFlag);

  // Allow tiny final shrink from markdown re-render (** → <strong>, etc.) — only count the streaming phase.
  const streamingSamples = samples.slice(0, -1);
  const monotonic = streamingSamples.length >= 3 && streamingSamples.every((s, i) => i === 0 || s.len >= streamingSamples[i - 1].len);
  const peak = Math.max(...samples.map(s => s.len));
  const incremental = samples.length >= 3 && samples[1].len > 0 && samples[1].len < peak;
  const streamingUsed = streamFlag !== '0';

  const pass = monotonic && incremental && streamingUsed;
  console.log('[stream-test] monotonic_growth:', monotonic);
  console.log('[stream-test] incremental_render:', incremental);
  console.log('[stream-test] streaming_path_used:', streamingUsed);
  console.log('[stream-test] OVERALL:', pass ? 'PASS' : 'FAIL');

  await page.waitForTimeout(3000);
  await browser.close();
  process.exit(pass ? 0 : 1);
})();

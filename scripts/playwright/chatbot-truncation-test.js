// Verify the chatbot reply ends with a proper sentence terminator (no mid-sentence cutoff).
// Sends a "be thorough" prompt that previously triggered truncation, then checks the
// final character of the rendered reply is one of . ! ? ) "
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 150 });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await ctx.newPage();

  console.log('[trunc-test] navigating');
  await page.goto('http://localhost/Rielcode/', { waitUntil: 'networkidle' });
  await page.waitForSelector('#chatbot-icon');
  await page.click('#chatbot-icon');
  await page.waitForSelector('#user-input', { state: 'visible' });

  const prompt = 'Give me a detailed comparison of every package you sell, including who each suits, what features they include, and pricing. Be thorough.';
  const baseBotCount = await page.locator('.message.bot').count();
  await page.fill('#user-input', prompt);
  await page.click('#send-btn');

  await page.waitForFunction(
    (n) => document.querySelectorAll('.message.bot').length > n,
    baseBotCount,
    { timeout: 15000 }
  );

  // Wait until text stops growing for 1500ms
  let last = '';
  let stableSince = Date.now();
  const start = Date.now();
  while (Date.now() - start < 30000) {
    const txt = (await page.locator('.message.bot').last().innerText()).trim();
    if (txt !== last) {
      last = txt;
      stableSince = Date.now();
    } else if (Date.now() - stableSince > 1500 && txt.length > 0) {
      break;
    }
    await page.waitForTimeout(200);
  }

  // Strip trailing whitespace + emoji (any char above U+007F counts as emoji/sentinel)
  // then check the last "real" char is sentence-ending punctuation.
  const stripped = last.replace(/[\s ]+$/, '').replace(/[^\x00-\x7F]+$/u, '').trim();
  const finalChar = stripped.slice(-1);
  const validEnders = ['.', '!', '?', ')', '"', '”', '…'];
  const cleanFinish = validEnders.includes(finalChar);

  console.log('[trunc-test] final length:', last.length);
  console.log('[trunc-test] last 120 chars:', last.slice(-120));
  console.log('[trunc-test] final char:', JSON.stringify(finalChar));
  console.log('[trunc-test] clean_finish:', cleanFinish);
  console.log('[trunc-test] OVERALL:', cleanFinish ? 'PASS' : 'FAIL');

  await browser.close();
  process.exit(cleanFinish ? 0 : 1);
})();

const { chromium } = require('playwright');

const OPERA_PATH = 'C:/Users/pc/AppData/Local/Programs/Opera/131.0.5877.24/opera.exe';
const URL = 'http://localhost/Rielcode/';

(async () => {
  const browser = await chromium.launch({
    executablePath: OPERA_PATH,
    headless: false,
    args: ['--no-sandbox'],
  });

  const context = await browser.newContext();
  const page = await context.newPage();

  // Capture all console messages
  page.on('console', msg => {
    console.log(`[BROWSER ${msg.type().toUpperCase()}] ${msg.text()}`);
  });

  // Capture all network responses
  page.on('response', async response => {
    const url = response.url();
    if (url.includes('proxy')) {
      console.log(`[NET] ${response.status()} ${url}`);
      console.log(`[NET] Content-Type: ${response.headers()['content-type']}`);
      // Check if body is readable
      try {
        const body = await response.body();
        console.log(`[NET] Body length: ${body.length} bytes`);
        console.log(`[NET] Body preview: ${body.toString('utf8').slice(0, 300)}`);
      } catch (e) {
        console.log(`[NET] Body read error: ${e.message}`);
      }
    }
  });

  page.on('requestfailed', req => {
    if (req.url().includes('proxy')) {
      console.log(`[NET FAIL] ${req.url()} — ${req.failure()?.errorText}`);
    }
  });

  console.log('[*] Navigating to', URL);
  await page.goto(URL, { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  // Open chatbot
  console.log('[*] Opening chatbot...');
  await page.click('#chatbot-icon');
  await page.waitForTimeout(1000);

  // Check res.body support via in-page eval
  const bodySupport = await page.evaluate(async () => {
    try {
      const r = await fetch(window.location.origin + '/Rielcode/proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'text/event-stream' },
        body: JSON.stringify({ message: 'hi', source: 'chatbot' }),
      });
      return {
        status: r.status,
        contentType: r.headers.get('content-type'),
        bodyIsNull: r.body === null,
        bodyType: typeof r.body,
        hasGetReader: typeof r.body?.getReader === 'function',
      };
    } catch (e) {
      return { error: e.message };
    }
  });
  console.log('[BODY SUPPORT TEST]', JSON.stringify(bodySupport, null, 2));

  // Send a message through the chatbot UI
  console.log('[*] Sending test message...');
  await page.fill('#user-input', 'What packages do you offer?');
  await page.click('#send-btn');

  // Wait for response (up to 30s)
  await page.waitForTimeout(15000);

  // Grab chat messages
  const msgs = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('#chat-messages > div')).map(d => ({
      class: d.className,
      text: d.textContent.slice(0, 200),
    }));
  });
  console.log('[CHAT MESSAGES]', JSON.stringify(msgs, null, 2));

  await browser.close();
})();

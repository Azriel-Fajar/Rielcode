// Stress: 35 sequential POSTs to /proxy.php.
// Expect 30 × 200, then 429 + RC-RL-001 starting at request 31.
const http = require('http');

const HOST = 'localhost';
const PATH = '/Rielcode/proxy.php';
const TOTAL = 35;
// source must be 'chatbot' — that's the only source gated by the rate limiter (proxy.php:87).
const BODY = JSON.stringify({ message: 'ping ' + Date.now(), source: 'chatbot' });

function post(i) {
  return new Promise((resolve) => {
    const t0 = Date.now();
    const req = http.request({
      host: HOST, path: PATH, method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(BODY) },
    }, res => {
      let body = '';
      res.on('data', c => body += c);
      res.on('end', () => {
        let parsed = null;
        try { parsed = JSON.parse(body); } catch (_) {}
        resolve({ i, status: res.statusCode, ms: Date.now() - t0, code: parsed?.code, reply: parsed?.reply, raw: body.slice(0, 120) });
      });
    });
    req.on('error', e => resolve({ i, status: 'ERR', error: e.message, ms: Date.now() - t0 }));
    req.write(BODY); req.end();
  });
}

(async () => {
  console.log(`[stress] starting ${TOTAL} sequential POSTs to http://${HOST}${PATH}`);
  const results = [];
  for (let i = 1; i <= TOTAL; i++) {
    const r = await post(i);
    results.push(r);
    console.log(`[stress] #${String(i).padStart(2)} status=${r.status} ms=${r.ms} code=${r.code || '-'} reply="${(r.reply || r.raw || '').slice(0, 60)}"`);
  }

  const ok = results.filter(r => r.status === 200);
  const rl = results.filter(r => r.status === 429);
  const other = results.filter(r => r.status !== 200 && r.status !== 429);

  console.log('\n[stress] ===== SUMMARY =====');
  console.log(`[stress] 200 OK:           ${ok.length}`);
  console.log(`[stress] 429 rate-limited: ${rl.length}`);
  console.log(`[stress] other:            ${other.length}`);
  console.log(`[stress] first 429 at req #${rl[0]?.i ?? 'n/a'}`);
  console.log(`[stress] 429 codes:        ${[...new Set(rl.map(r => r.code).filter(Boolean))].join(', ') || '(none surfaced)'}`);

  const pass = ok.length >= 25 && rl.length >= 3 && rl.every(r => r.code === 'RC-RL-001' || !r.code);
  console.log('[stress] OVERALL:', pass ? 'PASS' : 'INSPECT');
  process.exit(pass ? 0 : 1);
})();

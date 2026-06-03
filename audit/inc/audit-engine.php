<?php
/**
 * audit/inc/audit-engine.php
 * Pure-PHP website audit. No Node, no Playwright — runs on PHP-only hosts.
 *
 * rc_run_audit(string $url): array
 *   Returns: [
 *     'ok'           => bool,    // false if site unreachable
 *     'score'        => int,     // 0-100
 *     'load_time_ms' => int,
 *     'checks'       => [ key => ['pass'=>bool, 'points'=>int, 'label'=>string] ],
 *   ]
 */

if (!function_exists('rc_run_audit')) {

    /** Weight table — total 100. */
    function rc_audit_weights(): array {
        // 'label' = neutral name (full table). 'issue' = deficit phrasing (top issues).
        return [
            'mobile'   => ['points' => 15, 'label' => 'Mobile responsive (viewport set)', 'issue' => 'Not mobile responsive'],
            'title'    => ['points' => 10, 'label' => 'Page title present',               'issue' => 'Missing or weak page title'],
            'metadesc' => ['points' => 10, 'label' => 'Meta description present',          'issue' => 'No meta description'],
            'h1'       => ['points' => 10, 'label' => 'H1 heading present',                'issue' => 'No main heading (H1)'],
            'alt'      => ['points' => 10, 'label' => 'Images have alt text',              'issue' => 'Images missing alt text'],
            'speed'    => ['points' => 15, 'label' => 'Loads under 3 seconds',            'issue' => 'Slow to load (over 3 seconds)'],
            'ssl'      => ['points' => 10, 'label' => 'Secure (HTTPS)',                    'issue' => 'Not secure (no HTTPS)'],
            'contact'  => ['points' => 10, 'label' => 'Contact method visible',           'issue' => 'No clear contact method'],
            'cta'      => ['points' => 10, 'label' => 'Call-to-action present',            'issue' => 'No clear call-to-action'],
        ];
    }

    function rc_run_audit(string $url): array {
        $weights = rc_audit_weights();

        // --- Fetch ---
        $start = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; RielcodeAuditBot/1.0; +https://rielcode.com/audit)',
            CURLOPT_SSL_VERIFYPEER => false, // scoring tool, not a security gate
        ]);
        $html       = curl_exec($ch);
        $loadMs     = (int) round((microtime(true) - $start) * 1000);
        $finalUrl   = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
        $httpCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr    = curl_error($ch);
        curl_close($ch);

        if ($html === false || $httpCode === 0 || $httpCode >= 500) {
            return [
                'ok'           => false,
                'score'        => 0,
                'load_time_ms' => $loadMs,
                'checks'       => [],
                'error'        => $curlErr ?: ('HTTP ' . $httpCode),
            ];
        }

        // --- Parse ---
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        $lcHtml = strtolower($html);

        // --- Checks ---
        $results = [];

        // mobile: viewport meta
        $results['mobile'] = $xp->query('//meta[@name="viewport"]')->length > 0;

        // title
        $titleNode = $xp->query('//title')->item(0);
        $results['title'] = $titleNode && strlen(trim($titleNode->textContent)) > 10;

        // meta description
        $results['metadesc'] = $xp->query('//meta[@name="description"]')->length > 0;

        // h1
        $results['h1'] = $xp->query('//h1')->length > 0;

        // alt: >50% of imgs have non-empty alt
        $imgs = $xp->query('//img');
        if ($imgs->length === 0) {
            $results['alt'] = true; // no images, nothing to fail
        } else {
            $withAlt = 0;
            foreach ($imgs as $img) {
                if (trim($img->getAttribute('alt')) !== '') $withAlt++;
            }
            $results['alt'] = ($withAlt / $imgs->length) >= 0.5;
        }

        // speed
        $results['speed'] = $loadMs < 3000;

        // ssl: final url is https
        $results['ssl'] = stripos($finalUrl, 'https://') === 0;

        // contact: tel:/mailto: link, or a <form>
        $results['contact'] =
            $xp->query('//a[starts-with(@href,"mailto:")]')->length > 0 ||
            $xp->query('//a[starts-with(@href,"tel:")]')->length > 0 ||
            $xp->query('//form')->length > 0 ||
            strpos($lcHtml, 'wa.me/') !== false ||
            strpos($lcHtml, 'whatsapp') !== false;

        // cta: button, role=button, or input submit
        $results['cta'] =
            $xp->query('//button')->length > 0 ||
            $xp->query('//input[@type="submit"]')->length > 0 ||
            $xp->query('//a[contains(@class,"btn") or contains(@class,"button")]')->length > 0;

        // --- Score ---
        $score  = 0;
        $checks = [];
        foreach ($weights as $key => $w) {
            $pass = (bool) ($results[$key] ?? false);
            if ($pass) $score += $w['points'];
            $checks[$key] = [
                'pass'   => $pass,
                'points' => $w['points'],
                'label'  => $w['label'],
                'issue'  => $w['issue'],
            ];
        }

        return [
            'ok'           => true,
            'score'        => $score,
            'load_time_ms' => $loadMs,
            'checks'       => $checks,
        ];
    }
}

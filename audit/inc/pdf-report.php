<?php
/**
 * audit/inc/pdf-report.php
 * Builds a branded PDF audit report from audit data using dompdf.
 *
 * rc_build_audit_pdf(array $lead, array $checks): string  (raw PDF bytes)
 *   $lead   = ['url','email','score','load_time_ms','audit_id', ...]
 *   $checks = decoded checks_json [ key => ['pass','points','label','issue'] ]
 */

require_once __DIR__ . '/../../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!function_exists('rc_build_audit_pdf')) {

    function rc_build_audit_pdf(array $lead, array $checks): string {
        $score = (int) ($lead['score'] ?? 0);
        $url   = htmlspecialchars($lead['url'] ?? '');
        $aid   = htmlspecialchars($lead['audit_id'] ?? '');
        $load  = (int) ($lead['load_time_ms'] ?? 0);

        if ($score >= 75)     { $band = '#1e8e5a'; $label = 'Strong'; }
        elseif ($score >= 50) { $band = '#c08a1e'; $label = 'Needs work'; }
        else                  { $band = '#c0392b'; $label = 'Critical'; }

        // Failing issues with a short fix suggestion each.
        $fixes = [
            'mobile'   => 'Add a responsive viewport and test on a phone.',
            'title'    => 'Write a clear, keyword-rich page title (50-60 chars).',
            'metadesc' => 'Add a meta description that summarises the page (under 160 chars).',
            'h1'       => 'Add one clear H1 heading that states what the page is about.',
            'alt'      => 'Give every image descriptive alt text for SEO and accessibility.',
            'speed'    => 'Compress images and remove unused scripts to load under 3 seconds.',
            'ssl'      => 'Install an SSL certificate so the site loads over HTTPS.',
            'contact'  => 'Add a visible phone, email, WhatsApp, or contact form.',
            'cta'      => 'Add one clear call-to-action button (Book, Buy, Message).',
        ];

        $rows = '';
        foreach ($checks as $key => $c) {
            $mark  = $c['pass'] ? '&#10003;' : '&#10007;';
            $color = $c['pass'] ? '#1e8e5a' : '#c0392b';
            $rows .= '<tr>'
                . '<td style="color:' . $color . ';font-weight:bold;width:24px">' . $mark . '</td>'
                . '<td>' . htmlspecialchars($c['label']) . '</td>'
                . '<td style="text-align:right;color:#888">' . (int) $c['points'] . ' pts</td>'
                . '</tr>';
        }

        $upgrades = '';
        foreach ($checks as $key => $c) {
            if (!$c['pass'] && isset($fixes[$key])) {
                $upgrades .= '<li><strong>' . htmlspecialchars($c['issue']) . ':</strong> '
                    . htmlspecialchars($fixes[$key]) . '</li>';
            }
        }
        if ($upgrades === '') {
            $upgrades = '<li>Your site covers the fundamentals. Focus next on content, conversion copy, and ongoing SEO.</li>';
        }

        $date = date('j M Y');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { color: #1a1a1a; font-size: 12px; }
  .wrap { padding: 8px 4px; }
  .brand { font-size: 22px; font-weight: bold; color: #6c5ce7; }
  .muted { color: #888; }
  .score-box { text-align: center; margin: 26px 0; }
  .score-num { font-size: 64px; font-weight: bold; color: $band; line-height: 1; }
  .band { display: inline-block; color: $band; border: 2px solid $band;
          padding: 4px 14px; border-radius: 20px; font-weight: bold; margin-top: 8px; }
  h2 { font-size: 15px; border-bottom: 2px solid #eee; padding-bottom: 6px; margin-top: 26px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  td { padding: 7px 6px; border-bottom: 1px solid #eee; }
  ul { padding-left: 18px; }
  li { margin-bottom: 8px; }
  .cta { margin-top: 28px; padding: 16px; background: #f4f2ff; border-radius: 10px; text-align: center; }
  .cta a { color: #6c5ce7; font-weight: bold; text-decoration: none; }
</style></head>
<body><div class="wrap">

  <div class="brand">Rielcode</div>
  <div class="muted">Free Website Audit Report &middot; $date</div>

  <div class="score-box">
    <div class="muted" style="margin-bottom:6px">Audit score for $url</div>
    <div class="score-num">$score<span style="font-size:20px;color:#bbb">/100</span></div>
    <div class="band">$label</div>
    <div class="muted" style="margin-top:10px">Page loaded in {$load}ms</div>
  </div>

  <h2>Full check breakdown</h2>
  <table>$rows</table>

  <h2>What to fix first</h2>
  <ul>$upgrades</ul>

  <div class="cta">
    <p>Want these fixed without lifting a finger?</p>
    <p><a href="https://wa.me/6281295536876">Message Rielcode on WhatsApp</a> &middot; quote audit ID <strong>$aid</strong></p>
  </div>

  <p class="muted" style="margin-top:24px;text-align:center;font-size:10px">
    rielcode.com &middot; Real Code. Real Results.
  </p>

</div></body></html>
HTML;

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}

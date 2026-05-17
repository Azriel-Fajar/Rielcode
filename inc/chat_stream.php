<?php
/**
 * inc/chat_stream.php
 * SSE relay from OpenAI streaming response to the browser.
 *
 * Emits frames:
 *   event: start  data: {"ok":true}
 *   event: delta  data: {"v":"..."}
 *   event: usage  data: {"in":N,"out":N,"total":N}
 *   event: done   data: {"ok":true}
 *   event: error  data: {"code":"RC-CHAT-XXX","reply":"..."}
 *
 * Returns: ['reply' => assembled string, 'usage' => ['in'=>..,'out'=>..,'total'=>..]]
 */

require_once __DIR__ . '/error_codes.php';

if (!function_exists('rc_sse_send')) {
    function rc_sse_send(string $event, array $data): void {
        echo "event: $event\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
        @ob_flush();
        @flush();
    }
}

if (!function_exists('rc_stream_openai')) {
    function rc_stream_openai(string $apiKey, array $payload, bool $verifySsl = true): array {
        // SSE response headers
        @header('Content-Type: text/event-stream; charset=utf-8');
        @header('Cache-Control: no-cache, no-transform');
        @header('X-Accel-Buffering: no');
        @header('Connection: keep-alive');

        // Kill output buffering so chunks reach the browser immediately
        while (ob_get_level() > 0) { @ob_end_flush(); }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        ob_implicit_flush(true);

        rc_sse_send('start', ['ok' => true]);

        // Padding comment: Opera Turbo / some proxies buffer until ~2KB before flushing.
        // SSE comment lines (":...") are ignored by all SSE parsers including our JS client.
        echo ':' . str_repeat(' ', 2048) . "\n\n";
        @ob_flush(); @flush();

        $payload['stream'] = true;
        $payload['stream_options'] = ['include_usage' => true];

        $assembled = '';
        $usage     = ['in' => 0, 'out' => 0, 'total' => 0];
        $buffer    = '';

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'Accept: text/event-stream',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$assembled, &$usage, &$buffer) {
                $buffer .= $chunk;
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line   = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $line   = trim($line);
                    if ($line === '' || strpos($line, 'data:') !== 0) continue;
                    $data = trim(substr($line, 5));
                    if ($data === '[DONE]') continue;
                    $obj = json_decode($data, true);
                    if (!is_array($obj)) continue;

                    // Token usage chunk (final frame when stream_options.include_usage=true)
                    if (isset($obj['usage']) && is_array($obj['usage'])) {
                        $usage = [
                            'in'    => (int)($obj['usage']['prompt_tokens']     ?? 0),
                            'out'   => (int)($obj['usage']['completion_tokens'] ?? 0),
                            'total' => (int)($obj['usage']['total_tokens']      ?? 0),
                        ];
                        rc_sse_send('usage', $usage);
                        continue;
                    }

                    // Delta chunk
                    $delta = $obj['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '') {
                        $assembled .= $delta;
                        rc_sse_send('delta', ['v' => $delta]);
                    }
                }
                return strlen($chunk);
            },
        ]);

        $ok       = curl_exec($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$ok || $err) {
            $resp = rc_error_response('RC-CHAT-003', $err ?: 'curl exec failed');
            rc_sse_send('error', ['code' => $resp['code'], 'reply' => $resp['reply']]);
            return ['reply' => '', 'usage' => $usage, 'http' => $httpCode, 'error' => $err];
        }
        if ($httpCode >= 400) {
            $code = $httpCode >= 500 || $httpCode === 429 ? 'RC-CHAT-005' : 'RC-CHAT-004';
            $resp = rc_error_response($code, "OpenAI HTTP $httpCode");
            rc_sse_send('error', ['code' => $resp['code'], 'reply' => $resp['reply']]);
            return ['reply' => '', 'usage' => $usage, 'http' => $httpCode];
        }

        rc_sse_send('done', ['ok' => true]);
        return ['reply' => $assembled, 'usage' => $usage, 'http' => $httpCode];
    }
}

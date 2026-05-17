<?php
/**
 * inc/ip.php
 * Resolves the real client IP for rate limiting + audit logging.
 * Precedence: Cloudflare > X-Forwarded-For (first hop) > REMOTE_ADDR.
 */

if (!function_exists('rc_client_ip')) {
    function rc_client_ip(): string {
        $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
        if ($cf && filter_var($cf, FILTER_VALIDATE_IP)) return substr($cf, 0, 45);

        $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($xff) {
            $first = trim(explode(',', $xff)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) return substr($first, 0, 45);
        }

        $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return substr($remote, 0, 45);
    }
}

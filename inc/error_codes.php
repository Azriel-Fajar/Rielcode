<?php
/**
 * inc/error_codes.php
 * Central registry. User sees: "<friendly msg> (ref: RC-XXX-NNN)".
 * Internal detail goes to error_log() + audit_log.
 */

const RC_ERRORS = [
    'RC-DB-001'   => ['msg' => 'Service temporarily unavailable.',        'http' => 503],
    'RC-DB-002'   => ['msg' => 'Could not save your request.',            'http' => 500],
    'RC-DB-003'   => ['msg' => 'Record not found.',                       'http' => 404],

    'RC-AUTH-001' => ['msg' => 'Invalid credentials.',                    'http' => 401],
    'RC-AUTH-002' => ['msg' => 'Session expired, please log in again.',   'http' => 401],
    'RC-AUTH-003' => ['msg' => 'Form expired, please reload.',            'http' => 419],

    'RC-CHAT-001' => ['msg' => 'Please type a message.',                  'http' => 400],
    'RC-CHAT-002' => ['msg' => 'RielBot only supports English.',          'http' => 400],
    'RC-CHAT-003' => ['msg' => 'AI temporarily unreachable.',             'http' => 502],
    'RC-CHAT-004' => ['msg' => 'Invalid request to AI.',                  'http' => 400],
    'RC-CHAT-005' => ['msg' => 'AI service is busy.',                     'http' => 503],
    'RC-CHAT-006' => ['msg' => 'No response, please retry.',              'http' => 502],
    'RC-CHAT-007' => ['msg' => 'Streaming error, falling back.',          'http' => 500],

    'RC-RATE-001' => ['msg' => "You've hit the hourly chat limit. Try again in an hour.", 'http' => 429],
    'RC-RATE-002' => ['msg' => 'Daily chat limit reached. Try again tomorrow.',           'http' => 429],
    'RC-RATE-003' => ['msg' => 'Daily AI usage reached, try tomorrow.',                   'http' => 429],

    'RC-ORDER-001' => ['msg' => 'Order not found.',                       'http' => 404],
    'RC-ORDER-002' => ['msg' => 'Selected package is unavailable.',       'http' => 404],
    'RC-ORDER-003' => ['msg' => 'Order could not be saved.',              'http' => 500],
    'RC-ORDER-004' => ['msg' => 'Invalid plan selection.',                'http' => 400],

    'RC-PAY-001'  => ['msg' => 'Invoice could not be generated.',         'http' => 500],
    'RC-PAY-002'  => ['msg' => 'Upload failed, try a smaller image.',     'http' => 400],
    'RC-PAY-003'  => ['msg' => 'Confirmation email could not be sent.',   'http' => 500],

    'RC-ADMIN-001' => ['msg' => 'Forbidden.',                             'http' => 403],
    'RC-ADMIN-002' => ['msg' => 'Action failed.',                         'http' => 500],
    'RC-ADMIN-003' => ['msg' => 'Could not generate token.',              'http' => 500],
];

if (!function_exists('rc_user_msg')) {
    function rc_user_msg(string $code): string {
        $entry = RC_ERRORS[$code] ?? null;
        $msg   = $entry['msg'] ?? 'Something went wrong.';
        return $msg;
    }
}

if (!function_exists('rc_error_response')) {
    /**
     * Build a JSON-friendly error envelope. Caller decides whether to echo + exit.
     * Also writes the internal detail to error_log so admins can grep by ref code.
     */
    function rc_error_response(string $code, ?string $detail = null, ?int $httpOverride = null): array {
        $entry = RC_ERRORS[$code] ?? ['msg' => 'Something went wrong.', 'http' => 500];
        $http  = $httpOverride ?? $entry['http'];

        if ($detail !== null && $detail !== '') {
            error_log("[$code] $detail");
        }

        return [
            'ok'    => false,
            'code'  => $code,
            'http'  => $http,
            'reply' => $entry['msg'] . ' (ref: ' . $code . ')',
        ];
    }
}

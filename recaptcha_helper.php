<?php
require_once __DIR__ . '/recaptcha_config.php';

/**
 * Server-side verify of Google reCAPTCHA.
 * Returns [bool $ok, string $message]
 */
function verify_recaptcha(?string $token, ?string $remote_ip = null): array {
    if (!defined('RECAPTCHA_SECRET_KEY') || RECAPTCHA_SECRET_KEY === 'PASTE_YOUR_SECRET_KEY_HERE') {
        // Keys not configured yet.
        return [false, 'reCAPTCHA is not configured. Please set SITE/SECRET keys in recaptcha_config.php'];
    }

    $token = trim((string)$token);
    if ($token === '') {
        return [false, 'Please complete the reCAPTCHA.'];
    }

    $post_data = http_build_query([
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $remote_ip ?? '',
    ]);

    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $response_json = null;

    // Prefer cURL when available
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response_json = curl_exec($ch);
        curl_close($ch);
    } else {
        // Fallback to file_get_contents
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $post_data,
                'timeout' => 10,
            ]
        ]);
        $response_json = @file_get_contents($url, false, $context);
    }

    if (!$response_json) {
        return [false, 'Unable to verify reCAPTCHA (no response).'];
    }

    $data = json_decode($response_json, true);
    if (!is_array($data)) {
        return [false, 'Unable to verify reCAPTCHA (invalid response).'];
    }

    if (!empty($data['success'])) {
        return [true, ''];
    }

    // Optional: include error codes for debugging
    $codes = '';
    if (!empty($data['error-codes']) && is_array($data['error-codes'])) {
        $codes = ' (' . implode(', ', $data['error-codes']) . ')';
    }

    return [false, 'reCAPTCHA failed. Please try again.' . $codes];
}

<?php
require_once __DIR__ . '/mail_config.php';

/**
 * Delete expired OTPs.
 */
function cleanup_expired_otps(mysqli $conn): void {
    $sql = "DELETE FROM email_otps WHERE expires_at < NOW()";
    @mysqli_query($conn, $sql);
}

/**
 * Generate numeric OTP.
 */
function generate_numeric_otp(int $length = OTP_LENGTH): string {
    $min = (int)('1' . str_repeat('0', max(0, $length - 1)));
    $max = (int)str_repeat('9', $length);
    return (string)random_int($min, $max);
}

/**
 * Store OTP hash in DB and return raw OTP (so it can be emailed).
 */
function create_email_otp(mysqli $conn, string $email, string $purpose, ?int $user_id = null, int $expire_minutes = OTP_EXPIRE_MINUTES): ?string {
    cleanup_expired_otps($conn);

    // Rate-limit: keep only the latest OTP per (email,purpose)
    $del = "DELETE FROM email_otps WHERE email = ? AND purpose = ?";
    if ($stmt = mysqli_prepare($conn, $del)) {
        mysqli_stmt_bind_param($stmt, 'ss', $email, $purpose);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $otp = generate_numeric_otp(OTP_LENGTH);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);

    $sql = "INSERT INTO email_otps (user_id, email, purpose, otp_hash, expires_at)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))";
    if (!$stmt = mysqli_prepare($conn, $sql)) {
        return null;
    }

    // user_id is nullable
    if ($user_id === null) {
        // bind NULL by using a variable and setting it to null
        $null = null;
        mysqli_stmt_bind_param($stmt, 'isssi', $null, $email, $purpose, $otp_hash, $expire_minutes);
    } else {
        mysqli_stmt_bind_param($stmt, 'isssi', $user_id, $email, $purpose, $otp_hash, $expire_minutes);
    }

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok ? $otp : null;
}

/**
 * Verify OTP and delete it after success.
 */
function verify_email_otp(mysqli $conn, string $email, string $purpose, string $otp_input): bool {
    cleanup_expired_otps($conn);

    $sql = "SELECT id, otp_hash, expires_at
            FROM email_otps
            WHERE email = ? AND purpose = ?
            ORDER BY id DESC
            LIMIT 1";
    if (!$stmt = mysqli_prepare($conn, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $email, $purpose);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $row = ($res && mysqli_num_rows($res) === 1) ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if (!$row) return false;

    $hash = $row['otp_hash'] ?? '';

    $ok = false;
    if (password_verify($otp_input, $hash)) {
        $ok = true;
    } elseif (hash_equals($hash, $otp_input)) {
        // legacy support (if otp_hash accidentally stored as plain)
        $ok = true;
    }

    if ($ok) {
        // delete all OTPs for this (email,purpose) after success
        $del = "DELETE FROM email_otps WHERE email = ? AND purpose = ?";
        if ($d = mysqli_prepare($conn, $del)) {
            mysqli_stmt_bind_param($d, 'ss', $email, $purpose);
            mysqli_stmt_execute($d);
            mysqli_stmt_close($d);
        }
    }

    return $ok;
}

/**
 * Send OTP email using PHP mail().
 * NOTE: For localhost, mail() may not work unless SMTP is configured.
 */
function send_otp_email(string $to_email, string $otp, string $purpose): bool {
    $purpose_label = ($purpose === 'login') ? 'Login' : 'Sign Up';
    $subject = "Your {$purpose_label} OTP Code";

    $minutes = (int)OTP_EXPIRE_MINUTES;
    $message = "Your OTP code is: {$otp}\n\n" .
               "This code will expire in {$minutes} minutes.\n\n" .
               "If you did not request this, please ignore this email.";

    $from = MAIL_FROM;
    $from_name = MAIL_FROM_NAME;

    // Common headers
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . $from_name . ' <' . $from . '>';
    if (defined('MAIL_REPLY_TO') && MAIL_REPLY_TO !== '') {
        $headers[] = 'Reply-To: ' . MAIL_REPLY_TO;
    }

    // Recommended: SMTP (works on localhost)
    if (defined('SMTP_ENABLED') && SMTP_ENABLED) {
        return smtp_send_mail(
            SMTP_HOST,
            (int)SMTP_PORT,
            (string)SMTP_SECURE,
            (string)SMTP_USERNAME,
            (string)SMTP_PASSWORD,
            $from,
            $from_name,
            $to_email,
            $subject,
            $message
        );
    }

    // Fallback: PHP mail() (may not work on localhost without server config)
    return @mail($to_email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Minimal SMTP sender (LOGIN auth) – no external library needed.
 * Works with Gmail/most SMTP providers.
 */
function smtp_send_mail(
    string $host,
    int $port,
    string $secure,
    string $username,
    string $password,
    string $from_email,
    string $from_name,
    string $to_email,
    string $subject,
    string $body_text
): bool {
    $secure = strtolower(trim($secure));

    if ($host === '' || $port <= 0) {
        error_log('SMTP config missing: host/port');
        return false;
    }
    if ($username === '' || $password === '') {
        error_log('SMTP config missing: username/password');
        return false;
    }

    $timeout = 20;
    $remote = ($secure === 'ssl') ? "ssl://{$host}:{$port}" : "{$host}:{$port}";
    $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        error_log("SMTP connect failed: {$errno} {$errstr}");
        return false;
    }
    stream_set_timeout($fp, $timeout);

    $read = function() use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            // multi-line responses end when 4th char is space
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $expect = function(string $resp, array $codes) {
        $code = (int)substr($resp, 0, 3);
        return in_array($code, $codes, true);
    };
    $send = function(string $cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };

    $banner = $read();
    if (!$expect($banner, [220])) {
        error_log('SMTP bad banner: ' . trim($banner));
        fclose($fp);
        return false;
    }

    $localHost = 'localhost';
    $send("EHLO {$localHost}");
    $ehlo = $read();
    if (!$expect($ehlo, [250])) {
        // try HELO fallback
        $send("HELO {$localHost}");
        $helo = $read();
        if (!$expect($helo, [250])) {
            error_log('SMTP EHLO/HELO failed: ' . trim($ehlo));
            fclose($fp);
            return false;
        }
    }

    // STARTTLS if requested (tls)
    if ($secure === 'tls') {
        $send('STARTTLS');
        $starttls = $read();
        if (!$expect($starttls, [220])) {
            error_log('SMTP STARTTLS failed: ' . trim($starttls));
            fclose($fp);
            return false;
        }
        $crypto_ok = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($crypto_ok !== true) {
            error_log('SMTP TLS negotiation failed');
            fclose($fp);
            return false;
        }
        // Re-EHLO after TLS
        $send("EHLO {$localHost}");
        $ehlo2 = $read();
        if (!$expect($ehlo2, [250])) {
            error_log('SMTP EHLO after TLS failed: ' . trim($ehlo2));
            fclose($fp);
            return false;
        }
    }

    // AUTH LOGIN
    $send('AUTH LOGIN');
    $r1 = $read();
    if (!$expect($r1, [334])) {
        error_log('SMTP AUTH not accepted: ' . trim($r1));
        fclose($fp);
        return false;
    }
    $send(base64_encode($username));
    $r2 = $read();
    if (!$expect($r2, [334])) {
        error_log('SMTP username rejected: ' . trim($r2));
        fclose($fp);
        return false;
    }
    $send(base64_encode($password));
    $r3 = $read();
    if (!$expect($r3, [235, 503])) { // 503 = already authenticated (rare)
        error_log('SMTP password rejected: ' . trim($r3));
        fclose($fp);
        return false;
    }

    // MAIL FROM / RCPT TO
    $send('MAIL FROM:<' . $from_email . '>');
    $r4 = $read();
    if (!$expect($r4, [250])) {
        error_log('SMTP MAIL FROM failed: ' . trim($r4));
        fclose($fp);
        return false;
    }
    $send('RCPT TO:<' . $to_email . '>');
    $r5 = $read();
    if (!$expect($r5, [250, 251])) {
        error_log('SMTP RCPT TO failed: ' . trim($r5));
        fclose($fp);
        return false;
    }

    // DATA
    $send('DATA');
    $r6 = $read();
    if (!$expect($r6, [354])) {
        error_log('SMTP DATA failed: ' . trim($r6));
        fclose($fp);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [];
    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
    $headers[] = 'To: <' . $to_email . '>';
    if (defined('MAIL_REPLY_TO') && MAIL_REPLY_TO !== '') {
        $headers[] = 'Reply-To: ' . MAIL_REPLY_TO;
    }
    $headers[] = 'Subject: ' . $encodedSubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    // Dot-stuffing
    $safeBody = str_replace(["\r\n", "\r"], "\n", $body_text);
    $safeBody = explode("\n", $safeBody);
    foreach ($safeBody as &$line) {
        if (isset($line[0]) && $line[0] === '.') $line = '.' . $line;
    }
    $safeBody = implode("\r\n", $safeBody);

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $safeBody . "\r\n.";
    $send($data);
    $r7 = $read();
    if (!$expect($r7, [250])) {
        error_log('SMTP message not accepted: ' . trim($r7));
        fclose($fp);
        return false;
    }

    $send('QUIT');
    $read();
    fclose($fp);
    return true;
}

<?php
session_start();

require_once __DIR__ . '/otp_helper.php';

// ---- Messages ----
$info = '';
$error = '';

// ---- DB CONNECTION ----
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection failed: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

cleanup_expired_otps($conn);

// If no pending login, go back
if (empty($_SESSION['pending_login']) || empty($_SESSION['pending_login']['email'])) {
    header('Location: login.php');
    exit();
}

$pending = $_SESSION['pending_login'];
$email = (string)$pending['email'];

// ---- CSRF ----
$csrf_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
        $csrf_error = 'CSRF token missing.';
    } else {
        $max_time = 60 * 60 * 24;
        $token_time = (int)$_SESSION['csrf_token_time'];
        if (($token_time + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = 'CSRF token expired.';
        } elseif (!hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
            $csrf_error = 'CSRF token invalid.';
        }
    }
}

// Generate CSRF token
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

// ---- Actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === '') {

    if (isset($_POST['resend_otp'])) {
        $otp = create_email_otp($conn, $email, 'login', (int)$pending['u_id']);
        if (!$otp) {
            $error = 'Failed to generate OTP. Please try again.';
        } elseif (!send_otp_email($email, $otp, 'login')) {
            $error = 'OTP email send failed (mail() not configured).';
        } else {
            $info = 'OTP re-sent to your email.';
        }
    }

    if (isset($_POST['verify_otp'])) {
        $otp_in = trim($_POST['otp'] ?? '');
        if ($otp_in === '') {
            $error = 'OTP is required.';
        } elseif (!verify_email_otp($conn, $email, 'login', $otp_in)) {
            $error = 'Invalid/expired OTP.';
        } else {
            // OTP success => finish login
            $_SESSION['user_name'] = $pending['u_name'];
            $_SESSION['u_id']      = $pending['u_id'];
            $_SESSION['role']      = $pending['role'];
            unset($_SESSION['pending_login']);
            header('Location: index.php');
            exit();
        }
    }
}

// Reset flow
if (isset($_GET['reset'])) {
    unset($_SESSION['pending_login']);
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login OTP</title>
    <link rel="stylesheet" href="login_style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Verify OTP</h2>

            <p style="margin: 6px 0 12px; color:#166534; font-weight:bold;">
                We sent an OTP to: <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <?php if ($csrf_error !== ''): ?>
                <div style="margin-bottom:10px; color:#b91c1c; background:#fee2e2; padding:8px; border-radius:4px; font-weight:bold;">
                    <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($info !== ''): ?>
                <div style="margin-bottom:10px; color:#166534; background:#dcfce7; padding:8px; border-radius:4px; font-weight:bold;">
                    <?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div style="margin-bottom:10px; color:#b91c1c; background:#fee2e2; padding:8px; border-radius:4px; font-weight:bold;">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="textbox">
                    <input type="text" name="otp" placeholder="Enter OTP" required>
                </div>

                <button type="submit" class="btn" name="verify_otp">Verify & Login</button>
                <button type="submit" class="btn" name="resend_otp" style="margin-top:8px; background:#444;">Resend OTP</button>

                <div class="signup-link">
                    Want to change account? <a href="login_otp.php?reset=1">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
session_start();

require_once __DIR__ . '/otp_helper.php';

// ---- Messages ----
$info = '';
$error = '';
$csrf_error = '';
$field_error = '';

// ---- DB CONNECTION ----
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

cleanup_expired_otps($conn);

if (empty($_SESSION['pending_signup']) || empty($_SESSION['pending_signup']['email'])) {
    header('Location: signup.php');
    exit();
}

$pending = $_SESSION['pending_signup'];
$email = (string)$pending['email'];

// ---- CSRF ----
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
        $otp = create_email_otp($conn, $email, 'signup', null);
        if (!$otp) {
            $error = 'Failed to generate OTP. Please try again.';
        } elseif (!send_otp_email($email, $otp, 'signup')) {
            $error = 'OTP email send failed (mail() not configured).';
        } else {
            $info = 'OTP re-sent to your email.';
        }
    }

    if (isset($_POST['verify_otp'])) {
        $otp_in = trim($_POST['otp'] ?? '');
        if ($otp_in === '' || !preg_match('/^[0-9]{4,8}$/', $otp_in)) {
            $field_error = 'Please enter a valid OTP.';
        } elseif (!verify_email_otp($conn, $email, 'signup', $otp_in)) {
            $error = 'Invalid or expired OTP.';
        } else {
            // Create account
            $sql = "INSERT INTO user(u_name, email, phone, password, role) VALUES(?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssss',
                    $pending['u_name'],
                    $pending['email'],
                    $pending['phone'],
                    $pending['password_hash'],
                    $pending['role']
                );

                if (mysqli_stmt_execute($stmt)) {
                    $uid = mysqli_insert_id($conn);
                    $_SESSION['uid'] = $uid;

                    unset($_SESSION['pending_signup']);

                    if ($pending['role'] === 'Farmer') {
                        header('Location:farmersignup.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit();
                }

                $error = 'Query error: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Query preparation error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

// Reset flow
if (isset($_GET['reset'])) {
    unset($_SESSION['pending_signup']);
    header('Location: signup.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Sign Up OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 bg-cover bg-center" style="background-image: url('cover-crops.png');">

<div class="flex justify-center items-center min-h-screen">
    <div class="bg-white bg-opacity-75 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Verify OTP</h2>

        <p class="text-sm text-gray-700 mb-4">
            We sent an OTP to: <b><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></b>
        </p>

        <?php if ($csrf_error !== ''): ?>
            <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
                <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($info !== ''): ?>
            <div class="mb-4 text-green-700 font-semibold bg-green-100 p-2 rounded">
                <?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-4">
                <label for="otp" class="block text-sm font-medium text-gray-700">OTP Code</label>
                <input type="text" id="otp" name="otp" maxlength="8"
                       class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg"
                       required placeholder="Enter OTP">
                <?php if ($field_error !== ''): ?>
                    <div class="text-red-500 text-sm"><?php echo htmlspecialchars($field_error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>

          
            <div class="mb-4">
                <button type="submit" name="verify_otp" value="1" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                    Verify & Create Account
                </button>
            </div>

            <div class="mb-4">
                <button type="submit" name="resend_otp" value="1" class="w-full bg-gray-700 text-white py-2 px-4 rounded-lg hover:bg-gray-800">
                    Resend OTP
                </button>
            </div>

            <div class="text-center">
                <a href="signup_otp.php?reset=1" class="text-sm text-blue-600 hover:text-blue-800">Start over</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

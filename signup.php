<?php
session_start();

require_once __DIR__ . '/recaptcha_helper.php';
require_once __DIR__ . '/otp_helper.php';

// ---- CSRF status messages ----
$csrf_error = "";
$csrf_success = "";

// ---- Form field + error variables ----
$name = $email = $phone = $password = $role = '';
$error = array('name' => '', 'email' => '', 'phone' => '', 'password' => '', 'role' => '', 'otp' => '');

// ---- reCAPTCHA error message ----
$captcha_error = "";

// ---- OTP messages ----
$otp_info = "";
$otp_error = "";

// ---- DB CONNECTION ----
// ---- DB CONNECTION ----
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');

if (!$conn) {
    die('Connection error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// Always cleanup expired OTPs
cleanup_expired_otps($conn);

// If user already requested OTP, redirect to OTP page
if (!empty($_SESSION['pending_signup'])) {
    header('Location: signup_otp.php');
    exit();
}

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
        $csrf_error = "CSRF token missing.";
    } else {
        $max_time = 60 * 60 * 24; // 24 hours
        $token_time = $_SESSION['csrf_token_time'];

        if (($token_time + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } else {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $csrf_error = "CSRF token invalid.";
            }
        }
    }

    if ($csrf_error === "") {
        $csrf_success = "CSRF token successful. Proceed to next step.";
    }
}

// ---- Generate new CSRF token for the form ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();


// STEP 1: Request OTP (validate form + reCAPTCHA) and send OTP

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === '') {

    // Fill back values
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // ---- reCAPTCHA VALIDATION ----
    [$ok, $msg] = verify_recaptcha($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR'] ?? null);
    if (!$ok) {
        $captcha_error = $msg;
    }

    // ---- FIELD VALIDATION ----
    if ($name === '') {
        $error['name'] = 'Full Name is required.<br>';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $error['name'] = 'Name can only contain letters and spaces.<br>';
    }

    if ($email === '') {
        $error['email'] = 'Email is required.<br>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'Email must be a valid email address.<br>';
    }

    if ($phone === '') {
        $error['phone'] = 'Phone number is required.<br>';
    } elseif (!preg_match('/^[0-9]{11}$/', $phone)) {
        $error['phone'] = 'Phone number must be exactly 11 digits.<br>';
    }

    if ($password === '') {
        $error['password'] = 'Password is required.<br>';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error['password'] = 'Password must be at least 8 characters long, include 1 uppercase, 1 lowercase, 1 digit, and 1 special character.<br>';
    }

    if ($role === '') {
        $error['role'] = 'Role selection is required.<br>';
    }

    // Email unique check
    if ($error['email'] === '') {
        $chk = "SELECT 1 FROM user WHERE email = ? LIMIT 1";
        if ($st = mysqli_prepare($conn, $chk)) {
            mysqli_stmt_bind_param($st, 's', $email);
            mysqli_stmt_execute($st);
            $res = mysqli_stmt_get_result($st);
            if ($res && mysqli_num_rows($res) > 0) {
                $error['email'] = 'This email is already registered. Please login.<br>';
            }
            mysqli_stmt_close($st);
        }
    }

    // If ok => create + send OTP
    if (!array_filter($error) && $captcha_error === '') {

        $otp = create_email_otp($conn, $email, 'signup', null);
        if ($otp === null) {
            $otp_error = "Could not generate OTP. Please try again.";
        } else {
            $sent = send_otp_email($email, $otp, 'signup');
            if (!$sent) {
                $otp_error = "OTP generated, but email could not be sent (mail() not configured on this server).";
            } else {
                $otp_info = "OTP sent to your email. Please enter it below.";
            }

            // Store pending signup in session
            $_SESSION['pending_signup'] = [
                'u_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role
            ];

            // Redirect to OTP page
            header('Location: signup_otp.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Sign Up Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-gray-100 bg-cover bg-center" style="background-image: url('cover-crops.png');">

    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white bg-opacity-75 p-8 rounded-lg shadow-lg w-full max-w-md">

            <h2 class="text-2xl font-bold text-center mb-6">Create Your Account</h2>

            <!-- Messages -->
            <?php if ($csrf_error !== ""): ?>
                <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
                    <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($csrf_success !== "" && $csrf_error === ""): ?>
                <div class="mb-4 text-green-600 font-semibold bg-green-100 p-2 rounded">
                    <?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($captcha_error !== ""): ?>
                <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
                    <?php echo htmlspecialchars($captcha_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($otp_info !== ""): ?>
                <div class="mb-4 text-green-600 font-semibold bg-green-100 p-2 rounded">
                    <?php echo htmlspecialchars($otp_info, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($otp_error !== ""): ?>
                <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
                    <?php echo htmlspecialchars($otp_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>


                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="name" name="name"
                               value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                               class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg"
                               required placeholder="Enter your full name">
                        <div class="text-red-500 text-sm"><?php echo $error['name']; ?></div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                               class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg"
                               required placeholder="Enter your email address">
                        <div class="text-red-500 text-sm"><?php echo $error['email']; ?></div>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                               class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg"
                               required placeholder="Enter your phone number">
                        <div class="text-red-500 text-sm"><?php echo $error['phone']; ?></div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password <span class="text-xs">(at least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special character)</span>
                        </label>
                        <input type="password" id="password" name="password"
                               class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg"
                               required placeholder="Create a password">
                        <div class="text-red-500 text-sm"><?php echo $error['password']; ?></div>
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg" required>
                            <option value="">Select Role</option>
                            <option value="Farmer" <?php if ($role == 'Farmer') echo 'selected'; ?>>Farmer</option>
                            <option value="Buyer" <?php if ($role == 'Buyer') echo 'selected'; ?>>Buyer</option>
                            <option value="Admin" <?php if ($role == 'Admin') echo 'selected'; ?>>Admin</option>
                        </select>
                        <div class="text-red-500 text-sm"><?php echo $error['role']; ?></div>
                    </div>

                    <!-- reCAPTCHA -->
                    <div class="mb-4 flex justify-center">
                        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-4">
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                            Sign Up
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm">Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">Login here</a></p>
                    </div>
                </form>

        </div>
    </div>

</body>
</html>

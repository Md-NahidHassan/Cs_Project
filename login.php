<?php
session_start();

require_once __DIR__ . '/recaptcha_helper.php';
require_once __DIR__ . '/otp_helper.php';

// ---- CSRF status messages ----
$csrf_error = "";
$csrf_success = "";

// ---- Login error message ----
$login_error = "";

// ---- reCAPTCHA error message ----
$captcha_error = "";

// ---- DB CONNECTION ----
// ---- DB CONNECTION ----
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');

if (!$conn) {
    die('Connection failed: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// SETTINGS
$WINDOW_SECONDS = 15;   // window for counting failed attempts
$MAX_ATTEMPTS   = 3;    // red limit
$BLOCK_SECONDS  = 15;   // lock duration
$MAX_STRIKES    = 2;    // 2nd lock => blacklist

/**
 * Localhost test override:
 * http://localhost/login.php?test_ip=8.8.8.8
 * Remove before hosting online.
 */
function get_client_ip_for_testing(): string {
    $real = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (isset($_GET['test_ip']) && in_array($real, ['127.0.0.1', '::1'], true)) {
        $fake = trim($_GET['test_ip']);
        if (filter_var($fake, FILTER_VALIDATE_IP)) return $fake;
    }
    return $real;
}

function is_ip_blacklisted(mysqli $conn, string $ip): bool {
    $sql = "SELECT 1 FROM ip_blacklist WHERE ip = ? LIMIT 1";
    if (!$stmt = mysqli_prepare($conn, $sql)) return false;
    mysqli_stmt_bind_param($stmt, "s", $ip);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = ($res && mysqli_num_rows($res) > 0);
    mysqli_stmt_close($stmt);
    return $ok;
}

/**
 * Returns MAX strike_count for that IP (from ip_details).
 * strike_count is stored on temp_block/blacklisted rows.
 */
function get_ip_strikes(mysqli $conn, string $ip): int {
    $sql = "SELECT COALESCE(MAX(strike_count), 0) AS strikes FROM ip_details WHERE ip = ?";
    if (!$stmt = mysqli_prepare($conn, $sql)) return 0;
    mysqli_stmt_bind_param($stmt, "s", $ip);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $strikes = 0;
    if ($res && mysqli_num_rows($res) === 1) {
        $strikes = (int)mysqli_fetch_assoc($res)['strikes'];
    }
    mysqli_stmt_close($stmt);
    return $strikes;
}

/**
 * Count FAILED attempts in last WINDOW seconds.
 * We only count status='ok' rows (failed attempts recorded as ok).
 */
function failed_attempts_in_window(mysqli $conn, string $ip, int $since): int {
    $sql = "SELECT COUNT(*) AS total_count
            FROM ip_details
            WHERE ip = ? AND status = 'ok' AND login_time > ?";
    if (!$stmt = mysqli_prepare($conn, $sql)) return 0;
    mysqli_stmt_bind_param($stmt, "si", $ip, $since);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $count = 0;
    if ($res && mysqli_num_rows($res) === 1) {
        $count = (int)mysqli_fetch_assoc($res)['total_count'];
    }
    mysqli_stmt_close($stmt);
    return $count;
}

/**
 * Check current temp block remaining seconds.
 * Looks at latest temp_block row and computes remaining seconds.
 */
function temp_block_seconds_left(mysqli $conn, string $ip, int $BLOCK_SECONDS): int {
    $sql = "SELECT login_time
            FROM ip_details
            WHERE ip = ? AND status = 'temp_block'
            ORDER BY id DESC
            LIMIT 1";
    if (!$stmt = mysqli_prepare($conn, $sql)) return 0;
    mysqli_stmt_bind_param($stmt, "s", $ip);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $left = 0;
    if ($res && mysqli_num_rows($res) === 1) {
        $start = (int)mysqli_fetch_assoc($res)['login_time']; // your schema is INT ✅
        $left = max(0, ($start + $BLOCK_SECONDS) - time());
    }
    mysqli_stmt_close($stmt);
    return $left;
}

/**
 * Insert into ip_details (supports NULL u_id correctly)
 */
function insert_ip_details(mysqli $conn, string $ip, ?int $u_id, int $login_time, int $strike_count, string $status): void {
    if ($u_id === null) {
        $sql = "INSERT INTO ip_details (ip, u_id, login_time, strike_count, status)
                VALUES (?, NULL, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiis", $ip, $login_time, $strike_count, $status);
            // ^ can't bind "s" for $ip if signature wrong, so do correct below
            mysqli_stmt_close($stmt);
        }
        // Correct binding:
        $sql = "INSERT INTO ip_details (ip, u_id, login_time, strike_count, status)
                VALUES (?, NULL, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "siis", $ip, $login_time, $strike_count, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return;
    }

    $sql = "INSERT INTO ip_details (ip, u_id, login_time, strike_count, status)
            VALUES (?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "siiis", $ip, $u_id, $login_time, $strike_count, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/**
 * Insert into blacklist (supports NULL u_id correctly; no u_id=0!)
 */
function insert_blacklist(mysqli $conn, string $ip, ?int $u_id, string $reason): void {
    if ($u_id === null) {
        $sql = "INSERT INTO ip_blacklist (ip, u_id, status, reason)
                VALUES (?, NULL, 'banned', ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $ip, $reason);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return;
    }

    $sql = "INSERT INTO ip_blacklist (ip, u_id, status, reason)
            VALUES (?, ?, 'banned', ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sis", $ip, $u_id, $reason);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$client_ip = get_client_ip_for_testing();

// If user is already in OTP step, show the OTP page
if (!empty($_SESSION['pending_login'])) {
    header('Location: login_otp.php');
    exit();
}


// UI lock state
$seconds_left_ui = 0;
$is_locked = false;

// Pre-check permanent blacklist
if (is_ip_blacklisted($conn, $client_ip)) {
    $login_error = "Your IP is permanently blocked.";
    $is_locked = true;
} else {
    // Pre-check temp block
    $seconds_left_ui = temp_block_seconds_left($conn, $client_ip, $BLOCK_SECONDS);
    if ($seconds_left_ui > 0) {
        $login_error = "Too many attempts. Try again in {$seconds_left_ui} seconds.";
        $is_locked = true;
    }
}

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check token presence
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time'])
    ) {
        $csrf_error = "CSRF token missing.";
    } else {
        $max_time   = 60 * 60 * 24;
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

    // ✅ Hard block on POST too (so you cannot attempt during lock)
    if ($login_error === "" && $csrf_error === "") {
        if (is_ip_blacklisted($conn, $client_ip)) {
            $login_error = "Your IP is permanently blocked.";
            $is_locked = true;
        } else {
            $seconds_left_ui = temp_block_seconds_left($conn, $client_ip, $BLOCK_SECONDS);
            if ($seconds_left_ui > 0) {
                $login_error = "Too many attempts. Try again in {$seconds_left_ui} seconds.";
                $is_locked = true;
            }
        }
    }

    // NOTE:
    // OTP verify/resend happens in login_otp.php (separate OTP page as requested).

    // ---- reCAPTCHA VALIDATION (runs on POST) ---- Main task 
    if (isset($_POST['login']) && $csrf_error === "" && $login_error === "") {
        [$ok, $msg] = verify_recaptcha($_POST['g-recaptcha-response'] ?? '', $client_ip);
        if (!$ok) {
            $captcha_error = $msg;
        }
    }

    // ---- LOGIN LOGIC ----
    if (isset($_POST['login']) && $csrf_error === "" && $login_error === "" && $captcha_error === "") {

        $username = trim($_POST['username'] ?? '');
        $pwd      = $_POST['password'] ?? '';

        // Lookup user for u_id
        $found_user = null;
        $found_u_id = null;

        $sql  = "SELECT u_id, u_name, email, password, role FROM user WHERE u_name = ? LIMIT 1";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $found_user = mysqli_fetch_assoc($result);
                $found_u_id = (int)$found_user['u_id'];
            }
            mysqli_stmt_close($stmt);
        }

        // Verify password
        $login_ok = false;
        if ($found_user) {
            $stored_pass = $found_user['password'];

            if (password_verify($pwd, $stored_pass)) {
                $login_ok = true;
            } elseif (hash_equals($stored_pass, $pwd)) {
                $login_ok = true;

                // upgrade plain -> hash
                $new_hash = password_hash($pwd, PASSWORD_DEFAULT);
                $upd_sql  = "UPDATE user SET password = ? WHERE u_id = ?";
                if ($upd_stmt = mysqli_prepare($conn, $upd_sql)) {
                    mysqli_stmt_bind_param($upd_stmt, "si", $new_hash, $found_u_id);
                    mysqli_stmt_execute($upd_stmt);
                    mysqli_stmt_close($upd_stmt);
                }
            }
        }

        if ($login_ok) {
            // ✅ Step-1 ok (password ok) => send OTP to user's email
            $user_email = $found_user['email'] ?? '';
            if ($user_email === '') {
                $login_error = "Email not found for this account.";
            } else {
                $otp = create_email_otp($conn, $user_email, 'login', $found_u_id);
                if (!$otp) {
                    $login_error = "Failed to generate OTP. Please try again.";
                } else {
                    $sent = send_otp_email($user_email, $otp, 'login');
                    if (!$sent) {
                        $login_error = "OTP email send failed (mail() not configured).";
                    } else {
                        $_SESSION['pending_login'] = [
                            'u_id' => (int)$found_user['u_id'],
                            'u_name' => $found_user['u_name'],
                            'role' => $found_user['role'],
                            'email' => $user_email,
                        ];
                        // inactivity timer starts here
            $_SESSION['last_activity'] = time();
                        // Redirect to OTP page
                        header('Location: login_otp.php');
                        exit();
                    }
                }
            }
        }

        if (!$login_ok) {

        // ❌ Login failed => record failed attempt as status='ok'
        $now = time();
        $current_strikes = get_ip_strikes($conn, $client_ip);

        insert_ip_details($conn, $client_ip, $found_u_id, $now, $current_strikes, 'ok');

        // Now check failed count including this just-recorded attempt
        $since = time() - $WINDOW_SECONDS;
        $fail_count = failed_attempts_in_window($conn, $client_ip, $since);

        if ($fail_count >= $MAX_ATTEMPTS) {
            // Lock event (strike increases)
            $new_strikes = $current_strikes + 1;

            if ($new_strikes >= $MAX_STRIKES) {
                // Permanent blacklist
                insert_blacklist($conn, $client_ip, $found_u_id, "Second lockout (3 failures twice).");
                insert_ip_details($conn, $client_ip, $found_u_id, time(), $new_strikes, 'blacklisted');

                $login_error = "Your IP is permanently blocked due to repeated suspicious activity.";
                $seconds_left_ui = 0;
                $is_locked = true;
            } else {
                // Temp block 15 sec
                insert_ip_details($conn, $client_ip, $found_u_id, time(), $new_strikes, 'temp_block');

                $seconds_left_ui = $BLOCK_SECONDS;
                $login_error = "Too many attempts. Try again in {$seconds_left_ui} seconds.";
                $is_locked = true;
            }
        } else {
            $remaining = $MAX_ATTEMPTS - $fail_count;
            $login_error = "Invalid credentials! Attempts remaining: {$remaining}";
        }

        } // end if(!$login_ok)
    }
}

// ---- Generate new CSRF token ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token']      = $token;
$_SESSION['csrf_token_time'] = time();

 // inactivity timer starts here
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FarmHub</title>
    <link rel="stylesheet" href="login_style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>

            <!-- Live countdown message -->
            <p id="lockMsg" style="font-weight:bold; color:red; margin-top:6px;">
                <?php echo $seconds_left_ui > 0 ? "Too many attempts. Try again in {$seconds_left_ui} seconds." : ""; ?>
            </p>

            <!-- CSRF messages -->
            <?php if ($csrf_error !== ""): ?>
                <div style="margin-bottom:10px; color:#b91c1c; background:#fee2e2; padding:8px; border-radius:4px; font-weight:bold;">
                    <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($csrf_success !== "" && $csrf_error === ""): ?>
                <div style="margin-bottom:10px; color:#166534; background:#dcfce7; padding:8px; border-radius:4px; font-weight:bold;">
                    <?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            

            <form method="POST">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="textbox">
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="textbox">
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn" name="login" id="loginBtn">Login</button>

                <!-- CAPTCHA in the Login Page -->
                <div class="captcha-wrap">
                    <div class="g-recaptcha"
                        data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
            </form>


            

            <?php if (!empty($login_error)): ?>
                <p style="font-weight: bold; color: red;">
                    <?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

<script>
(function () {
    let secondsLeft = <?php echo (int)$seconds_left_ui; ?>;
    const btn = document.getElementById('loginBtn');
    const msgEl = document.getElementById('lockMsg');

    function tick() {
        if (secondsLeft <= 0) {
            if (btn) btn.disabled = false;
            if (msgEl) msgEl.textContent = "";
            return;
        }
        if (btn) btn.disabled = true;
        if (msgEl) msgEl.textContent = "Too many attempts. Try again in " + secondsLeft + " seconds.";
        secondsLeft--;
        setTimeout(tick, 1000);
    }

    if (secondsLeft > 0) tick();
})();
</script>
</body>
</html>

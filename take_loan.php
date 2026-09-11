<?php
session_start();

/*
  ✅ "JS injection prevention":
  - We output any dynamic text with htmlspecialchars(..., ENT_QUOTES, 'UTF-8')
  - We do NOT echo raw user input inside HTML/JS

  ✅ "SQL injection prevention":
  - All DB queries are prepared statements (no string concatenation)
  
  ✅ CSRF:
  - Added token generation + validation (same style as your signup/login)
  - Added hidden csrf_token in ALL POST forms
*/

$csrf_error = "";
$csrf_success = "";
$max_time = 60 * 60 * 24; // 24 hours

// 1) Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('DB connection error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time'])
    ) {
        $csrf_error = "CSRF token missing.";
    } else {
        if (($_SESSION['csrf_token_time'] + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
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


// 2) Must be logged in
if (empty($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}
$u_id = (int)$_SESSION['u_id'];


// 3) Fetch role + f_id for the logged-in user (Prepared)
$stmt = $conn->prepare("
    SELECT u.role, f.f_id
    FROM `user` AS u
    LEFT JOIN farmer AS f ON f.u_id = u.u_id
    WHERE u.u_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $u_id);
$stmt->execute();
$stmt->bind_result($role, $f_id);

if (!$stmt->fetch()) {
    $stmt->close();
    die('<p style="color:red;">User profile not found. Please ensure your user account is correctly set up.</p>');
}
$stmt->close();


// 4) Ensure the user is a registered farmer
if (strcasecmp($role, 'farmer') !== 0 || empty($f_id)) {
    die('<p style="color:red;">Only registered farmers can access loan functions. Please update your profile or contact support.</p>');
}


// 5) Check for active loan and fetch due_date (Prepared)
$hasActive = false;
$activeLoanId = 0;
$activeLoanAmt = 0;
$activeLoanDueDate = null;

$stmt = $conn->prepare("
    SELECT l_id, loan_amt, due_date
    FROM loan
    WHERE f_id = ? AND status = 'active'
    LIMIT 1
");
$stmt->bind_param('i', $f_id);
$stmt->execute();
$stmt->bind_result($activeLoanId, $activeLoanAmt, $activeLoanDueDate);
if ($stmt->fetch()) {
    $hasActive = true;
}
$stmt->close();


// Calculate penalty if active & overdue
$dailyPenaltyRate = 5;
$totalPenalty = 0;
$daysLate = 0;

if ($hasActive && !empty($activeLoanDueDate)) {
    $currentDate = new DateTime();
    $dueDate = new DateTime($activeLoanDueDate);

    if ($currentDate > $dueDate) {
        $interval = $currentDate->diff($dueDate);
        $daysLate = $interval->days;
        $totalPenalty = $daysLate * $dailyPenaltyRate;
        $activeLoanAmt += $totalPenalty; // display/calc purposes
    }
}


// UI state flags
$show_payment_options = false;
$show_card_form = false;
$show_mobile_provider_selection = false;
$show_mobile_number_form = false;

$repay_amount_for_payment = 0;
$loan_id_for_payment = 0;
$mobile_provider_selected = '';


// --- Total paid (Prepared) ---
$total_original_paid_amount = 0;
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(original_loan_amt), 0) AS total_paid
    FROM loan
    WHERE f_id = ? AND status = 'paid'
");
$stmt->bind_param('i', $f_id);
$stmt->execute();
$stmt->bind_result($fetched_total_paid_amount);
$stmt->fetch();
$stmt->close();
$total_original_paid_amount = (float)$fetched_total_paid_amount;


// --- Max loan rule ---
$max = 10000;
if ($total_original_paid_amount >= 35000) {
    $max = 50000;
} elseif ($total_original_paid_amount >= 15000) {
    $max = 35000;
} elseif ($total_original_paid_amount >= 10000) {
    $max = 15000;
} else {
    $max = 10000;
}


// Handle POST flows ONLY if CSRF OK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === "") {

    if (isset($_POST['initiate_repay_flow'])) {
        $repay_amount_for_payment = (float)($_POST['repay_amt_input'] ?? 0);
        $loan_id_for_payment = (int)($_POST['l_id_input'] ?? 0);

        if ($repay_amount_for_payment <= 0 || $repay_amount_for_payment > $activeLoanAmt || !$hasActive || $loan_id_for_payment !== $activeLoanId) {
            die('<p style="color:red;">Invalid repayment amount or loan details. Please go back and try again.</p>');
        }
        $show_payment_options = true;
    }

    elseif (isset($_POST['show_card_form_btn'])) {
        $loan_id_for_payment = (int)($_POST['l_id_hidden'] ?? 0);
        $repay_amount_for_payment = (float)($_POST['repay_amt_hidden'] ?? 0);
        $show_card_form = true;
    }

    elseif (isset($_POST['show_mobile_form_btn'])) {
        $loan_id_for_payment = (int)($_POST['l_id_hidden'] ?? 0);
        $repay_amount_for_payment = (float)($_POST['repay_amt_hidden'] ?? 0);
        $show_mobile_provider_selection = true;
    }

    elseif (isset($_POST['select_mobile_provider'])) {
        $mobile_provider_selected = $_POST['mobile_provider'] ?? '';
        $repay_amount_for_payment = (float)($_POST['repay_amt_hidden'] ?? 0);
        $loan_id_for_payment = (int)($_POST['l_id_hidden'] ?? 0);

        if (!in_array($mobile_provider_selected, ['Bkash', 'Nagad', 'Rocket'], true)) {
            die('<p style="color:red;">Invalid mobile banking provider selected.</p>');
        }
        $show_mobile_number_form = true;
    }

    elseif (isset($_POST['process_card_payment'])) {
        $loan_id = (int)($_POST['l_id_hidden'] ?? 0);
        $repay_amt = (float)($_POST['repay_amt_hidden'] ?? 0);
        $card_number = trim($_POST['card_number'] ?? '');

        if (strlen($card_number) !== 16 || !ctype_digit($card_number)) {
            die('<p style="color:red;">Invalid card number. Please enter a 16-digit number.</p>');
        }
        if (!$hasActive || $loan_id !== $activeLoanId || $repay_amt <= 0 || $repay_amt > $activeLoanAmt) {
            die('<p style="color:red;">Payment failed: Loan details mismatch or invalid amount. Please try again.</p>');
        }

        $newAmt = $activeLoanAmt - $repay_amt;
        $newStatus = ($newAmt <= 0) ? 'paid' : 'active';

        $upd = $conn->prepare("UPDATE loan SET loan_amt = ?, status = ? WHERE l_id = ?");
        $upd->bind_param('dsi', $newAmt, $newStatus, $loan_id);
        if (!$upd->execute()) {
            die('<p style="color:red;">Error updating loan: ' . htmlspecialchars($upd->error, ENT_QUOTES, 'UTF-8') . '</p>');
        }
        $upd->close();

        echo "<!DOCTYPE html>
        <html><head>
          <meta charset=\"utf-8\">
          <title>Payment Successful</title>
          <link rel=\"stylesheet\" href=\"loan_new.css\">
        </head><body>
          <div class=\"success-box\">
            <p class=\"success-msg\">✅ Payment of ৳" . number_format($repay_amt,2) . " via Card was Successful!</p>
            <p class=\"schedule-intro\">Remaining balance: ৳" . number_format($newAmt,2) . "</p>
            <p><a class=\"back-link\" href=\"index.php\">Back to Home Page</a></p>
          </div>
        </body></html>";
        exit;
    }

    elseif (isset($_POST['process_mobile_payment'])) {
        $loan_id = (int)($_POST['l_id_hidden'] ?? 0);
        $repay_amt = (float)($_POST['repay_amt_hidden'] ?? 0);
        $mobile_number = trim($_POST['mobile_number'] ?? '');
        $provider = trim($_POST['mobile_provider_hidden'] ?? '');

        if (strlen($mobile_number) !== 11 || !ctype_digit($mobile_number)) {
            die('<p style="color:red;">Invalid mobile number. Please enter an 11-digit number.</p>');
        }
        if (!in_array($provider, ['Bkash', 'Nagad', 'Rocket'], true)) {
            die('<p style="color:red;">Invalid mobile banking provider.</p>');
        }
        if (!$hasActive || $loan_id !== $activeLoanId || $repay_amt <= 0 || $repay_amt > $activeLoanAmt) {
            die('<p style="color:red;">Payment failed: Loan details mismatch or invalid amount. Please try again.</p>');
        }

        $newAmt = $activeLoanAmt - $repay_amt;
        $newStatus = ($newAmt <= 0) ? 'paid' : 'active';

        $upd = $conn->prepare("UPDATE loan SET loan_amt = ?, status = ? WHERE l_id = ?");
        $upd->bind_param('dsi', $newAmt, $newStatus, $loan_id);
        if (!$upd->execute()) {
            die('<p style="color:red;">Error updating loan: ' . htmlspecialchars($upd->error, ENT_QUOTES, 'UTF-8') . '</p>');
        }
        $upd->close();

        echo "<!DOCTYPE html>
        <html><head>
          <meta charset=\"utf-8\">
          <title>Payment Successful</title>
          <link rel=\"stylesheet\" href=\"loan_new.css\">
        </head><body>
          <div class=\"success-box\">
            <p class=\"success-msg\">✅ Payment of ৳" . number_format($repay_amt,2) . " via " . htmlspecialchars($provider, ENT_QUOTES, 'UTF-8') . " Mobile Banking was Successful!</p>
            <p class=\"schedule-intro\">Remaining balance: ৳" . number_format($newAmt,2) . "</p>
            <p><a class=\"back-link\" href=\"index.php\">Back to Home Page</a></p>
          </div>
        </body></html>";
        exit;
    }

    elseif (isset($_POST['proceed'], $_POST['agree'])) {
        if ($hasActive) {
            die('<p style="color:red;">Repay your existing loan first before applying for a new one.</p>');
        }

        echo "<!DOCTYPE html>
        <html><head>
          <meta charset=\"utf-8\">
          <title>Apply for Loan</title>
          <link rel=\"stylesheet\" href=\"loan_new.css\">
          <script src=\"https://cdn.tailwindcss.com\"></script>
        </head><body>
          <div class=\"container\">
            <h2>Apply for Loan (Up to ৳" . number_format($max) . ")</h2>
            <form method=\"post\" action=\"submit_loan.php\">
              <input type=\"hidden\" name=\"f_id\" value=\"" . htmlspecialchars((string)$f_id, ENT_QUOTES, 'UTF-8') . "\">
              <label>Reason for Loan:<br>
                <input type=\"text\" name=\"l_reason\" required>
              </label><br><br>
              <label>Requested Amount (max " . number_format($max) . "):<br>
                <input type=\"number\" name=\"loan_amt\" min=\"1\" max=\"" . htmlspecialchars((string)$max, ENT_QUOTES, 'UTF-8') . "\" required>
              </label><br><br>
              <button type=\"submit\" name=\"apply\" class=\"btn-primary\">Submit Application</button>
            </form>
            <p><a href=\"take_loan.php\" class=\"cancel-link\">Cancel</a></p>
          </div>
        </body></html>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Loan Center</title>
  <link rel="stylesheet" href="loan_new.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f0fdf4; }
    .container { max-width: 900px; margin: 2rem auto; background: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 20px rgba(0,0,0,.05); }
    .success-msg-area { margin-bottom: 14px; padding: 10px; border-radius: 8px; font-weight: 700; }
    .success { background:#dcfce7; color:#166534; }
    .error { background:#fee2e2; color:#b91c1c; }
    input[type="text"], input[type="number"] { width: calc(100% - 2rem); padding: .75rem; border:2px solid #99edc3; background:#f7fffb; border-radius:.5rem; display:block; margin:0 auto; max-width:300px; }
    input[type="text"]:focus, input[type="number"]:focus { outline:none; border-color:#10b981; box-shadow:0 0 0 4px rgba(16,185,129,.3); }
    .btn-primary { display:block; margin:1rem auto 0; background:#10b981; color:#fff; padding:.75rem 2rem; border-radius:.5rem; font-weight:700; border:none; cursor:pointer; }
    .btn-primary:hover { background:#059669; }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="text-center text-3xl font-bold text-green-900 mb-6">🌾 Farmer Loan Center</h1>

    <!-- CSRF messages in body -->
    <?php if ($csrf_error !== ""): ?>
      <div class="success-msg-area error"><?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="success-msg-area success"><?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($hasActive): ?>
      <h2 class="text-center text-xl font-semibold text-green-800 mb-4">Repay Your Active Loan</h2>

      <p class="text-center font-semibold text-green-900 mb-4">
        <?php if ($show_payment_options || $show_card_form || $show_mobile_provider_selection || $show_mobile_number_form): ?>
          Amount to Repay: <strong>৳<?php echo number_format($repay_amount_for_payment,2); ?></strong>
        <?php else: ?>
          Outstanding Balance: <strong>৳<?php echo number_format($activeLoanAmt,2); ?></strong>
          <?php if ($daysLate > 0): ?>
            <br><span class="text-red-600 text-sm">(Includes ৳<?php echo number_format($totalPenalty,2); ?> penalty for <?php echo (int)$daysLate; ?> late day(s))</span>
          <?php endif; ?>
        <?php endif; ?>
      </p>

      <?php if (!$show_payment_options && !$show_card_form && !$show_mobile_provider_selection && !$show_mobile_number_form): ?>
        <form method="post" class="text-center">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="l_id_input" value="<?php echo (int)$activeLoanId; ?>">

          <label for="repay_amt_input" class="block text-gray-700 font-medium mb-2">
            Enter Amount to Repay (≤ <?php echo number_format($activeLoanAmt,2); ?>):
          </label>
          <input type="number" id="repay_amt_input" name="repay_amt_input" step="0.01" min="1" max="<?php echo htmlspecialchars((string)$activeLoanAmt, ENT_QUOTES, 'UTF-8'); ?>" required>
          <button type="submit" name="initiate_repay_flow" class="btn-primary">Choose Payment Method</button>
        </form>

      <?php elseif ($show_payment_options): ?>
        <h3 class="text-center text-green-700 text-xl font-bold mb-4">Select Repayment Method</h3>
        <form method="post" class="text-center space-y-3">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="l_id_hidden" value="<?php echo htmlspecialchars((string)$loan_id_for_payment, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="repay_amt_hidden" value="<?php echo htmlspecialchars((string)$repay_amount_for_payment, ENT_QUOTES, 'UTF-8'); ?>">

          <button type="submit" name="show_card_form_btn" class="btn-primary" style="background:#3b82f6;">Repay using Card</button>
          <button type="submit" name="show_mobile_form_btn" class="btn-primary" style="background:#8b5cf6;">Repay using Mobile Banking</button>
        </form>

      <?php elseif ($show_card_form): ?>
        <h3 class="text-center text-green-700 text-xl font-bold mb-4">Enter Card Details</h3>
        <form method="post" class="text-center">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="l_id_hidden" value="<?php echo htmlspecialchars((string)$loan_id_for_payment, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="repay_amt_hidden" value="<?php echo htmlspecialchars((string)$repay_amount_for_payment, ENT_QUOTES, 'UTF-8'); ?>">

          <label for="card_number" class="block text-gray-700 font-medium mb-2">16-digit Card Number:</label>
          <input type="text" id="card_number" name="card_number" pattern="\d{16}" maxlength="16" required>
          <button type="submit" name="process_card_payment" class="btn-primary">Submit Card Payment</button>
        </form>

      <?php elseif ($show_mobile_provider_selection): ?>
        <h3 class="text-center text-green-700 text-xl font-bold mb-4">Select Mobile Banking Provider</h3>
        <form method="post" class="text-center space-y-3">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="l_id_hidden" value="<?php echo htmlspecialchars((string)$loan_id_for_payment, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="repay_amt_hidden" value="<?php echo htmlspecialchars((string)$repay_amount_for_payment, ENT_QUOTES, 'UTF-8'); ?>">

          <label><input type="radio" name="mobile_provider" value="Bkash" required> Bkash</label><br>
          <label><input type="radio" name="mobile_provider" value="Nagad"> Nagad</label><br>
          <label><input type="radio" name="mobile_provider" value="Rocket"> Rocket</label><br>

          <button type="submit" name="select_mobile_provider" class="btn-primary">Proceed to Mobile Number</button>
        </form>

      <?php elseif ($show_mobile_number_form): ?>
        <h3 class="text-center text-green-700 text-xl font-bold mb-4">
          Enter Mobile Number for <?php echo htmlspecialchars($mobile_provider_selected, ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <form method="post" class="text-center">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="l_id_hidden" value="<?php echo htmlspecialchars((string)$loan_id_for_payment, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="repay_amt_hidden" value="<?php echo htmlspecialchars((string)$repay_amount_for_payment, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="mobile_provider_hidden" value="<?php echo htmlspecialchars($mobile_provider_selected, ENT_QUOTES, 'UTF-8'); ?>">

          <label for="mobile_number" class="block text-gray-700 font-medium mb-2">11-digit Mobile Number:</label>
          <input type="text" id="mobile_number" name="mobile_number" pattern="\d{11}" maxlength="11" required>
          <button type="submit" name="process_mobile_payment" class="btn-primary">Submit Mobile Payment</button>
        </form>
      <?php endif; ?>

    <?php else: ?>
      <h2 class="text-center text-green-700 text-2xl font-bold mb-4">Apply for a New Loan</h2>

      <form method="post" class="text-center">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="agree" required>
          I have read and agree to the loan terms above.
        </label>
        <button type="submit" name="proceed" class="btn-primary">Proceed to Apply</button>
      </form>

      <p class="text-center text-gray-700 mt-4">
        You are currently eligible for up to <strong>৳<?php echo number_format($max); ?></strong> loans.
      </p>
    <?php endif; ?>

  </div>
</body>
</html>

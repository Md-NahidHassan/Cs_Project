<?php
session_start();

/*
  ✅ Same upgrades as your Local Price “do same” version:
  - CSRF token (24h expiry + hash_equals)
  - Prepared statements for BOTH inserts (national_price + prev_mp)
  - Validation (crop_name/type/price/date/status/country)
  - Transaction (commit/rollback)
  - Safe error output (htmlspecialchars)
*/

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: nationalprice.php");
    exit();
}

$admin_id = (int)($_SESSION['u_id'] ?? 0);

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die("Connection error: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

$error = '';
$csrf_error = '';
$csrf_success = '';

// ---------------- CSRF CHECK ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
        $csrf_error = "CSRF token missing.";
    } else {
        $max_time = 60 * 60 * 24; // 24 hours
        if (($_SESSION['csrf_token_time'] + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
        }
    }

    if ($csrf_error === '') {
        $csrf_success = "CSRF token successful.";
    }
}

// ---------------- HANDLE POST (only if CSRF ok) ----------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && $csrf_error === '') {

    $crop_name      = trim($_POST['crop_name'] ?? '');
    $type           = trim($_POST['type'] ?? '');
    $national_price = $_POST['national_price'] ?? '';
    $update_time    = trim($_POST['update_time'] ?? '');
    $status         = trim($_POST['status'] ?? '');
    $country_name   = trim($_POST['country_name'] ?? '');

    if ($admin_id <= 0) {
        $error = "Invalid admin session.";
    } elseif ($crop_name === '') {
        $error = 'Crop name is required.';
    } elseif ($type === '') {
        $error = 'Type is required.';
    } elseif (!is_numeric($national_price) || (float)$national_price < 0) {
        $error = 'National price must be a non-negative number.';
    } elseif ($update_time === '') {
        $error = 'Update date is required.';
    } elseif ($status === '') {
        $error = 'Status is required.';
    } elseif ($country_name === '') {
        $error = 'Country name is required.';
    }

    if ($error === '') {
        $price_num = (float)$national_price;

        // Optional: Validate date format YYYY-MM-DD
        $dt = DateTime::createFromFormat('Y-m-d', $update_time);
        if (!$dt || $dt->format('Y-m-d') !== $update_time) {
            $error = "Update date must be in YYYY-MM-DD format.";
        } else {

            mysqli_begin_transaction($conn);

            try {
                // 1) Insert into national_price
                $stmt1 = mysqli_prepare($conn, "
                    INSERT INTO national_price (u_id, crop_name, type, national_price, update_time, status, country_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$stmt1) {
                    throw new Exception("Prepare failed (national_price): " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param(
                    $stmt1,
                    "issdsss",
                    $admin_id,
                    $crop_name,
                    $type,
                    $price_num,
                    $update_time,
                    $status,
                    $country_name
                );

                if (!mysqli_stmt_execute($stmt1)) {
                    throw new Exception("Execute failed (national_price): " . mysqli_stmt_error($stmt1));
                }
                mysqli_stmt_close($stmt1);

                // 2) Insert into prev_mp (history)
                $stmt2 = mysqli_prepare($conn, "
                    INSERT INTO prev_mp (
                        u_id, crop_name, type, old_price, update_time, status, country_name, source_table, changed_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'national_price', NOW())
                ");
                if (!$stmt2) {
                    throw new Exception("Prepare failed (prev_mp): " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param(
                    $stmt2,
                    "issdsss",
                    $admin_id,
                    $crop_name,
                    $type,
                    $price_num,
                    $update_time,
                    $status,
                    $country_name
                );

                if (!mysqli_stmt_execute($stmt2)) {
                    throw new Exception("Execute failed (prev_mp): " . mysqli_stmt_error($stmt2));
                }
                mysqli_stmt_close($stmt2);

                mysqli_commit($conn);

                header("Location: nationalprice.php?msg=added");
                exit();

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

// ---------------- Generate CSRF token for form ----------------
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<?php include("navbar.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add National Price</title>
  <style>
    :root { --farm-green:#166534; --farm-dark:#14532d; }
    body { font-family: Arial, sans-serif; background:#f0fdf4; padding:20px; }

    .max-w-xl { max-width:40rem; margin-left:auto; margin-right:auto; }
    .mt-10 { margin-top:2.5rem; }
    .bg-white { background-color:white; }
    .shadow-md { box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .rounded-lg { border-radius:0.5rem; }
    .p-8 { padding:2rem; }
    .border { border-width:1px; border-style:solid; }
    .border-farm-green { border-color:var(--farm-green); }

    h2 { color:var(--farm-dark); font-weight:600; font-size:1.5rem; margin-bottom:1.5rem; text-align:center; }
    form > div { margin-bottom:1rem; }
    label { display:block; color:var(--farm-dark); font-weight:600; margin-bottom:0.25rem; }

    input[type="text"], input[type="number"], input[type="date"] {
      width:100%; padding:0.5rem 1rem; border:1px solid #d1d5db; border-radius:0.375rem;
      font-size:1rem; transition:border-color 0.2s, box-shadow 0.2s;
    }
    input:focus { outline:none; border-color:var(--farm-green); box-shadow:0 0 0 3px rgba(22,101,52,0.3); }

    button {
      display:block; width:100%; background-color:var(--farm-green); color:white; padding:0.5rem 0;
      border:none; border-radius:0.375rem; font-weight:700; cursor:pointer; font-size:1.125rem;
      transition:background-color 0.2s;
    }
    button:hover { background-color:#14532d; }

    .text-center { text-align:center; }

    .error-box {
      background-color:#f87171; color:white; padding:0.75rem; margin-bottom:1rem;
      border-radius:0.375rem; font-weight:600; text-align:center;
    }
    .info-box {
      background-color:#60a5fa; color:white; padding:0.75rem; margin-bottom:1rem;
      border-radius:0.375rem; font-weight:600; text-align:center;
    }
  </style>
</head>
<body>

<div class="max-w-xl mt-10 bg-white shadow-md rounded-lg p-8 border border-farm-green">
  <h2>Add National Price</h2>

  <?php if ($csrf_error !== ''): ?>
    <div class="error-box"><?= htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php elseif ($csrf_success !== '' && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="info-box"><?= htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($error !== ''): ?>
    <div class="error-box"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="POST">
    <!-- ✅ CSRF token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

    <div>
      <label for="crop_name">Crop Name</label>
      <input type="text" id="crop_name" name="crop_name" required
        value="<?= isset($_POST['crop_name']) ? htmlspecialchars($_POST['crop_name'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div>
      <label for="type">Type</label>
      <input type="text" id="type" name="type" required
        value="<?= isset($_POST['type']) ? htmlspecialchars($_POST['type'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div>
      <label for="national_price">National Price (৳)</label>
      <input type="number" step="0.01" id="national_price" name="national_price" required min="0"
        value="<?= isset($_POST['national_price']) ? htmlspecialchars($_POST['national_price'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div>
      <label for="update_time">Update Date</label>
      <input type="date" id="update_time" name="update_time" required
        value="<?= isset($_POST['update_time']) ? htmlspecialchars($_POST['update_time'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div>
      <label for="status">Status</label>
      <input type="text" id="status" name="status" required
        value="<?= isset($_POST['status']) ? htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div>
      <label for="country_name">Country Name</label>
      <input type="text" id="country_name" name="country_name" required
        value="<?= isset($_POST['country_name']) ? htmlspecialchars($_POST['country_name'], ENT_QUOTES, 'UTF-8') : '' ?>" />
    </div>

    <div class="text-center">
      <button type="submit">➕ Add Record</button>
    </div>

    <div class="text-center mt-4">
      <a href="nationalprice.php"
         style="display:inline-block;background-color:grey;color:white;padding:0.5rem 1.5rem;border-radius:0.375rem;font-weight:600;text-decoration:none;">
        Go Back
      </a>
    </div>
  </form>
</div>

</body>
</html>

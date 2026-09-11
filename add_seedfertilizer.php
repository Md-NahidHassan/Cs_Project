<?php
session_start();

/*
  ✅ Same upgrades applied (like your other pages):
  - CSRF token (24h expiry + hash_equals)
  - Prepared statements (INSERT) to prevent SQL injection
  - Validation (type/quantity/price)
  - Safer error output (htmlspecialchars)
*/

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: sf_list.php");
    exit();
}

$admin_id = (int)($_SESSION['u_id'] ?? 0);

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

$error = '';
$success = '';
$csrf_error = '';
$csrf_success = '';

// ---- CSRF VALIDATION (POST) ----
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

// ---- HANDLE POST (only if CSRF ok) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === '') {

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? '';

    if ($name === '') {
        $error = 'Name is required.';
    } elseif (!in_array($type, ['Seed', 'Fertilizer'], true)) {
        $error = 'Type must be either "Seed" or "Fertilizer".';
    } elseif (!is_numeric($quantity) || (float)$quantity < 0) {
        $error = 'Quantity must be a non-negative number.';
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $error = 'Price must be a non-negative number.';
    } elseif ($admin_id <= 0) {
        $error = 'Invalid admin session.';
    }

    if ($error === '') {
        $quantity_num = (float)$quantity;
        $price_num = (float)$price;

        // Prepared INSERT
        $stmt = mysqli_prepare($conn, "
            INSERT INTO seeds_fertilizer (name, type, quantity, price, admin_id)
            VALUES (?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            $error = "Database prepare error: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        } else {
            mysqli_stmt_bind_param($stmt, "ssddi", $name, $type, $quantity_num, $price_num, $admin_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: seedadmindashboard.php");
                exit();
            } else {
                $error = "Database error: " . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// ---- Generate new CSRF token for form ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<?php include("navbar.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add New Seed/Fertilizer</title>
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

    input[type="text"], input[type="number"], select {
      width:100%; padding:0.5rem 1rem; border:1px solid #d1d5db; border-radius:0.375rem;
      font-size:1rem; transition:border-color 0.2s, box-shadow 0.2s;
    }

    input[type="text"]:focus, input[type="number"]:focus, select:focus {
      outline:none; border-color:var(--farm-green); box-shadow:0 0 0 3px rgba(22,101,52,0.3);
    }

    button {
      display:block; width:100%; background-color:var(--farm-green); color:white;
      padding:0.5rem 0; border:none; border-radius:0.375rem;
      font-weight:700; cursor:pointer; font-size:1.125rem; transition:background-color 0.2s;
    }
    button:hover { background-color:#14532d; }

    .text-center { text-align:center; }

    .error-box {
      background-color:#f87171; color:white; padding:0.75rem; margin-bottom:1rem;
      border-radius:0.375rem; font-weight:600; text-align:center;
    }
    .success-box {
      background-color:#22c55e; color:white; padding:0.75rem; margin-bottom:1rem;
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
  <h2>Add New Seed or Fertilizer</h2>

  <?php if ($csrf_error !== ''): ?>
    <div class="error-box"><?= htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php elseif ($csrf_success !== '' && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="info-box"><?= htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($error !== ''): ?>
    <div class="error-box"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <!-- ✅ CSRF token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

    <div>
      <label for="name">Name</label>
      <input
        type="text"
        id="name"
        name="name"
        placeholder="Enter name"
        required
        value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '' ?>"
      />
    </div>

    <div>
      <label for="type">Type</label>
      <select id="type" name="type" required>
        <option value="">Select Type</option>
        <option value="Seed" <?= (isset($_POST['type']) && $_POST['type'] === 'Seed') ? 'selected' : '' ?>>Seed</option>
        <option value="Fertilizer" <?= (isset($_POST['type']) && $_POST['type'] === 'Fertilizer') ? 'selected' : '' ?>>Fertilizer</option>
      </select>
    </div>

    <div>
      <label for="quantity">Quantity (kg)</label>
      <input
        type="number"
        step="0.01"
        id="quantity"
        name="quantity"
        placeholder="Enter quantity"
        min="0"
        required
        value="<?= isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity'], ENT_QUOTES, 'UTF-8') : '' ?>"
      />
    </div>

    <div>
      <label for="price">Price (৳)</label>
      <input
        type="number"
        step="0.01"
        id="price"
        name="price"
        placeholder="Enter price"
        min="0"
        required
        value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8') : '' ?>"
      />
    </div>

    <div class="text-center">
      <button type="submit">Add Seed/Fertilizer</button>
    </div>

    <div class="text-center mt-4">
      <a href="seedadmindashboard.php"
         style="display:inline-block;background-color:grey;color:white;padding:0.5rem 1.5rem;border-radius:0.375rem;font-weight:600;text-decoration:none;">
        Go Back
      </a>
    </div>
  </form>
</div>

</body>
</html>

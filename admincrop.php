<?php
session_start();

/*
  ✅ Upgrades added (same style as your other pages):
  - CSRF token (24h expiry + hash_equals)
  - SQL injection prevention (prepared statement)
  - Safer output (htmlspecialchars)
  - Basic validation (numbers for expected_yield, length trim)
*/

$csrf_error = "";
$csrf_success = "";

if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    die("Unauthorized access");
}

$username = $_SESSION['user_name'];
$role = $_SESSION['role'];

// OPTIONAL: If this page is only for admin, enforce it:
if (strtolower($role) !== 'admin') {
    die("Unauthorized: Admin only.");
}

// DB connect
$conn = mysqli_connect('localhost', 'naba', '12345', 'farmsystem');
if (!$conn) {
    die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        $csrf_error = "CSRF token missing.";
    } else {
        $max_time = 60 * 60 * 24; // 24 hours
        $token_time = $_SESSION['csrf_token_time'];

        if (($token_time + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
        }
    }

    if ($csrf_error === "") {
        $csrf_success = "CSRF token successful.";
    }
}

// ---- Handle form submit (only if CSRF OK) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === "") {

    $crop_name = trim($_POST['crop_name'] ?? '');
    $season = trim($_POST['season'] ?? '');
    $expected_yield_raw = trim($_POST['expected_yield'] ?? '');
    $harvest_time = trim($_POST['harvest_time'] ?? '');
    $crop_description = trim($_POST['crop_description'] ?? '');

    // Basic validation
    if ($crop_name === '' || $season === '' || $expected_yield_raw === '' || $harvest_time === '' || $crop_description === '') {
        $csrf_error = "All fields are required.";
    } elseif (!is_numeric($expected_yield_raw)) {
        $csrf_error = "Expected Yield must be a number.";
    } else {
        $expected_yield = (float)$expected_yield_raw;

        // ✅ Prepared statement (no SQL injection)
        $sql = "INSERT INTO crop (c_name, season, expected_yield, harvest_time, c_desp)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            $csrf_error = "Query preparation error: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        } else {
            mysqli_stmt_bind_param($stmt, "ssdss", $crop_name, $season, $expected_yield, $harvest_time, $crop_description);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                // Use safe redirect instead of JS alert (cleaner)
                header("Location: cropman.php?msg=CropAdded");
                exit();
            } else {
                $csrf_error = "Insert failed: " . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// ---- Generate new CSRF token for the form ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add/Edit Crop - FarmHub Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f9f4;
      margin: 0;
      padding: 40px 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      box-sizing: border-box;
      flex-direction: column;
    }

    .welcome {
      font-size: 1.2rem;
      font-weight: 600;
      color: #2e7d32;
      margin-bottom: 20px;
    }

    .container {
      background: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      width: 500px;
    }

    h1 {
      color: #2e7d32;
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 1.8rem;
      text-align: center;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 130px 1fr;
      row-gap: 16px;
      column-gap: 10px;
      align-items: center;
    }

    .form-grid label {
      font-weight: 600;
      color: #2e7d32;
    }

    .form-grid input[type="text"],
    .form-grid textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #a5d6a7;
      border-radius: 8px;
      font-size: 1rem;
      resize: vertical;
    }

    .form-grid textarea {
      height: 100px;
    }

    button {
      grid-column: span 2;
      width: 100%;
      padding: 12px;
      background: linear-gradient(45deg, #80e27e, #4caf50);
      border: none;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: linear-gradient(45deg, #4caf50, #388e3c);
    }

    .back-link {
      margin-top: 20px;
      text-align: center;
    }

    .back-link a {
      color: #388e3c;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    .msg-error {
      margin-bottom: 12px;
      color: #b91c1c;
      background: #fee2e2;
      padding: 10px;
      border-radius: 8px;
      font-weight: 700;
      width: 500px;
      box-sizing: border-box;
    }

    .msg-success {
      margin-bottom: 12px;
      color: #166534;
      background: #dcfce7;
      padding: 10px;
      border-radius: 8px;
      font-weight: 700;
      width: 500px;
      box-sizing: border-box;
    }
  </style>
</head>
<body>

  <div class="welcome">
    Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!
    (Role: <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>)
  </div>

  <?php if ($csrf_error !== ""): ?>
    <div class="msg-error"><?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="msg-success"><?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <div class="container">
    <h1>Add/Edit Crop</h1>

    <form method="POST">
      <!-- ✅ CSRF token -->
      <input type="hidden" name="csrf_token"
             value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-grid">
        <label for="crop_name">Crop Name:</label>
        <input type="text" id="crop_name" name="crop_name" required>

        <label for="season">Season (summer):</label>
        <input type="text" id="season" name="season" required>

        <label for="expected_yield">Expected Yield:</label>
        <input type="text" id="expected_yield" name="expected_yield" required>

        <label for="harvest_time">Harvest Time (june):</label>
        <input type="text" id="harvest_time" name="harvest_time" required>

        <label for="crop_description">Crop Description:</label>
        <textarea id="crop_description" name="crop_description" required></textarea>

        <button type="submit">Submit</button>
      </div>
    </form>

    <div class="back-link">
      <a href="cropman.php">← Back to Crops List</a>
    </div>
  </div>

</body>
</html>

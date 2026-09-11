<?php
session_start();

/*
  ✅ “Do same” upgrades (like your national/local pages):
  - CSRF token (24h expiry + hash_equals)
  - Prepared statements (SELECT crop_id + INSERT crop_bd)
  - Validation (role check optional, numeric checks, growsINBD allowed values)
  - Safe output (htmlspecialchars)
*/

if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    die("Unauthorized access");
}

$username = $_SESSION['user_name'];
$role = strtolower($_SESSION['role']);

// Optional: if only admin can access this page, uncomment:
// if ($role !== 'admin') { die("Unauthorized access"); }

$conn = mysqli_connect('localhost', 'naba', '12345', 'farmsystem');
if (!$conn) {
    die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

$message = '';
$crop_name_input = '';

// ---------------- CSRF CHECK ----------------
$csrf_error = '';
$csrf_success = '';

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === '') {

    $crop_name_input = trim($_POST['crop_name'] ?? '');
    $growsINBD = trim($_POST['growsINBD'] ?? '');
    $grows_in_which_country = trim($_POST['grows_in_which_country'] ?? '');
    $national_price = $_POST['national_price'] ?? '';
    $ideal_soil = trim($_POST['ideal_soil'] ?? '');
    $ideal_rainfall = trim($_POST['ideal_rainfall'] ?? '');
    $ideal_temp = $_POST['ideal_temp'] ?? '';
    $tips = trim($_POST['tips'] ?? '');
    $tipstogrowinbd = trim($_POST['tipstogrowinbd'] ?? '');

    // Basic validation
    if ($crop_name_input === '') {
        $message = "Error: Crop name is required.";
    } elseif (!in_array($growsINBD, ['1', '2'], true)) {
        $message = "Error: Grows in BD must be 1 (Yes) or 2 (No).";
    } elseif ($grows_in_which_country === '') {
        $message = "Error: Grows in which country is required.";
    } elseif (!is_numeric($national_price) || (float)$national_price < 0) {
        $message = "Error: National price must be a non-negative number.";
    } elseif ($ideal_soil === '') {
        $message = "Error: Ideal soil is required.";
    } elseif ($ideal_temp === '' || !is_numeric($ideal_temp)) {
        $message = "Error: Ideal temperature must be a number.";
    } else {

        $national_price_num = (float)$national_price;
        $ideal_temp_num = (float)$ideal_temp;

        // 1) Find crop_id using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT crop_id FROM crop WHERE c_name = ? LIMIT 1");
        if (!$stmt) {
            $message = "Error: Prepare failed (find crop): " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        } else {
            mysqli_stmt_bind_param($stmt, "s", $crop_name_input);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $crop_id);
            $found = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if (!$found) {
                $message = "Error: Invalid Crop Name. Please ensure the crop exists in the 'crop' table.";
            } else {
                // 2) Insert into crop_bd using prepared statement
                $ins = mysqli_prepare($conn, "
                    INSERT INTO crop_bd
                    (crop_id, growsINBD, grows_in_which_country, national_price, ideal_soil, ideal_rainfall, ideal_temp, tips, tipstogrowinbd)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$ins) {
                    $message = "Error: Prepare failed (insert crop_bd): " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
                } else {
                    mysqli_stmt_bind_param(
                        $ins,
                        "issdssdss",
                        $crop_id,
                        $growsINBD,
                        $grows_in_which_country,
                        $national_price_num,
                        $ideal_soil,
                        $ideal_rainfall,
                        $ideal_temp_num,
                        $tips,
                        $tipstogrowinbd
                    );

                    if (mysqli_stmt_execute($ins)) {
                        mysqli_stmt_close($ins);
                        echo "<script>alert('Crop BD data added successfully!'); window.location.href='cropman.php';</script>";
                        exit();
                    } else {
                        $message = "Error inserting data: " . htmlspecialchars(mysqli_stmt_error($ins), ENT_QUOTES, 'UTF-8');
                        mysqli_stmt_close($ins);
                    }
                }
            }
        }
    }
}

// ---------------- Generate CSRF token for form ----------------
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add/Edit Crop BD - FarmHub Admin</title>
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
            width: 600px;
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
            grid-template-columns: 150px 1fr;
            row-gap: 16px;
            column-gap: 10px;
            align-items: center;
        }
        .form-grid label {
            font-weight: 600;
            color: #2e7d32;
        }
        .form-grid input[type="text"],
        .form-grid input[type="number"],
        .form-grid textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #a5d6a7;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
        }
        .form-grid textarea { height: 80px; }
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
        button:hover { background: linear-gradient(45deg, #4caf50, #388e3c); }

        .back-link { margin-top: 20px; text-align: center; }
        .back-link a { color: #388e3c; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }

        .error-box {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 10px 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .info-box {
            background: #dbeafe;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            padding: 10px 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="welcome">
        Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!
        (Role: <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>)
    </div>

    <div class="container">
        <h1>Add/Edit Crop BD</h1>

        <?php if ($csrf_error !== ''): ?>
            <div class="error-box"><?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($csrf_success !== '' && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="info-box"><?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="error-box"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-grid">
                <label for="crop_name">Crop Name:</label>
                <input type="text" id="crop_name" name="crop_name" required
                       value="<?php echo htmlspecialchars($crop_name_input, ENT_QUOTES, 'UTF-8'); ?>">

                <label for="growsINBD">Grows in BD (1=Yes / 2=No):</label>
                <input type="text" id="growsINBD" name="growsINBD" required
                       value="<?php echo isset($_POST['growsINBD']) ? htmlspecialchars($_POST['growsINBD'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="grows_in_which_country">Grows in Which Country:</label>
                <input type="text" id="grows_in_which_country" name="grows_in_which_country" required
                       value="<?php echo isset($_POST['grows_in_which_country']) ? htmlspecialchars($_POST['grows_in_which_country'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="national_price">National Price:</label>
                <input type="number" step="0.01" id="national_price" name="national_price" required min="0"
                       value="<?php echo isset($_POST['national_price']) ? htmlspecialchars($_POST['national_price'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="ideal_soil">Ideal Soil:</label>
                <input type="text" id="ideal_soil" name="ideal_soil" required
                       value="<?php echo isset($_POST['ideal_soil']) ? htmlspecialchars($_POST['ideal_soil'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="ideal_rainfall">Ideal Rainfall:</label>
                <input type="text" id="ideal_rainfall" name="ideal_rainfall"
                       value="<?php echo isset($_POST['ideal_rainfall']) ? htmlspecialchars($_POST['ideal_rainfall'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="ideal_temp">Ideal Temperature:</label>
                <input type="number" step="0.01" id="ideal_temp" name="ideal_temp" required
                       value="<?php echo isset($_POST['ideal_temp']) ? htmlspecialchars($_POST['ideal_temp'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="tips">General Tips:</label>
                <input type="text" id="tips" name="tips"
                       value="<?php echo isset($_POST['tips']) ? htmlspecialchars($_POST['tips'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                <label for="tipstogrowinbd">Tips to Grow in BD:</label>
                <textarea id="tipstogrowinbd" name="tipstogrowinbd"><?php
                    echo isset($_POST['tipstogrowinbd']) ? htmlspecialchars($_POST['tipstogrowinbd'], ENT_QUOTES, 'UTF-8') : '';
                ?></textarea>

                <button type="submit">Submit</button>
            </div>
        </form>

        <div class="back-link">
            <a href="cropman.php">← Back to Crops List</a>
        </div>
    </div>
</body>
</html>

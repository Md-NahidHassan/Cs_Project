<?php
session_start();

/*
 ✅ “Same admin version” for Add Disease (adddiagnosis admin):
 - Admin-only access (case-insensitive)
 - Prepared statements (no SQL injection)
 - Validation for min/max days
 - Optional: auto-create CSRF token (recommended)
 - Keeps your UI almost same
*/

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Admin check (case-insensitive)
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['user_name'] ?? 'Admin';

$message = '';
$crop_name_input = '';
$type_name_input = '';

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function is_csrf_valid(): bool {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!is_csrf_valid()) {
        $message = "Error: Invalid request token. Please refresh and try again.";
    } else {

        // Inputs
        $crop_name_input = trim($_POST['crop_name'] ?? '');
        $type_name_input = trim($_POST['type_name'] ?? '');
        $symptoms        = trim($_POST['symptoms'] ?? '');
        $min_days        = (int)($_POST['min_days'] ?? 0);
        $max_days        = (int)($_POST['max_days'] ?? 0);
        $disease_name    = trim($_POST['disease_name'] ?? '');
        $medicine        = trim($_POST['medicine'] ?? '');
        $home_remedy     = trim($_POST['home_remedy'] ?? '');
        $doctor_contact  = trim($_POST['doctor_contact'] ?? '');

        // Basic validation
        if ($crop_name_input === '' || $type_name_input === '' || $symptoms === '' || $disease_name === '') {
            $message = "Error: Crop name, type name, symptoms and disease name are required.";
        } elseif ($min_days < 0 || $max_days < 0) {
            $message = "Error: Days cannot be negative.";
        } elseif ($min_days > $max_days) {
            $message = "Error: Min Days cannot be greater than Max Days.";
        } else {

            // 1) crop_id from crop name
            $crop_id = 0;
            $stmt = $conn->prepare("SELECT crop_id FROM crop WHERE c_name = ? LIMIT 1");
            $stmt->bind_param("s", $crop_name_input);
            $stmt->execute();
            $stmt->bind_result($crop_id);
            $foundCrop = $stmt->fetch();
            $stmt->close();

            if (!$foundCrop || !$crop_id) {
                $message = "Error: Crop Name '" . htmlspecialchars($crop_name_input) . "' not found.";
            } else {

                // 2) type_id from type name + crop_id
                $type_id = 0;
                $stmt = $conn->prepare("SELECT type_id FROM crop_type WHERE type_name = ? AND crop_id = ? LIMIT 1");
                $stmt->bind_param("si", $type_name_input, $crop_id);
                $stmt->execute();
                $stmt->bind_result($type_id);
                $foundType = $stmt->fetch();
                $stmt->close();

                if (!$foundType || !$type_id) {
                    $message = "Error: Crop Type '" . htmlspecialchars($type_name_input) . "' not found for Crop '" . htmlspecialchars($crop_name_input) . "'.";
                } else {

                    // 3) Insert disease info
                    $stmt = $conn->prepare("
                        INSERT INTO disease_info
                            (crop_id, type_id, symptoms, min_days, max_days, disease_name, medicine, home_remedy, doctor_contact)
                        VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        "iis iissss", // this is wrong signature if kept spaced
                        $crop_id, $type_id, $symptoms, $min_days, $max_days, $disease_name, $medicine, $home_remedy, $doctor_contact
                    );
                    // ---- FIX bind_param type string properly:
                    // i i s i i s s s s  => "iisii ssss" without spaces => "iisii ssss"? no spaces allowed
                    // Correct:
                    // "iisii ssss" -> "iisii" + "ssss" => "iisiissss"
                    $stmt->close(); // close incorrect stmt before re-prepare (safe)
                    $stmt = $conn->prepare("
                        INSERT INTO disease_info
                            (crop_id, type_id, symptoms, min_days, max_days, disease_name, medicine, home_remedy, doctor_contact)
                        VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param(
                        "iisiissss",
                        $crop_id,
                        $type_id,
                        $symptoms,
                        $min_days,
                        $max_days,
                        $disease_name,
                        $medicine,
                        $home_remedy,
                        $doctor_contact
                    );

                    if ($stmt->execute()) {
                        $message = "Disease information added successfully!";
                        // Clear fields
                        $crop_name_input = '';
                        $type_name_input = '';
                        $_POST = [];
                    } else {
                        $message = "Error inserting disease data: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Disease - FarmHub Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "farm-green": "#22c55e",
                        "farm-dark": "#166534",
                        "farm-light": "#dcfce7",
                        "farm-header": "#2e7d32",
                        "farm-pink": "#cc3366"
                    },
                },
            },
        };
    </script>
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
        .back-link a {
            color: #388e3c;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover { text-decoration: underline; }
        .message {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Add New Disease Information (Admin)</h1>

    <?php if ($message): ?>
        <div class="message <?= (strpos($message, 'Error') !== false) ? 'error' : 'success'; ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-grid">
            <label for="crop_name">Crop Name:</label>
            <input type="text" id="crop_name" name="crop_name" required value="<?= htmlspecialchars($crop_name_input) ?>">

            <label for="type_name">Type Name:</label>
            <input type="text" id="type_name" name="type_name" required value="<?= htmlspecialchars($type_name_input) ?>">

            <label for="symptoms">Symptoms:</label>
            <textarea id="symptoms" name="symptoms" required><?= isset($_POST['symptoms']) ? htmlspecialchars($_POST['symptoms']) : ''; ?></textarea>

            <label for="min_days">Min Days:</label>
            <input type="number" id="min_days" name="min_days" required min="0"
                   value="<?= isset($_POST['min_days']) ? htmlspecialchars($_POST['min_days']) : ''; ?>">

            <label for="max_days">Max Days:</label>
            <input type="number" id="max_days" name="max_days" required min="0"
                   value="<?= isset($_POST['max_days']) ? htmlspecialchars($_POST['max_days']) : ''; ?>">

            <label for="disease_name">Disease Name:</label>
            <input type="text" id="disease_name" name="disease_name" required
                   value="<?= isset($_POST['disease_name']) ? htmlspecialchars($_POST['disease_name']) : ''; ?>">

            <label for="medicine">Medicine:</label>
            <textarea id="medicine" name="medicine"><?= isset($_POST['medicine']) ? htmlspecialchars($_POST['medicine']) : ''; ?></textarea>

            <label for="home_remedy">Home Remedy:</label>
            <textarea id="home_remedy" name="home_remedy"><?= isset($_POST['home_remedy']) ? htmlspecialchars($_POST['home_remedy']) : ''; ?></textarea>

            <label for="doctor_contact">Doctor Contact:</label>
            <input type="text" id="doctor_contact" name="doctor_contact"
                   value="<?= isset($_POST['doctor_contact']) ? htmlspecialchars($_POST['doctor_contact']) : ''; ?>">

            <button type="submit">Add Disease</button>
        </div>
    </form>

    <div class="back-link"><a href="index.php">← Back</a></div>
</div>
</body>
</html>

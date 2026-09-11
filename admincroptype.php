<?php
session_start();

/*
  ✅ Upgrades added (same as before):
  - CSRF token (24h expiry + hash_equals)
  - SQL injection prevention (prepared statements for SELECT + INSERT)
  - Validation + safer outputs
  - Correct numeric handling (price is double)
*/

$csrf_error = "";
$csrf_success = "";

if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    die("Unauthorized access");
}

$username = $_SESSION['user_name'];
$role = $_SESSION['role'];

// Admin-only (recommended)
if (strtolower($role) !== 'admin') {
    die("Unauthorized: Admin only.");
}

// DB
$hostname = 'localhost';
$db_username = 'root';
$db_password = '';
$database = 'farmsystem';

$connection = mysqli_connect($hostname, $db_username, $db_password, $database);
if (!$connection) {
    die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

$message = '';
$crop_id = 0;
$crop_name_input = '';

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

// ---- PROCESS FORM (only if CSRF OK) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === "") {

    $crop_name_input = trim($_POST['crop_name'] ?? '');
    $num_types = (int)($_POST['num_types'] ?? 0);

    if ($crop_name_input === '') {
        $message = "Crop Name is required.";
    } elseif ($num_types < 1 || $num_types > 3) {
        $message = "Number of Types must be between 1 and 3.";
    } else {
        // Find crop_id from crop_name (prepared)
        $findStmt = mysqli_prepare($connection, "SELECT crop_id FROM crop WHERE c_name = ? LIMIT 1");
        if (!$findStmt) {
            $message = "Query error: " . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8');
        } else {
            mysqli_stmt_bind_param($findStmt, "s", $crop_name_input);
            mysqli_stmt_execute($findStmt);
            mysqli_stmt_bind_result($findStmt, $crop_id_db);
            $found = mysqli_stmt_fetch($findStmt);
            mysqli_stmt_close($findStmt);

            if (!$found) {
                $message = "Invalid Crop Name entered. Please enter a valid crop name.";
            } else {
                $crop_id = (int)$crop_id_db;

                // Prepare INSERT for crop_type
                $insertStmt = mysqli_prepare($connection, "
                    INSERT INTO crop_type
                      (crop_id, type_name, species, price, grain_size, grain_color, disease, strach_content, protein_content)
                    VALUES
                      (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$insertStmt) {
                    $message = "Insert preparation error: " . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8');
                } else {
                    $insertCount = 0;

                    for ($i = 1; $i <= $num_types; $i++) {
                        $type_name = trim($_POST["type_name_$i"] ?? '');
                        $species = trim($_POST["species_$i"] ?? '');
                        $price_raw = trim($_POST["price_$i"] ?? '');
                        $grain_size = trim($_POST["grain_size_$i"] ?? '');
                        $grain_color = trim($_POST["grain_color_$i"] ?? '');
                        $disease = trim($_POST["disease_$i"] ?? '');
                        $strach_content = trim($_POST["strach_content_$i"] ?? '');
                        $protein_content = trim($_POST["protein_content_$i"] ?? '');

                        // Insert only if required fields filled
                        if ($type_name !== '' && $species !== '') {

                            // price can be empty -> 0, else must be numeric
                            $price = 0.0;
                            if ($price_raw !== '') {
                                if (!is_numeric($price_raw)) {
                                    // Skip this row if price invalid
                                    continue;
                                }
                                $price = (float)$price_raw;
                            }

                            // Bind + execute
                            mysqli_stmt_bind_param(
                                $insertStmt,
                                "issdsssss",
                                $crop_id,
                                $type_name,
                                $species,
                                $price,
                                $grain_size,
                                $grain_color,
                                $disease,
                                $strach_content,
                                $protein_content
                            );

                            if (mysqli_stmt_execute($insertStmt)) {
                                $insertCount++;
                            }
                        }
                    }

                    mysqli_stmt_close($insertStmt);

                    if ($insertCount > 0) {
                        $message = $insertCount . " crop type(s) added successfully!";
                    } else {
                        $message = "Please fill in at least one complete Type Name + Species (and make sure Price is numeric if given).";
                    }
                }
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
<title>Add Crop Types - FarmHub Admin</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f9f4;
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .page-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        max-width: 520px;
        padding: 20px;
        box-sizing: border-box;
    }

    .welcome {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2e7d32;
        margin-bottom: 14px;
        text-align: center;
    }

    .msg-error {
        width: 500px;
        box-sizing: border-box;
        margin-bottom: 12px;
        color: #b91c1c;
        background: #fee2e2;
        padding: 10px;
        border-radius: 8px;
        font-weight: 700;
        text-align: center;
    }

    .msg-success {
        width: 500px;
        box-sizing: border-box;
        margin-bottom: 12px;
        color: #166534;
        background: #dcfce7;
        padding: 10px;
        border-radius: 8px;
        font-weight: 700;
        text-align: center;
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
    form > .form-grid {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .form-grid label {
        font-weight: 600;
        color: #2e7d32;
        display: block;
        margin-bottom: 6px;
    }
    .form-grid input[type="text"],
    .form-grid input[type="number"],
    .form-grid select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #a5d6a7;
        border-radius: 8px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    .top-fields {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .top-fields > div {
        flex: 1;
    }
    .type-species-set {
        border: 1px solid #a5d6a7;
        padding: 15px 20px;
        border-radius: 10px;
        background: #e8f5e9;
    }
    .type-species-set .section-title {
        font-weight: 700;
        color: #388e3c;
        margin-bottom: 12px;
        font-size: 1.1rem;
    }
    button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(45deg, #80e27e, #4caf50);
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-top: 10px;
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
</style>

<script>
    function onNumTypesChange() {
        const numTypes = parseInt(document.getElementById('num_types').value);
        for (let i = 1; i <= 3; i++) {
            const section = document.getElementById(`type_species_set_${i}`);
            if (section) section.style.display = (i <= numTypes) ? 'block' : 'none';
        }
    }
    window.onload = function() { onNumTypesChange(); }
</script>
</head>

<body>
<div class="page-wrapper">

    <div class="welcome">
        Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!
        (Role: <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>)
    </div>

    <?php if ($csrf_error !== ""): ?>
        <div class="msg-error"><?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="msg-success"><?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="<?php echo (stripos($message, 'success') !== false) ? 'msg-success' : 'msg-error'; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <h1>Add Crop Types</h1>

        <form method="POST">
            <!-- ✅ CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-grid">

                <div class="top-fields">
                    <div>
                        <label for="crop_name">Crop Name:</label>
                        <input type="text" id="crop_name" name="crop_name" required
                               value="<?php echo htmlspecialchars($crop_name_input, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label for="num_types">Number of Types:</label>
                        <select id="num_types" name="num_types" onchange="onNumTypesChange()" required>
                            <option value="1" <?php if(isset($_POST['num_types']) && $_POST['num_types']=='1') echo 'selected'; ?>>1</option>
                            <option value="2" <?php if(isset($_POST['num_types']) && $_POST['num_types']=='2') echo 'selected'; ?>>2</option>
                            <option value="3" <?php if(isset($_POST['num_types']) && $_POST['num_types']=='3') echo 'selected'; ?>>3</option>
                        </select>
                    </div>
                </div>

                <!-- SET 1 -->
                <div id="type_species_set_1" class="type-species-set" style="display:none;">
                    <div class="section-title">Type/Species Set 1</div>

                    <label for="type_name_1">Type Name:</label>
                    <input type="text" id="type_name_1" name="type_name_1" required
                           value="<?php echo isset($_POST['type_name_1']) ? htmlspecialchars($_POST['type_name_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="species_1">Species:</label>
                    <input type="text" id="species_1" name="species_1" required
                           value="<?php echo isset($_POST['species_1']) ? htmlspecialchars($_POST['species_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="price_1">Price:</label>
                    <input type="number" step="0.01" id="price_1" name="price_1"
                           value="<?php echo isset($_POST['price_1']) ? htmlspecialchars($_POST['price_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_size_1">Grain Size:</label>
                    <input type="text" id="grain_size_1" name="grain_size_1"
                           value="<?php echo isset($_POST['grain_size_1']) ? htmlspecialchars($_POST['grain_size_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_color_1">Grain Color:</label>
                    <input type="text" id="grain_color_1" name="grain_color_1"
                           value="<?php echo isset($_POST['grain_color_1']) ? htmlspecialchars($_POST['grain_color_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="disease_1">Disease:</label>
                    <input type="text" id="disease_1" name="disease_1"
                           value="<?php echo isset($_POST['disease_1']) ? htmlspecialchars($_POST['disease_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="strach_content_1">Starch Content:</label>
                    <input type="text" id="strach_content_1" name="strach_content_1"
                           value="<?php echo isset($_POST['strach_content_1']) ? htmlspecialchars($_POST['strach_content_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="protein_content_1">Protein Content:</label>
                    <input type="text" id="protein_content_1" name="protein_content_1"
                           value="<?php echo isset($_POST['protein_content_1']) ? htmlspecialchars($_POST['protein_content_1'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>

                <!-- SET 2 -->
                <div id="type_species_set_2" class="type-species-set" style="display:none;">
                    <div class="section-title">Type/Species Set 2</div>

                    <label for="type_name_2">Type Name:</label>
                    <input type="text" id="type_name_2" name="type_name_2"
                           value="<?php echo isset($_POST['type_name_2']) ? htmlspecialchars($_POST['type_name_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="species_2">Species:</label>
                    <input type="text" id="species_2" name="species_2"
                           value="<?php echo isset($_POST['species_2']) ? htmlspecialchars($_POST['species_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="price_2">Price:</label>
                    <input type="number" step="0.01" id="price_2" name="price_2"
                           value="<?php echo isset($_POST['price_2']) ? htmlspecialchars($_POST['price_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_size_2">Grain Size:</label>
                    <input type="text" id="grain_size_2" name="grain_size_2"
                           value="<?php echo isset($_POST['grain_size_2']) ? htmlspecialchars($_POST['grain_size_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_color_2">Grain Color:</label>
                    <input type="text" id="grain_color_2" name="grain_color_2"
                           value="<?php echo isset($_POST['grain_color_2']) ? htmlspecialchars($_POST['grain_color_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="disease_2">Disease:</label>
                    <input type="text" id="disease_2" name="disease_2"
                           value="<?php echo isset($_POST['disease_2']) ? htmlspecialchars($_POST['disease_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="strach_content_2">Starch Content:</label>
                    <input type="text" id="strach_content_2" name="strach_content_2"
                           value="<?php echo isset($_POST['strach_content_2']) ? htmlspecialchars($_POST['strach_content_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="protein_content_2">Protein Content:</label>
                    <input type="text" id="protein_content_2" name="protein_content_2"
                           value="<?php echo isset($_POST['protein_content_2']) ? htmlspecialchars($_POST['protein_content_2'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>

                <!-- SET 3 -->
                <div id="type_species_set_3" class="type-species-set" style="display:none;">
                    <div class="section-title">Type/Species Set 3</div>

                    <label for="type_name_3">Type Name:</label>
                    <input type="text" id="type_name_3" name="type_name_3"
                           value="<?php echo isset($_POST['type_name_3']) ? htmlspecialchars($_POST['type_name_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="species_3">Species:</label>
                    <input type="text" id="species_3" name="species_3"
                           value="<?php echo isset($_POST['species_3']) ? htmlspecialchars($_POST['species_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="price_3">Price:</label>
                    <input type="number" step="0.01" id="price_3" name="price_3"
                           value="<?php echo isset($_POST['price_3']) ? htmlspecialchars($_POST['price_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_size_3">Grain Size:</label>
                    <input type="text" id="grain_size_3" name="grain_size_3"
                           value="<?php echo isset($_POST['grain_size_3']) ? htmlspecialchars($_POST['grain_size_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="grain_color_3">Grain Color:</label>
                    <input type="text" id="grain_color_3" name="grain_color_3"
                           value="<?php echo isset($_POST['grain_color_3']) ? htmlspecialchars($_POST['grain_color_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="disease_3">Disease:</label>
                    <input type="text" id="disease_3" name="disease_3"
                           value="<?php echo isset($_POST['disease_3']) ? htmlspecialchars($_POST['disease_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="strach_content_3">Starch Content:</label>
                    <input type="text" id="strach_content_3" name="strach_content_3"
                           value="<?php echo isset($_POST['strach_content_3']) ? htmlspecialchars($_POST['strach_content_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">

                    <label for="protein_content_3">Protein Content:</label>
                    <input type="text" id="protein_content_3" name="protein_content_3"
                           value="<?php echo isset($_POST['protein_content_3']) ? htmlspecialchars($_POST['protein_content_3'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </div>

                <button type="submit">Submit</button>
            </div>
        </form>

        <div class="back-link"><a href="cropman.php">← Back to Crops List</a></div>
    </div>
</div>

<script>
    onNumTypesChange();
</script>
</body>
</html>

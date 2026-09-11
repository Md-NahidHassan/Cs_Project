<?php
session_start();

/*
 ✅ “Same thing” upgrade for govtedit:
 - Role check (case-insensitive) so "Admin"/"admin" both work
 - CSRF token (24h expiry + hash_equals)
 - Prepared statements for INSERT / UPDATE / DELETE / SELECT
 - Safe validation for dates + URL
 - Correct delete operation (your old code never actually deleted)
*/

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// Admin role (case-insensitive)
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: index.php");
    exit();
}

$operation = $_GET['operation'] ?? 'add';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_id   = $_SESSION['u_id'] ?? '';

$error_message = '';
$scheme = null;

/* ---------------- CSRF ---------------- */
$csrf_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'], $_SESSION['csrf_token_time'])) {
        $csrf_error = "CSRF token missing.";
    } else {
        $max_time = 60 * 60 * 24; // 24h
        if (($_SESSION['csrf_token_time'] + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
        }
    }
}

/* ---------------- Helpers ---------------- */
function valid_date_yyyy_mm_dd($date) {
    if ($date === '' || $date === null) return false;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/* ---------------- POST actions ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === '') {

    // DELETE
    if ($operation === 'delete') {
        if ($id <= 0) {
            $error_message = "Invalid scheme id.";
        } else {
            $del = mysqli_prepare($conn, "DELETE FROM govt_scheme WHERE scheme_id = ?");
            if (!$del) {
                $error_message = "Prepare failed: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
            } else {
                mysqli_stmt_bind_param($del, "i", $id);
                if (mysqli_stmt_execute($del)) {
                    mysqli_stmt_close($del);
                    header("Location: govt.php?msg=deleted");
                    exit();
                }
                $error_message = "Delete failed: " . htmlspecialchars(mysqli_stmt_error($del), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($del);
            }
        }
    }

    // ADD / EDIT
    if ($operation === 'add' || $operation === 'edit') {
        $scheme_name = trim($_POST['scheme_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $start_date  = trim($_POST['start_date'] ?? '');
        $end_date    = trim($_POST['end_date'] ?? '');
        $source_link = trim($_POST['source_link'] ?? '');

        // Validation
        if ($scheme_name === '') {
            $error_message = "Scheme name is required.";
        } elseif ($description === '') {
            $error_message = "Description is required.";
        } elseif (!valid_date_yyyy_mm_dd($start_date)) {
            $error_message = "Start date must be valid (YYYY-MM-DD).";
        } elseif ($end_date !== '' && !valid_date_yyyy_mm_dd($end_date)) {
            $error_message = "End date must be valid (YYYY-MM-DD).";
        } elseif ($end_date !== '' && valid_date_yyyy_mm_dd($start_date) && valid_date_yyyy_mm_dd($end_date)) {
            if (strtotime($end_date) < strtotime($start_date)) {
                $error_message = "End date cannot be earlier than start date.";
            }
        } elseif ($source_link !== '' && !filter_var($source_link, FILTER_VALIDATE_URL)) {
            $error_message = "Source link must be a valid URL.";
        }

        // Save
        if ($error_message === '') {
            if ($operation === 'add') {
                $ins = mysqli_prepare($conn, "
                    INSERT INTO govt_scheme (scheme_name, description, start_date, end_date, source_link)
                    VALUES (?, ?, ?, ?, ?)
                ");
                if (!$ins) {
                    $error_message = "Prepare failed: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
                } else {
                    mysqli_stmt_bind_param($ins, "sssss", $scheme_name, $description, $start_date, $end_date, $source_link);
                    if (mysqli_stmt_execute($ins)) {
                        mysqli_stmt_close($ins);
                        header("Location: govt.php?msg=added");
                        exit();
                    }
                    $error_message = "Insert failed: " . htmlspecialchars(mysqli_stmt_error($ins), ENT_QUOTES, 'UTF-8');
                    mysqli_stmt_close($ins);
                }
            } else { // edit
                if ($id <= 0) {
                    $error_message = "Invalid scheme id.";
                } else {
                    $upd = mysqli_prepare($conn, "
                        UPDATE govt_scheme
                        SET scheme_name = ?, description = ?, start_date = ?, end_date = ?, source_link = ?
                        WHERE scheme_id = ?
                    ");
                    if (!$upd) {
                        $error_message = "Prepare failed: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
                    } else {
                        mysqli_stmt_bind_param($upd, "sssssi", $scheme_name, $description, $start_date, $end_date, $source_link, $id);
                        if (mysqli_stmt_execute($upd)) {
                            mysqli_stmt_close($upd);
                            header("Location: govt.php?msg=updated");
                            exit();
                        }
                        $error_message = "Update failed: " . htmlspecialchars(mysqli_stmt_error($upd), ENT_QUOTES, 'UTF-8');
                        mysqli_stmt_close($upd);
                    }
                }
            }
        }
    }
}

/* ---------------- GET: load scheme for edit/delete ---------------- */
if (($operation === 'edit' || $operation === 'delete') && $id > 0) {
    $sel = mysqli_prepare($conn, "SELECT scheme_id, scheme_name, description, start_date, end_date, source_link FROM govt_scheme WHERE scheme_id = ? LIMIT 1");
    if ($sel) {
        mysqli_stmt_bind_param($sel, "i", $id);
        mysqli_stmt_execute($sel);
        $res = mysqli_stmt_get_result($sel);
        $scheme = mysqli_fetch_assoc($res);
        mysqli_stmt_close($sel);
    }
    if (!$scheme) {
        $error_message = "Scheme not found.";
        // If scheme not found, force add mode
        if ($operation !== 'add') $operation = 'add';
    }
}

/* ---------------- CSRF token for form ---------------- */
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars(ucfirst($operation), ENT_QUOTES, 'UTF-8') ?> Scheme - FarmHub Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f9f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background: #ffffff;
            margin: 40px 0;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            width: 420px;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 1.8rem;
            text-align: center;
        }
        .welcome-msg {
            font-size: 1rem;
            color: #4caf50;
            text-align: center;
            margin-bottom: 25px;
        }
        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2e7d32;
        }
        form input[type="text"],
        form input[type="url"],
        form input[type="date"],
        form textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #a5d6a7;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
        }
        form textarea { height: 100px; }
        form button {
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
        form button:hover { background: linear-gradient(45deg, #4caf50, #388e3c); }
        .back-link { margin-top: 20px; text-align: center; }
        .back-link a { color: #388e3c; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
        .error-message {
            background: #ffdddd;
            color: #b00020;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
        .info-message {
            background: #dbeafe;
            color: #1e3a8a;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars(ucfirst($operation), ENT_QUOTES, 'UTF-8') ?> Scheme</h1>
    <p class="welcome-msg">
        Welcome, <strong><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></strong>
        (ID: <?= htmlspecialchars((string)$user_id, ENT_QUOTES, 'UTF-8') ?>)
    </p>

    <?php if ($csrf_error !== ''): ?>
        <div class="error-message"><?= htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($operation === 'delete' && $id > 0): ?>
        <p style="text-align:center;">Are you sure you want to delete this scheme?</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Yes, Delete</button>
        </form>
        <div class="back-link"><a href="govt.php">No, Go Back</a></div>

    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

            <label for="scheme_name">Scheme Name:</label>
            <input type="text" id="scheme_name" name="scheme_name"
                   value="<?= htmlspecialchars($scheme['scheme_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($scheme['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date"
                   value="<?= htmlspecialchars($scheme['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date"
                   value="<?= htmlspecialchars($scheme['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label for="source_link">Source Link:</label>
            <input type="url" id="source_link" name="source_link"
                   value="<?= htmlspecialchars($scheme['source_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit"><?= ($operation === 'edit') ? 'Update' : 'Add' ?> Scheme</button>
        </form>
        <div class="back-link"><a href="govt.php">← Back to List</a></div>
    <?php endif; ?>
</div>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Connect to the database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. Check login
if (empty($_SESSION['user_name'])) {
    // Not logged in — redirect to login page
    header("Location: login.php");
    exit;
}

// 3. Sanitize and fetch user data
$username = mysqli_real_escape_string($conn, $_SESSION['user_name']);

// 4. Optionally store u_id in session if not already stored
if (empty($_SESSION['u_id'])) {
    $result = mysqli_query($conn, "SELECT u_id FROM user WHERE u_name = '$username' LIMIT 1");
    if (!$result || mysqli_num_rows($result) === 0) {
        die("User not found.");
    }
    $user = mysqli_fetch_assoc($result);
    $_SESSION['u_id'] = $user['u_id'];
}

// 5. Set variable for easy access
$u_id = $_SESSION['u_id'];
?>

<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die('Connection failed: ' . mysqli_connect_error());

if (!isset($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}

$u_id = (int)$_SESSION['u_id'];

// First delete from the 'farmer' table (if role is farmer)
if ($_SESSION['role'] === 'farmer') {
    $delete_farmer = "DELETE FROM farmer WHERE u_id = $u_id";
    mysqli_query($conn, $delete_farmer);
}

// Then delete from the 'user' table
$delete_user = "DELETE FROM user WHERE u_id = $u_id";
if (mysqli_query($conn, $delete_user)) {
    // Clear session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php?message=Account+deleted+successfully");
    exit();
} else {
    echo "Error deleting account: " . mysqli_error($conn);
}
?>


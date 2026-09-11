<?php
session_start();

// Admin-only access check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: sf_list.php");
    exit();
}

// Check if admin ID is available
if (!isset($_SESSION['u_id'])) {
    die('Unauthorized: Admin ID not set.');
}
$admin_id = $_SESSION['u_id'];

// Validate and sanitize sf_id
if (!isset($_GET['sf_id']) || !ctype_digit($_GET['sf_id'])) {
    die('Invalid or missing item ID.');
}
$sf_id = (int)$_GET['sf_id'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'farmsystem');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Ensure the record belongs to the logged-in admin
$stmt = $conn->prepare("SELECT sf_id FROM seeds_fertilizer WHERE sf_id = ? AND admin_id = ?");
$stmt->bind_param("ii", $sf_id, $admin_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die('Item not found or access denied.');
}
$stmt->close();

// Delete the seed/fertilizer
$stmt = $conn->prepare("DELETE FROM seeds_fertilizer WHERE sf_id = ?");
$stmt->bind_param("i", $sf_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Redirect with success flag
    header("Location: seedadmindashboard.php?deleted=1");
    exit();
} else {
    $stmt->close();
    $conn->close();
    die('Deletion failed: ' . $conn->error);
}
?>

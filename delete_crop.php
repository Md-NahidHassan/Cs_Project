<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');

if (!isset($_SESSION['user_name'])) die("Unauthorized");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fc_id'])) {
    $fc_id = intval($_POST['fc_id']);
    
    // Optionally verify that the crop belongs to the logged-in user
    $username = $_SESSION['user_name'];
    $sql = "SELECT * FROM user WHERE u_name='$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

   
    $u_id = $user['u_id'];
$query = "SELECT f_id FROM farmer WHERE u_id='$u_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$f_id = $row['f_id'];


    // Check ownership
    $check = mysqli_query($conn, "SELECT * FROM farmer_crop WHERE fc_id='$fc_id' AND f_id='$f_id'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM farmer_crop WHERE fc_id='$fc_id'");
    }
}

header("Location: dashboard.php"); // redirect back
exit;

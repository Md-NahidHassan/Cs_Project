<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: nationalprice.php");
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die("Connection error: " . mysqli_connect_error());
}

$n_id = (int)($_GET['n_id'] ?? 0);
if ($n_id > 0) {
    $sql = "DELETE FROM national_price WHERE n_id = $n_id";
    mysqli_query($conn, $sql);
}

mysqli_close($conn);

header("Location: nationalprice.php?msg=deleted");
exit();

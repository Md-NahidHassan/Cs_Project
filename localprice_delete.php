<?php
session_start();

// Admin check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: localprice.php?msg=deleted");
exit();

}

// DB connect
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validate and delete
if (isset($_GET['l_id']) && is_numeric($_GET['l_id'])) {
    $l_id = intval($_GET['l_id']);

   

    $stmt = mysqli_prepare($conn, "DELETE FROM local_price WHERE l_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $l_id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: localprice.php");
            exit();
        } else {
            echo "Error deleting record: " . mysqli_stmt_error($stmt);
        }
    } else {
        echo "Statement preparation failed: " . mysqli_error($conn);
    }
} else {
    echo "Invalid ID.";
}
?>

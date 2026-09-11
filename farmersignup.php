<?php
// Start session to access UID
session_start();

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    echo 'Connection error: ' . mysqli_connect_error();
    exit();
}

// Initialize variables and errors
$farmer_name = $location = $farm_size = $crop_specialization = '';
$errors = array('farmer_name' => '', 'location' => '', 'farm_size' => '', 'crop_specialization' => '');

// Trim all inputs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = array_map('trim', $_POST);

    // Farmer Name Validation
    if (empty($_POST['farmer_name'])) {
        $errors['farmer_name'] = 'Farmer name is required.';
    } else {
        $farmer_name = mysqli_real_escape_string($conn, $_POST['farmer_name']);
    }

    // Location Validation
    if (empty($_POST['location'])) {
        $errors['location'] = 'Location is required.';
    } else {
        $location = mysqli_real_escape_string($conn, $_POST['location']);
    }

    // Farm Size Validation
    if (empty($_POST['farm_size'])) {
        $errors['farm_size'] = 'Farm size is required.';
    } elseif (!preg_match('/^\d+(\.\d+)?(\s*acres)?$/i', $_POST['farm_size'])) {
        $errors['farm_size'] = 'Invalid farm size format. Example: 5 acres';
    } else {
        $farm_size = mysqli_real_escape_string($conn, $_POST['farm_size']);
    }

    // Crop Specialization Validation
    if (empty($_POST['crop_specialization'])) {
        $errors['crop_specialization'] = 'Crop specialization is required.';
    } else {
        $crop_specialization = mysqli_real_escape_string($conn, $_POST['crop_specialization']);
    }

    // If no errors, insert into database
    if (!array_filter($errors)) {
        if (isset($_SESSION['uid'])) {
            $uid = $_SESSION['uid'];

            $sql = "INSERT INTO farmer (f_name, location, farm_size, crop_sp, u_id)
                    VALUES ('$farmer_name', '$location', '$farm_size', '$crop_specialization', '$uid')";

            if (mysqli_query($conn, $sql)) {
                echo "<p style='color:green;'>Farmer info saved successfully!</p>";
                // Optionally redirect
                header('Location: index.php');
                // exit();
            } else {
                echo "<p style='color:red;'>Database error: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color:red;'>User ID not found in session. Please log in again.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Info</title>
    <link rel="stylesheet" href="farmerstyle.css">
</head>
<body>
    <h2>Enter Farmer Details</h2>
    <form method="POST" action="">
        <label>Farmer Name:</label><br>
        <input type="text" name="farmer_name" value="<?php echo htmlspecialchars($farmer_name); ?>"><br>
        <span style="color:red;"><?php echo $errors['farmer_name']; ?></span><br>

        <label>Location:</label><br>
        <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>"><br>
        <span style="color:red;"><?php echo $errors['location']; ?></span><br>

        <label>Farm Size (e.g. 5 acres):</label><br>
        <input type="text" name="farm_size" value="<?php echo htmlspecialchars($farm_size); ?>"><br>
        <span style="color:red;"><?php echo $errors['farm_size']; ?></span><br>

        <label>Crop Specialization:</label><br>
        <input type="text" name="crop_specialization" value="<?php echo htmlspecialchars($crop_specialization); ?>"><br>
        <span style="color:red;"><?php echo $errors['crop_specialization']; ?></span><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>

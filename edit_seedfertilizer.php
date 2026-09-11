<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: sf_list.php");
    exit();
}

$admin_id = $_SESSION['u_id'];
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die('Connection error: ' . mysqli_connect_error());

$sf_id = (int)($_GET['sf_id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM seeds_fertilizer WHERE sf_id=$sf_id AND admin_id=$admin_id");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "Item not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $quantity = (float)$_POST['quantity'];
    $price = (float)$_POST['price'];

    $sql = "UPDATE seeds_fertilizer
            SET name='$name', type='$type', quantity=$quantity, price=$price
            WHERE sf_id=$sf_id AND admin_id=$admin_id";
    mysqli_query($conn, $sql);

    header("Location: seedadmindashboard.php");
    exit();
}

// Now safe to include navbar (since no header() call will follow)
include("navbar.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Seed/Fertilizer</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0fdf4;
      padding: 20px;
    }
    .container {
      max-width: 550px;
      margin: auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    h2 {
      text-align: center;
      color: #166534;
      font-size: 24px;
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-weight: 600;
      margin-top: 10px;
      color: #333;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }
    button {
      display: block;
      width: 100%;
      background-color:green;
      color: white;
      padding: 0.5rem 0;
      border: none;
      border-radius: 0.375rem;
      font-weight: 700;
      cursor: pointer;
      font-size: 1.125rem;
      transition: background-color 0.2s;
    }

    button:hover {
      background-color: #14532d; /* darker green */
    }
    
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Seed/Fertilizer</h2>
    <form method="POST" action="">
      <label for="name">Name</label>
      <input type="text" name="name" id="name" value="<?= htmlspecialchars($row['name']) ?>" required />

      <label for="type">Type</label>
      <select name="type" id="type" required>
        <option value="Seed" <?= $row['type'] === 'Seed' ? 'selected' : '' ?>>Seed</option>
        <option value="Fertilizer" <?= $row['type'] === 'Fertilizer' ? 'selected' : '' ?>>Fertilizer</option>
      </select>

      <label for="quantity">Quantity (kg)</label>
      <input type="number" step="0.01" name="quantity" id="quantity" value="<?= $row['quantity'] ?>" required />

      <label for="price">Price (৳)</label>
      <input type="number" step="0.01" name="price" id="price" value="<?= $row['price'] ?>" required />

      <button type="submit">Update</button>
    </form>

     <div class="text-center mt-4">
  <a href="seedadmindashboard.php" style="display: inline-block; background-color: grey; color: white; padding: 0.5rem 1.5rem; border-radius: 0.375rem; font-weight: 600; text-decoration: none;">
    Go Back
  </a>
</div>

  </div>
</body>
</html>

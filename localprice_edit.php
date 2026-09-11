<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: localprice.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "farmsystem");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

$l_id = (int)($_GET['l_id'] ?? 0);
$admin_id = $_SESSION['u_id'];

// Fetch old data
$old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM local_price WHERE l_id = $l_id"));
if (!$old) {
    echo "Record not found.";
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_name = $_POST['crop_name'];
    $type = $_POST['type'];
    $local_price = $_POST['local_price'];
    $update_time = $_POST['update_time'];
    $status = $_POST['status'];
    $region = $_POST['region'];

    // Update local_price
    $stmt = $conn->prepare("UPDATE local_price SET crop_name=?, type=?, local_price=?, update_time=?, status=?, region=? WHERE l_id=?");
    $stmt->bind_param("ssdsssi", $crop_name, $type, $local_price, $update_time, $status, $region, $l_id);
    $stmt->execute();

   $priceChanged = ($old['local_price'] != $local_price);
$timeChanged = ($old['update_time'] !== $update_time);  // compare full datetime string
if ($priceChanged && $timeChanged) {
    $stmt3 = $conn->prepare("INSERT INTO prev_mp (u_id, crop_name, type, old_price, update_time, status, region, source_table, changed_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'local_price', NOW())");
    $stmt3->bind_param("issdsss", $admin_id, $old['crop_name'], $old['type'], $local_price, $update_time, $old['status'], $old['region']);
    $stmt3->execute();
    $stmt3->close();
}




    header("Location: localprice.php");
    exit();
}


?>

<?php include("navbar.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Local Price</title>
  <style>
    :root {
      --farm-green: #166534;
      --farm-dark: #14532d;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f0fdf4;
      padding: 20px;
    }
    .max-w-xl {
      max-width: 40rem;
      margin-left: auto;
      margin-right: auto;
    }
    .mt-10 {
      margin-top: 2.5rem;
    }
    .bg-white {
      background-color: white;
    }
    .shadow-md {
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .rounded-lg {
      border-radius: 0.5rem;
    }
    .p-8 {
      padding: 2rem;
    }
    .border {
      border-width: 1px;
      border-style: solid;
    }
    .border-farm-green {
      border-color: var(--farm-green);
    }
    h2 {
      color: var(--farm-dark);
      font-weight: 600;
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    form > div {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      color: var(--farm-dark);
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    input[type="text"],
    input[type="number"],
    input[type="datetime-local"] {
      width: 100%;
      padding: 0.5rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      font-size: 1rem;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    input:focus {
      outline: none;
      border-color: var(--farm-green);
      box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.3);
    }
    select {
      width: 100%;
      padding: 0.5rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      font-size: 1rem;
    }
    button {
      display: block;
      width: 100%;
      background-color: var(--farm-green);
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
      background-color: #14532d;
    }
    .text-center {
      text-align: center;
    }
    .error {
      background-color: #f87171;
      color: white;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: 0.375rem;
      font-weight: 600;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="max-w-xl mt-10 bg-white shadow-md rounded-lg p-8 border border-farm-green">
  <h2>Edit Local Price</h2>

  <?php if ($error): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div>
      <label>Crop Name</label>
      <input type="text" name="crop_name" value="<?= htmlspecialchars($old['crop_name']) ?>" required>
    </div>

    <div>
      <label>Type</label>
      <input type="text" name="type" value="<?= htmlspecialchars($old['type']) ?>" required>
    </div>

    <div>
      <label>Local Price (৳)</label>
      <input type="number" name="local_price" step="0.01" value="<?= $old['local_price'] ?>" required>
    </div>

    <div>
      <label>Update Time</label>
      <input type="datetime-local" name="update_time" value="<?= date('Y-m-d\TH:i', strtotime($old['update_time'])) ?>" required>
    </div>

    <div>
      <label>Status</label>
      <select name="status" required>
        <option value="active" <?= $old['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $old['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>

    <div>
      <label>Region</label>
      <input type="text" name="region" value="<?= htmlspecialchars($old['region']) ?>" required>
    </div>

    <div class="text-center">
      <button type="submit">💾 Update Record</button>
    </div>

    <div class="text-center mt-4">
      <a href="localprice.php" style="display: inline-block; background-color: grey; color: white; padding: 0.5rem 1.5rem; border-radius: 0.375rem; font-weight: 600; text-decoration: none;">
        Go Back
      </a>
    </div>
  </form>
</div>

</body>
</html>

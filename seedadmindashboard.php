<?php
session_start();

// Admin role check BEFORE any output or include
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head><meta charset='UTF-8'><title>Access Denied</title></head>
    <body style='font-family: Arial, sans-serif; padding: 20px;'>
      <h1 style='color: red;'>Access Denied</h1>
      <p>You must be an <strong>admin</strong> to view this page.</p>
      <p><a href='sf_list.php'>Go back to list page</a></p>
    </body>
    </html>";
    exit();
}

include("navbar.php");

$admin_id = $_SESSION['u_id'];

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die('Connection error: ' . mysqli_connect_error());

// Fetch admin name from user table using prepared statement for safety
$admin_name = "Admin"; // default fallback
if ($stmt = mysqli_prepare($conn, "SELECT u_name FROM user WHERE u_id = ? LIMIT 1")) {
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $fetched_name);
    if (mysqli_stmt_fetch($stmt)) {
        $admin_name = $fetched_name;
    }
    mysqli_stmt_close($stmt);
}

// Fetch seeds and fertilizer entries owned by this admin
$result = mysqli_query($conn, "SELECT * FROM seeds_fertilizer WHERE admin_id = $admin_id ORDER BY sf_id DESC");

// Dashboard stats
$countQuery = "SELECT COUNT(*) AS total FROM seeds_fertilizer WHERE admin_id = $admin_id";
$countResult = mysqli_fetch_assoc(mysqli_query($conn, $countQuery));
$totalAdded = $countResult['total'] ?? 0;

$lowStockQuery = "SELECT name, type, quantity FROM seeds_fertilizer WHERE admin_id = $admin_id AND quantity < 10 ORDER BY quantity ASC";
$lowStockResult = mysqli_query($conn, $lowStockQuery);

$soldOutQuery = "SELECT name, type, quantity FROM seeds_fertilizer WHERE admin_id = $admin_id AND quantity <= 0";
$soldOutResult = mysqli_query($conn, $soldOutQuery);
$soldOutCount = mysqli_num_rows($soldOutResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Seed & Fertilizer Admin Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0fdf4;
      padding: 20px;
    }
    .container {
      max-width: 960px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px #ccc;
    }
    .welcome-bar {
      background: #bbf7d0;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-size: 18px;
      color: #065f46;
      text-align: center;
    }
    h2 {
      color: #166534;
      text-align: center;
      margin-bottom: 20px;
    }
    .stats-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 30px;
    }
    .stats-box {
      flex: 1 1 250px;
      background: #e0fce4;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px #ccc;
      overflow-y: auto;
      max-height: 180px;
    }
    .stats-box h3 {
      margin-top: 0;
      color: #166534;
      margin-bottom: 10px;
    }
    .btn-group {
      margin-bottom: 20px;
      text-align: center;
      gap: 10px;
      display: inline-flex;
      flex-wrap: wrap;
      justify-content: center;
      visibility: visible !important;
    }
    .btn {
      padding: 8px 14px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
      font-size: 15px;
      transition: background-color 0.3s ease;
      display: inline-block;
    }
    .btn-add {
      background-color: #16a34a;
      color: white;
    }
    .btn-edit {
      background-color: transparent;
      color: #3b82f6;
    }
    .btn-delete {
      background-color: transparent;
      color: #ef4444;
    }
    .btn-back {
      background-color: #6b7280;
      color: white;
    }
    .btn-sales {
      background-color: #f59e0b;
      color: white;
    }
    .btn:hover {
      opacity: 0.85;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th {
      background-color: #166534;
      color: white;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="welcome-bar">🌿 Welcome, Admin <?= htmlspecialchars($admin_name) ?>!</div>

    <!-- Stats Section -->
    <div class="stats-container">
      <div class="stats-box">
        <h3>Total Items Added</h3>
        <p><strong><?= $totalAdded ?></strong> seeds/fertilizers added</p>
      </div>
      <div class="stats-box">
        <h3>Low Stock (< 10)</h3>
        <?php if (mysqli_num_rows($lowStockResult) === 0): ?>
          <p>All stock levels are healthy</p>
        <?php else: ?>
          <ul>
            <?php while ($row = mysqli_fetch_assoc($lowStockResult)): ?>
              <li><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['type']) ?>): <?= (int)$row['quantity'] ?> left</li>
            <?php endwhile; ?>
          </ul>
        <?php endif; ?>
      </div>
      <div class="stats-box" style="background: #ef4444; color: white; box-shadow: 0 0 10px #ef444499;">
        <h3>Sold Out Items</h3>
        <?php if ($soldOutCount === 0): ?>
          <p>No sold out items</p>
        <?php else: ?>
          <ul>
            <?php
              mysqli_data_seek($soldOutResult, 0);
              while ($rowSold = mysqli_fetch_assoc($soldOutResult)):
                $soldQty = (int)$rowSold['quantity'] <= 0 ? abs((int)$rowSold['quantity']) : 0;
            ?>
              <li><?= htmlspecialchars($rowSold['name']) ?> (<?= htmlspecialchars($rowSold['type']) ?>) — Sold Out (<?= $soldQty ?> unit<?= $soldQty != 1 ? 's' : '' ?>)</li>
            <?php endwhile; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <h2>Your Seeds & Fertilizers</h2>

    <div class="btn-group">
      <a href="add_seedfertilizer.php" class="btn btn-add">+ Add New</a>
      <a href="buy_seeds.php" class="btn btn-sales">View Sales</a>
      <a href="marketlist.php" class="btn btn-back">← Go Back</a>
    </div>

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Quantity (kg)</th>
          <th>Price (৳)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="5">No entries found. Click "Add New" to begin.</td></tr>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td><?= (int)$row['quantity'] ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td>
                <a href="edit_seedfertilizer.php?sf_id=<?= (int)$row['sf_id'] ?>" class="btn btn-edit">Edit</a>
                <a href="delete_seedfertilizer.php?sf_id=<?= (int)$row['sf_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>


  <footer class="bg-gray-900 py-6 mt-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row justify-between items-center text-white">
        <div class="text-2xl font-bold text-farm-green mb-4 md:mb-0">
          FarmHub
        </div>
        <div class="space-x-6 text-sm opacity-75">
          <a href="about.php" class="hover:text-farm-green transition-colors">About Us</a>
          <a href="contact.php" class="hover:text-farm-green transition-colors">Contact</a>
          <a href="privacy.php" class="hover:text-farm-green transition-colors">Privacy Policy</a>
          <a href="terms.php" class="hover:text-farm-green transition-colors">Terms & Conditions</a>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>

<?php 
session_start();
include("navbar.php");

// Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

// Identify if user is a farmer
$is_farmer = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'farmer');

// Get search inputs
$search_name = isset($_GET['crop_name']) ? $_GET['crop_name'] : '';
$search_type = isset($_GET['crop_type']) ? $_GET['crop_type'] : '';





$username = $_SESSION['user_name'];
$user_result = mysqli_query($conn, "SELECT u_id FROM user WHERE u_name='$username'");
$user_data = mysqli_fetch_assoc($user_result);
$u_id = $user_data['u_id'];

$f_result = mysqli_query($conn, "SELECT f_id FROM farmer WHERE u_id='$u_id'");
$f_data = mysqli_fetch_assoc($f_result);
$f_id = $f_data ? $f_data['f_id'] : null;





// Build query with user name joined
$sql = "SELECT ml.list_id, ml.f_id, ml.price, ml.l_quantity, ml.l_date, ml.l_st,
               ml.crop_name, ml.crop_type, u.u_name
        FROM market_listing ml
        LEFT JOIN farmer f ON ml.f_id = f.f_id
        LEFT JOIN user u ON f.u_id = u.u_id
        WHERE 1";







if (!empty($search_name)) {
    $sql .= " AND crop_name LIKE '%" . mysqli_real_escape_string($conn, $search_name) . "%'";
}
if (!empty($search_type)) {
    $sql .= " AND LOWER(crop_type) = '" . strtolower(mysqli_real_escape_string($conn, $search_type)) . "'";
}

$sql .= " ORDER BY f_id ASC";

// Fetch results
$result = mysqli_query($conn, $sql);
$listings = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch distinct crop types
$type_result = mysqli_query($conn, "SELECT DISTINCT LOWER(crop_type) AS crop_type FROM market_listing");
$types = [];
while ($row = mysqli_fetch_assoc($type_result)) {
    $types[] = ucfirst($row['crop_type']);
}
$types = array_unique($types);

mysqli_free_result($result);
mysqli_free_result($type_result);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Market Listing</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .headline {
      color: green;
      font-size: 30px;
      font-weight: 800;
      text-align: center;
      user-select: none;
      font-family: 'Inter', sans-serif;
      letter-spacing: 1px;
    }
    .search-bar-wrapper {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }
    .search-form {
      display: flex;
      gap: 10px;
      background-color: #f0fdf4;
      padding: 12px 20px;
      border-radius: 8px;
      border: 1px solid #bbf7d0;
      box-shadow: 0 2px 6px rgba(0, 128, 0, 0.1);
    }
    .search-form input,
    .search-form select,
    .search-form button {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .action-buttons {
      text-align: right;
      margin-bottom: 20px;
    }
    .action-buttons a {
      padding: 10px 16px;
      background-color: grey;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      margin-left: 10px;
    }
    .action-buttons a:first-child {
      background-color: #f97316;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    }
    thead {
      background-color: #166534;
      color: white;
    }
    th, td {
      padding: 12px 14px;
      border-bottom: 1px solid #c8e6c9;
      text-align: left;
      white-space: nowrap;
      text-overflow: ellipsis;
    }
    tr:hover td {
      background-color: #f0fdf4;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="headline-wrapper">
    <h2 class="headline">🧺 Market Listing</h2>
  </div>

  <!-- Search Bar -->
  <div class="search-bar-wrapper">
    <form method="GET" action="" class="search-form">
      <input type="text" name="crop_name" placeholder="Crop Name" value="<?= htmlspecialchars($search_name) ?>">
      <select name="crop_type">
        <option value="">All Types</option>
        <?php foreach ($types as $type): ?>
          <option value="<?= $type ?>" <?= strtolower($search_type) == strtolower($type) ? 'selected' : '' ?>>
            <?= $type ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">🔍 Search</button>
    </form>
  </div>

  <div class="action-buttons">
    <a href="marketlist.php">🔙 Go Back</a>
    <a href="localprice.php">🛒 View Market Price</a>
    <a href="Exp&Sell.php">🌾 Crop Sale</a>
  </div>

  <!-- Table -->
  <div>
    <table>
      <thead>
        <tr>
          <th>Crop Name</th>
          <th>Crop Type</th>
          <th>Price (৳)</th>
          <th>Quantity (Kg)</th>
          <th>Listed Date</th>
          <?php if ($is_farmer): ?>
            <th>Farmer Name</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (count($listings) > 0): ?>
          <?php foreach ($listings as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['crop_name']) ?></td>
              <td><?= htmlspecialchars($row['crop_type']) ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td><?= htmlspecialchars($row['l_quantity']) ?></td>
              <td><?= htmlspecialchars($row['l_date']) ?></td>

              
             <?php if ($is_farmer): ?>
  <td>
    <?= ($row['f_id'] == $f_id) ? htmlspecialchars($row['u_name']) : '-' ?>
  </td>
<?php endif; ?>

            </tr>
          <?php endforeach; ?>


        <?php else: ?>
          <tr>
            <td colspan="<?= $is_farmer ? 6 : 5 ?>" style="text-align: center; padding: 20px; color: #555;">
              No listings found.
            </td>
          </tr>
        <?php endif; ?>


      </tbody>
    </table>
  </div>
</div>

</body>
</html>


 <script>
    // Simple scroll effect for navbar
    window.addEventListener("scroll", function () {
      const header = document.querySelector("header");
      if (window.scrollY > 100) {
        header.classList.add("shadow-2xl");
      } else {
        header.classList.remove("shadow-2xl");
      }
    });

    // Mobile menu toggle (you can expand this)
    function toggleMobileMenu() {
      // Add mobile menu functionality here
      console.log("Mobile menu toggled");
    }
  </script>
<footer class="bg-gray-900 py-6 mt-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div
        class="flex flex-col md:flex-row justify-between items-center text-white"
      >
        <div class="text-2xl font-bold text-farm-green mb-4 md:mb-0">
          FarmHub
        </div>
        <div class="space-x-6 text-sm opacity-75">
          <a href="about.php" class="hover:text-farm-green transition-colors"
            >About Us</a
          >
          <a href="contact.php" class="hover:text-farm-green transition-colors"
            >Contact</a
          >
          <a
            href="privacy.php"
            class="hover:text-farm-green transition-colors"
            >Privacy Policy</a
          >
          <a
            href="terms.php"
            class="hover:text-farm-green transition-colors"
            >Terms & Conditions</a
          >
        </div>
      </div>
    </div>
  </footer>
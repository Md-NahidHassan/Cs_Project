<?php
session_start();
include("navbar.php");

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

// Get distinct names and types for filter dropdowns
$names_result = mysqli_query($conn, "SELECT DISTINCT name FROM seeds_fertilizer ORDER BY name ASC");
$types_result = mysqli_query($conn, "SELECT DISTINCT type FROM seeds_fertilizer ORDER BY type ASC");

// Get selected filters from GET
$filter_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$filter_type = isset($_GET['type']) ? trim($_GET['type']) : '';

$where_clauses = [];
if ($filter_name !== '' && $filter_name !== 'all') {
    $where_clauses[] = "name = '" . mysqli_real_escape_string($conn, $filter_name) . "'";
}
if ($filter_type !== '' && $filter_type !== 'all') {
    $where_clauses[] = "type = '" . mysqli_real_escape_string($conn, $filter_type) . "'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$query = "SELECT * FROM seeds_fertilizer $where_sql ORDER BY sf_id DESC";
$result = mysqli_query($conn, $query);

// Build query string for back button to preserve filters
$back_query_params = [];
if ($filter_name !== '' && $filter_name !== 'all') {
    $back_query_params['name'] = $filter_name;
}
if ($filter_type !== '' && $filter_type !== 'all') {
    $back_query_params['type'] = $filter_type;
}
$back_query_string = http_build_query($back_query_params);
$back_url = 'dashboard.php' . ($back_query_string ? '?' . $back_query_string : '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Seeds & Fertilizers List</title>
  <link rel="stylesheet" href="your_styles.css" />
  <style>
    body {
      background-color: #f9fafb;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      padding: 24px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
      text-align: center;
    }
    h2 {
      color: #166534;
      margin-bottom: 24px;
    }
    form.filter-form {
      display: inline-flex;
      gap: 12px;
      margin-bottom: 24px;
      align-items: center;
      justify-content: center;
    }
    select, button {
      padding: 8px 14px;
      font-size: 16px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      cursor: pointer;
      background: white;
      transition: background-color 0.2s;
    }
    select:hover, button:hover {
      background-color: #e6f4ea;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0;
    }
    th, td {
      padding: 12px 10px;
      border: 1px solid #d1d5db;
      text-align: center;
    }
    th {
      background-color: #166534;
      color: white;
      font-weight: 600;
    }
    tbody tr:hover {
      background-color: #f0fdf4;
    }
    .no-results {
      text-align: center;
      color: #6b7280;
      padding: 20px 0;
    }
    a.clear-btn {
      margin-left: 10px;
      font-size: 14px;
      color: #4b5563;
      text-decoration: underline;
      cursor: pointer;
    }

    /* Buttons container */
    .btn-container {
      margin-bottom: 20px;
      display: flex;
      justify-content: center;
      gap: 12px;
    }
    .btn {
      padding: 10px 18px;
      border-radius: 6px;
      font-weight: 600;
      box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
      text-decoration: none;
      color: white;
      transition: background-color 0.2s;
      display: inline-block;
      cursor: pointer;
    }
    .btn-back {
      background-color: #f97316; /* Orange */
    }
    .btn-back:hover {
      background-color: #c2410c; /* Darker orange */
    }
    .btn-exp {
      background-color: #16a34a; /* Green */
    }
    .btn-exp:hover {
      background-color: #15803d;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>🌾 Seeds & Fertilizers Available</h2>


    <!-- Filter Form -->
    <form method="GET" class="filter-form" aria-label="Filter seeds and fertilizers">
      <label for="nameFilter" class="sr-only">Filter by Name</label>
      <select id="nameFilter" name="name">
        <option value="all">-- All Names --</option>
        <?php while ($row = mysqli_fetch_assoc($names_result)): ?>
          <option value="<?= htmlspecialchars($row['name']) ?>" <?= ($filter_name === $row['name']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="typeFilter" class="sr-only">Filter by Type</label>
      <select id="typeFilter" name="type">
        <option value="all">-- All Types --</option>
        <?php while ($row = mysqli_fetch_assoc($types_result)): ?>
          <option value="<?= htmlspecialchars($row['type']) ?>" <?= ($filter_type === $row['type']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['type']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Filter</button>
      <a href="sf_list.php" class="clear-btn" title="Clear filters">Clear</a>
    </form>
     <!-- Buttons -->
    <div class="btn-container">
      <a href="marketlist.php" class="btn btn-back" title="View Expenses & Sales">Go Back</a>
      <a href="buy_seeds.php" class="btn btn-exp" title="Buy Seeds">Buy Seeds</a>
    </div>

    <table role="table">
      <thead>
        <tr>
          
          <th scope="col">Name</th>
          <th scope="col">Type</th>
          <th scope="col">Quantity (kg)</th>
          <th scope="col">Price (৳)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="5" class="no-results">No items found.</td></tr>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $row['price'] ?></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>


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

</body>
</html>
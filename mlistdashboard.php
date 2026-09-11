<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (!isset($_SESSION['user_name'])) {
    die("Unauthorized access");
}

$username = $_SESSION['user_name'];

// Step 1: Get u_id from user table
$user_result = mysqli_query($conn, "SELECT u_id FROM user WHERE u_name='$username'");
if (!$user_result || mysqli_num_rows($user_result) == 0) {
    die("User not found");
}
$user_row = mysqli_fetch_assoc($user_result);
$uid = $user_row['u_id'];

// Step 2: Get f_id from farmer table using u_id
$farmer_result = mysqli_query($conn, "SELECT f_id, f_name FROM farmer WHERE u_id='$uid'");
if (!$farmer_result || mysqli_num_rows($farmer_result) == 0) {
    die("Farmer not found");
}
$farmer_row = mysqli_fetch_assoc($farmer_result);
$fid = $farmer_row['f_id'];
$fname = $farmer_row['f_name'];

// Step 3: Fetch market listings for that f_id
$sql = "SELECT * FROM market_listing WHERE f_id = '$fid'";
$result = mysqli_query($conn, $sql);
$listings = mysqli_fetch_all($result, MYSQLI_ASSOC);




// Dashboard stats (from market_listing only)
$totalQuery = "SELECT COUNT(*) AS total FROM market_listing WHERE f_id = $fid";
$totalResult = mysqli_fetch_assoc(mysqli_query($conn, $totalQuery));
$totalAdded = $totalResult['total'] ?? 0;

$lowStockQuery = "SELECT crop_name, crop_type, l_quantity FROM market_listing WHERE f_id = $fid AND l_quantity < 50 AND l_quantity > 0 ORDER BY l_quantity ASC";
$lowStockResult = mysqli_query($conn, $lowStockQuery);

$soldOutQuery = "SELECT crop_name, crop_type FROM market_listing WHERE f_id = $fid AND l_quantity <= 0";
$soldOutResult = mysqli_query($conn, $soldOutQuery);
$soldOutCount = mysqli_num_rows($soldOutResult);



?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>FarmHub - Market Crops</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
  />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "farm-green": "#22c55e",    // bright green
            "farm-dark": "#166534",     // dark green
            "farm-light": "#dcfce7",    // light green background
            "farm-header": "#2e7d32"    // header green (slightly darker)
          },
        },
      },
    };
  </script>
</head>
<body class="bg-white">

<!-- Header (navbar unchanged) -->
<header class="bg-white shadow-lg sticky top-0 z-50">



  <!-- Top Navigation -->
  <div class="bg-farm-dark text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center space-x-4">
          <div class="text-2xl font-bold text-farm-green">
            <i class="fas fa-seedling mr-2"></i>FarmHub
          </div>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-sm">
          <i class="fa-regular fa-map text-farm-green"></i>
          <div>
            <p class="text-xs opacity-75">Deliver to</p>
            <p class="font-semibold">Bangladesh</p>
          </div>
        </div>
        <div class="flex-1 max-w-2xl mx-8">
          <div class="relative flex">
            <select
              class="bg-gray-100 text-gray-800 px-3 py-2 rounded-l-lg border-0 focus:ring-2 focus:ring-farm-green"
            >
              <option>ALL Crops</option>
              <option>Rice</option>
              <option>Wheat</option>
              <option>Vegetables</option>
            </select>
            <input
              type="text"
              placeholder="Search for Crops, Equipment, or Farmers"
              class="flex-1 px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-farm-green"
            />
            <button
              class="bg-farm-green hover:bg-green-600 px-4 py-2 rounded-r-lg transition-colors"
            >
              <i class="fa-solid fa-magnifying-glass text-white"></i>
            </button>
          </div>
        </div>
        <div class="flex items-center space-x-6 text-sm">
          <?php if (isset($_SESSION['user_name'])) : ?>
          <div id="user-greeting">
            <p class="font-semibold">
              Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </p>
            <a
              href="logout.php"
              class="text-farm-green hover:text-green-300 transition-colors"
              >LOG OUT</a
            >
          </div>
          <?php else : ?>
          <div id="sign-in-section">
            <a
              href="signup.php"
              class="font-semibold hover:text-farm-green transition-colors"
              >Hello, Sign In</a
            ><br />
            <a
              href="login.php"
              class="text-farm-green hover:text-green-300 transition-colors"
              >LOG IN</a
            >
          </div>
          <?php endif; ?>

          <a
            href="view_cart.php"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="hidden md:inline">My cart</span>
          </a>
          <a
            href="#"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="hidden md:inline">My orders</span>
          </a>
          <a
            href="userdashboard.php"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-user"></i>
            <span class="hidden md:inline">Profile</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Nav -->
  <nav class="bg-farm-header text-white">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
      <div class="flex items-center justify-between h-12 w-full">
        <div class="flex items-center space-x-2 mr-4">
          <i class="fa-solid fa-bars text-base"></i>
          <span class="font-semibold text-base">
            <a
              href="index.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Home</a
            >
          </span>
        </div>
        <div
          class="hidden md:flex flex-1 justify-end space-x-4 text-sm whitespace-nowrap"
        >
             <a
              href="marketlist.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Market Place</a
            >
            <a
              href="cropman.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Crop Management</a
            >
            <a
              href="rental.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Equipment Rental</a
            >
            <a
              href="take_loan.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Loan Management</a
            >
            <a
              href="weather.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Weather Conditions</a
            >
            <a
              href="growthstage.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Growth Tracking</a
            >
            <a
              href="cropsinbd.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Crops in Bangladesh</a
            >
            <a
              href="Exp&Sell.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Sales</a
            >
            <a
              href="mpman.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Market Prices</a
            >
            <a
              href="govt.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Government Schemes</a
            >
            <a
              href="farmingtip.php"
              class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
              >Tips</a
            >
        </div>
      </div>
    </div>
  </nav>
</header>

<!-- Welcome Bar -->
<div class="bg-farm-light text-farm-dark text-center text-lg font-semibold py-3 rounded-md shadow-md max-w-5xl mx-auto mb-8">
    👋 Welcome,Farmer  <strong><?= htmlspecialchars($fname); ?></strong>!

      <p style="color: #4b5563; font-size: 16px;">
   Farmer Crop Management: Add to the list, Edit & Delete
  </p>
</div>

<!-- Add Crop Listing Button (Centered) -->
<div class="text-center max-w-5xl mx-auto mb-8">
    <a href="addtolist.php" class="bg-farm-green text-white font-bold py-2 px-6 rounded-md shadow hover:bg-green-600 transition inline-block">
        ➕ Add Crop Listing
    </a>
</div>

<!-- Buttons Container (Left aligned, stacked vertically) -->
<div class="max-w-5xl mx-auto px-4 mb-4 text-left">
    <a href="localprice.php" class="bg-farm-green text-white font-bold py-2 px-6 rounded-md shadow hover:bg-gray-700 transition inline-block mb-3">
        📊 View Market_price
    </a><br>
    <a href="mlist.php" class="px-4 py-2 bg-orange-500 text-white rounded-md font-semibold hover:bg-orange-600 transition inline-block">
        🔙 Market_Back
    </a>
</div>

<!-- Centered Headline below buttons -->
<h2 class="text-center text-2xl font-semibold mb-6 text-farm-dark max-w-5xl mx-auto">
    My Crop Listings
</h2>





<!-- Dashboard Summary Cards with Icons -->
<div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <!-- Total Crops -->
    <div class="bg-farm-light border border-farm-green rounded-lg p-4 shadow text-center">
        <i class="fas fa-seedling text-4xl text-farm-dark mb-2"></i>
        <h3 class="text-xl font-bold text-farm-dark">Total Crops Added</h3>
        <p class="text-3xl font-semibold text-farm-dark mt-2"><?= $totalAdded ?></p>
    </div>

    <!-- Low Quantity -->
    <div class="bg-yellow-50 border border-yellow-400 rounded-lg p-4 shadow text-center">
        <i class="fas fa-triangle-exclamation text-4xl text-yellow-600 mb-2"></i>
        <h3 class="text-xl font-bold text-yellow-700">Low Stock (&lt;50kg)</h3>
        <?php if (mysqli_num_rows($lowStockResult) > 0): ?>
            <ul class="text-sm mt-2 text-yellow-800 max-h-32 overflow-y-auto">
                <?php while ($row = mysqli_fetch_assoc($lowStockResult)): ?>
                    <li><?= htmlspecialchars($row['crop_name']) ?> (<?= htmlspecialchars($row['crop_type']) ?>) - <?= $row['l_quantity'] ?>kg</li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-sm mt-2 text-yellow-600">None</p>
        <?php endif; ?>
    </div>

    <!-- Sold Out -->
    <div class="bg-red-50 border border-red-400 rounded-lg p-4 shadow text-center">
        <i class="fas fa-circle-xmark text-4xl text-red-600 mb-2"></i>
        <h3 class="text-xl font-bold text-red-700">Sold Out</h3>
        <p class="text-sm mt-2 text-red-800">
        <?= $soldOutCount ?> Item sold out
    </p>
        <?php if ($soldOutCount > 0): ?>
            <ul class="text-sm mt-2 text-red-800 max-h-32 overflow-y-auto">
                <?php while ($row = mysqli_fetch_assoc($soldOutResult)): ?>
                    <li><?= htmlspecialchars($row['crop_name']) ?> (<?= htmlspecialchars($row['crop_type']) ?>)</li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-sm mt-2 text-red-600">None</p>
        <?php endif; ?>
    </div>
</div>





<!-- Centered Table Container -->
<div class="flex justify-center">
    <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white w-full max-w-5xl">
        <table class="min-w-full divide-y divide-farm-green">
            <thead class="bg-farm-header text-white">
                <tr>
                    <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Crop Name &amp; Type</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Price (৳)</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Quantity (kg)</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Listing Date</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-farm-green">
                <?php if (count($listings) > 0): ?>
                    <?php foreach ($listings as $listing): ?>
                        <tr class="hover:bg-farm-light transition-colors duration-200 cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-farm-dark">
                                <?= htmlspecialchars($listing['crop_name']); ?><br>
                                <span class="text-sm text-gray-600">(<?= htmlspecialchars($listing['crop_type']); ?>)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-farm-dark">
                                <?= htmlspecialchars($listing['price']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-farm-dark">
                                <?= htmlspecialchars($listing['l_quantity']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-farm-dark">
                                <?= htmlspecialchars($listing['l_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-4">
                                <a href="listing_edit.php?action=update&id=<?= $listing['list_id'] ?>" 
                                    class="inline-flex items-center px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700 transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Update
                                </a>
                                <a href="listing_edit.php?action=delete&id=<?= $listing['list_id'] ?>" 
                                    onclick="return confirm('Are you sure you want to delete this listing?')" 
                                    class="inline-flex items-center px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700 transition">
                                    <i class="fa-solid fa-trash mr-1"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500">No crop listings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
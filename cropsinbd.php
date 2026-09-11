<?php 
session_start();
?>
<?php

// Connect to DB
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Get filters from GET, sanitize
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$growsINBD_filter = isset($_GET['growsINBD']) ? (int)$_GET['growsINBD'] : 0;
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

<!-- Page Content -->
<div class="max-w-7xl px-4 sm:px-6 lg:px-8 mt-8 mb-12">
  <h1 class="text-3xl font-extrabold text-farm-header mb-6 select-none">
    <i class="fa-solid fa-seedling text-farm-green mr-2"></i>
    All Crops
  </h1>

  <!-- Go Back Button -->
  <div style="margin: 20px 0; text-align: right;">
    <a href="index.php"
       style="
         padding: 10px 16px;
         background-color: #f97316;
         color: white;
         text-decoration: none;
         border-radius: 6px;
         margin-right: 10px;
         font-weight: 600;
         transition: background-color 0.3s ease;
         display: inline-block;
       "
       onmouseover="this.style.backgroundColor='#fb923c';"
       onmouseout="this.style.backgroundColor='#f97316';"
    >
      🔙 Go Back
    </a>
    <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) == 'admin'): ?>
    <a href="adminbd.php?operation=add" 
       class="go-back" 
       style="
         margin-left: 10px;
         background: linear-gradient(45deg, #80e27e, #66bb6a);
         color: white;
         padding: 8px 16px;
         border-radius: 6px;
         text-decoration: none;
         font-weight: 600;
         display: inline-block;
         transition: background 0.3s ease;
       "
       onmouseover="this.style.background='linear-gradient(45deg, #66bb6a, #80e27e)'"
       onmouseout="this.style.background='linear-gradient(45deg, #80e27e, #66bb6a)'"
    >
      ➕ BD or export crop
    </a>
<?php endif; ?>

</div>
  </div>

  <!-- Dropdown Filter -->
  <form method="GET" class="mb-6">
    <div class="flex justify-center mb-4">
      <select
        name="growsINBD"
        onchange="this.form.submit()"
        class="border border-gray-300 p-2 rounded bg-dcfce7 text-farm-dark focus:outline-none focus:ring-2 focus:ring-farm-green"
      >
        <option value="0" <?= $growsINBD_filter === 0 ? 'selected' : '' ?>>-- All --</option>
        <option value="1" <?= $growsINBD_filter === 1 ? 'selected' : '' ?>>Bangladesh</option>
        <option value="2" <?= $growsINBD_filter === 2 ? 'selected' : '' ?>>Export</option>
        <!-- highlight -->
      </select>
    </div>
  </form>

  <?php
    $query = "SELECT c.c_name, bd.grows_in_which_country, bd.ideal_soil, c.season, c.expected_yield, c.harvest_time, 
              bd.ideal_rainfall, bd.ideal_temp, bd.tips, bd.tipstogrowinbd
              FROM crop AS c
              JOIN crop_bd AS bd ON c.crop_id = bd.crop_id";

    $conditions = [];

    if ($growsINBD_filter === 1 || $growsINBD_filter === 2) {
      $conditions[] = "bd.growsINBD = $growsINBD_filter";
    }

    if (count($conditions) > 0) {
      $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query_run = mysqli_query($conn, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
      while ($row = mysqli_fetch_assoc($query_run)) {
        echo "<div class='bg-dcfce7 border border-farm-green rounded-lg p-6 mb-6 shadow'>
                <h2 class='text-xl font-bold text-farm-header mb-2'>{$row['c_name']}</h2>
                <p><strong>Country:</strong> {$row['grows_in_which_country']}</p>
                <p><strong>Soil:</strong> {$row['ideal_soil']}</p>
                <p><strong>Season:</strong> {$row['season']}</p>
                <p><strong>Yield:</strong> {$row['expected_yield']}</p>
                <p><strong>Harvest Time:</strong> {$row['harvest_time']}</p>
                <p><strong>Rainfall:</strong> {$row['ideal_rainfall']}</p>
                <p><strong>Temperature:</strong> {$row['ideal_temp']}</p>";

        if ($growsINBD_filter != 2) {
          echo "<p><strong>Tips:</strong> {$row['tips']}</p>";
        }

        if ($growsINBD_filter != 1) {
          echo "<p><strong>Tips if not in BD:</strong> {$row['tipstogrowinbd']}</p>";
        }

        echo "</div>";
      }
    } else {
      echo "<div class='text-center text-gray-500 px-6 py-4'>No records found.</div>";
    }
  ?>
</div>

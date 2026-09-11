<?php
session_start();

/*
  ✅ JS injection prevention:
  - Any output to HTML uses htmlspecialchars(..., ENT_QUOTES, 'UTF-8')
  - We DO NOT echo raw user input into HTML/JS

  ✅ SQL injection prevention:
  - Query uses prepared statement (no "$loc" inside SQL)

  ✅ CSRF:
  - Added token generation + validation (same style as signup/login)
  - Added hidden csrf_token field in the form
*/

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

$pcTime = date('Y/m/d');

// ---- CSRF status messages ----
$csrf_error = "";
$csrf_success = "";
$max_time = 60 * 60 * 24; // 24 hours

// ---- Error message ----
$login_error = "";

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time'])
    ) {
        $csrf_error = "CSRF token missing.";
    } else {
        if (($_SESSION['csrf_token_time'] + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
        }
    }

    if ($csrf_error === "") {
        $csrf_success = "CSRF token successful. Proceed to next step.";
    }
}

// ---- Generate new CSRF token for the form ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();


// ---- Weather lookup (only if CSRF OK) ----
if (isset($_POST['weath']) && $csrf_error === "") {
    $loc = trim($_POST['location'] ?? '');

    // Prepared statement (SQL injection prevention)
    $stmt = mysqli_prepare($conn, "SELECT * FROM weather WHERE location = ? LIMIT 1");
    if (!$stmt) {
        $login_error = "Query preparation failed.";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $loc);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['weather_data'] = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            header("Location: weatherinside.php");
            exit();
        } else {
            $login_error = "Location not in range.";
        }
        mysqli_stmt_close($stmt);
    }
}
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
            "farm-green": "#22c55e",
            "farm-dark": "#166534",
            "farm-light": "#dcfce7",
            "farm-header": "#2e7d32"
          },
        },
      },
    };
  </script>
</head>

<body class="bg-white min-h-screen flex flex-col">

<!-- Header (navbar unchanged) -->
<header class="bg-white shadow-lg sticky top-0 z-50">
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
            <select class="bg-gray-100 text-gray-800 px-3 py-2 rounded-l-lg border-0 focus:ring-2 focus:ring-farm-green">
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
            <button class="bg-farm-green hover:bg-green-600 px-4 py-2 rounded-r-lg transition-colors">
              <i class="fa-solid fa-magnifying-glass text-white"></i>
            </button>
          </div>
        </div>

        <div class="flex items-center space-x-6 text-sm">
          <?php if (isset($_SESSION['user_name'])) : ?>
          <div id="user-greeting">
            <p class="font-semibold">
              Hello, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>!
            </p>
            <a href="logout.php" class="text-farm-green hover:text-green-300 transition-colors">LOG OUT</a>
          </div>
          <?php else : ?>
          <div id="sign-in-section">
            <a href="signup.php" class="font-semibold hover:text-farm-green transition-colors">Hello, Sign In</a><br />
            <a href="login.php" class="text-farm-green hover:text-green-300 transition-colors">LOG IN</a>
          </div>
          <?php endif; ?>

          <a href="view_cart.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="hidden md:inline">My cart</span>
          </a>
          <a href="#" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="hidden md:inline">My orders</span>
          </a>
          <a href="userdashboard.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
            <i class="fa-solid fa-user"></i>
            <span class="hidden md:inline">Profile</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <nav class="bg-farm-header text-white">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
      <div class="flex items-center justify-between h-12 w-full">
        <div class="flex items-center space-x-2 mr-4">
          <i class="fa-solid fa-bars text-base"></i>
          <span class="font-semibold text-base">
            <a href="index.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Home</a>
          </span>
        </div>

        <div class="hidden md:flex flex-1 justify-end space-x-4 text-sm whitespace-nowrap">
          <a href="marketlist.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Market Place</a>
          <a href="cropman.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Crop Management</a>
          <a href="rental.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Equipment Rental</a>
          <a href="take_loan.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Loan Management</a>
          <a href="weather.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Weather Conditions</a>
          <a href="growthstage.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Growth Tracking</a>
          <a href="cropsinbd.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Crops in Bangladesh</a>
          <a href="Exp&Sell.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Sales</a>
          <a href="mpman.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Market Prices</a>
          <a href="govt.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Government Schemes</a>
          <a href="farmingtip.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Tips</a>
        </div>
      </div>
    </div>
  </nav>
</header>

<!-- Navbar -->
<nav class="w-full bg-farm-green text-white p-4 shadow-md">
  <div class="container mx-auto text-center text-xl font-bold">
    Real time weather
  </div>
</nav>

<!-- Main Content -->
<div class="flex-grow flex items-center justify-center">
  <div class="weather-container max-w-md w-full p-6 bg-farm-light rounded-lg shadow-2xl transform transition-transform hover:-translate-y-2 hover:shadow-2xl mt-32">
    <h1 class="text-2xl font-semibold text-farm-dark mb-4 text-center">Real-Time Weather</h1>

    <!-- CSRF messages in body -->
    <?php if ($csrf_error !== ""): ?>
      <div class="mb-4 text-center text-red-700 font-semibold bg-red-100 p-2 rounded">
        <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="mb-4 text-center text-green-700 font-semibold bg-green-100 p-2 rounded">
        <?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <div class="weather-form">
      <form method="POST" action="" class="flex space-x-2">
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

        <input
          type="text"
          name="location"
          placeholder="Enter city name"
          required
          class="flex-grow px-4 py-2 rounded-l-md border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green"
        />
        <button
          type="submit"
          name="weath"
          class="bg-farm-green text-white px-5 py-2 rounded-r-md hover:bg-green-700 transition"
        >
          Get Weather
        </button>
      </form>
    </div>

    <?php
    if (!empty($login_error)) {
        echo '<div class="mt-4 text-center text-red-600 font-medium">' .
            htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8') .
            '</div>';
    }
    ?>
  </div>
</div>

</body>
</html>

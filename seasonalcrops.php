<?php 
session_start();
?>

<?php  
// Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');

// Check the connection
if (!$conn) {
    echo 'Connection error: ' . mysqli_connect_error();
}

// Check for selected season from the form
if (isset($_GET['season'])) {//season here is form name
    $selected_season = $_GET['season'];//we are saving thr form cwhich user clicked to selected_season
} else {
    $selected_season = 'All';
}


// Build the query based on the selected season
if ($selected_season === 'All') {
    $sql = 'SELECT c_name, season, expected_yield, harvest_time FROM crop ORDER BY c_name';
} else {
    $sql = "SELECT c_name, season, expected_yield, harvest_time FROM crop WHERE season = '" . mysqli_real_escape_string($conn, $selected_season) . "' ORDER BY c_name";
}

// Execute the query
$result = mysqli_query($conn, $sql);

// Fetch results
$crops = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Free memory and close connection
mysqli_free_result($result);
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmHub - Farming Management System</title>
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
          },
        },
      },
    };
  </script>
</head>
<style>
  .bg-farm-header {
    background-color: #166534; /* Dark green */
  }
  .border-farm-green {
    border-color: #86efac; /* Light green */
  }
  .text-farm-dark {
    color: #065f46; /* Darker green */
  }
  .divide-farm-green > :not([hidden]) ~ :not([hidden]) {
    border-color: #bbf7d0; /* Line color for table divide */
  }
</style>

<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-white shadow-lg sticky top-0 z-50">
    <!-- Top Navigation -->
    <div class="bg-farm-dark text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center space-x-4">
            <div class="text-2xl font-bold text-farm-green">
              <i class="fas fa-seedling mr-2"></i>FarmHub
            </div>
          </div>

          <!-- Delivery Location -->
          <div class="hidden md:flex items-center space-x-2 text-sm">
            <i class="fa-regular fa-map text-farm-green"></i>
            <div>
              <p class="text-xs opacity-75">Deliver to</p>
              <p class="font-semibold">Bangladesh</p>
            </div>
          </div>

          <!-- Search Bar -->
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

          <!-- User Section -->
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
    <nav class="bg-farm-green text-white">
      <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
        <div class="flex items-center justify-between h-12 w-full">
          <!-- Left: Menu icon + Home -->
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

          <!-- Right: Nav links -->
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
  <div>
  <div class="container">
  <h1 style="color: #166534; font-size: 2rem; margin-bottom: 1rem;">🌿 All Crops</h1>

  <!-- Season Filter Form -->
  <form method="GET" action="" style="
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 16px;
    border-radius: 8px;
    width: fit-content;
    box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1);
    margin-bottom: 24px;
  ">
    <label for="seasonSelect" style="font-weight: 600; color: #166534; margin-right: 10px;">Filter by Season:</label>
    <select 
      name="season" 
      id="seasonSelect" 
      onchange="this.form.submit()" 
      style="
        padding: 8px 12px;
        border: 1px solid #86efac;
        border-radius: 6px;
        background-color: #dcfce7;
        color: #065f46;
        font-weight: 500;
        transition: background-color 0.3s ease;
      "
      onmouseover="this.style.backgroundColor='#bbf7d0';"
      onmouseout="this.style.backgroundColor='#dcfce7';"
    >
      <option value="All" <?php if ($selected_season === 'All') echo 'selected'; ?>>All</option>
      <option value="Summer" <?php if ($selected_season === 'Summer') echo 'selected'; ?>>Summer</option>
      <option value="Winter" <?php if ($selected_season === 'Winter') echo 'selected'; ?>>Winter</option>
      <option value="Monsoon" <?php if ($selected_season === 'Monsoon') echo 'selected'; ?>>Monsoon</option>
      <option value="Autumn" <?php if ($selected_season === 'Autumn') echo 'selected'; ?>>Autumn</option>
      <option value="Spring" <?php if ($selected_season === 'Spring') echo 'selected'; ?>>Spring</option>
      <option value="Pre-Winter" <?php if ($selected_season === 'Pre-Winter') echo 'selected'; ?>>Pre-Winter</option>
      <option value="Annual" <?php if ($selected_season === 'Annual') echo 'selected'; ?>>Annual</option>
    </select>
  </form>

  <div style="margin: 20px 0; text-align: right;">
    <a href="cropman.php"
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
  </div>

  <!-- Styled Crop Table -->
  <div style="display: flex; justify-content: center; margin-top: 30px;">
    <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white" style="width: 95%;">
      <table class="min-w-full divide-y divide-farm-green">
        <thead class="bg-farm-header text-white">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Crop Name</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Season</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Expected Yield (tons)</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Harvest Time</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-farm-green">
          <?php if (count($crops) > 0): ?>
            <?php foreach ($crops as $crop): ?>
              <tr class="hover:bg-farm-light transition-colors duration-200 cursor-pointer">
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?php echo htmlspecialchars($crop['c_name']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop['season']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop['expected_yield']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop['harvest_time']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="px-6 py-4 text-center text-farm-dark">No crops found for the selected season.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

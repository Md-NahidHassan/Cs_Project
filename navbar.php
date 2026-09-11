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
              href="myOrders.php"
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
</body>
</html>
<?php  
session_start();
require_once 'session_timeout.php';

if (empty($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    echo 'Connection error: ' . mysqli_connect_error();
    exit;
}

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));//sql saftety
    $sql = "SELECT c_name, season, expected_yield, harvest_time, c_desp 
            FROM crop 
            WHERE c_name LIKE '%$search%'
            ORDER BY c_name";//for seach
} else {
    $sql = "SELECT c_name, season, expected_yield, harvest_time, c_desp 
            FROM crop 
            ORDER BY c_name";//no search
}

$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "Error fetching crops: " . mysqli_error($conn);
    $crops = [];
} else {
    $crops = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
}
mysqli_close($conn);
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-12">
  <h1 class="text-3xl font-extrabold text-farm-header mb-6 select-none">
    <i class="fa-solid fa-seedling text-farm-green mr-2"></i>

    All Crops
  </h1>
  <form method="GET" action="">
  <div class="relative flex max-w-2xl mx-8">
    <input 
      type="text" 
      name="search"
      placeholder="Search for Crops, Equipment, or Farmers"
      value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
      class="flex-1 px-4 py-2 text-gray-800 bg-farm-light rounded-l-lg border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green"
    >
    <button type="submit" class="bg-farm-green hover:bg-green-600 px-4 py-2 rounded-r-lg transition-colors">
      <i class="fa-solid fa-magnifying-glass text-white"></i>
    </button>
  </div>
</form>


  <!-- Action Buttons -->
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


  <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) == 'admin'): ?>
    <a href="admincrop.php?operation=add" 
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
      ➕ Insert a crop
    </a>
<?php endif; ?>

</div>

  


  <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white">
    <table class="min-w-full divide-y divide-farm-green">
      <thead class="bg-farm-header text-white">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Crop Name</th>
         
          <th scope="col" class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Expected Yield (kg)</th>
          <th scope="col" class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Harvest Time</th>
          <th scope="col" class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Description</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-farm-green">
        <?php foreach ($crops as $crop): ?>
        <tr class="hover:bg-farm-light transition-colors duration-200 cursor-pointer">
          <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?php echo htmlspecialchars($crop['c_name']); ?></td>
         
          <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop['expected_yield']); ?></td>
          <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop['harvest_time']); ?></td>
          <td class="px-6 py-4 whitespace-nowrap text-farm-dark max-w-xl truncate" title="<?php echo htmlspecialchars($crop['c_desp']); ?>">
            <?php echo htmlspecialchars($crop['c_desp']); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
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

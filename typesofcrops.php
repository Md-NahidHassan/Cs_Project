<?php 
session_start();

// Step 1: Connect to the database
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'farmsystem';

$connection = mysqli_connect($hostname, $username, $password, $database);

// Step 2: Check connection
if (!$connection) {
    echo 'Connection error: ' . mysqli_connect_error();
}

// Step 3: Get selected crop name
$selected_crop = isset($_GET['crop_filter']) ? mysqli_real_escape_string($connection, $_GET['crop_filter']) : '';

// Step 4: Get crop names that exist in crop_type table for dropdown names
$crop_names_query = "
    SELECT DISTINCT c.c_name 
    FROM crop AS c 
    JOIN crop_type AS t ON c.crop_id = t.crop_id 
    ORDER BY c.c_name
";
$crop_names_result = mysqli_query($connection, $crop_names_query);
$crop_names_array = mysqli_fetch_all($crop_names_result, MYSQLI_ASSOC);

// Step 5: Base query that only includes crops with crop_type entries for shwoing main query
$query = "
    SELECT c.c_name, c.season, c.expected_yield, c.harvest_time, c.c_desp,
           t.type_name, t.species, t.price, t.grain_color, t.grain_size,
           t.disease, t.strach_content, t.protein_content
    FROM crop AS c 
    JOIN crop_type AS t ON c.crop_id = t.crop_id
";

// Step 6: Add crop filter if selected
if (!empty($selected_crop)) {
    $query .= " WHERE c.c_name = '" . $selected_crop . "'";
}

// Step 7: Order results
$query .= " ORDER BY c.c_name";

// Step 8: Execute query
$result = mysqli_query($connection, $query);
$crops_type_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Step 9: Free result and close connection
mysqli_free_result($result);
mysqli_close($connection);
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

<!-- Crop Type Filter Form -->
<<!-- Filter by Crop Name (only crops in crop_type table) -->
<form method="GET" action="" style="
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 16px;
  border-radius: 8px;
  width: fit-content;
  box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1);
  margin-bottom: 24px;
">
  <label for="crop_filter" style="font-weight: 600; color: #166534; margin-right: 10px;">
    Filter by Crop Name:
  </label>
  <select 
    name="crop_filter" 
    id="crop_filter" 
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
    <option value="">-- All Crops --</option>
    <?php 
    foreach ($crop_names_array as $crop_name_row) {
        $crop_name_value = htmlspecialchars($crop_name_row['c_name']);
        $is_selected = ($selected_crop == $crop_name_value) ? 'selected' : '';
        echo "<option value='$crop_name_value' $is_selected>$crop_name_value</option>";
    }
    ?>
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

  <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) == 'admin'): ?>
    <a href="admincroptype.php?operation=add" 
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
      ➕ Insert a crop types
    </a>
<?php endif; ?>

</div>

<!-- Centered Table Container with Top Margin -->
<div style="display: flex; justify-content: center; margin-top: 30px;">
  <div class="rounded-lg shadow-md border border-farm-green bg-white" style="width: 100%;">
    <table class="min-w-full table-auto divide-y divide-farm-green">
      <thead class="bg-farm-header text-white">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Crop Name</th>
          
          
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Description</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Type Name</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Grain Size</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Grain Color</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Disease</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Starch Content</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Protein Content</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-farm-green">
        <?php foreach ($crops_type_array as $crop_row): ?>
          <tr class="hover:bg-farm-light transition-colors duration-200 cursor-pointer">
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?php echo htmlspecialchars($crop_row['c_name']); ?></td>
            
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark max-w-xl truncate" title="<?php echo htmlspecialchars($crop_row['c_desp']); ?>">
              <?php echo htmlspecialchars($crop_row['c_desp']); ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['type_name']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['grain_size']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['grain_color']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['disease']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['strach_content']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?php echo htmlspecialchars($crop_row['protein_content']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
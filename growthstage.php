<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmHub - All Crops</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
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
  <style>
    .bg-farm-header {
      background-color: #166534;
    }
    .border-farm-green {
      border-color: #86efac;
    }
    .text-farm-dark {
      color: #065f46;
    }
    .divide-farm-green > :not([hidden]) ~ :not([hidden]) {
      border-color: #bbf7d0;
    }
  </style>
</head>
<body class="bg-gray-50">

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
    <h1 style="color: #166534; font-size: 2rem; margin-bottom: 1rem;">🌿 My Crops Growth </h1>
  <!-- Content -->
  <main class="max-w-6xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
    <h1 class="text-2xl font-bold text-farm-dark mb-6">🌿 All Crops</h1>
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
</div>


    <?php
    // DB connection
    $conn = mysqli_connect('localhost', 'naba', '12345', 'farmsystem');
    if (!$conn) die("Connection failed: " . mysqli_connect_error());

    if (!isset($_SESSION['user_name'])) die("Unauthorized access");

    $username = $_SESSION['user_name'];

    $user_result = mysqli_query($conn, "SELECT * FROM user WHERE u_name='$username'");
    if (!$user_result || mysqli_num_rows($user_result) === 0) die("User not found.");
    $user = mysqli_fetch_assoc($user_result);
    $u_id = $user['u_id'];

    $f_result = mysqli_query($conn, "SELECT f_id FROM farmer WHERE u_id='$u_id'");
    if (!$f_result || mysqli_num_rows($f_result) === 0) die("Farmer not found.");
    $f_id = mysqli_fetch_assoc($f_result)['f_id'];

    $query = "
        SELECT 
            c.crop_id, 
            c.c_name AS crop_name, 
            ct.type_name AS crop_type,
            fc.planted_at AS planted_time, 
            fc.harvested_time AS harvest_time
        FROM farmer_crop AS fc
        JOIN crop AS c ON c.crop_id = fc.crop_id
        JOIN crop_type AS ct ON ct.type_id = fc.type_id
        WHERE fc.f_id = '$f_id'
    ";

    $result = mysqli_query($conn, $query);
    if (!$result) die("Query failed: " . mysqli_error($conn));

    if (mysqli_num_rows($result) === 0) {
        echo "<p>No crops found.</p>";
    } else {
        echo '<div class="overflow-x-auto">';
        echo '<table class="w-full table-auto border border-farm-green">';
        echo '<thead class="bg-farm-light text-farm-dark">';
        echo '<tr>
                <th class="p-3 border">Crop Name</th>
                <th class="p-3 border">Crop Type</th>
                <th class="p-3 border">Planted At</th>
                <th class="p-3 border">Harvest Time</th>
                <th class="p-3 border">Growth Stage</th>
                <th class="p-3 border">Growth Rate</th>
                <th class="p-3 border">Tips / What to Do</th>
                <th class="p-3 border">Fertilizer / Medicine</th>
              </tr>';
        echo '</thead><tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $crop_name = $row['crop_name'];
            $crop_type = $row['crop_type'];
            $planted_time = strtotime($row['planted_time']);
            $harvest_time = strtotime($row['harvest_time']);
            $now = time();

            $days_since_planted = round(($now - $planted_time) / 86400);
            $days_until_ripe = max(0, round(($harvest_time - $now) / 86400));

            // Growth logic
            if ($days_since_planted < 7) {
                $stage = 'Germination(planed)';
                $rate = 'Slow';
                $tip = 'Ensure regular watering';
                $fertilizer = 'Use Nitrogen-rich fertilizer';
            } elseif ($days_since_planted < 20) {
                $stage = 'vegetation(growing)';
                $rate = 'Moderate';
                $tip = 'Monitor for pests';
                $fertilizer = 'Add compost';
            } elseif ($days_since_planted < 30) {
                $stage = 'Flowering';
                $rate = 'Fast';
                $tip = 'Support stems, reduce excess water';
                $fertilizer = 'Use phosphate-rich fertilizer';
            } elseif ($days_since_planted < 40) {
                $stage = 'Fruiting';
                $rate = 'High';
                $tip = 'Start harvesting prep';
                $fertilizer = 'Minimal, potassium recommended';
            } else {
                $stage = 'Harvest Ready';
                $rate = 'Completed';
                $tip = 'Harvest immediately';
                $fertilizer = 'None';
            }

            echo "<tr class='text-center border-b hover:bg-farm-light transition'>";
            echo "<td class='p-2 border'>$crop_name</td>";
            echo "<td class='p-2 border'>$crop_type</td>";
            echo "<td class='p-2 border'>" . date("Y-m-d", $planted_time) . "</td>";
            echo "<td class='p-2 border'>" . date("Y-m-d", $harvest_time) . "</td>";
            echo "<td class='p-2 border'>$stage</td>";
            echo "<td class='p-2 border'>$rate</td>";
            echo "<td class='p-2 border'>$tip</td>";
            echo "<td class='p-2 border'>$fertilizer</td>";
            echo "</tr>";
        }

        echo '</tbody></table></div>';
    }

    mysqli_close($conn);
    ?>
  </main>
</body>
</html>

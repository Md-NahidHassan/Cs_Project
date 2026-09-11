<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));

if (!isset($_SESSION['user_name'])) die("Unauthorized");

// ---------------- CSRF SETUP ----------------
$csrf_error = "";
$max_time = 60 * 60 * 24; // 24 hours

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
}

// Always generate a fresh token for the form
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
// ---------------- END CSRF ----------------


// ----------- Get fc_id safely -----------
if (!isset($_GET['fc_id'])) die("No crop ID provided.");
$fc_id = (int)$_GET['fc_id'];

// ----------- Fetch existing crop data (Prepared Statement) -----------
$fetch_sql = "SELECT fc.*, c.c_name, c.harvest_time AS c_harvest_time
              FROM farmer_crop fc
              JOIN crop c ON fc.crop_id = c.crop_id
              WHERE fc.fc_id = ? LIMIT 1";
$fetch_stmt = mysqli_prepare($conn, $fetch_sql);
if (!$fetch_stmt) die("Prepare failed (fetch).");

mysqli_stmt_bind_param($fetch_stmt, "i", $fc_id);
mysqli_stmt_execute($fetch_stmt);
$fetch_result = mysqli_stmt_get_result($fetch_stmt);

if (!$fetch_result || mysqli_num_rows($fetch_result) == 0) {
    mysqli_stmt_close($fetch_stmt);
    die("Crop not found.");
}
$data = mysqli_fetch_assoc($fetch_result);
mysqli_stmt_close($fetch_stmt);


// ----------- Handle update (Prepared Statements + CSRF OK) -----------
if (isset($_POST['update']) && $csrf_error === "") {
    $crop_name = trim($_POST['crop_name'] ?? '');
    $planted_at = $_POST['planted_at'] ?? '';
    $harvest_time = $_POST['harvest_time'] ?? '';

    // 1) Get crop_id for crop_name (Prepared)
    $crop_check_sql = "SELECT crop_id FROM crop WHERE c_name = ? LIMIT 1";
    $crop_check_stmt = mysqli_prepare($conn, $crop_check_sql);
    if (!$crop_check_stmt) die("Prepare failed (crop check).");

    mysqli_stmt_bind_param($crop_check_stmt, "s", $crop_name);
    mysqli_stmt_execute($crop_check_stmt);
    $crop_check_result = mysqli_stmt_get_result($crop_check_stmt);

    if (!$crop_check_result || mysqli_num_rows($crop_check_result) == 0) {
        mysqli_stmt_close($crop_check_stmt);
        $csrf_error = "Error: Crop name '" . htmlspecialchars($crop_name, ENT_QUOTES, 'UTF-8') . "' does not exist in the crop table.";
    } else {
        $crop_row = mysqli_fetch_assoc($crop_check_result);
        $new_crop_id = (int)$crop_row['crop_id'];
        mysqli_stmt_close($crop_check_stmt);

        // 2) Get type_id from POST safely (it comes from dropdown)
        $type_id = isset($_POST['type_id']) ? (int)$_POST['type_id'] : 0;

        // Optional safety: confirm type_id belongs to this crop_id (Prepared)
        $type_check_sql = "SELECT type_id FROM crop_type WHERE crop_id = ? AND type_id = ? LIMIT 1";
        $type_check_stmt = mysqli_prepare($conn, $type_check_sql);
        if (!$type_check_stmt) die("Prepare failed (type check).");

        mysqli_stmt_bind_param($type_check_stmt, "ii", $new_crop_id, $type_id);
        mysqli_stmt_execute($type_check_stmt);
        $type_check_result = mysqli_stmt_get_result($type_check_stmt);

        if (!$type_check_result || mysqli_num_rows($type_check_result) == 0) {
            mysqli_stmt_close($type_check_stmt);
            $csrf_error = "Error: Selected type does not match the selected crop. Please choose a valid type.";
        } else {
            mysqli_stmt_close($type_check_stmt);

            // 3) Update farmer_crop (Prepared)
            $update_sql = "UPDATE farmer_crop
                           SET crop_id = ?, crop_name = ?, planted_at = ?, harvested_time = ?, type_id = ?
                           WHERE fc_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            if (!$update_stmt) die("Prepare failed (update).");

            mysqli_stmt_bind_param($update_stmt, "isssii", $new_crop_id, $crop_name, $planted_at, $harvest_time, $type_id, $fc_id);

            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_stmt_close($update_stmt);
                header("Location: dashboard.php");
                exit();
            } else {
                $csrf_error = "Error updating crop: " . htmlspecialchars(mysqli_stmt_error($update_stmt), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($update_stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmHub - Farming Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
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

<body class="bg-gray-50">
  <!-- Header (unchanged) -->
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
              <input type="text" placeholder="Search for Crops, Equipment, or Farmers"
                     class="flex-1 px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-farm-green"/>
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
              <a href="signup.php" class="font-semibold hover:text-farm-green transition-colors">Hello, Sign In</a><br/>
              <a href="login.php" class="text-farm-green hover:text-green-300 transition-colors">LOG IN</a>
            </div>
            <?php endif; ?>

            <a href="view_cart.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
              <i class="fa-solid fa-cart-shopping"></i><span class="hidden md:inline">My cart</span>
            </a>
            <a href="#" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
              <i class="fa-solid fa-bag-shopping"></i><span class="hidden md:inline">My orders</span>
            </a>
            <a href="userdashboard.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
              <i class="fa-solid fa-user"></i><span class="hidden md:inline">Profile</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <nav class="bg-farm-green text-white">
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

  <div class="max-w-xl mx-auto mt-10 bg-white shadow-md rounded-lg p-8 border border-farm-green">
    <h2 class="text-2xl font-semibold text-farm-dark mb-6 text-center">Update Crop</h2>

    <!-- CSRF / Update errors in body -->
    <?php if ($csrf_error !== ""): ?>
      <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
        <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <!-- CSRF token -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <div>
        <label for="crop_name" class="block text-farm-dark font-medium">Crop Name</label>
        <input type="text" id="crop_name" name="crop_name"
               value="<?php echo htmlspecialchars($data['crop_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="planted_at" class="block text-farm-dark font-medium">Planted At</label>
        <input type="date" id="planted_at" name="planted_at"
               value="<?php echo htmlspecialchars($data['planted_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="harvest_time" class="block text-farm-dark font-medium">Harvest Time</label>
        <input type="date" id="harvest_time" name="harvest_time"
               value="<?php echo htmlspecialchars($data['harvested_time'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="type_id" class="block text-farm-dark font-medium">Select Crop Type</label>
        <select name="type_id" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green">
          <option value="">--Select Type--</option>
          <?php
            $type_result = mysqli_query($conn, "SELECT type_id, type_name FROM crop_type ORDER BY type_name");
            while ($type = mysqli_fetch_assoc($type_result)) {
                $tid = (int)$type['type_id'];
                $tname = htmlspecialchars($type['type_name'], ENT_QUOTES, 'UTF-8');
                $selected = ($tid == (int)($data['type_id'] ?? 0)) ? "selected" : "";
                echo "<option value='{$tid}' {$selected}>{$tname}</option>";
            }
          ?>
        </select>
      </div>

      <div class="text-center pt-4">
        <button type="submit" name="update"
                class="bg-farm-green text-white px-6 py-2 rounded-md hover:bg-green-600 transition duration-200 font-semibold">
          Update
        </button>
      </div>
    </form>
  </div>
</body>
</html>

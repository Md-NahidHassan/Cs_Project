<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));

if (!isset($_SESSION['user_name'])) {
    die("Unauthorized access");
}

// ---------------- CSRF SETUP ----------------
$csrf_error = "";
$csrf_success = "";
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

    if ($csrf_error === "") {
        $csrf_success = "CSRF token successful. Proceed to next step.";
    }
}

// Always generate a new CSRF token for the form
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
// ---------------- END CSRF ----------------


// ----------- Read action/id safely -----------
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid ID");
}

// ----------- DELETE (Prepared + CSRF) -----------
// NOTE: Deleting via GET is not ideal, but you asked "do the same thing" so we keep structure.
// To enforce CSRF, we allow delete only if it comes with a valid POST request.
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Delete must be POST.");
    }
    if ($csrf_error !== "") {
        die(htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'));
    }

    $del_sql = "DELETE FROM market_listing WHERE list_id = ?";
    $del_stmt = mysqli_prepare($conn, $del_sql);
    if (!$del_stmt) die("Prepare failed (delete).");

    mysqli_stmt_bind_param($del_stmt, "i", $id);
    mysqli_stmt_execute($del_stmt);
    mysqli_stmt_close($del_stmt);

    header("Location: mlistdashboard.php?msg=Deleted");
    exit;
}

// ----------- UPDATE -----------
// Fetch listing (Prepared)
if ($action === 'update') {
    $fetch_sql = "SELECT * FROM market_listing WHERE list_id = ? LIMIT 1";
    $fetch_stmt = mysqli_prepare($conn, $fetch_sql);
    if (!$fetch_stmt) die("Prepare failed (fetch).");

    mysqli_stmt_bind_param($fetch_stmt, "i", $id);
    mysqli_stmt_execute($fetch_stmt);
    $result = mysqli_stmt_get_result($fetch_stmt);

    if (!$result || mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($fetch_stmt);
        die("Listing not found.");
    }
    $listing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($fetch_stmt);

    // Update form submitted (Prepared + CSRF OK)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === "") {
        $crop_name = trim($_POST['crop_name'] ?? '');
        $crop_type = trim($_POST['crop_type'] ?? '');
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $quantity = isset($_POST['l_quantity']) ? (float)$_POST['l_quantity'] : 0;

        $update_sql = "UPDATE market_listing
                       SET crop_name = ?, crop_type = ?, price = ?, l_quantity = ?
                       WHERE list_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);

        if (!$update_stmt) {
            $csrf_error = "Prepare failed (update): " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        } else {
            mysqli_stmt_bind_param($update_stmt, "ssddi", $crop_name, $crop_type, $price, $quantity, $id);

            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_stmt_close($update_stmt);
                header("Location: mlistdashboard.php?msg=Updated");
                exit;
            } else {
                $csrf_error = "Failed to update: " . htmlspecialchars(mysqli_stmt_error($update_stmt), ENT_QUOTES, 'UTF-8');
                mysqli_stmt_close($update_stmt);
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
    .bg-farm-header { background-color: #166534; }
    .border-farm-green { border-color: #86efac; }
    .text-farm-dark { color: #065f46; }
    .divide-farm-green > :not([hidden]) ~ :not([hidden]) { border-color: #bbf7d0; }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Header -->
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
                class="flex-1 px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-farm-green" />
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
    <h2 class="text-2xl font-semibold text-farm-dark mb-6 text-center">Update Crop Listing</h2>

    <!-- CSRF / Update errors in body -->
    <?php if ($csrf_error !== ""): ?>
      <div class="mb-4 text-red-600 font-semibold bg-red-100 p-2 rounded">
        <?php echo htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="mb-4 text-green-600 font-semibold bg-green-100 p-2 rounded">
        <?php echo htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <!-- CSRF token -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <div>
        <label for="crop_name" class="block text-farm-dark font-medium">Crop Name</label>
        <input type="text" id="crop_name" name="crop_name"
               value="<?php echo htmlspecialchars($listing['crop_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="crop_type" class="block text-farm-dark font-medium">Crop Type</label>
        <input type="text" id="crop_type" name="crop_type"
               value="<?php echo htmlspecialchars($listing['crop_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="price" class="block text-farm-dark font-medium">Price (৳)</label>
        <input type="number" id="price" name="price"
               value="<?php echo htmlspecialchars((string)($listing['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div>
        <label for="l_quantity" class="block text-farm-dark font-medium">Quantity (kg)</label>
        <input type="number" id="l_quantity" name="l_quantity"
               value="<?php echo htmlspecialchars((string)($listing['l_quantity'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
               required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-farm-green focus:border-farm-green"/>
      </div>

      <div class="flex justify-between pt-4">
        <button type="submit"
                class="bg-farm-green text-white px-6 py-2 rounded-md hover:bg-green-600 transition duration-200 font-semibold">
          Update
        </button>
        <a href="mlistdashboard.php"
           class="inline-block px-6 py-2 rounded-md border border-farm-green text-farm-green font-semibold hover:bg-farm-green hover:text-white transition duration-200">
          Back
        </a>
      </div>
    </form>
  </div>
</body>
</html>
<?php
} else {
    die("Invalid action.");
}
?>

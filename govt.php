<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM govt_scheme WHERE 1";
if ($search) {
    $sql .= " AND (scheme_name LIKE '%$search%' OR description LIKE '%$search%')";
}
$result = mysqli_query($conn, $sql);

// For search auto-complete
$scheme_names = [];
$name_query = mysqli_query($conn, "SELECT DISTINCT scheme_name FROM govt_scheme WHERE scheme_name IS NOT NULL AND scheme_name != ''");
while ($row = mysqli_fetch_assoc($name_query)) {
    $scheme_names[] = trim($row['scheme_name']);
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmHub - Farming Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'farm-green': '#22c55e',
                        'farm-dark': '#166534',
                        'farm-light': '#dcfce7'
                    }
                }
            }
        }
    </script>
    <title>Government Schemes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff; /* White background */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff; /* White background for container */
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        h2.headline-box {
            display: inline-block;
            background-color: #e8f5e9; /* Light pastel green */
            color: #00695c; /* Dark green text */
            padding: 12px 30px;
            border-radius: 10px;
            margin: 0 auto 30px auto;
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            box-shadow: 0 2px 10px rgba(46, 125, 50, 0.15);
        }

        .headline-container {
            display: flex;
            justify-content: center;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        input[type="text"], button {
            padding: 10px 16px;
            font-size: 16px;
            border: 1px solid #e8f5e9; /* Border color matching the headline */
            border-radius: 8px;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #66bb6a; /* Slightly darker border color on focus */
        }

        button {
            background-color: #e8f5e9; /* Button color matching headline */
            color: green;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
            border: none;
        }

        button:hover {
            background-color: #66bb6a; /* Darker green on hover */
        }

        .top-bar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 30px; /* Make sure it's visible above form */
    gap: 10px;
}

        .go-back {
            background: linear-gradient(45deg, #80e27e, #66bb6a); /* Gradient similar to your image */
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .go-back:hover {
            background: linear-gradient(45deg, #66bb6a, #4caf50); /* Slightly darker gradient on hover */
            color: white;
        }

        .scheme-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .scheme-box {
            background: #ffffff;
            padding: 16px 18px;
            border: 1px solid #80e27e; /* Border color matching the headline */
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(46, 125, 50, 0.12);
            transition: 0.3s ease;
        }

        .scheme-box:hover {
            box-shadow: 0 6px 16px rgba(46, 125, 50, 0.2); /* Darker shadow effect */
        }

        /* Scheme name box with pastel green color matching the image */
        .scheme-box h3 {
            background-color: #e8f5e9; /* Light pastel green */
            padding: 8px 12px;
            border-radius: 6px;
            color: #00695c; /* Dark green text */
            margin: 0 0 10px 0;
            font-size: 1.1rem;
        }

        .scheme-box p {
            margin: 6px 0;
            font-size: 0.9rem;
            color: #555;
        }

        .view-button {
            margin-top: 12px;
        }

        /* View Source button */
        .view-button a {
            display: inline-block;
            padding: 8px 16px;
            background: #e8f5e9; /* Button color matching headline */
            color: green;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .view-button a:hover {
            background: #66bb6a; /* Darker green on hover */
        }
    </style>
</head>
<body>
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

<div class="container">

    <div class="headline-container">
        <h2 class="headline-box">🌿 Govt Agriculture Schemes – Bangladesh</h2>
    </div>

    <form method="get" action="">
  <div class="relative flex max-w-2xl mx-8">
    <input 
      type="text" 
      name="search" 
      list="scheme_names"
      placeholder="Search by scheme name" 
      value="<?= htmlspecialchars($search) ?>" 
      autocomplete="off"
      class="flex-1 px-4 py-2 text-gray-800 bg-farm-light rounded-l-lg border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green"
    >
    <datalist id="scheme_names">
      <?php foreach ($scheme_names as $name): ?>
        <option value="<?= htmlspecialchars($name) ?>">
      <?php endforeach; ?>
    </datalist>
    <button type="submit" class="bg-farm-green hover:bg-green-600 px-4 py-2 rounded-r-lg transition-colors">
      <i class="fa-solid fa-magnifying-glass text-white"></i>
    </button>
  </div>
</form>


    <div class="top-bar">
        <a href="index.php" class="go-back">🔙 Go Back</a>
        <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) == 'admin'): ?>
            <a href="editGovt.php?operation=add" class="go-back" style="margin-left: 10px;">➕ Add Scheme</a>
        <?php endif; ?>
    </div>

    <div class="scheme-list">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="scheme-box">
                    <h3><?= htmlspecialchars($row['scheme_name']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <p><strong>Start:</strong> <?= htmlspecialchars($row['start_date']) ?></p>
                    <p><strong>End:</strong> <?= $row['end_date'] ? htmlspecialchars($row['end_date']) : "<i>Still in process</i>" ?></p>
                    <div class="view-button">
                        <?php if (!empty($row['source_link'])): ?>
                            <a href="<?= htmlspecialchars($row['source_link']) ?>" target="_blank">🔗 View Source</a>
                        <?php else: ?>
                            <i>No link available</i>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) == 'admin'): ?>
                            <br><br>
                            <a href="editGovt.php?operation=edit&id=<?= $row['scheme_id'] ?>" style="color: #00695c;">✏️ Edit</a> |
                            <a href="editGovt.php?operation=delete&id=<?= $row['scheme_id'] ?>" style="color: red;" onclick="return confirm('Are you sure you want to delete this scheme?')">🗑️ Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No schemes found.</p>
        <?php endif; ?>
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
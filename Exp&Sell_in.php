<?php
// Exp&Sell_in.php - Introductory page for sales with category selection
session_start();
// Include session_user.php if you need user session details on this landing page,
// otherwise, it's not strictly necessary here.
// require 'session_user.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmHub - Sales Categories</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
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
        /* Custom CSS variables for consistent theming */
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;
            --bg-farm-header: #166534;
            --text-farm-dark: #065f46;
            --border-farm-green: #86efac;
        }

        .bg-farm-dark { background-color: var(--farm-dark); }
        .text-farm-green { color: var(--farm-green); }
        .bg-farm-green { background-color: var(--farm-green); }
        .hover\:bg-green-700:hover { background-color: #1a9e4e; }
        .hover\:text-farm-light:hover { color: var(--farm-light); }
        .bg-farm-light { background-color: var(--farm-light); }
        .text-farm-dark { color: var(--text-farm-dark); }
        .border-farm-green { border-color: var(--border-farm-green); }
        .bg-farm-header { background-color: var(--bg-farm-header); }

        /* General body styling */
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Main content container styling */
        .main-content {
            flex-grow: 1; /* Allows content to take up available space */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            text-align: center;
        }

        .main-content h1 {
            font-size: 3rem; /* Larger heading */
            font-weight: bold;
            color: var(--farm-dark);
            margin-bottom: 3rem;
        }

        /* Button container for alignment */
        .button-container {
            display: flex;
            flex-direction: column; /* Stack buttons vertically on small screens */
            gap: 2rem; /* Space between buttons */
            max-width: 600px; /* Limit width for larger screens */
            width: 100%;
            margin: 0 auto;
        }

        /* Styling for the category buttons */
        .category-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 2.5rem; /* Generous padding */
            background-color: var(--farm-green);
            color: white;
            font-size: 1.5rem; /* Large font size */
            font-weight: 700; /* Bold text */
            text-decoration: none;
            border-radius: 12px; /* More rounded corners */
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); /* Prominent shadow */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            position: relative; /* For subtle animation */
            overflow: hidden; /* For pseudo-element effects */
        }

        .category-button:hover {
            background-color: #1a9e4e; /* Darker green on hover */
            transform: translateY(-5px); /* Lift effect */
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        .category-button i {
            margin-right: 1rem; /* Space between icon and text */
            font-size: 2rem; /* Larger icon size */
        }

        /* Responsive adjustments for buttons */
        @media (min-width: 640px) {
            .button-container {
                flex-direction: row; /* Display buttons side-by-side on larger screens */
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section (consistent with other FarmHub pages) -->
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

                    <!-- Delivery Location (hidden on smaller screens) -->
                    <div class="hidden md:flex items-center space-x-2 text-sm">
                        <i class="fa-regular fa-map text-farm-green"></i>
                        <div>
                            <p class="text-xs opacity-75">Deliver to</p>
                            <p class="font-semibold">Bangladesh</p>
                        </div>
                    </div>

                    <!-- Search Bar (flex-1 to take available space) -->
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
                    <div class="flex items-center space-x-4 text-sm">
                        <?php if (isset($_SESSION['user_name'])) : ?>
                        <div id="user-greeting" class="flex flex-col items-end whitespace-nowrap">
                            <p class="font-semibold">
                                Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                            </p>
                            <a
                                href="logout.php"
                                class="text-farm-green hover:text-green-300 transition-colors text-xs"
                                >LOG OUT</a
                            >
                        </div>
                        <?php else : ?>
                        <div id="sign-in-section" class="flex flex-col items-end whitespace-nowrap">
                            <a
                                href="signup.php"
                                class="font-semibold hover:text-farm-green transition-colors text-xs"
                                >Hello, Sign In</a
                            ><br />
                            <a
                                href="login.php"
                                class="text-farm-green hover:text-green-300 transition-colors text-xs"
                                >LOG IN</a
                            >
                        </div>
                        <?php endif; ?>

                        <a
                            href="view_cart.php"
                            class="flex flex-col items-center space-y-1 hover:text-farm-green transition-colors whitespace-nowrap"
                        >
                            <i class="fa-solid fa-cart-shopping text-base"></i>
                            <span class="text-xs">My crop cart</span>
                        </a>
                        <a
                            href="view_seeds_cart.php"
                            class="flex flex-col items-center space-y-1 hover:text-farm-green transition-colors whitespace-nowrap"
                        >
                            <i class="fas fa-seedling text-base"></i>
                            <span class="text-xs">My seed cart</span>
                        </a>
                        <a
                            href="myOrders.php"
                            class="flex flex-col items-center space-y-1 hover:text-farm-green transition-colors whitespace-nowrap"
                        >
                            <i class="fa-solid fa-bag-shopping text-base"></i>
                            <span class="text-xs">My orders</span>
                        </a>
                        <a
                            href="userdashboard.php"
                            class="flex flex-col items-center space-y-1 hover:text-farm-green transition-colors whitespace-nowrap"
                        >
                            <i class="fa-solid fa-user text-base"></i>
                            <span class="text-xs">Profile</span>
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

    <main class="main-content">
        <h1>🌱 Farm Produce Marketplace</h1>

        <div class="button-container">
            <a href="Exp&Sell.php" class="category-button">
                <i class="fa-solid fa-carrot"></i> Buy Crops
            </a>
            <a href="buy_seeds.php" class="category-button">
                <i class="fas fa-seedling"></i> Seeds and Fertilizer
            </a>
        </div>
    </main>

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

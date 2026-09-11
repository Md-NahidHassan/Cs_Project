<?php

// 1) Connect & start session
$conn = mysqli_connect('localhost','root','','farmsystem');
if (!$conn) {
    die('Connection error: '.mysqli_connect_error());
}
session_start();

// Exp&Sell.php
require 'session_user.php'; 
// safely use $conn and $u_id

// Fetch all listings + farmer location
$sql = "
    SELECT 
        m.list_id,
        m.crop_name,
        m.crop_type,
        m.l_quantity AS available_qty,
        m.price        AS price_per_kg,
        f.f_name       AS owner_name,
        f.location     AS farmer_location
    FROM market_listing m
    JOIN farmer        f ON m.f_id = f.f_id
    ORDER BY m.crop_name
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmHub - Exp & Sell Crops</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        /* Custom Tailwind Colors from buy_seeds.php for consistency */
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

        /* Container for the table */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: white; /* Light Green */
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1; /* Allows content to take up available space */
            text-align: center; /* Center the heading */
        }

        /* Heading style */
        .container h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--farm-dark);
            margin-bottom: 2rem;
        }

        /* Table specific styles */
        table {
            width: 100%;
            border-collapse: collapse; /* Ensure borders collapse for rounded corners */
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px; /* Apply border-radius to the whole table */
            overflow: hidden; /* Crucial for border-radius to work with collapsed borders */
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }

        thead {
            background-color: var(--farm-dark); /* Apply dark green background to thead */
            color: white;
            /* No need for individual th border-radius when applied to thead with overflow: hidden */
        }

        th, td {
            padding: 15px 20px; /* Consistent padding */
            border-bottom: 1px solid #e0e0e0; /* Lighter border */
            text-align: left;
        }

        th {
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
        }

        /* Specific border-radius for the top corners of the table header */
        table thead tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }
        table thead tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }

        /* Zebra striping and hover effect for table rows */
        tbody tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        tbody tr:hover {
            background-color: #e6ffe6; /* Light green on hover */
            transition: background-color 0.2s ease;
        }

        /* Ensure the last row doesn't have a bottom border inside the rounded table */
        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Input and Select styling within table */
        input[type="number"],
        select,
        input[type="text"] {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-align: center;
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="number"]:focus,
        select:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--farm-green);
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }

        input[type="number"] {
            width: 80px; /* Specific width for quantity */
        }

        input[type="text"][name^="location"] {
            width: 100%; /* Make location input take full width */
        }

        /* Checkbox styling */
        input[type="checkbox"] {
            transform: scale(1.1); /* Slightly larger checkbox */
            margin-right: 0.5rem;
            accent-color: var(--farm-green); /* Green accent for checkbox */
        }
        
        .note {
            font-size: 0.8rem; 
            color: #4d7c0f;
            margin-top: 4px;
            line-height: 1.2;
        }

        /* Action button style */
        .add-to-cart-btn {
            background-color: var(--farm-green);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .add-to-cart-btn:hover {
            background-color: #16a34a; /* Darker green on hover */
            transform: translateY(-2px); /* Slight lift */
            box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        }

        /* Footer Styles (from farmingtip.php) */
        footer {
            background-color: var(--farm-dark); /* Using farm-dark for consistency with the theme */
            color: white; /* Keep text white */
            padding: 2rem 0; /* Slightly more padding for better visual presence */
            text-align: center;
            margin-top: auto; /* Ensures footer sticks to the bottom */
        }

        footer .space-x-6 {
            opacity: 1; /* Make the text fully opaque */
        }

        footer a {
            color: var(--farm-green); /* Links remain farm-green for contrast */
            transition: color 0.2s ease;
        }

        footer a:hover {
            color: var(--farm-light); /* Hover to farm-light for a consistent, subtle effect */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem auto;
            }
            .container h1 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: 100%;
                margin: 10px auto;
                padding: 10px;
            }
            .container h1 {
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
            /* Label the data for mobile view */
            td:nth-of-type(1):before { content: "Select"; }
            td:nth-of-type(2):before { content: "Crop Name"; }
            td:nth-of-type(3):before { content: "Type"; }
            td:nth-of-type(4):before { content: "Available"; }
            td:nth-of-type(5):before { content: "Price/kg"; }
            td:nth-of-type(6):before { content: "Quantity"; }
            td:nth-of-type(7):before { content: "Option"; }
            td:nth-of-type(8):before { content: "Location"; }
            td:nth-of-type(9):before { content: "Delivery Charge"; } /* New label for mobile */


            td:first-of-type {
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            td:last-of-type {
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
            }
            input[type="number"], select, input[type="text"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            input[type="checkbox"] {
                width: auto;
                height: auto;
                margin-right: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section (consistent with other pages) -->
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

                    <!-- User Section - Adjusted for better spacing and visibility -->
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
        </nav>
    </header>

    <div class="container">
        <h1>🛒 Add Crops to Cart</h1>


        <!-- New buttons added here -->
            <div class="flex justify-start gap-4 mb-8">
                <a href="buy_seeds.php" class="inline-block bg-farm-green text-white py-2 px-6 rounded-lg shadow-md hover:bg-farm-dark transition-all duration-300">
                    Go Seeds Sales
                </a>
                <a href="Exp&Sell_in.php" class="inline-block bg-farm-green text-white py-2 px-6 rounded-lg shadow-md hover:bg-farm-dark transition-all duration-300">
                    Go Back
                </a>
            </div>

        <form method="POST" action="add_to_cart.php">
            <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white">
                <table class="min-w-full divide-y divide-farm-green">
                    <thead class="bg-farm-header text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Select</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Crop Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Available (kg)</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Price/kg (৳)</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Option</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Location (IF Delivery)</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold uppercase">Delivery Charge (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-farm-green">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php $id = $row['list_id']; ?>
                                <tr class="hover:bg-farm-light transition-colors duration-200">
                                    <td class="px-4 py-2">
                                        <?php if ($row['available_qty'] > 0): ?>
                                            <input type="checkbox" name="select[]" value="<?=$id?>">
                                        <?php else: ?>
                                            <span style="color: #ccc;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2"><?=htmlspecialchars($row['crop_name'])?></td>
                                    <td class="px-4 py-2"><?=htmlspecialchars($row['crop_type'])?></td>
                                    <td class="px-4 py-2"><?=$row['available_qty']?></td>
                                    <td class="px-4 py-2"><?=$row['price_per_kg']?></td>
                                    <td class="px-4 py-2">
                                        <?php if ($row['available_qty'] > 0): ?>
                                            <input 
                                                type="number" 
                                                name="quantity[<?=$id?>]" 
                                                min="1" 
                                                max="<?=$row['available_qty']?>" 
                                                value="1"
                                                placeholder="max <?=$row['available_qty']?>" 
                                                class="w-24 px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green"
                                                <?php echo ($row['available_qty'] == 0) ? 'disabled' : ''; ?>
                                            />
                                            <div class="note">
                                                Only <?=$row['available_qty']?> kg left.
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#888;">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <select name="delivery_option[<?=$id?>]" 
                                                class="px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green"
                                                <?php echo ($row['available_qty'] == 0) ? 'disabled' : ''; ?>>
                                            <option value="pickup">Pickup 🏠</option>
                                            <option value="delivery">Delivery 🚚</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" name="location[<?=$id?>]" placeholder="Delivery District"
                                               class="px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green"
                                               <?php echo ($row['available_qty'] == 0) ? 'disabled' : ''; ?>>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span id="delivery_charge_display_<?=$id?>">Calculated on Order</span>
                                        <!-- This hidden input can be used to pass a placeholder or a default 0,
                                             but the actual calculation MUST be done in add_to_cart.php -->
                                        <input type="hidden" name="delivery_charge[<?=$id?>]" value="0">
                                    </td>

                                    <!-- Hidden inputs -->
                                    <input type="hidden" name="price_per_kg[<?=$id?>]" value="<?=$row['price_per_kg']?>">
                                    <input type="hidden" name="available_qty_hidden[<?=$id?>]" value="<?=$row['available_qty']?>">
                                    <input type="hidden" name="farmer_location[<?=$id?>]" value="<?=htmlspecialchars($row['farmer_location'])?>">
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-farm-dark">No crops available for sale at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center;">
                <button type="submit" name="bulk_add" class="add-to-cart-btn">
                    ✅ Add Selected to Cart
                </button>
            </div>
        </form>
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
<?php
// Close database connection at the very end of the script
mysqli_close($conn);
?>

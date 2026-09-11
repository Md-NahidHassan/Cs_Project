<?php
// view_seeds_cart.php
session_start();

// --- START: Enhanced Error Reporting (Keep for debugging) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END: Enhanced Error Reporting ---

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

$u_id = $_SESSION['u_id'] ?? null;

// Handle item removal if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    if (!$u_id) {
        // User not logged in, redirect to login
        header('Location: login.php');
        exit();
    }

    $cart_id_to_remove = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($cart_id_to_remove) {
        // Prepare and execute the DELETE statement
        // Added a check for successful statement preparation
        $stmt_delete = mysqli_prepare($conn, "DELETE FROM seeds_cart WHERE cart_id = ? AND u_id = ?");
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "ii", $cart_id_to_remove, $u_id);
            if (!mysqli_stmt_execute($stmt_delete)) {
                // Handle deletion error
                echo "<p style='color:red; text-align:center;'>Error removing item: " . htmlspecialchars(mysqli_stmt_error($stmt_delete)) . "</p>";
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            // Handle statement preparation error
            echo "<p style='color:red; text-align:center;'>Error preparing delete statement: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        }
    }
    // Redirect back to view_seeds_cart.php to refresh the cart display
    header('Location: view_seeds_cart.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmHub - My Seeds & Fertilizers Cart</title>
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
    <style>
        /* Custom CSS based on your existing styles */

        :root {
            --farm-dark: #166534;
        }

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
        /* Specific styles for the login/empty cart messages for consistency */
        .message-box {
            max-width: 500px; /* Adjusted max-width */
            margin: 100px auto 50px auto; /* Margin to clear fixed header */
            padding: 30px; /* Increased padding */
            background-color: #dcfce7; /* Farm light green */
            border: 2px solid #22c55e; /* Farm green border */
            border-radius: 10px; /* More rounded corners */
            font-family: Arial, sans-serif;
            color: #166534; /* Farm dark green text */
            text-align: center;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2); /* More prominent shadow */
        }
        .message-box p {
            font-size: 1.25rem; /* Larger font size */
            margin-bottom: 20px; /* More spacing */
            font-weight: 600;
            line-height: 1.5;
        }
        .message-box a {
            display: inline-block;
            padding: 12px 25px; /* Larger button padding */
            background-color: #22c55e; /* Farm green button */
            color: white;
            text-decoration: none;
            border-radius: 8px; /* More rounded button */
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease; /* Added transform for hover effect */
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); /* Button shadow */
        }
        .message-box a:hover {
            background-color: #16a34a; /* Darker green on hover */
            transform: translateY(-1px); /* Slight lift */
        }

        /* Styles for the cart table container */
        .cart-container {
            max-width: 1000px; /* Increased width for the table container */
            width: 95%; /* Make it responsive and take 95% of screen width */
            margin: 30px auto; /* Top margin to clear header */
            padding: 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .cart-container h1 {
            font-size: 2.5rem; /* Larger heading */
            font-weight: bold;
            color: var(--farm-dark);
            margin-bottom: 30px;
        }
        /* Styles for the table itself */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners apply to table content */
            box-shadow: 0 1px 5px rgba(0,0,0,0.1); /* Slightly more pronounced table shadow */
        }
        th, td {
            padding: 15px 20px; /* Increased padding */
            border-bottom: 1px solid #e0e0e0; /* Lighter border */
            text-align: left;
        }
        th {
            background-color: var(--farm-dark);
            color: white;
            font-weight: bold;
            font-size: 1rem; /* Slightly larger header font */
            text-transform: uppercase;
        }
        th:hover,
        th:focus,
        th:active {
            background-color: var(--farm-dark) !important;
            color: white !important;
            outline: none;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8; /* More subtle zebra striping */
        }
        tr:hover {
            background-color: #e8f5e9; /* Lighter green on hover */
        }
        /* Style for the remove button */
        .remove-btn {
            background-color: #ef4444; /* Red color for remove */
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .remove-btn:hover {
            background-color: #dc2626; /* Darker red on hover */
            transform: translateY(-1px);
        }
        .confirm-order-btn {
            padding: 12px 24px;
            background-color: #22c55e;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .confirm-order-btn:hover {
            background-color: #4ade80;
        }
    </style>
</head>
<body class="bg-gray-50">
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
                            <span class="text-xs">My cart</span>
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

        <nav class="bg-farm-green text-white">
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

<?php
if (!$u_id) {
    echo "
    <div class='message-box'>
        <p>Please log in to view your seeds and fertilizers cart.</p>
        <a href='login.php'>Log In</a>
    </div>";
    exit; // Stop script execution if not logged in
}

// Fetch cart items from 'seeds_cart' table for display
// This needs to be after the POST handling, so it always shows the latest state.
$result_display = mysqli_query($conn, "
    SELECT sc.cart_id, sc.item_name, sc.item_type, sc.quantity, sc.delivery_option, sc.total_price, sc.location
    FROM seeds_cart sc
    WHERE sc.u_id = $u_id
");

if (!$result_display) {
    // Handle query error, though die() at connection makes this less likely for connection issues
    echo "<p style='color:red; text-align:center;'>Error fetching cart items: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
}

if (mysqli_num_rows($result_display) === 0) {
    echo "
    <div class='message-box'>
        <p>🌱 Your seeds and fertilizers cart is empty. Time to grow your collection!</p>
        <a href='buy_seeds.php'>Buy Seeds & Fertilizers</a>
    </div>";
    // Important: Do not exit here if you want the header and footer to show,
    // but the table itself will be empty. If you want to exit and only show the message, uncomment the exit below.
    exit;
}
?>

<div class="cart-container">
    <h1>🛒 Your Seeds & Fertilizers Cart</h1>

    <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white">
        <table class="min-w-full divide-y divide-farm-green">
            <thead class="bg-farm-header text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Item Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Quantity (kg/unit)</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Option</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Location</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Total Price (৳)</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-farm-green">
                <?php while ($row = mysqli_fetch_assoc($result_display)): // Use $result_display here ?>
                    <tr class="hover:bg-farm-light transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?= htmlspecialchars($row['item_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['item_type']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= $row['quantity'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= $row['delivery_option'] === 'delivery' ? '🚚 Delivery' : '🏠 Pickup' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['location']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= number_format($row['total_price'], 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="view_seeds_cart.php" method="post" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                                <button type="submit" name="remove_item" class="remove-btn">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <form action="confirm_seeds_order.php" method="post" style="margin-top: 20px; text-align: center;">
        <button type="submit" name="confirm_order" class="confirm-order-btn">
            ✅ Confirm Order
        </button>
    </form>
</div>
</body>
</html>
<?php
// Close database connection at the very end of the script
mysqli_close($conn);
?>
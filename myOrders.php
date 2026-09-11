<?php
// myOrders.php - This file displays a user's placed orders.
session_start();

// --- START: Enhanced Error Reporting (Keep for debugging) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END: Enhanced Error Reporting ---

// Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('DB Error: Could not connect to database: ' . mysqli_connect_error());
}

$u_id = $_SESSION['u_id'] ?? null;

// START of common HTML head and navbar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmHub - My Orders</title> <!-- Specific title for this page -->
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
        /* Custom CSS based on your existing styles for consistency */
         :root {
            --farm-dark: #166534;
            --farm-green: #22c55e;
        }

        .bg-farm-header { background-color: #166534; }
        .border-farm-green { border-color: #86efac; }
        .text-farm-dark { color: #065f46; }
        .divide-farm-green > :not([hidden]) ~ :not([hidden]) { border-color: #bbf7d0; }

        body {
            min-height: 100vh;
            background-color: #f3f4f6; /* Consistent light gray background */
            padding-bottom: 50px; /* Space for content below table */
        }

        /* Styles for message boxes (Not Logged In, Empty Orders) */
        .message-box {
            max-width: 500px; /* Slightly wider for better text flow */
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

        /* Styles for the main orders table */
        .page-container {
            max-width: 1200px; /* Increased width for the table container */
            width: 95%; /* Make it responsive and take 95% of screen width */
            margin: 30px auto; /* Top margin to clear header */
            padding: 25px;
            background-color: white; /* Light Green */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
 h1 {
    font-size: 3rem;          /* Bigger size */
    font-weight: 800;         /* Extra bold */
    color: var(--farm-dark);  /* Dark green */
    margin-bottom: 30px;
    text-align: center;
    font-family: Arial, sans-serif; /* Fallback clean font */
}





        table {
            width: 100%;
            border-collapse: collapse; /* Ensure borders collapse */
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px; /* Apply border-radius to the whole table */
            overflow: hidden; /* Crucial for border-radius to work with collapsed borders */
            box-shadow: 0 1px 5px rgba(0,0,0,0.1); /* Slightly more pronounced table shadow */
        }
        thead {
            background-color: var(--farm-dark);
            color: white;
            /* Apply border-radius directly to the thead for top corners */
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            overflow: hidden; /* Important for thead's own border-radius to show */
        }
        th, td {
            padding: 15px 20px; /* Increased padding */
            border-bottom: 1px solid #e0e0e0; /* Lighter border */
            text-align: left;
        }
        th {
            font-weight: bold;
            font-size: 1rem; /* Slightly larger header font */
            text-transform: uppercase;
        }

        /* No need for individual th border-radius when applied to thead with overflow: hidden */


        /* Hover effect for table rows - Made more distinct */
        tr:nth-child(even) {
            background-color: #f8f8f8; /* More subtle zebra striping */
        }
        tr:hover {
            background-color: #c6f6d5; /* More visible light green on hover */
            transform: translateY(-1px); /* Slight lift on hover */
            transition: background-color 0.2s ease, transform 0.2s ease; /* Smooth transition */
        }
        /* Ensure the last row doesn't have a bottom border inside the rounded table */
        table tbody tr:last-child td {
            border-bottom: none;
        }


        .back-link-btn {
            display: inline-block;
            background-color: var(--farm-green);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 30px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .back-link-btn:hover {
            background-color: #1a9e4e;
            transform: translateY(-1px);
        }
        /* Specific styles for status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px; /* Full rounded */
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-pending { background-color: #fef9c3; color: #a16207; } /* yellow */
        .status-paid { background-color: #dcfce7; color: #166534; } /* green */
        .status-failed { background-color: #fee2e2; color: #dc2626; } /* red */
        .status-shipped { background-color: #e0f2f7; color: #01579b; } /* light blue */
        .status-delivered { background-color: #dcfce7; color: #166534; } /* green */
        .status-cancelled { background-color: #fef2f2; color: #b91c1c; } /* light red */

        .cod-message {
            background-color: #fffbe6; /* Light yellow */
            border: 1px solid #fcd34d; /* Yellow border */
            color: #92400e; /* Darker yellow/orange text */
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            font-size: 0.95rem;
        }

        /* Responsive table behavior */
        @media (max-width: 768px) {
            .page-container {
                padding: 15px;
            }
            h1 {
                font-size: 2.5rem;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 640px) {
            .page-container {
                width: 100%;
                margin: 20px auto;
                padding: 10px;
            }
            h1 {
                font-size: 1.8rem;
                margin-bottom: 20px;
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
            /* Label the data */
            td:nth-of-type(1):before { content: "Order ID"; }
            td:nth-of-type(2):before { content: "Item Name"; }
            td:nth-of-type(3):before { content: "Item Type"; } /* Added for mobile view */
            td:nth-of-type(4):before { content: "Quantity"; }
            td:nth-of-type(5):before { content: "Delivery Type"; }
            td:nth-of-type(6):before { content: "Delivery Location"; }
            td:nth-of-type(7):before { content: "Total Price"; }
            td:nth-of-type(8):before { content: "Payment Method"; }
            td:nth-of-type(9):before { content: "Payment Status"; }
            td:nth-of-type(10):before { content: "Delivery Status"; }
            td:nth-of-type(11):before { content: "Ordered At"; } /* Adjusted for new column */

            .cod-message td {
                padding-left: 15px; /* Adjust padding for COD message on mobile */
                text-align: center;
            }
            .cod-message td:before {
                content: none; /* Remove label for COD message */
            }
        }

        thead th {
    background-color: var(--farm-dark) !important;
    color: white !important;
    outline: none;
}

/* Handle interaction states specifically */
thead th:hover,
thead th:focus,
thead th:active {
    background-color: var(--farm-dark) !important;
    color: white !important;
    outline: none;
}



    </style>
</head>
<body class="bg-gray-50">
    <!-- Header (Your existing navbar code) -->
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
    <!-- END of common HTML head and navbar -->

<?php
// PHP logic to handle non-logged-in state or empty orders
if (!$u_id) {
    // If not logged in, display a message and exit
    echo "
    <div class='message-box'>
        <p>Please log in to view your orders.</p>
        <a href='login.php'>Log In</a>
    </div>";
    echo "</body></html>"; // Close body and html tags opened by the header
    exit;
}

// Fetch all orders for the logged-in user
$sql = "
    SELECT o.order_id, o.list_id, o.quantity, o.truck, o.total_price, o.ordered_at,
           o.payment_method, o.payment_status, o.transaction_id, o.delivery_status, o.order_type, o.delivery_location
    FROM orders o
    WHERE o.u_id = ?
    ORDER BY o.ordered_at DESC
";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('SQL Prepare Error: ' . mysqli_error($conn) . ' Query: ' . htmlspecialchars($sql));
}

mysqli_stmt_bind_param($stmt, "i", $u_id);

if (!mysqli_stmt_execute($stmt)) {
    die('SQL Execute Error: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

$orders_to_display = [];
if (mysqli_num_rows($result) > 0) {
    while ($order = mysqli_fetch_assoc($result)) {
        $item_name = 'N/A';
        $item_type = 'N/A';

        if ($order['order_type'] === 'crop') {
            // Fetch crop details
            $stmt_crop = mysqli_prepare($conn, "SELECT crop_name FROM market_listing WHERE list_id = ?");
            if ($stmt_crop) {
                mysqli_stmt_bind_param($stmt_crop, "i", $order['list_id']);
                mysqli_stmt_execute($stmt_crop);
                $crop_result = mysqli_stmt_get_result($stmt_crop);
                if ($crop_row = mysqli_fetch_assoc($crop_result)) {
                    $item_name = $crop_row['crop_name'];
                    $item_type = 'Crop';
                }
                mysqli_stmt_close($stmt_crop);
            }
        } elseif ($order['order_type'] === 'seed_fertilizer') {
            // Fetch seed/fertilizer details
            $stmt_sf = mysqli_prepare($conn, "SELECT name, type FROM seeds_fertilizer WHERE sf_id = ?");
            if ($stmt_sf) {
                mysqli_stmt_bind_param($stmt_sf, "i", $order['list_id']); // list_id stores sf_id for these orders
                mysqli_stmt_execute($stmt_sf);
                $sf_result = mysqli_stmt_get_result($stmt_sf);
                if ($sf_row = mysqli_fetch_assoc($sf_result)) {
                    $item_name = $sf_row['name'];
                    $item_type = ucfirst($sf_row['type']); // Capitalize 'seed' or 'fertilizer'
                }
                mysqli_stmt_close($stmt_sf);
            }
        }

        // Add display names to the order array
        $order['display_item_name'] = $item_name;
        $order['display_item_type'] = $item_type;
        $orders_to_display[] = $order;
    }
}
mysqli_stmt_close($stmt);


// Check if there are any orders to display after processing
if (empty($orders_to_display)) {
    echo "
    <div class='message-box'>
        <p>📦 You haven't placed any orders yet. Start shopping! 🎉</p>
        <a href='marketlist.php'>Go to Market Place</a>
    </div>";
    echo "</body></html>"; // Close body and html tags
    exit;
}

// If user is logged in and orders are not empty, display the orders table
?>
    <div class="page-container">
        <h1>📦 Your Orders</h1>

        <table>
            <thead class="bg-farm-header"> <!-- Added bg-farm-header class here -->
                <tr>
                    <th>Order ID</th>
                    <th>Item Name</th>
                    <th>Item Type</th>
                    <th>Quantity</th>
                    <th>Delivery Type</th>
                    <th>Delivery Location</th> <!-- Added Delivery Location -->
                    <th>Total Price (৳)</th>
                    <th>Payment Method</th>
                    <th>Delivery Status</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders_to_display as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['display_item_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['display_item_type']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['truck'] === 'Yes' ? '🚚 Truck Delivery' : '🏠 Pickup'); ?></td>
                        <td><?php echo htmlspecialchars($order['delivery_location'] ?: 'N/A'); ?></td> <!-- Display Delivery Location -->
                        <td>৳<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                        <td>
                            <?php
                                $delivery_status_class = '';
                                switch($order['delivery_status']) {
                                    case 'Pending':    $delivery_status_class = 'status-pending'; break;
                                    case 'Processing': $delivery_status_class = 'status-shipped'; break; // Use shipped style for processing
                                    case 'Shipped':    $delivery_status_class = 'status-shipped'; break;
                                    case 'Delivered':  $delivery_status_class = 'status-delivered'; break;
                                    case 'Cancelled':  $delivery_status_class = 'status-cancelled'; break;
                                    default: $delivery_status_class = 'status-pending'; break; // Default
                                }
                            ?>
                            <span class="status-badge <?= $delivery_status_class ?>">
                                <?= htmlspecialchars($order['delivery_status']) ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($order['ordered_at']); ?></td>
                    </tr>
                    <?php if ($order['payment_method'] === 'Cash on Delivery'): ?>
                        <tr>
                            <td colspan="10" class="cod-message"> <!-- Adjusted colspan for the new column -->
                                <strong>Note:</strong> This is a Cash on Delivery order. Payment will be collected upon delivery.
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="Exp&Sell_in.php" class="back-link-btn">Back Sales</a>
    </div>

</body>
</html>
<?php
// Close database connection at the very end of the script
mysqli_close($conn);
?>

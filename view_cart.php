<?php
// view_cart.php
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

// Function to display messages (replaces alert and provides consistent styling)
function displayMessage($message, $type = 'success', $redirect_url = null) {
    $color = ($type === 'success') ? 'green' : 'red';
    $bgColor = ($type === 'success') ? 'bg-green-100' : 'bg-red-100';
    $textColor = ($type === 'success') ? 'text-green-800' : 'text-red-800';

    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Message</title><script src='https://cdn.tailwindcss.com'></script><style>
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
        .message-box-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .custom-message-box {
            background-color: #dcfce7; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center; max-width: 400px; width: 90%; color: #166534; border: 1px solid;
            border-color: " . (($type === 'success') ? '#22c55e' : '#ef4444') . ";
        }
        .custom-message-box p { margin-bottom: 20px; font-size: 1.1rem; }
        .custom-message-box-button {
            background-color: #22c55e; color: white; padding: 10px 20px; border-radius: 5px;
            border: none; cursor: pointer; font-weight: bold; transition: background-color 0.3s;
            text-decoration: none; display: inline-block;
        }
        .custom-message-box-button:hover { background-color: #1a9e4e; }
    </style></head><body>";
    echo "<div class='message-box-overlay'>";
    echo "<div class='custom-message-box'>";
    echo "<p>" . htmlspecialchars($message) . "</p>";
    if ($redirect_url) {
        echo "<a href='" . htmlspecialchars($redirect_url) . "' class='custom-message-box-button'>OK</a>";
    } else {
        echo "<button onclick='this.closest(\".message-box-overlay\").remove()' class='custom-message-box-button'>OK</button>";
    }
    echo "</div>";
    echo "</div></body></html>";
}

// Handle item removal if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    if (!$u_id) {
        displayMessage('Please log in to remove items from your cart.', 'error', 'login.php');
        exit();
    }

    $cart_id_to_remove = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($cart_id_to_remove) {
        // Prepare and execute the DELETE statement
        $stmt_delete = mysqli_prepare($conn, "DELETE FROM cart WHERE cart_id = ? AND u_id = ?");
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "ii", $cart_id_to_remove, $u_id);
            if (mysqli_stmt_execute($stmt_delete)) {
                // Successfully removed, redirect to view_cart.php to refresh
                header('Location: view_cart.php');
                exit();
            } else {
                displayMessage("Error removing item: " . htmlspecialchars(mysqli_stmt_error($stmt_delete)), 'error');
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            displayMessage("Error preparing delete statement: " . htmlspecialchars(mysqli_error($conn)), 'error');
        }
    } else {
        displayMessage("Invalid item selected for removal.", 'error');
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmHub - My Cart</title>
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


        .bg-farm-header { background-color: #166534; /* Dark green */ }
        .border-farm-green { border-color: #86efac; /* Light green */ }
        .text-farm-dark { color: #065f46; /* Darker green */ }
        .divide-farm-green > :not([hidden]) ~ :not([hidden]) { border-color: #bbf7d0; /* Line color for table divide */ }
        
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Specific styles for the login/empty cart messages for consistency */
        .message-box-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .custom-message-box {
            background-color: #dcfce7;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            color: #166534;
        }

        .custom-message-box p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .custom-message-box-button {
            background-color: var(--farm-green);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none; /* For <a> tags */
            display: inline-block;
        }

        .custom-message-box-button:hover {
            background-color: #1a9e4e;
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
            flex-grow: 1; /* Allows container to take remaining space */
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
            margin-top: 20px; /* Adjusted margin */
        }
        .confirm-order-btn:hover {
            background-color: #4ade80;
        }

        /* Footer Styles (consistent with farmingtip.php) */
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


        /* Responsive adjustments for table */
        @media (max-width: 768px) {
            .cart-container {
                padding: 1rem;
                margin: 1rem auto;
            }
            .cart-container h1 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 640px) {
            .cart-container {
                width: 100%;
                margin: 10px auto;
                padding: 10px;
            }
            .cart-container h1 {
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
            td:nth-of-type(1):before { content: "Crop"; }
            td:nth-of-type(2):before { content: "Quantity"; }
            td:nth-of-type(3):before { content: "Delivery"; }
            td:nth-of-type(4):before { content: "Total Price"; }
            td:nth-of-type(5):before { content: "Action"; }

            td:first-of-type {
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            td:last-of-type {
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
            }
            .remove-btn {
                width: auto;
                margin: 0 auto;
            }
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
            </nav>
        </header>

<?php
if (!$u_id) {
    displayMessage('Please log in to view your cart.', 'error', 'login.php');
    exit;
}

// Fetch cart items using list_id instead of crop_id
$result = mysqli_query($conn, "
    SELECT c.cart_id, m.crop_name, c.quantity, c.truck, c.total_price, c.location
    FROM cart c
    JOIN market_listing m ON c.list_id = m.list_id
    WHERE c.u_id = $u_id
");

if (!$result || mysqli_num_rows($result) === 0) {
    displayMessage('🛒 Your cart is empty, continue shopping in sales. Good deals await! 🎉', 'info', 'Exp&Sell.php');
    exit;
}
?>

<div class="cart-container">
    <h1>🛒 Your Shopping Cart</h1>
    
    <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white">
        <table class="min-w-full divide-y divide-farm-green">
            <thead class="bg-farm-header text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Crop</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Quantity (kg)</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Option</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Location</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Total Price (৳)</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-farm-green">
                <?php 
                $overall_total_price = 0;
                $display_delivery_charge_amount = 350.00; // The fixed amount you want to *display* for delivery
                
                while ($row = mysqli_fetch_assoc($result)): 
                    // Use the total_price ALREADY CALCULATED and stored in the database by add_to_cart.php
                    $item_total = $row['total_price']; 
                    $overall_total_price += $item_total; // Sum up the stored total prices for the overall total
                ?>
                    <tr class="hover:bg-farm-light transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?= htmlspecialchars($row['crop_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= $row['quantity'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark">
                            <?= $row['truck'] === 'Yes' ? '🚚 Truck Delivery' : '🏠 Pickup' ?>
                            <?php 
                            // Only display the delivery charge amount if 'truck' is 'Yes'
                            // The actual charge is already included in $row['total_price'] from DB
                            if ($row['truck'] === 'Yes'): 
                            ?>
                                <br><span class="text-xs text-gray-500">(+৳<?= number_format($display_delivery_charge_amount, 2) ?> Delivery)</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['location'] ?: 'N/A') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= number_format($item_total, 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="view_cart.php" method="post">
                                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                                <button type="submit" name="remove_item" class="remove-btn">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-right font-bold text-farm-dark">Overall Total:</td>
                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-bold"><?= number_format($overall_total_price, 2) ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <form action="confirm_cart.php" method="post">
            <button type="submit" name="confirm_order" class="confirm-order-btn">
                ✅ Confirm Order
            </button>
        </form>
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
<?php
// Close database connection at the very end of the script
mysqli_close($conn);
?>
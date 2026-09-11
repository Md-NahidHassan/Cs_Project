<?php
// buy_seeds.php - A new file for users to buy seeds and fertilizers

session_start();


// Database connection details
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

$u_id = $_SESSION['u_id'] ?? null; // Get user ID from session

// Handle adding selected seeds/fertilizers to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_seeds_cart'])) {
    if (!$u_id) {
        // User not logged in, redirect to login
        header('Location: login.php');
        exit();
    }

    $selected_items = $_POST['selected_sf'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $delivery_options = $_POST['delivery_option'] ?? [];
    $locations = $_POST['location'] ?? [];

    if (empty($selected_items)) {
        echo "<p class='message-box' style='color:red;'>Please select at least one item to add to cart.</p>";
    } else {
        $insert_success = true;
        foreach ($selected_items as $sf_id) {
            $sf_id = filter_var($sf_id, FILTER_SANITIZE_NUMBER_INT);
            $quantity = filter_var($quantities[$sf_id] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $delivery_option = filter_var($delivery_options[$sf_id] ?? 'pickup', FILTER_SANITIZE_STRING);
            $location = filter_var($locations[$sf_id] ?? '', FILTER_SANITIZE_STRING);

            // Client-side validation is implemented, but a server-side check is good practice too
            if ($delivery_option === 'delivery' && empty(trim($location))) {
                // This scenario should be caught by client-side JS, but as a fallback
                echo "<p class='message-box' style='color:red;'>Delivery Address is required for delivery option.</p>";
                $insert_success = false;
                continue; // Skip to next item if validation fails
            }


            if ($sf_id > 0 && $quantity > 0) {
                // Fetch item details from seeds_fertilizer table
                $stmt_fetch = mysqli_prepare($conn, "SELECT name, type, price FROM seeds_fertilizer WHERE sf_id = ?");
                if ($stmt_fetch) {
                    mysqli_stmt_bind_param($stmt_fetch, "i", $sf_id);
                    mysqli_stmt_execute($stmt_fetch);
                    $result_fetch = mysqli_stmt_get_result($stmt_fetch);
                    $item_details = mysqli_fetch_assoc($result_fetch);
                    mysqli_stmt_close($stmt_fetch);

                    if ($item_details) {
                        $item_name = $item_details['name'];
                        $item_type = $item_details['type'];
                        $price_per_unit = $item_details['price'];
                        $total_price = $quantity * $price_per_unit;

                        // Insert into seeds_cart table
                        $stmt_insert = mysqli_prepare($conn, "INSERT INTO seeds_cart (u_id, sf_id, item_name, item_type, quantity, total_price, delivery_option, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt_insert) {
                            mysqli_stmt_bind_param($stmt_insert, "iissddss", $u_id, $sf_id, $item_name, $item_type, $quantity, $total_price, $delivery_option, $location);
                            if (!mysqli_stmt_execute($stmt_insert)) {
                                $insert_success = false;
                                echo "<p class='message-box' style='color:red;'>Error adding " . htmlspecialchars($item_name) . " to cart: " . htmlspecialchars(mysqli_stmt_error($stmt_insert)) . "</p>";
                            }
                            mysqli_stmt_close($stmt_insert);
                        } else {
                            $insert_success = false;
                            echo "<p class='message-box' style='color:red;'>Error preparing insert statement for " . htmlspecialchars($item_name) . ": " . htmlspecialchars(mysqli_error($conn)) . "</p>";
                        }
                    } else {
                        $insert_success = false;
                        echo "<p class='message-box' style='color:red;'>Item with ID " . htmlspecialchars($sf_id) . " not found.</p>";
                    }
                } else {
                    $insert_success = false;
                    echo "<p class='message-box' style='color:red;'>Error preparing fetch statement for item: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
                }
            }
        }
        if ($insert_success) {
            header('Location: view_seeds_cart.php');
            exit();
        }
    }
}

include("navbar.php");

// Fetch all seeds and fertilizers from the 'seeds_fertilizer' table
$sql = "
    SELECT
        sf_id,
        name,
        type,
        quantity,
        price
    FROM seeds_fertilizer
    ORDER BY name ASC
";
$result = mysqli_query($conn, $sql);

// Check for query errors
if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buy Seeds & Fertilizers</title>
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

        // Function to show custom message box (replaces alert)
        function showMessageBox(message) {
            const overlay = document.createElement('div');
            overlay.className = 'message-box-overlay';

            const msgBox = document.createElement('div');
            msgBox.className = 'custom-message-box';

            const msgText = document.createElement('p');
            msgText.textContent = message;

            const closeButton = document.createElement('button');
            closeButton.textContent = 'OK';
            closeButton.onclick = () => document.body.removeChild(overlay);

            msgBox.appendChild(msgText);
            msgBox.appendChild(closeButton);
            overlay.appendChild(msgBox);
            document.body.appendChild(overlay);
        }

        // Toggles quantity, delivery option, and location inputs based on checkbox
        function toggleRowInputs(checkbox) { // Removed sfId as it's not needed directly here
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('input[name^="quantity["]');
            const deliveryOptionSelect = row.querySelector('select[name^="delivery_option["]');
            const locationInput = row.querySelector('input[name^="location["]');

            if (checkbox.checked) {
                quantityInput.disabled = false;
                deliveryOptionSelect.disabled = false;
                // Only enable location and make required if delivery is selected
                if (deliveryOptionSelect.value === 'delivery') {
                    locationInput.disabled = false;
                    locationInput.required = true;
                }
            } else {
                quantityInput.disabled = true;
                deliveryOptionSelect.disabled = true;
                locationInput.disabled = true;
                locationInput.value = ''; // Clear location when unchecked
                locationInput.required = false; // Not required if unchecked
            }
        }

        // Toggles delivery location input based on delivery option selection
        function toggleDeliveryLocation(selectElement) {
            const row = selectElement.closest('tr');
            const locationInput = row.querySelector('input[name^="location["]');
            const checkbox = row.querySelector('input[type="checkbox"][name^="selected_sf"]');

            if (checkbox.checked) { // Only update location input if the item's checkbox is checked
                if (selectElement.value === 'delivery') {
                    locationInput.disabled = false;
                    locationInput.required = true;
                } else {
                    locationInput.disabled = true;
                    locationInput.value = '';
                    locationInput.required = false;
                }
            } else {
                locationInput.disabled = true;
                locationInput.value = '';
                locationInput.required = false;
            }
        }


        // Main form validation function
        function validateForm() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="selected_sf"]');
            let atLeastOneSelected = false;

            for (const checkbox of checkboxes) {
                if (checkbox.checked) {
                    atLeastOneSelected = true;
                    const row = checkbox.closest('tr');
                    const deliveryOptionSelect = row.querySelector('select[name^="delivery_option["]');
                    const locationInput = row.querySelector('input[name^="location["]');
                    const quantityInput = row.querySelector('input[name^="quantity["]');

                    // Check if delivery option is selected and location is empty
                    if (deliveryOptionSelect.value === 'delivery' && locationInput.value.trim() === '') {
                        showMessageBox('Please enter a Delivery Address for selected items with Delivery option.');
                        locationInput.focus();
                        return false;
                    }

                    // Check if quantity is valid
                    if (quantityInput.disabled) {
                         // This case should ideally not be hit if UI logic is perfect, but kept as a safeguard
                         showMessageBox('Quantity input for a selected item is disabled. Please ensure the checkbox is correctly handled.');
                         return false;
                    }

                    const currentQuantity = parseInt(quantityInput.value);
                    const maxQuantity = parseInt(quantityInput.max);

                    if (isNaN(currentQuantity) || currentQuantity <= 0 || currentQuantity > maxQuantity) {
                        showMessageBox(`Please enter a valid quantity for ${row.querySelector('td:nth-child(2)').textContent}. Quantity must be between 1 and ${maxQuantity}.`);
                        quantityInput.focus();
                        return false;
                    }
                }
            }

            if (!atLeastOneSelected) {
                showMessageBox('Please select at least one item to add to your cart.');
                return false;
            }

            return true;
        }

        // Initialize state and attach listeners on page load
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="selected_sf"]');
            checkboxes.forEach(checkbox => {
                // Initial setup for inputs based on checkbox state
                toggleRowInputs(checkbox);

                // Attach change listener to checkbox
                checkbox.addEventListener('change', function() {
                    toggleRowInputs(this);
                });
            });

            // Attach change listener to each delivery option select
            document.querySelectorAll('select[name^="delivery_option["]').forEach(select => {
                select.addEventListener('change', function() {
                    toggleDeliveryLocation(this);
                });
            });
        });
    </script>
    <style>
        /* Custom CSS variables for consistent theming */
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;
        }

        /* Generic styles from view_cart for consistency */
        .bg-farm-header {
            background-color: var(--farm-dark);
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

        .custom-message-box button {
            background-color: var(--farm-green);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .custom-message-box button:hover {
            background-color: #1a9e4e;
        }


        .message-box {
            max-width: 500px;
            margin: 100px auto 50px auto;
            padding: 30px;
            background-color: var(--farm-light);
            border: 2px solid var(--farm-green);
            border-radius: 10px;
            font-family: Arial, sans-serif;
            color: var(--farm-dark);
            text-align: center;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }
        .message-box p {
            font-size: 1.25rem;
            margin-bottom: 20px;
            font-weight: 600;
            line-height: 1.5;
        }
        .message-box a {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--farm-green);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .message-box a:hover {
            background-color: #16a34a;
            transform: translateY(-1px);
        }

        .table-container {
    max-width: 1200px; /* Wider container for more columns */
    width: 95%;
    margin: 30px auto;
    padding: 0; /* Changed to 0 to remove padding */
    background-color: #ffffff; /* Changed to white */
    border-radius: 10px; /* Can keep or remove this */
    box-shadow: none; /* Changed to none to remove shadow */
    text-align: center;
}
        .table-container h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--farm-dark);
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse; /* Changed to collapse to fix border-radius issue */
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px; /* Applied here for the outer container of the table */
            overflow: hidden; /* Important for rounding corners of the table */
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }
      th, td {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    vertical-align: middle; /* Add this line */
}
        th {
            background-color: var(--farm-dark);
            color: white;
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
        }
        /* Specific border-radius for the top corners of the header cells - Adjusted */
        table thead tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }
        table thead tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }
        /* Remove border-radius from individual cells if table-collapse is used */
        table thead th:not(:first-child) {
            border-top-left-radius: 0;
        }
        table thead th:not(:last-child) {
            border-top-right-radius: 0;
        }


        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        tr:hover {
            background-color: #e6ffe6; /* Lighter green hover effect, more visible */
            transition: background-color 0.2s ease; /* Smooth transition for hover */
        }

        .add-to-cart-btn {
            background-color: var(--farm-green);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .add-to-cart-btn:hover {
            background-color: #16a34a;
            transform: translateY(-2px);
        }

        /* Footer Styles */
        footer {
            background-color: #166534; /* Using farm-dark for consistency with the theme */
            color: white; /* Keep text white */
            padding: 2rem 0; /* Slightly more padding for better visual presence */
            text-align: center;
            margin-top: auto; /* Ensures footer sticks to the bottom */
        }

        footer .space-x-6 {
            /* Target the div containing opacity-75 */
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
            .table-container {
                padding: 15px;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            .message-box {
                margin-top: 80px;
                padding: 20px;
            }
            .message-box p {
                font-size: 1rem;
            }
            .message-box a {
                padding: 10px 20px;
            }
        }
        @media (max-width: 640px) {
            .table-container {
                width: 100%;
                margin: 20px auto;
                padding: 10px;
            }
            .table-container h1 {
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
            td:nth-of-type(1):before { content: "Select"; }
            td:nth-of-type(2):before { content: "Name"; }
            td:nth-of-type(3):before { content: "Type"; }
            td:nth-of-type(4):before { content: "Available"; }
            td:nth-of-type(5):before { content: "Price"; }
            td:nth-of-type(6):before { content: "Quantity"; }
            td:nth-of-type(7):before { content: "Delivery"; }
            td:nth-of-type(8):before { content: "Location"; }

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





    <?php
    if (!$u_id) {
        echo "
        <div class='message-box'>
            <p>Please log in to add seeds and fertilizers to your cart.</p>
            <a href='signup.php'>Log In</a>
        </div>";
        // Do not exit here so that the list of seeds/fertilizers can still be viewed
    }
    ?>

    <div class="table-container">
        <h1>🌱 Buy Seeds & Fertilizers</h1>

 <!-- New buttons added here -->
            <div class="flex justify-start gap-4 mb-8">
                <a href="Exp&Sell.php" class="inline-block bg-farm-green text-white py-2 px-6 rounded-lg shadow-md hover:bg-farm-dark transition-all duration-300">
                    Go Crop Sales
                </a>
                <a href="Exp&Sell_in.php" class="inline-block bg-farm-green text-white py-2 px-6 rounded-lg shadow-md hover:bg-farm-dark transition-all duration-300">
                    Go Back
                </a>
            </div>


        <form action="buy_seeds.php" method="post" onsubmit="return validateForm()">
            <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white">
                <table class="min-w-full divide-y divide-farm-green">
                    <thead class="bg-farm-header text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Select</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Available Quantity (kg/unit)</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Price per Unit (৳)</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Option</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Location</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-farm-green">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-farm-light transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark">
                                        <input type="checkbox" name="selected_sf[]" value="<?= htmlspecialchars($row['sf_id']) ?>" class="h-4 w-4 text-farm-green border-gray-300 rounded focus:ring-farm-green">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark capitalize"><?= htmlspecialchars($row['type']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= $row['quantity'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= number_format($row['price'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" name="quantity[<?= htmlspecialchars($row['sf_id']) ?>]" min="1" max="<?= htmlspecialchars($row['quantity']) ?>" value="1" class="w-24 px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green" disabled>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="delivery_option[<?= htmlspecialchars($row['sf_id']) ?>]" class="px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green" disabled>
                                            <option value="pickup">Pickup 🏠</option>
                                            <option value="delivery">Delivery 🚚</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="location[<?= htmlspecialchars($row['sf_id']) ?>]" placeholder="Delivery Address" class="px-2 py-1 border border-gray-300 rounded-md focus:ring-farm-green focus:border-farm-green" disabled>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-farm-dark">No seeds or fertilizers available at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="mt-8 text-center">
                    <button type="submit" name="add_to_seeds_cart" class="add-to-cart-btn">
                        ➕ Add Selected Seeds/Fertilizer to Cart
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

   
  <script>
    // Simple scroll effect for navbar
    window.addEventListener("scroll", function () {
      const header = document.querySelector("header");
      if (window.scrollY > 100) {
        header.classList.add("shadow-2xl");
      } else {
        header.classList.remove("shadow-2xl");
      }
    });

    // Mobile menu toggle (you can expand this)
    function toggleMobileMenu() {
      // Add mobile menu functionality here
      console.log("Mobile menu toggled");
    }
  </script>
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

    <script>
        // Function to show custom message box (replaces alert)
        function showMessageBox(message) {
            const overlay = document.createElement('div');
            overlay.className = 'message-box-overlay';

            const msgBox = document.createElement('div');
            msgBox.className = 'custom-message-box';

            const msgText = document.createElement('p');
            msgText.textContent = message;

            const closeButton = document.createElement('button');
            closeButton.textContent = 'OK';
            closeButton.onclick = () => document.body.removeChild(overlay);

            msgBox.appendChild(msgText);
            msgBox.appendChild(closeButton);
            overlay.appendChild(msgBox);
            document.body.appendChild(overlay);
        }

        // Toggles quantity, delivery option, and location inputs based on checkbox
        function toggleRowInputs(checkbox) { // Removed sfId as it's not needed directly here
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('input[name^="quantity["]');
            const deliveryOptionSelect = row.querySelector('select[name^="delivery_option["]');
            const locationInput = row.querySelector('input[name^="location["]');

            if (checkbox.checked) {
                quantityInput.disabled = false;
                deliveryOptionSelect.disabled = false;
                // Only enable location and make required if delivery is selected
                if (deliveryOptionSelect.value === 'delivery') {
                    locationInput.disabled = false;
                    locationInput.required = true;
                }
            } else {
                quantityInput.disabled = true;
                deliveryOptionSelect.disabled = true;
                locationInput.disabled = true;
                locationInput.value = ''; // Clear location when unchecked
                locationInput.required = false; // Not required if unchecked
            }
        }

        // Toggles delivery location input based on delivery option selection
        function toggleDeliveryLocation(selectElement) {
            const row = selectElement.closest('tr');
            const locationInput = row.querySelector('input[name^="location["]');
            const checkbox = row.querySelector('input[type="checkbox"][name^="selected_sf"]');

            if (checkbox.checked) { // Only update location input if the item's checkbox is checked
                if (selectElement.value === 'delivery') {
                    locationInput.disabled = false;
                    locationInput.required = true;
                } else {
                    locationInput.disabled = true;
                    locationInput.value = '';
                    locationInput.required = false;
                }
            } else {
                locationInput.disabled = true;
                locationInput.value = '';
                locationInput.required = false;
            }
        }


        // Main form validation function
        function validateForm() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="selected_sf"]');
            let atLeastOneSelected = false;

            for (const checkbox of checkboxes) {
                if (checkbox.checked) {
                    atLeastOneSelected = true;
                    const row = checkbox.closest('tr');
                    const deliveryOptionSelect = row.querySelector('select[name^="delivery_option["]');
                    const locationInput = row.querySelector('input[name^="location["]');
                    const quantityInput = row.querySelector('input[name^="quantity["]');

                    // Check if delivery option is selected and location is empty
                    if (deliveryOptionSelect.value === 'delivery' && locationInput.value.trim() === '') {
                        showMessageBox('Please enter a Delivery Address for selected items with Delivery option.');
                        locationInput.focus();
                        return false;
                    }

                    // Check if quantity is valid
                    if (quantityInput.disabled) {
                         // This case should ideally not be hit if UI logic is perfect, but kept as a safeguard
                         showMessageBox('Quantity input for a selected item is disabled. Please ensure the checkbox is correctly handled.');
                         return false;
                    }

                    const currentQuantity = parseInt(quantityInput.value);
                    const maxQuantity = parseInt(quantityInput.max);

                    if (isNaN(currentQuantity) || currentQuantity <= 0 || currentQuantity > maxQuantity) {
                        showMessageBox(`Please enter a valid quantity for ${row.querySelector('td:nth-child(2)').textContent}. Quantity must be between 1 and ${maxQuantity}.`);
                        quantityInput.focus();
                        return false;
                    }
                }
            }

            if (!atLeastOneSelected) {
                showMessageBox('Please select at least one item to add to your cart.');
                return false;
            }

            return true;
        }

        // Initialize state and attach listeners on page load
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="selected_sf"]');
            checkboxes.forEach(checkbox => {
                // Initial setup for inputs based on checkbox state
                toggleRowInputs(checkbox);

                // Attach change listener to checkbox
                checkbox.addEventListener('change', function() {
                    toggleRowInputs(this);
                });
            });

            // Attach change listener to each delivery option select
            document.querySelectorAll('select[name^="delivery_option["]').forEach(select => {
                select.addEventListener('change', function() {
                    toggleDeliveryLocation(this);
                });
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>

<?php
// add_to_cart.php - Handles adding selected crops to the user's cart

session_start();

// --- START: Enhanced Error Reporting (Keep for debugging during development) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END: Enhanced Error Reporting ---

// Database connection details
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

$u_id = $_SESSION['u_id'] ?? null; // Get user ID from session

// Function to display messages (replaces alert and provides consistent styling)
function displayMessage($message, $type = 'success', $redirect_url = null) {
    // Determine colors based on message type
    $bgColor = ($type === 'success') ? 'bg-green-100' : 'bg-red-100';
    $textColor = ($type === 'success') ? 'text-green-800' : 'text-red-800';
    $borderColor = ($type === 'success') ? 'border-green-500' : 'border-red-500';

    // HTML for the message box - inline basic styles for quick display
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Message</title><script src='https://cdn.tailwindcss.com'></script><style>
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; }
        .message-box-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .custom-message-box {
            padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center; max-width: 400px; width: 90%;
            border: 1px solid; /* Added border for consistency */
        }
        .custom-message-box p { margin-bottom: 20px; font-size: 1.1rem; }
        .custom-message-box-button {
            background-color: #22c55e; color: white; padding: 10px 20px; border-radius: 5px;
            border: none; cursor: pointer; font-weight: bold; transition: background-color 0.3s;
            text-decoration: none; display: inline-block; /* Ensure it behaves like a button */
        }
        .custom-message-box-button:hover { background-color: #1a9e4e; }
    </style></head><body>";
    echo "<div class='message-box-overlay'>";
    echo "<div class='custom-message-box {$bgColor} {$textColor} {$borderColor}'>";
    echo "<p>" . htmlspecialchars($message) . "</p>";
    if ($redirect_url) {
        echo "<a href='" . htmlspecialchars($redirect_url) . "' class='custom-message-box-button'>OK</a>";
    } else {
        echo "<button onclick='this.closest(\".message-box-overlay\").remove()' class='custom-message-box-button'>OK</button>";
    }
    echo "</div>";
    echo "</div></body></html>";
}


// Redirect to login if user not logged in
if (!$u_id) {
    displayMessage('Please log in to add items to your cart.', 'error', 'login.php');
    exit();
}

// Check if the form was submitted and items were selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_add']) && isset($_POST['select']) && is_array($_POST['select'])) {
    $selected_list_ids = $_POST['select'];
    $quantities = $_POST['quantity'] ?? [];
    $delivery_options = $_POST['delivery_option'] ?? [];
    $locations = $_POST['location'] ?? [];
    $available_qty_hiddens = $_POST['available_qty_hidden'] ?? [];

    $items_added_count = 0;
    $errors = [];

    // Prepare insert statement for cart
    $cartStmt = mysqli_prepare($conn, 
        "INSERT INTO cart (u_id, list_id, quantity, truck, total_price, location) 
         VALUES (?, ?, ?, ?, ?, ?)");
    if (!$cartStmt) {
        displayMessage("Error preparing cart insert statement: " . htmlspecialchars(mysqli_error($conn)), "error");
        exit();
    }

    // Prepare update statement for market_listing quantity
    $updateStmt = mysqli_prepare($conn, 
        "UPDATE market_listing
         SET l_quantity = l_quantity - ?
         WHERE list_id = ?");
    if (!$updateStmt) {
        displayMessage("Error preparing stock update statement: " . htmlspecialchars(mysqli_error($conn)), "error");
        mysqli_stmt_close($cartStmt); 
        exit();
    }

    foreach ($selected_list_ids as $list_id_raw) {
        $list_id = (int) $list_id_raw;

        if (!isset($quantities[$list_id]) || !isset($delivery_options[$list_id]) || 
            !isset($available_qty_hiddens[$list_id])) {
            $errors[] = "Missing form data for item ID: " . htmlspecialchars($list_id);
            continue;
        }

        $quantity = (int) $quantities[$list_id];
        $delivery_option = trim($delivery_options[$list_id]);
        $location = trim($locations[$list_id] ?? '');
        $available_qty = (int) $available_qty_hiddens[$list_id];

        // --- FETCH PRICE FROM DATABASE (SECURE) ---
        $price_query = mysqli_prepare($conn, "SELECT price FROM market_listing WHERE list_id = ?");
        mysqli_stmt_bind_param($price_query, "i", $list_id);
        mysqli_stmt_execute($price_query);
        $price_result = mysqli_stmt_get_result($price_query);
        $price_data = mysqli_fetch_assoc($price_result);
        mysqli_stmt_close($price_query);

        if (!$price_data) {
            $errors[] = "Could not find price data for item ID: " . htmlspecialchars($list_id);
            continue;
        }
        $price_per_kg = (float) $price_data['price'];
        // --- END FETCH PRICE ---


        // Basic validation
        if ($list_id <= 0 || $quantity < 1 || $quantity > $available_qty || $price_per_kg <= 0) {
            $errors[] = "Invalid input for item ID: " . htmlspecialchars($list_id) . 
                         ". Quantity: {$quantity} (Available: {$available_qty}), Price: {$price_per_kg}.";
            continue;
        }

        $delivery_charge = 0;
        $truck_delivery = 'No'; // Default to No (Pickup)

        if ($delivery_option === 'delivery') {
            $truck_delivery = 'Yes';
            if (empty($location)) {
                $errors[] = "Delivery district is required for item ID: " . htmlspecialchars($list_id) . " when delivery option is selected.";
                continue;
            }
            // --- SIMPLIFIED DELIVERY CHARGE LOGIC ---
            $delivery_charge = 350; // Always 350 if delivery is chosen
            // --- END SIMPLIFIED LOGIC ---
        }

        $total_price = ($quantity * $price_per_kg) + $delivery_charge;

        // --- Transaction (Optional but Recommended) ---
        // mysqli_begin_transaction($conn);

        // 1. Insert into cart
        mysqli_stmt_bind_param($cartStmt, "iiisds", 
                                 $u_id, $list_id, $quantity, $truck_delivery, $total_price, $location);
        if (!mysqli_stmt_execute($cartStmt)) {
            $errors[] = "Database error adding item ID " . htmlspecialchars($list_id) . " to cart: " . htmlspecialchars(mysqli_stmt_error($cartStmt));
            // mysqli_rollback($conn);
            continue;
        }

        // 2. Update stock in market_listing
        mysqli_stmt_bind_param($updateStmt, "ii", $quantity, $list_id);
        if (!mysqli_stmt_execute($updateStmt)) {
            $errors[] = "Database error updating stock for item ID " . htmlspecialchars($list_id) . ": " . htmlspecialchars(mysqli_stmt_error($updateStmt));
            // mysqli_rollback($conn);
            continue;
        }
        
        // mysqli_commit($conn);
        $items_added_count++;
    }

    // Close prepared statements
    mysqli_stmt_close($cartStmt);
    mysqli_stmt_close($updateStmt);

    if ($items_added_count > 0) {
        header('Location: view_cart.php');
        exit();
    } else {
        displayMessage("No items were added to your cart due to errors. Please review the selections and try again.", "error", "Exp&Sell.php");
        foreach ($errors as $error) {
            displayMessage($error, "error");
        }
    }
} else {
    displayMessage('Please select items to add to your cart from the sales page.', 'info', 'Exp&Sell.php');
    exit();
}

mysqli_close($conn);
?>
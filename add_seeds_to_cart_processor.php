<?php
// add_seeds_to_cart_processor.php - Processes the form submission from buy_seeds.php

session_start();

// Include session_user.php if you need the user ID for database-backed cart
// require 'session_user.php'; 

// Database connection (only if you plan to update database stock here or log cart items)
// Uses 'localhost', 'root', '', 'farmsystem' as provided by you
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

if (isset($_POST['bulk_add_seeds']) && isset($_POST['select']) && is_array($_POST['select'])) {
    // Initialize cart in session if it doesn't exist
    if (!isset($_SESSION['cart_seeds'])) {
        $_SESSION['cart_seeds'] = [];
    }

    $items_added_count = 0;

    foreach ($_POST['select'] as $sf_id) {
        // Sanitize the sf_id
        $sf_id = mysqli_real_escape_string($conn, $sf_id);

        // Retrieve other form data for this sf_id
        $quantity_to_buy = isset($_POST['quantity'][$sf_id]) ? (int)$_POST['quantity'][$sf_id] : 0;
        $delivery_option = isset($_POST['delivery_option'][$sf_id]) ? mysqli_real_escape_string($conn, $_POST['delivery_option'][$sf_id]) : 'pickup';
        $location_input = isset($_POST['location'][$sf_id]) ? mysqli_real_escape_string($conn, $_POST['location'][$sf_id]) : '';
        
        // Retrieve hidden inputs for item details (from your seeds_fertilizer table)
        $price_per_unit = isset($_POST['price_per_unit'][$sf_id]) ? (float)$_POST['price_per_unit'][$sf_id] : 0;
        $available_qty_hidden = isset($_POST['available_qty_hidden'][$sf_id]) ? (int)$_POST['available_qty_hidden'][$sf_id] : 0;
        $item_name = isset($_POST['item_name'][$sf_id]) ? mysqli_real_escape_string($conn, $_POST['item_name'][$sf_id]) : 'Unknown Seed/Fertilizer';
        $item_type = isset($_POST['item_type'][$sf_id]) ? mysqli_real_escape_string($conn, $_POST['item_type'][$sf_id]) : 'Unknown Type';

        // Basic validation: Check if quantity is positive and not exceeding available stock
        if ($quantity_to_buy > 0 && $quantity_to_buy <= $available_qty_hidden) {
            // Add or update item in the session cart
            $_SESSION['cart_seeds'][$sf_id] = [
                'sf_id' => $sf_id,
                'name' => $item_name,
                'type' => $item_type,
                'quantity_to_buy' => $quantity_to_buy,
                'price_per_unit' => $price_per_unit,
                'delivery_option' => $delivery_option,
                'location_input' => $location_input
            ];
            $items_added_count++;
        }
    }

    // Close the connection BEFORE redirecting and exiting
    $conn->close();

    if ($items_added_count > 0) {
        header('Location: view_seeds_cart.php?status=added&count=' . $items_added_count);
    } else {
        header('Location: buy_seeds.php?status=no_items_selected');
    }
    exit();

} else {
    // If no items were selected or the form was not submitted correctly
    // In this case, we might not have used $conn for any actual queries,
    // but it's good practice to close it if opened.
    $conn->close(); // Close here too, before redirecting in this branch.
    header('Location: buy_seeds.php?status=no_items_selected');
    exit();
}

// The line below is now unreachable only if die() is called during connection failure,
// which is fine as die() also terminates script.
// If the connection succeeds, it will always be closed in one of the branches above.
?>
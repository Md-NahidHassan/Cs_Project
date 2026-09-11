<?php
// process_order.php - Handles the final order processing after successful payment simulation

session_start();

// Database connection
// IMPORTANT: Replace 'localhost', 'root', '', 'farmsystem' with your actual database credentials
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    // Log the error instead of dying directly in production
    error_log('Database connection error in process_order.php: ' . mysqli_connect_error());
    die('<p style="color:red;">Error connecting to the database. Please try again later.</p>');
}

$order_details = $_SESSION['order_details'] ?? null;
$cart_seeds = $_SESSION['cart_seeds'] ?? [];

// Check if order details were passed from checkout_seeds.php
if (empty($order_details) || empty($cart_seeds)) {
    // If no order details or empty cart, redirect to home or cart with an error
    header('Location: index.php?status=order_error&message=No order details found or cart empty.');
    exit();
}

$u_id = $_SESSION['u_id'] ?? null; // Get user ID if available, otherwise null for guest orders

// Extract order details for easier access
$grand_total = $order_details['grand_total'];
$customer_name = $order_details['customer_name'];
$customer_email = $order_details['customer_email'];
$customer_phone = $order_details['customer_phone'];
$delivery_address = $order_details['delivery_address'];
$payment_method = $order_details['payment_method'];
$transaction_id = $order_details['transaction_id'];

$order_success = false;
$order_id = null; // To store the newly inserted order ID

// Start a database transaction for atomicity (all or nothing)
mysqli_begin_transaction($conn);

try {
    // 1. Insert the main order into the `orders` table
    // FIX: Changed 'grand_total' to 'order_total'. If your column is named something else
    // (e.g., 'total_amount'), please change 'order_total' in the query below to match your database.
    $stmt = $conn->prepare("
        INSERT INTO `orders` (u_id, order_total, customer_name, customer_email, customer_phone, delivery_address, payment_method, transaction_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed')
    ");
    // 's' for string (NULL will be handled by PDO or MySQLi if u_id is nullable)
    // For simplicity, we assume u_id can be NULL.
    $stmt->bind_param('idssssss', $u_id, $grand_total, $customer_name, $customer_email, $customer_phone, $delivery_address, $payment_method, $transaction_id);

    if (!$stmt->execute()) {
        throw new Exception('Error inserting order: ' . $stmt->error);
    }
    $order_id = mysqli_insert_id($conn); // Get the ID of the newly inserted order
    $stmt->close();

    // 2. Insert individual items into `order_items` table and update stock
    $item_stmt = $conn->prepare("
        INSERT INTO `order_items` (order_id, sf_id, item_name, item_type, quantity_ordered, price_at_order)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stock_stmt = $conn->prepare("
        UPDATE `seeds_fertilizers` SET stock_quantity = stock_quantity - ? WHERE sf_id = ? AND stock_quantity >= ?
    ");

    foreach ($cart_seeds as $sf_id => $item) {
        // Insert order item
        $item_name = $item['name'];
        $item_type = $item['type'];
        $quantity_to_buy = $item['quantity_to_buy'];
        $price_per_unit = $item['price_per_unit'];

        $item_stmt->bind_param('iissid', $order_id, $sf_id, $item_name, $item_type, $quantity_to_buy, $price_per_unit);
        if (!$item_stmt->execute()) {
            throw new Exception('Error inserting order item for ' . htmlspecialchars($item_name) . ': ' . $item_stmt->error);
        }

        // Update stock quantity
        $stock_stmt->bind_param('iii', $quantity_to_buy, $sf_id, $quantity_to_buy);
        if (!$stock_stmt->execute()) {
            throw new Exception('Error updating stock for ' . htmlspecialchars($item_name) . ': ' . $stock_stmt->error);
        }
        // Check if stock was actually updated (meaning stock_quantity >= quantity_to_buy)
        if (mysqli_affected_rows($conn) === 0) {
            // This means stock was insufficient or item not found, rollback
            throw new Exception('Insufficient stock for ' . htmlspecialchars($item_name) . ' or item not found.');
        }
    }
    $item_stmt->close();
    $stock_stmt->close();

    // If all successful, commit the transaction
    mysqli_commit($conn);
    $order_success = true;

} catch (Exception $e) {
    // Rollback the transaction if any error occurred
    mysqli_rollback($conn);
    error_log('Order processing failed: ' . $e->getMessage());
    $error_message = 'Failed to process your order: ' . htmlspecialchars($e->getMessage());
    $order_success = false;
} finally {
    mysqli_close($conn);
}

// Clear the cart and order details from session regardless of success
unset($_SESSION['cart_seeds']);
unset($_SESSION['order_details']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $order_success ? 'Order Confirmed!' : 'Order Failed' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom CSS variables for consistent theming */
        :root {
            --color-farm-green: #16a34a;
            --color-farm-dark: #166534;
            --color-farm-light: #dcfce7;
            --color-red-error: #dc2626; /* Tailwind red-600 */
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6; /* Light gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .message-box {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 0.75rem;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid var(--color-farm-green);
        }
        .message-box.error {
            border-color: var(--color-red-error);
        }
        h1 {
            color: var(--color-farm-dark);
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: bold;
        }
        .success-msg {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-farm-dark);
            margin-bottom: 1rem;
        }
        .error-msg-display { /* Distinct class for displaying general error messages */
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-red-error);
            margin-bottom: 1rem;
        }
        p {
            margin-bottom: 1rem;
            line-height: 1.5;
            color: #333;
        }
        .back-link {
            display: inline-block;
            background-color: var(--color-farm-green);
            color: white;
            padding: 0.8rem 1.6rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-top: 2rem;
        }
        .back-link:hover {
            background-color: #1a9e4e;
        }
    </style>
</head>
<body>
    <div class="message-box <?= $order_success ? '' : 'error' ?>">
        <?php if ($order_success): ?>
            <h1>🎉 Order Confirmed!</h1>
            <p class="success-msg">Your order for seeds and fertilizers has been successfully placed.</p>
            <p><strong>Order Total:</strong> ৳<?= number_format($grand_total, 2) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment_method) ?></p>
            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction_id) ?></p>
            <p class="mt-4">You will receive an email confirmation shortly.</p>
            <a href="index.php" class="back-link">Back to Home Page</a>
        <?php else: ?>
            <h1>❌ Order Failed!</h1>
            <p class="error-msg-display">We encountered an issue while processing your order.</p>
            <p class="text-red-500"><?= htmlspecialchars($error_message ?? 'Please try again or contact support.') ?></p>
            <a href="view_seeds_cart.php" class="back-link" style="background-color: #ef4444;">Back to Cart</a>
            <a href="index.php" class="back-link" style="background-color: #6b7280; margin-left: 10px;">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>

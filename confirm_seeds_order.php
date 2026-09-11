<?php
// confirm_seeds_order.php
session_start();

// --- START: Enhanced Error Reporting (Keep for debugging) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END: Enhanced Error Reporting ---

$conn = mysqli_connect('localhost', 'naba', '12345', 'farmsystem');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

$u_id = $_SESSION['u_id'] ?? null;
if (!$u_id) {
    // Redirect to login if user is not logged in
    header('Location: login.php');
    exit();
}

// Fetch cart items from the seeds_cart table
$stmt_cart = mysqli_prepare($conn, "
    SELECT sc.cart_id, sc.sf_id, sc.item_name, sc.item_type, sc.quantity, sc.delivery_option, sc.location, sc.total_price
    FROM seeds_cart sc
    WHERE sc.u_id = ?
");
mysqli_stmt_bind_param($stmt_cart, "i", $u_id);
mysqli_stmt_execute($stmt_cart);
$result_cart = mysqli_stmt_get_result($stmt_cart);

if (mysqli_num_rows($result_cart) === 0) {
    echo "
    <!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Empty Seeds Cart</title>
        <script src=\"https://cdn.tailwindcss.com\"></script>
        <style>
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
        </style>
    </head>
    <body class=\"bg-gray-50\">
        <div class='message-box'>
            <p>Your seeds and fertilizers cart is empty. Nothing to confirm.</p>
            <a href='buy_seeds.php'>Go to Buy Seeds & Fertilizers</a>
        </div>
    </body>
    </html>";
    exit();
}

$cart_items = [];
$overall_total = 0;
$requires_delivery_charge = false; // Flag to track if delivery charge is needed

while ($row = mysqli_fetch_assoc($result_cart)) {
    $cart_items[] = $row;
    $overall_total += $row['total_price'];
    if ($row['delivery_option'] === 'delivery') {
        $requires_delivery_charge = true;
    }
}
mysqli_stmt_close($stmt_cart);

$delivery_charge_amount = 0;
if ($requires_delivery_charge) {
    $delivery_charge_amount = 150.00; // Flat 150 Taka delivery charge
    $overall_total += $delivery_charge_amount;
}


// Handle form submission for payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = null;
    $payment_status = 'Pending'; // Default status
    $delivery_status = 'Pending'; // Default delivery status

    // Validate payment method and collect specific details
    switch ($payment_method) {
        case 'Cash on Delivery':
            // No extra details needed, payment_status remains 'Pending'
            break;
        case 'Card':
            $card_number = trim($_POST['card_number'] ?? '');
            // Simple validation (actual systems would use secure tokens)
            if (empty($card_number) || !ctype_digit($card_number) || strlen($card_number) !== 16) {
                die('<p style="color:red; text-align:center;">Invalid card number. Please enter a 16-digit number.</p>');
            }
            // Simulate a transaction ID
            $transaction_id = 'CARD_' . uniqid() . '_' . substr($card_number, -4);
            $payment_status = 'Paid'; // Assume successful payment for simulation
            break;
        case 'Mobile Banking':
            $mobile_provider = $_POST['mobile_provider'] ?? '';
            $mobile_number = trim($_POST['mobile_number'] ?? '');
            // Simple validation
            if (empty($mobile_provider) || !in_array($mobile_provider, ['Bkash', 'Nagad', 'Rocket'])) {
                die('<p style="color:red; text-align:center;">Invalid mobile banking provider.</p>');
            }
            if (empty($mobile_number) || !ctype_digit($mobile_number) || strlen($mobile_number) !== 11) {
                die('<p style="color:red; text-align:center;">Invalid mobile number. Please enter an 11-digit number.</p>');
            }
            // Simulate a transaction ID
            $transaction_id = strtoupper($mobile_provider) . '_' . uniqid() . '_' . substr($mobile_number, -4);
            $payment_status = 'Paid'; // Assume successful payment for simulation
            break;
        default:
            die('<p style="color:red; text-align:center;">Please select a valid payment method.</p>');
    }

    // Insert each cart item into the orders table
    // Now including 'delivery_location'
    $insert_order_sql = "
        INSERT INTO orders
        (u_id, list_id, quantity, truck, total_price, payment_method, payment_status, transaction_id, delivery_status, ordered_at, order_type, delivery_location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
    ";
    $stmt_insert_order = mysqli_prepare($conn, $insert_order_sql);

    // Prepare for inventory update
    $stmt_update_inventory = mysqli_prepare($conn, "UPDATE seeds_fertilizer SET quantity = quantity - ? WHERE sf_id = ?");

    foreach ($cart_items as $item) {
        // Map delivery_option to 'truck' field (Yes/No)
        $mapped_truck_value = ($item['delivery_option'] === 'delivery') ? 'Yes' : 'No';
        $order_type = 'seed_fertilizer'; // Explicitly set order type
        $delivery_location_for_order = ($item['delivery_option'] === 'delivery') ? $item['location'] : null; // Only save location if delivery

        // For individual items in the orders table, we still use their base total_price.
        // The overall_total handles the delivery charge.
        $item_total_price_for_order = $item['total_price'];

        mysqli_stmt_bind_param(
            $stmt_insert_order,
            'iisdsssssss', // NOTICE the extra 's' for delivery_location (u_id, list_id, quantity, truck, total_price, payment_method, payment_status, transaction_id, delivery_status, order_type, delivery_location)
            $u_id,
            $item['sf_id'], // Using sf_id as list_id in the orders table
            $item['quantity'],
            $mapped_truck_value,
            $item_total_price_for_order, // Use item's original total price
            $payment_method,
            $payment_status,
            $transaction_id,
            $delivery_status,
            $order_type,
            $delivery_location_for_order // Bind the new delivery_location
        );
        if (!mysqli_stmt_execute($stmt_insert_order)) {
            die('<p style="color:red; text-align:center;">Error placing order for ' . htmlspecialchars($item['item_name']) . ': ' . htmlspecialchars(mysqli_stmt_error($stmt_insert_order)) . '</p>');
        }

        // Update inventory for seeds_fertilizer
        mysqli_stmt_bind_param($stmt_update_inventory, "ii", $item['quantity'], $item['sf_id']);
        if (!mysqli_stmt_execute($stmt_update_inventory)) {
            error_log("Error updating inventory for sf_id " . $item['sf_id'] . ": " . mysqli_stmt_error($stmt_update_inventory));
            // You might choose to log this error but still proceed with the order if inventory update is not critical enough to halt the whole transaction
        }
    }
    mysqli_stmt_close($stmt_insert_order);
    mysqli_stmt_close($stmt_update_inventory); // Close the inventory update statement

    // Clear the seeds_cart after successful order placement
    $stmt_clear_cart = mysqli_prepare($conn, "DELETE FROM seeds_cart WHERE u_id = ?");
    mysqli_stmt_bind_param($stmt_clear_cart, "i", $u_id);
    mysqli_stmt_execute($stmt_clear_cart);
    mysqli_stmt_close($stmt_clear_cart);
    mysqli_close($conn);

    // Redirect to a success page or my_orders.php
    echo "<!DOCTYPE html>
    <html><head>
      <meta charset=\"utf-8\">
      <title>Seeds & Fertilizers Order Confirmed!</title>
      <script src=\"https://cdn.tailwindcss.com\"></script>
      <style>
          body { font-family: Arial, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
          .success-box { background-color: #dcfce7; color: #166534; padding: 2rem; border-radius: 0.75rem; max-width: 500px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border: 1px solid #22c55e; }
          .success-msg { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
          .success-details { font-size: 1.1rem; margin-bottom: 1.5rem; }
          .back-link { display: inline-block; background-color: #22c55e; color: white; padding: 0.6rem 1.2rem; border-radius: 0.375rem; text-decoration: none; font-weight: bold; transition: background-color 0.3s ease; }
          .back-link:hover { background-color: #1a9e4e; }
      </style>
    </head><body>
      <div class=\"success-box\">
        <p class=\"success-msg\">✅ Your Seeds & Fertilizers order has been placed!</p>
        <p class=\"success-details\">Subtotal: ৳" . number_format($overall_total - $delivery_charge_amount, 2) . "</p>";
    if ($requires_delivery_charge) {
        echo "<p class=\"success-details\">Delivery Charge: ৳" . number_format($delivery_charge_amount, 2) . "</p>";
    }
    echo "
        <p class=\"success-details\">Total: ৳" . number_format($overall_total, 2) . "</p>
        <p class=\"success-details\">Payment Method: " . htmlspecialchars($payment_method) . "</p>";
    if ($transaction_id) {
        echo "<p class=\"success-details\">Transaction ID: " . htmlspecialchars($transaction_id) . "</p>";
    }
    echo "
        <p><a class=\"back-link\" href=\"myOrders.php\">View My Orders</a></p>
      </div>
    </body></html>";
    exit;
}

mysqli_close($conn); // Close connection as early as possible
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Seeds & Fertilizers Order - FarmHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;
            --farm-accent: #8bc34a; /* A lighter, brighter green for accents */
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background-color: #AEEA94; /* Existing background color, consider making it more subtle if needed */
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: var(--farm-dark);
            margin-bottom: 25px;
            font-size: 2.5rem;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        th {
            background-color: var(--farm-dark);
            color: white;
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
        }
        tr:nth-child(even) { background-color: #f8f8f8; }
        tr:hover { background-color: #e8f5e9; }
        .total-row td {
            font-weight: bold;
            font-size: 1.1rem;
            background-color: #f0fdf4; /* Light green for total row */
        }
        .payment-options-section {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 30px;
            text-align: left;
        }
        .payment-options-section h2 {
            font-size: 1.8rem;
            color: var(--farm-dark);
            margin-bottom: 20px;
            text-align: center;
        }

        /* Modern Payment Method Styling */
        .payment-method-group {
            display: flex;
            flex-direction: column; /* Stack radio buttons */
            gap: 15px; /* Spacing between options */
            margin-bottom: 25px;
        }

        .payment-method-option {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #fcfcfc;
            position: relative; /* For custom radio button */
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .payment-method-option:hover {
            border-color: var(--farm-accent);
            background-color: #f9fff6;
            transform: translateY(-2px);
        }

        .payment-method-option input[type="radio"] {
            /* Hide default radio button */
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-method-option .custom-radio {
            width: 20px;
            height: 20px;
            border: 2px solid var(--farm-green);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0; /* Prevent it from shrinking */
            transition: all 0.2s ease;
        }

        .payment-method-option input[type="radio"]:checked + .custom-radio {
            background-color: var(--farm-green);
            border-color: var(--farm-dark);
            box-shadow: 0 0 0 4px var(--farm-light); /* Outer ring effect */
        }

        .payment-method-option input[type="radio"]:checked + .custom-radio::after {
            content: '';
            width: 8px;
            height: 8px;
            background-color: white;
            border-radius: 50%;
            display: block;
        }

        .payment-method-option span {
            font-size: 1.1rem;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px; /* Space between icon and text */
        }
        .payment-method-option i {
            font-size: 1.3rem; /* Icon size */
            color: var(--farm-green);
        }

        /* Details forms */
        .payment-details-form {
            background-color: #f0fdf4; /* Very light green for sub-forms */
            padding: 20px;
            border-radius: 5px;
            border: 1px solid var(--farm-green);
            margin-top: 15px;
            display: none; /* Hidden by default, JS toggles */
        }
        .payment-details-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--farm-dark);
            font-size: 0.95rem;
        }
        .payment-details-form input[type="text"],
        .payment-details-form input[type="number"],
        .payment-details-form select {
            width: 100%;
            padding: 12px; /* Increased padding */
            border: 1px solid #ccc;
            border-radius: 6px; /* Slightly more rounded */
            margin-top: 5px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .payment-details-form input[type="text"]:focus,
        .payment-details-form input[type="number"]:focus,
        .payment-details-form select:focus {
            border-color: var(--farm-green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); /* Focus ring */
            outline: none;
        }

        .confirm-btn {
            padding: 12px 25px;
            background-color: var(--farm-green);
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .confirm-btn:hover {
            background-color: #1a9e4e;
            transform: translateY(-1px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            h1 {
                font-size: 2rem;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            .payment-options-section {
                padding: 15px;
            }
            .payment-options-section h2 {
                font-size: 1.5rem;
            }
            .payment-method-option {
                padding: 12px 15px;
            }
            .payment-method-option span {
                font-size: 1rem;
                gap: 8px;
            }
            .payment-method-option i {
                font-size: 1.1rem;
            }
        }
        @media (max-width: 640px) {
            .container {
                padding: 10px;
            }
            h1 {
                font-size: 1.8rem;
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
            td:nth-of-type(1):before { content: "Item Name"; }
            td:nth-of-type(2):before { content: "Type"; }
            td:nth-of-type(3):before { content: "Quantity"; }
            td:nth-of-type(4):before { content: "Delivery Option"; }
            td:nth-of-type(5):before { content: "Delivery Location"; }
            td:nth-of-type(6):before { content: "Total Price"; }
            .total-row td:nth-of-type(1):before { content: ""; } /* Clear label for total row first cell */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirm Your Seeds & Fertilizers Order</h1>

        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Type</th>
                    <th>Quantity (kg/unit)</th>
                    <th>Delivery Option</th>
                    <th>Delivery Location</th>
                    <th>Total Price (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['item_type']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['delivery_option'] === 'delivery' ? '🚚 Delivery' : '🏠 Pickup') ?></td>
                        <td><?= htmlspecialchars($item['location'] ?: 'N/A') ?></td>
                        <td><?= number_format($item['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Subtotal:</td>
                    <td></td>
                    <td>৳<?= number_format($overall_total - $delivery_charge_amount, 2) ?></td>
                </tr>
                <?php if ($requires_delivery_charge): ?>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Delivery Charge:</td>
                    <td></td>
                    <td>৳<?= number_format($delivery_charge_amount, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-right">Overall Total:</td>
                    <td></td>
                    <td>৳<?= number_format($overall_total, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="payment-options-section">
            <h2>Select Payment Method</h2>
            <form method="post" onsubmit="return validatePaymentForm()">
                <div class="payment-method-group">
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="Cash on Delivery" checked onchange="showPaymentDetails()">
                        <span class="custom-radio"></span>
                        <span><i class="fas fa-handshake"></i> Cash on Delivery (COD)</span>
                    </label>
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="Card" onchange="showPaymentDetails()">
                        <span class="custom-radio"></span>
                        <span><i class="fas fa-credit-card"></i> Card Payment</span>
                    </label>
                    <label class="payment-method-option">
                        <input type="radio" name="payment_method" value="Mobile Banking" onchange="showPaymentDetails()">
                        <span class="custom-radio"></span>
                        <span><i class="fas fa-mobile-alt"></i> Mobile Banking</span>
                    </label>
                </div>

                <div id="card_details" class="payment-details-form" style="display: none;">
                    <label for="card_number">Card Number (16 digits):</label>
                    <input type="text" id="card_number" name="card_number" pattern="\d{16}" maxlength="16" placeholder="XXXXXXXXXXXXXXXX">
                </div>

                <div id="mobile_banking_details" class="payment-details-form" style="display: none;">
                    <label for="mobile_provider">Mobile Banking Provider:</label>
                    <select id="mobile_provider" name="mobile_provider">
                        <option value="">Select Provider</option>
                        <option value="Bkash">Bkash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Rocket">Rocket</option>
                    </select>
                    <label for="mobile_number">Mobile Number (11 digits):</label>
                    <input type="text" id="mobile_number" name="mobile_number" pattern="\d{11}" maxlength="11" placeholder="01XXXXXXXXX">
                </div>

                <button type="submit" name="process_payment" class="confirm-btn">Place Order</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to show/hide payment details forms
        function showPaymentDetails() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById('card_details').style.display = 'none';
            document.getElementById('mobile_banking_details').style.display = 'none';

            // Clear previous validation messages/styles if any and remove 'required'
            document.getElementById('card_number').removeAttribute('required');
            document.getElementById('mobile_provider').removeAttribute('required');
            document.getElementById('mobile_number').removeAttribute('required');

            if (selectedMethod === 'Card') {
                document.getElementById('card_details').style.display = 'block';
                document.getElementById('card_number').setAttribute('required', 'required');
            } else if (selectedMethod === 'Mobile Banking') {
                document.getElementById('mobile_banking_details').style.display = 'block';
                document.getElementById('mobile_provider').setAttribute('required', 'required');
                document.getElementById('mobile_number').setAttribute('required', 'required');
            }
        }

        // Add client-side validation
        function validatePaymentForm() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;

            if (selectedMethod === 'Card') {
                const cardNumber = document.getElementById('card_number').value;
                if (!/^\d{16}$/.test(cardNumber)) {
                    showMessageBox('Please enter a valid 16-digit card number.');
                    return false;
                }
            } else if (selectedMethod === 'Mobile Banking') {
                const mobileProvider = document.getElementById('mobile_provider').value;
                const mobileNumber = document.getElementById('mobile_number').value;

                if (mobileProvider === '') {
                    showMessageBox('Please select a mobile banking provider.');
                    return false;
                }
                if (!/^\d{11}$/.test(mobileNumber)) {
                    showMessageBox('Please enter a valid 11-digit mobile number.');
                    return false;
                }
            }
            return true; // Allow form submission
        }

        // Custom message box function (replaces alert())
        function showMessageBox(message) {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;';
            overlay.id = 'messageBoxOverlay';

            // Create message box
            const msgBox = document.createElement('div');
            msgBox.style.cssText = 'background-color: #dcfce7; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: center; max-width: 400px; width: 90%; color: #166534;';

            const msgText = document.createElement('p');
            msgText.textContent = message;
            msgText.style.cssText = 'margin-bottom: 20px; font-size: 1.1rem;';

            const closeButton = document.createElement('button');
            closeButton.textContent = 'OK';
            closeButton.style.cssText = 'background-color: #22c55e; color: white; padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; transition: background-color 0.3s;';
            closeButton.onmouseover = function() { this.style.backgroundColor = '#1a9e4e'; };
            closeButton.onmouseout = function() { this.style.backgroundColor = '#22c55e'; };
            closeButton.onclick = function() {
                document.body.removeChild(overlay);
            };

            msgBox.appendChild(msgText);
            msgBox.appendChild(closeButton);
            overlay.appendChild(msgBox);
            document.body.appendChild(overlay);
        }

        // Call on page load to set initial state
        document.addEventListener('DOMContentLoaded', showPaymentDetails);
    </script>
</body>
</html>
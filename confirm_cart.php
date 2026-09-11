<?php
session_start();

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

// Fetch cart items
$stmt_cart = mysqli_prepare($conn, "
    SELECT c.cart_id, c.list_id, m.crop_name, c.quantity, c.truck, c.total_price
    FROM cart c
    JOIN market_listing m ON c.list_id = m.list_id
    WHERE c.u_id = ?
");
mysqli_stmt_bind_param($stmt_cart, "i", $u_id);
mysqli_stmt_execute($stmt_cart);
$result_cart = mysqli_stmt_get_result($stmt_cart);

if (mysqli_num_rows($result_cart) === 0) {
    echo "<!DOCTYPE html>
    <html><head>
      <meta charset=\"utf-8\">
      <title>Cart Empty</title>
      <script src=\"https://cdn.tailwindcss.com\"></script>
      <style>
          body { font-family: Arial, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
          .empty-box { background-color: #ffe0b2; color: #e65100; padding: 2rem; border-radius: 0.75rem; max-width: 500px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border: 1px solid #ff9800; }
          .empty-msg { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
          .back-link { display: inline-block; background-color: #22c55e; color: white; padding: 0.6rem 1.2rem; border-radius: 0.375rem; text-decoration: none; font-weight: bold; transition: background-color 0.3s ease; }
          .back-link:hover { background-color: #1a9e4e; }
      </style>
    </head><body>
      <div class=\"empty-box\">
        <p class=\"empty-msg\">🛒 Your cart is empty. Nothing to confirm.</p>
        <p><a class=\"back-link\" href=\"marketlist.php\">Go to Market Place</a></p>
      </div>
    </body></html>";
    exit();
}

$cart_items = [];
$overall_total = 0;
while ($row = mysqli_fetch_assoc($result_cart)) {
    $cart_items[] = $row;
    $overall_total += $row['total_price'];
}
mysqli_stmt_close($stmt_cart);


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
                die('<p style="color:red;">Invalid card number. Please enter a 16-digit number.</p>');
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
                die('<p style="color:red;">Invalid mobile banking provider.</p>');
            }
            if (empty($mobile_number) || !ctype_digit($mobile_number) || strlen($mobile_number) !== 11) {
                die('<p style="color:red;">Invalid mobile number. Please enter an 11-digit number.</p>');
            }
            // Simulate a transaction ID
            $transaction_id = strtoupper($mobile_provider) . '_' . uniqid() . '_' . substr($mobile_number, -4);
            $payment_status = 'Paid'; // Assume successful payment for simulation
            break;
        default:
            die('<p style="color:red;">Please select a valid payment method.</p>');
    }

    // Insert each cart item into the orders table
    $insert_order_sql = "
        INSERT INTO orders
        (u_id, list_id, quantity, truck, total_price, payment_method, payment_status, transaction_id, delivery_status, ordered_at, order_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ";
    $stmt_insert_order = mysqli_prepare($conn, $insert_order_sql);

    foreach ($cart_items as $item) {
        $raw_truck_from_cart = trim($item['truck']);
        if ($raw_truck_from_cart === 'Yes') {
            $mapped_truck_value = 'Yes';
        } else {
            $mapped_truck_value = 'No';
        }
        $order_type = 'crop'; // Set order_type to 'crop' for crop orders

        mysqli_stmt_bind_param(
            $stmt_insert_order,
            'iisdssssss', // NOTICE the extra 's' for order_type
            $u_id,
            $item['list_id'],
            $item['quantity'],
            $mapped_truck_value,
            $item['total_price'],
            $payment_method,
            $payment_status,
            $transaction_id,
            $delivery_status,
            $order_type // Bind the new order_type here
        );
        if (!mysqli_stmt_execute($stmt_insert_order)) {
            die('<p style="color:red;">Error placing order: ' . htmlspecialchars(mysqli_stmt_error($stmt_insert_order)) . '</p>');
        }
    }
    mysqli_stmt_close($stmt_insert_order);

    // Clear the cart after successful order placement
    $stmt_clear_cart = mysqli_prepare($conn, "DELETE FROM cart WHERE u_id = ?");
    mysqli_stmt_bind_param($stmt_clear_cart, "i", $u_id);
    mysqli_stmt_execute($stmt_clear_cart);
    mysqli_stmt_close($stmt_clear_cart);
    mysqli_close($conn);

    // Redirect to a success page or my_orders.php
    echo "<!DOCTYPE html>
    <html><head>
      <meta charset=\"utf-8\">
      <title>Order Confirmed!</title>
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
        <p class=\"success-msg\">✅ Your order has been placed!</p>
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
    <title>Confirm Order - FarmHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;

            /* Mobile Banking Colors - Exact colors from the image */
            --bkash-color: #E4026B;
            --nagad-color: #F68B1F; /* Adjusted to more orange based on image */
            --rocket-color: #8D2B84;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #AEEA94;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-align: center;
            margin-bottom: 20px;
        }
        h1 {
            color: var(--farm-dark);
            margin-bottom: 25px;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
            letter-spacing: 0.05em;
        }
        tr:nth-child(even) { background-color: #f8f8f8; }
        tr:hover { background-color: #e8f5e9; }
        .total-row td {
            font-weight: bold;
            font-size: 1.2rem;
            background-color: #f0fdf4;
            border-top: 2px solid var(--farm-green);
        }
        .payment-options-section {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            margin-top: 35px;
            text-align: left;
        }
        .payment-options-section h2 {
            font-size: 2rem;
            color: var(--farm-dark);
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
            text-shadow: 0.5px 0.5px 1px rgba(0,0,0,0.05);
        }

        /* Payment Method Cards */
        .payment-method-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .payment-method-card {
            flex: 1 1 280px;
            background-color: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-height: 80px;
        }
        .payment-method-card:hover {
            border-color: var(--farm-green);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .payment-method-card.active {
            border-color: var(--farm-dark);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            background-color: var(--farm-light);
        }
        .payment-method-card input[type="radio"] {
            display: none; /* Hide default radio button */
        }
        .payment-method-card label {
            font-size: 1.3rem;
            font-weight: bold;
            color: #555;
            display: flex;
            align-items: center;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }
        .payment-method-card label i {
            margin-right: 12px;
            font-size: 1.8rem;
        }

        /* Styling for Mobile Banking Options */
        .mobile-banking-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px; /* Space between radio button and colored box */
            cursor: pointer;
        }
        .mobile-banking-option input[type="radio"] {
            -webkit-appearance: none; /* Hide default radio button for custom styling */
            -moz-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 50%;
            outline: none;
            cursor: pointer;
            position: relative;
            flex-shrink: 0; /* Prevent it from shrinking */
        }
        .mobile-banking-option input[type="radio"]:checked {
            border-color: var(--farm-dark);
        }
        .mobile-banking-option input[type="radio"]:checked::before {
            content: '';
            width: 10px;
            height: 10px;
            background-color: var(--farm-dark);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: block;
        }

        .mobile-banking-label-content {
            display: flex;
            align-items: center;
            justify-content: center; /* Center the box and text */
            padding: 8px 20px; /* Padding inside the colored box */
            border-radius: 6px;
            font-weight: bold;
            color: white; /* Text color inside the colored box */
            font-size: 1.2rem;
            min-width: 120px; /* Minimum width for the colored box */
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Specific colors for mobile banking providers */
        .mobile-banking-label-content.bkash { background-color: var(--bkash-color); }
        .mobile-banking-label-content.nagad { background-color: var(--nagad-color); }
        .mobile-banking-label-content.rocket { background-color: var(--rocket-color); }


        .payment-details-form {
            background-color: #f0fdf4;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--farm-green);
            margin-top: 25px;
            transition: all 0.4s ease-in-out;
        }
        .payment-details-form label {
            display: block;
            margin-bottom: 12px;
            font-weight: bold;
            color: var(--farm-dark);
            font-size: 1rem;
        }
        .payment-details-form input[type="text"],
        .payment-details-form input[type="number"],
        .payment-details-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cce;
            border-radius: 6px;
            margin-top: 5px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
        }
        .payment-details-form input[type="text"]:focus,
        .payment-details-form input[type="number"]:focus,
        .payment-details-form select:focus {
            border-color: var(--farm-green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }
        .confirm-btn {
            padding: 14px 30px;
            background-color: var(--farm-green);
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .confirm-btn:hover {
            background-color: #1a9e4e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        }

        /* Custom Message Box Styles (kept for consistency with previous solution) */
        #messageBoxOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }
        #messageBoxOverlay div {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            text-align: center;
            max-width: 450px;
            width: 90%;
            color: #333;
            animation: slideIn 0.3s ease-out;
            border: 2px solid var(--farm-green);
        }
        #messageBoxOverlay p {
            margin-bottom: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--farm-dark);
        }
        #messageBoxOverlay button {
            background-color: var(--farm-green);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        #messageBoxOverlay button:hover {
            background-color: #1a9e4e;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirm Your Order</h1>

        <table>
            <thead>
                <tr>
                    <th>Crop</th>
                    <th>Quantity (kg)</th>
                    <th>Delivery</th>
                    <th>Total Price (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['crop_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['truck'] === 'Yes' ? '🚚 Truck Delivery' : '🏠 Pickup') ?></td>
                        <td><?= number_format($item['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Overall Total:</td>
                    <td>৳<?= number_format($overall_total, 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="payment-options-section">
            <h2>Select Payment Method</h2>
            <form method="post" onsubmit="return validatePaymentForm()">
                <div class="payment-method-group">
                    <div class="payment-method-card" id="card_cod">
                        <label class="mobile-banking-option">
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked onchange="showPaymentDetails()">
                            Cash on Delivery (COD)
                        </label>
                    </div>
                    <div class="payment-method-card" id="card_card">
                        <label class="mobile-banking-option">
                            <input type="radio" name="payment_method" value="Card" onchange="showPaymentDetails()">
                            <i class="fa-solid fa-credit-card"></i> Card Payment
                        </label>
                    </div>
                    <div class="payment-method-card" id="card_mobile_banking">
                        <label class="mobile-banking-option">
                            <input type="radio" name="payment_method" value="Mobile Banking" onchange="showPaymentDetails()">
                            <i class="fa-solid fa-mobile-screen-button"></i> Mobile Banking
                        </label>
                    </div>
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
                    <div id="mobile_provider_display" style="text-align: center; margin-top: 15px;">
                        </div>
                </div>

                <button type="submit" name="process_payment" class="confirm-btn">Place Order</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to show/hide payment details forms and manage card active state
        function showPaymentDetails() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const cardDetails = document.getElementById('card_details');
            const mobileBankingDetails = document.getElementById('mobile_banking_details');

            // Reset display and required attributes
            cardDetails.style.display = 'none';
            mobileBankingDetails.style.display = 'none';
            document.getElementById('card_number').required = false;
            document.getElementById('mobile_provider').required = false;
            document.getElementById('mobile_number').required = false;

            // Remove active class from all cards
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('active');
            });

            // Set display and required attributes based on selected method
            if (selectedMethod === 'Card') {
                cardDetails.style.display = 'block';
                document.getElementById('card_number').required = true;
                document.getElementById('card_card').classList.add('active');
            } else if (selectedMethod === 'Mobile Banking') {
                mobileBankingDetails.style.display = 'block';
                document.getElementById('mobile_provider').required = true;
                document.getElementById('mobile_number').required = true;
                document.getElementById('card_mobile_banking').classList.add('active');
                updateMobileProviderDisplay(); // Call to update display on initial selection
            } else if (selectedMethod === 'Cash on Delivery') {
                document.getElementById('card_cod').classList.add('active');
            }
        }

        // Function to update mobile banking provider display with colored block
        function updateMobileProviderDisplay() {
            const mobileProvider = document.getElementById('mobile_provider').value;
            const displayDiv = document.getElementById('mobile_provider_display');
            displayDiv.innerHTML = ''; // Clear previous content

            if (mobileProvider === 'Bkash') {
                displayDiv.innerHTML = '<div class="mobile-banking-label-content bkash">Bkash</div>';
            } else if (mobileProvider === 'Nagad') {
                displayDiv.innerHTML = '<div class="mobile-banking-label-content nagad">Nagad</div>';
            } else if (mobileProvider === 'Rocket') {
                displayDiv.innerHTML = '<div class="mobile-banking-label-content rocket">Rocket</div>';
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
            overlay.id = 'messageBoxOverlay'; // Use the existing ID for styling

            // Create message box
            const msgBox = document.createElement('div');

            const msgText = document.createElement('p');
            msgText.textContent = message;

            const closeButton = document.createElement('button');
            closeButton.textContent = 'OK';
            closeButton.onclick = function() {
                document.body.removeChild(overlay);
            };

            msgBox.appendChild(msgText);
            msgBox.appendChild(closeButton);
            overlay.appendChild(msgBox);
            document.body.appendChild(overlay);
        }

        // Attach event listener for mobile provider selection change
        document.getElementById('mobile_provider').addEventListener('change', updateMobileProviderDisplay);

        // Call on page load to set initial state
        document.addEventListener('DOMContentLoaded', () => {
            showPaymentDetails();
            updateMobileProviderDisplay(); // Ensure initial mobile banking display if it's the default/selected
        });
    </script>
</body>
</html>
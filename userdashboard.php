<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// --- START: Enhanced Error Reporting (Keep for debugging) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// --- END: Enhanced Error Reporting ---

// Check if user is logged in
if (!isset($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}

$u_id = (int)$_SESSION['u_id'];

// Initialize $f_id_for_loan_query early to avoid scope issues later.
// This will hold the farmer's f_id if the user is a farmer.
$f_id_for_loan_query = null;

// Fetch user info
// Using prepared statements for security, even for internal IDs, is good practice.
$user_query = "SELECT u_id, u_name, email, password, role, address, phone, created_at FROM user WHERE u_id = ?";
$stmt_user = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt_user, "i", $u_id);
mysqli_stmt_execute($stmt_user);
$user_result = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt_user);

// If user somehow doesn't exist after login (shouldn't happen with proper login flow)
if (!$user) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Fetch farmer info if applicable AND set f_id for loan queries
$farmer = null;
if (strtolower($user['role']) === 'farmer') {
    // Select f_id along with other farmer details
    $farmer_query = "SELECT f_id, f_name, location, farm_size, crop_sp FROM farmer WHERE u_id = ?";
    $stmt_farmer = mysqli_prepare($conn, $farmer_query);
    mysqli_stmt_bind_param($stmt_farmer, "i", $u_id);
    mysqli_stmt_execute($stmt_farmer);
    $farmer_result = mysqli_stmt_get_result($stmt_farmer);
    if ($farmer_result && mysqli_num_rows($farmer_result) > 0) {
        $farmer = mysqli_fetch_assoc($farmer_result);
        // Explicitly set f_id_for_loan_query from the fetched farmer data
        $f_id_for_loan_query = $farmer['f_id'];
    }
    mysqli_stmt_close($stmt_farmer);
}

// --- Fetch User's Quiz Progression (Past Scores) ---
$quiz_progression = [];
// Assuming quiz_id 1 is the general farming tips quiz
$quiz_id_to_fetch = 1;

$select_quiz_sql = "SELECT score, timestamp FROM quiz_results WHERE user_id = ? AND quiz_id = ? ORDER BY timestamp DESC LIMIT 5";
$stmt_quiz = mysqli_prepare($conn, $select_quiz_sql);
if ($stmt_quiz) {
    mysqli_stmt_bind_param($stmt_quiz, "ii", $u_id, $quiz_id_to_fetch);
    mysqli_stmt_execute($stmt_quiz);
    $quiz_result = mysqli_stmt_get_result($stmt_quiz);
    while ($row = mysqli_fetch_assoc($quiz_result)) {
        $quiz_progression[] = $row;
    }
    mysqli_stmt_close($stmt_quiz);
} else {
    // Log the error for debugging purposes, not for display to user
    error_log("Error preparing statement for fetching quiz results in userdashboard: " . mysqli_error($conn));
}
// --- End Fetch User's Quiz Progression ---

// --- Fetch User's Active Loan Details and calculate penalty ---
$active_loan_details = null;
$totalPenalty = 0; // Initialize penalty
$daysLate = 0; // Initialize days late

// Only attempt to fetch loan if the user is identified as a farmer AND has an f_id
if (strtolower($user['role']) === 'farmer' && $f_id_for_loan_query !== null) {
    $loan_query = "SELECT l_id, loan_amt, l_reason, a_date, due_date FROM loan WHERE f_id = ? AND status = 'active' LIMIT 1";
    $stmt_loan = mysqli_prepare($conn, $loan_query);
    if ($stmt_loan) {
        mysqli_stmt_bind_param($stmt_loan, "i", $f_id_for_loan_query); // Use the reliably set f_id
        mysqli_stmt_execute($stmt_loan);
        $loan_result = mysqli_stmt_get_result($stmt_loan);
        if (mysqli_num_rows($loan_result) > 0) {
            $active_loan_details = mysqli_fetch_assoc($loan_result);

            // Calculate penalty if loan is overdue
            $dailyPenaltyRate = 5; // ৳5 per day
            $currentDate = new DateTime();
            $dueDate = new DateTime($active_loan_details['due_date']);

            if ($currentDate > $dueDate) {
                $interval = $currentDate->diff($dueDate);
                $daysLate = $interval->days;
                $totalPenalty = $daysLate * $dailyPenaltyRate;
                // Add penalty to the loan_amt for display only on this page
                $active_loan_details['loan_amt'] += $totalPenalty;
            }


            // Fetch installments for the active loan
            $installments_query = "SELECT due_date, amount_due FROM loan_installments WHERE l_id = ? ORDER BY due_date ASC";
            $stmt_installments = mysqli_prepare($conn, $installments_query);
            if ($stmt_installments) {
                mysqli_stmt_bind_param($stmt_installments, "i", $active_loan_details['l_id']);
                mysqli_stmt_execute($stmt_installments);
                $installments_result = mysqli_stmt_get_result($stmt_installments);
                $active_loan_details['installments'] = [];
                while ($row = mysqli_fetch_assoc($installments_result)) {
                    $active_loan_details['installments'][] = $row;
                }
                mysqli_stmt_close($stmt_installments);
            } else {
                error_log("Error preparing statement for fetching installments: " . mysqli_error($conn));
            }
        }
        mysqli_stmt_close($stmt_loan);
    } else {
        error_log("Error preparing statement for fetching active loan: " . mysqli_error($conn));
    }
}
// --- End Fetch User's Active Loan Details ---


// Close database connection
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmHub - User Dashboard</title>
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
        /* Custom CSS from view_cart.php/myOrders.php for consistency */
        .bg-farm-header { background-color: #166534; }
        .border-farm-green { border-color: #86efac; }
        .text-farm-dark { color: #065f46; }
        .divide-farm-green > :not([hidden]) ~ :not([hidden]) { border-color: #bbf7d0; }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6; /* Consistent light gray background outside the main container */
            padding-bottom: 50px; /* Space for content below table */
            padding-top: 0; /* Remove default body padding-top */
        }

        .container {
            background-color: #AEEA94; /* Reverted to green as per user request */
            padding: 25px;
            border-radius: 10px;
            max-width: 1000px; /* Increased max-width for better layout */
            margin: 30px auto; /* Margin to clear sticky header */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex; /* Use flexbox for layout */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            gap: 20px; /* Space between flex items */
            justify-content: center; /* Center items when they wrap */
        }

        .section-box {
            background-color: #f9f9f9; /* Slightly off-white for sections, provides contrast */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            flex: 1; /* Allow sections to grow and shrink */
            min-width: 300px; /* Minimum width before wrapping */
        }

        h2, h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px; /* Added spacing */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #ffffff; /* White background for inner tables */
            border-radius: 8px; /* Rounded corners for tables */
            overflow: hidden; /* Ensures rounded corners apply to content */
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Subtle shadow for tables */
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #88B04B; /* Darker green for table headers */
            color: white;
            font-weight: bold;
        }

        tr:last-child td {
            border-bottom: none; /* No border on the last row */
        }

        .btn-group {
            margin-top: 30px; /* Increased spacing */
            text-align: center;
            width: 100%; /* Ensure button group takes full width */
            display: flex; /* Use flex for buttons */
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px; /* Space between buttons */
        }

        .btn {
            background-color: #FFDF88; /* Yellowish button */
            border: none;
            padding: 12px 22px; /* Increased padding */
            border-radius: 8px; /* More rounded corners */
            cursor: pointer;
            font-weight: bold;
            color: #333; /* Dark text for contrast */
            text-decoration: none; /* For anchor tags acting as buttons */
            display: inline-block; /* To allow padding for anchor tags */
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }

        .btn:hover {
            background-color: #ffd966; /* Slightly darker yellow on hover */
        }
        .message {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.info {
            background-color: #e0f2f7;
            color: #01579b;
            border: 1px solid #b3e5fc;
        }

        /* Styling for the Loan Application Status box */
        .loan-status-box {
            background-color: #dcfce7; /* farm-light green */
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #22c55e; /* farm-green border */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
            text-align: center;
            width: 100%; /* Ensure it takes full width when displayed */
        }
        .loan-status-box h3 {
            color: #166534; /* farm-dark green */
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        .loan-status-box .status-icon {
            color: #22c55e; /* farm-green */
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .loan-status-box p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
        }
        .loan-status-box ul {
            list-style: none;
            padding: 0;
            margin-top: 10px;
            text-align: left; /* Align list items to the left */
            display: inline-block; /* To allow text-align on list */
        }
        .loan-status-box ul li {
            margin-bottom: 5px;
            color: #555;
        }
        .loan-status-box .back-link {
            display: inline-block;
            background-color: #f97316; /* Orange for action */
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .loan-status-box .back-link:hover {
            background-color: #ea580c;
        }
    </style>
</head>
<body class="bg-gray-50">
  <!-- Header (Copied from view_cart.php for consistency) -->
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

<div class="container">
    <h2 style="width: 100%;">Welcome, <?php echo htmlspecialchars($user['u_name']); ?>!</h2>

    <div class="section-box">
        <h3>Your Profile Details</h3>
        <table>
            <tr><td><strong>User ID</strong></td><td><?php echo $user['u_id']; ?></td></tr>
            <tr><td><strong>Username</strong></td><td><?php echo htmlspecialchars($user['u_name']); ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
            <tr><td><strong>Phone</strong></td><td><?php echo htmlspecialchars($user['phone']); ?></td></tr>
            <tr><td><strong>Role</strong></td><td><?php echo htmlspecialchars($user['role']); ?></td></tr>
            <tr><td><strong>Address</strong></td><td><?php echo htmlspecialchars($user['address']); ?></td></tr>
            <tr><td><strong>Joined At</strong></td><td><?php echo htmlspecialchars($user['created_at']); ?></td></tr>
        </table>
    </div>

    <?php if ($farmer): ?>
        <div class="section-box">
            <h3>Farmer Specific Details</h3>
            <table>
                <tr><td><strong>Name</strong></td><td><?php echo htmlspecialchars($farmer['f_name']); ?></td></tr>
                <tr><td><strong>Location</strong></td><td><?php echo htmlspecialchars($farmer['location']); ?></td></tr>
                <tr><td><strong>Farm Size</strong></td><td><?php echo htmlspecialchars($farmer['farm_size']); ?> acres</td></tr>
                <tr><td><strong>Crop Specialization</strong></td><td><?php echo htmlspecialchars($farmer['crop_sp']); ?></td></tr>
            </table>
        </div>
    <?php endif; ?>

    <div class="section-box" style="flex-grow: 2;"> <!-- Allow quiz progression to take more space -->
        <h3>Your Quiz Progression</h3>
        <?php if (!empty($quiz_progression)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Attempt Date</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quiz_progression as $score_entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($score_entry['timestamp']))); ?></td>
                            <td><?php echo htmlspecialchars($score_entry['score']); ?> / 10</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="message info">Showing your last <?php echo count($quiz_progression); ?> quiz attempts.</p>
        <?php else: ?>
            <p class="message info">No past quiz records found for your account yet.</p>
            <div class="text-center mt-4"> <!-- Added margin-top for spacing -->
                <a href="farmingtip.php" class="btn">Take the first quiz!</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($active_loan_details): ?>
        <div class="loan-status-box" style="width: 100%;">
            <div class="status-icon">✅</div>
            <h3>Your Active Loan Details</h3>
            <p><strong>Loan Amount:</strong> ৳<?php echo number_format($active_loan_details['loan_amt'], 2); ?></p>
            <?php if ($daysLate > 0): ?>
                <p class="text-red-600 text-sm">
                    (Includes ৳<?php echo number_format($totalPenalty, 2); ?> penalty for <?= $daysLate ?> late day(s))
                </p>
            <?php endif; ?>
            <p><strong>Reason:</strong> <?php echo htmlspecialchars($active_loan_details['l_reason']); ?></p>
            <p><strong>Application Date:</strong> <?php echo htmlspecialchars($active_loan_details['a_date']); ?></p>
            <p><strong>Due Date:</strong> <?php echo htmlspecialchars($active_loan_details['due_date']); ?></p>

            <?php if (!empty($active_loan_details['installments'])): ?>
                <p style="margin-top: 15px;"><strong>Scheduled Installments:</strong></p>
                <ul>
                    <?php foreach ($active_loan_details['installments'] as $installment): ?>
                        <li><?php echo htmlspecialchars($installment['due_date']); ?>: ৳<?php echo number_format($installment['amount_due'], 2); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="take_loan.php" class="back-link">Repay Loan</a>
        </div>
    <?php endif; ?>

    <div class="btn-group">
        <a href="deleteuser.php" onclick="return confirm('Are you sure you want to delete your account?');">
            <button class="btn">Delete Account</button>
        </a>
        <a href="farmingtip.php" class="btn">Go to Courses & Quiz</a>
        <a href="take_loan.php" class="btn">Loan Center</a> <!-- Existing button for Loan Center -->
        <a href="history.php" class="btn">Loan History</a> <!-- NEW BUTTON -->
        <a href="logout.php" class="btn">Log Out</a>
    </div>
</div>
</body>
</html>

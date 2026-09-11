<?php
session_start();

// 1) Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
  die('DB connection error: ' . mysqli_connect_error());
}

// 2) Must be logged in
if (empty($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}
$u_id = (int)$_SESSION['u_id'];

// 3) Fetch f_id based on u_id and validate role
$f_id = 0; // Initialize f_id
$role = '';
$stmt = $conn->prepare("
  SELECT u.role, f.f_id
  FROM `user` AS u
  LEFT JOIN farmer AS f ON f.u_id = u.u_id
  WHERE u.u_id = ?
  LIMIT 1
");
$stmt->bind_param('i', $u_id);
$stmt->execute();
$stmt->bind_result($role, $fetched_f_id);
if ($stmt->fetch()) {
    $f_id = (int)$fetched_f_id;
}
$stmt->close();

// 4) Ensure the user is a registered farmer
if (strcasecmp($role,'farmer')!==0 || $f_id <= 0) {
    die('<p style="color:red;">Only registered farmers can view loan history. Please ensure your profile is complete.</p>');
}

// 5) Fetch all PAID loans for this farmer (f_id)
$paid_loans = [];
// IMPORTANT: Now selecting 'original_loan_amt' instead of 'loan_amt' for history
$select_loans_sql = "SELECT l_id, original_loan_amt, l_reason, a_date, due_date, status
                     FROM loan
                     WHERE f_id = ? AND status = 'paid'
                     ORDER BY a_date DESC"; // Order by application date, newest first

$stmt_loans = mysqli_prepare($conn, $select_loans_sql);
if ($stmt_loans) {
    mysqli_stmt_bind_param($stmt_loans, "i", $f_id);
    mysqli_stmt_execute($stmt_loans);
    $result_loans = mysqli_stmt_get_result($stmt_loans);
    while ($row = mysqli_fetch_assoc($result_loans)) {
        $paid_loans[] = $row;
    }
    mysqli_stmt_close($stmt_loans);
} else {
    error_log("Error preparing statement for fetching paid loans: " . mysqli_error($conn));
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan History - FarmHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        /* Custom Tailwind Colors for consistency */
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;
            --bg-farm-header: #166534;
            --text-farm-dark: #065f46;
            --border-farm-green: #86efac;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            background-color: #AEEA94; /* Light Green */
            padding: 30px;
            border-radius: 10px;
            max-width: 900px; /* Wider container for tables */
            width: 100%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: var(--farm-dark);
            margin-bottom: 30px;
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: var(--farm-dark); /* Dark green header */
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Subtle zebra striping */
        }
        tr:hover {
            background-color: #e6ffe6; /* Light green on hover */
        }
        .message {
            background-color: var(--farm-light);
            color: var(--text-farm-dark);
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
            border: 1px solid var(--border-farm-green);
        }
        .back-link {
            display: inline-block;
            background-color: var(--farm-green);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 30px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #1a9e4e; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 Your Loan History</h1>

        <?php if (!empty($paid_loans)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Original Amount</th> <!-- Updated header -->
                        <th>Reason</th>
                        <th>Applied Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paid_loans as $loan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loan['l_id']); ?></td>
                            <td>৳<?php echo number_format($loan['original_loan_amt'], 2); ?></td> <!-- Displaying original_loan_amt -->
                            <td><?php echo htmlspecialchars($loan['l_reason']); ?></td>
                            <td><?php echo htmlspecialchars($loan['a_date']); ?></td>
                            <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                            <td><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="message">No paid loan history found for your account yet.</p>
        <?php endif; ?>

        <a href="userdashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>

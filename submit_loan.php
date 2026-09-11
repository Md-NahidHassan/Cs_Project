<?php
// submit_loan.php
session_start();

// 1) Must be logged in
if (empty($_SESSION['u_id'])) {
    die('<p style="color:red;">Please log in to submit a loan application.</p>');
}
$u_id = (int)$_SESSION['u_id']; // Get the user ID from the session

// 2) Connect to database
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

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
$stmt->bind_result($role, $fetched_f_id); // Bind to a temporary variable
if ($stmt->fetch()) {
    $f_id = (int)$fetched_f_id; // Assign to f_id
}
$stmt->close();

if (strcasecmp($role, 'farmer') !== 0 || $f_id <= 0) {
    die('<p style="color:red;">Only registered farmers can submit loan applications. Please ensure your profile is complete.</p>');
}


// 4) Collect and validate POST data
// f_id is now retrieved from the database, not directly from POST
$l_reason = isset($_POST['l_reason']) ? trim($_POST['l_reason']) : '';
$loan_amt = isset($_POST['loan_amt']) ? (float) $_POST['loan_amt'] : 0; // This is the original requested amount

if ($l_reason === '' || $loan_amt <= 0) {
    die('<p style="color:red;">Invalid loan amount or reason. Please go back and try again.</p>');
}

// 5) Insert into loan table
// IMPORTANT: Added 'original_loan_amt' to the column list and a placeholder '?'
$stmt = $conn->prepare("
    INSERT INTO loan
      (f_id, loan_amt, original_loan_amt, i_rate, l_reason, a_date, due_date, status)
    VALUES
      (?, ?, ?, 0, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'active')
");
// 'idds' corresponds to f_id (int), loan_amt (double), original_loan_amt (double), l_reason (string)
$stmt->bind_param('idds', $f_id, $loan_amt, $loan_amt, $l_reason); // loan_amt is inserted into both columns

if (! $stmt->execute()) {
    die('<p style="color:red;">Error creating loan: ' . htmlspecialchars($stmt->error) . '</p>');
}

// 6) Get the new loan ID
$l_id = $stmt->insert_id;
$stmt->close();

// 7) Calculate 3 installments: 30%, 30%, 40%
$inst1_amt = round($loan_amt * 0.30, 2);
$inst2_amt = round($loan_amt * 0.30, 2);
$inst3_amt = round($loan_amt * 0.40, 2);

$due1 = date('Y-m-d', strtotime('+1 month'));
$due2 = date('Y-m-d', strtotime('+2 month'));
$due3 = date('Y-m-d', strtotime('+3 month'));

$installments = [
    ['due_date' => $due1, 'amount_due' => $inst1_amt],
    ['due_date' => $due2, 'amount_due' => $inst2_amt],
    ['due_date' => $due3, 'amount_due' => $inst3_amt],
];

// 8) Insert installments into loan_installments table
$insStmt = $conn->prepare("
    INSERT INTO loan_installments
      (l_id, due_date, amount_due, amount_paid, paid_date, late_fee)
    VALUES
      (?, ?, ?, 0, NULL, 0)
");
foreach ($installments as $inst) {
    $insStmt->bind_param(
        'isd', // l_id (int), due_date (string), amount_due (double)
        $l_id,
        $inst['due_date'],
        $inst['amount_due']
    );
    if (! $insStmt->execute()) {
        die('<p style="color:red;">Error scheduling installments: ' . htmlspecialchars($insStmt->error) . '</p>');
    }
}
$insStmt->close();

// 9) Display success message
echo "<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <title>Loan Submitted</title>
  <link rel='stylesheet' href='loan.css'>
  <!-- Re-using Tailwind colors for consistency -->
  <script src='https://cdn.tailwindcss.com'></script>
  <style>
      /* Re-define custom Tailwind colors for this success page */
      :root {
          --farm-green: #22c55e;
          --farm-dark: #166534;
          --farm-light: #dcfce7;
      }
      body {
          font-family: Arial, sans-serif;
          background-color: #f3f4f6; /* Light gray background */
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          margin: 0;
      }
      .success-box {
          background-color: var(--farm-light);
          color: var(--farm-dark);
          padding: 2rem;
          border-radius: 0.75rem;
          max-width: 500px;
          margin: 2rem auto;
          text-align: center;
          box-shadow: 0 4px 8px rgba(0,0,0,0.1);
          border: 1px solid var(--farm-green);
      }
      .success-msg {
          font-size: 1.5rem;
          font-weight: bold;
          margin-bottom: 1rem;
      }
      .schedule-intro {
          font-size: 1.1rem;
          margin-top: 1.5rem;
          margin-bottom: 0.5rem;
      }
      .schedule-list {
          list-style: none;
          padding: 0;
          margin: 0.5rem 0 1.5rem 0;
      }
      .schedule-list li {
          margin-bottom: 0.5rem;
          font-size: 1rem;
          color: #555;
      }
      .back-link {
          display: inline-block;
          background-color: var(--farm-green);
          color: white;
          padding: 0.6rem 1.2rem;
          border-radius: 0.375rem;
          text-decoration: none;
          font-weight: bold;
          transition: background-color 0.3s ease;
      }
      .back-link:hover {
          background-color: #1a9e4e;
      }
  </style>
</head>
<body>
  <div class='success-box'>
    <p class='success-msg'>✅ Your loan application for ৳" . number_format($loan_amt, 2) . " has been submitted.</p>
    <p class='schedule-intro'>3 installments have been scheduled on:</p>
    <ul class='schedule-list'>
      <li>{$due1}: ৳" . number_format($inst1_amt, 2) . "</li>
      <li>{$due2}: ৳" . number_format($inst2_amt, 2) . "</li>
      <li>{$due3}: ৳" . number_format($inst3_amt, 2) . "</li>
    </ul>
    <p><a class='back-link' href='take_loan.php'>Back to Loan Center</a></p>
  </div>
</body>
</html>";

mysqli_close($conn); // Close connection after everything is done
?>

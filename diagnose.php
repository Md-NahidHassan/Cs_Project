<?php
session_start();

/*
  ✅ JS injection prevention:
  - Any output to HTML uses htmlspecialchars(..., ENT_QUOTES, 'UTF-8')
  - Checkbox values/labels and title are escaped

  ✅ SQL injection prevention:
  - ALL queries use prepared statements (no variables inside SQL strings)
  - Removed raw "$fc_id", "$crop_id", "$type_id" inside SQL

  ✅ CSRF:
  - Added token generation + validation (same style as your signup/login)
  - Added hidden csrf_token field in the form
*/

$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));

if (!isset($_SESSION['user_name'])) die("Unauthorized");

// ---- CSRF status messages ----
$csrf_error = "";
$csrf_success = "";
$max_time = 60 * 60 * 24; // 24 hours

// ---- CSRF VALIDATION (runs on POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time'])
    ) {
        $csrf_error = "CSRF token missing.";
    } else {
        if (($_SESSION['csrf_token_time'] + $max_time) < time()) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            $csrf_error = "CSRF token expired.";
        } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $csrf_error = "CSRF token invalid.";
        }
    }

    if ($csrf_error === "") {
        $csrf_success = "CSRF token successful. Proceed to next step.";
    }
}

// ---- Generate new CSRF token for the form ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

// ---- Get fc_id safely ----
$fc_id = isset($_GET['fc_id']) ? (int)$_GET['fc_id'] : 0;
if ($fc_id <= 0) die("No crop ID provided.");

// ---- Fetch crop row using prepared statement ----
$sql = "
    SELECT fc.*, c.c_name, ct.type_name
    FROM farmer_crop fc
    JOIN crop c ON fc.crop_id = c.crop_id
    JOIN crop_type ct ON fc.type_id = ct.type_id
    WHERE fc.fc_id = ?
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) die("Query preparation failed.");
mysqli_stmt_bind_param($stmt, "i", $fc_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$crop = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$crop) die("Crop not found.");

// include navbar (keep as is)
include("navbar.php");

// ---- Get symptom keywords (using prepared statements) ----
// Option A: all symptoms from the entire table (your final UI does this)
$symptom_keywords = [];
$stmt = mysqli_prepare($conn, "SELECT symptoms FROM disease_info");
if (!$stmt) die("Query preparation failed.");
mysqli_stmt_execute($stmt);
$symptom_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($symptom_result)) {
    $parts = explode(',', (string)$row['symptoms']);
    foreach ($parts as $sym) {
        $trimmed = strtolower(trim($sym));
        if ($trimmed !== '' && !in_array($trimmed, $symptom_keywords, true)) {
            $symptom_keywords[] = $trimmed;
        }
    }
}
mysqli_stmt_close($stmt);

sort($symptom_keywords);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Diagnose Crop</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-green-50 p-8">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-green-700">
      Diagnose:
      <?= htmlspecialchars($crop['c_name'], ENT_QUOTES, 'UTF-8'); ?>
      (<?= htmlspecialchars($crop['type_name'], ENT_QUOTES, 'UTF-8'); ?>)
    </h2>

    <!-- CSRF messages in body -->
    <?php if ($csrf_error !== ""): ?>
      <div class="mb-4 text-red-700 font-semibold bg-red-100 p-2 rounded">
        <?= htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="mb-4 text-green-700 font-semibold bg-green-100 p-2 rounded">
        <?= htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form action="diagnose_result.php" method="POST" class="space-y-4">
      <!-- CSRF token -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <input type="hidden" name="fc_id" value="<?= (int)$fc_id; ?>">
      <input type="hidden" name="crop_id" value="<?= (int)$crop['crop_id']; ?>">
      <input type="hidden" name="type_id" value="<?= (int)$crop['type_id']; ?>">

      <div>
        <label class="block font-semibold mb-2">Select Symptoms:</label>
        <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-scroll border p-3 rounded bg-gray-50">
          <?php foreach ($symptom_keywords as $keyword): ?>
            <?php
              $safeKeyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); // ✅ JS/HTML injection safe output
              $id = 'sym_' . md5($keyword);
            ?>
            <div class="flex items-center">
              <input
                type="checkbox"
                name="symptoms[]"
                value="<?= $safeKeyword; ?>"
                id="<?= $id; ?>"
                class="mr-2 accent-green-600"
              >
              <label for="<?= $id; ?>"><?= htmlspecialchars(ucfirst($keyword), ENT_QUOTES, 'UTF-8'); ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <label class="block font-semibold">Duration (in days):</label>
        <input type="number" name="days" required class="w-full border rounded p-2" min="1">
      </div>

      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Diagnose
      </button>
    </form>

    <div class="mt-4">
      <a href="dashboard.php" class="text-blue-600 hover:underline">🔙 Back to Dashboard</a>
    </div>
  </div>
</body>
</html>

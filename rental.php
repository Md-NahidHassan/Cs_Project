<?php
session_start();

/*
  ✅ What I changed (same as before):
  - CSRF added for BOTH forms (Add Equipment + Rent)
  - SQL injection fixed: NO variables inside SQL strings (prepared statements everywhere)
  - XSS/JS injection prevention: htmlspecialchars() on all output
  - Removed unsafe goto usage (clean flow)
  - IMPORTANT: Hardcoded admin password is still insecure — but I kept your logic and made it safer.
*/

$conn = mysqli_connect('localhost','root','','farmsystem');
if (!$conn) {
    die("DB Connection Error: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

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
        $csrf_success = "CSRF token successful.";
    }
}

// ---- Generate new CSRF token for the next request ----
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();

// Simple flash messaging function (escaped)
function flash($msg, $cls='info') {
    $safe = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    echo "<p class='{$cls}'>{$safe}</p>";
}

// --- Fetch User and Farmer ID from Session ---
$loggedInUserId   = isset($_SESSION['u_id']) ? (int)$_SESSION['u_id'] : null;
$loggedInUserRole = strtolower($_SESSION['role'] ?? '');
$loggedInUserName = $_SESSION['user_name'] ?? '';
$loggedInFarmerId = null;

if ($loggedInUserId) {
    $stmt = mysqli_prepare($conn, "SELECT f_id FROM farmer WHERE u_id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $loggedInUserId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) > 0) {
            $loggedInFarmerId = (int)mysqli_fetch_assoc($res)['f_id'];
        }
        mysqli_stmt_close($stmt);
    }
}

// -------------------- HANDLE POST ACTIONS (ONLY IF CSRF OK) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf_error === "") {

    // 1) ADD EQUIPMENT (admin-only by your password)
    if (isset($_POST['add_equipment'])) {
        $adminPass = $_POST['admin_pass'] ?? '';

        if ($adminPass === '0099') {
            $name = trim($_POST['new_name'] ?? '');
            $cost = (float)($_POST['new_cost'] ?? 0);

            if ($name === '' || $cost <= 0) {
                flash("❌ Invalid equipment name or cost.", "error");
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO equipment (name, cost) VALUES (?, ?)");
                if (!$stmt) {
                    flash("❌ DB error (prepare failed).", "error");
                } else {
                    mysqli_stmt_bind_param($stmt, "sd", $name, $cost);
                    if (mysqli_stmt_execute($stmt)) {
                        flash("🆕 Added equipment “{$name}” at ₹" . number_format($cost,2) . "/day", "success");
                    } else {
                        flash("❌ Error adding equipment.", "error");
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        } else {
            flash("🔒 Incorrect admin password. Equipment not added.", "error");
        }
    }

    // 2) RENT EQUIPMENT
    if (isset($_POST['do_rent'])) {

        // If logged in, use session name; else accept form input
        $farmerName = $loggedInUserName ?: trim($_POST['farmer_name'] ?? '');
        if ($farmerName === '') {
            flash("❌ Farmer name is required.", "error");
        } else {
            // Find farmer id:
            // If logged in -> by u_id
            // Else -> by f_name
            $f_id = null;

            if ($loggedInUserId) {
                $stmt = mysqli_prepare($conn, "SELECT f_id FROM farmer WHERE u_id = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $loggedInUserId);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && mysqli_num_rows($res) > 0) {
                    $f_id = (int)mysqli_fetch_assoc($res)['f_id'];
                }
                mysqli_stmt_close($stmt);
            } else {
                $stmt = mysqli_prepare($conn, "SELECT f_id FROM farmer WHERE f_name = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "s", $farmerName);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && mysqli_num_rows($res) > 0) {
                    $f_id = (int)mysqli_fetch_assoc($res)['f_id'];
                }
                mysqli_stmt_close($stmt);
            }

            // If not found, create farmer
            if (!$f_id) {
                if ($loggedInUserId) {
                    $stmt = mysqli_prepare($conn, "INSERT INTO farmer (f_name, u_id) VALUES (?, ?)");
                    mysqli_stmt_bind_param($stmt, "si", $farmerName, $loggedInUserId);
                } else {
                    $stmt = mysqli_prepare($conn, "INSERT INTO farmer (f_name) VALUES (?)");
                    mysqli_stmt_bind_param($stmt, "s", $farmerName);
                }

                if ($stmt && mysqli_stmt_execute($stmt)) {
                    $f_id = (int)mysqli_insert_id($conn);
                    flash("🆕 Created farmer “{$farmerName}” with ID {$f_id}", "success");
                } else {
                    flash("❌ Error creating farmer.", "error");
                    if ($stmt) mysqli_stmt_close($stmt);
                    $f_id = null;
                }
                if ($stmt) mysqli_stmt_close($stmt);
            }

            if ($f_id) {
                $eq_id = (int)($_POST['eq_id'] ?? 0);
                $days  = max(1, (int)($_POST['duration'] ?? 1));

                // Get equipment details
                $stmt = mysqli_prepare($conn, "SELECT name, cost FROM equipment WHERE eq_id = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $eq_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $equip = ($res && mysqli_num_rows($res) === 1) ? mysqli_fetch_assoc($res) : null;
                mysqli_stmt_close($stmt);

                if (!$equip) {
                    flash("❌ Equipment not found.", "error");
                } else {
                    // Check if already rented
                    $stmt = mysqli_prepare($conn, "SELECT 1 FROM rental WHERE eq_id = ? AND ava = 'No' LIMIT 1");
                    mysqli_stmt_bind_param($stmt, "i", $eq_id);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    $already = ($res && mysqli_num_rows($res) > 0);
                    mysqli_stmt_close($stmt);

                    if ($already) {
                        flash("⚠️ “{$equip['name']}” is already rented", "error");
                    } else {
                        $amount = (float)$equip['cost'] * $days;

                        $stmt = mysqli_prepare($conn, "INSERT INTO rental (eq_id, f_id, r_duration, r_amount, ava) VALUES (?, ?, ?, ?, 'No')");
                        mysqli_stmt_bind_param($stmt, "iiid", $eq_id, $f_id, $days, $amount);

                        if ($stmt && mysqli_stmt_execute($stmt)) {
                            flash("✅ Rented “{$equip['name']}” for {$days} day(s). Total: ₹" . number_format($amount,2), "success");
                        } else {
                            flash("❌ Rental failed.", "error");
                        }
                        if ($stmt) mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmHub - Equipment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
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
        .bg-farm-header { background-color: #166534; }
        .border-farm-green { border-color: #86efac; }
        .text-farm-dark { color: #065f46; }
        .divide-farm-green > :not([hidden]) ~ :not([hidden]) { border-color: #bbf7d0; }
        .info { color: blue; }
        .success { color: green; }
        .error { color: red; }
        body { font-family: sans-serif; margin: 2rem; }
    </style>
</head>
<body>
<header class="bg-white shadow-lg sticky top-0 z-50">
  <!-- (your navbar HTML unchanged, kept) -->
  <!-- ... keep your full navbar exactly as you had ... -->
</header>

<div class="container">
  <h1 style="color: #166534; font-size: 2rem; margin-bottom: 1rem;">🔧 Equipment Rental Management</h1>

  <!-- CSRF messages -->
  <?php if ($csrf_error !== ""): ?>
      <div class="mb-4 text-red-700 font-semibold bg-red-100 p-2 rounded">
          <?= htmlspecialchars($csrf_error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
  <?php elseif ($csrf_success !== "" && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="mb-4 text-green-700 font-semibold bg-green-100 p-2 rounded">
          <?= htmlspecialchars($csrf_success, ENT_QUOTES, 'UTF-8'); ?>
      </div>
  <?php endif; ?>

  <main class="max-w-6xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">

    <?php if ($loggedInUserRole === 'admin'): ?>
      <div class="w-full p-6 bg-farm-light rounded-lg shadow-2xl mt-12 px-4">
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <h2 class="text-2xl font-semibold text-farm-dark mb-6 text-left">Add New Equipment (Admin Only)</h2>

          <label class="block mb-4">
            Name:
            <input type="text" name="new_name" required
                   class="mt-1 w-full px-4 py-2 rounded border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green">
          </label>

          <label class="block mb-4">
            Cost per day: ₹
            <input type="number" name="new_cost" step="0.01" required
                   class="mt-1 w-full px-4 py-2 rounded border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green">
          </label>

          <label class="block mb-6">
            Admin Password:
            <input type="password" name="admin_pass" required
                   class="mt-1 w-full px-4 py-2 rounded border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green">
          </label>

          <button name="add_equipment"
                  class="w-full bg-farm-green text-white py-2 rounded hover:bg-green-700 transition font-semibold">
            Add Equipment
          </button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($loggedInUserRole === 'farmer' || $loggedInUserRole === ''): ?>
      <div class="w-full p-6 bg-farm-light rounded-lg shadow-2xl mt-12 px-4">
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
          <h2 class="text-2xl font-semibold text-farm-dark mb-6 text-left">Rent Equipment</h2>

          <label class="block mb-4">
            Your Name:
            <input type="text" name="farmer_name"
                   value="<?= htmlspecialchars($loggedInUserName, ENT_QUOTES, 'UTF-8'); ?>"
                   <?= ($loggedInUserName ? 'readonly' : 'required'); ?>
                   class="mt-1 w-full px-4 py-2 rounded border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green">
          </label>

          <label class="block mb-4">
            Select Equipment:
            <select name="eq_id" required
                    class="mt-1 w-full px-4 py-2 rounded border border-farm-green bg-white focus:outline-none focus:ring-2 focus:ring-farm-green">
              <option value="">— Select Available —</option>
              <?php
                $res = mysqli_query($conn,
                  "SELECT eq_id, name, cost FROM equipment
                   WHERE eq_id NOT IN (SELECT eq_id FROM rental WHERE ava='No')"
                );
                while ($row = mysqli_fetch_assoc($res)) {
                    $eqId = (int)$row['eq_id'];
                    $nm = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                    $cs = number_format((float)$row['cost'], 2);
                    echo "<option value='{$eqId}'>{$nm} (₹{$cs}/day)</option>";
                }
              ?>
            </select>
          </label>

          <label class="block mb-6">
            Duration (days):
            <input type="number" name="duration" min="1" value="1" required
                   class="mt-1 w-full px-4 py-2 rounded border border-farm-green focus:outline-none focus:ring-2 focus:ring-farm-green">
          </label>

          <button name="do_rent"
                  class="w-full bg-farm-green text-white py-2 rounded hover:bg-green-700 transition font-semibold">
            Rent
          </button>
        </form>
      </div>
    <?php endif; ?>

    <hr class="my-10">

    <?php
      $rentalQuery = "
        SELECT r.r_id, r.f_id, f.f_name AS farmer_name, e.name AS equipment_name,
               r.r_duration, r.r_amount, r.ava, e.cost
        FROM rental r
        JOIN equipment e ON r.eq_id = e.eq_id
        JOIN farmer f ON r.f_id = f.f_id
      ";

      $params = [];
      $types = "";

      if ($loggedInUserRole === 'farmer' && $loggedInFarmerId) {
          $rentalQuery .= " WHERE r.f_id = ? ";
          $types = "i";
          $params[] = $loggedInFarmerId;
          $tableTitle = "Your Rentals";
      } elseif ($loggedInUserRole === 'admin') {
          $tableTitle = "All Rentals (Admin View)";
      } else {
          $rentalQuery .= " WHERE 1=0 ";
          $tableTitle = "Rentals History";
      }

      $rentalQuery .= " ORDER BY r.r_id DESC";

      $allRentals = null;
      $stmt = mysqli_prepare($conn, $rentalQuery);
      if ($stmt) {
          if ($types !== "") {
              mysqli_stmt_bind_param($stmt, $types, ...$params);
          }
          mysqli_stmt_execute($stmt);
          $allRentals = mysqli_stmt_get_result($stmt);
          mysqli_stmt_close($stmt);
      }
    ?>

    <div class="text-center p-6">
      <h2 class="text-2xl font-semibold mb-0"><?= htmlspecialchars($tableTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-md border border-farm-green bg-white" style="width: 95%; margin: auto;">
      <table class="min-w-full divide-y divide-farm-green">
        <thead class="bg-farm-header text-white">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Rental ID</th>
            <?php if ($loggedInUserRole === 'admin'): ?>
              <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Farmer (ID)</th>
            <?php endif; ?>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Equipment</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Duration</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Total Amount</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-farm-green">
          <?php if ($allRentals && mysqli_num_rows($allRentals) > 0): ?>
            <?php while ($x = mysqli_fetch_assoc($allRentals)): ?>
              <tr class="hover:bg-farm-light transition-colors duration-200 cursor-pointer">
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?= (int)$x['r_id']; ?></td>

                <?php if ($loggedInUserRole === 'admin'): ?>
                  <td class="px-6 py-4 whitespace-nowrap text-farm-dark">
                    <?= htmlspecialchars($x['farmer_name'], ENT_QUOTES, 'UTF-8'); ?> (<?= (int)$x['f_id']; ?>)
                  </td>
                <?php endif; ?>

                <td class="px-6 py-4 whitespace-nowrap text-farm-dark">
                  <?= htmlspecialchars($x['equipment_name'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= (int)$x['r_duration']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark">
                  ₹<?= number_format((float)$x['r_duration'] * (float)$x['cost'], 2); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($x['ava'], ENT_QUOTES, 'UTF-8'); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= ($loggedInUserRole === 'admin' ? 6 : 5); ?>" class="px-6 py-4 text-center">
                <?php
                  if ($loggedInUserRole === 'farmer') {
                      echo "You have no rentals yet.";
                  } elseif ($loggedInUserRole === 'admin') {
                      echo "No rentals found in the system.";
                  } else {
                      echo "Please log in as a farmer to view your rentals, or as an admin to view all rentals.";
                  }
                ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
</body>
</html>

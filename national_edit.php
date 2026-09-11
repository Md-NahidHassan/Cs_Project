<?php
session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: nationalprice.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "farmsystem");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

$n_id = (int)($_GET['n_id'] ?? 0);
$admin_id = $_SESSION['u_id'];

// Fetch old data
$old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM national_price WHERE n_id = $n_id"));
if (!$old) {
    echo "Record not found.";
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_name = $_POST['crop_name'];
    $type = $_POST['type'];
    $national_price = $_POST['national_price'];
    $update_time = $_POST['update_time'];
    $status = $_POST['status'];
    $country_name = $_POST['country_name'];

    // Update national_price
    $stmt = $conn->prepare("UPDATE national_price SET crop_name=?, type=?, national_price=?, update_time=?, status=?, country_name=? WHERE n_id=?");
    $stmt->bind_param("ssdsssi", $crop_name, $type, $national_price, $update_time, $status, $country_name, $n_id);

    if ($stmt->execute()) {
        // Log if both price and date changed
        $priceChanged = ($old['national_price'] != $national_price);
        $timeChanged = ($old['update_time'] !== $update_time);
        if ($priceChanged && $timeChanged) {
            $stmt3 = $conn->prepare("INSERT INTO prev_mp (u_id, crop_name, type, old_price, update_time, status, country_name, source_table, changed_at)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, 'national_price', NOW())");
            $stmt3->bind_param("issdsss", $admin_id, $old['crop_name'], $old['type'], $national_price, $update_time, $old['status'], $old['country_name']);
            $stmt3->execute();
            $stmt3->close();
        }

        header("Location: nationalprice.php");
        exit();
    } else {
        $error = "Database error: " . $stmt->error;
    }

    $stmt->close();
}

mysqli_close($conn);
?>

<?php include("navbar.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit National Price</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0fdf4;
      padding: 20px;
    }
    .container {
      max-width: 40rem;
      margin: 2.5rem auto;
      background-color: #fff;
      border: 1px solid #166534;
      border-radius: 0.5rem;
      padding: 2rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #14532d;
      font-size: 1.75rem;
      margin-bottom: 1.5rem;
    }
    label {
      font-weight: 600;
      color: #14532d;
    }
    input[type="text"],
    input[type="number"],
    input[type="date"] {
      width: 100%;
      padding: 0.5rem 1rem;
      border: 1px solid #ccc;
      border-radius: 0.375rem;
      margin-bottom: 1rem;
    }
    button {
      width: 100%;
      background-color: #166534;
      color: white;
      padding: 0.75rem;
      border: none;
      font-weight: bold;
      font-size: 1.1rem;
      border-radius: 0.375rem;
      cursor: pointer;
    }
    button:hover {
      background-color: #14532d;
    }
    .error {
      background: #f87171;
      color: white;
      padding: 1rem;
      text-align: center;
      border-radius: 0.375rem;
      margin-bottom: 1rem;
      font-weight: bold;
    }
    .back {
      display: block;
      text-align: center;
      margin-top: 1rem;
      text-decoration: none;
      font-weight: 600;
      color: white;
      background: gray;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>✏️ Edit National Price</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="crop_name">Crop Name</label>
    <input type="text" id="crop_name" name="crop_name" value="<?= htmlspecialchars($old['crop_name']) ?>" required>

    <label for="type">Type</label>
    <input type="text" id="type" name="type" value="<?= htmlspecialchars($old['type']) ?>" required>

    <label for="national_price">National Price</label>
    <input type="number" step="0.01" id="national_price" name="national_price" value="<?= htmlspecialchars($old['national_price']) ?>" required>

    <label for="update_time">Update Date</label>
    <input type="date" id="update_time" name="update_time" value="<?= htmlspecialchars($old['update_time']) ?>" required>

    <label for="status">Status</label>
    <input type="text" id="status" name="status" value="<?= htmlspecialchars($old['status']) ?>" required>

    <label for="country_name">Country Name</label>
    <input type="text" id="country_name" name="country_name" value="<?= htmlspecialchars($old['country_name']) ?>" required>

    <button type="submit">💾 Update Record</button>
  </form>

  <a class="back" href="nationalprice.php">⬅️ Go Back</a>
</div>

</body>
</html>





<script>
    // Simple scroll effect for navbar
    window.addEventListener("scroll", function () {
      const header = document.querySelector("header");
      if (window.scrollY > 100) {
        header.classList.add("shadow-2xl");
      } else {
        header.classList.remove("shadow-2xl");
      }
    });

    // Mobile menu toggle (you can expand this)
    function toggleMobileMenu() {
      // Add mobile menu functionality here
      console.log("Mobile menu toggled");
    }
  </script>
<footer class="bg-gray-900 py-6 mt-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div
        class="flex flex-col md:flex-row justify-between items-center text-white"
      >
        <div class="text-2xl font-bold text-farm-green mb-4 md:mb-0">
          FarmHub
        </div>
        <div class="space-x-6 text-sm opacity-75">
          <a href="about.php" class="hover:text-farm-green transition-colors"
            >About Us</a
          >
          <a href="contact.php" class="hover:text-farm-green transition-colors"
            >Contact</a
          >
          <a
            href="privacy.php"
            class="hover:text-farm-green transition-colors"
            >Privacy Policy</a
          >
          <a
            href="terms.php"
            class="hover:text-farm-green transition-colors"
            >Terms & Conditions</a
          >
        </div>
      </div>
    </div>
  </footer>
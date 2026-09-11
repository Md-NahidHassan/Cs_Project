<?php 
session_start();
include("navbar.php");

// Admin Welcome (Minimal top spacing, inside container)
ob_start();
$adminWelcome = '';
if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
    $adminWelcome = '<div style="
        text-align: center;
        margin-bottom: 16px;
        padding: 10px 20px;
        background-color: #dcfce7;
        color: #065f46;
        font-size: 18px;
        font-weight: 600;
        border: 1px solid #86efac;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(6, 95, 70, 0.1);
    ">👋 Welcome, Admin ' . htmlspecialchars($_SESSION['user_name']) . '</div>';
}

// Success Message
$msgBox = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msgBox = '<div style="
        margin: 10px auto;
        max-width: 600px;
        padding: 10px 16px;
        background-color: #d1fae5;
        color: #065f46;
        border-radius: 6px;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 6px rgba(6, 95, 70, 0.3);
    ">✔️ Entry deleted successfully.</div>';
}

// DB Connection
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die('Connection error: ' . mysqli_connect_error());

// Filters
$cropName = $_GET['crop_name'] ?? '';
$status = $_GET['status'] ?? '';
$country = $_GET['country_name'] ?? '';

// WHERE conditions
$where = [];
if (!empty($cropName)) $where[] = "np.crop_name LIKE '%" . mysqli_real_escape_string($conn, $cropName) . "%'";
if (!empty($status)) $where[] = "np.status = '" . mysqli_real_escape_string($conn, $status) . "'";
if (!empty($country)) $where[] = "np.country_name LIKE '%" . mysqli_real_escape_string($conn, $country) . "%'";

// Main query
$sql = "SELECT np.*, u.u_name, u.role
        FROM national_price np
        JOIN user u ON np.u_id = u.u_id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY np.n_id";

$result = mysqli_query($conn, $sql);
$marketData = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Dropdown data
$crops = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT crop_name FROM national_price"), MYSQLI_ASSOC);
$statuses = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT status FROM national_price"), MYSQLI_ASSOC);
$countries = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT country_name FROM national_price"), MYSQLI_ASSOC);

mysqli_close($conn);

// Exchange rates
$exchangeRates = [
    'USA' => 110.0, 'India' => 1.2, 'China' => 15.5, 'Bangladesh' => 1.0, 'Thailand' => 3.1,
    'Saudi Arabia' => 29.3, 'UAE' => 30.5, 'Oman' => 28.9, 'Qatar' => 30.2, 'Kuwait' => 32.8,
    'Germany' => 110.0, 'UK' => 110.0, 'Netherlands' => 110.0, 'France' => 110.0,
    'Canada' => 112.0, 'Malaysia' => 26.5, 'Singapore' => 27.3,
];
?>

<!-- 🟩 Begin main white container -->
<div style="max-width: 1200px; margin: 20px auto; padding: 24px; background-color: white; border-radius: 12px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);">

  <?= $adminWelcome ?>
  <?= $msgBox ?>

  <!-- Title -->
  <h1 style="color: #166534; font-size: 28px; font-weight: 700; margin-bottom: 16px; text-align: center;">
    🌐 National Price Listing
  </h1>

  <!-- Filter Form -->
  <form method="GET" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px; box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1); margin: 0 auto 20px auto; display: flex; flex-wrap: wrap; gap: 16px; justify-content: center;">
    <div>
      <label style="font-weight: 600; color: #166534;">Crop:</label><br>
      <select name="crop_name" style="padding: 8px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7;">
        <option value="">--Select Crop--</option>
        <?php foreach ($crops as $crop): ?>
          <option value="<?= htmlspecialchars($crop['crop_name']) ?>" <?= $crop['crop_name'] === $cropName ? 'selected' : '' ?>>
            <?= htmlspecialchars($crop['crop_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-weight: 600; color: #166534;">Country:</label><br>
      <select name="country_name" style="padding: 8px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7;">
        <option value="">--Select Country--</option>
        <?php foreach ($countries as $c): ?>
          <option value="<?= htmlspecialchars($c['country_name']) ?>" <?= $c['country_name'] === $country ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['country_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-weight: 600; color: #166534;">Status:</label><br>
      <select name="status" style="padding: 8px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7;">
        <option value="">--Select Status--</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= htmlspecialchars($s['status']) ?>" <?= $s['status'] === $status ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['status']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="align-self: flex-end;">
      <button type="submit" style="padding: 8px 16px; background-color: #34d399; color: white; font-weight: 600; border: none; border-radius: 6px;">
        🔍 Filter
      </button>
    </div>
  </form>

  <!-- Add Button -->
  <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
    <div style="text-align: center; margin-bottom: 16px;">
      <a href="national_add.php" style="padding: 10px 20px; background-color: #059669; color: white; font-weight: 600; text-decoration: none; border-radius: 6px;">➕ Add New</a>
    </div>
  <?php endif; ?>


  <div style="display: flex; justify-content: center; margin-top: 20px;">
  <div style="width: 85%; max-width: 1100px;">

   <!-- Back Button INSIDE container -->
    <div style="text-align: right; margin-bottom: 10px;">
      <a href="mpman.php" style="background-color: #6b7280; color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;" 
         onmouseover="this.style.backgroundColor='#4b5563'" onmouseout="this.style.backgroundColor='#6b7280'">
        ← Go Back
      </a>
    </div>


   <!-- Table -->

    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background-color: #166534; color: white;">
        <tr>
          <th class="px-6 py-3">Crop Name</th>
          <th class="px-6 py-3">Type</th>
          <th class="px-6 py-3">Country</th>
          <th class="px-6 py-3">National Price</th>
          <th class="px-6 py-3">BD Price</th>
          <th class="px-6 py-3">Status</th>
          <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
            <th class="px-6 py-3">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody style="background-color: #ffffff;">
        <?php if ($marketData): ?>
          <?php foreach ($marketData as $row): 
            $rate = $exchangeRates[$row['country_name']] ?? 1;
            $bd_price = $row['national_price'] * $rate;
          ?>
            <tr>
              <td class="px-6 py-4"><?= htmlspecialchars($row['crop_name']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['type']) ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['country_name']) ?></td>
              <td class="px-6 py-4"><?= number_format($row['national_price'], 2) ?></td>
              <td class="px-6 py-4"><?= number_format($bd_price, 2) ?> ৳</td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['status']) ?></td>
              <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                <td class="px-6 py-4">
                  <a href="national_edit.php?n_id=<?= $row['n_id'] ?>" style="color: #2563eb; font-weight: 600; margin-right: 12px;">Edit</a>
                  <a href="national_delete.php?n_id=<?= $row['n_id'] ?>" onclick="return confirm('Delete this entry?');" style="color: #dc2626; font-weight: 600;">Delete</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" style="padding: 20px; text-align: center;">No national price data found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


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
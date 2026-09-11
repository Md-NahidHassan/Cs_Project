<?php 
session_start();
include("navbar.php");  // keep your navbar



// Success message on deletion
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    echo '<div style="
            margin: 20px auto;
            max-width: 600px;
            padding: 10px 16px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 2px 6px rgba(6, 95, 70, 0.3);
          ">
          ✔️ Entry deleted successfully.
          </div>';
}


// DB connection
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection error: ' . mysqli_connect_error());
}

// Get filters safely
$cropName = isset($_GET['crop_name']) ? mysqli_real_escape_string($conn, $_GET['crop_name']) : '';
$region = isset($_GET['region']) ? mysqli_real_escape_string($conn, $_GET['region']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build WHERE conditions
$whereConditions = [];
if (!empty($cropName)) $whereConditions[] = "local.crop_name LIKE '%$cropName%'";
if (!empty($region)) $whereConditions[] = "local.region LIKE '%$region%'";
if (!empty($status)) $whereConditions[] = "local.status = '$status'";

// Query to fetch local price data
$sql = "SELECT local.l_id, local.crop_name, local.type, local.local_price, local.region, local.status, local.update_time,
        usr.u_name, usr.role
        FROM local_price local
        JOIN user usr ON local.u_id = usr.u_id";

if (count($whereConditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}
$sql .= " ORDER BY local.l_id";

$result = mysqli_query($conn, $sql);
$marketData = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Dropdown filter data
$crops = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT crop_name FROM local_price"), MYSQLI_ASSOC);
$regions = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT region FROM local_price"), MYSQLI_ASSOC);
$statuses = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT status FROM local_price"), MYSQLI_ASSOC);

mysqli_close($conn);
?>



<!-- 🟩 Begin white container -->
<div style="
  max-width: 1200px;
  margin: 10px auto; /* reduced top margin */
  padding: 30px;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
">

  <?php
  // ✅ Clean welcome message with minimal spacing
  if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
      echo '<div style="
          text-align: center;
          margin-bottom: 20px;
          padding: 10px 20px;
          background-color: #dcfce7;
          color: #065f46;
          font-size: 18px;
          font-weight: 600;
          border: 1px solid #86efac;
          border-radius: 8px;
          box-shadow: 0 2px 6px rgba(6, 95, 70, 0.1);
      ">
          👋 Welcome, Admin ' . htmlspecialchars($_SESSION['user_name']) . '
      </div>';
  }
  ?>





<!-- Page Title -->
<h1 style="
  color: #166534;
  font-size: 28px;
  font-weight: 700;
  margin-top: 20px;  /* reduced from 40px */
  margin-bottom: 12px;  /* tighter gap */
  text-align: center;
">
  🌾 Local Price Listing
</h1>


<!-- Filter Form -->
<div style="display: flex; justify-content: center;">
  <form method="GET" style="
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 12px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(22, 101, 52, 0.1);
  margin-bottom: 16px; /* tighter spacing after filter */
  display: flex;
  flex-wrap: wrap;
  gap: 12px;  /* slightly reduced gap between filter items */
  justify-content: center;
">

    <div>
      <label style="font-weight: 600; color: #166534; margin-right: 8px;">Crop:</label>
      <select name="crop_name" style="padding: 8px 12px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7; color: #065f46; font-weight: 500;">
        <option value="">--Select Crop--</option>
        <?php foreach ($crops as $crop): ?>
          <option value="<?= htmlspecialchars($crop['crop_name']) ?>" <?= $crop['crop_name'] === $cropName ? 'selected' : '' ?>>
            <?= htmlspecialchars($crop['crop_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-weight: 600; color: #166534; margin-right: 8px;">Region:</label>
      <select name="region" style="padding: 8px 12px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7; color: #065f46; font-weight: 500;">
        <option value="">--Select Region--</option>
        <?php foreach ($regions as $r): ?>
          <option value="<?= htmlspecialchars($r['region']) ?>" <?= $r['region'] === $region ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['region']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-weight: 600; color: #166534; margin-right: 8px;">Status:</label>
      <select name="status" style="padding: 8px 12px; border: 1px solid #86efac; border-radius: 6px; background-color: #dcfce7; color: #065f46; font-weight: 500;">
        <option value="">--Select Status--</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= htmlspecialchars($s['status']) ?>" <?= $s['status'] === $status ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['status']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <button type="submit" style="padding: 8px 16px; background-color: #34d399; color: white; font-weight: 600; border: none; border-radius: 6px;">
        🔍 Filter
      </button>
    </div>
  </form>
</div>

<!-- Admin Add Button -->
<?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>

  <div style="text-align: center; margin-bottom: 12px;">  <!-- was 20px -->

    <a href="localprice_add.php" style="
      padding: 10px 20px;
      background-color: #059669;
      color: white;
      font-weight: 600;
      text-decoration: none;
      border-radius: 6px;
    ">
      ➕ Add New 
    </a>
  </div>
<?php endif; ?>

<!-- Go Back Button (inside and right-aligned) -->
<div style="display: flex; justify-content: center;">
 <div style="width: 85%; max-width: 1100px; text-align: right; margin-bottom: 10px;">  <!-- ok -->

    <a href="mpman.php" style="
      display: inline-block;
      background-color: #6b7280;  /* Tailwind's gray-500 */
      color: white;
      padding: 10px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s;
    " onmouseover="this.style.backgroundColor='#4b5563'" onmouseout="this.style.backgroundColor='#6b7280'">
      ← Go Back
    </a>
  </div>
</div>



<!-- Table Display -->
<div style="display: flex; justify-content: center; margin-top: 20px;">
  <div class="..." style="width: 85%; max-width: 1100px;">

    <table class="min-w-full divide-y divide-farm-green" style="width: 90%; border-collapse: collapse;">
      <thead style="background-color: #166534; color: white;">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Crop Name</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Type</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Region</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Local Price (৳)</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
          <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody style="background-color: #ffffff;">
        <?php if (count($marketData) > 0): ?>
          <?php foreach ($marketData as $row): ?>
            <tr class="hover:bg-farm-light transition-colors duration-200" style="cursor: pointer;">
              <td class="px-6 py-4 whitespace-nowrap text-farm-dark font-medium"><?= htmlspecialchars($row['crop_name']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['type']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['region']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= number_format($row['local_price'], 2) ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['status']) ?></td>
              <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                <td class="px-6 py-4 whitespace-nowrap">
                  <a href="localprice_edit.php?l_id=<?= $row['l_id'] ?>" style="color: #2563eb; font-weight: 600; margin-right: 12px;">Edit</a>
                  <a href="localprice_delete.php?l_id=<?= $row['l_id'] ?>" onclick="return confirm('Are you sure you want to delete this entry?');" style="color: #dc2626; font-weight: 600;">Delete</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?= (strtolower($_SESSION['role']) === 'admin') ? '6' : '5' ?>" style="padding: 20px; text-align: center; color: #555;">No local price data found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

</div>

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
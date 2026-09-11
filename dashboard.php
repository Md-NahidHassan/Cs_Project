<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (!isset($_SESSION['user_name'])) die("Unauthorized");

$username = $_SESSION['user_name'];

$query = "SELECT * FROM user WHERE u_name='$username'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

$u_id = $user['u_id'];

$query = "SELECT f_id, f_name FROM farmer WHERE u_id='$u_id'";
$result = mysqli_query($conn, $query);

$row = mysqli_fetch_assoc($result);
if (!$row) {
    die("admin /buyer cant acess");
}

$f_id = $row['f_id'];
$fname = $row['f_name'];

// your next query...
$sql = "SELECT fc.fc_id,
            fc.crop_name AS farmer_crop_name,
            fc.planted_at,
            fc.harvested_time,
            ct.type_name,
            ct.species,
            ct.price,
            c.season,
            c.expected_yield,
            c.harvest_time,
            c.c_desp,
            ct.grain_size,
            ct.grain_color,
            ct.disease,
            ct.protein_content,
            ct.strach_content
        FROM farmer_crop fc
          LEFT JOIN crop_type ct ON fc.type_id = ct.type_id
          LEFT JOIN crop c ON fc.crop_id = c.crop_id
        WHERE fc.f_id = '$f_id'";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>FarmHub - My Crops</title>
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
          "farm-header": "#2e7d32",
          "farm-pink": "#cc3366"
        },
      },
    },
  };
</script>
</head>
<body class="bg-farm-light">
  <header class="bg-white shadow-lg sticky top-0 z-50">
  <!-- Top Navigation -->
  <div class="bg-farm-dark text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center space-x-4">
          <div class="text-2xl font-bold text-farm-green">
            <i class="fas fa-seedling mr-2"></i>FarmHub
          </div>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-sm">
          <i class="fa-regular fa-map text-farm-green"></i>
          <div>
            <p class="text-xs opacity-75">Deliver to</p>
            <p class="font-semibold">Bangladesh</p>
          </div>
        </div>
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
        <div class="flex items-center space-x-6 text-sm">
          <?php if (isset($_SESSION['user_name'])) : ?>
          <div id="user-greeting">
            <p class="font-semibold">
              Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </p>
            <a
              href="logout.php"
              class="text-farm-green hover:text-green-300 transition-colors"
              >LOG OUT</a
            >
          </div>
          <?php else : ?>
          <div id="sign-in-section">
            <a
              href="signup.php"
              class="font-semibold hover:text-farm-green transition-colors"
              >Hello, Sign In</a
            ><br />
            <a
              href="login.php"
              class="text-farm-green hover:text-green-300 transition-colors"
              >LOG IN</a
            >
          </div>
          <?php endif; ?>

          <a
            href="view_cart.php"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="hidden md:inline">My cart</span>
          </a>
          <a
            href="#"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="hidden md:inline">My orders</span>
          </a>
          <a
            href="userdashboard.php"
            class="flex items-center space-x-1 hover:text-farm-green transition-colors"
          >
            <i class="fa-solid fa-user"></i>
            <span class="hidden md:inline">Profile</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Nav -->
  <nav class="bg-farm-header text-white">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
      <div class="flex items-center justify-between h-12 w-full">
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



  <div class="text-center text-farm-pink font-semibold text-2xl mb-2">My Crops</div>
  <div class="text-center mb-4 text-farm-dark text-lg font-semibold">
    👋 Welcome, <strong><?= htmlspecialchars($fname) ?></strong>!
  </div>
  <div class="text-center mb-6">
    <a href="addacrop.php" class="inline-block bg-farm-green text-white font-bold py-2 px-6 rounded-md shadow hover:bg-green-600 transition">
      + Add New Crop
    </a>
  </div>
  <div class="my-4 flex justify-end gap-4 pr-32">
  <!-- Go Back Button -->
  <a href="cropman.php" class="inline-block px-4 py-2 bg-orange-500 text-white rounded-md font-semibold hover:bg-orange-600 transition">
    🔙 Go Back
  </a>

  <!-- Growth Stage Button -->
  <a href="growthstage.php" class="inline-block px-4 py-2 bg-orange-500 text-white rounded-md font-semibold hover:bg-orange-600 transition">
    🌱 See how your crops are doing
  </a>
</div>




<div class="w-full bg-white rounded-lg shadow-md p-6 overflow-auto">


  <table class="w-full table-auto text-sm text-center divide-y divide-farm-green break-words">

    <thead class="bg-farm-header text-white">
      <tr>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Crop Name</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Planted At</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Harvest Date</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Type</th>
        
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Season</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Yield</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Duration</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Grain size</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Grain Color</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Disease</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">protein_content</th>
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">stracg content</th>

        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Description</th>
        
        <th class="px-6 py-3 uppercase font-semibold tracking-wide text-left">Actions</th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-farm-green">
      <?php while($row = mysqli_fetch_assoc($result)): ?>
      <tr class="hover:bg-farm-light transition-colors duration-200">
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark font-medium"><?= htmlspecialchars($row['farmer_crop_name']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['planted_at']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['harvested_time']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['type_name']); ?></td>
       
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['season']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['expected_yield']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark"><?= htmlspecialchars($row['harvest_time']); ?></td>
         <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['grain_size']); ?></td>
          <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['grain_color']); ?></td>
           <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['disease']); ?></td>
            <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['protein_content']); ?></td>
             <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['strach_content']); ?></td>
        <td class="px-6 py-3 whitespace-nowrap text-farm-dark max-w-xs break-words"><?= htmlspecialchars($row['c_desp']); ?></td>
        
        <td class="px-6 py-3 whitespace-nowrap space-y-2">
 <form action="delete_crop.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this crop?');" class="inline-block mr-2">
  <input type="hidden" name="fc_id" value="<?= $row['fc_id']; ?>">
  <button type="submit" 
    class="inline-flex justify-center items-center bg-red-600 hover:bg-red-700 text-white px-2 py-1 text-xs rounded-md transition w-24">
    <i class="fa-solid fa-trash mr-1"></i> Delete
  </button>
</form>
<form action="update_crop.php" method="GET" class="inline-block">
  <input type="hidden" name="fc_id" value="<?= $row['fc_id']; ?>">
  <button type="submit" 
    class="inline-flex justify-center items-center bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 text-xs rounded-md transition w-28">
    <i class="fa-solid fa-pen-to-square mr-1"></i> Update
  </button>
</form>


        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>

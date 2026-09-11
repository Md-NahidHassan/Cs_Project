<?php
session_start();
// Assuming navbar.php exists and handles connection or provides $conn
// If navbar.php doesn't include the connection, you might need to ensure it's handled here or in navbar.php
include("navbar.php");

// Ensure $conn is established. If navbar.php already establishes it, this might be redundant.
// But it's safer to have it here in case navbar.php is modified or doesn't always provide $conn.
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Conversion rates from various currencies to BDT (update rates as needed)
$conversionRates = [
    'Bangladesh' => 1,
    'USA' => 117.5,
    'India' => 1.3,
    'China' => 15.5,
    'UK' => 149.0,
    'EU' => 128.0,
    'Japan' => 0.85,
    'Canada' => 87.0,
    'Australia' => 80.5,
    'Malaysia' => 25.0,
    'Saudi Arabia' => 31.25,   // SAR to BDT (example rate)
    'Russia' => 1.4            // RUB to BDT (example rate)
];


// Currency symbols and names (moved here to be available for $chartData preparation)
$currencyNames = [
    'USA' => ['symbol' => '$', 'name' => 'Dollar'],
    'India' => ['symbol' => '₹', 'name' => 'Rupee'],
    'China' => ['symbol' => '¥', 'name' => 'Yuan'],
    'UK' => ['symbol' => '£', 'name' => 'Pound'],
    'EU' => ['symbol' => '€', 'name' => 'Euro'],
    'Japan' => ['symbol' => '¥', 'name' => 'Yen'],
    'Canada' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
    'Australia' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
    'Malaysia' => ['symbol' => 'RM', 'name' => 'Ringgit'],
    'Bangladesh' => ['symbol' => '৳', 'name' => 'Taka'],
    'Saudi Arabia' => ['symbol' => '﷼', 'name' => 'Riyal'],
    'Russia' => ['symbol' => '₽', 'name' => 'Ruble']
];


$currencySymbols = [
    'USA' => '$',
    'India' => '₹',
    'China' => '¥',
    'UK' => '£',
    'EU' => '€',
    'Japan' => '¥',
    'Canada' => 'C$',
    'Australia' => 'A$',
    'Malaysia' => 'RM',
    'Bangladesh' => '৳',
    'Saudi Arabia' => '﷼',
    'Russia' => '₽'
];


// Helper: Convert to BDT if national price
function convertToBDT($price, $country, $rates) {
    return isset($rates[$country]) ? $price * $rates[$country] : $price;
}

// Fetch distinct crop names and years
$crops = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT crop_name FROM prev_mp"), MYSQLI_ASSOC);
$years = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT YEAR(update_time) AS year FROM prev_mp ORDER BY year DESC"), MYSQLI_ASSOC);

// Get filter inputs
$cropName = $_GET['crop_name'] ?? '';
$source = $_GET['source_table'] ?? '';
$year1 = $_GET['year1'] ?? '';
$year2 = $_GET['year2'] ?? '';
$isFilterApplied = $cropName && $source && $year1 && $year2;
$compareData = [];
$chartData = []; // New array to store data for Chart.js

if ($isFilterApplied) {
    $stmt = $conn->prepare("
        SELECT
            type,
            region,
            country_name,
            YEAR(update_time) AS year,
            old_price
        FROM prev_mp
        WHERE crop_name = ? AND source_table = ? AND YEAR(update_time) IN (?, ?)
        ORDER BY type, region, country_name, update_time
    ");
    $stmt->bind_param("ssii", $cropName, $source, $year1, $year2);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $key = $row['type'] . '|' . ($source === 'local_price' ? $row['region'] : $row['country_name']);
        $compareData[$key][$row['year']] = $row['old_price'];
    }

    $stmt->close();

    // Prepare chart data after fetching all compareData
    foreach ($compareData as $key => $years_data) {
        list($type, $place) = explode('|', $key);
        $p1 = $years_data[$year1] ?? null;
        $p2 = $years_data[$year2] ?? null;

        if ($p1 !== null && $p2 !== null) { // Only include if both prices exist
            $percentageChange = ($p1 != 0) ? (($p2 - $p1) / $p1) * 100 : 0;

            // Determine currency for chartData
            $currentCurrencyName = '';
            $currentCurrencySymbol = '';
            if ($source === 'national_price') {
                $currentCurrencyName = $currencyNames[$place]['name'] ?? 'Currency';
                $currentCurrencySymbol = $currencySymbols[$place] ?? '';
            } else {
                $currentCurrencyName = 'Taka';
                $currentCurrencySymbol = '৳';
            }

            $chartData[] = [
                'label' => htmlspecialchars($cropName) . ' (' . htmlspecialchars($type) . ') in ' . htmlspecialchars($place),
                'year1_price' => $p1,
                'year2_price' => $p2,
                'year1' => $year1,
                'year2' => $year2,
                'percentage_change' => round($percentageChange, 2),
                'currency' => $currentCurrencyName,
                'symbol' => $currentCurrencySymbol
            ];
        }
    }
}

// If no filter, fetch full table
$fullData = [];
if (!$isFilterApplied) {
    $fullRes = mysqli_query($conn, "SELECT crop_name, type, old_price, status, region, country_name, update_time, changed_at, source_table
                                     FROM prev_mp
                                     ORDER BY update_time DESC");
    while ($r = mysqli_fetch_assoc($fullRes)) {
        $fullData[] = $r;
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Market Price Insights</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
</head>
<body>

<div style="text-align: center; margin-top: 30px; margin-bottom: 20px;">
  <h1 style="color: #166534; font-size: 30px; font-weight: 800; margin-bottom: 8px;">
    🧾 Historical Market Price Insights
  </h1>
  <p style="color: #4b5563; font-size: 16px;">
    Compare past crop prices by year, type, and region to track trends and make informed decisions.
  </p>
</div>

<div style="display: flex; justify-content: center; margin-top: 20px;">
  <form method="GET" style="padding: 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
    <label>Crop:
      <select name="crop_name">
        <option value="">--Select Crop--</option>
        <?php foreach ($crops as $crop): ?>
          <option value="<?= htmlspecialchars($crop['crop_name']) ?>" <?= ($cropName === $crop['crop_name']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($crop['crop_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Source:
      <select name="source_table">
        <option value="">--Select Source--</option>
        <option value="local_price" <?= ($source === 'local_price') ? 'selected' : '' ?>>Local</option>
        <option value="national_price" <?= ($source === 'national_price') ? 'selected' : '' ?>>National</option>
      </select>
    </label>

    <label>Year 1:
      <select name="year1">
        <option value="">--Select Year--</option>
        <?php foreach ($years as $y): ?>
          <option value="<?= $y['year'] ?>" <?= ($year1 == $y['year']) ? 'selected' : '' ?>><?= $y['year'] ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Year 2:
      <select name="year2">
        <option value="">--Select Year--</option>
        <?php foreach ($years as $y): ?>
          <option value="<?= $y['year'] ?>" <?= ($year2 == $y['year']) ? 'selected' : '' ?>><?= $y['year'] ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <button type="submit" style="background-color: #34d399; color: white; padding: 6px 12px; border: none; border-radius: 5px; font-weight: 600;">Compare</button>
    <?php if ($isFilterApplied): ?>
      <a href="pricecomp.php" style="background-color: #f87171; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-weight: 600;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<style>
  .back-btn {
    margin-left: 8px;
    background-color: white;
    color: #166534;
    border: 2px solid #166534;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    display: inline-block;
    transition: all 0.3s ease;
  }
  .back-btn:hover {
    background-color: #166534;
    color: white;
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(22, 101, 52, 0.3);
  }

  .gray-btn {
    background-color: #6b7280;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 700;
    transition: background-color 0.3s;
    border: none;
  }

  .gray-btn:hover {
    background-color: #4b5563;
  }
</style>

<div style="width: 90%; max-width: 1000px; margin: 20px auto 0 auto; text-align: right;">
  <a href="mpman.php" class="gray-btn">← Go Back</a>
  <a href="localprice.php" class="back-btn">⬅️ Local Price</a>
  <a href="nationalprice.php" class="back-btn">⬅️ National Price</a>
</div>

<?php if (!$isFilterApplied && !empty($fullData)): ?>
  <div style="margin: 20px auto; width: 90%; max-width: 1000px;">
    <table style="width: 100%; border: 2px solid #166534; border-collapse: separate; border-spacing: 0 8px; margin-top: 16px; font-size: 16px;">
      <thead style="background-color: #166534; color: white;">
        <tr>
          <th style="padding: 8px;">Crop_Name</th>
          <th>Type</th>
          <th>Price</th>
          <th>Year</th>
          <th>Source_Table</th>
          <th>Country</th>
          <th>Region</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fullData as $row): ?>
          <tr style="background-color: #ffffff; text-align: center;">
            <td style="padding: 12px 8px;"><?= htmlspecialchars($row['crop_name']) ?></td>
            <td style="padding: 12px 8px;"><?= htmlspecialchars($row['type']) ?></td>
            <td style="padding: 12px 8px;"><?= number_format($row['old_price'], 2) ?></td>
            <td style="padding: 12px 8px;"><?= htmlspecialchars(date('Y', strtotime($row['update_time']))) ?></td>
            <td style="padding: 12px 8px;"><?= htmlspecialchars($row['source_table']) ?></td>
            <td style="padding: 12px 8px;"><?= htmlspecialchars($row['country_name'] ?: '-') ?></td>
            <td style="padding: 12px 8px;"><?= htmlspecialchars($row['region'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php if ($isFilterApplied && !empty($compareData)): ?>
  <div style="margin: 20px auto; width: 90%; max-width: 800px;">
    <h2 style="text-align:center; color:#166534;">📋 Filtered Data: <?= htmlspecialchars($cropName) ?> (<?= htmlspecialchars($source) ?>)</h2>
   <table style="width:100%; border-collapse: separate; border-spacing: 0 8px; margin-top: 16px;">

      <thead style="background-color: #16a34a; color: white;">
        <tr>
          <th style="padding: 8px;">Type</th>
          <th><?= htmlspecialchars($year1) ?></th>
          <th><?= htmlspecialchars($year2) ?></th>
          <th>Region / Country</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($compareData as $key => $years_data): ?>
          <?php
            list($type, $place) = explode('|', $key);
            // Currency symbols for countries - defined globally now

            $val1 = '❌';
            if (isset($years_data[$year1])) {
                $price1 = $years_data[$year1];
                if ($source === 'national_price') {
                    $sym = $currencySymbols[$place] ?? '';
                    $val1 = number_format($price1, 2) . " $sym";
                } else {
                    // local price, show BDT with ৳
                    $val1 = number_format($price1, 2) . "৳";
                }
            }

            $val2 = '❌';
            if (isset($years_data[$year2])) {
                $price2 = $years_data[$year2];
                if ($source === 'national_price') {
                    $sym = $currencySymbols[$place] ?? '';
                    $val2 = number_format($price2, 2) . " $sym";
                } else {
                    $val2 = number_format($price2, 2) . "৳";
                }
            }

          ?>
          <tr style="text-align: center; background-color: #f9fafb;">
            <td><?= htmlspecialchars($type) ?></td>
            <td><?= $val1 ?></td>
            <td><?= $val2 ?></td>
            <td><?= htmlspecialchars($place) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($chartData)): // Ensure chartData exists before attempting to render graphs ?>
    <div style="margin: 40px auto; width: 90%; max-width: 1000px;">
        <h2 style="text-align:center; color:#166534; margin-bottom: 20px;">📊 Price Comparison (<?= htmlspecialchars($year1) ?> vs. <?= htmlspecialchars($year2) ?>)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($chartData as $index => $data): ?>
                <div style="background-color: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <canvas id="priceComparisonChart<?= $index ?>"></canvas>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Register the datalabels plugin globally
        Chart.register(ChartDataLabels);

        document.addEventListener('DOMContentLoaded', function() {
            const chartData = <?= json_encode($chartData) ?>;

            // --- DEBUGGING LOGS (keep these for now, they are very helpful!) ---
            console.log("Chart Data received from PHP:", chartData);
            if (chartData.length === 0) {
                console.warn("chartData is empty. No charts will be drawn.");
            }
            // --- END DEBUGGING LOGS ---

            chartData.forEach((data, index) => {
                const canvasId = 'priceComparisonChart' + index;
                const canvasElement = document.getElementById(canvasId);

                // --- DEBUGGING LOGS ---
                console.log("Attempting to get canvas:", canvasId);
                if (!canvasElement) {
                    console.error("Canvas element with ID '" + canvasId + "' not found!");
                    console.log("Available elements:", document.querySelectorAll('canvas')); // Check all canvases
                    return; // Skip this chart if canvas not found
                }
                // --- END DEBUGGING LOGS ---

                const ctx = canvasElement.getContext('2d');

                // Determine the color for the second bar (year2_price)
                let year2BarColor;
                if (data.year2_price > data.year1_price) {
                    year2BarColor = 'rgba(244, 67, 54, 0.8)'; // Red for Increase
                } else if (data.year2_price < data.year1_price) {
                    year2BarColor = 'rgba(76, 175, 80, 0.8)'; // Green for Decrease
                } else {
                    year2BarColor = 'rgba(255, 193, 7, 0.8)'; // Yellow for No Change
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [data.year1, data.year2],
                        datasets: [{
                            label: 'Price',
                            // FIX: Ensure data values are parsed as floats
                            data: [
                                parseFloat(data.year1_price),
                                parseFloat(data.year2_price)
                            ],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.6)', // A neutral blue for Year 1
                                year2BarColor // Dynamically set color for Year 2
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                year2BarColor.replace('0.8', '1') // Solid color for border
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: data.label + ' (Change: ' + data.percentage_change + '%)'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        // FIX: Ensure context.raw is parsed as float before toFixed
                                        let price = parseFloat(context.raw);
                                        if (isNaN(price)) price = 0; // Fallback for invalid numbers

                                        label += price.toFixed(2) + ' ' + data.symbol + ' (' + data.currency + ')';
                                        return label;
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: function(value) {
                                    // FIX: Ensure value is parsed as float before toFixed
                                    let displayValue = parseFloat(value);
                                    if (isNaN(displayValue)) displayValue = 0; // Fallback for invalid numbers

                                    return displayValue.toFixed(2) + ' ' + data.symbol;
                                },
                                color: '#333',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Price'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Year'
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
  <?php endif; ?>

  <div style="margin: 20px auto; text-align: center; font-weight: 600; color: #065f46;">
    <?php
    // $currencyNames moved to the top of the file

    foreach ($compareData as $key => $years_data) {
        list($type, $place) = explode('|', $key);
        $p1 = $years_data[$year1] ?? null;
        $p2 = $years_data[$year2] ?? null;

        // Currency info for display in summary text
        $cur_display_symbol = '';
        $cur_display_name = '';
        if ($source === 'national_price') {
            $cur_display_symbol = $currencySymbols[$place] ?? '';
            $cur_display_name = $currencyNames[$place]['name'] ?? 'Currency';
        } else {
            $cur_display_symbol = '৳';
            $cur_display_name = 'Taka';
        }

        $summaryText = "<p style='color:#065f46; font-weight:600; margin-top:10px;'>"
                     . htmlspecialchars($cropName) . " (" . htmlspecialchars($type) . ") in "
                     . htmlspecialchars($place);

        // Prepare prices for comparison and display
        $priceInfo = [];

        if ($p1 !== null) {
            $origPriceFormatted1 = number_format($p1, 2);
            if ($source === 'national_price') {
                $priceInfo[] = "had price <strong>{$origPriceFormatted1} {$cur_display_symbol} ({$cur_display_name})</strong>" . " (" . number_format(convertToBDT($p1, $place, $conversionRates), 2) . " Taka) in " . htmlspecialchars($year1);
            } else {
                $priceInfo[] = "had price <strong>{$origPriceFormatted1} {$cur_display_symbol} ({$cur_display_name})</strong> in " . htmlspecialchars($year1);
            }
        }

        if ($p2 !== null) {
            $origPriceFormatted2 = number_format($p2, 2);
            if ($source === 'national_price') {
                $priceInfo[] = "and then <strong>{$origPriceFormatted2} {$cur_display_symbol} ({$cur_display_name})</strong>" . " (" . number_format(convertToBDT($p2, $place, $conversionRates), 2) . " Taka) in " . htmlspecialchars($year2);
            } else {
                $priceInfo[] = "and then <strong>{$origPriceFormatted2} {$cur_display_symbol} ({$cur_display_name})</strong> in " . htmlspecialchars($year2);
            }
        }

        if (!empty($priceInfo)) {
            $summaryText .= " " . implode(' ', $priceInfo);

            // Calculate and display percentage change if both prices are available
            if ($p1 !== null && $p2 !== null && $p1 != 0) {
                $percentageChange = (($p2 - $p1) / $p1) * 100;
                $changeType = $percentageChange >= 0 ? 'increased' : 'decreased';
                $summaryText .= ". The price {$changeType} by <strong>" . number_format(abs($percentageChange), 2) . "%</strong>.";
            }
            $summaryText .= ".</p>"; // Close the paragraph tag
        }

        echo $summaryText;
    }
    ?>
  </div>
  <?php elseif ($isFilterApplied): // This block only runs if filter applied but no compareData ?>
    <p style="text-align: center; color: #b91c1c; font-weight: 600;">No matching data found for the selected filters.</p>
<?php endif; ?>

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
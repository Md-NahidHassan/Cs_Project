

<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (!isset($_SESSION['user_name'])) die("Unauthorized");

// Gather inputs
$symptoms_array = $_POST['symptoms'] ?? [];
$days = isset($_POST['days']) ? intval($_POST['days']) : 0;
$crop_id = isset($_POST['crop_id']) ? intval($_POST['crop_id']) : 0;
$type_id = isset($_POST['type_id']) ? intval($_POST['type_id']) : 0;

if (!is_array($symptoms_array) || count($symptoms_array) === 0 || $days <= 0) {
    die("Please select at least one symptom and enter a valid duration.");
}
$symptoms_input = implode(', ', $symptoms_array);

// Fetch crop name and type
$crop_name = '';
$type_name = '';
if ($crop_id > 0 && $type_id > 0) {
    $crop_query = "
        SELECT c.c_name, ct.type_name
        FROM crop c
        JOIN crop_type ct ON ct.type_id = ?
        WHERE c.crop_id = ?
    ";
    $stmt_crop = mysqli_prepare($conn, $crop_query);
    mysqli_stmt_bind_param($stmt_crop, "ii", $type_id, $crop_id);
    mysqli_stmt_execute($stmt_crop);
    $crop_result = mysqli_stmt_get_result($stmt_crop);
    if ($row = mysqli_fetch_assoc($crop_result)) {
        $crop_name = $row['c_name'];
        $type_name = $row['type_name'];
    }
}

// Build symptom match query
$terms = array_map('trim', $symptoms_array);
$like_clauses = [];
$params = [];
$types = '';

foreach ($terms as $term) {
    $like_clauses[] = "LOWER(symptoms) LIKE ?";
    $params[] = "%" . strtolower($term) . "%";
    $types .= 's';
}

$where_like = "(" . implode(" OR ", $like_clauses) . ")";
$disease_query = "
    SELECT * FROM disease_info
    WHERE $where_like
    AND ? BETWEEN min_days AND max_days
";

$bind_types = $types . 'i';
$bind_values = array_merge($params, [$days]);

$stmt = mysqli_prepare($conn, $disease_query);
if (!$stmt) die("Prepare failed: " . mysqli_error($conn));

// Bind params
$bind_params = [];
foreach ($bind_values as $key => $value) {
    $bind_params[$key] = &$bind_values[$key];
}
call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt, $bind_types], $bind_params));
mysqli_stmt_execute($stmt);
$disease_result = mysqli_stmt_get_result($stmt);
if (!$disease_result) die("Get result failed: " . mysqli_stmt_error($stmt));

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diagnosis Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 text-green-800">Diagnosis Results</h2>

        <?php if ($crop_name && $type_name): ?>
            <p class="mb-2 text-gray-700">
                <strong>Crop:</strong> <?= htmlspecialchars($crop_name) ?> <br>
                <strong>Type:</strong> <?= htmlspecialchars($type_name) ?>
            </p>
        <?php endif; ?>

        <p class="mb-2"><strong>Symptoms Selected:</strong> <?= htmlspecialchars($symptoms_input) ?></p>
        <p class="mb-6"><strong>Duration:</strong> <?= $days ?> day(s)</p>

        <?php if (mysqli_num_rows($disease_result) > 0): ?>
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-green-100">
                        <th class="px-4 py-2 text-left">Disease</th>
                        <th class="px-4 py-2 text-left">Medicine</th>
                        <th class="px-4 py-2 text-left">Home Remedy</th>
                        <th class="px-4 py-2 text-left">Doctor Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($disease_result)): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= htmlspecialchars($row['disease_name']) ?></td>
                            <td class="px-4 py-2"><?= nl2br(htmlspecialchars($row['medicine'])) ?></td>
                            <td class="px-4 py-2"><?= nl2br(htmlspecialchars($row['home_remedy'])) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['doctor_contact']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-red-600 font-semibold"> No matching diseases found. Try selecting different symptoms or duration.</p>
        <?php endif; ?>

        <div class="mt-6">
            <a href="diseasechecker.php" class="text-blue-600 hover:underline">🔙 Back to Symptom Checker</a>
        </div>
    </div>
</body>
</html>



<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'farmsystem');

if (!$conn) {
    echo 'Connection error: ' . mysqli_connect_error();
}

if (!isset($_SESSION['weather_data'])) {
    header('Location: weather.php'); // 
    exit(); // best practice
}

$weather_data = $_SESSION['weather_data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather Info</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            height: 100%;
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f8ff;
            transition: background-image 0.5s ease-in-out, background-color 0.5s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .container:hover {
            background-image: url('c15.jpg');
        }

        .circle {
            width: 400px;
            height: 400px;
            border: 5px solid black;
            border-radius: 50%;
            background-color: white;
            padding: 20px;
            text-align: center;
        }

        .go-back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: pink;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .go-back-btn:hover {
            background-color: deeppink;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="circle">
        <h2>Weather Info</h2>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($weather_data['location']); ?></p>
        <p><strong>Temperature:</strong> <?php echo htmlspecialchars($weather_data['temp']); ?> °C</p>
        <p><strong>Rainfall:</strong> <?php echo htmlspecialchars($weather_data['rainfall']); ?> mm</p>
        <p><strong>Tips:</strong> <?php echo htmlspecialchars($weather_data['tips']); ?></p>
        <a href="index.php" class="go-back-btn">Go Back</a>
    </div>
</div>

</body>
</html>
<script>
    const circle = document.querySelector('.circle');
    const container = document.querySelector('.container');

    circle.addEventListener('mouseenter', () => {
        container.style.backgroundImage = "url('c20.jpg')";
    });

    circle.addEventListener('mouseleave', () => {
        container.style.backgroundImage = "";
    });
</script>


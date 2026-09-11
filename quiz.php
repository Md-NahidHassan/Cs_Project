<?php
// 1) CONNECT & SESSION
// This part is crucial for connecting to your database and managing sessions.
// The credentials should match those in your rental.php file.
$conn = mysqli_connect('localhost','root','','farmsystem');
if (!$conn) {
  // If connection fails, stop execution and show error
  die("DB Connection Error: " . mysqli_connect_error());
}
session_start(); // Start the PHP session to access $_SESSION variables

// 2) Define Correct Answers for Your Quiz
// These are the correct options for each question.
// Make sure to add entries for all questions in your quiz.
$correct_answers = [
    'question1' => 'option C', // For "Sustainable Crop Cultivation" - C) It improves soil structure and promotes healthier root growth.
    'question2' => 'option D', // For "Pest and Disease Management" - D) Practicing crop rotation.
    'question3' => 'option B', // For "Advanced Soil Nutrition" - B) Conducting regular soil tests.
    'question4' => 'option A', // For "Farm Equipment Operation & Safety" - A) Ensuring tires, fluids, and moving parts are in good condition.
    'question5' => 'option C', // For "Water Resource Management for Farms" - C) Drip irrigation.
    'question6' => 'option C', // For "Organic Farming Principles" - C) Embracing composting.
    'question7' => 'option B', // For "Livestock Health and Nutrition" - B) Clean, fresh water and a balanced diet.
    'question8' => 'option C', // For "Agricultural Marketing Strategies" - C) Building relationships with local restaurants and markets.
    'question9' => 'option B', // For "Farm Business Planning & Management" - B) It helps in budgeting and making informed business decisions.
    'question10'=> 'option B', // For "Introduction to Agri-Technology" - B) Soil moisture sensors.
];

$score = 0; // Initialize user's score
$total_questions = count($correct_answers); // Total number of questions in the quiz
$feedback = []; // Array to store feedback for each question

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop through each correct answer to check user's submission
    foreach ($correct_answers as $q_name => $correct_ans) {
        // Check if the user selected an answer for the current question
        if (isset($_POST[$q_name])) {
            $user_answer = $_POST[$q_name]; // Get the user's selected answer
            if ($user_answer == $correct_ans) {
                $score++; // Increment score if answer is correct
                $feedback[$q_name] = "Correct!"; // Set feedback message
            } else {
                $feedback[$q_name] = "Incorrect. The correct answer was: " . $correct_ans; // Set feedback for incorrect answer
            }
        } else {
            $feedback[$q_name] = "Not answered."; // If user didn't answer a question
        }
    }

    // --- Database Insertion Part ---
    // Get the user ID from the session. This assumes your login process sets $_SESSION['u_id'].
    // If user is not logged in, $user_id will be null.
    // FOR TESTING: If you don't have a login system yet, you can temporarily hardcode a user ID here.
    // REMEMBER TO REMOVE OR COMMENT OUT THIS LINE IN PRODUCTION!
    $_SESSION['u_id'] = $_SESSION['u_id'] ?? 1; // Temporary: Assign a default user_id if not logged in.
                                               // In a real app, you'd redirect to login or show an error if $_SESSION['u_id'] is not set.

    $user_id = $_SESSION['u_id'] ?? null; // Get user ID from session. If still null, it means no user logged in or temp ID not set.
    $quiz_id = 1; // Assign a unique ID for THIS specific quiz (e.g., 1 for "Farmer Training Quiz").
                  // If you have multiple quizzes, this ID would come from the form or another logic.

    // Only attempt to insert into DB if a user ID is available and DB connection is open
    if ($user_id !== null && $conn) {
        // Prepare an SQL statement to insert the quiz result.
        // Using prepared statements is essential to prevent SQL injection vulnerabilities.
        $insert_sql = "INSERT INTO quiz_results (user_id, quiz_id, score, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_sql);

        if ($stmt) {
            // Bind parameters: 'iii' means three integers (user_id, quiz_id, score)
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $quiz_id, $score);
            
            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Result saved successfully (you could add a flash message here if needed)
                // flash("Quiz result saved!", "success");
            } else {
                // Log the error if saving fails
                error_log("Error saving quiz result: " . mysqli_error($conn));
                // You might also display a user-friendly error message
                // flash("Failed to save quiz result.", "error");
            }
            mysqli_stmt_close($stmt); // Close the statement
        } else {
            // Log error if statement preparation fails
            error_log("Error preparing statement for quiz results: " . mysqli_error($conn));
        }
    } else {
        // Handle cases where user is not logged in or DB connection is not available
        // For now, it just won't save to the DB. You could add a message.
        // flash("Quiz results not saved. Please ensure you are logged in.", "info");
    }
    // --- End Database Insertion Part ---

    // --- Fetch User's Progression (Past Scores) ---
    $past_scores = [];
    if ($user_id !== null && $conn) {
        $select_sql = "SELECT score, timestamp FROM quiz_results WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5"; // Get last 5 scores
        $stmt = mysqli_prepare($conn, $select_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $past_scores[] = $row;
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error preparing statement for fetching past quiz results: " . mysqli_error($conn));
        }
    }
    // --- End Fetch Progression ---

    // Display results to the user on an HTML page
    echo "<!DOCTYPE html><html><head><title>Quiz Results</title>";
    echo "<script src='https://cdn.tailwindcss.com'></script>";
    // Inline CSS for consistency with the main theme colors
    echo "<style>:root {--farm-green: #22c55e; --farm-dark: #166534; --farm-light: #dcfce7; } .text-farm-dark { color: #065f46; } .bg-farm-light { background-color: var(--farm-light); } .border-farm-green { border-color: #86efac; }</style>";
    echo "</head><body class='bg-gray-100 font-sans'>";
    echo "<div class='container mx-auto p-6 mt-8 max-w-xl bg-farm-light rounded-lg shadow-md border border-farm-green'>";
    echo "<h1 class='text-3xl font-bold text-center text-farm-dark mb-6'>Your Quiz Results</h1>";
    echo "<p class='text-xl text-center text-farm-dark mb-4'>You scored: " . $score . " out of " . $total_questions . "</p>";

    // Display feedback for each question
    echo "<div class='mt-6 mb-8 p-4 bg-white rounded-lg shadow-inner'>";
    echo "<h3 class='text-xl font-semibold text-farm-dark mb-4'>Detailed Feedback:</h3>";
    foreach ($feedback as $q_name => $msg) {
        // Format question name nicely (e.g., "Question 1")
        $display_q_name = ucfirst(str_replace('question', 'Question ', $q_name));
        $color_class = (strpos($msg, 'Correct') !== false) ? 'text-green-600' : 'text-red-600';
        echo "<p class='mb-2 " . $color_class . "'><strong>" . $display_q_name . ":</strong> " . $msg . "</p>";
    }
    echo "</div>"; // End Detailed Feedback Div

    // Display User's Progression (Past Scores)
    if (!empty($past_scores)) {
        echo "<div class='mt-6 mb-8 p-4 bg-white rounded-lg shadow-inner'>";
        echo "<h3 class='text-xl font-semibold text-farm-dark mb-4'>Your Quiz Progression:</h3>";
        echo "<table class='min-w-full divide-y divide-farm-green'>";
        echo "<thead class='bg-farm-header text-white'>";
        echo "<tr><th class='px-4 py-2 text-left'>Attempt Date</th><th class='px-4 py-2 text-left'>Score</th></tr>";
        echo "</thead><tbody class='divide-y divide-gray-200'>";
        foreach ($past_scores as $past_score) {
            echo "<tr><td class='px-4 py-2'>" . date('Y-m-d H:i', strtotime($past_score['timestamp'])) . "</td><td class='px-4 py-2'>" . $past_score['score'] . " / " . $total_questions . "</td></tr>";
        }
        echo "</tbody></table>";
        echo "</div>"; // End Progression Div
    } else {
        echo "<p class='text-center text-gray-600 mt-4'>No past quiz records found for this user.</p>";
    }


    // Link back to the main course page (farmingtips.php)
    echo "<p class='text-center mt-6'><a href='farmingtips.php' class='bg-farm-green text-white py-2 px-4 rounded hover:bg-green-700'>Go Back to Courses</a></p>";
    echo "</div></body></html>";

} else {
    // If someone tries to access quiz.php directly without a POST submission (e.g., by typing URL)
    // Redirect them back to the main course page
    header("Location: farmingtips.php");
    exit(); // Always exit after a header redirect
}

// Close the database connection when done
mysqli_close($conn);
?>

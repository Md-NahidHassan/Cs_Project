<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmHub - Farmer Training Courses</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        /* Custom Tailwind Colors from rental.php for consistency */
        :root {
            --farm-green: #22c55e;
            --farm-dark: #166534;
            --farm-light: #dcfce7;
            --bg-farm-header: #166534;
            --text-farm-dark: #065f46;
            --border-farm-green: #86efac;
        }

        .bg-farm-dark { background-color: var(--farm-dark); }
        .text-farm-green { color: var(--farm-green); }
        .bg-farm-green { background-color: var(--farm-green); }
        .hover\:bg-green-700:hover { background-color: #1a9e4e; }
        .hover\:text-farm-light:hover { color: var(--farm-light); }
        .bg-farm-light { background-color: var(--farm-light); }
        .text-farm-dark { color: var(--text-farm-dark); }
        .border-farm-green { border-color: var(--border-farm-green); }
        .bg-farm-header { background-color: var(--bg-farm-header); }

        /* General body styling */
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>

    <!-- Header Section (consistent with rental.php) -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <!-- Top Navigation -->
        <div class="bg-farm-dark text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-4">
                        <div class="text-2xl font-bold text-farm-green">
                            <i class="fas fa-seedling mr-2"></i>FarmHub
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex-1 flex justify-end space-x-4 text-sm whitespace-nowrap">
                        <a
                            href="index.php"
                            class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
                        >Home</a>
                        <a
                            href="#courses-section"
                            class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
                        >Our Courses</a>
                        <a
                            href="#quiz-section"
                            class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
                        >Take Quiz</a>
                        <a
                            href="rental.php"
                            class="hover:text-farm-light hover:text-[15px] transition-all duration-200"
                            >Equipment Rental</a
                        >
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area for Courses -->
    <main class="container mx-auto p-6 mt-8" id="courses-section">
        <h1 class="text-4xl font-bold text-center text-farm-dark mb-12">Farmer Training Courses & Tips</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Course 1 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 1: Sustainable Crop Cultivation</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Ensure your soil has good drainage and aeration. Compacted soil hinders root growth and water absorption. Regularly aerate your land to improve soil structure for healthier crops.
                    </p>
            </div>
                <a href="https://youtu.be/yGvVLYMNc3E" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>
            <!-- Course 2 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 2: Pest and Disease Management</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Practice crop rotation to disrupt pest life cycles and reduce disease buildup in the soil. Changing crops annually helps keep your fields productive and healthier naturally.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/3JigXb9KXqI" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 3 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 3: Advanced Soil Nutrition</h2>
                    <p class="text-gray-700 mb-4">
                       <strong>Farming Tip:</strong> Conduct regular soil tests to understand nutrient deficiencies. This allows you to apply precise amounts of fertilizer, avoiding waste and ensuring your crops get exactly what they need.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/26qTgXJKMAE" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 4 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 4: Farm Equipment Operation & Safety</h2>
                    <p class="text-gray-700 mb-4">
                       <strong>Farming Tip:</strong> Always perform pre-operation checks on your farm equipment. Ensuring tires, fluids, and moving parts are in good condition prevents breakdowns and ensures safety during use.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/IrMBIgoy7Ro" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 5 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 5: Water Resource Management for Farms</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Consider drip irrigation for water-intensive crops. This method delivers water directly to the plant roots, significantly reducing water waste compared to traditional methods.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/TCk6LeLZF0M" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 6 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 6: Organic Farming Principles</h2>
                    <p class="text-gray-700 mb-4">
                       <strong>Farming Tip:</strong> Embrace composting. Organic compost enriches soil with vital nutrients, improves soil structure, and reduces the need for synthetic fertilizers, promoting healthier growth.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/wPlQV9FhmqA" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 7 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 7: Livestock Health and Nutrition</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Provide clean, fresh water and a balanced diet tailored to your livestock's age and type. Proper nutrition is fundamental to preventing illness and ensuring animal productivity.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/eeQBlVzmRQU" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 8 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 8: Agricultural Marketing Strategies</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Build relationships with local restaurants and markets. Direct sales can often fetch better prices for your produce and build a loyal customer base.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/lg3uNcDpYm8" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 9 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 9: Farm Business Planning & Management</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Keep detailed financial records. Tracking expenses and income helps in budgeting, identifies areas for improvement, and aids in making informed business decisions.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/vccikqRWzMM" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>

            <!-- Course 10 -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-farm-green hover:shadow-xl transition-shadow duration-300 flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-farm-dark mb-4">Course 10: Introduction to Agri-Technology</h2>
                    <p class="text-gray-700 mb-4">
                        <strong>Farming Tip:</strong> Explore simple smart farming tools like soil moisture sensors. These devices can help you optimize irrigation, saving water and ensuring plants get enough hydration without overwatering.
                    </p>
                </div>
                <a href="https://www.youtube.com/embed/hb6t_fg5Pcs" target="_blank" class="block w-full text-center bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold mt-auto">
                    Click here to learn more
                </a>
            </div>
        </div>
    </main>

    <!-- Quiz Session -->
    <section class="container mx-auto p-6 mt-12 bg-farm-light rounded-lg shadow-md border border-farm-green" id="quiz-section">
        <h2 class="text-3xl font-bold text-center text-farm-dark mb-8">Quiz Session: Test Your Knowledge!</h2>
        <p class="text-center text-gray-700 mb-6">After watching the course videos, try this quiz to check what you've learned. (Note: This is a single static form and requires backend PHP processing to grade and save results.)</p>

        <form action="quiz.php" method="POST" class="max-w-xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <!-- Question 1 (from Course 1) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 1: What is a primary benefit of ensuring good soil drainage and aeration?</p>
                <label class="block mb-2">
                    <input type="radio" name="question1" value="optionA" class="mr-2"> A) It increases the soil's acidity.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question1" value="optionB" class="mr-2"> B) It hinders root growth and water absorption.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question1" value="optionC" class="mr-2"> C) It improves soil structure and promotes healthier root growth.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question1" value="optionD" class="mr-2"> D) It makes the soil denser and more compact.
                </label>
            </div>

            <!-- Question 2 (from Course 2) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 2: According to the tip for "Pest and Disease Management," what practice helps disrupt pest life cycles?</p>
                <label class="block mb-2">
                    <input type="radio" name="question2" value="optionA" class="mr-2"> A) Continuous monocropping.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question2" value="optionB" class="mr-2"> B) Frequent application of chemical pesticides.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question2" value="optionC" class="mr-2"> C) Regular soil testing.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question2" value="optionD" class="mr-2"> D) Practicing crop rotation.
                </label>
            </div>

            <!-- Question 3 (from Course 3) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 3: What is recommended to understand nutrient deficiencies and apply precise amounts of fertilizer?</p>
                <label class="block mb-2">
                    <input type="radio" name="question3" value="optionA" class="mr-2"> A) Guessing based on plant appearance.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question3" value="optionB" class="mr-2"> B) Conducting regular soil tests.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question3" value="optionC" class="mr-2"> C) Applying a fixed amount of fertilizer annually.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question3" value="optionD" class="mr-2"> D) Consulting a neighbor's farming practices.
                </label>
            </div>

            <!-- Question 4 (from Course 4) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 4: What is a key safety tip before operating farm equipment?</p>
                <label class="block mb-2">
                    <input type="radio" name="question4" value="optionA" class="mr-2"> A) Ensuring tires, fluids, and moving parts are in good condition.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question4" value="optionB" class="mr-2"> B) Checking the weather forecast.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question4" value="optionC" class="mr-2"> C) Loading fuel only when the engine is running.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question4" value="optionD" class="mr-2"> D) Operating only at high speeds.
                </label>
            </div>

            <!-- Question 5 (from Course 5) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 5: Which irrigation method is highlighted for significantly reducing water waste?</p>
                <label class="block mb-2">
                    <input type="radio" name="question5" value="optionA" class="mr-2"> A) Flood irrigation.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question5" value="optionB" class="mr-2"> B) Sprinkler systems.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question5" value="optionC" class="mr-2"> C) Drip irrigation.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question5" value="optionD" class="mr-2"> D) Overhead watering.
                </label>
            </div>

            <!-- Question 6 (from Course 6) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 6: What organic practice is recommended for enriching soil and reducing the need for synthetic fertilizers?</p>
                <label class="block mb-2">
                    <input type="radio" name="question6" value="optionA" class="mr-2"> A) Slash and burn.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question6" value="optionB" class="mr-2"> B) Heavy tillage.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question6" value="optionC" class="mr-2"> C) Embracing composting.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question6" value="optionD" class="mr-2"> D) Using only chemical pesticides.
                </label>
            </div>

            <!-- Question 7 (from Course 7) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 7: For livestock health, what two fundamental provisions are emphasized?</p>
                <label class="block mb-2">
                    <input type="radio" name="question7" value="optionA" class="mr-2"> A) Limited food and occasional water.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question7" value="optionB" class="mr-2"> B) Clean, fresh water and a balanced diet.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question7" value="optionC" class="mr-2"> C) Constant medication and isolated housing.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question7" value="optionD" class="mr-2"> D) High-fat diet and infrequent veterinary checks.
                </label>
            </div>

            <!-- Question 8 (from Course 8) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 8: What marketing strategy is suggested to potentially get better prices for your produce?</p>
                <label class="block mb-2">
                    <input type="radio" name="question8" value="optionA" class="mr-2"> A) Selling only to large distributors.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question8" value="optionB" class="mr-2"> B) Dumping excess produce at low prices.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question8" value="optionC" class="mr-2"> C) Building relationships with local restaurants and markets.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question8" value="optionD" class="mr-2"> D) Avoiding direct interaction with buyers.
                </label>
            </div>

            <!-- Question 9 (from Course 9) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 9: What is a key benefit of keeping detailed financial records for your farm?</p>
                <label class="block mb-2">
                    <input type="radio" name="question9" value="optionA" class="mr-2"> A) It makes tax filing more complicated.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question9" value="optionB" class="mr-2"> B) It helps in budgeting and making informed business decisions.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question9" value="optionC" class="mr-2"> C) It provides unnecessary bureaucracy.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question9" value="optionD" class="mr-2"> D) It limits your ability to expand operations.
                </label>
            </div>

            <!-- Question 10 (from Course 10) -->
            <div class="mb-6">
                <p class="text-lg font-semibold text-farm-dark mb-3">Question 10: Which simple smart farming tool is suggested to optimize irrigation?</p>
                <label class="block mb-2">
                    <input type="radio" name="question10" value="optionA" class="mr-2"> A) Automated harvesting robots.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question10" value="optionB" class="mr-2"> B) Soil moisture sensors.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question10" value="optionC" class="mr-2"> C) Satellite imagery for crop analysis.
                </label>
                <label class="block mb-2">
                    <input type="radio" name="question10" value="optionD" class="mr-2"> D) GPS-guided tractors.
                </label>
            </div>

            <button type="submit" class="w-full bg-farm-green text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                Submit Answers
            </button>
        </form>
    </section>

    <!-- Footer Section (consistent with rental.php) -->
    <footer class="bg-farm-dark text-white py-8 mt-12">
        <div class="container mx-auto text-center text-sm">
            © <?php echo date("Y"); ?> FarmHub. All rights reserved.
        </div>
    </footer>

</body>
</html>

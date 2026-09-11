<?php 
session_start();
require_once 'session_timeout.php';

if (empty($_SESSION['u_id'])) {
    header('Location: login.php');
    exit();
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmHub - Farming Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'farm-green': '#22c55e',
                        'farm-dark': '#166534',
                        'farm-light': '#dcfce7'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
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

                    <!-- Delivery Location -->
                    <div class="hidden md:flex items-center space-x-2 text-sm">
                        <i class="fa-regular fa-map text-farm-green"></i>
                        <div>
                            <p class="text-xs opacity-75">Deliver to</p>
                            <p class="font-semibold">Bangladesh</p>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex-1 max-w-2xl mx-8">
                        <div class="relative flex">
                            <select class="bg-gray-100 text-gray-800 px-3 py-2 rounded-l-lg border-0 focus:ring-2 focus:ring-farm-green">
                                <option>ALL Crops</option>
                                <option>Rice</option>
                                <option>Wheat</option>
                                <option>Vegetables</option>
                            </select>
                            <input 
                                type="text" 
                                placeholder="Search for Crops, Equipment, or Farmers"
                                class="flex-1 px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-farm-green"
                            >
                            <button class="bg-farm-green hover:bg-green-600 px-4 py-2 rounded-r-lg transition-colors">
                                <i class="fa-solid fa-magnifying-glass text-white"></i>
                            </button>
                        </div>
                    </div>

                    <!-- User Section -->
                    <div class="flex items-center space-x-6">
                        <!-- Sign In / User Info -->
                        <div class="text-sm">
                            <!-- PHP Session Integration - Replace with your actual PHP logic -->
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <div id="user-greeting">
                                    <p class="font-semibold">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                                    <a href="logout.php" class="text-farm-green hover:text-green-300 transition-colors">LOG OUT</a>
                                </div>
                            <?php else: ?>
                                <div id="sign-in-section">
                                    <a href="signup.php" class="font-semibold hover:text-farm-green transition-colors">Hello, Sign In</a>
                                    <br>
                                    <a href="login.php" class="text-farm-green hover:text-green-300 transition-colors">LOG IN</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="view_cart.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
                           <i class="fa-solid fa-cart-shopping"></i>
                            <span class="hidden md:inline">My cart</span>
                        </a>
                         <a href="myOrders.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
                            <i class="fa-solid fa-bag-shopping"></i>
                           
                            <span class="hidden md:inline">My oders</span>
                        </a>

                        <!-- User Dashboard -->
                        <a href="userdashboard.php" class="flex items-center space-x-1 hover:text-farm-green transition-colors">
                            <i class="fa-solid fa-user"></i>
                            <span class="hidden md:inline">profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <nav class="bg-farm-green text-white">
  <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
    <div class="flex items-center justify-between h-12 w-full">
      <!-- Left: Menu icon + Home -->
      <div class="flex items-center space-x-2 mr-4">
        <i class="fa-solid fa-bars text-base"></i>
        <span class="font-semibold text-base">
  <a href="index.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Home</a>
</span>

      </div>

      <!-- Right: Nav links -->
      <div class="hidden md:flex flex-1 justify-end space-x-4 text-sm whitespace-nowrap">
        <a href="marketlist.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Market Place</a>
        <a href="cropman.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Crop Management</a>
        <a href="rental.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Equipment Rental</a>
        <a href="take_loan.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Loan Management</a>
        <a href="weather.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Weather Conditions</a>
        <a href="growthstage.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Growth Tracking</a>
        <a href="cropsinbd.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Crops in Bangladesh</a>
        <a href="Exp&sell.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Sales</a>
        <a href="mpman.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Market Prices</a>
        <a href="govt.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Government Schemes</a>
        <a href="farmingtip.php" class="hover:text-farm-light hover:text-[15px] transition-all duration-200">Tips</a>
      </div>
    </div>
  </div>
</nav>



    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-farm-green to-green-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Modern Farming Made Simple
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">
                Manage your crops, equipment, and finances all in one place
            </p>
            <a href="#services" class="bg-white text-farm-green px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center">
                Get Started <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
                Comprehensive Farm Management Tools
            </h2>
            
            <!-- Main Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <!-- Crop Management -->
                <a href="cropman.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-48 relative overflow-hidden bg-gradient-to-br from-green-400 to-green-600">
                        <img src="https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Crop Management" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-green-500 hidden items-center justify-center">
                            <i class="fas fa-seedling text-6xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Crop Management</h3>
                        <p class="text-gray-600 text-sm mb-4">Monitor and manage your crops from planting to harvest</p>
                        <div class="flex items-center text-farm-green group-hover:text-farm-dark transition-colors">
                            <span class="text-sm font-semibold">Learn More</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </div>
                </a>

                <!-- Equipment Rental -->
                <a href="rental.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-48 relative overflow-hidden bg-gradient-to-br from-blue-400 to-blue-600">
                        <img src="https://images.unsplash.com/photo-1581833971358-2c8b550f87b3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Equipment Rental" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-blue-500 hidden items-center justify-center">
                            <i class="fas fa-tools text-6xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Equipment Rental</h3>
                        <p class="text-gray-600 text-sm mb-4">Rent farming equipment when you need it</p>
                        <div class="flex items-center text-farm-green group-hover:text-farm-dark transition-colors">
                            <span class="text-sm font-semibold">Learn More</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </div>
                </a>

                <!-- Marketplace -->
                <a href="marketlist.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-48 relative overflow-hidden bg-gradient-to-br from-orange-400 to-orange-600">
                        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Marketplace" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-orange-500 hidden items-center justify-center">
                            <i class="fas fa-store text-6xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Marketplace</h3>
                        <p class="text-gray-600 text-sm mb-4">Buy and sell agricultural products online</p>
                        <div class="flex items-center text-farm-green group-hover:text-farm-dark transition-colors">
                            <span class="text-sm font-semibold">Learn More</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </div>
                </a>

                <!-- Loan Management -->
                <a href="take_loan.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-48 relative overflow-hidden bg-gradient-to-br from-purple-400 to-purple-600">
                        <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Loan Management" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-purple-500 hidden items-center justify-center">
                            <i class="fas fa-coins text-6xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Loan Management</h3>
                        <p class="text-gray-600 text-sm mb-4">Access funding and manage agricultural loans</p>
                        <div class="flex items-center text-farm-green group-hover:text-farm-dark transition-colors">
                            <span class="text-sm font-semibold">Learn More</span>
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Secondary Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Weather -->
                <a href="weather.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-sky-400 to-sky-600">
                        <img src="https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Weather Forecast" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-sky-500 hidden items-center justify-center">
                            <i class="fas fa-cloud-sun text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Weather Forecast</h3>
                        <p class="text-gray-600 text-sm">Get accurate weather predictions for better planning</p>
                    </div>
                </a>

                <!-- Market Prices -->
                <a href="mpman.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-red-400 to-red-600">
                        <img src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Market Prices" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-red-500 hidden items-center justify-center">
                            <i class="fas fa-chart-line text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Real-Time Market Prices</h3>
                        <p class="text-gray-600 text-sm">Real-time pricing information for crops</p>
                    </div>
                </a>

                <!-- Crops in Bangladesh -->
                <a href="cropsinbd.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-emerald-400 to-emerald-600">
                        <img src="cropbd.jpg"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-emerald-500 hidden items-center justify-center">
                            <i class="fas fa-leaf text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Crops in Bangladesh</h3>
                        <p class="text-gray-600 text-sm">Information about local crops and varieties</p>
                    </div>
                </a>

                <!-- Government Schemes -->
                <a href="govt.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-yellow-400 to-yellow-600">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Government Schemes" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-yellow-500 hidden items-center justify-center">
                            <i class="fas fa-university text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Government Schemes</h3>
                        <p class="text-gray-600 text-sm">Access government agricultural support programs</p>
                    </div>
                </a>

                <!-- Growth Tracking -->
                <a href="growthstage.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-teal-400 to-teal-600">
                        <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Growth Tracking" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-teal-500 hidden items-center justify-center">
                            <i class="fas fa-chart-area text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Growth Tracking</h3>
                        <p class="text-gray-600 text-sm">Monitor crop growth stages and progress</p>
                    </div>
                </a>

                <!-- Expenses & Sales -->
                <a href="Exp&Sell_in.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-indigo-400 to-indigo-600">
                        <img src="expen.jpg"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-indigo-500 hidden items-center justify-center">
                            <i class="fas fa-calculator text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Expenses & Sales</h3>
                        <p class="text-gray-600 text-sm">Track your farming expenses and sales revenue</p>
                    </div>
                </a>

                <!-- dieasechecker -->
                <a href="dieaseman.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-violet-400 to-violet-600">
                        <img src="die.jpg"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-violet-500 hidden items-center justify-center">
                            <i class="fas fa-globe text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Disease checker</h3>
                        <p class="text-gray-600 text-sm">Manage Disease checker</p>
                    </div>
                </a>

                <!-- Farming Tips -->
                <a href="farmingtip.php" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow group cursor-pointer">
                    <div class="h-40 relative overflow-hidden bg-gradient-to-br from-pink-400 to-pink-600">
                        <img src="https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Farming Tips" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 bg-pink-500 hidden items-center justify-center">
                            <i class="fas fa-lightbulb text-5xl text-white opacity-80"></i>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-10 transition-opacity"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Farming Tips</h3>
                        <p class="text-gray-600 text-sm">Expert advice and best practices for farming</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-farm-light py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
                Why Choose FarmHub?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-farm-green w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-mobile-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Easy to Use</h3>
                    <p class="text-gray-600">Simple, intuitive interface designed for farmers of all tech levels</p>
                </div>
                <div class="text-center">
                    <div class="bg-farm-green w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Secure & Reliable</h3>
                    <p class="text-gray-600">Your data is protected with enterprise-grade security measures</p>
                </div>
                <div class="text-center">
                    <div class="bg-farm-green w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">24/7 Support</h3>
                    <p class="text-gray-600">Get help whenever you need it with our dedicated support team</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-farm-green">Get to Know Us</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-farm-green transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">About FarmHub</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Investor Relations</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 text-farm-green">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-farm-green transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Shipping & Returns</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Track Orders</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 text-farm-green">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-farm-green transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-farm-green transition-colors">Cookie Settings</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4 text-farm-green">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-farm-green rounded-full flex items-center justify-center hover:bg-green-600 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-farm-green rounded-full flex items-center justify-center hover:bg-green-600 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-farm-green rounded-full flex items-center justify-center hover:bg-green-600 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-900 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-2xl font-bold text-farm-green mb-2 md:mb-0">
                        <i class="fas fa-seedling mr-2"></i>FarmHub
                    </div>
                    <div class="text-sm text-gray-400">
                        © 2025 FarmHub, Inc. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button 
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed bottom-6 right-6 bg-farm-green hover:bg-farm-dark text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-colors z-50"
    >
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Simple scroll effect for navbar
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('shadow-2xl');
            } else {
                header.classList.remove('shadow-2xl');
            }
        });

        // Mobile menu toggle (you can expand this)
        function toggleMobileMenu() {
            // Add mobile menu functionality here
            console.log('Mobile menu toggled');
        }
    </script>
</body>
</html>
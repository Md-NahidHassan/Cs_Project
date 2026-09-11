-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 06:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farmsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `truck` enum('Yes','No') DEFAULT 'No',
  `total_price` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crop`
--

CREATE TABLE `crop` (
  `crop_id` int(11) NOT NULL,
  `c_name` varchar(50) NOT NULL,
  `season` varchar(50) DEFAULT NULL,
  `expected_yield` double DEFAULT NULL,
  `harvest_time` varchar(50) DEFAULT NULL,
  `c_desp` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop`
--

INSERT INTO `crop` (`crop_id`, `c_name`, `season`, `expected_yield`, `harvest_time`, `c_desp`) VALUES
(1, 'rice', 'summer', 3000, 'November', 'Staple grain grown in water fields.'),
(2, 'Wheat', 'pre-winter', 2800, 'March', 'Major cereal crop for winter season.'),
(3, 'Maize', 'Monsoon', 3200, 'October', 'Popular cereal crop for food and fodder.'),
(4, 'Jute', 'Annual', 2000, 'August', 'Fiber crop used for making sacks.'),
(5, 'Potato', 'Winter', 2500, 'February', 'Tuber crop grown under the soil.'),
(6, 'Sugarcane', 'Annual', 50000, 'December', 'Tall grass for sugar production.'),
(7, 'Mustard', 'Autumn', 1800, 'March', 'Oilseed crop for cooking oil.'),
(8, 'Tomato', 'Summer', 1500, 'June', 'Fruit vegetable, rich in vitamins.'),
(9, 'Brinjal', 'Summer', 1400, 'July', 'Common vegetable also called eggplant.'),
(10, 'Chili', 'Summer', 1300, 'July', 'Spicy vegetable used in curries.'),
(11, 'Onion', 'Spring', 2000, 'April', 'Essential kitchen vegetable.'),
(12, 'Garlic', 'summer', 1500, 'April', 'Pungent vegetable for flavoring.'),
(13, 'Peanut', 'Winter', 2300, 'October', 'Legume grown for edible seeds.'),
(14, 'Sesame', 'Summer', 1100, 'September', 'Oilseed known for small seeds.'),
(15, 'Sunflower', 'Spring', 2500, 'October', 'Grown for seeds and oil.'),
(16, 'Carrot', 'Winter', 1800, 'February', 'Root vegetable rich in Vitamin A.'),
(17, 'Cauliflower', 'Winter', 1700, 'January', 'Popular white vegetable.'),
(18, 'Cabbage', 'Winter', 1600, 'January', 'Leafy green vegetable.'),
(19, 'Lentil', 'pre-Winter', 1400, 'March', 'Pulse crop rich in proteins.'),
(20, 'mango', 'Summer', 5000, 'June', 'King of fruits grown in hot climates.'),
(21, 'Quinoa', 'Spring', 1800, 'August', 'High-protein grain native to Andes region.'),
(22, 'Blueberry', 'Summer', 1200, 'July', 'Temperate berry fruit not suited to BD climate.'),
(23, 'Avocado', 'Monsoon', 2500, 'October', 'Tropical fruit with high fat content.'),
(24, 'Olive', 'Autumn', 3000, 'November', 'Mediterranean oil-bearing fruit tree.'),
(25, 'Barley', 'Winter', 2000, 'April', 'Grain crop mostly grown in cooler climates.'),
(26, 'Grapes', 'Summer', 3500, 'July', 'Fruit crop grown in vineyards in dry climates.'),
(27, 'Strawberry', 'Winter', 1000, 'March', 'Delicate fruit grown in temperate zones.'),
(28, 'Lavender', 'Summer', 800, 'June', 'Aromatic herb for oil and fragrance.'),
(29, 'Almond', 'Spring', 1200, 'August', 'Nuts grown in temperate climates.'),
(30, 'Cherry', 'Spring', 1500, 'June', 'Temperate tree bearing red fruits.'),
(31, 'Kiwi', 'Autumn', 1700, 'October', 'Fuzzy fruit grown in cold climates.'),
(32, 'Saffron', 'Autumn', 500, 'November', 'Expensive spice from crocus flower.'),
(33, 'Hazelnut', 'Spring', 1400, 'August', 'Nut-bearing tree of Europe and America.'),
(34, 'Artichoke', 'Winter', 900, 'March', 'Edible flower bud grown in mild winters.'),
(35, 'Cranberry', 'Autumn', 1600, 'November', 'Berry crop from cool, acidic wetlands.'),
(36, 'banana', 'spring', 700, 'June', 'a popular fruit, often consumed fresh, but also us'),
(37, 'k', '\'spring\'', 700, '\'March\'', '.i like it\''),
(38, 'jackfruit', 'summer', 100, 'April', 'yummy'),
(39, 'catcus', 'winter', 200, 'October', 'for hot weather'),
(40, 'bloodorange', 'annua', 400, 'October', 'red like blood'),
(41, 'lichi', 'summer', 3000, 'july', 'very juicy .'),
(42, 'lol', 'Unknown', 0, NULL, 'No description');

-- --------------------------------------------------------

--
-- Table structure for table `crop_bd`
--

CREATE TABLE `crop_bd` (
  `CBD_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `growsINBD` varchar(50) NOT NULL,
  `grows_in_which_country` varchar(50) NOT NULL,
  `national_price` double NOT NULL,
  `ideal_soil` varchar(255) NOT NULL,
  `ideal_rainfall` varchar(255) DEFAULT NULL,
  `ideal_temp` float NOT NULL,
  `tips` varchar(255) DEFAULT NULL,
  `tipstogrowinbd` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_bd`
--

INSERT INTO `crop_bd` (`CBD_id`, `crop_id`, `growsINBD`, `grows_in_which_country`, `national_price`, `ideal_soil`, `ideal_rainfall`, `ideal_temp`, `tips`, `tipstogrowinbd`) VALUES
(21, 1, '1', 'Bangladesh', 28.5, 'Clayey loam', '1200–1600 mm', 28, 'Use early transplanting in puddled fields.', 'Not applicable'),
(22, 2, '1', 'Bangladesh', 30, 'Loamy soil', '1000–1200 mm', 20, 'Ensure weed control at 30 days.\r\nSow seeds in rows in cool dry weather', 'NoT applicable'),
(23, 3, '1', 'Bangladesh', 32, 'Sandy loam', '600–900 mm', 27, 'Fertilize after rainfall.Plant during monsoon for better yield', '\r\nNot appplicaple'),
(24, 4, '2', 'Bangladesh', 20, 'Alluvial soil', '1500–2000 mm', 25, 'NOT applicable', 'Requires high humidity and retting process.'),
(25, 5, '1', 'Bangladesh', 25, 'Sandy soil', '600–800 mm', 15, 'Irrigate every 10 days for good tubers.Use disease-free seed potatoes and plant in furrows', 'NOT applicable'),
(26, 6, '2', 'Brazil', 60, 'Silty loam', '1200–2500 mm', 27.5, 'Not appplicaple', 'Needs 12–18 months to mature.'),
(27, 7, '1', 'Bangladesh', 22.5, 'Loamy', '500–1000 mm', 18, 'Harvest when pods are dry.Broadcast before late autumn for best oil yield', 'Not applicable.'),
(28, 8, '1', 'Bangladesh', 18, 'Sandy loam', '500–800 mm', 26, 'Use bamboo support for climbing types.Plant in well-drained beds, water regularly', 'not applicable'),
(29, 9, '1', 'Bangladesh', 17, 'Loamy', '600–900 mm', 27, 'Control pests with neem spray.Use raised beds for better drainage', 'not applicable'),
(30, 10, '1', 'Bangladesh', 20, 'Well-drained loam', '500–800 mm', 26.5, 'Avoid excess nitrogen.', 'Use drip irrigation to avoid root rot'),
(31, 11, '1', 'Bangladesh', 26, 'Well-drained loam', '400–800 mm', 21, 'Keep in dry storage.', 'Use organic fertilizer and avoid waterlogging'),
(32, 12, '1', 'Bangladesh', 24, 'Sandy loam', '300–600 mm', 22, 'Use healthy bulbs.', 'Avoid overhead watering to prevent rot'),
(33, 13, '1', 'Bangladesh', 21.5, 'Light sandy soil', '400–600 mm', 23, 'Plant on ridges.', 'Ensure proper drying after harvest'),
(34, 14, '2', 'China', 18.5, 'Well-drained loam', '500–700 mm', 26, 'Not applicable', '\r\nAvoid heavy rainfall to prevent seed rot.'),
(35, 15, '1', 'Bangladesh', 29, 'Loamy soil', '600–900 mm', 25, 'Ensure full sun exposure.Grow with 60 cm spacing between plants', 'not apllicable'),
(36, 16, '1', 'Bangladesh', 20, 'Sandy loam', '300–600 mm', 18, 'Keep moist soil during growth.Thin seedlings to avoid crowding', 'not applicable'),
(37, 17, '2', 'Italy', 23.5, 'Sandy loam', '800–1000 mm', 16, 'not apllicatle', 'Cover during summer.'),
(38, 18, '1', 'Bangladesh', 18, 'Fertile loamy', '600–800 mm', 17, 'Use mulch to retain moisture.Plant in rows and water deeply', 'not applicable'),
(39, 19, '1', 'Bangladesh', 16.5, 'Clay loam', '400–700 mm', 20, 'Rotate with cereals.Plant in winter and inoculate seeds', 'not applicable'),
(40, 20, '2', 'India', 55, 'Loamy', '750–1000 mm', 30, 'Not applicable', '\r\nRequires dry spells before harvest.'),
(41, 21, '2', 'Peru, Bolivia', 450, 'Loamy', '500-1000 mm', 15, 'not apllicable', 'Difficult to grow due to low altitude.Requires cool climate and high altitudes.'),
(42, 22, '2', 'USA, Canada', 700, 'Acidic sandy', '600-1200 mm', 10, 'not apllicable', 'Not suitable due to warm winters.Needs chill hours and acidic soil.'),
(43, 23, '2', 'Mexico, Chile', 1200, 'Well-drained', '1000 mm', 20, 'not apllicable', 'Struggles in BD humidity.Needs consistent warm temperatures.'),
(44, 24, '2', 'Spain, Italy, Greece', 900, 'Calcareous', '400-600 mm', 15, 'not applicable', 'Not ideal due to high rainfall.Requires dry summers and mild winters.'),
(45, 25, '2', 'Russia, Canada', 300, 'Sandy loam', '450 mm', 5, 'not applicable', 'BD is too warm.Prefers cold and dry climates.'),
(46, 26, '2', 'France, USA', 1500, 'Loamy, well-drained', '600-800 mm', 15, 'not applicable', 'High humidity affects fruit quality.Needs dry climate and sloped land.'),
(47, 27, '2', 'UK, USA', 600, 'Loamy, acidic', '700-1000 mm', 10, 'not applicable', 'Hard to fruit in hot weather.Prefers cold temperatures.'),
(48, 28, '2', 'France, Italy', 500, 'Sandy, well-drained', '400-600 mm', 10, 'not applicable', 'Too humid in BD.Needs dry heat and good airflow.'),
(49, 29, '2', 'USA, Spain', 1000, 'Loamy', '500-700 mm', 15, 'not applicable', 'BD winters not cold enough.Chill hours essential.'),
(50, 30, '2', 'USA, Turkey', 800, 'Loamy', '600 mm', 10, 'not applicable', 'Not possible due to lack of chill.Needs winter dormancy.'),
(51, 31, '2', 'New Zealand, Italy', 950, 'Loamy, well-drained', '1000 mm', 10, 'not applicable', 'Heat stress common.Requires winter chill.'),
(52, 32, '2', 'Iran, Spain', 2500, 'Well-drained sandy', '400 mm', 10, 'not applicable', 'Too moist for healthy growth.Grows from bulbs in cold climates.'),
(53, 33, '2', 'Turkey, USA', 1400, 'Loamy', '600 mm', 15, 'not applicable', 'Not ideal in high rainfall areas.Needs well-drained soil.'),
(54, 34, '2', 'Italy, Spain', 800, 'Sandy loam', '500 mm', 10, 'not applicable', 'Not suited for tropical regions.Grows well in mild climates.'),
(55, 35, '2', 'USA, Canada', 1200, 'Loamy', '700 mm', 5, 'not applicable', 'Requires a cold, acidic environment.Prefers cold and moist conditions.'),
(56, 41, '1', 'Bangladesh', 0, 'Loamy soi', '', 1200, 'Ensure weed control at 30 days', 'not applicable');

-- --------------------------------------------------------

--
-- Table structure for table `crop_type`
--

CREATE TABLE `crop_type` (
  `type_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `species` varchar(100) NOT NULL,
  `price` double NOT NULL,
  `grain_size` varchar(50) NOT NULL,
  `grain_color` varchar(50) NOT NULL,
  `disease` varchar(255) NOT NULL,
  `strach_content` float NOT NULL,
  `protein_content` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crop_type`
--

INSERT INTO `crop_type` (`type_id`, `crop_id`, `type_name`, `species`, `price`, `grain_size`, `grain_color`, `disease`, `strach_content`, `protein_content`) VALUES
(1, 1, 'Hybrid', 'Oryza sativa', 35.5, 'Medium', 'Golden', 'None', 15.2, 9.8),
(2, 2, 'Desi', 'Triticum aestivum', 28, 'Large', 'White', 'Blight', 12.7, 10.2),
(3, 3, 'Hybrid', 'Zea mays', 22, 'Small', 'Yellow', 'Wilt', 18.5, 8.4),
(4, 4, 'Local', 'Corchorus olitorius', 18, 'Medium', 'Brown', 'Rust', 10, 7.5),
(5, 5, 'Hybrid', 'Solanum tuberosum', 25, 'Large', 'Red', 'None', 20.3, 11.6),
(6, 6, 'Desi', 'Saccharum officinarum', 45, 'Small', 'Purple', 'Mildew', 13.4, 8.1),
(7, 7, 'Local', 'Brassica juncea', 32, 'Medium', 'Green', 'Root rot', 17, 9),
(8, 8, 'Hybrid', 'Solanum lycopersicum', 30, 'Large', 'Orange', 'None', 22.1, 10.9),
(9, 9, 'Desi', 'Solanum melongena', 20, 'Small', 'Black', 'Wilt', 11.5, 6.3),
(10, 10, 'Hybrid', 'Capsicum annuum', 24, 'Medium', 'Golden', 'Blight', 14.6, 8.7),
(11, 11, 'Local', 'Allium cepa', 26, 'Large', 'Yellow', 'None', 19.2, 10),
(12, 12, 'Desi', 'Allium sativum', 23, 'Small', 'White', 'Rust', 13.3, 7.9),
(13, 13, 'Hybrid', 'Arachis hypogaea', 34, 'Medium', 'Red', 'Mildew', 16.1, 9.5),
(14, 14, 'Local', 'Sesamum indicum', 36, 'Large', 'Brown', 'None', 21, 11.1),
(15, 15, 'Hybrid', 'Helianthus annuus', 33, 'Small', 'Green', 'Root rot', 12.2, 6.8),
(16, 16, 'Desi', 'Daucus carota', 21, 'Medium', 'Golden', 'Wilt', 15.4, 8.3),
(17, 17, 'Local', 'Brassica oleracea', 20, 'Large', 'Orange', 'Blight', 18, 9.9),
(18, 18, 'Hybrid', 'Brassica oleracea', 19, 'Small', 'Purple', 'Rust', 11.8, 6.5),
(19, 19, 'Desi', 'Lens culinaris', 27, 'Medium', 'White', 'None', 16.9, 10.6),
(20, 20, 'Local', 'Mangifera indica', 40, 'Large', 'Black', 'Mildew', 20.7, 12),
(22, 1, 'Desi', 'Oryza sativa - Desi', 32, 'Medium', 'Red', 'Wilt', 14.5, 8.6),
(23, 1, 'Local', 'Oryza sativa - Local', 30, 'Large', 'Green', 'None', 19.8, 11.3),
(24, 2, 'Hybrid', 'Triticum aestivum - Hybrid', 29.5, 'Small', 'Orange', 'Rust', 13.1, 7.7),
(26, 2, 'Local', 'Triticum aestivum - Local', 26.5, 'Large', 'White', 'Wilt', 21.5, 10.8),
(27, 41, 'local', 'Litchi chinensis', 0, 'Small', 'Red', 'None', 10, 5.5),
(28, 41, 'hybrid', 'Litchi chinensis Hak', 0, '', '', '', 0, 0),
(29, 38, 'hybrid', 'khfwd', 60, 'small', 'red', 'crown rust', 5.7, 9);

-- --------------------------------------------------------

--
-- Table structure for table `disease_info`
--

CREATE TABLE `disease_info` (
  `id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `symptoms` text NOT NULL,
  `min_days` int(11) NOT NULL,
  `max_days` int(11) NOT NULL,
  `disease_name` varchar(255) NOT NULL,
  `medicine` text DEFAULT NULL,
  `home_remedy` text DEFAULT NULL,
  `doctor_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disease_info`
--

INSERT INTO `disease_info` (`id`, `crop_id`, `type_id`, `symptoms`, `min_days`, `max_days`, `disease_name`, `medicine`, `home_remedy`, `doctor_contact`) VALUES
(38, 1, 1, 'Yellowing tips and leaf curling with dry patches near the stem base.', 4, 10, 'Chlorofade Syndrome', 'Multi-vitamin foliar spray', 'Banana peel tea', 'plantdoc1@example.com'),
(39, 2, 2, 'Brown lesions on leaves, stem blight, reduced grain fill.', 7, 15, 'Blight', 'Copper oxychloride', 'Turmeric and water spray', 'plantdoc2@example.com'),
(40, 3, 3, 'Sudden leaf drooping, root rot, poor cob formation.', 5, 12, 'Wilt', 'Trichoderma solution', 'Ginger juice in soil', 'plantdoc3@example.com'),
(41, 4, 4, 'Rusty orange powder on underside of leaves, brittle stems.', 6, 14, 'Rust', 'Mancozeb spray', 'Aloe vera spray', 'plantdoc4@example.com'),
(42, 5, 5, 'Soggy tuber texture, brown circular spots on foliage.', 3, 8, 'Stem Soft Rot', 'Carbendazim powder', 'Garlic bulb tea', 'plantdoc5@example.com'),
(43, 6, 6, 'Powdery layer on leaves, stunted growth, leaf curling.', 8, 16, 'Mildew', 'Sulfur dust', 'Baking soda and vinegar rinse', 'plantdoc6@example.com'),
(44, 7, 7, 'Soft and decaying roots, yellow leaves, foul odor near base.', 5, 11, 'Root rot', 'Fungicide drench', 'Charcoal ash with cow urine', 'plantdoc7@example.com'),
(45, 8, 8, 'Spots on fruit skin, leaf yellowing, delayed ripening.', 4, 9, 'Sunburn Leaf Taint', 'Bordeaux mixture', 'Chili and garlic solution', 'plantdoc8@example.com'),
(46, 9, 9, 'Wilting stems, dry leaves, purple streaks on stalks.', 6, 13, 'Wilt', 'Streptomycin sulphate', 'Neem and onion blend', 'plantdoc9@example.com'),
(47, 10, 10, 'Brown dry edges on leaves, malformed fruits, leaf drop.', 5, 10, 'Blight', 'Copper sulfate', 'Clove oil spray', 'plantdoc10@example.com'),
(48, 11, 11, 'Mushy bulbs, yellowing tips, sulfuric smell from base.', 4, 8, 'Fiber Wilting Syndrome', 'Tebuconazole', 'Mustard oil and ash mix', 'plantdoc11@example.com'),
(49, 12, 12, 'Leaf spots with black centers, drying leaves, brittle stems.', 7, 12, 'Rust', 'Tridemorph fungicide', 'Fenugreek seed water', 'plantdoc12@example.com'),
(50, 13, 13, 'Fuzzy white patches on lower leaves, nut shrinkage.', 6, 11, 'Mildew', 'Milk spray (1:10 dilution)', 'Basil oil and water blend', 'plantdoc13@example.com'),
(51, 14, 14, 'Seed pods discoloration, leaf tip burning, brittle stems.', 3, 9, 'Root Lethargy Disorder', 'Azoxystrobin spray', 'Cinnamon powder solution', 'plantdoc14@example.com'),
(52, 15, 15, 'Rotting base, yellow outer leaves, delayed flower development.', 5, 13, 'Root rot', 'Soil sterilizer treatment', 'Potato peel decoction', 'plantdoc15@example.com'),
(53, 16, 16, 'Orange streaks on leaves, soft roots, underdeveloped carrots.', 6, 14, 'Wilt', 'Propiconazole drench', 'Marigold flower compost tea', 'plantdoc16@example.com'),
(54, 17, 17, 'Patchy yellow spots, leaf curling, weakened stems.', 4, 10, 'Blight', 'Captan fungicide', 'Garlic and lime spray', 'plantdoc17@example.com'),
(55, 18, 18, 'Rust-colored pustules on inner leaves, foul scent from base.', 7, 12, 'Rust', 'Mancozeb with zinc', 'Papaya leaf extract', 'plantdoc18@example.com'),
(56, 19, 19, 'Leaf curling, pod discoloration, poor flowering.', 3, 7, 'Photosynth Block Virus', 'Manganese tonic', 'Curry leaf and turmeric soak', 'plantdoc19@example.com'),
(57, 20, 20, 'White fuzzy patches on leaves, sap oozing from fruit.', 5, 11, 'Mildew', 'Horticultural oil', 'Honey water wash', 'plantdoc20@example.com'),
(58, 1, 22, 'Dull leaf color, early yellowing, thin stalks.', 4, 8, 'Wilt', 'Bacillus subtilis suspension', 'Potato starch mix', 'plantdoc21@example.com'),
(59, 1, 23, 'Cracked stems, powder on underside of leaves.', 6, 12, 'Tuber Black Vein', 'Trifloxystrobin', 'Tulsi extract water', 'plantdoc22@example.com'),
(60, 2, 24, 'Orange blotches on grain heads, shriveled kernels.', 7, 14, 'Rust', 'Metalaxyl', 'Mint water spray', 'plantdoc23@example.com'),
(61, 2, 26, 'Yellowing leaves, weak root system, broken stalks.', 5, 11, 'Wilt', 'Fluazinam-based drench', 'Neem cake infusion', 'plantdoc24@example.com'),
(62, 41, 27, 'Dry fruit flesh, shriveled skin, early fruit drop.', 6, 10, 'Grain Shrivel Mosaic', 'Vitamin B1 foliar spray', 'Rice water foliar feed', 'plantdoc25@example.com'),
(63, 41, 28, 'Fruit cracking, leaf spotting, pink mold presence.', 7, 13, 'Foliar Crinkle Blight', 'Botanical extract blend', 'Coriander juice spray', 'plantdoc26@example.com'),
(64, 38, 29, 'crown,rust', 3, 5, 'leafeater', 'texexrl', 'use egges', 'plant@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `email_otps`
--

CREATE TABLE `email_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `purpose` enum('signup','login') NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `eq_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `cost` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`eq_id`, `name`, `cost`) VALUES
(1, 'axe', 20),
(2, 'axe', 20),
(3, 'rax', 200),
(4, 'shovel', 200),
(5, 'shovel', 300),
(6, 'shovel', 300),
(7, 'shovel', 700),
(8, 'shovel', 900),
(9, 'shovel', 1200),
(10, 'shovel', 1200),
(11, 'shovel', 200),
(12, 'shovel', 20),
(13, 'shovel', 23),
(14, 'shovel', 23),
(15, 'shovel', 23),
(16, 'shovel', 23),
(17, 'shovel', 20),
(18, 'hammer', 10),
(19, 'hammer', 20),
(20, 'hammer', 20);

-- --------------------------------------------------------

--
-- Table structure for table `expens`
--

CREATE TABLE `expens` (
  `e_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `e_type` varchar(50) NOT NULL,
  `salesamt` double NOT NULL,
  `amtspe` double NOT NULL,
  `profit` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmer`
--

CREATE TABLE `farmer` (
  `f_id` int(11) NOT NULL,
  `f_name` varchar(50) NOT NULL,
  `location` varchar(50) DEFAULT NULL,
  `farm_size` double DEFAULT NULL,
  `crop_sp` varchar(50) DEFAULT NULL,
  `u_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer`
--

INSERT INTO `farmer` (`f_id`, `f_name`, `location`, `farm_size`, `crop_sp`, `u_id`) VALUES
(5, 'niama', 'Rajshahi', 34, 'wheat', 15),
(6, 'tamim', 'Chittagong', 34, 'manga', 17),
(7, 'jhorna', 'Dhaka', 100, 'blueberry', 18),
(18, 'labiba', 'dhaka', 4555, 'blueberry', 27),
(19, 'mona', 'dhaka', 3000, 'bueberry', 28),
(20, 'maysa', 'dhaka', 2000, 'mango', 29),
(21, 'qk', 'dhaka', 4555, 'manga', 30),
(22, 'zoha', 'dhaka', 100, 'mango', 31),
(23, 'aq', 'dhaka', 5000, 'manga', 34),
(24, 'Rakib Hossain', 'Alaolpur, Goshairhat, Shariatur, Dhaka, Bangladesh', 3, 'Don\'t know', 44);

-- --------------------------------------------------------

--
-- Table structure for table `farmer_crop`
--

CREATE TABLE `farmer_crop` (
  `fc_id` int(11) NOT NULL,
  `f_id` int(11) DEFAULT NULL,
  `crop_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `crop_name` varchar(50) DEFAULT NULL,
  `planted_at` date DEFAULT NULL,
  `Harvested_time` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer_crop`
--

INSERT INTO `farmer_crop` (`fc_id`, `f_id`, `crop_id`, `type_id`, `crop_name`, `planted_at`, `Harvested_time`) VALUES
(37, 18, 20, 20, 'mango', '2025-05-11', '2025-12-25'),
(38, 18, 1, 23, 'rice', '2025-06-11', '2025-06-11'),
(39, 18, 38, 29, 'jackfruit', '2025-06-19', '2025-08-19'),
(40, 18, 1, 22, 'rice', '2025-05-23', '2025-07-23'),
(43, 18, 2, 26, 'wheat', '2025-06-30', '2025-08-30'),
(44, 18, 20, 20, 'mango', '2025-11-26', '2026-01-26'),
(45, 18, 1, 22, 'rice', '2025-12-25', '2026-03-25');

-- --------------------------------------------------------

--
-- Table structure for table `govt_scheme`
--

CREATE TABLE `govt_scheme` (
  `scheme_id` int(11) NOT NULL,
  `scheme_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `eligibility` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `apply_method` varchar(100) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `status` enum('Active','Ended','Upcoming') DEFAULT NULL,
  `source_link` varchar(255) DEFAULT NULL,
  `success_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `govt_scheme`
--

INSERT INTO `govt_scheme` (`scheme_id`, `scheme_name`, `description`, `start_date`, `end_date`, `eligibility`, `benefits`, `apply_method`, `contact_info`, `region`, `status`, `source_link`, `success_status`) VALUES
(1, 'Ektee Bari Ektee Khamar', 'A rural development program aimed at reducing poverty by promoting farming and welfare through small loans and savings.', '2009-01-01', '2020-12-31', 'Rural households, especially women and small farmers.', 'Matching savings, small loans, and support for income-generating activities.', 'Through Village Development Associations.', 'Local Union Parishad offices.', 'Nationwide', 'Ended', 'https://en.wikipedia.org/wiki/Ektee_Bari_Ektee_Khamar_project', 'Successful'),
(2, 'National Agricultural Technology Program (NATP)', 'Boosted agricultural productivity through research-extension-farmer linkages and modern input delivery.', '2008-01-01', '2021-12-31', 'Small and marginal farmers across 57 districts.', 'Modern seeds, training, research support, and better input supply.', 'Apply via Upazila Agricultural Office under NATP coordination.', 'Ministry of Agriculture, NATP Office.', '57 districts', 'Ended', 'https://projects.worldbank.org/en/projects-operations/project-detail/P149553', 'Successful'),
(3, 'Agri-input Assistance for Small & Marginal Farmers', 'Provides free agricultural inputs to disaster-affected farmers to ensure food security.', '2010-01-01', NULL, 'Small and marginal farmers in disaster-prone and flood-hit areas.', 'Free seeds, fertilizers, and sometimes financial aid.', 'Apply through Department of Agricultural Extension offices.', 'Upazila Agriculture Office, DAE.', 'Flood/cyclone affected zones', 'Active', 'https://dae.portal.gov.bd/', 'Successful'),
(4, 'Smart Agriculture Card Pilot Program', 'Introduced digital cards to provide targeted subsidies and digital agri services to farmers.', '2018-01-01', '2022-12-31', 'Digitally registered farmers in pilot districts.', 'Fertilizer subsidies, crop insurance, digital advisory services.', 'Through Upazila Agri Offices during registration drive.', 'DAE Smart Card Cell.', '10 pilot districts', 'Ended', 'https://dae.portal.gov.bd/', 'Successful'),
(5, 'Krishak Smart Card Program', 'A government initiative to provide farmers with smart cards for accessing subsidies and credit support.', '2023-04-01', '2028-03-31', 'Registered farmers across Bangladesh.', 'Access to input subsidies, credit facilities, and agricultural services.', 'Registration through local agricultural offices.', 'Department of Agricultural Extension.', 'Nationwide', 'Active', 'https://www.thedailystar.net/news/bangladesh/agriculture/news/agri-ministry-takes-tk-7214cr-project-3284211', NULL),
(6, 'Tk 7,000 Crore Scheme for Farm Sector', 'A government project to boost the commercialization of farming and accelerate agricultural exports by expanding safe food production.', '2023-10-11', '2028-10-10', 'Farmers involved in fruit and vegetable cultivation.', 'Support for expanding cultivation following Good Agricultural Practices (GAPs).', 'Through local agricultural offices.', 'Department of Agricultural Extension.', 'Nationwide', 'Active', 'https://www.thedailystar.net/business/economy/news/tk-7000cr-scheme-farm-sector-kicks-3440096', NULL),
(7, 'Char Development and Settlement Project (CDSP)', 'A project aimed at improving the livelihoods of people living in newly emerged char lands through infrastructure development and settlement support.', '1994-01-01', NULL, 'Residents of char areas.', 'Housing, agricultural support, and infrastructure development.', 'Through local project offices.', 'CDSP Headquarters, Dhaka.', 'Char areas across Bangladesh', 'Active', 'https://en.wikipedia.org/wiki/Char_Development_and_Settlement_Project', NULL),
(8, 'Agriculture Microfinance Program by ARS Bangladesh', 'A program providing affordable loans to small farmers and agricultural workers for farming inputs and equipment.', '2015-01-01', NULL, 'Smallholder farmers, tenant farmers, and landless laborers.', 'Loans for seeds, fertilizers, pesticides, irrigation, and machinery.', 'Application through ARS Bangladesh offices.', 'ARS Bangladesh, Dhaka.', 'Nationwide', 'Active', 'https://arsbd.org/agriculture-microfinance-program/', NULL),
(9, 'Dairy and Livestock Development Project (DLDP) by DISA', 'A project to stimulate growth and enable sustainable development of livestock value chains in Bangladesh.', '2010-01-01', NULL, 'Marginalized farmers interested in dairy and livestock farming.', 'Loans, training, veterinary support, and insurance for livestock.', 'Application through DISA offices.', 'DISA Headquarters, Dhaka.', 'Nationwide', 'Active', 'https://www.disabd.org/agrilivestock.php', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `import_export`
--

CREATE TABLE `import_export` (
  `ex_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `export_country` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `import_country` varchar(50) NOT NULL,
  `ex_quantiy` double NOT NULL,
  `exp_date` date NOT NULL,
  `port_name` varchar(50) NOT NULL,
  `transport` varchar(50) NOT NULL,
  `price_perunit_ex` double NOT NULL,
  `total_cost` double NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ip_blacklist`
--

CREATE TABLE `ip_blacklist` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `u_id` int(11) DEFAULT NULL,
  `status` enum('banned') NOT NULL DEFAULT 'banned',
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ip_details`
--

CREATE TABLE `ip_details` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `u_id` int(11) DEFAULT NULL,
  `login_time` int(11) NOT NULL,
  `strike_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('ok','temp_block','blacklisted') NOT NULL DEFAULT 'ok'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ip_details`
--

INSERT INTO `ip_details` (`id`, `ip`, `u_id`, `login_time`, `strike_count`, `status`) VALUES
(29, '::1', NULL, 1768370944, 0, 'ok'),
(30, '::1', NULL, 1768370976, 0, 'ok'),
(31, '::1', NULL, 1768719811, 0, 'ok'),
(32, '::1', NULL, 1768802011, 0, 'ok'),
(33, '::1', NULL, 1768802039, 0, 'ok'),
(34, '::1', NULL, 1768804642, 0, 'ok'),
(35, '::1', NULL, 1769656553, 0, 'ok'),
(36, '::1', 48, 1770183322, 0, 'ok');

-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

CREATE TABLE `loan` (
  `l_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `loan_amt` int(11) NOT NULL,
  `original_loan_amt` double NOT NULL DEFAULT 0,
  `i_rate` double DEFAULT 0,
  `l_reason` varchar(50) DEFAULT NULL,
  `a_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('active','paid') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan`
--

INSERT INTO `loan` (`l_id`, `f_id`, `loan_amt`, `original_loan_amt`, `i_rate`, `l_reason`, `a_date`, `due_date`, `status`) VALUES
(2, 19, 5000, 0, 0, 'investment', '2025-05-06', '2025-08-06', 'active'),
(3, 23, 500, 0, 0, 'poor', '2025-05-29', '2025-08-29', 'active'),
(4, 18, 0, 0, 0, 'poor', '2025-06-08', '2025-09-08', 'paid'),
(5, 18, 0, 100, 0, 'poor', '2025-06-20', '2025-09-20', 'paid'),
(6, 18, 0, 500, 0, 'poor', '2025-06-30', '2025-09-30', 'paid'),
(7, 18, 0, 500, 0, 'poor', '2025-11-19', '2026-02-19', 'paid'),
(8, 18, 0, 1000, 0, 'poor', '2025-12-25', '2026-03-25', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `loan_installments`
--

CREATE TABLE `loan_installments` (
  `installment_id` int(11) NOT NULL,
  `l_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount_due` int(11) NOT NULL,
  `amount_paid` int(11) DEFAULT 0,
  `paid_date` date DEFAULT NULL,
  `late_fee` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_installments`
--

INSERT INTO `loan_installments` (`installment_id`, `l_id`, `due_date`, `amount_due`, `amount_paid`, `paid_date`, `late_fee`) VALUES
(4, 2, '2025-06-05', 1500, 0, NULL, 0),
(5, 2, '2025-07-05', 1500, 0, NULL, 0),
(6, 2, '2025-08-05', 2000, 0, NULL, 0),
(7, 3, '2025-06-29', 150, 0, NULL, 0),
(8, 3, '2025-07-29', 150, 0, NULL, 0),
(9, 3, '2025-08-29', 200, 0, NULL, 0),
(10, 4, '2025-07-08', 150, 0, NULL, 0),
(11, 4, '2025-08-08', 150, 0, NULL, 0),
(12, 4, '2025-09-08', 200, 0, NULL, 0),
(13, 5, '2025-07-20', 30, 0, NULL, 0),
(14, 5, '2025-08-20', 30, 0, NULL, 0),
(15, 5, '2025-09-20', 40, 0, NULL, 0),
(16, 6, '2025-07-30', 150, 0, NULL, 0),
(17, 6, '2025-08-30', 150, 0, NULL, 0),
(18, 6, '2025-09-30', 200, 0, NULL, 0),
(19, 7, '2025-12-19', 150, 0, NULL, 0),
(20, 7, '2026-01-19', 150, 0, NULL, 0),
(21, 7, '2026-02-19', 200, 0, NULL, 0),
(22, 8, '2026-01-25', 300, 0, NULL, 0),
(23, 8, '2026-02-25', 300, 0, NULL, 0),
(24, 8, '2026-03-25', 400, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `local_price`
--

CREATE TABLE `local_price` (
  `l_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `crop_name` varchar(50) NOT NULL,
  `type` enum('Local','Desi','Hybide') NOT NULL,
  `local_price` decimal(10,2) NOT NULL,
  `update_time` date NOT NULL,
  `status` enum('active','inactive','sold') NOT NULL DEFAULT 'active',
  `region` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `local_price`
--

INSERT INTO `local_price` (`l_id`, `u_id`, `crop_name`, `type`, `local_price`, `update_time`, `status`, `region`) VALUES
(1, 35, 'Rice', 'Local', 66.00, '2025-10-01', 'active', 'Dhaka'),
(2, 35, 'Rice', 'Desi', 81.50, '2024-04-02', 'active', 'Dhaka'),
(3, 35, 'Rice', 'Hybide', 102.00, '2025-04-14', 'active', 'Dhaka'),
(5, 35, 'Potato', 'Desi', 40.00, '2020-04-03', 'active', 'Khulna'),
(17, 35, 'Wheat', 'Local', 30.00, '1990-06-02', 'active', 'Rajshahi'),
(18, 35, 'Mango', 'Desi', 45.00, '2021-10-17', 'active', 'Dhaka'),
(19, 35, 'Mango', 'Desi', 70.00, '2022-06-16', 'active', 'Rajshahi'),
(20, 35, 'Onion', 'Desi', 95.00, '2025-06-01', 'active', 'Rajshahi'),
(21, 35, 'Green chili', 'Desi', 145.00, '2025-06-07', 'active', 'Dhaka'),
(22, 35, 'Tomato', 'Desi', 126.00, '2025-06-13', 'active', 'Dhaka'),
(23, 35, 'Garlic', 'Desi', 240.00, '2024-06-05', 'active', 'Dhaka'),
(24, 35, 'Ginger', 'Desi', 130.00, '2025-06-18', 'active', 'Dhaka');

-- --------------------------------------------------------

--
-- Table structure for table `market_listing`
--

CREATE TABLE `market_listing` (
  `list_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `price` double NOT NULL,
  `l_quantity` double NOT NULL,
  `l_date` date NOT NULL,
  `l_st` varchar(50) NOT NULL,
  `crop_name` varchar(100) DEFAULT NULL,
  `crop_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `market_listing`
--

INSERT INTO `market_listing` (`list_id`, `f_id`, `price`, `l_quantity`, `l_date`, `l_st`, `crop_name`, `crop_type`) VALUES
(4, 5, 28, 400, '2024-03-18', 'available', 'Potato', 'Desi'),
(5, 5, 30, 370, '2024-04-09', 'sold', 'Potato', 'Hybrid'),
(6, 5, 26.5, 300, '2024-04-20', 'available', 'Potato', 'Local'),
(7, 6, 40, 500, '2024-05-01', 'available', 'Mango', 'Desi'),
(10, 5, 80, 500, '2024-06-02', 'available', 'Tomato', 'Desi'),
(13, 5, 30, 320, '2024-07-22', 'available', 'Potato', 'Hybrid'),
(14, 5, 26, 280, '2024-08-04', 'sold', 'Potato', 'Local'),
(17, 5, 23, 120, '2024-09-15', 'sold', 'Mango', 'Local'),
(19, 5, 60, 0, '2024-10-10', 'sold', 'Tomato', 'Local'),
(31, 19, 200, 0, '2025-12-23', 'available', 'Jackfruit', 'Local'),
(32, 7, 25, 0, '2025-06-01', 'available', 'Wheat', 'Local'),
(34, 7, 20, 100, '2025-06-14', 'available', 'Sweet potato', 'Desi'),
(36, 7, 60, 900, '2025-06-20', 'available', 'Rice', 'Desi'),
(37, 7, 35, 250, '2025-06-02', 'available', 'Potato', 'Hybrid'),
(38, 7, 28, 380, '2025-06-12', 'available', 'Potato', 'Desi'),
(39, 7, 100, 580, '2025-06-03', 'available', 'Lentil', 'Desi'),
(40, 7, 40, 700, '2025-06-05', 'available', 'Onion', 'Desi'),
(41, 7, 80, 45, '2025-05-08', 'available', 'Lentil', 'Local'),
(42, 7, 15, 0, '2025-05-02', 'available', 'Potato', 'Local'),
(43, 7, 50, 0, '2025-05-14', 'available', 'Rice', 'Local'),
(44, 7, 100, 500, '2025-05-30', 'available', 'Rice', 'Hybrid'),
(48, 7, 80, 198, '2025-06-05', 'available', 'Ginger', 'Desi'),
(49, 18, 20, 10, '2025-06-30', 'available', 'rice', 'local'),
(50, 18, 15, 10, '2025-12-25', 'available', 'rice', 'local');

-- --------------------------------------------------------

--
-- Table structure for table `national_price`
--

CREATE TABLE `national_price` (
  `n_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `crop_name` varchar(50) NOT NULL,
  `type` enum('Local','Desi','Hybide') NOT NULL,
  `national_price` decimal(10,2) NOT NULL,
  `update_time` date NOT NULL,
  `status` enum('active','inactive','sold') NOT NULL,
  `country_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `national_price`
--

INSERT INTO `national_price` (`n_id`, `u_id`, `crop_name`, `type`, `national_price`, `update_time`, `status`, `country_name`) VALUES
(1, 35, 'Rice', 'Local', 60.50, '2023-04-01', 'active', 'USA'),
(2, 35, 'Rice', 'Desi', 100.30, '2023-04-02', 'active', 'USA'),
(3, 35, 'Rice', '', 110.50, '2023-04-02', 'active', 'USA'),
(4, 35, 'Potato', 'Local', 30.00, '2020-04-03', 'active', 'India'),
(5, 35, 'Potato', 'Desi', 20.10, '2020-04-03', 'active', 'Myanmar'),
(7, 35, 'Tomato', 'Desi', 80.00, '2020-06-17', 'active', 'USA'),
(8, 35, 'Tomato', 'Local', 40.00, '2020-06-18', 'active', 'India'),
(9, 35, 'Banana', 'Desi', 66.00, '2022-06-10', 'active', 'India'),
(11, 35, 'Chinigura', 'Desi', 130.00, '2024-06-01', 'active', 'Saudi Arabia'),
(14, 35, 'Tea', 'Desi', 208.00, '2025-06-11', 'active', 'Russia'),
(15, 35, 'Jute', 'Desi', 95.00, '2025-06-19', 'active', 'USA');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `truck` varchar(3) NOT NULL DEFAULT 'No',
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_status` enum('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `delivery_location` varchar(255) DEFAULT NULL,
  `order_type` enum('crop','seed_fertilizer') NOT NULL DEFAULT 'crop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `u_id`, `list_id`, `quantity`, `truck`, `total_price`, `payment_method`, `payment_status`, `transaction_id`, `ordered_at`, `delivery_status`, `delivery_location`, `order_type`) VALUES
(5, 28, 31, 1, 'Yes', 1200.00, 'Cash on Delivery', 'Pending', NULL, '2025-05-06 05:51:03', 'Pending', NULL, 'crop'),
(6, 27, 31, 1, 'Yes', 1200.00, 'Cash on Delivery', 'Pending', NULL, '2025-05-29 15:21:07', 'Pending', NULL, 'crop'),
(7, 27, 31, 1, 'No', 200.00, 'Cash on Delivery', 'Pending', NULL, '2025-05-29 15:21:07', 'Pending', NULL, 'crop'),
(8, 27, 7, 50, 'Yes', 5000.00, 'Cash on Delivery', 'Pending', NULL, '2025-05-29 15:34:52', 'Pending', NULL, 'crop'),
(9, 27, 32, 5, 'No', 1500.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-01 07:32:47', 'Pending', NULL, 'crop'),
(10, 27, 19, 50, 'No', 1800.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-01 08:08:13', 'Pending', NULL, 'crop'),
(11, 27, 31, 1, 'Yes', 1200.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-02 04:36:37', 'Pending', NULL, 'crop'),
(12, 27, 31, 1, 'No', 200.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-02 04:59:23', 'Pending', NULL, 'crop'),
(13, 27, 19, 50, 'No', 1800.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-02 11:01:15', 'Pending', NULL, 'crop'),
(14, 27, 31, 2, 'No', 400.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-03 11:25:16', 'Pending', NULL, 'crop'),
(15, 27, 31, 1, 'Yes', 1200.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-03 11:31:21', 'Pending', NULL, 'crop'),
(16, 27, 19, 50, 'Yes', 4800.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-03 11:33:51', 'Pending', NULL, 'crop'),
(17, 27, 33, 1, 'Yes', 1050.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-08 12:45:13', 'Pending', NULL, 'crop'),
(18, 27, 33, 1, '0', 1050.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-10 08:58:28', 'Pending', NULL, 'crop'),
(19, 18, 17, 50, '0', 4150.00, 'Mobile Banking', 'Paid', 'BKASH_684d46383cbfc_7433', '2025-06-14 09:51:52', 'Pending', NULL, 'crop'),
(20, 35, 17, 50, '0', 4150.00, 'Mobile Banking', 'Paid', 'BKASH_685133686102a_4030', '2025-06-17 09:20:40', 'Pending', NULL, 'crop'),
(21, 35, 1, 20, '0', 910.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-18 18:12:18', 'Pending', 'Dhaka', 'seed_fertilizer'),
(22, 35, 14, 2, '0', 80.00, 'Mobile Banking', 'Paid', 'BKASH_685321a8d23cd_4033', '2025-06-18 20:29:28', 'Pending', 'Dhaka', 'seed_fertilizer'),
(23, 35, 5, 2, '0', 50.00, 'Mobile Banking', 'Paid', 'BKASH_685321a8d23cd_4033', '2025-06-18 20:29:28', 'Pending', 'Dhaka', 'seed_fertilizer'),
(24, 35, 14, 18, '0', 720.00, 'Mobile Banking', 'Paid', 'BKASH_68534787a354a_2256', '2025-06-18 23:11:03', 'Pending', 'Rajshahi', 'seed_fertilizer'),
(25, 35, 2, 195, '0', 11700.00, 'Mobile Banking', 'Paid', 'BKASH_68534787a354a_2256', '2025-06-18 23:11:03', 'Pending', NULL, 'seed_fertilizer'),
(26, 27, 48, 1, '0', 80.00, 'Cash on Delivery', 'Pending', NULL, '2025-06-29 18:58:08', 'Pending', NULL, 'crop');

-- --------------------------------------------------------

--
-- Table structure for table `prev_mp`
--

CREATE TABLE `prev_mp` (
  `prev_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `crop_name` varchar(100) NOT NULL,
  `type` enum('Local','Desi','Hybide') NOT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `update_time` date NOT NULL,
  `status` enum('active','inactive','sold') NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country_name` varchar(100) DEFAULT NULL,
  `source_table` enum('local_price','national_price') NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prev_mp`
--

INSERT INTO `prev_mp` (`prev_id`, `u_id`, `crop_name`, `type`, `old_price`, `update_time`, `status`, `region`, `country_name`, `source_table`, `changed_at`) VALUES
(1, 35, 'Sweet potato', 'Desi', 30.00, '2023-06-17', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 10:00:57'),
(3, 35, 'Sweet potato', 'Desi', 25.00, '2024-06-17', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 10:00:57'),
(6, 35, 'Wheat', 'Hybide', 40.00, '2023-06-02', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-17 10:22:20'),
(7, 35, 'Rice', 'Hybide', 100.00, '2023-04-02', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 10:27:18'),
(8, 35, 'Mango', 'Desi', 40.00, '2020-06-17', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 11:13:33'),
(10, 35, 'Rice', 'Local', 60.20, '2023-10-01', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 11:18:09'),
(11, 35, 'Mango', 'Desi', 42.00, '2021-10-17', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 11:19:52'),
(12, 35, 'Mango', 'Desi', 60.00, '2020-06-16', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-17 11:23:17'),
(14, 35, 'Mango', 'Desi', 68.00, '2022-06-16', 'active', 'Dhaka', NULL, 'local_price', '2025-06-17 11:28:32'),
(15, 35, 'Wheat', 'Local', 20.00, '2024-06-02', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-17 11:47:49'),
(16, 35, 'Wheat', 'Local', 30.00, '2025-06-02', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-17 11:49:21'),
(17, 35, 'Tomato', 'Desi', 50.00, '2020-06-17', 'active', NULL, 'USA', 'national_price', '2025-06-17 13:07:04'),
(18, 35, 'Tomato', 'Local', 40.00, '2020-06-18', 'active', NULL, 'India', 'national_price', '2025-06-17 14:09:03'),
(19, 35, 'Banana', 'Desi', 60.00, '2020-06-10', 'active', NULL, 'India', 'national_price', '2025-06-17 14:22:18'),
(20, 35, 'Banana', 'Desi', 65.00, '2021-06-10', 'active', NULL, 'India', 'national_price', '2025-06-17 14:33:20'),
(21, 35, 'Rice', 'Local', 66.00, '2025-10-01', 'active', 'Dhaka', NULL, 'local_price', '2025-06-22 13:32:18'),
(22, 35, 'Rice', 'Desi', 81.50, '2024-04-02', 'active', 'Dhaka', NULL, 'local_price', '2025-06-22 13:38:17'),
(23, 35, 'Onion', 'Desi', 60.00, '2024-06-01', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-22 14:00:17'),
(24, 35, 'Onion', 'Desi', 62.00, '2025-06-01', 'active', 'Rajshahi', NULL, 'local_price', '2025-06-22 14:00:52'),
(26, 35, 'Chinigura', 'Desi', 130.00, '2024-06-01', 'active', NULL, 'Saudi Arabia', 'national_price', '2025-06-29 01:50:17'),
(28, 35, 'Green chili', 'Desi', 140.00, '2024-06-07', 'active', 'Dhaka', NULL, 'local_price', '2025-06-29 01:58:30'),
(29, 35, 'Tomato', 'Desi', 120.00, '2024-06-12', 'active', 'Dhaka', NULL, 'local_price', '2025-06-29 02:01:14'),
(30, 35, 'Garlic', 'Desi', 240.00, '2024-06-05', 'active', 'Dhaka', NULL, 'local_price', '2025-06-29 02:05:11'),
(31, 35, 'Ginger', 'Desi', 130.00, '2025-06-18', 'active', 'Dhaka', NULL, 'local_price', '2025-06-29 02:05:51'),
(32, 35, 'Tomato', 'Desi', 126.00, '2025-06-13', 'active', 'Dhaka', NULL, 'local_price', '2025-06-29 04:54:50'),
(37, 35, 'Tea', 'Desi', 200.00, '2024-06-29', 'active', NULL, 'Russia', 'national_price', '2025-06-29 05:37:55'),
(38, 35, 'Tea', 'Desi', 208.00, '2025-06-11', 'active', NULL, 'Russia', 'national_price', '2025-06-29 05:45:27'),
(39, 35, 'Jute', 'Desi', 90.00, '2024-06-19', 'active', NULL, 'USA', 'national_price', '2025-06-29 05:47:09'),
(40, 35, 'Jute', 'Desi', 95.00, '2025-06-19', 'active', NULL, 'USA', 'national_price', '2025-06-29 05:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`result_id`, `user_id`, `quiz_id`, `score`, `timestamp`) VALUES
(1, 18, 1, 0, '2025-06-14 15:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `rental`
--

CREATE TABLE `rental` (
  `r_id` int(11) NOT NULL,
  `eq_id` int(11) NOT NULL,
  `f_id` int(11) DEFAULT NULL,
  `r_duration` int(11) DEFAULT 0,
  `r_amount` decimal(10,2) DEFAULT 0.00,
  `ava` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `r_condition` varchar(50) DEFAULT 'Good'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental`
--

INSERT INTO `rental` (`r_id`, `eq_id`, `f_id`, `r_duration`, `r_amount`, `ava`, `r_condition`) VALUES
(1, 1, NULL, 1, 20.00, 'No', 'Good'),
(2, 2, 7, 1, 20.00, 'No', 'Good'),
(3, 3, NULL, 1, 200.00, 'No', 'Good'),
(4, 4, 10, 1, 200.00, 'No', 'Good'),
(5, 5, 8, 1, 300.00, 'No', 'Good'),
(6, 6, 10, 1, 300.00, 'No', 'Good'),
(7, 7, NULL, 1, 700.00, 'No', 'Good'),
(8, 8, NULL, 1, 900.00, 'No', 'Good'),
(9, 9, 19, 1, 1200.00, 'No', 'Good'),
(10, 10, 18, 1, 1200.00, 'No', 'Good'),
(11, 11, 7, 1, 200.00, 'No', 'Good'),
(12, 12, 18, 1, 20.00, 'No', 'Good'),
(13, 17, 18, 1, 20.00, 'No', 'Good'),
(14, 18, 18, 2, 20.00, 'No', 'Good'),
(15, 19, 18, 2, 40.00, 'No', 'Good'),
(16, 14, 18, 2, 46.00, 'No', 'Good'),
(17, 20, 18, 2, 40.00, 'No', 'Good'),
(18, 13, 18, 2, 46.00, 'No', 'Good'),
(19, 15, 18, 3, 69.00, 'No', 'Good');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `s_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `quantity` float NOT NULL,
  `per_unit_price` float NOT NULL,
  `totalamt` int(11) NOT NULL,
  `broughtamt` int(11) NOT NULL,
  `sold_date` date NOT NULL,
  `payment_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seeds_cart`
--

CREATE TABLE `seeds_cart` (
  `cart_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `sf_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_type` enum('seed','fertilizer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_option` enum('pickup','delivery') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seeds_fertilizer`
--

CREATE TABLE `seeds_fertilizer` (
  `sf_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('seed','fertilizer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seeds_fertilizer`
--

INSERT INTO `seeds_fertilizer` (`sf_id`, `name`, `type`, `quantity`, `price`, `admin_id`) VALUES
(2, 'Hybrid Maize Seed', 'seed', 5, 60.00, 35),
(3, 'Wheat Seed', 'seed', 150, 38.75, 35),
(4, 'Potato Seed', 'seed', 100, 52.00, 35),
(5, 'DAP Fertilizer', 'fertilizer', 298, 25.00, 35),
(6, 'Urea Fertilizer', 'fertilizer', 500, 18.50, 35),
(7, 'Organic Compost', 'fertilizer', 250, 30.00, 35),
(14, 'Basmati Rice seed', 'seed', 0, 40.00, 35);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `u_id` int(11) NOT NULL,
  `u_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(10) NOT NULL,
  `address` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`u_id`, `u_name`, `email`, `password`, `role`, `address`, `phone`, `created_at`) VALUES
(35, 'jia', 'labibazoha176@gmail.com', '$2y$10$L5NFFxkFgQX21AQNCcgVZOagX7ovzJnldbhiEzXxEmP.Sb7JhSCaS', 'Admin', 'shegun bagicha', '01752993746', '2025-06-02'),
(48, 'Rakib', 'rakibraz202@gmail.com', '$2y$10$pRUHWv3DZeDIv6Q8wRicM.KFmfqeDTLrx8GLMQJJenCZ0KxLg8Wle', 'Buyer', '', '01711706306', '2026-01-29'),
(49, 'Hossain', 'rakibrazcse@gmail.com', '$2y$10$P2t8fo91DTkM2mGtjWwMJeZnLfcgXrRfTPHB9Ls5RUOSKSkXH8bVq', 'Admin', '', '01711706306', '2026-02-04');

-- --------------------------------------------------------

--
-- Table structure for table `weather`
--

CREATE TABLE `weather` (
  `w_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `temp` double NOT NULL,
  `rainfall` double NOT NULL,
  `tips` varchar(255) NOT NULL,
  `time` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weather`
--

INSERT INTO `weather` (`w_id`, `location`, `temp`, `rainfall`, `tips`, `time`) VALUES
(101, 'Mirpur', 32.5, 5, 'Water crops in the evening to avoid evaporation.', '2025-04-29'),
(102, 'Dhanmondi', 31.2, 8.5, 'Prepare for potential flooding; reinforce drainage systems.', '2025-04-29'),
(103, 'Gulshan', 33, 2.5, 'Apply mulch to crops to conserve moisture.', '2025-04-29'),
(104, 'Banani', 34.5, 1, 'Increase irrigation for heat-stressed crops.', '2025-04-29'),
(105, 'Uttara', 30, 6.5, 'Monitor crops for fungal diseases due to humidity.', '2025-04-29'),
(106, 'Mohakhali', 31.8, 9, 'Ensure soil is not waterlogged; check for root rot.', '2025-04-29'),
(107, 'Farmgate', 32.1, 7.5, 'Use pest control methods due to higher humidity levels.', '2025-04-29'),
(108, 'Baridhara', 33.4, 3.2, 'Use shade nets to protect tender crops from intense sun.', '2025-04-29'),
(109, 'Mohammadpur', 30.5, 10, 'Prepare for possible soil erosion; strengthen terraces.', '2025-04-29'),
(110, 'Tejgaon', 32, 2, 'Prune crops for better airflow and reduce disease risk.', '2025-04-29'),
(111, 'Narayanganj', 33.2, 1.5, 'Ensure adequate watering during dry conditions.', '2025-04-29'),
(112, 'Savar', 29.5, 11, 'Apply fungicide to prevent fungal growth from excessive rain.', '2025-04-29'),
(113, 'Gazipur', 28.8, 14, 'Prepare for waterlogging; improve drainage channels.', '2025-04-29'),
(114, 'Tongi', 30.2, 5.5, 'Monitor for pests and insects attracted by warm, wet conditions.', '2025-04-29'),
(115, 'Ashulia', 31, 3, 'Increase irrigation to keep soil moist during dry periods.', '2025-04-29'),
(116, 'Khilgaon', 30.5, 8.5, 'Maintain soil health with organic compost due to humidity.', '2025-04-29'),
(117, 'Shahbagh', 32.8, 6, 'Check for signs of wilting; water crops regularly.', '2025-04-29'),
(118, 'Motijheel', 31.9, 7.8, 'Harvest early to avoid damage from incoming storms.', '2025-04-29'),
(119, 'Badda', 32.3, 3.5, 'Use windbreaks to protect crops from strong winds.', '2025-04-29'),
(120, 'Jatrabari', 31.5, 9, 'Fertilize crops to boost growth during wet conditions.', '2025-04-29'),
(121, 'Chittagong', 29.5, 15, 'Increase drainage to prevent crop drowning in excessive rain.', '2025-04-29'),
(122, 'Cox\'s Bazar', 28, 20, 'Secure crops from storm damage and excessive rain.', '2025-04-29'),
(123, 'Sylhet', 27.5, 18, 'Monitor for crop diseases due to high rainfall.', '2025-04-29'),
(124, 'Comilla', 30, 9.5, 'Check crops for root rot in areas with poor drainage.', '2025-04-29'),
(125, 'Rajshahi', 36, 1, 'Increase watering for heat-stressed crops.', '2025-04-29'),
(126, 'Rangpur', 25.5, 11.5, 'Protect soil from erosion due to heavy rainfall.', '2025-04-29'),
(127, 'Khulna', 31.2, 7, 'Use irrigation systems to water crops during dry spells.', '2025-04-29'),
(128, 'Barisal', 30.7, 8, 'Monitor crops for pests that thrive in wet conditions.', '2025-04-29'),
(129, 'Jessore', 32.5, 6.5, 'Ensure proper irrigation for crops during the dry season.', '2025-04-29'),
(130, 'Mymensingh', 29.8, 9, 'Apply mulch to conserve soil moisture.', '2025-04-29'),
(131, 'Noakhali', 30, 12, 'Prepare for heavy rain; prevent crop damage by reinforcing soil barriers.', '2025-04-29'),
(132, 'Pabna', 32.2, 2, 'Water crops in the early morning to prevent excessive evaporation.', '2025-04-29'),
(133, 'Patuakhali', 29, 13.5, 'Check for soil erosion; secure crops from excessive rain.', '2025-04-29'),
(134, 'Bhola', 28.5, 14, 'Ensure drainage to prevent crops from flooding.', '2025-04-29'),
(135, 'Bogra', 30.4, 5, 'Use pest control methods as humidity increases.', '2025-04-29'),
(136, 'Kushtia', 31.8, 7.5, 'Increase irrigation and mulch crops to protect them from dry conditions.', '2025-04-29'),
(137, 'Narsingdi', 30.6, 9.5, 'Maintain soil fertility by adding compost.', '2025-04-29'),
(138, 'Lakshmipur', 29.5, 10.2, 'Be prepared for wet weather; maintain soil structure.', '2025-04-29'),
(139, 'Dinajpur', 27.8, 11.7, 'Increase irrigation during periods of low rainfall.', '2025-04-29'),
(140, 'Chandpur', 29, 13, 'Check crops for signs of waterlogging.', '2025-04-29'),
(141, 'Madaripur', 28.5, 12.5, 'Provide windbreaks to prevent damage from strong winds.', '2025-04-29'),
(142, 'Shariatpur', 29.7, 11.5, 'Keep the soil moist to prevent crops from drying out.', '2025-04-29'),
(143, 'Sunamganj', 26.5, 19, 'Watch for fungal infections due to excessive rain.', '2025-04-29'),
(144, 'Habiganj', 27.5, 17, 'Prepare for wet weather; reinforce crop protection structures.', '2025-04-29'),
(145, 'Netrokona', 28.2, 14.5, 'Ensure good drainage to protect crops from flooding.', '2025-04-29'),
(146, 'Jhenaidah', 31, 6, 'Provide extra irrigation to crops during dry spells.', '2025-04-29'),
(147, 'Chuadanga', 32, 3.5, 'Monitor crops for dehydration in the heat.', '2025-04-29'),
(148, 'Meherpur', 33, 2.5, 'Water crops during the cooler hours of the day to avoid excessive evaporation.', '2025-04-29'),
(149, 'Satkhira', 30.5, 7.2, 'Ensure proper irrigation to maintain crop growth.', '2025-04-29'),
(150, 'Bagerhat', 31.3, 6.8, 'Mulch crops to retain soil moisture during dry periods.', '2025-04-29'),
(151, 'Paris', 24, 3, 'Consider mulching to conserve water during dry spells.', '2025-04-29'),
(152, 'New York', 22, 2, 'Monitor soil moisture; water crops as needed.', '2025-04-29'),
(153, 'London', 20.5, 4.5, 'Prepare for cooler weather; protect tender crops.', '2025-04-29'),
(154, 'Tokyo', 27, 7, 'Increase irrigation during dry periods to prevent crop stress.', '2025-04-29'),
(155, 'Toronto', 19, 2.5, 'Cover crops to prevent frost damage in cold temperatures.', '2025-04-29'),
(156, 'Sydney', 25.5, 6, 'Water crops early in the morning to avoid evaporation.', '2025-04-29'),
(157, 'Berlin', 21, 5.5, 'Check soil moisture regularly and water as needed.', '2025-04-29'),
(158, 'Moscow', 17, 1, 'Prepare crops for potential frost.', '2025-04-29'),
(159, 'Dubai', 38, 0, 'Increase irrigation during extremely hot days to prevent crop dehydration.', '2025-04-29'),
(160, 'Delhi', 35.5, 1.5, 'Water crops regularly to prevent wilting in the heat.', '2025-04-29'),
(161, 'Singapore', 30, 9, 'Maintain drainage systems to avoid waterlogging.', '2025-04-29'),
(162, 'Kuala Lumpur', 29, 11, 'Protect crops from pests attracted by high humidity.', '2025-04-29'),
(163, 'Jakarta', 28.5, 14, 'Ensure good soil drainage to prevent waterlogging.', '2025-04-29'),
(164, 'Bangkok', 32.5, 10.5, 'Maintain proper soil moisture to avoid crop stress.', '2025-04-29'),
(165, 'Hong Kong', 27.5, 7.5, 'Use shade nets to protect crops from intense sun.', '2025-04-29'),
(166, 'Seoul', 24.5, 5, 'Prune crops regularly to avoid overcrowding in wet conditions.', '2025-04-29'),
(167, 'Beijing', 25, 2.5, 'Water crops early in the day to minimize evaporation.', '2025-04-29'),
(168, 'Madrid', 26, 0.5, 'Protect crops from extreme heat by providing shade.', '2025-04-29'),
(169, 'Rome', 27.5, 1, 'Water crops regularly to ensure they are properly hydrated.', '2025-04-29'),
(170, 'Vienna', 23.5, 3.5, 'Ensure good drainage to avoid waterlogging in heavy rain.', '2025-04-29'),
(171, 'Madani Avenue', 32, 5.2, 'Light rain expected — delay pesticide application to avoid wash-off.', '2025-06-23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `u_id` (`u_id`),
  ADD KEY `cart_ibfk_2` (`list_id`);

--
-- Indexes for table `crop`
--
ALTER TABLE `crop`
  ADD PRIMARY KEY (`crop_id`);

--
-- Indexes for table `crop_bd`
--
ALTER TABLE `crop_bd`
  ADD PRIMARY KEY (`CBD_id`),
  ADD KEY `crop_bd` (`crop_id`) USING BTREE;

--
-- Indexes for table `crop_type`
--
ALTER TABLE `crop_type`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `crop_tp` (`crop_id`);

--
-- Indexes for table `disease_info`
--
ALTER TABLE `disease_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crop_id` (`crop_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email_purpose` (`email`,`purpose`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`eq_id`);

--
-- Indexes for table `expens`
--
ALTER TABLE `expens`
  ADD PRIMARY KEY (`e_id`),
  ADD KEY `exf_fk1` (`f_id`),
  ADD KEY `exc_fk2` (`crop_id`);

--
-- Indexes for table `farmer`
--
ALTER TABLE `farmer`
  ADD PRIMARY KEY (`f_id`),
  ADD KEY `fk_1` (`u_id`);

--
-- Indexes for table `farmer_crop`
--
ALTER TABLE `farmer_crop`
  ADD PRIMARY KEY (`fc_id`),
  ADD KEY `f_id` (`f_id`),
  ADD KEY `crop_id` (`crop_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `govt_scheme`
--
ALTER TABLE `govt_scheme`
  ADD PRIMARY KEY (`scheme_id`);

--
-- Indexes for table `import_export`
--
ALTER TABLE `import_export`
  ADD PRIMARY KEY (`ex_id`),
  ADD KEY `imf_fk1` (`f_id`),
  ADD KEY `imc_fk2` (`crop_id`);

--
-- Indexes for table `ip_blacklist`
--
ALTER TABLE `ip_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `ip_details`
--
ALTER TABLE `ip_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `loan`
--
ALTER TABLE `loan`
  ADD PRIMARY KEY (`l_id`),
  ADD KEY `loan_ibfk_1` (`f_id`);

--
-- Indexes for table `loan_installments`
--
ALTER TABLE `loan_installments`
  ADD PRIMARY KEY (`installment_id`),
  ADD KEY `l_id` (`l_id`);

--
-- Indexes for table `local_price`
--
ALTER TABLE `local_price`
  ADD PRIMARY KEY (`l_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `market_listing`
--
ALTER TABLE `market_listing`
  ADD PRIMARY KEY (`list_id`),
  ADD KEY `l_fk` (`f_id`);

--
-- Indexes for table `national_price`
--
ALTER TABLE `national_price`
  ADD PRIMARY KEY (`n_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `list_id` (`list_id`),
  ADD KEY `order_fk1` (`u_id`);

--
-- Indexes for table `prev_mp`
--
ALTER TABLE `prev_mp`
  ADD PRIMARY KEY (`prev_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rental`
--
ALTER TABLE `rental`
  ADD PRIMARY KEY (`r_id`),
  ADD KEY `eq_id` (`eq_id`),
  ADD KEY `f_id` (`f_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`s_id`),
  ADD KEY `salf_fk` (`f_id`),
  ADD KEY `salc_fk` (`crop_id`);

--
-- Indexes for table `seeds_cart`
--
ALTER TABLE `seeds_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `sf_id` (`sf_id`);

--
-- Indexes for table `seeds_fertilizer`
--
ALTER TABLE `seeds_fertilizer`
  ADD PRIMARY KEY (`sf_id`),
  ADD KEY `fk_admin` (`admin_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `weather`
--
ALTER TABLE `weather`
  ADD PRIMARY KEY (`w_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `crop`
--
ALTER TABLE `crop`
  MODIFY `crop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `crop_bd`
--
ALTER TABLE `crop_bd`
  MODIFY `CBD_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `crop_type`
--
ALTER TABLE `crop_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `disease_info`
--
ALTER TABLE `disease_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `eq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `expens`
--
ALTER TABLE `expens`
  MODIFY `e_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `farmer`
--
ALTER TABLE `farmer`
  MODIFY `f_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `farmer_crop`
--
ALTER TABLE `farmer_crop`
  MODIFY `fc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `govt_scheme`
--
ALTER TABLE `govt_scheme`
  MODIFY `scheme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ip_blacklist`
--
ALTER TABLE `ip_blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ip_details`
--
ALTER TABLE `ip_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `loan`
--
ALTER TABLE `loan`
  MODIFY `l_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loan_installments`
--
ALTER TABLE `loan_installments`
  MODIFY `installment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `local_price`
--
ALTER TABLE `local_price`
  MODIFY `l_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `market_listing`
--
ALTER TABLE `market_listing`
  MODIFY `list_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `national_price`
--
ALTER TABLE `national_price`
  MODIFY `n_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `prev_mp`
--
ALTER TABLE `prev_mp`
  MODIFY `prev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rental`
--
ALTER TABLE `rental`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `seeds_cart`
--
ALTER TABLE `seeds_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `seeds_fertilizer`
--
ALTER TABLE `seeds_fertilizer`
  MODIFY `sf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`list_id`) REFERENCES `market_listing` (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crop_bd`
--
ALTER TABLE `crop_bd`
  ADD CONSTRAINT `crop_bd_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crop` (`crop_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crop_type`
--
ALTER TABLE `crop_type`
  ADD CONSTRAINT `crop_type_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crop` (`crop_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `disease_info`
--
ALTER TABLE `disease_info`
  ADD CONSTRAINT `disease_info_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crop` (`crop_id`),
  ADD CONSTRAINT `disease_info_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `crop_type` (`type_id`);

--
-- Constraints for table `farmer_crop`
--
ALTER TABLE `farmer_crop`
  ADD CONSTRAINT `farmer_crop_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crop` (`crop_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `farmer_crop_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `crop_type` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `farmer_crop_ibfk_3` FOREIGN KEY (`f_id`) REFERENCES `farmer` (`f_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ip_blacklist`
--
ALTER TABLE `ip_blacklist`
  ADD CONSTRAINT `fk_ipblacklist_user` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ip_details`
--
ALTER TABLE `ip_details`
  ADD CONSTRAINT `fk_ipdetails_user` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `local_price`
--
ALTER TABLE `local_price`
  ADD CONSTRAINT `local_price_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `national_price`
--
ALTER TABLE `national_price`
  ADD CONSTRAINT `national_price_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `prev_mp`
--
ALTER TABLE `prev_mp`
  ADD CONSTRAINT `prev_mp_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user` (`u_id`);

--
-- Constraints for table `seeds_cart`
--
ALTER TABLE `seeds_cart`
  ADD CONSTRAINT `seeds_cart_ibfk_1` FOREIGN KEY (`sf_id`) REFERENCES `seeds_fertilizer` (`sf_id`);

--
-- Constraints for table `seeds_fertilizer`
--
ALTER TABLE `seeds_fertilizer`
  ADD CONSTRAINT `fk_admin` FOREIGN KEY (`admin_id`) REFERENCES `user` (`u_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

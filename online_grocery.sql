-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2026 at 02:56 PM
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
-- Database: `online_grocery`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `unit_no` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `customer_id`, `unit_no`, `street`, `city`, `state`, `postal_code`, `country`, `active`) VALUES
(1, 1, '9', 'Lorong 5/SS6, Bandar Tasek Mutiara', 'Simpang Ampat', 'Penang', '14120', 'Malaysia', 1),
(2, 2, 'B15-01', 'Pangsapuri Ixora,', 'Bukit Beruang', 'Melaka', '75450', 'Malaysia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified` int(1) DEFAULT 0,
  `admin_role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `email`, `password`, `email_verified`, `admin_role`, `created_at`, `verification_token`) VALUES
(1, 'superadmin', 'hcwmmt0114@gmail.com', '$2y$13$kbVuAKDN7Bor2XWBxKiLQe9oJja5GhXtZmEsD6lTRDHWyRWcZa//O', 1, 'superadmin', '2026-06-14 23:11:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `selected` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `product_id`, `quantity`, `created_at`, `active`, `selected`) VALUES
(1, 1, 1, 1, '2026-07-05 04:56:12', 0, 1),
(2, 1, 22, 1, '2026-07-05 04:56:27', 0, 1),
(3, 1, 9, 1, '2026-07-05 04:56:33', 0, 1),
(4, 1, 14, 1, '2026-07-05 04:56:51', 0, 1),
(5, 1, 22, 1, '2026-07-05 08:54:45', 0, 1),
(6, 1, 11, 1, '2026-07-05 08:54:52', 0, 1),
(7, 1, 10, 1, '2026-07-05 08:54:59', 0, 1),
(8, 1, 3, 2, '2026-07-05 08:55:11', 0, 1),
(9, 1, 27, 1, '2026-07-05 08:55:27', 0, 1),
(10, 1, 16, 1, '2026-07-05 08:55:32', 0, 1),
(11, 1, 24, 20, '2026-07-05 10:35:54', 0, 1),
(12, 1, 4, 3, '2026-07-05 10:38:45', 0, 1),
(13, 1, 4, 3, '2026-07-05 10:48:35', 0, 1),
(14, 1, 4, 1, '2026-07-05 10:55:50', 0, 1),
(15, 1, 4, 1, '2026-07-05 10:56:07', 0, 1),
(16, 1, 24, 5, '2026-07-05 10:57:51', 0, 1),
(17, 1, 24, 3, '2026-07-06 05:26:29', 0, 1),
(18, 2, 24, 1, '2026-07-06 05:28:30', 0, 1),
(19, 1, 24, 3, '2026-07-06 05:29:07', 0, 1),
(20, 1, 24, 3, '2026-07-06 05:34:08', 0, 1),
(21, 2, 24, 3, '2026-07-06 05:36:03', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(5, 'Beverages'),
(9, 'Eggs'),
(4, 'Food Essentials'),
(7, 'Frozen'),
(2, 'Fruits'),
(3, 'Snacks'),
(1, 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_password` varchar(255) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `customer_email`, `customer_password`, `customer_phone`, `created_at`, `is_verified`) VALUES
(1, 'Khoh Hao Chen', 'khohhaochen@gmail.com', '$2y$10$lv2GAJ6P8WINuZxgthWRPujOGO3hUSwaM5jPcoC07xh332Qrghc9C', '01121127750', '2026-07-05 04:53:10', 1),
(2, 'HC Khoh', 'hcwmmt0114@gmail.com', '$2y$10$9Y0YN5eAlmMrjz3wI3pEJeZrr3pA5HPhks5APom7AcxMYrT6anY.e', '01121127750', '2026-07-06 05:27:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `address_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `delivery_status` enum('pending','shipped','delivered') NOT NULL DEFAULT 'pending',
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_date`, `address_id`, `total_price`, `payment_method`, `delivery_status`, `customer_id`) VALUES
(1, '2026-07-05 05:00:24', 1, 37.30, 'TnG E-Wallet', 'delivered', 1),
(2, '2026-07-05 08:56:28', 1, 48.80, 'Credit/Debit Card', 'delivered', 1),
(3, '2026-07-05 10:37:14', 1, 339.00, 'TnG E-Wallet', 'shipped', 1),
(4, '2026-07-05 10:39:46', 1, 35.00, 'Credit/Debit Card', 'shipped', 1),
(5, '2026-07-05 10:50:10', 1, 25.00, 'TnG E-Wallet', 'shipped', 1),
(6, '2026-07-05 10:57:04', 1, 10.00, 'Credit/Debit Card', 'pending', 1),
(7, '2026-07-05 10:58:52', 1, 88.50, 'TnG E-Wallet', 'pending', 1),
(8, '2026-07-06 05:38:25', 2, 55.10, 'Credit/Debit Card', 'pending', 2);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `product_price`) VALUES
(1, 1, 1, 1, 4.90),
(2, 1, 22, 1, 4.00),
(3, 1, 9, 1, 15.90),
(4, 1, 14, 1, 7.50),
(5, 2, 22, 1, 4.00),
(6, 2, 11, 1, 10.50),
(7, 2, 10, 1, 12.40),
(8, 2, 3, 2, 3.20),
(9, 2, 27, 1, 9.00),
(10, 2, 16, 1, 1.50),
(11, 3, 24, 20, 16.70),
(12, 4, 4, 6, 5.00),
(13, 5, 4, 4, 5.00),
(14, 6, 4, 1, 5.00),
(15, 7, 24, 5, 16.70),
(16, 8, 24, 3, 16.70);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pass_reset`
--

CREATE TABLE `pass_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_date`, `price`, `payment_status`) VALUES
(1, 1, '2026-07-05 05:00:24', 37.30, 'completed'),
(2, 2, '2026-07-05 08:56:29', 48.80, 'completed'),
(3, 3, '2026-07-05 10:37:14', 339.00, 'completed'),
(4, 4, '2026-07-05 10:39:46', 35.00, 'completed'),
(5, 5, '2026-07-05 10:50:10', 25.00, 'completed'),
(6, 6, '2026-07-05 10:57:04', 10.00, 'completed'),
(7, 7, '2026-07-05 10:58:52', 88.50, 'completed'),
(8, 8, '2026-07-06 05:38:25', 55.10, 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `created_at`, `product_image`, `active`) VALUES
(1, 1, 'Beijing Cabbage (China) 600g', 'Beijing Cabbage (China) 600g', 4.90, 46, '2026-06-14 23:11:18', 'beijingcabbage600g.jpg', 1),
(2, 2, 'Dole Banana (Philippines) 1pack', 'Ripe bananas from tropical plantations, known for their creamy texture and natural sweetness. The hand of fruits arrives at perfect eating ripeness. Required 3-days advance for large order.', 8.20, 48, '2026-06-14 23:11:18', 'dolebanana1pack.jpg', 1),
(3, 3, 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 3.20, 46, '2026-06-14 23:11:18', 'oreovanillaslugsandwich110.4g.jpg', 1),
(4, 4, 'San Remo No.5 Spaghetti 500g', 'San Remo No.5 Spaghetti 500g', 5.00, 0, '2026-06-14 23:11:18', 'sanremospaghetti500g.jpg', 1),
(5, 1, 'Bok Choy (Sawi Putih) 350g', 'Bok Choy (Sawi Putih) 350g', 4.70, 48, '2026-06-14 23:11:18', 'bokchoy(sawiputih)350g.webp', 1),
(6, 3, 'Oriental Super Ring Family Pack 14g x 8', 'Oriental Super Ring Family Pack 14g x 8', 5.00, 98, '2026-06-14 23:11:18', 'oriental_super_ring_familypack14gx8.jpg', 1),
(7, 5, 'Yarra Farm Australian Pasteurized Cows Milk 1L', 'Yarra Farm Australian Pasteurized Cows Milk 1L', 8.20, 0, '2026-06-14 23:11:18', 'yarra_farm_cows_milk1L.jpg', 1),
(8, 5, 'Coca-Cola Carbonated Drink 1.5L', 'Coca-Cola Carbonated Drink 1.5L', 4.40, 99, '2026-06-14 23:38:27', 'cocacola_ori1.5L.png', 1),
(9, 7, 'Simplot Hashbrown Original 637g', 'Simplot Hashbrown Original 637g', 15.90, 98, '2026-06-14 23:38:27', 'Simplot_Hashbrown_Original_637g.webp', 1),
(10, 7, 'Figo Steamboat Choice 500g', 'Figo Steamboat Choice 500g', 12.40, 99, '2026-06-14 23:38:27', 'Figo_Steamboat_Choice_500g.webp', 1),
(11, 7, 'Quaker Instant Oatmeal 800g', 'These instant oats are a rich source of fiber and energy that can be prepared in minutes. They provide a heart healthy breakfast option that can be customized with various toppings.', 10.50, 99, '2026-06-14 23:38:27', 'Quaker_Instant_Oatmeal_800g.webp', 1),
(12, 7, 'Nestle Koko Krunch Cereal 450g', 'Nestle Koko Krunch Cereal 450g', 17.50, 100, '2026-06-14 23:38:27', 'Nestle_Koko_Krunch_Cereal_450g.webp', 1),
(13, 4, 'Sunwhite Beras Wangi 5kg', 'A high quality fragrant rice known for its soft texture and pleasant aroma after cooking. It is ideal for daily meals and pairs perfectly with a wide variety of Asian cuisines.', 36.90, 99, '2026-06-14 23:38:27', 'SunwhiteBerasWangi5kg.webp', 1),
(14, 1, 'Lushious Tomato Value Pack (Malaysia) 600g', 'Lushious Tomato Value Pack (Malaysia) 600g', 7.50, 98, '2026-06-14 23:38:27', 'LushiousTomatoValuePack600g.webp', 1),
(15, 2, 'Red Seedless Watermelon 4.5kg', 'Red Seedless Watermelon 4.5kg', 28.00, 99, '2026-06-14 23:38:27', 'RedSeedlessWatermelon4.5kg.webp', 1),
(16, 5, 'F&N Ice Mountain Mineral Water 1.5L', '', 1.50, 96, '2026-06-14 23:38:27', 'F&NIceMountainMineralWater1.5L.webp', 1),
(17, 9, 'NutriPlus Omega-3 Chicken Eggs 15pcs/pack', 'NutriPlus Omega-3 Chicken Eggs 15pcs/pack', 11.50, 100, '2026-06-14 23:45:26', 'NutriPlusOmega-3ChickenEggs15pcsperpack.jpg', 1),
(18, 9, 'Nutriplus Kampung Eggs with Omega-3 10pcs/pack', 'Nutriplus Kampung Eggs with Omega-3 10pcs/pack', 8.60, 100, '2026-06-14 23:46:45', 'NutriplusKampungEggswithOmega-3_10pcsperpack.jpg', 1),
(19, 4, 'Adabi Serbuk Kari Ayam dan Daging (Chicken and Meat Curry Powder) 250g', 'Adabi Serbuk Kari Ayam dan Daging (Chicken and Meat Curry Powder) 250g', 6.90, 50, '2026-07-05 04:41:42', 'adabi_chicken_curry_250g.jpg', 1),
(20, 4, 'Maggi Ikan Bilis Stock Cubes 60g', 'Maggi Ikan Bilis Stock Cubes 60g', 3.40, 50, '2026-07-05 04:41:42', 'maggi_ikan_bilis_stock.jpg', 1),
(21, 4, 'Ferrero Nutella Hazelnut Spread with Cocoa 350g', 'Ferrero Nutella Hazelnut Spread with Cocoa 350g', 23.90, 30, '2026-07-05 04:41:42', 'nutella_350g.jpg', 1),
(22, 1, 'Cameron Garden French Bean 250g', 'Cameron Garden French Bean (Kacang Buncis) 250g', 4.00, 38, '2026-07-05 04:41:42', 'french_bean_250g.jpg', 1),
(23, 1, 'Paprika Farm Japanese Cucumber 330g', 'Paprika Farm Japanese Cucumber (Kyuri) 330g', 3.90, 40, '2026-07-05 04:41:42', 'japanese_cucumber.jpg', 1),
(24, 5, 'Farm Fresh Pure Fresh Milk 2L', 'Farm Fresh Pure Fresh Milk 2L', 16.70, 10, '2026-07-05 04:41:42', 'farm_fresh_milk_2L.jpg', 1),
(25, 5, 'Tropicana Twister Apple 1.5L', 'Tropicana Twister Apple 1.5L', 6.45, 50, '2026-07-05 04:41:42', 'tropicana_apple_1.5L.jpg', 1),
(26, 3, 'Hup Seng Ping Pong Cream Cracker 428g', 'Hup Seng Ping Pong Cream Cracker 428g', 4.90, 60, '2026-07-05 04:41:42', 'hup_seng_crackers.jpg', 1),
(27, 3, 'Bin Bin Rice Crackers 150g', 'These Thai style rice crackers are baked to perfection and glazed with a sweet and savory soy-based sauce. They are light, airy, and completely cholesterol free, making them a popular choice for mindful snacking.', 9.00, 39, '2026-07-05 04:41:42', 'bin_bin_rice_crackers.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `register_verify`
--

CREATE TABLE `register_verify` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `pass_reset`
--
ALTER TABLE `pass_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `register_verify`
--
ALTER TABLE `register_verify`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pass_reset`
--
ALTER TABLE `pass_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `register_verify`
--
ALTER TABLE `register_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

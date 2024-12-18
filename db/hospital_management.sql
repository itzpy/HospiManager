-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 11:50 PM
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
-- Database: `hospital_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_changed` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `action`, `item_id`, `quantity_changed`, `notes`, `timestamp`) VALUES
(1, 1, 'add', 1, 1000, 'Initial stock', '2024-12-14 01:20:21'),
(2, 1, 'add', 17, 5000, 'Initial stock', '2024-12-14 01:20:21'),
(3, 1, 'add', 10, 20, 'Initial stock', '2024-12-14 01:20:21'),
(4, 1, 'remove', 18, -100, 'Regular usage', '2024-12-14 01:20:21'),
(5, 1, 'add', 21, 10, 'Restocking', '2024-12-14 01:20:21'),
(6, 1, 'remove', 5, -50, 'Emergency department request', '2024-12-14 01:20:21'),
(8, 2, 'add', 25, 100, 'New item added', '2024-12-14 13:47:52'),
(13, 2, 'stock_in', 2, 50, 'update stock', '2024-12-14 13:51:24'),
(14, 2, 'add', 26, 10, 'New item added', '2024-12-14 21:15:13'),
(17, 2, 'add', 29, 10, 'New item added', '2024-12-15 01:27:08'),
(18, 2, 'adjust', 21, 10, 'we need more first aid kits', '2024-12-15 01:30:22'),
(31, 3, 'stock_out', 29, 10, 'Stock removed by admin: For a 10 patients', '2024-12-15 19:34:58'),
(32, 3, 'stock_out', 13, 1, 'Stock removed by admin: update', '2024-12-15 20:45:17'),
(33, 3, 'stock_out', 13, 1, 'Stock removed by admin: restock', '2024-12-15 20:46:02'),
(34, 2, 'adjust', 26, 10, 'Low', '2024-12-15 20:50:37'),
(35, 3, 'add', 29, 10, 'Stock addd by admin: update', '2024-12-15 21:04:23'),
(38, 2, 'update', 29, 0, 'Unit updated', '2024-12-15 21:06:59'),
(39, 3, 'add', 29, 5, 'Stock addd by admin: Restock', '2024-12-15 21:07:31'),
(40, 2, 'update', 25, 0, 'Category changed', '2024-12-15 22:17:56'),
(47, 2, 'update', 2, -550, 'Quantity changed by -550', '2024-12-16 17:36:32'),
(48, 2, 'adjust', 2, 10, 'restock', '2024-12-16 17:38:11'),
(49, 2, 'update', 2, 0, 'Unit updated', '2024-12-16 17:38:43'),
(51, 2, 'adjust', 2, -10, 'checking', '2024-12-17 19:00:05'),
(52, 3, 'add', 13, 5, 'Stock addd by admin: update', '2024-12-17 19:58:40'),
(58, 2, 'adjust', 2, 5, 'update', '2024-12-18 19:36:50'),
(59, 2, 'adjust', 2, 10, 'update', '2024-12-18 19:37:06'),
(62, 10, 'add', 29, 1, 'Stock addd by admin: add', '2024-12-18 19:45:16'),
(63, 10, 'remove', 29, 1, 'Stock removed by admin: remove', '2024-12-18 19:45:31'),
(64, 3, 'add', 29, 5, 'Stock addd by admin: add', '2024-12-18 21:36:49'),
(65, 3, 'remove', 29, 5, 'Stock removed by admin: remove\n', '2024-12-18 21:37:05'),
(66, 3, 'add', 29, 5, 'Stock addd by admin: let&#39;s see', '2024-12-18 21:37:35'),
(67, 3, 'add', 29, 5, 'Stock addd by admin: Lt&#39;', '2024-12-18 21:40:00'),
(68, 3, 'add', 29, 5, 'Stock addd by admin: 5', '2024-12-18 21:40:21'),
(69, 3, 'add', 29, 4, 'Stock addd by admin: lets see', '2024-12-18 21:42:04'),
(70, 3, 'add', 29, 5, 'let&#39;s see', '2024-12-18 21:50:10'),
(71, 3, 'add', 29, 4, 'Stock addd by admin: lets see', '2024-12-18 21:51:07'),
(72, 3, 'add', 29, 3, 'Stock addd by admin: les see', '2024-12-18 21:51:23'),
(73, 3, 'remove', 29, 5, 'Stock removed by admin: lets ', '2024-12-18 21:51:56'),
(74, 3, 'add', 29, 10, 'Stock addd by admin: py', '2024-12-18 22:06:35'),
(75, 3, 'add', 29, 1, 'Stock addd by admin: lets see', '2024-12-18 22:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Medications', 'All types of medicines and drugs', '2024-12-14 00:49:17'),
(2, 'Medical Supplies', 'Bandages, syringes, and other medical supplies', '2024-12-14 00:49:17'),
(3, 'Equipment', 'Medical equipment and devices', '2024-12-14 00:49:17'),
(4, 'Laboratory', 'Laboratory supplies and materials', '2024-12-14 00:49:17'),
(5, 'Personal Protective Equipment', 'PPE and safety equipment', '2024-12-14 01:20:21'),
(6, 'Emergency Supplies', 'Emergency and first aid supplies', '2024-12-14 01:20:21'),
(11, 'Food Items', 'food for patients', '2024-12-18 19:40:01');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL DEFAULT 'unit',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `minimum_quantity` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `category_id`, `name`, `description`, `quantity`, `unit`, `created_at`, `last_updated`, `minimum_quantity`) VALUES
(1, 1, 'Paracetamol 500mg', 'Pain reliever and fever reducer', 1000, 'tablets', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(2, 1, 'Amoxicillin 250mg', 'Antibiotic capsules', 15, '0', '2024-12-14 01:20:21', '2024-12-18 19:37:06', 10),
(3, 1, 'Ibuprofen 400mg', 'Anti-inflammatory medication', 750, 'tablets', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(4, 1, 'Omeprazole 20mg', 'Acid reflux medication', 300, 'capsules', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(5, 2, 'Gauze Bandages', 'Sterile wound dressing', 1000, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(6, 2, 'Disposable Syringes 5ml', 'Sterile syringes for injections', 2000, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(7, 2, 'Medical Tape', 'Adhesive tape for bandages', 500, 'rolls', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(8, 2, 'Cotton Swabs', 'Sterile cotton swabs', 5000, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(9, 3, 'Digital Thermometer', 'Electronic body temperature measurement', 50, 'units', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(10, 3, 'Blood Pressure Monitor', 'Automatic BP measurement device', 20, 'units', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(11, 3, 'Stethoscope', 'Acoustic medical device', 30, 'units', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(12, 3, 'Pulse Oximeter', 'Oxygen saturation monitor', 25, 'units', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(13, 4, 'Blood Collection Tubes', 'Vacuum sealed collection tubes', 1003, 'pieces', '2024-12-14 01:20:21', '2024-12-17 19:58:40', 10),
(14, 4, 'Microscope Slides', 'Glass slides for microscopy', 500, 'boxes', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(15, 4, 'Test Strips', 'Urinalysis test strips', 1500, 'strips', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(16, 4, 'Petri Dishes', 'Culture plates', 300, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(17, 5, 'Surgical Masks', 'Disposable face masks', 5000, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(18, 5, 'Latex Gloves', 'Disposable examination gloves', 10000, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(19, 5, 'Face Shields', 'Protective face shields', 200, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(20, 5, 'Isolation Gowns', 'Disposable protective gowns', 500, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(21, 6, 'First Aid Kits', 'Complete emergency kits', 60, 'kits', '2024-12-14 01:20:21', '2024-12-15 01:30:22', 10),
(22, 6, 'Emergency Blankets', 'Thermal emergency blankets', 200, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(23, 6, 'Ice Packs', 'Instant cold packs', 300, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(24, 6, 'Burn Dressings', 'Specialized burn treatment', 100, 'pieces', '2024-12-14 01:20:21', '2024-12-14 01:20:21', 10),
(25, 5, 'Nose Mask', 'These are the black ones', 100, '0', '2024-12-14 13:47:52', '2024-12-15 22:17:56', 10),
(26, 2, 'Condoms', 'For the visitors', 20, 'boxes', '2024-12-14 21:15:13', '2024-12-15 20:50:37', 10),
(29, 1, 'Advil 250g', 'Pain Killers', 52, '0', '2024-12-15 01:27:08', '2024-12-18 22:06:45', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','superadmin','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `role`, `created_at`, `last_login`) VALUES
(1, 'Admin', 'User', 'admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2024-12-14 00:49:17', NULL),
(2, 'Papa', 'Badu', 'raybadu10@gmail.com', '$2y$10$f3JJnQF1.u53yCWv7f9Druw4yWmNw3XtsZAFTiH9zp5bLYVM8qP2q', 'superadmin', '2024-12-14 00:49:59', '2024-12-15 23:58:19'),
(3, 'Regular', 'Admin', 'Admin@gmail.com', '$2y$10$XDqLDuZNVd6TN/ItiQKduunFLih5acqSVQsj0L8NcAuUTY61qgSw.', 'admin', '2024-12-14 01:08:30', '2024-12-15 22:20:23'),
(7, 'Staff', 'Doctor', 'Staff@gmail.com', '$2y$10$xA.Oc0sdKlSLFJaUa1x3sucLD8UCwY/gH5xQFiL4mx.Hy/xlIV7KC', 'staff', '2024-12-16 00:28:12', NULL),
(10, 'Tester', 'Staff', 'TStaff@gmail.com', '$2y$10$sm1WWHcFea66.9FxX4jxiOtJH3P8U4Zzb0LJECMtwITmT0p3la5i.', 'admin', '2024-12-18 19:43:08', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

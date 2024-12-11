-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2024 at 09:34 PM
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
-- Database: `recipe_sharing`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `food_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `food_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `type` enum('breakfast','lunch','dinner','snack','dessert') NOT NULL,
  `is_healthy` tinyint(1) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `preparation_time` int(11) DEFAULT NULL,
  `cooking_time` int(11) DEFAULT NULL,
  `serving_size` int(11) DEFAULT NULL,
  `calories_per_serving` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `nutritional_value` text DEFAULT NULL,
  `allergen_info` varchar(255) DEFAULT NULL,
  `shelf_life` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nutritionfacts`
--

CREATE TABLE `nutritionfacts` (
  `nutrition_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL,
  `protein` decimal(5,2) DEFAULT NULL,
  `carbohydrates` decimal(5,2) DEFAULT NULL,
  `fat` decimal(5,2) DEFAULT NULL,
  `fiber` decimal(5,2) DEFAULT NULL,
  `sugar` decimal(5,2) DEFAULT NULL,
  `sodium` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `optional` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Papa', 'Badu', 'raybadu10@gmail.com\r\n', 'Papabadu2004!', 1, '2024-11-07 20:20:47', '2024-11-07 20:20:47'),
(2, 'Admin', 'User', 'admin@gmail.com', '$2y$10$xarTXka8c.CHIWk6X06VgOEnpXt7VH3cKgmUilnTJL.LdRNIL2Sty', 2, '2024-11-07 20:22:34', '2024-11-07 20:22:34'),
(3, 'Papa', 'Tawiah', 'papat@gmail.com', '$2y$10$maqyGrHXVPLUna7PUVbs9ek2pwNmPp/VW89rFJ4AmBEx82t59Hx5O', 2, '2024-11-20 12:14:59', '2024-11-20 12:14:59'),
(5, 'Eugene', 'Kojo', 'egdad@gmail.com', '$2y$10$5GgezuwkUHvNDvFxejIgHev4BrjRcSQzzB0I.C4IlNRYpNnxPQnxW', 2, '2024-11-20 12:21:31', '2024-11-20 12:21:31'),
(6, 'Kwadwo', 'Budu', 'kbudu@gmail.com', '$2y$10$ia/4PjFzen.egKyvC45Lqu9iI6tjexMMYcupuYqKXVY97CjPt65iW', 2, '2024-11-20 12:22:54', '2024-11-20 12:22:54'),
(7, 'Papa', 'Budu', 'pabudu@gmail.com', '$2y$10$6CIbu59F1yPEURfvcbj4AuW/VmW97zvnbf4.w6id3DsgDvzFrt7bq', 2, '2024-11-20 12:27:15', '2024-11-20 12:27:15'),
(8, 'jt', 'man', 'john@mail.com', '$2y$10$EOCLPEAZeu1vtKwcqgFRWegmvNnrZBldlnoELr85eJBtPLEOqh.oi', 2, '2024-11-20 12:35:00', '2024-11-20 12:35:00'),
(9, 'dsf', 'fdsd', 'fdsfds@gmail.com', '$2y$10$Zvc27OynugghxTU8uZ8aT.jFa.tCbpmT3wzdyuNtKPmhC98d8xsvm', 2, '2024-11-20 12:57:14', '2024-11-20 12:57:14'),
(10, 'Bs', 'usdfi', 'yaya2@gmail.com', '$2y$10$/9FNinPS5hKvqcIExDutau/XGxD1w5jnBwp0HgklGBs8pcAMQ5WCS', 2, '2024-11-20 13:04:08', '2024-11-20 13:04:08'),
(11, 'Bs', 'usdfi', 'PAPA@GMAIL.COM', '$2y$10$Nm8iRpBl9JsZTKVJsiosVO6x6zQX64bstYahhNDfGDHOgK.iX2eVC', 2, '2024-11-20 13:07:39', '2024-11-20 13:07:39'),
(12, 'sadnas', 'dshads', 'dfafd@gmail.com', '$2y$10$FGC2nXRdhABQDxFwNYWWCeB2ZR6PnUl5qhm.Fl7XH6aWp6WMULaNe', 2, '2024-11-20 13:10:23', '2024-11-20 13:10:23'),
(13, 'papa', 'haes', 'rabds@gnad.com', '$2y$10$ifVuUHgcKY6A.fGXB3J9NuQVNuuchg0y.Rf515TTPnjjY0X6Iktim', 2, '2024-11-20 13:13:46', '2024-11-20 13:13:46'),
(14, 'haah', 'jdads', 'raybadu110@gmail.com', '$2y$10$GkWEK7bQS4NytP1Bh0.GE.ukUnFvgrEee3MUaQFXBZly6DHIQwEum', 2, '2024-11-20 21:56:02', '2024-11-20 21:56:02'),
(15, 'Papa', 'Badu', 'raybadu10@gmail.com', '$2y$10$8wYHz/USrd.2WUilaYpEJul/u8p618epDxAyugdGgOWwsvX4iwRjK', 2, '2024-11-20 22:13:36', '2024-11-20 22:13:36'),
(16, 'Kofi', 'Adjei', 'Kagyei@gmail.com', '$2y$10$h3VbZIZL93UN8BdqKebchehsNimTuwl49OWdt/0ItzPBZh2VULQyK', 2, '2024-11-20 22:44:18', '2024-11-20 22:44:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `food_id` (`food_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`);

--
-- Indexes for table `nutritionfacts`
--
ALTER TABLE `nutritionfacts`
  ADD PRIMARY KEY (`nutrition_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `food_id` (`food_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `food_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nutritionfacts`
--
ALTER TABLE `nutritionfacts`
  MODIFY `nutrition_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `foods` (`food_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `foods` (`food_id`) ON DELETE CASCADE;

--
-- Constraints for table `foods`
--
ALTER TABLE `foods`
  ADD CONSTRAINT `foods_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `nutritionfacts`
--
ALTER TABLE `nutritionfacts`
  ADD CONSTRAINT `nutritionfacts_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `foods` (`food_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `foods` (`food_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

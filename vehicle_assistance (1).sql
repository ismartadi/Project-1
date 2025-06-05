-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 11:26 AM
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
-- Database: `vehicle_assistance`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `phone`, `created_at`) VALUES
(1, 'Gnani sir', '$2y$10$0m9OoN5hGeKHWVIH3zkrfePzcJDo7fWFnVtp9bTRaRICqJeIVizyi', '1234567890', '2025-04-01 07:02:46'),
(2, 'Adhi', '$2y$10$5Wcj8edxk3Vm60gs4fCcreDzJPpkmrr4BbANAPPeLMA.UrPbkY1/a', '0123456789', '2025-04-10 09:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `mechanic_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `mechanic_id`, `rating`, `comment`, `created_at`) VALUES
(2, 1, 4, 'nice work', '2025-04-09 15:59:50'),
(3, 3, 1, 'nice work', '2025-04-10 04:33:17'),
(4, 3, 4, 'nice work...', '2025-04-11 06:48:39');

-- --------------------------------------------------------

--
-- Table structure for table `mechanics`
--

CREATE TABLE `mechanics` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('free','busy') DEFAULT 'free'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mechanics`
--

INSERT INTO `mechanics` (`id`, `username`, `password`, `phone`, `languages`, `name`, `location`, `latitude`, `longitude`, `profile_picture`, `created_at`, `status`) VALUES
(1, 'Prabhu', '$2y$10$6UQM1KaWGUBV8j0qEf6n1.usLQCqa35ANNtnPuu/MpRSK01RXxaeq', '7386692191', 'Telugu , English', 'prabhu kumar', 'Yanam, Puducherry, 533464, India', 16.73322350, 82.20181178, 'uploads/mechanic 2.jpeg', '2025-03-29 06:33:45', 'free'),
(2, 'rajur', '$2y$10$OlH4lxzpg5PS4CP56JZMcOTuMGvUv4636eQP4nBV52.6ypdMfwFdC', '9849351544', 'Telugu , English', 'raju', 'Kakinada, Kakinada (Urban), Kakinada, Andhra Pradesh, 533001, India', 16.94373850, 82.23506070, 'uploads/mechanic 7.jpeg', '2025-03-31 12:32:11', 'free'),
(3, 'Deepika', '$2y$10$r3nuIj1aKsnNmRH.gIOrGe7nNDXP5TQwRtrCIBHOCS6SAKch.71iK', '9701244339', 'Telugu , Hindi', 'deepika', '16.731533708951435, 82.21189979972756', 16.73153371, 82.21189980, 'uploads/mechanic 5 g.jpeg', '2025-04-01 08:04:19', 'free'),
(5, 'Gowtham', '$2y$10$4S22vLKIriFw/c1Qdh5fJupGXWi2./rs.xep0CQ7UDN0B8XKscOqG', '7095612389', 'Telugu , Hindi', 'Gowtham', 'Nellore, Sri Potti Sriramulu Nellore, Andhra Pradesh, 524001, India', 14.44937170, 79.98737630, 'uploads/mechanic 1.jpeg', '2025-04-02 06:43:44', 'free'),
(6, 'lalitha', '$2y$10$D2FAe37WoDxV0g.FlyaK5uuixUuFc0KTdWK5rdPEU54rOgFclEUWG', '+918978956733', 'Telugu , Hindi', 'lalitha', '16.729889762316436, 82.21619125939222', 16.72988976, 82.21619126, 'uploads/mechanic 5 g.jpeg', '2025-04-11 04:33:18', 'free'),
(7, 'ravi', '$2y$10$zPIW1JZ5azuGW/M4CCzIk.LpyC6JY1A2LeK0Trnu/t0uwCPzvawE6', '+916304717345', 'Telugu , Hindi', 'ravi', '16.728345614530717, 82.22048271905689', 16.72834561, 82.22048272, 'uploads/mechanic 8.jpeg', '2025-04-11 04:46:01', 'free');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `mechanic_id` int(11) NOT NULL,
  `vehicle_type` varchar(255) NOT NULL,
  `issue_description` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `user_latitude` decimal(10,8) NOT NULL,
  `user_longitude` decimal(11,8) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `mechanic_id`, `vehicle_type`, `issue_description`, `phone`, `user_latitude`, `user_longitude`, `status`, `created_at`, `updated_at`) VALUES
(45, 3, 'Car', 'Brake Problem', '9701244339', 16.73687400, 82.21044200, 'accepted', '2025-04-11 06:47:18', '2025-04-11 06:47:51'),
(46, 1, 'Bike', 'Brake Problem', '7386692191', 16.73689300, 82.21045700, 'accepted', '2025-04-14 06:31:08', '2025-04-14 06:32:25'),
(47, 2, 'Car', 'Engine Issue', '9849351544', 17.39340000, 78.47060000, 'pending', '2025-05-09 09:58:46', '2025-05-09 09:58:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `phone`, `created_at`) VALUES
(2, 'pavani', '$2y$10$91Ukykq/WxJGbS4vI/5wSuyFpaDiRAJXK6DIvjeGwWhQdIsxCKoKy', '6281581062', '2025-03-29 06:27:49'),
(3, 'lucky', '$2y$10$ULzbAYJRMSj4i1ioB4aMu.6tsMIYOlPD40DFeuNnSPpJxEHcV4CJW', '4854655321', '2025-05-09 09:59:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mechanic_id` (`mechanic_id`);

--
-- Indexes for table `mechanics`
--
ALTER TABLE `mechanics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mechanic_id` (`mechanic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mechanics`
--
ALTER TABLE `mechanics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

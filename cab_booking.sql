-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 11:13 AM
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
-- Database: `cab_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$qwoyY/p/3PqEuvUiERwU1.Lk07ev5eLv2GLqcpeP969FSyGQ2A9Se', 'admin@cabbooking.com', '2026-02-08 04:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `booking_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `driver_id`, `pickup_location`, `dropoff_location`, `fare`, `status`, `booking_time`, `completed_at`) VALUES
(1, 1, 1, '123 Main St, Downtown', '456 Oak Ave, Uptown', 25.50, 'completed', '2026-02-01 05:00:00', '2026-02-01 05:30:00'),
(2, 1, 2, '456 Oak Ave, Uptown', '789 Pine Rd, Suburbs', 32.00, 'completed', '2026-02-02 08:45:00', '2026-02-02 09:25:00'),
(3, 2, 3, '321 Elm St, Midtown', '654 Maple Dr, Airport', 45.75, 'completed', '2026-02-03 02:30:00', '2026-02-03 03:45:00'),
(4, 2, 1, '100 First Ave, Central', '200 Second St, North', 18.25, 'confirmed', '2026-02-07 11:00:00', NULL),
(5, 3, 4, '500 Fifth St, East', '600 Sixth Ave, West', 28.50, 'completed', '2026-02-04 06:30:00', '2026-02-04 07:00:00'),
(6, 3, 2, '700 Seventh Rd, South', '800 Eighth Ln, Harbor', 35.00, 'pending', '2026-02-08 01:30:00', NULL),
(7, 4, 5, '900 Ninth Blvd, Station', '1000 Tenth St, Mall', 22.00, 'completed', '2026-02-05 13:15:00', '2026-02-05 13:45:00'),
(8, 4, 6, '1100 Market St, Plaza', '1200 Center Ave, Park', 15.50, 'cancelled', '2026-02-06 04:00:00', NULL),
(9, 5, 7, '1300 Hill St, Heights', '1400 Valley Rd, Beach', 40.25, 'completed', '2026-02-06 05:30:00', '2026-02-06 06:30:00'),
(10, 5, 1, '1500 River Dr, Bridge', '1600 Lake Ave, Marina', 30.75, 'pending', '2026-02-08 03:00:00', NULL),
(11, 1, 3, '1700 Mountain Way, Resort', '1800 Ocean Blvd, Coast', 55.00, 'completed', '2026-02-07 07:30:00', '2026-02-07 09:00:00'),
(12, 2, 4, '1900 Forest Ln, Woods', '2000 Desert Rd, Oasis', 48.50, 'completed', '2026-02-07 04:45:00', '2026-02-07 06:15:00'),
(13, 1, 1, 'kengeri', 'majestic', 36.55, 'confirmed', '2026-02-12 05:31:27', NULL),
(14, 10, 7, 'kengeri', 'yelahanka', 100.00, 'confirmed', '2026-02-12 06:58:32', NULL),
(15, 1, 1, 'kengeri', 'majestic', 0.00, 'confirmed', '2026-02-12 09:09:43', NULL),
(21, 1, 1, 'banglore', 'davanagere', 1.00, 'confirmed', '2026-02-12 11:24:18', NULL),
(22, 10, 2, 'kengeri', 'majestic', 90.00, 'cancelled', '2026-02-12 11:25:33', NULL),
(23, 15, 1, 'davanagere', 'banglore', 755.00, 'confirmed', '2026-02-12 12:14:47', NULL),
(24, 10, 1, 'kengeri', 'yelahanka', 85.00, 'confirmed', '2026-02-12 12:17:13', NULL),
(25, 15, 2, 'davanagere', 'banglore', 7.50, 'pending', '2026-02-12 12:22:14', NULL),
(26, 15, 1, 'sdhj', 'rty245', 1467.50, 'confirmed', '2026-02-12 12:23:26', NULL),
(27, 15, 2, 'sdfg', 's', 115.00, 'cancelled', '2026-02-12 12:24:45', NULL),
(28, 1, 2, 'ypr', 'sbc', 35.00, 'pending', '2026-03-05 10:47:31', NULL),
(29, 16, 6, 'ypr', 'sbc', 17.50, 'completed', '2026-03-05 10:49:03', NULL),
(30, 16, 2, 'kengeri', 'yelahanka', 62.50, 'pending', '2026-03-05 11:18:04', NULL),
(31, 16, 2, 'kengeri', 'yelahanka', 405.95, 'confirmed', '2026-03-12 10:43:15', NULL),
(32, 16, 5, 'kengeri', 'majestic', 210254.60, 'pending', '2026-03-12 10:44:12', NULL),
(33, 1, 2, 'kengeri', 'sbc', 5.00, 'pending', '2026-03-12 10:45:08', NULL),
(34, 16, 6, 'chennai central', 'chennai airport', 274.25, 'pending', '2026-03-12 10:50:32', NULL),
(35, 16, 6, 'mumbai central', 'mumbai airport', 263.00, 'pending', '2026-03-12 11:00:24', NULL),
(36, 16, 5, 'mumbai central', 'mumbai airport', 263.00, 'pending', '2026-03-12 11:23:17', NULL),
(37, 1, 1, 'mumbai central', 'mumbai airport', 125.00, 'pending', '2026-03-12 11:34:45', NULL),
(38, 16, 1, 'mumbai central', 'mumbai airport', 263.00, 'pending', '2026-03-12 11:35:32', NULL),
(39, 1, 5, 'kengeri', 'majestic', 185.00, 'pending', '2026-03-23 09:35:10', NULL),
(40, 16, 1, 'kengeri', 'yelahanka', 405.95, 'pending', '2026-03-23 09:51:15', NULL),
(41, 16, 1, 'kengeri', 'salem', 198911.75, 'pending', '2026-03-23 09:52:18', NULL),
(42, 16, 1, 'kengeri', 'salem', 198911.75, 'pending', '2026-03-23 09:52:20', NULL),
(43, 16, 1, 'mumbai central', 'mumbai airport', 263.00, 'confirmed', '2026-04-02 08:19:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `car_number` varchar(20) NOT NULL,
  `rating` decimal(3,2) DEFAULT 5.00,
  `status` enum('available','busy','offline') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `car_model`, `car_number`, `rating`, `status`, `created_at`) VALUES
(1, 'Michael Johnson', '+1-555-1001', 'Toyota Camry 2022', 'CAB-1234', 4.80, 'available', '2026-02-08 04:03:05'),
(2, 'Sarah Williams', '+1-555-1002', 'Honda Accord 2023', 'CAB-2345', 4.90, 'available', '2026-02-08 04:03:05'),
(3, 'David Martinez', '+1-555-1003', 'Hyundai Elantra 2021', 'CAB-3456', 4.60, 'busy', '2026-02-08 04:03:05'),
(4, 'Emily Garcia', '+1-555-1004', 'Nissan Altima 2022', 'CAB-4567', 4.70, 'offline', '2026-02-08 04:03:05'),
(5, 'James Rodriguez', '+1-555-1005', 'Ford Fusion 2023', 'CAB-5678', 4.90, 'available', '2026-02-08 04:03:05'),
(6, 'Lisa Anderson', '+1-555-1006', 'Chevrolet Malibu 2021', 'CAB-6789', 4.50, 'available', '2026-02-08 04:03:05'),
(7, 'Robert Taylor', '+1-555-1007', 'Volkswagen Jetta 2022', 'CAB-7890', 4.80, 'available', '2026-02-08 04:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `status`, `created_at`) VALUES
(1, 1, 1, 25.50, 'Credit Card', 'TXN-20260201-001', 'completed', '2026-02-01 05:30:00'),
(2, 2, 1, 32.00, 'Debit Card', 'TXN-20260202-002', 'completed', '2026-02-02 09:25:00'),
(3, 3, 2, 45.75, 'Credit Card', 'TXN-20260203-003', 'completed', '2026-02-03 03:45:00'),
(4, 4, 2, 18.25, 'Cash', 'TXN-20260207-004', 'pending', '2026-02-07 11:00:00'),
(5, 5, 3, 28.50, 'UPI', 'TXN-20260204-005', 'completed', '2026-02-04 07:00:00'),
(6, 6, 3, 35.00, 'Credit Card', 'TXN-20260208-006', 'pending', '2026-02-08 01:30:00'),
(7, 7, 4, 22.00, 'Debit Card', 'TXN-20260205-007', 'completed', '2026-02-05 13:45:00'),
(8, 8, 4, 15.50, 'Cash', 'TXN-20260206-008', 'failed', '2026-02-06 04:00:00'),
(9, 9, 5, 40.25, 'Credit Card', 'TXN-20260206-009', 'completed', '2026-02-06 06:30:00'),
(10, 10, 5, 30.75, 'UPI', 'TXN-20260208-010', 'pending', '2026-02-08 03:00:00'),
(11, 11, 1, 55.00, 'Credit Card', 'TXN-20260207-011', 'completed', '2026-02-07 09:00:00'),
(12, 12, 2, 48.50, 'Debit Card', 'TXN-20260207-012', 'completed', '2026-02-07 06:15:00'),
(13, 13, 1, 36.55, 'UPI', 'TXN-20260212-9553', 'completed', '2026-02-12 05:31:27'),
(14, 14, 10, 0.00, 'Credit Card', 'TXN-20260212-8381', 'completed', '2026-02-12 06:58:32'),
(15, 15, 1, 0.00, 'UPI', 'TXN-20260212-3660', 'completed', '2026-02-12 09:09:43'),
(21, 21, 1, 1.00, 'Cash', 'TXN-20260212-4225', 'completed', '2026-02-12 11:24:18'),
(22, 22, 10, 90.00, 'Credit Card', 'TXN-20260212-7867', 'pending', '2026-02-12 11:25:33'),
(23, 23, 15, 755.00, 'Credit Card', 'TXN-20260212-1325', 'completed', '2026-02-12 12:14:47'),
(24, 24, 10, 85.00, 'Credit Card', 'TXN-20260212-7479', 'completed', '2026-02-12 12:17:13'),
(25, 25, 15, 7.50, 'Credit Card', 'TXN-20260212-1194', 'pending', '2026-02-12 12:22:14'),
(26, 26, 15, 1467.50, 'Credit Card', 'TXN-20260212-8362', 'completed', '2026-02-12 12:23:26'),
(27, 27, 15, 115.00, 'Credit Card', 'TXN-20260212-1045', 'pending', '2026-02-12 12:24:45'),
(28, 28, 1, 35.00, 'Credit Card', 'TXN-20260305-4854', 'pending', '2026-03-05 10:47:31'),
(29, 29, 16, 17.50, 'Credit Card', 'TXN-20260305-9796', 'pending', '2026-03-05 10:49:03'),
(30, 30, 16, 62.50, 'Credit Card', 'TXN-20260305-2334', 'pending', '2026-03-05 11:18:04'),
(31, 31, 16, 405.95, 'UPI', 'TXN-20260312-6312', 'completed', '2026-03-12 10:43:15'),
(32, 32, 16, 210254.60, 'Credit Card', 'TXN-20260312-9324', 'pending', '2026-03-12 10:44:12'),
(33, 33, 1, 5.00, 'Credit Card', 'TXN-20260312-7476', 'pending', '2026-03-12 10:45:08'),
(34, 34, 16, 274.25, 'Credit Card', 'TXN-20260312-9038', 'pending', '2026-03-12 10:50:32'),
(35, 35, 16, 263.00, 'Credit Card', 'TXN-20260312-1955', 'pending', '2026-03-12 11:00:24'),
(36, 36, 16, 263.00, 'Credit Card', 'TXN-20260312-1330', 'pending', '2026-03-12 11:23:17'),
(37, 37, 1, 125.00, 'Credit Card', 'TXN-20260312-6048', 'pending', '2026-03-12 11:34:45'),
(38, 38, 16, 263.00, 'Credit Card', 'TXN-20260312-7060', 'pending', '2026-03-12 11:35:32'),
(39, 39, 1, 185.00, 'Credit Card', 'TXN-20260323-6119', 'pending', '2026-03-23 09:35:10'),
(40, 40, 16, 405.95, 'Credit Card', 'TXN-20260323-8454', 'pending', '2026-03-23 09:51:15'),
(41, 41, 16, 198911.75, 'Credit Card', 'TXN-20260323-5971', 'pending', '2026-03-23 09:52:18'),
(42, 42, 16, 198911.75, 'Credit Card', 'TXN-20260323-6122', 'pending', '2026-03-23 09:52:20'),
(43, 43, 16, 263.00, 'UPI', '2345654323', 'completed', '2026-04-02 08:19:09');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `driver_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 1, 5, 'Excellent service! Very professional driver.', '2026-02-01 05:35:00'),
(2, 2, 1, 2, 5, 'Great experience, smooth ride!', '2026-02-02 09:30:00'),
(3, 3, 2, 3, 4, 'Good driver but car could be cleaner.', '2026-02-03 03:50:00'),
(4, 5, 3, 4, 5, 'Amazing! On time and very friendly.', '2026-02-04 07:05:00'),
(5, 7, 4, 5, 4, 'Nice ride, but took a longer route.', '2026-02-05 13:50:00'),
(6, 9, 5, 7, 5, 'Perfect! Best driver ever!', '2026-02-06 06:35:00'),
(7, 11, 1, 3, 5, '<img src=x onerror=alert(1)>', '2026-02-07 09:05:00'),
(8, 12, 2, 4, 5, 'Very satisfied with the ride!', '2026-02-07 06:20:00'),
(9, 14, 10, 7, 5, 'cabbie was friendly', '2026-02-12 07:06:13'),
(10, 14, 10, 7, 3, 'fghj,', '2026-02-12 09:26:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default-avatar.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `profile_pic`, `created_at`, `profile_image`) VALUES
(1, 'john_doe', 'john@example.com', 'password123', '+1-555-0001', 'index.php', '2026-02-08 04:03:05', NULL),
(2, 'jane_smith', 'jane@example.com', 'password456', '+1-555-0002', 'default-avatar.png', '2026-02-08 04:03:05', NULL),
(3, 'bob_wilson', 'bob@example.com', 'password789', '+1-555-0003', 'default-avatar.png', '2026-02-08 04:03:05', NULL),
(4, 'alice_brown', 'alice@example.com', 'alice2026', '+1-555-0004', 'default-avatar.png', '2026-02-08 04:03:05', NULL),
(5, 'charlie_davis', 'charlie@example.com', 'charlie123', '+1-555-0005', 'default-avatar.png', '2026-02-08 04:03:05', NULL),
(6, 'ROCKY', 'rocky@gmail.com', 'Jocky@123', '9933442288', 'default-avatar.png', '2026-02-08 04:14:10', NULL),
(9, 'Toxic', 'toxic@gmail.com', '$2y$10$neafuuaKaFtcq0zlfQ3Z1.HVETGTmWoQ.crLl2buqcL0c08qG7VbG', '4455332211', 'default-avatar.png', '2026-02-08 05:10:55', NULL),
(10, 'KGF', 'kgf@gmail.com', '$2y$10$rzvj46Ff5Up/KlukCkUHlOfpDoLdytKeR3DMCuxiyz/gGgfsc99ry', '8888777734', 'default-avatar.png', '2026-02-12 06:56:34', NULL),
(11, 'sakshath', 'dhdh@gmail.com', '$2y$10$XCun9YEL3BjV51dKTN9iUOZhriRiwKzdhmKLaA22mCPGO2ideuuIe', '8118111188118', 'default-avatar.png', '2026-02-12 09:21:14', NULL),
(15, 'ada', 'ab@gmail.com', '$2y$10$Ycl3pTp0KI3fEMkGz7CKDeIlS7jKXqm6y7m2NOw0kUC0fcsT4fca2', '1234567890', '7.jpeg', '2026-02-12 11:43:59', NULL),
(16, 'vevion', 'vevion@gmail.com', '$2y$10$Jnf9aAGjBN6Ev2wwpkIeRuWTXhdAlz1ad249tcFpnz3j/ObnAvKX.', '3456781234', 'default-avatar.png', '2026-03-05 09:41:30', 'avatar_16_c7af2b99b82c1474.jpg'),
(17, 'vikram', 'vikram@gmail.com', '$2y$10$R6twiWfaYJ9lHPweU0VDWe9I2aisu5MzNo7Kbbs52ieNggHisKYDu', '87654678762', 'default-avatar.png', '2026-04-02 08:39:29', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

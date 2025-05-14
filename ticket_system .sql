-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 06:58 AM
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
-- Database: `ticket_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `source_station` varchar(50) NOT NULL,
  `destination_station` varchar(50) NOT NULL,
  `booking_date` datetime NOT NULL,
  `departure_time` time NOT NULL,
  `class` varchar(20) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `train_name` varchar(50) NOT NULL,
  `train_number` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Confirmed',
  `passengers` int(11) NOT NULL DEFAULT 1,
  `ticket_type` enum('one-way','return') NOT NULL DEFAULT 'one-way',
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `user_id`, `source_station`, `destination_station`, `booking_date`, `departure_time`, `class`, `fare`, `train_name`, `train_number`, `status`, `passengers`, `ticket_type`, `return_date`, `created_at`) VALUES
(71, 'BK-68241AB70CABB', 8, 'Misr Station', 'Luxor Station', '2025-05-14 06:23:19', '08:15:00', 'second', 63.00, 'Cairo-Luxor Day Train', '980', 'Confirmed', 1, 'one-way', NULL, '2025-05-14 04:23:19'),
(72, 'BK-68241C9E2383C', 8, 'Aswan Station', 'Misr Station', '2025-05-14 06:31:26', '17:15:00', 'second', 616.00, 'Aswan-Cairo Sleeper', '89', 'Confirmed', 1, 'one-way', NULL, '2025-05-14 04:31:26'),
(73, 'BK-682422434701A', 1, 'Aswan Station', 'Misr Station', '2025-05-14 06:55:31', '17:15:00', 'first', 660.00, 'Aswan-Cairo Sleeper', '89', 'Confirmed', 1, 'one-way', NULL, '2025-05-14 04:55:31');

-- --------------------------------------------------------

--
-- Table structure for table `compensation_requests`
--

CREATE TABLE `compensation_requests` (
  `request_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL COMMENT 'Reference to bookings.id',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason_for_request` text NOT NULL,
  `detailed_description` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','PROCESSED') DEFAULT 'PENDING',
  `admin_notes` text DEFAULT NULL,
  `processed_by_user_id` int(11) DEFAULT NULL COMMENT 'Admin user ID who processed it, matches users.user_id',
  `compensation_amount` decimal(10,2) DEFAULT NULL,
  `processed_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `compensation_requests`
--

INSERT INTO `compensation_requests` (`request_id`, `user_id`, `booking_id`, `request_date`, `reason_for_request`, `detailed_description`, `status`, `admin_notes`, `processed_by_user_id`, `compensation_amount`, `processed_date`, `created_at`, `updated_at`) VALUES
(10, 1, 73, '2025-05-14 04:56:37', 'Significant Delay', 'the train delayed about 1 hour', 'PENDING', NULL, NULL, NULL, NULL, '2025-05-14 04:56:37', '2025-05-14 04:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'client'),
(3, 'station_master');

-- --------------------------------------------------------

--
-- Table structure for table `stations`
--

CREATE TABLE `stations` (
  `station_id` int(11) NOT NULL,
  `station_code` varchar(10) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 for active, 0 for inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`station_id`, `station_code`, `station_name`, `city`, `created_at`, `updated_at`, `is_active`) VALUES
(22, 'ALX', 'Sidi Gaber Station', 'Alexandria', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(23, 'MISR', 'Misr Station', 'Cairo', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(24, 'GZ', 'Giza Station', 'Giza', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(25, 'LUX', 'Luxor Station', 'Luxor', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(26, 'ASW', 'Aswan Station', 'Aswan', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(27, 'PTS', 'Port Said Station', 'Port Said', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(28, 'SUE', 'Suez Station', 'Suez', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(29, 'ISMA', 'Ismailia Station', 'Ismailia', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(30, 'DMN', 'Damanhur Station', 'Damanhur', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(31, 'TNT', 'Tanta Station', 'Tanta', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(32, 'ZAG', 'Zagazig Station', 'Zagazig', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(33, 'MNY', 'Minya Station', 'Minya', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(34, 'ASY', 'Asyut Station', 'Asyut', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(35, 'SOH', 'Sohag Station', 'Sohag', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(36, 'QEN', 'Qena Station', 'Qena', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(37, 'BNH', 'Banha Station', 'Banha', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(38, 'KFS', 'Kafr El Sheikh Station', 'Kafr El Sheikh', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(39, 'MNS', 'Mansoura Station', 'Mansoura', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(40, 'DMTA', 'Damietta Station', 'Damietta', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(41, 'FAY', 'Faiyum Station', 'Faiyum', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1),
(42, 'BNSF', 'Beni Suef Station', 'Beni Suef', '2025-05-14 03:47:45', '2025-05-14 03:47:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `station_distances`
--

CREATE TABLE `station_distances` (
  `distance_id` int(11) NOT NULL,
  `station1_id` int(11) NOT NULL,
  `station2_id` int(11) NOT NULL,
  `distance` decimal(10,2) NOT NULL COMMENT 'Distance in miles',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `station_distances`
--

INSERT INTO `station_distances` (`distance_id`, `station1_id`, `station2_id`, `distance`, `created_at`) VALUES
(1, 24, 22, 300.00, '2025-05-10 16:14:43'),
(2, 36, 32, 215.00, '2025-05-10 16:14:43'),
(4, 24, 25, 400.00, '2025-05-11 20:03:34'),
(6, 22, 23, 130.00, '2025-05-14 04:06:50'),
(7, 23, 24, 10.00, '2025-05-14 04:06:50'),
(8, 23, 25, 420.00, '2025-05-14 04:06:50'),
(9, 23, 26, 550.00, '2025-05-14 04:06:50'),
(10, 23, 27, 140.00, '2025-05-14 04:06:50'),
(11, 23, 29, 80.00, '2025-05-14 04:06:50'),
(12, 23, 30, 75.00, '2025-05-14 04:06:50'),
(13, 23, 31, 55.00, '2025-05-14 04:06:50'),
(14, 23, 33, 150.00, '2025-05-14 04:06:50'),
(15, 23, 34, 230.00, '2025-05-14 04:06:50'),
(16, 23, 35, 290.00, '2025-05-14 04:06:50'),
(17, 23, 36, 380.00, '2025-05-14 04:06:50'),
(18, 30, 31, 20.00, '2025-05-14 04:06:50'),
(19, 22, 30, 40.00, '2025-05-14 04:06:50'),
(20, 25, 26, 130.00, '2025-05-14 04:06:50'),
(21, 33, 34, 80.00, '2025-05-14 04:06:50'),
(22, 34, 35, 60.00, '2025-05-14 04:06:50'),
(23, 35, 36, 90.00, '2025-05-14 04:06:50'),
(24, 25, 36, 35.00, '2025-05-14 04:06:50'),
(25, 27, 29, 50.00, '2025-05-14 04:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `train_id` int(11) NOT NULL,
  `train_number` varchar(20) NOT NULL,
  `train_name` varchar(100) NOT NULL,
  `source_station_id` int(11) NOT NULL,
  `destination_station_id` int(11) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `running_days` varchar(20) NOT NULL COMMENT 'Comma separated days (0=Sun,1=Mon,...)',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`train_id`, `train_number`, `train_name`, `source_station_id`, `destination_station_id`, `departure_time`, `arrival_time`, `running_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1237, '903', 'Special Express (Cairo-Alex)', 23, 22, '07:00:00', '09:50:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1238, '915', 'VIP Train (Cairo-Alex)', 23, 22, '14:00:00', '16:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1239, '904', 'Special Express (Alex-Cairo)', 22, 23, '08:00:00', '10:50:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1240, '916', 'VIP Train (Alex-Cairo)', 22, 23, '15:00:00', '17:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1241, '980', 'Cairo-Luxor Day Train', 23, 25, '08:15:00', '18:00:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1242, '1980', 'Luxor-Cairo Day Train', 25, 23, '07:30:00', '17:15:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1243, '88', 'Cairo-Aswan Sleeper', 23, 26, '19:30:00', '09:20:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1244, '89', 'Aswan-Cairo Sleeper', 26, 23, '17:15:00', '06:50:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1245, '945', 'Cairo-Port Said Express', 23, 27, '09:00:00', '12:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1246, '952', 'Port Said-Cairo Express', 27, 23, '14:00:00', '17:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1247, '196', 'Cairo-Damanhur Local', 23, 30, '11:00:00', '12:45:00', '0,1,2,3,4,5', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1248, '567', 'Tanta-Mansoura Commuter', 31, 39, '16:00:00', '17:05:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1249, '568', 'Mansoura-Tanta Commuter', 39, 31, '07:30:00', '08:35:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1250, '2008', 'Cairo-Aswan VIP', 23, 26, '20:00:00', '08:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1251, '2009', 'Aswan-Cairo VIP', 26, 23, '18:00:00', '06:30:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1252, '990', 'Cairo-Sohag Express', 23, 35, '10:30:00', '17:00:00', '0,1,2,3,4,5,6', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1253, '163', 'Cairo-Asyut Regular', 23, 34, '13:15:00', '19:45:00', '1,3,5', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50'),
(1254, '934', 'Alexandria-Luxor (via Cairo)', 22, 25, '22:30:00', '10:00:00', '0,2,4', 1, '2025-05-14 04:00:50', '2025-05-14 04:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `train_schedule`
--

CREATE TABLE `train_schedule` (
  `id` int(11) NOT NULL,
  `train_number` varchar(50) NOT NULL,
  `source_station` varchar(100) NOT NULL COMMENT 'Name or code of the source station for this schedule entry',
  `destination_station` varchar(100) NOT NULL COMMENT 'Name or code of the destination station for this schedule entry',
  `expected_departure_time` datetime DEFAULT NULL COMMENT 'Expected departure date and time',
  `expected_arrival_time` datetime DEFAULT NULL COMMENT 'Expected arrival date and time',
  `actual_departure_time` datetime DEFAULT NULL,
  `actual_arrival_time` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'SCHEDULED' COMMENT 'e.g., SCHEDULED, ON_TIME, DELAYED, CANCELLED, ARRIVED, DEPARTED',
  `public_notice` varchar(255) DEFAULT NULL COMMENT 'Optional public announcement for this stop',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `train_schedule`
--

INSERT INTO `train_schedule` (`id`, `train_number`, `source_station`, `destination_station`, `expected_departure_time`, `expected_arrival_time`, `actual_departure_time`, `actual_arrival_time`, `status`, `public_notice`, `created_at`, `updated_at`) VALUES
(6, '903', 'Misr Station', 'Sidi Gaber Station', '2024-05-15 07:00:00', '2024-05-15 09:50:00', NULL, NULL, 'ON_TIME', 'Boarding from platform 5.', '2025-05-14 04:04:07', '2025-05-14 04:04:44'),
(7, '88', 'Misr Station', 'Aswan Station', '2024-05-15 19:30:00', '2024-05-16 09:20:00', NULL, NULL, 'ON_TIME', 'Sleeper car assignments at information desk.', '2025-05-14 04:04:07', '2025-05-14 04:04:47'),
(8, '945', 'Misr Station', 'Port Said Station', '2024-05-15 09:00:00', '2024-05-15 12:30:00', NULL, NULL, 'ON_TIME', NULL, '2025-05-14 04:04:07', '2025-05-14 04:04:50'),
(9, '915', 'Misr Station', 'Sidi Gaber Station', '2024-05-16 14:00:00', '2024-05-16 16:30:00', NULL, NULL, 'ON_TIME', 'This is a VIP service.', '2025-05-14 04:04:07', '2025-05-14 04:04:53'),
(10, '980', 'Misr Station', 'Luxor Station', '2024-05-17 08:15:00', '2024-05-17 18:00:00', NULL, NULL, 'ON_TIME', NULL, '2025-05-14 04:04:07', '2025-05-14 04:04:55');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` enum('BOOKING_PAYMENT','BOOKING_REFUND','ACCOUNT_RECHARGE','COMPENSATION_CREDIT','ADMIN_ADJUSTMENT') NOT NULL,
  `amount` decimal(10,2) NOT NULL COMMENT 'Positive for credits/recharges, negative for payments/debits',
  `booking_id` int(11) DEFAULT NULL COMMENT 'Reference to bookings.id',
  `related_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'e.g., compensation_request_id, admin_recharge_log_id',
  `description` varchar(255) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) NOT NULL COMMENT 'Transaction type (e.g., ADMIN_RECHARGE)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `transaction_type`, `amount`, `booking_id`, `related_id`, `description`, `transaction_date`, `type`) VALUES
(1, 12, 'ACCOUNT_RECHARGE', 20.00, NULL, NULL, 'Wallet recharge via system', '2025-05-11 18:38:43', ''),
(3, 8, 'ACCOUNT_RECHARGE', 20.00, NULL, NULL, 'Wallet recharge via system', '2025-05-11 19:06:39', ''),
(5, 8, 'ACCOUNT_RECHARGE', 50.00, NULL, NULL, 'Wallet recharge via system', '2025-05-11 20:22:01', ''),
(6, 8, 'ACCOUNT_RECHARGE', 20.00, NULL, NULL, 'Wallet recharge via system', '2025-05-11 23:41:35', ''),
(7, 12, 'BOOKING_PAYMENT', 50.00, NULL, NULL, 'Recharge by Admin ID: 19', '2025-05-12 18:12:12', 'ADMIN_RECHARGE'),
(8, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-12 19:02:41', ''),
(9, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-12 19:03:03', ''),
(10, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-12 19:09:44', ''),
(11, 8, 'ACCOUNT_RECHARGE', 10.00, NULL, NULL, 'Wallet recharge', '2025-05-13 00:02:37', ''),
(12, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 00:02:50', ''),
(13, 8, 'ACCOUNT_RECHARGE', 200.00, NULL, NULL, 'Wallet recharge', '2025-05-13 00:13:35', ''),
(14, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 00:13:46', ''),
(15, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 00:16:43', ''),
(16, 8, 'ACCOUNT_RECHARGE', 11.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 00:23:05', ''),
(17, 8, 'ACCOUNT_RECHARGE', 10.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 00:23:14', ''),
(18, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 00:27:49', ''),
(19, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User//recharge.php', '2025-05-13 00:33:49', ''),
(20, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 02:17:10', ''),
(21, 8, 'ACCOUNT_RECHARGE', 300.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 02:30:41', ''),
(22, 8, 'ACCOUNT_RECHARGE', 1000.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User/recharge.php', '2025-05-13 02:30:49', ''),
(23, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge via http://localhost/GoTrain/views/User//recharge.php', '2025-05-13 02:31:05', ''),
(24, 1, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 02:39:42', ''),
(25, 1, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 02:39:56', ''),
(26, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822BBAFDE084', '2025-05-13 03:25:35', ''),
(27, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822BBC40F829', '2025-05-13 03:25:56', ''),
(28, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C1B018914', '2025-05-13 03:51:12', ''),
(29, 8, 'BOOKING_PAYMENT', 96.75, NULL, NULL, 'Ticket payment for Booking BK-6822C1C337471', '2025-05-13 03:51:31', ''),
(30, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C1D4C5F54', '2025-05-13 03:51:48', ''),
(31, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C21675D90', '2025-05-13 03:52:54', ''),
(32, 8, 'BOOKING_PAYMENT', 430.00, NULL, NULL, 'Ticket payment for Booking BK-6822C235B1D99', '2025-05-13 03:53:25', ''),
(33, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C2B1D6E43', '2025-05-13 03:55:29', ''),
(34, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 03:55:38', ''),
(35, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C30796357', '2025-05-13 03:56:55', ''),
(36, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822C45D3B7A3', '2025-05-13 04:02:37', ''),
(37, 8, 'BOOKING_PAYMENT', 464.40, NULL, NULL, 'Ticket payment for Booking BK-6822DCAE9B69D', '2025-05-13 05:46:22', ''),
(38, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822DCC92DA34', '2025-05-13 05:46:49', ''),
(39, 8, 'ACCOUNT_RECHARGE', 31.00, NULL, NULL, 'Wallet recharge', '2025-05-13 05:47:25', ''),
(40, 8, 'BOOKING_PAYMENT', 174.15, NULL, NULL, 'Ticket payment for Booking BK-6822E264BADA2', '2025-05-13 06:10:44', ''),
(41, 8, 'BOOKING_PAYMENT', 1548.00, NULL, NULL, 'Ticket payment for Booking BK-6822E28063077', '2025-05-13 06:11:12', ''),
(42, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 06:12:18', ''),
(43, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 06:13:12', ''),
(44, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822F19E8BD48', '2025-05-13 07:15:42', ''),
(45, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6823D1DD06E5E', '2025-05-13 23:12:29', ''),
(46, 8, 'ACCOUNT_RECHARGE', 100.00, NULL, NULL, 'Wallet recharge', '2025-05-13 23:14:06', ''),
(47, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-68240A3B65706', '2025-05-14 03:12:59', ''),
(48, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-68240A8E099B2', '2025-05-14 03:14:22', ''),
(49, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-68240AA52D806', '2025-05-14 03:14:45', ''),
(50, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-68240B01CE4AF', '2025-05-14 03:16:17', ''),
(51, 8, 'BOOKING_PAYMENT', 63.00, NULL, NULL, 'Ticket payment for Booking BK-68241AB70CABB', '2025-05-14 04:23:19', ''),
(52, 8, 'ACCOUNT_RECHARGE', 800.00, NULL, NULL, 'Wallet recharge', '2025-05-14 04:31:14', ''),
(53, 8, 'BOOKING_PAYMENT', 616.00, NULL, NULL, 'Ticket payment for Booking BK-68241C9E2383C', '2025-05-14 04:31:26', ''),
(54, 1, 'ACCOUNT_RECHARGE', 400.00, NULL, NULL, 'Wallet recharge', '2025-05-14 04:55:12', ''),
(55, 1, 'BOOKING_PAYMENT', 660.00, NULL, NULL, 'Ticket payment for Booking BK-682422434701A', '2025-05-14 04:55:31', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `last_recharge_date` date DEFAULT NULL,
  `roleid` int(11) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `balance`, `last_recharge_date`, `roleid`, `role`, `created_at`, `last_login`, `updated_at`) VALUES
(1, 'Abdelazem', 'Abdelaze@gmail.com', 'Se15@g135', 210.00, '2025-05-14', 2, 'user', '2025-05-08 20:11:03', NULL, '2025-05-14 04:55:31'),
(8, 'Mohamed', 'Mohamed@gmail.com', 'Se15@g135', 227.50, '2025-05-14', 2, 'user', '2025-05-08 20:11:03', NULL, '2025-05-14 04:31:26'),
(12, 'Mahmoud', 'Mahmoud@gmail.com', 'Se15@g135', 112.75, '2025-05-12', 1, 'admin', '2025-05-08 20:11:03', NULL, '2025-05-14 04:17:49'),
(19, 'Yahia', 'yahia@gmail.com', 'Se15@g135', 0.00, NULL, 1, 'admin', '2025-05-08 20:11:03', NULL, '2025-05-14 04:17:51'),
(22, 'Tarek', 'Mahmoud@gmail.com', 'Se15@g135', 800.00, '2025-05-06', 3, 'station master', '2025-05-08 20:11:03', NULL, '2025-05-14 04:17:53'),
(31, 'Tawfik', 'Tawfik@gmail.com', 'Se15@g135', 0.00, NULL, 3, 'station master', '2025-05-13 06:26:01', NULL, '2025-05-14 04:17:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `compensation_requests`
--
ALTER TABLE `compensation_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `processed_by_user_id` (`processed_by_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`station_id`),
  ADD UNIQUE KEY `station_code` (`station_code`),
  ADD KEY `idx_station_name` (`station_name`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `station_distances`
--
ALTER TABLE `station_distances`
  ADD PRIMARY KEY (`distance_id`),
  ADD UNIQUE KEY `unique_station_pair` (`station1_id`,`station2_id`),
  ADD KEY `station2_id` (`station2_id`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`train_id`),
  ADD UNIQUE KEY `train_number` (`train_number`),
  ADD KEY `destination_station_id` (`destination_station_id`),
  ADD KEY `idx_train_number` (`train_number`),
  ADD KEY `idx_source_destination` (`source_station_id`,`destination_station_id`);

--
-- Indexes for table `train_schedule`
--
ALTER TABLE `train_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_train_number` (`train_number`),
  ADD KEY `idx_expected_departure_time` (`expected_departure_time`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `roleid` (`roleid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `compensation_requests`
--
ALTER TABLE `compensation_requests`
  MODIFY `request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `station_distances`
--
ALTER TABLE `station_distances`
  MODIFY `distance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1255;

--
-- AUTO_INCREMENT for table `train_schedule`
--
ALTER TABLE `train_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `compensation_requests`
--
ALTER TABLE `compensation_requests`
  ADD CONSTRAINT `compensation_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compensation_requests_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compensation_requests_ibfk_3` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `station_distances`
--
ALTER TABLE `station_distances`
  ADD CONSTRAINT `station_distances_ibfk_1` FOREIGN KEY (`station1_id`) REFERENCES `stations` (`station_id`),
  ADD CONSTRAINT `station_distances_ibfk_2` FOREIGN KEY (`station2_id`) REFERENCES `stations` (`station_id`);

--
-- Constraints for table `trains`
--
ALTER TABLE `trains`
  ADD CONSTRAINT `trains_ibfk_1` FOREIGN KEY (`source_station_id`) REFERENCES `stations` (`station_id`),
  ADD CONSTRAINT `trains_ibfk_2` FOREIGN KEY (`destination_station_id`) REFERENCES `stations` (`station_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

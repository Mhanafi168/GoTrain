-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 09:24 AM
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
(2, 'BK789012', 8, 'Boston', 'Washington DC', '2023-06-16 09:15:00', '12:30:00', 'Business', 90.00, 'Coastal Connector', 'CC300', 'Confirmed', 2, 'one-way', NULL, '2025-05-12 21:54:28'),
(3, 'BK345678', 22, 'New York', 'Washington DC', '2023-06-10 16:45:00', '07:45:00', 'First', 120.00, 'Capital Limited', 'CL200', 'Confirmed', 1, 'one-way', NULL, '2025-05-12 21:54:28'),
(4, 'BK901234', 22, 'Washington DC', 'New York', '2023-06-18 11:20:00', '15:20:00', 'Economy', 40.00, 'Capital Limited', 'CL200', 'Confirmed', 3, 'one-way', NULL, '2025-05-12 21:54:28'),
(5, 'BK567890', 8, 'New York', 'Chicago', '2023-06-20 10:00:00', '09:00:00', 'Business', 150.00, 'Midwest Express', 'MW400', 'Confirmed', 2, 'one-way', NULL, '2025-05-12 21:54:28'),
(6, 'BK112233', 22, 'Chicago', 'New York', '2023-05-25 13:10:00', '14:30:00', 'Economy', 80.00, 'Midwest Express', 'MW400', 'Completed', 1, 'one-way', NULL, '2025-05-12 21:54:28'),
(7, 'BK445566', 8, 'Boston', 'New York', '2023-05-28 08:45:00', '17:15:00', 'First', 75.00, 'Northeast Express', 'NE100', 'Cancelled', 1, 'one-way', NULL, '2025-05-12 21:54:28'),
(43, 'BK-6822C1B018914', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:51:12', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 03:51:12'),
(44, 'BK-6822C1C337471', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:51:31', '14:00:00', 'economy', 96.75, 'Express 2', 'T456', 'Confirmed', 3, 'one-way', NULL, '2025-05-13 03:51:31'),
(45, 'BK-6822C1D4C5F54', 8, 'New York Penn Station', 'Boston South Station', '2025-05-13 05:51:48', '08:00:00', 'economy', 32.25, 'Express 1', 'T123', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 03:51:48'),
(48, 'BK-6822C21675D90', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:52:54', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 03:52:54'),
(49, 'BK-6822C235B1D99', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:53:25', '14:00:00', 'first', 430.00, 'Express 2', 'T456', 'Confirmed', 5, 'one-way', NULL, '2025-05-13 03:53:25'),
(50, 'BK-6822C2B1D6E43', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:55:29', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 03:55:29'),
(51, 'BK-6822C30796357', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 05:56:55', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 03:56:55'),
(52, 'BK-6822C45D3B7A3', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 06:02:37', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 04:02:37'),
(53, 'BK-6822DCAE9B69D', 8, 'New York Penn Station', 'Boston South Station', '2025-05-13 07:46:22', '08:00:00', 'first', 464.40, 'Express 1', 'T123', 'Confirmed', 3, 'return', '2025-05-29', '2025-05-13 05:46:22'),
(54, 'BK-6822DCC92DA34', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 07:46:49', '14:00:00', 'economy', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 05:46:49'),
(55, 'BK-6822E264BADA2', 8, 'New York Penn Station', 'Boston South Station', '2025-05-13 08:10:44', '08:00:00', 'second', 174.15, 'Express 1', 'T123', 'Confirmed', 3, 'return', '2025-05-23', '2025-05-13 06:10:44'),
(56, 'BK-6822E28063077', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 08:11:12', '14:00:00', 'first', 1548.00, 'Express 2', 'T456', 'Confirmed', 10, 'return', '2025-05-30', '2025-05-13 06:11:12'),
(57, 'BK-6822F19E8BD48', 8, 'Boston South Station', 'New York Penn Station', '2025-05-13 09:15:42', '14:00:00', 'second', 32.25, 'Express 2', 'T456', 'Confirmed', 1, 'one-way', NULL, '2025-05-13 07:15:42');

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
(1, 8, 2, '2025-05-11 18:16:05', 'Significant Delay', 'asfas fas fsalk fsa flkh lsakhfl as lkfhl \r\n', 'APPROVED', 'dsgf d fsdfg sdgf', 8, 30.00, '2025-05-12 11:27:10', '2025-05-11 18:16:05', '2025-05-12 11:27:10'),
(4, 8, 2, '2025-05-12 19:05:24', 'Train Cancellation', 'sadfdg sdag sda gagsa', 'APPROVED', 'xv', 8, 50.00, '2025-05-13 00:50:01', '2025-05-12 19:05:24', '2025-05-13 00:50:01'),
(5, 8, 2, '2025-05-13 00:32:18', 'Train Cancellation', 'dfxhdf hfshdfh dfssadgdsgsadgds', 'REJECTED', NULL, 8, NULL, '2025-05-13 00:49:51', '2025-05-13 00:32:18', '2025-05-13 00:49:51'),
(6, 8, 43, '2025-05-13 05:47:54', 'Train Cancellation', 'safas gfsdgsdgh dsfhgfdh df', 'APPROVED', 'safasf s', 19, 34.00, '2025-05-13 05:51:58', '2025-05-13 05:47:54', '2025-05-13 05:51:58'),
(7, 8, 2, '2025-05-13 06:14:10', 'Significant Delay', 'asfsadgdsgh ds hdsahsdh ', 'REJECTED', NULL, 19, NULL, '2025-05-13 06:21:22', '2025-05-13 06:14:10', '2025-05-13 06:21:22'),
(8, 8, 7, '2025-05-13 07:16:11', 'Significant Delay', 'SDGDGAG SDGSGSGS DGDGDDFDFSGDFG', 'APPROVED', 'DFHD', 19, 20.00, '2025-05-13 07:18:01', '2025-05-13 07:16:11', '2025-05-13 07:18:01');

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
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'United States',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `has_wifi` tinyint(1) DEFAULT 0,
  `has_parking` tinyint(1) DEFAULT 0,
  `has_accessible_facilities` tinyint(1) DEFAULT 0,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `platform_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`station_id`, `station_code`, `station_name`, `city`, `state`, `country`, `latitude`, `longitude`, `zone`, `is_active`, `has_wifi`, `has_parking`, `has_accessible_facilities`, `opening_time`, `closing_time`, `platform_count`, `created_at`, `updated_at`) VALUES
(1, 'NYC', 'New York Penn Station', 'New York', 'NY', 'United States', 40.75058000, -73.99358400, 'Northeast', 1, 1, 1, 0, NULL, NULL, 1, '2025-05-10 03:10:16', '2025-05-10 03:10:16'),
(2, 'BOS', 'Boston South Station', 'Boston', 'MA', 'United States', 42.35227100, -71.05524200, 'Northeast', 1, 1, 1, 0, NULL, NULL, 1, '2025-05-10 03:10:16', '2025-05-10 03:10:16'),
(3, 'WAS', 'Washington Union Station', 'Washington', 'DC', 'United States', 38.89767500, -77.00621800, 'Mid-Atlantic', 1, 1, 1, 0, NULL, NULL, 1, '2025-05-10 03:10:16', '2025-05-10 03:10:16'),
(5, 'BAL', 'Baltimore Penn Station', 'Baltimor', 'MD', 'United States', 39.30732300, -76.61554300, 'Mid-Atlantic', 1, 1, 1, 0, NULL, NULL, 1, '2025-05-10 03:10:16', '2025-05-12 21:03:07'),
(6, 'CHI', 'Chicago Union Station', 'Chicago', 'IL', 'United States', 41.87866800, -87.64040400, 'Midwest', 1, 1, 1, 0, NULL, NULL, 1, '2025-05-10 03:10:16', '2025-05-10 03:10:16'),
(14, '123', 'Cario', 'safaf', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-12 20:36:07', '2025-05-12 20:36:07'),
(16, 'daasdf', 'sdafsdaf', 'sadfsdf', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 02:11:26', '2025-05-13 02:11:26'),
(17, 'dgjjfg', 'ghjfgfdg', 'jdfgj', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 04:04:33', '2025-05-13 04:04:33'),
(18, 'fdhg', 'fdg', 'fdgh', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 04:12:37', '2025-05-13 04:12:37'),
(19, 'asdf', 'asffa', 'sadf', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 05:48:53', '2025-05-13 05:48:53'),
(20, 'saf', 'saf', 'asf', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 06:17:08', '2025-05-13 06:17:08'),
(21, 'SAFFSA', 'SAFSAF', 'ASF', NULL, 'United States', NULL, NULL, NULL, 1, 0, 0, 0, NULL, NULL, 1, '2025-05-13 07:16:54', '2025-05-13 07:16:54');

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
(1, 1, 2, 300.00, '2025-05-10 16:14:43'),
(2, 2, 1, 215.00, '2025-05-10 16:14:43'),
(4, 1, 6, 400.00, '2025-05-11 20:03:34');

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
(123, '12', 'fhdfhgf', 5, 2, '15:36:07', '18:36:07', '0', 1, '2025-05-10 14:36:50', '2025-05-10 14:36:50'),
(1234, '132', 'gfcjfd', 1, 2, '15:43:00', '18:43:00', '0', 1, '2025-05-10 14:43:42', '2025-05-10 14:43:42'),
(1235, 'T123', 'Express 1', 1, 2, '08:00:00', '10:30:00', '1,2,3,4,5,6', 1, '2025-05-10 14:49:30', '2025-05-10 14:49:30'),
(1236, 'T456', 'Express 2', 2, 1, '14:00:00', '16:30:00', '1,2,3,4,5,6', 1, '2025-05-10 14:49:30', '2025-05-10 14:49:30');

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
(1, 'safsad', 'asfasd', 'safdas', '2025-05-10 08:23:00', '1970-01-01 01:00:00', '2025-06-04 08:24:00', '2025-05-21 08:24:00', 'On Time', 'safgasd gsdgsdgsd', '2025-05-13 05:23:34', '2025-05-13 06:19:33'),
(2, 'saf', 'asf', 'saf', '2025-05-10 08:23:00', NULL, '2025-05-20 09:23:00', '2025-05-21 09:23:00', 'delayed', 'sadify safkdjhd sf ksjadfg jhksg fad', '2025-05-13 05:23:37', '2025-05-13 06:25:10'),
(3, 'asd', 'sad', 'asd', '2025-05-09 08:49:00', '2025-05-26 08:49:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'On Time', NULL, '2025-05-13 05:49:35', '2025-05-13 05:49:35'),
(4, '12', 'sad', 'sad', '2025-05-09 09:19:00', '2025-05-14 09:19:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'On Time', NULL, '2025-05-13 06:19:19', '2025-05-13 06:19:19'),
(5, 'saf', 'asfsafsa', 'xcbvASFASF', '2025-05-16 10:17:00', NULL, '2025-05-15 10:19:00', '2025-05-15 10:19:00', 'delayed', 'SDGASDGSSDAG', '2025-05-13 07:17:11', '2025-05-13 07:20:21');

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
(44, 8, 'BOOKING_PAYMENT', 32.25, NULL, NULL, 'Ticket payment for Booking BK-6822F19E8BD48', '2025-05-13 07:15:42', '');

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
(1, 'AbdelazeFG', 'mz4669845@gmail.com', '123456', 450.00, '2025-05-13', 2, 'user', '2025-05-08 20:11:03', NULL, '2025-05-13 07:17:32'),
(8, 'yara', 'yara@example.com', '123456', 167.75, '2025-05-13', 2, 'user', '2025-05-08 20:11:03', NULL, '2025-05-13 07:16:22'),
(12, 'dina_salah', 'dina@example.com', '123456', 112.75, '2025-05-12', 1, 'user', '2025-05-08 20:11:03', NULL, '2025-05-13 06:16:52'),
(19, 'yahia', 'yahia@example.com', '123456', 0.00, NULL, 1, 'user', '2025-05-08 20:11:03', NULL, '2025-05-12 17:46:43'),
(22, 'mahmoud', 'Mahmoud@example.com', '123456', 800.00, '2025-05-06', 3, 'user', '2025-05-08 20:11:03', NULL, '2025-05-13 04:04:17'),
(30, 'yahia231', 'yasdfhia@example.com', 'sdfgasdgsd', 3.00, '2025-05-13', 2, 'user', '2025-05-13 05:57:14', NULL, '2025-05-13 07:17:36'),
(31, 'safdsa', 'asdfdsfas@sgdds.sag', 'moashfosghaf', 0.00, NULL, 2, 'user', '2025-05-13 06:26:01', NULL, '2025-05-13 06:26:01'),
(32, 'asfsaf', 'asf@gsad.saf', 'asfasf', 0.00, NULL, 2, 'user', '2025-05-13 06:27:23', NULL, '2025-05-13 06:27:23'),
(33, 'safsa', 'safds@sdg.sdgHHHHHHHHHHHHH', 'sadsdgsdgds', 0.00, NULL, 2, 'user', '2025-05-13 06:27:58', NULL, '2025-05-13 07:16:44'),
(35, 'sadfsd', 'asdf@asdfg.safdsdf', 'safdasdf', 0.00, NULL, 2, 'user', '2025-05-13 06:28:53', NULL, '2025-05-13 06:28:53');

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
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_zone` (`zone`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `compensation_requests`
--
ALTER TABLE `compensation_requests`
  MODIFY `request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `station_distances`
--
ALTER TABLE `station_distances`
  MODIFY `distance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1237;

--
-- AUTO_INCREMENT for table `train_schedule`
--
ALTER TABLE `train_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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

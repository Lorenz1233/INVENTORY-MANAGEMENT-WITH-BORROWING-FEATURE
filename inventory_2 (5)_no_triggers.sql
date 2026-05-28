-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 04:28 PM
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
-- Database: `inventory_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `action_type` varchar(80) NOT NULL,
  `table_name` varchar(80) DEFAULT NULL,
  `record_id` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`audit_id`, `actor_user_id`, `action_type`, `table_name`, `record_id`, `details`, `created_at`) VALUES
(1, 1, 'user_login', 'users', '1', NULL, '2026-05-23 02:04:13'),
(2, 1, 'category_delete', 'category', '5', '{\"category_name\":\"tubol\"}', '2026-05-23 02:15:20'),
(3, 1, 'unit_delete', 'unit', '4', '{\"unit_name\":\"tae\"}', '2026-05-23 02:15:27'),
(4, 1, 'user_role_update', 'users', '8', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-23 06:12:12'),
(5, 1, 'transaction_return', 'transactions', '13', '{\"item_id\":1,\"quantity\":1}', '2026-05-23 06:12:50'),
(6, 1, 'user_role_update', 'users', '8', '{\"old_role\":\"faculty\",\"new_role\":\"student\"}', '2026-05-23 06:14:13'),
(7, 1, 'user_role_update', 'users', '8', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-23 08:02:51'),
(8, 4, 'user_login', 'users', '4', NULL, '2026-05-23 09:11:15'),
(9, 4, 'borrow_request_create', 'borrow_request', '13', '{\"item_id\":1,\"quantity\":1}', '2026-05-23 09:15:32'),
(10, 1, 'user_login', 'users', '1', NULL, '2026-05-23 09:26:05'),
(11, 1, 'borrow_request_reject', 'borrow_request', '13', '{\"remarks\":\"\"}', '2026-05-23 09:26:24'),
(12, 5, 'user_login', 'users', '5', NULL, '2026-05-23 09:27:51'),
(13, 5, 'borrow_request_create', 'borrow_request', '14', '{\"item_id\":1,\"quantity\":1}', '2026-05-23 09:28:20'),
(14, 1, 'user_login', 'users', '1', NULL, '2026-05-23 09:29:06'),
(15, 1, 'borrow_request_approve', 'borrow_request', '14', '{\"remarks\":\"\"}', '2026-05-23 09:47:56'),
(16, 1, 'transaction_return', 'transactions', '12', '{\"item_id\":1,\"quantity\":1}', '2026-05-23 09:48:27'),
(17, 1, 'transaction_return', 'transactions', '8', '{\"item_id\":5,\"quantity\":0}', '2026-05-23 09:48:31'),
(18, 1, 'password_change', 'users', '1', NULL, '2026-05-23 09:51:27'),
(19, 1, 'user_login', 'users', '1', NULL, '2026-05-23 12:21:13'),
(20, 1, 'user_create', 'users', '9', '{\"username\":\"12345\",\"role\":\"faculty\"}', '2026-05-23 12:22:04'),
(21, 1, 'masterlist_save', 'officials_masterlist', '12345', '{\"user_type\":\"faculty\",\"user_id\":9}', '2026-05-23 12:22:04'),
(22, 9, 'user_login', 'users', '9', NULL, '2026-05-23 12:32:52'),
(23, 9, 'password_change', 'users', '9', NULL, '2026-05-23 12:33:20'),
(24, 9, 'transaction_return', 'transactions', '23', '{\"item_id\":1,\"quantity\":1}', '2026-05-23 12:33:36'),
(25, 9, 'item_create', 'items', '9', '{\"item_name\":\"glass\",\"type\":\"equipment\"}', '2026-05-23 12:34:36'),
(26, 9, 'item_create', 'items', '10', '{\"item_name\":\"tae\",\"type\":\"equipment\"}', '2026-05-23 13:49:25'),
(27, 9, 'item_update', 'items', '10', '{\"item_name\":\"tae\",\"type\":\"material\"}', '2026-05-23 13:50:02'),
(28, 9, 'item_update', 'items', '4', '{\"item_name\":\"Bond Paper (ream)\",\"type\":\"material\"}', '2026-05-23 13:50:09'),
(29, 9, 'masterlist_save', 'master_list', '2023002', '{\"user_type\":\"student\",\"user_id\":2}', '2026-05-25 14:08:16'),
(30, 1, 'user_login', 'users', '1', NULL, '2026-05-26 15:53:09'),
(31, 1, 'item_update', 'items', '2', '{\"item_name\":\"Office Chair\",\"type\":\"equipment\"}', '2026-05-26 15:53:46'),
(32, 2, 'user_login', 'users', '2', NULL, '2026-05-27 06:37:16'),
(33, 2, 'user_login', 'users', '2', NULL, '2026-05-27 06:37:36'),
(34, 2, 'borrow_request_create', 'borrow_request', '15', '{\"item_id\":9,\"quantity\":1}', '2026-05-27 06:37:48'),
(35, 1, 'user_login', 'users', '1', NULL, '2026-05-27 06:42:47'),
(36, 1, 'borrow_request_reject', 'borrow_request', '15', '{\"remarks\":\"no\"}', '2026-05-27 06:42:58'),
(37, 1, 'user_login', 'users', '1', NULL, '2026-05-27 07:51:28'),
(38, 1, 'item_import_row', 'items', '11', '{\"item_name\":\"Digital Multimeter\",\"dataset\":\"equipment\"}', '2026-05-27 08:03:00'),
(39, 1, 'item_import_row', 'items', '12', '{\"item_name\":\"Microscope\",\"dataset\":\"equipment\"}', '2026-05-27 08:03:00'),
(40, 1, 'csv_import', NULL, 'equipment', '{\"mode\":\"safe\",\"total\":2,\"success\":2,\"failed\":0,\"errors\":[]}', '2026-05-27 08:03:00'),
(41, 1, 'course_create', 'course', 'BSIT', '{\"course_name\":\"BSIT\"}', '2026-05-27 08:03:48'),
(42, 1, 'user_create', 'users', '10', '{\"username\":\"20240001\",\"role\":\"student\"}', '2026-05-27 08:03:48'),
(43, 1, 'masterlist_import_row', 'master_list', '20240001', '{\"user_id\":10}', '2026-05-27 08:03:48'),
(44, 1, 'user_create', 'users', '11', '{\"username\":\"20240002\",\"role\":\"student\"}', '2026-05-27 08:03:48'),
(45, 1, 'masterlist_import_row', 'master_list', '20240002', '{\"user_id\":11}', '2026-05-27 08:03:48'),
(46, 1, 'csv_import', NULL, 'students', '{\"mode\":\"strict\",\"total\":2,\"success\":2,\"failed\":0,\"errors\":[]}', '2026-05-27 08:03:48'),
(47, 1, 'masterlist_import_row', 'master_list', '20240001', '{\"user_id\":10}', '2026-05-27 08:04:13'),
(48, 1, 'masterlist_import_row', 'master_list', '20240002', '{\"user_id\":11}', '2026-05-27 08:04:13'),
(49, 1, 'csv_import', NULL, 'students', '{\"mode\":\"safe\",\"total\":2,\"success\":2,\"failed\":0,\"errors\":[]}', '2026-05-27 08:04:13'),
(50, 1, 'user_role_update', 'users', '7', '{\"old_role\":\"student\",\"new_role\":\"admin\"}', '2026-05-27 08:13:40'),
(51, 1, 'user_role_update', 'users', '4', '{\"old_role\":\"student\",\"new_role\":\"admin\"}', '2026-05-27 08:39:45'),
(52, 1, 'user_role_update', 'users', '5', '{\"old_role\":\"student\",\"new_role\":\"admin\"}', '2026-05-27 08:46:31'),
(53, 1, 'user_role_update', 'users', '7', '{\"old_role\":\"admin\",\"new_role\":\"student\"}', '2026-05-27 09:23:03'),
(54, 1, 'user_role_update', 'users', '7', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-27 09:23:16'),
(55, 1, 'user_role_update', 'users', '10', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-27 10:01:34'),
(56, 1, 'user_create', 'users', '12', '{\"username\":\"12345736232\",\"role\":\"student\"}', '2026-05-27 12:07:36'),
(57, 1, 'masterlist_save', 'master_list', '12345736232', '{\"user_type\":\"student\",\"user_id\":12}', '2026-05-27 12:07:36'),
(58, 1, 'masterlist_save', 'master_list', '2147483647', '{\"user_type\":\"student\",\"user_id\":12}', '2026-05-27 12:07:49'),
(59, 1, 'masterlist_save', 'master_list', '2147483647', '{\"user_type\":\"student\",\"user_id\":12}', '2026-05-27 12:08:04'),
(60, 2, 'user_login', 'users', '2', NULL, '2026-05-27 12:23:20'),
(61, 2, 'borrow_request_create', 'borrow_request', '16', '{\"item_id\":2,\"quantity\":3}', '2026-05-27 12:23:47'),
(62, 1, 'user_login', 'users', '1', NULL, '2026-05-27 12:24:07'),
(63, 1, 'masterlist_save', 'master_list', '2147483647', '{\"user_type\":\"student\",\"user_id\":12}', '2026-05-27 12:27:27'),
(64, 1, 'borrow_request_approve', 'borrow_request', '16', '{\"remarks\":\"\"}', '2026-05-27 12:27:37'),
(65, 1, 'transaction_return', 'transactions', '24', '{\"item_id\":2,\"quantity\":3}', '2026-05-27 12:28:32'),
(66, 1, 'item_delete', 'items', '12', '{\"item_name\":\"Microscope\",\"type\":\"equipment\"}', '2026-05-27 12:28:46'),
(67, 1, 'item_delete', 'items', '2', '{\"item_name\":\"Office Chair\",\"type\":\"equipment\"}', '2026-05-27 12:34:28'),
(68, 1, 'item_create', 'items', '13', '{\"item_name\":\"hehe\",\"type\":\"equipment\"}', '2026-05-27 12:35:08'),
(69, 1, 'user_create', 'users', '13', '{\"username\":\"3434\",\"role\":\"faculty\"}', '2026-05-27 12:36:18'),
(70, 1, 'masterlist_save', 'officials_masterlist', '3434', '{\"user_type\":\"faculty\",\"role\":\"faculty\",\"user_id\":13}', '2026-05-27 12:36:18'),
(71, 1, 'user_role_update', 'users', '13', '{\"old_role\":\"faculty\",\"new_role\":\"admin\"}', '2026-05-27 12:58:25'),
(72, 1, 'user_deactivate', 'users', '12', NULL, '2026-05-27 12:58:53'),
(73, 1, 'user_deactivate', 'users', '12', NULL, '2026-05-27 12:59:02'),
(74, 1, 'user_reactivate', 'users', '12', '{\"username\":\"2147483647\"}', '2026-05-27 13:03:20'),
(75, 1, 'user_deactivate', 'users', '12', NULL, '2026-05-27 13:03:27'),
(76, 1, 'user_reactivate', 'users', '12', '{\"username\":\"2147483647\"}', '2026-05-27 13:03:30'),
(77, 1, 'user_role_update', 'users', '13', '{\"old_role\":\"admin\",\"new_role\":\"student\"}', '2026-05-27 13:03:41'),
(78, 1, 'user_role_update', 'users', '13', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-27 13:03:58'),
(79, 1, 'user_create', 'users', '14', '{\"username\":\"167668\",\"role\":\"student\"}', '2026-05-27 13:05:33'),
(80, 1, 'masterlist_save', 'master_list', '167668', '{\"user_type\":\"student\",\"user_id\":14}', '2026-05-27 13:05:33'),
(81, 1, 'user_role_update', 'users', '14', '{\"old_role\":\"student\",\"new_role\":\"faculty\"}', '2026-05-27 13:06:29'),
(82, 1, 'password_change', 'users', '1', NULL, '2026-05-27 13:07:24'),
(83, 1, 'user_login', 'users', '1', NULL, '2026-05-27 13:07:44'),
(84, 1, 'user_login', 'users', '1', NULL, '2026-05-27 13:08:12'),
(85, 1, 'category_create', 'category', '8', '{\"category_name\":\"industrial\"}', '2026-05-27 13:09:20'),
(86, 1, 'unit_create', 'unit', '5', '{\"unit_name\":\"per laptop\"}', '2026-05-27 13:09:41'),
(87, 1, 'position_save', 'positions', 'MIT', '{\"position_name\":\"sir javs\"}', '2026-05-27 13:09:56'),
(88, 2, 'user_login', 'users', '2', NULL, '2026-05-27 13:10:42'),
(89, 2, 'borrow_request_create', 'borrow_request', '17', '{\"item_id\":13,\"quantity\":10}', '2026-05-27 13:11:33'),
(90, 2, 'borrow_request_create', 'borrow_request', '18', '{\"item_id\":13,\"quantity\":1}', '2026-05-27 13:12:14'),
(91, 1, 'user_login', 'users', '1', NULL, '2026-05-27 13:12:24'),
(92, 2, 'user_login', 'users', '2', NULL, '2026-05-27 13:13:06'),
(93, 2, 'borrow_request_create', 'borrow_request', '19', '{\"item_id\":13,\"quantity\":5}', '2026-05-27 13:13:18'),
(94, 1, 'user_login', 'users', '1', NULL, '2026-05-27 13:13:29'),
(95, 1, 'borrow_request_approve', 'borrow_request', '17', '{\"remarks\":\"\"}', '2026-05-27 13:13:38'),
(96, 1, 'borrow_request_approve', 'borrow_request', '18', '{\"remarks\":\"\"}', '2026-05-27 13:13:52'),
(97, 1, 'borrow_request_reject', 'borrow_request', '19', '{\"remarks\":\"\"}', '2026-05-27 13:14:15'),
(98, 1, 'transaction_return', 'transactions', '25', '{\"item_id\":13,\"quantity\":10}', '2026-05-27 13:14:41'),
(99, 1, 'transaction_return', 'transactions', '26', '{\"item_id\":13,\"quantity\":1}', '2026-05-27 13:14:45'),
(101, 1, 'masterlist_save', 'master_list', '20240002', '{\"user_type\":\"student\",\"user_id\":11}', '2026-05-27 13:43:15'),
(102, 1, 'system_setting_update', 'system_settings', 'system_notes', '{\"length\":4}', '2026-05-27 13:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_request`
--

CREATE TABLE `borrow_request` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL CHECK (`quantity_requested` > 0),
  `request_date` date NOT NULL,
  `days_to_borrow` int(11) NOT NULL CHECK (`days_to_borrow` > 0),
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') DEFAULT 'PENDING',
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_request`
--

INSERT INTO `borrow_request` (`request_id`, `student_id`, `item_id`, `quantity_requested`, `request_date`, `days_to_borrow`, `status`, `remarks`, `created_at`) VALUES
(1, 2023002, 3, 2, '2026-04-20', 5, 'APPROVED', 'Need for class presentation', '2026-04-27 06:58:37'),
(2, 20240041, 1, 1, '2026-04-21', 7, 'APPROVED', 'Approved – laptop for online exam', '2026-04-27 06:58:37'),
(7, 12323, 3, 1, '2026-05-13', 23, 'APPROVED', NULL, '2026-05-13 09:57:44'),
(9, 2023002, 5, 1, '2026-05-17', 3, 'REJECTED', NULL, '2026-05-17 10:30:16'),
(10, 20240041, 4, 12, '2026-05-17', 12, 'APPROVED', NULL, '2026-05-17 13:17:11'),
(11, 20240041, 1, 1, '2026-05-17', 23, 'APPROVED', NULL, '2026-05-17 13:23:38'),
(12, 20240041, 1, 1, '2026-05-17', 21, 'APPROVED', NULL, '2026-05-17 13:26:28'),
(13, 20240041, 1, 1, '2026-05-23', 4, 'REJECTED', '', '2026-05-23 09:15:32'),
(14, 20240042, 1, 1, '2026-05-23', 12, 'APPROVED', '', '2026-05-23 09:28:20'),
(15, 2023002, 9, 1, '2026-05-27', 1, 'REJECTED', 'no', '2026-05-27 06:37:48'),
(17, 2023002, 13, 10, '2026-05-27', 3, 'APPROVED', '', '2026-05-27 13:11:33'),
(18, 2023002, 13, 1, '2026-05-27', 1, 'APPROVED', '', '2026-05-27 13:12:14'),
(19, 2023002, 13, 5, '2026-05-27', 1, 'REJECTED', '', '2026-05-27 13:13:18');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Electronics'),
(2, 'Furniture'),
(8, 'industrial'),
(4, 'Laboratory'),
(3, 'Office Supplies');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_code`, `course_name`) VALUES
('BSCS', 'COMPUTER SCIENCE'),
('BSIT', 'BSIT'),
('CRIM', 'CRIMINOLOGY');

-- --------------------------------------------------------

--
-- Table structure for table `import_errors`
--

CREATE TABLE `import_errors` (
  `error_id` int(11) NOT NULL,
  `import_type` varchar(50) NOT NULL,
  `row_number` int(11) NOT NULL,
  `reason` varchar(500) NOT NULL,
  `raw_data` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `import_logs`
--

CREATE TABLE `import_logs` (
  `import_id` int(11) NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `import_type` varchar(50) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `total_rows` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `error_summary` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity_alert` int(11) DEFAULT 5,
  `received_by_official_id` varchar(50) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `condition` varchar(100) DEFAULT 'Good',
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `stock_status` enum('out_of_stock','low_stock','available') NOT NULL DEFAULT 'available',
  `inventory_status` enum('available','low_stock','out_of_stock') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_name`, `description`, `unit_id`, `category_id`, `total_quantity`, `available_quantity`, `min_quantity_alert`, `received_by_official_id`, `date_added`, `status`, `created_at`, `updated_at`, `condition`, `unit_price`, `stock_status`, `inventory_status`) VALUES
(1, 'Laptop', 'Dell Latitude 5520', 1, 1, 10, 10, 2, 'OFF001', '2026-01-10', 'active', '2026-04-27 06:58:36', '2026-05-23 12:33:36', 'Good', 0.00, 'available', 'available'),
(3, 'Microphone', 'USB condenser mic', 1, 1, 8, 8, 2, 'OFF002', '2026-02-01', 'inactive', '2026-04-27 06:58:36', '2026-04-30 09:10:53', 'Good', 0.00, 'available', 'available'),
(4, 'Bond Paper (ream)', 'A4 size, 500 sheets\nType: Material\nUnit price: PHP 12', 3, 3, 50, 38, 10, 'OFF003', '2026-03-05', 'active', '2026-04-27 06:58:36', '2026-05-23 13:50:09', 'Good', 0.00, 'available', 'available'),
(5, 'Lab Goggles', 'Chemical splash safety goggles', 1, 4, 20, 20, 5, 'OFF002', '2026-01-20', 'active', '2026-04-27 06:58:36', '2026-04-27 06:58:36', 'Good', 0.00, 'available', 'available'),
(9, 'glass', 'nice\nType: Equipment\nItem code: 1\nCondition: Good', 2, 2, 12, 12, 5, NULL, '2026-05-23', 'active', '2026-05-23 12:34:36', '2026-05-23 12:34:36', 'Good', 0.00, 'available', 'available'),
(10, 'tae', 'dd\nType: Material\nUnit price: PHP 500', 3, 3, 12, 12, 5, NULL, '2026-05-23', 'active', '2026-05-23 13:49:25', '2026-05-23 13:50:02', 'Good', 0.00, 'available', 'available'),
(11, 'Digital Multimeter', 'Handheld meter for lab use\nType: Equipment\nItem code: EQ-1001\nCondition: Good', 1, 1, 12, 12, 5, NULL, '2026-05-27', 'active', '2026-05-27 08:03:00', '2026-05-27 08:03:00', 'Good', 0.00, 'available', 'available'),
(13, 'hehe', 'noice\nType: Equipment\nItem code: tae\nCondition: Good', 1, 2, 12, 12, 5, NULL, '2026-05-27', 'active', '2026-05-27 12:35:08', '2026-05-27 13:14:45', 'Good', 0.00, 'available', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `master_list`
--

CREATE TABLE `master_list` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_list`
--

INSERT INTO `master_list` (`student_id`, `first_name`, `last_name`, `course_code`, `year_level`, `created_at`, `email`, `contact_no`, `status`) VALUES
(12323, 'Lhorence', 'Abejo', 'BSCS', '2002', '2026-04-30 09:44:50', NULL, NULL, 'Active'),
(167668, 'francois', 'luardo', 'CRIM', 'Second', '2026-05-27 13:05:33', NULL, NULL, 'Active'),
(2023001, 'Juan', 'Dela Cruz', NULL, '2023', '2026-04-27 06:58:36', NULL, NULL, 'Active'),
(2023002, 'Maria', 'Santos', 'BSCS', '2023', '2026-04-27 06:58:36', NULL, NULL, 'Active'),
(2023003, 'Jose', 'Reyes', NULL, '2023', '2026-04-27 06:58:36', NULL, NULL, 'Active'),
(20240001, 'Ana', 'Santos', 'BSIT', '2024', '2026-05-27 08:03:48', NULL, NULL, 'Active'),
(20240002, 'reyes', 'mark', 'BSCS', '2023', '2026-05-27 08:03:48', NULL, NULL, 'Active'),
(20240041, 'Luis', 'Torres', NULL, '2024', '2026-04-27 06:58:36', NULL, NULL, 'Active'),
(20240042, 'Andrea', 'Villanueva', NULL, '2024', '2026-04-27 06:58:36', NULL, NULL, 'Active'),
(1212324324, 'Lhorence', 'Abejo', 'BSCS', '2003', '2026-04-28 08:10:35', NULL, NULL, 'Active'),
(2147483647, 'jovit', 'palma', 'CRIM', 'Second', '2026-05-27 12:07:36', NULL, NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `officials_masterlist`
--

CREATE TABLE `officials_masterlist` (
  `official_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position_code` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials_masterlist`
--

INSERT INTO `officials_masterlist` (`official_id`, `first_name`, `last_name`, `position_code`, `is_active`, `created_at`) VALUES
('12345', 'tae', 'User', 'PROF', 1, '2026-05-23 12:22:04'),
('12434234', 'tubol', 'tae', 'PROF', 1, '2026-04-28 08:47:02'),
('235346457', 'tubol', 'tae', 'prof', 1, '2026-04-30 09:53:00'),
('3434', 'romel', 'emp', 'PROF', 1, '2026-05-27 12:36:18'),
('OFF001', 'Roberto', 'Gonzales', NULL, 1, '2026-04-27 06:58:36'),
('OFF002', 'Elena', 'Morales', NULL, 1, '2026-04-27 06:58:36'),
('OFF003', 'Antonio', 'Velasco', NULL, 1, '2026-04-27 06:58:36');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_code` varchar(20) NOT NULL,
  `position_name` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_code`, `position_name`) VALUES
('MIT', 'sir javs'),
('PROF', 'PROFESSOR');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_by`, `updated_at`) VALUES
('system_notes', 'hala', 1, '2026-05-27 13:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_borrowed` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('PENDING','ONGOING','RETURNED') DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `request_id`, `student_id`, `item_id`, `quantity_borrowed`, `borrow_date`, `expected_return_date`, `created_at`, `updated_at`, `status`) VALUES
(4, 7, 12323, 3, 0, '2026-05-13', '2026-06-05', '2026-05-13 09:57:44', '2026-05-17 08:40:16', 'RETURNED'),
(5, 1, 2023002, 3, 0, '2026-05-14', '2026-05-19', '2026-05-14 03:17:31', '2026-05-17 08:30:44', 'RETURNED'),
(8, 9, 2023002, 5, 0, '2026-05-17', '2026-05-20', '2026-05-17 10:30:16', '2026-05-23 09:48:31', 'RETURNED'),
(9, 10, 20240041, 4, 0, '2026-05-17', '2026-05-29', '2026-05-17 13:17:11', '2026-05-17 13:17:43', 'RETURNED'),
(10, 10, 20240041, 4, 0, '2026-05-17', '2026-05-29', '2026-05-17 13:17:21', '2026-05-17 13:17:37', 'RETURNED'),
(11, 11, 20240041, 1, 0, '2026-05-17', '2026-06-09', '2026-05-17 13:23:38', '2026-05-17 13:27:17', 'RETURNED'),
(12, 11, 20240041, 1, 1, '2026-05-17', '2026-06-09', '2026-05-17 13:23:45', '2026-05-23 09:48:27', 'RETURNED'),
(13, 12, 20240041, 1, 1, '2026-05-17', '2026-06-07', '2026-05-17 13:27:03', '2026-05-23 06:12:50', 'RETURNED'),
(23, 14, 20240042, 1, 1, '2026-05-23', '2026-06-04', '2026-05-23 09:47:56', '2026-05-23 12:33:36', 'RETURNED'),
(25, 17, 2023002, 13, 10, '2026-05-27', '2026-05-30', '2026-05-27 13:13:38', '2026-05-27 13:14:41', 'RETURNED'),
(26, 18, 2023002, 13, 1, '2026-05-27', '2026-05-28', '2026-05-27 13:13:52', '2026-05-27 13:14:45', 'RETURNED');

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`unit_id`, `unit_name`) VALUES
(3, 'box/boxes'),
(2, 'kg'),
(1, 'pcs'),
(5, 'per laptop');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `official_id` varchar(50) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin','faculty') DEFAULT 'student',
  `is_default_password` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `student_id`, `official_id`, `username`, `password`, `role`, `is_default_password`, `is_active`, `last_login`, `created_at`) VALUES
(1, 2023001, NULL, '2023001', '$2y$10$lZbjp/t8K.3tm0a2GHppxO9kClOBgfvAcpeZLQSRBCZ.qswayoola', 'admin', 0, 1, '2026-05-27 13:13:29', '2026-04-27 06:58:36'),
(2, 2023002, NULL, '2023002', '$2y$10$vcAAtPguYOubJwTOeEWgIufwz1d331No.sU2afPT6uByuOcl179Ua', 'student', 0, 1, '2026-05-27 13:13:06', '2026-04-27 06:58:36'),
(3, 2023003, NULL, '2023003', 'jose123', 'student', 1, 1, NULL, '2026-04-27 06:58:36'),
(4, 20240041, NULL, '20240041', '$2y$10$0btWNvnIN9k69Chh.ApNFeYYy.A22MA0Fz4OhIyay72FIldTcJK1G', 'admin', 0, 1, '2026-05-23 09:11:15', '2026-04-27 06:58:36'),
(5, 20240042, NULL, '20240042', '$2y$10$JteBXDj6j.XsPiUSK88TFODo0IgPFAeRLpQsdm5tY5iqLQbvYmRM6', 'admin', 0, 1, '2026-05-23 09:27:51', '2026-04-27 06:58:36'),
(7, 12323, NULL, '12323', '$2y$10$mg7jP/jkoY7GZaretlXoRekVbK/tEpOoBVwz1WQKAbGeS4NOC3f8.', 'faculty', 1, 1, NULL, '2026-05-21 16:19:17'),
(8, 1212324324, NULL, '1212324324', '$2y$10$wIjnc6X/4vhocTL9vETHkeUIAO0o0U6f9N0ESOLWGXYHfi/VfN0Fe', 'faculty', 1, 1, NULL, '2026-05-21 16:19:17'),
(9, NULL, '12345', '12345', '$2y$10$Dr4SJYNMfkRWOYpl616E3uL.sHO/sO8ZgEJiD03JwYA4IAGX6x8vq', 'faculty', 0, 1, '2026-05-23 12:32:52', '2026-05-23 12:22:04'),
(10, 20240001, NULL, '20240001', '$2y$10$S6hv11waod9eQVnNp7jSm.MIhMERSddd5qF.CknewnGcLxp4dggsa', 'faculty', 1, 1, NULL, '2026-05-27 08:03:48'),
(11, 20240002, NULL, '20240002', '$2y$10$gz2cIBP6zdqMBMkaFBJnpOA7uBTMjHv/4PYQD.MDadUxdavkAKZFK', 'student', 1, 1, NULL, '2026-05-27 08:03:48'),
(12, 2147483647, NULL, '2147483647', '$2y$10$mnvmsht6YY6u2BMRpPir9OyYKFpigDrCv5F1CGoDv/OqdmwgBnTXO', 'student', 1, 1, NULL, '2026-05-27 12:07:36'),
(13, NULL, '3434', '3434', '$2y$10$qOst8bg5XJoRDPTF5c2Dz.vQrhubomRWkiOKNEhoMqU7oksNwdhMG', 'faculty', 1, 1, NULL, '2026-05-27 12:36:18'),
(14, 167668, NULL, '167668', '$2y$10$xYFMDVTBZdctYGJrIR0w9OBUYcroYiAx2x9MFa9S.dEHhM1Lfug/K', 'faculty', 1, 1, NULL, '2026-05-27 13:05:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_audit_actor` (`actor_user_id`),
  ADD KEY `idx_audit_action` (`action_type`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `borrow_request`
--
ALTER TABLE `borrow_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_item` (`item_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_request_date` (`request_date`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_code`);

--
-- Indexes for table `import_errors`
--
ALTER TABLE `import_errors`
  ADD PRIMARY KEY (`error_id`),
  ADD KEY `idx_import_errors_type` (`import_type`);

--
-- Indexes for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`import_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_items_receiver` (`received_by_official_id`);

--
-- Indexes for table `master_list`
--
ALTER TABLE `master_list`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `fk_course` (`course_code`);

--
-- Indexes for table `officials_masterlist`
--
ALTER TABLE `officials_masterlist`
  ADD PRIMARY KEY (`official_id`),
  ADD KEY `fk_masterlist` (`position_code`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_code`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`),
  ADD KEY `idx_system_settings_updated_by` (`updated_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_item` (`item_id`),
  ADD KEY `idx_expected_return` (`expected_return_date`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_student_id` (`student_id`),
  ADD UNIQUE KEY `idx_official_id` (`official_id`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `borrow_request`
--
ALTER TABLE `borrow_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `import_errors`
--
ALTER TABLE `import_errors`
  MODIFY `error_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `import_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_request`
--
ALTER TABLE `borrow_request`
  ADD CONSTRAINT `fk_request_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_student` FOREIGN KEY (`student_id`) REFERENCES `master_list` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_receiver` FOREIGN KEY (`received_by_official_id`) REFERENCES `officials_masterlist` (`official_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_unit` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`unit_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `master_list`
--
ALTER TABLE `master_list`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_code`) REFERENCES `course` (`course_code`);

--
-- Constraints for table `officials_masterlist`
--
ALTER TABLE `officials_masterlist`
  ADD CONSTRAINT `fk_masterlist` FOREIGN KEY (`position_code`) REFERENCES `positions` (`position_code`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_request` FOREIGN KEY (`request_id`) REFERENCES `borrow_request` (`request_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_student` FOREIGN KEY (`student_id`) REFERENCES `master_list` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_student` FOREIGN KEY (`student_id`) REFERENCES `master_list` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

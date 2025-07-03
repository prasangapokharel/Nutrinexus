-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 06:24 PM
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
-- Database: `nutrinexas`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(2, 4, 'Marsden Franklin', '+1 (412) 114-2873', '46 Fabien Avenue', 'Ut laborum Quia duc', 'Minus nostrum ullam', 'Ut qui nulla officia', 'Velit velit sed qui', 'Reprehenderit archit', 1, '2025-05-04 08:11:36', '2025-05-04 08:11:36'),
(3, 5, 'erwerewr', 'rewrewr', 'rewrewr', 'rewrwer', 'ewrwer', 'rwerewr', 'werwer', 'Neyy', 1, '2025-05-09 16:15:45', '2025-05-09 16:15:45'),
(17, 3, 'Prasanga Raman Pokharel', '9705470926', 'Inaruwa-1, SUnsari', 'Near khola', 'Inaruwa', '1', '51600', 'Nepal', 1, '2025-06-23 10:05:12', '2025-06-23 10:05:12'),
(18, 7, 'Stone Hutchinson', '+1 (948) 181-1034', '95 East New Court', NULL, 'Aut quae qui quia oc', 'Asperiores id vero', '', 'Eu dolorum exercitat', 1, '2025-07-02 12:23:42', '2025-07-02 12:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit_per_user` int(11) DEFAULT NULL,
  `usage_limit_global` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `applicable_products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_products`)),
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit_per_user`, `usage_limit_global`, `used_count`, `applicable_products`, `is_active`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'SAVE!)', NULL, 'fixed', 100.00, 1000.00, NULL, 2, 1, 0, '[39]', 1, '2025-06-30 23:02:00', '2025-06-27 13:56:16', '2025-06-27 13:56:16'),
(2, 'HARI10', NULL, 'percentage', 5.00, 1000.00, NULL, 10, NULL, 0, '[39]', 1, '2025-07-03 22:02:00', '2025-06-27 14:00:38', '2025-06-27 14:00:38'),
(3, 'NX2025', NULL, 'fixed', 200.00, 1000.00, NULL, 12, 1, 0, '[39]', 1, '2025-06-30 11:01:00', '2025-06-27 15:55:46', '2025-06-27 15:55:46'),
(4, 'KAPIL10', NULL, 'fixed', 100.00, 1000.00, NULL, 10, 10, 0, '[39]', 1, '2025-07-31 22:53:00', '2025-06-30 17:08:47', '2025-06-30 17:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_usage`
--

INSERT INTO `coupon_usage` (`id`, `coupon_id`, `user_id`, `order_id`, `discount_amount`, `used_at`) VALUES
(1, 2, 3, 66, 0.00, '2025-06-27 15:07:06'),
(2, 2, 3, 67, 0.00, '2025-06-27 15:11:50'),
(3, 3, 3, 68, 0.00, '2025-06-27 16:05:44');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_charges`
--

CREATE TABLE `delivery_charges` (
  `id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `charge` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_charges`
--

INSERT INTO `delivery_charges` (`id`, `location_name`, `charge`) VALUES
(1, 'Kathmandu', 150.00),
(2, 'Free', 0.00),
(3, 'Butwal', 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body` longtext NOT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT 5,
  `status` enum('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `max_attempts` tinyint(4) NOT NULL DEFAULT 3,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `last_attempt` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `esewa_payments`
--

CREATE TABLE `esewa_payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `khalti_payments`
--

CREATE TABLE `khalti_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pidx` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(2, 3, 'New Referral Commission', 'You earned ₹619.50 commission from a referral purchase.', 'referral_earning', 1, 1, '2025-05-07 14:39:07'),
(3, 3, 'New Referral Commission', 'You earned ₹743.40 commission from a referral purchase.', 'referral_earning', 2, 1, '2025-05-08 05:40:51'),
(4, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹362.00 has been submitted and is being processed.', 'withdrawal_request', 2, 1, '2025-05-08 06:56:21'),
(5, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹1,000.00 has been submitted and is being processed.', 'withdrawal_request', 3, 1, '2025-05-09 04:29:08'),
(6, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹445.00 has been submitted and is being processed.', 'withdrawal_request', 4, 0, '2025-07-02 12:30:46'),
(7, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹120.00 has been submitted and is being processed.', 'withdrawal_request', 5, 0, '2025-07-02 13:24:46'),
(8, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹220.00 has been submitted and is being processed.', 'withdrawal_request', 6, 0, '2025-07-02 13:29:06'),
(9, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 7, 0, '2025-07-02 13:31:26'),
(10, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 8, 0, '2025-07-02 13:52:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','unpaid','paid') NOT NULL DEFAULT 'pending',
  `address` text NOT NULL,
  `order_notes` text DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice`, `user_id`, `customer_name`, `contact_no`, `payment_method_id`, `status`, `address`, `order_notes`, `transaction_id`, `total_amount`, `delivery_fee`, `payment_screenshot`, `created_at`, `updated_at`) VALUES
(19, 'NN202505083038', 3, 'rwerewrew', '3244323432', 1, 'paid', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 17346.00, 0.00, NULL, '2025-05-08 05:00:16', '2025-05-08 05:00:16'),
(27, 'NN202505082554', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'paid', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', NULL, NULL, 14868.00, 0.00, NULL, '2025-05-08 05:49:55', '2025-05-08 05:40:51'),
(28, 'NN202505082021', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'processing', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-08 05:56:36', '2025-05-08 05:56:36'),
(29, 'NN202505083406', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 24780.00, 0.00, NULL, '2025-05-08 06:25:47', '2025-05-08 06:25:47'),
(30, 'NN202505088831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-08 06:26:31', '2025-05-08 06:26:31'),
(31, 'NN202505089371', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-08 06:29:27', '2025-05-08 06:29:27'),
(32, 'NN202505087737', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 42126.00, 0.00, NULL, '2025-05-08 06:55:16', '2025-05-08 06:55:16'),
(33, 'NN202505084831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 17346.00, 0.00, NULL, '2025-05-08 07:17:47', '2025-05-08 07:17:47'),
(34, 'NN202505091470', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-09 04:28:06', '2025-05-09 04:28:06'),
(35, 'NN202505099910', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:28:22', '2025-05-09 05:28:22'),
(36, 'NN202505091269', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 19824.00, 0.00, NULL, '2025-05-09 05:52:44', '2025-05-09 05:52:44'),
(37, 'NN202505098051', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 4956.00, 0.00, NULL, '2025-05-09 05:55:22', '2025-05-09 05:55:22'),
(38, 'NN202505093826', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:57:15', '2025-05-09 05:57:15'),
(39, 'NN202505097713', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:59:46', '2025-05-09 05:59:46'),
(40, 'NN202505097597', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-09 15:17:09', '2025-05-09 15:17:09'),
(41, 'NN202505094713', 5, 'erwerewr', 'rewrewr', 1, 'paid', 'rewrewr, ewrwer, rwerewr, Neyy', NULL, NULL, 13500.14, 0.00, NULL, '2025-05-09 16:16:19', '2025-07-01 04:00:06'),
(42, 'NN202506233246', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 13367.04, 0.00, NULL, '2025-06-23 05:47:25', '2025-06-23 05:47:25'),
(43, 'NN202506235573', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 1462.26, 0.00, NULL, '2025-06-23 05:49:19', '2025-06-23 05:49:19'),
(44, 'NN202506232914', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 1462.26, 0.00, NULL, '2025-06-23 05:50:51', '2025-06-23 05:50:51'),
(45, 'NN202506233562', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2924.51, 0.00, NULL, '2025-06-23 06:02:42', '2025-06-23 06:02:42'),
(46, 'NN202506233641', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2924.51, 0.00, NULL, '2025-06-23 06:39:56', '2025-06-23 06:39:56'),
(47, 'NN202506236885', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2511.04, 0.00, NULL, '2025-06-23 06:48:05', '2025-06-23 06:48:05'),
(62, 'NN202506231332', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '', 2511.04, 0.00, '', '2025-06-23 10:05:12', '2025-06-23 10:05:12'),
(63, 'NN202506236670', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'paid', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', 'test', '', 1110.14, 0.00, '', '2025-06-23 10:08:03', '2025-07-01 04:04:52'),
(64, 'NN202506235259', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'pending', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '434234342', 1110.14, 0.00, 'payment_1750674338_4594.png', '2025-06-23 10:10:38', '2025-06-23 10:10:38'),
(65, 'NN202506269428', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'shipped', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', 'shop', '', 1110.14, 0.00, '', '2025-06-26 13:49:51', '2025-07-01 04:11:36'),
(66, 'NN202506271176', 3, 'Prasanga Pokharel', '9765470926', 1, 'cancelled', 'Inaruwa, 1, Inaruwa, 1, Nepal', '', '', 1235.00, 0.00, NULL, '2025-06-27 14:52:06', '2025-07-01 05:01:57'),
(67, 'NN202506277198', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'cancelled', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '', 1235.00, 0.00, NULL, '2025-06-27 14:56:50', '2025-07-01 05:01:53'),
(68, 'NN202506273345', 3, 'Prasanga Pokharel', '9765470926', 1, 'paid', 'the , the , the , 1, Nepal', '', '', 1100.00, 0.00, NULL, '2025-06-27 15:50:44', '2025-07-01 04:11:28'),
(69, 'NTX202507023317', 3, 'Lila Glass', '+1 (547) 137-1217', 1, 'pending', '389 Clarendon Street, Nobis molestias dolo, Laboriosam doloribu, Rerum quos dolores r Ipsum nemo sint nul', 'Commodi sed dolor do', 'Totam id voluptatum ', 2600.00, 0.00, '', '2025-07-02 06:36:37', '2025-07-02 06:36:37'),
(70, 'NTX202507022411', 3, 'Mary Cruz', '+1 (596) 964-3661', 1, 'pending', '348 West White First Avenue, Officia tempora cupi, Omnis culpa at exerc, Corrupti et ut erro Tempore sint a eaqu', 'Eum similique ea et ', '', 1300.00, 0.00, '', '2025-07-02 06:38:50', '2025-07-02 06:38:50'),
(71, 'NTX202507026550', 3, 'rwerewrew', '3244323432', 1, 'pending', 'rssfasfs, fafssaf, fasff ', '', '', 1200.00, 0.00, '', '2025-07-02 10:05:08', '2025-07-02 10:05:08'),
(72, 'NTX202507029514', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '22222', 1300.00, 0.00, '1751450902_bank.png', '2025-07-02 10:08:22', '2025-07-02 11:22:02'),
(73, 'NTX202507026372', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 1300.00, 0.00, '', '2025-07-02 10:41:43', '2025-07-02 11:21:59'),
(74, 'NTX202507027866', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 8900.00, 0.00, '', '2025-07-02 12:23:52', '2025-07-02 12:24:57'),
(75, 'NTX202507024731', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 8900.00, 0.00, '', '2025-07-02 12:30:59', '2025-07-02 12:31:18'),
(76, 'NTX202507026017', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 1200.00, 0.00, '', '2025-07-02 13:37:11', '2025-07-02 13:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `invoice` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `total`, `invoice`) VALUES
(33, 27, 30, 1, 2100.00, 2100.00, 'NN202505082554'),
(35, 28, 30, 1, 2100.00, 2100.00, 'NN202505082021'),
(40, 32, 30, 2, 2100.00, 4200.00, 'NN202505087737'),
(41, 33, 30, 2, 2100.00, 4200.00, 'NN202505084831'),
(44, 35, 30, 1, 2100.00, 2100.00, 'NN202505099910'),
(46, 36, 30, 3, 2100.00, 6300.00, 'NN202505091269'),
(47, 37, 30, 2, 2100.00, 4200.00, 'NN202505098051'),
(48, 38, 30, 1, 2100.00, 2100.00, 'NN202505093826'),
(49, 39, 30, 1, 2100.00, 2100.00, 'NN202505097713'),
(53, 42, 35, 1, 2128.00, 2128.00, 'NN202506233246'),
(55, 43, 36, 1, 1239.20, 1239.20, 'NN202506235573'),
(56, 44, 36, 1, 1239.20, 1239.20, 'NN202506232914'),
(57, 45, 36, 2, 1239.20, 2478.40, 'NN202506233562'),
(58, 46, 36, 2, 1239.20, 2478.40, 'NN202506233641'),
(59, 47, 35, 1, 2128.00, 2128.00, 'NN202506236885'),
(81, 62, 35, 1, 2128.00, 2128.00, 'NN202506231332'),
(85, 66, 39, 1, 1300.00, 1300.00, 'NN202506271176'),
(86, 67, 39, 1, 1300.00, 1300.00, 'NN202506277198'),
(87, 68, 39, 1, 1300.00, 1300.00, 'NN202506273345'),
(88, 69, 39, 2, 1300.00, 2600.00, 'NTX202507023317'),
(89, 70, 39, 1, 1300.00, 1300.00, 'NTX202507022411'),
(90, 71, 39, 1, 1300.00, 1300.00, 'NTX202507026550'),
(91, 72, 39, 1, 1300.00, 1300.00, 'NTX202507029514'),
(92, 73, 39, 1, 1300.00, 1300.00, 'NTX202507026372'),
(93, 74, 40, 1, 8900.00, 8900.00, 'NTX202507027866'),
(94, 75, 40, 1, 8900.00, 8900.00, 'NTX202507024731'),
(95, 76, 39, 1, 1300.00, 1300.00, 'NTX202507026017');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`, `active`) VALUES
(1, 'Cash on Delivery', 'Pay when you receive your order', 1, '2025-04-21 01:59:59', '2025-04-21 01:59:59', 1),
(2, 'Khalti', 'Pay using Khalti digital wallet', 1, '2025-04-21 01:59:59', '2025-04-21 01:59:59', 1),
(3, 'eSewa', 'Pay using eSewa digital wallet', 1, '2025-04-21 01:59:59', '2025-04-21 01:59:59', 1),
(4, 'Bank Transfer', 'Pay via bank transfer', 1, '2025-04-21 01:59:59', '2025-04-21 01:59:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `serving` varchar(50) DEFAULT NULL,
  `capsule` tinyint(1) NOT NULL DEFAULT 0,
  `flavor` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sales_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `slug`, `description`, `price`, `sale_price`, `stock_quantity`, `category`, `weight`, `serving`, `capsule`, `flavor`, `image`, `sales_count`, `is_featured`, `created_at`, `updated_at`) VALUES
(30, 'Hk Vital Collagen', 'hk-vital-collagen', 'Clinically proven to reduce fine lines and wrinkles by 48% in 8 weeks* \r\n    4X smoother skin in 8 weeks*\r\n    87% users experienced glowing skin in 4 weeks**', 2116.80, NULL, 248, 'Protein', '', '', 0, '', 'https://img4.hkrtcdn.com/38845/prd_3884453-HK-Vitals-Skin-Radiance-Collagen-Marine-Collagen-200-g-Orange_o.jpg', 0, 0, '2025-05-07 13:35:00', '2025-07-02 11:15:12'),
(34, 'ASITIS ATOM Isolate Whey Protein 1kg Chocolate', 'asitis-atom-isolate-whey-protein-1kg-chocolate-68654125897e6', 'Isolate whey protein with 30g protein, 6.1g BCAA, and 13g EAA per serving. Chocolate flavor, supports muscle growth.', 4160.00, NULL, 0, 'Protein', '', '', 0, '', 'https://laz-img-sg.alicdn.com/p/645ba7e8c19b0091d075c5f45c2dce27.jpg', 15, 0, '2025-05-09 16:10:46', '2025-07-02 14:09:37'),
(35, 'Wellcore Creatine Monohydrate 307g Fruit Fusion', 'wellcore-creatine-monohydrate-307g-fruit-fusion', 'Lab-tested creatine monohydrate to support athletic performance and power. Fruit fusion flavor.', 2128.00, NULL, 97, 'Creatine', '', '', 0, '', 'https://img.drz.lazcdn.com/g/kf/S3b1324339578494d852861d350077c9eg.jpg_720x720q80.jpg', 20, 0, '2025-05-09 16:10:46', '2025-07-02 11:12:27'),
(36, 'Wellcore Electrolytes Miami Thunder 200g', 'wellcore-electrolytes-miami-thunder-200g-6865411c45983', 'Sugar-free electrolyte drink powder with 5 vital electrolytes (Na, Mg, Ca, K, PO4). Fat-fuel-powered, keto-friendly, Miami Thunder flavor.', 1239.20, NULL, 96, 'Electrolytes', '', '', 0, '', 'https://img.drz.lazcdn.com/static/np/p/33ceb9a8bc153dff89726a0bd0436a9c.jpg_720x720q80.jpg', 0, 0, '2025-05-09 16:10:46', '2025-07-02 14:09:28'),
(39, 'Myfitness Chocolate Peanut Butter Crunchy  | 23g protein per 100g', 'myfitness-chocolate-peanut-butter-crunchy-23g-protein-per-100g-6863742b3a5eb', 'Brand : MYFITNESS\r\n\r\nProduct dimension : 1250g:Pack Of 1: 15 cm x 13 cm x 14 cm\r\n\r\nFlavour : Chocolate crunchy\r\n\r\nNet weight :1250gm\r\n\r\nGross weight : 1380gm\r\n\r\nNut seed tupe : Peanut\r\n\r\n    High Protein , 95℅ less oil seperation\r\n\r\nMost of us can’t say no to peanut butter. Even more of us can’t say no to chocolate. Put the two together in a single jar, and you get an irresistible creamy treat! That’s exactly what we offer in the chocolatey variant of our famous Original Peanut Butter.\r\n\r\nAND... it’s a chocolate peanut butter spread dedicated to helping you get fit!', 1300.00, 1100.00, 7, 'Peanut', '1.25kg', '30g', 0, 'Chocolate', NULL, 0, 1, '2025-06-26 15:16:31', '2025-07-01 05:22:47'),
(40, 'Cellucor C4 The Original Explosive Pre-Workout, 360 g (0.80 lb)', 'cellucor-c4-the-original-explosive-pre-workout-360-g-0-80-lb', 'Global C4 Pre-Workout Protein Powder in India is the Global No.1 Brand known to build high energy levels for the next workout sessions to perform exploits. It is India’s one of the most explosive and rich supplement powders that leads to wonderful bodybuilding sessions and gives excellent results for exercise and training levels. It is also used for powerful, healthy physique building and performance. You can now easily depend upon Cellucor C4 Pre-Workout Original Protein Powder.\r\n\r\nThree Things you can Expect From Cellucor4 Supplement Powder and that is -\r\n\r\n1. Rich and High Amount of Energy\r\n2. Muscle Building Support\r\n3. Incredible Work Outs\r\n\r\nImportant Advantages and Benefits of Cellucor 4 Original\r\n\r\nIncreases Muscle and Body Growth in the Body\r\n\r\nCellucor C4 Pre –Workout contains Carnosine, a healthy amount of regulated acid that builds up muscles, it helps to build muscle by strengthening the body fully. Therefore the supplement of Beta-alanine supplements boosts the production of carnosine and gives excellent performance to the body for excellent physical activity.\r\n\r\nGives Excellent Immunity and Stamina for More Physical Exercise\r\n\r\nThe good amount of Creatine Nitrate present in the mix protein powder blend benefits you for high performance with excellent stamina and immunity counts. It removes all fatigue from the body and brings a great number of energy levels for much exercising and relaxation before working out. To keep your muscle growth enduring, you need to eat well and take Cellucor C4 on time before working out. This will surely help you keep weakness away from you!\r\n\r\nRich Amount of Calories\r\n\r\nCalories help you keep your energy level high. Without calories, nobody can survive or have a healthy lifestyle. Without energy levels, your cells in the body will be weak and die easily because of a lack of calories to maintain excellent function and growth of your cells, tissues, organs, and even over body physique. Cellucor C4 has a complete amount of calories which you require for your body.\r\nGood Amount of Carbohydrates\r\n\r\nCarbohydrates are the most important source for your body; whatever you eat or drink that has a healthy amount of food for you, it helps you be full, adds on the best calories, and supports you with high energy levels. And the c4 supplement has all the required amount of carbohydrates for your body that helps you for excessive workouts and builds a unique physique that you have planned for your body weight gains.\r\n\r\nImportant to Note*\r\n\r\nCarbohydrates have enough energy and sources which fuels up your mind, kidneys, heart muscles, and all the nervous system in the body. It keeps your lungs strong for healthy oxygen flow. Cellucor C4 available near you is easy to order and purchase with free delivery online in Mumbai, India. Don’t miss this product for healthiness for your pre-workout.\r\n\r\nVitamin C (Ascorbic Acid)\r\n\r\nThe Cellucor C4 has a good amount of Vitamin C that prevents people from having bad diet structures in the body. Now, if you ask what happens when there is less Vitamin C, it brings weakness. Inflammation of the gums can also cause bad purple marks on body parts like skin, wounds, and the corkscrew hairs. Also, less Vitamin C leads to poor wound healing. So knowing that Vitamin C is loaded in this product, Cellucor c4 is wellness for all the person who is into exercising, etc.\r\n\r\nOther beneficial sources for a healthy body are Niacin, Vitamin B, Folic Acid, Vitamin B 12, and Calcium.\r\n\r\nThe other Three Essential and Most Explosive Energy Sources are –\r\n\r\n· Beta-Alanine: Decreases and Removes Fatigue From the Body.\r\n· Creatinine Nitrate: Overall maintains and gives more strength for excellent performance.\r\n· Arginine AKG: Controls the hormonal balances and lowers the body\'s inflammation and more.\r\nPeople Also Ask Us Questions about CELLUCOR C4 Pre-Workout Original, 0.80 lbs, 360 g, 60 Servings\r\n\r\nQ1. Why is CELLUCOR C4 different From Other Protein Powder?\r\n\r\nCellucor C4 is a very powerful protein powder that contains an excellent and high amount of energy sources for excessive workout sessions. It has the best sources like Vitamins C, Vitamin B 6 and Vitamin B 12 and many more. It also has the three main essential sources like Beta Alanine, Creatine Nitrate and Arginine AKG.\r\n\r\nQ2. Is Cellucor C4 Healthy?\r\n\r\nCellucor C4 has the highest amount of caffeine that is up to 200mg. As long as you drin', 8900.00, 5900.00, 20, 'Pre-Workout', '360 g', '60serving', 0, 'watermelon', NULL, 0, 1, '2025-07-02 11:20:27', '2025-07-02 11:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `sort_order`, `created_at`, `updated_at`) VALUES
(11, 39, 'https://img.drz.lazcdn.com/g/kf/Sffea00e218574c6695aed2be17a8a81fP.jpg_2200x2200q80.jpg_.webp', 1, 1, '2025-06-26 15:16:31', '2025-07-01 05:38:26'),
(12, 39, 'https://img.drz.lazcdn.com/g/kf/S59eca1b9255c494987bfe016f7e5ecf0v.jpg_2200x2200q80.jpg_.webp', 0, 2, '2025-06-26 15:16:31', '2025-06-26 15:16:31'),
(14, 36, 'https://wellversed.in/cdn/shop/files/Packof2-MiamiThunder_Electrolytes_Listing_773x773.png?v=1730139354', 1, 0, '2025-07-02 11:11:26', '2025-07-02 11:11:26'),
(15, 35, 'https://wellversed.in/cdn/shop/files/Plain_Front___33serv___FruitFusion_Creatine___Listing___Wellcore___Wellversed_150x150_crop_center.jpg?v=1730139777', 1, 0, '2025-07-02 11:12:28', '2025-07-02 11:12:28'),
(16, 34, 'https://asitisnutrition.com/cdn/shop/files/Mango_Delight.jpg?v=1749638404&width=600', 1, 0, '2025-07-02 11:14:40', '2025-07-02 11:14:40'),
(17, 30, 'https://img10.hkrtcdn.com/39889/prd_3988809-HK-Vitals-Skin-Radiance-Collagen-Marine-Collagen-200-g-Orange_o.jpg', 1, 0, '2025-07-02 11:15:13', '2025-07-02 11:15:13'),
(18, 40, 'https://img.drz.lazcdn.com/g/kf/S4761ff3e570b47fa9165a119e37edc2dx.jpg_720x720q80.jpg', 1, 0, '2025-07-02 11:20:28', '2025-07-02 11:20:28'),
(19, 40, 'https://dukaan.b-cdn.net/2000x2000/webp/media/864ac30f-8e10-4817-bea5-57ab547193ff.jpg', 0, 1, '2025-07-02 11:20:28', '2025-07-02 11:20:28');

-- --------------------------------------------------------

--
-- Table structure for table `referral_earnings`
--

CREATE TABLE `referral_earnings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referral_earnings`
--

INSERT INTO `referral_earnings` (`id`, `user_id`, `order_id`, `amount`, `status`, `created_at`, `updated_at`) VALUES
(2, 3, 27, 743.40, 'paid', '2025-05-08 05:40:51', '2025-07-02 12:19:06'),
(3, 3, 74, 445.00, 'paid', '2025-07-02 12:09:57', '2025-07-02 12:09:57'),
(4, 3, 75, 890.00, 'paid', '2025-07-02 12:16:18', '2025-07-02 12:16:18'),
(5, 3, 76, 120.00, 'paid', '2025-07-02 13:23:06', '2025-07-02 13:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `review`, `created_at`, `updated_at`) VALUES
(1, 39, 3, 2, 'Nice Review', '2025-06-26 15:28:47', '2025-06-26 15:28:47'),
(3, 35, 3, 3, 'Geniun product', '2025-06-26 17:08:20', '2025-06-26 17:08:20'),
(4, 36, 3, 5, 'Effective product', '2025-06-30 16:17:32', '2025-06-30 16:17:32'),
(5, 40, 3, 3, 'good product', '2025-07-02 11:22:12', '2025-07-02 11:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'commission_rate', '5', '2025-05-08 10:36:07', '2025-05-08 10:36:07'),
(2, 'min_withdrawal', '100', '2025-05-08 10:36:07', '2025-05-08 10:36:07'),
(3, 'auto_approve', 'true', '2025-05-08 10:36:07', '2025-05-08 10:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `type`, `reference_id`, `reference_type`, `description`, `balance_after`, `created_at`) VALUES
(2, 3, 619.50, 'referral_earning', 1, 'referral_earning', 'Referral commission from order #NN202505074820', 619.50, '2025-05-07 14:39:07'),
(3, 3, 743.40, 'referral_earning', 2, 'referral_earning', 'Referral commission from order #NN202505082554', 1362.90, '2025-05-08 05:40:51'),
(4, 3, -362.00, 'withdrawal', 2, 'withdrawal', 'Withdrawal request #2', 1000.90, '2025-05-08 06:56:21'),
(5, 3, -1000.00, 'withdrawal', 3, 'withdrawal', 'Withdrawal request #3', 0.90, '2025-05-09 04:29:08'),
(6, 3, -445.00, 'withdrawal', 4, 'withdrawal', 'Withdrawal request #4', -444.10, '2025-07-02 12:30:46'),
(7, 3, -120.00, 'withdrawal', 5, 'withdrawal', 'Withdrawal request #5', -564.10, '2025-07-02 13:24:46'),
(8, 3, -220.00, 'withdrawal', 6, 'withdrawal', 'Withdrawal request #6', -784.10, '2025-07-02 13:29:06'),
(9, 3, -100.00, 'withdrawal', 7, 'withdrawal', 'Withdrawal request #7', -884.10, '2025-07-02 13:31:26'),
(10, 3, -100.00, 'withdrawal', 8, 'withdrawal', 'Withdrawal request #8', -984.10, '2025-07-02 13:52:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `referral_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `referral_code`, `referred_by`, `referral_earnings`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `first_name`, `last_name`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '9876543210', 'customer', 'ADMIN123', NULL, 0.00, NULL, NULL, '2025-05-04 06:30:00', '2025-07-02 12:17:48', '', ''),
(2, 'customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Customer', '9876543211', 'customer', 'CUST123', NULL, 0.00, NULL, NULL, '2025-05-04 06:30:00', '2025-05-04 06:30:00', '', ''),
(3, 'Prasanga741', 'prasangaramanpokharel@gmail.com', '$2y$10$PMW5yaBnJlSDxpTwvdbh7uRtyMMCsoj5AWbNPvA9S3mWCWVxC49HG', NULL, '9765470926', 'admin', '6816dab3e50ac', NULL, 470.90, '21860651dbc5718071151c4a94ac3a3389ee3dac844e5e0c1a1ed762910036fd', '2025-07-01 12:44:01', '2025-05-04 03:10:43', '2025-07-02 13:52:38', 'Prasanga', 'Pokharel'),
(4, 'umesh741', 'incpractical@gmail.com', '$2y$10$y98eHeqK54fKzyQj.lvktOAzmiC.DMfYpdxAD5ASTL0mWjdszJkuS', NULL, NULL, 'customer', '681721245ddb8', 3, 0.00, NULL, NULL, '2025-05-04 08:11:16', '2025-07-01 03:43:11', 'Umesh', 'Pokharel'),
(5, 'jayapokharel659', 'jaya@gmail.com', '$2y$10$aVbO62KAftm9s6wlFZv5jOddwKCQ6GhA5Gu70GCNlhvvlwtqdEk.2', 'Jaya Pokharel', '981138848', 'customer', '1d1cbbe8', NULL, 0.00, NULL, NULL, '2025-05-09 15:40:06', '2025-05-09 15:40:06', 'Jaya', 'Pokharel'),
(6, 'prasangapokharel366', 'prasangaraman@gmail.com', '$2y$10$jOzC3E7dx4QVo0y0ISysb..mPCAtnwHuSVEJ67VVt1IhKaztkrDh2', 'Prasanga Pokharel', '9765470926', 'customer', 'a8311d95', NULL, 0.00, NULL, NULL, '2025-06-23 05:06:34', '2025-06-23 10:22:28', 'Prasanga', 'Pokharel'),
(7, 'jayapokharel151', 'incpractical@gamil.com', '$2y$10$a74hYlh4raDu/bhTn3gh6e1DK6pXhYFC47RmU.tKYvcZDiZvw0P3a', 'Jaya Pokharel', '984212529', 'customer', '5a189707', 3, 0.00, NULL, NULL, '2025-07-02 12:05:27', '2025-07-02 12:05:27', 'Jaya', 'Pokharel'),
(8, 'umeshpokharel147', 'wizardvictor14@gmail.com', '$2y$10$o.0qr2D.cCAb4rGxbpf0ceG/M8EZa3tR.Dp2qQpMw48C7gdtvQbFS', 'Umesh Pokharel', '9842023379', 'customer', '8cb4di', 7, 0.00, NULL, NULL, '2025-07-02 12:26:18', '2025-07-02 12:26:18', 'Umesh', 'Pokharel'),
(9, 'prabidhisolution450', 'prashanna787898@gmail.com', '$2y$10$D2MxQzbAodALIXPKAxQzS.wUAbJ0khheKobKwTJfdYsd.X/Mtfqg6', 'Prabidhi Solution', '9842023389', 'customer', '60f744', NULL, 0.00, NULL, NULL, '2025-07-02 12:53:03', '2025-07-02 12:53:03', 'Prabidhi', 'Solution');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(17, 3, 35, '2025-06-26 16:03:53'),
(18, 3, 34, '2025-06-30 18:27:30'),
(21, 3, 40, '2025-07-02 14:35:18');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_details` text NOT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `payment_method`, `payment_details`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(2, 3, 362.00, 'upi', '{\"upi_id\":\"Prasanga Raman Pokharel\"}', 'completed', '', '2025-05-08 06:56:21', '2025-07-02 12:49:37'),
(3, 3, 1000.00, 'upi', '{\"upi_id\":\"985454354\"}', 'completed', '', '2025-05-09 04:29:08', '2025-05-09 04:30:11'),
(4, 3, 445.00, 'paytm', '{\"paytm_number\":\"9765470926\"}', 'completed', '', '2025-07-02 12:30:46', '2025-07-02 13:21:21'),
(5, 3, 120.00, 'Esewa', '[]', 'completed', '', '2025-07-02 13:24:46', '2025-07-02 13:24:58'),
(6, 3, 220.00, 'Esewa', '[]', 'completed', '', '2025-07-02 13:29:06', '2025-07-02 13:29:14'),
(7, 3, 100.00, 'Esewa', '[]', 'completed', '', '2025-07-02 13:31:26', '2025-07-02 13:31:32'),
(8, 3, 100.00, 'Esewa', '[]', 'completed', '', '2025-07-02 13:52:38', '2025-07-02 13:52:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon_user` (`coupon_id`,`user_id`),
  ADD KEY `idx_user_coupon` (`user_id`,`coupon_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `delivery_charges`
--
ALTER TABLE `delivery_charges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `esewa_payments`
--
ALTER TABLE `esewa_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `khalti_payments`
--
ALTER TABLE `khalti_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `is_primary` (`is_primary`),
  ADD KEY `sort_order` (`sort_order`),
  ADD KEY `idx_product_images_product_id` (`product_id`),
  ADD KEY `idx_product_images_primary` (`product_id`,`is_primary`);

--
-- Indexes for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `referred_by` (`referred_by`),
  ADD KEY `referred_by_idx` (`referred_by`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `delivery_charges`
--
ALTER TABLE `delivery_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `esewa_payments`
--
ALTER TABLE `esewa_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `khalti_payments`
--
ALTER TABLE `khalti_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `esewa_payments`
--
ALTER TABLE `esewa_payments`
  ADD CONSTRAINT `esewa_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `esewa_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `khalti_payments`
--
ALTER TABLE `khalti_payments`
  ADD CONSTRAINT `khalti_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `khalti_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  ADD CONSTRAINT `referral_earnings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_earnings_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2025 at 04:13 PM
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
(3, 'NX2025', NULL, 'fixed', 200.00, 1000.00, NULL, 12, 10, 0, '[41,40,39,36,35,34,30]', 1, '2025-08-28 11:01:00', '2025-06-27 15:55:46', '2025-07-03 13:15:31'),
(4, 'KAPIL10', NULL, 'fixed', 100.00, 1000.00, NULL, 10, 10, 0, '[41,40,39,36,35,34,30]', 1, '2025-08-29 22:53:00', '2025-06-30 17:08:47', '2025-07-03 08:42:28'),
(5, 'BHAWANA10', NULL, 'fixed', 200.00, 5000.00, NULL, 5, 10, 0, '[41,40,39,36,35,34,30]', 1, '2025-08-27 14:30:00', '2025-07-03 08:46:08', '2025-07-03 08:46:08');

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
-- Table structure for table `gateway_currencies`
--

CREATE TABLE `gateway_currencies` (
  `id` int(11) NOT NULL,
  `gateway_id` int(11) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_symbol` varchar(10) NOT NULL,
  `conversion_rate` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `min_limit` decimal(10,2) DEFAULT NULL,
  `max_limit` decimal(10,2) DEFAULT NULL,
  `percentage_charge` decimal(5,2) DEFAULT 0.00,
  `fixed_charge` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gateway_currencies`
--

INSERT INTO `gateway_currencies` (`id`, `gateway_id`, `currency_code`, `currency_symbol`, `conversion_rate`, `min_limit`, `max_limit`, `percentage_charge`, `fixed_charge`, `is_active`) VALUES
(1, 1, 'NPR', '₹', 1.0000, NULL, NULL, 0.00, 0.00, 1),
(2, 2, 'NPR', '₹', 1.0000, NULL, NULL, 0.00, 0.00, 1),
(3, 3, 'NPR', '₹', 1.0000, NULL, NULL, 0.00, 0.00, 1),
(4, 4, 'NPR', '₹', 1.0000, NULL, NULL, 0.00, 0.00, 1);

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
(6, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹445.00 has been submitted and is being processed.', 'withdrawal_request', 4, 1, '2025-07-02 12:30:46'),
(7, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹120.00 has been submitted and is being processed.', 'withdrawal_request', 5, 1, '2025-07-02 13:24:46'),
(8, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹220.00 has been submitted and is being processed.', 'withdrawal_request', 6, 1, '2025-07-02 13:29:06'),
(9, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 7, 1, '2025-07-02 13:31:26'),
(10, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 8, 1, '2025-07-02 13:52:38'),
(11, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 9, 1, '2025-07-03 07:28:38'),
(12, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹100.00 has been submitted and is being processed.', 'withdrawal_request', 10, 1, '2025-07-03 08:13:47');

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
  `payment_method_id` int(11) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','unpaid','paid') NOT NULL DEFAULT 'pending',
  `address` text NOT NULL,
  `order_notes` text DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gateway_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice`, `user_id`, `customer_name`, `contact_no`, `payment_method_id`, `status`, `address`, `order_notes`, `transaction_id`, `total_amount`, `delivery_fee`, `payment_screenshot`, `created_at`, `updated_at`, `gateway_id`) VALUES
(19, 'NN202505083038', 3, 'rwerewrew', '3244323432', 1, 'paid', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 17346.00, 0.00, NULL, '2025-05-08 05:00:16', '2025-07-03 06:33:44', 1),
(27, 'NN202505082554', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'paid', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', NULL, NULL, 14868.00, 0.00, NULL, '2025-05-08 05:49:55', '2025-07-03 06:33:44', 1),
(28, 'NN202505082021', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'processing', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-08 05:56:36', '2025-07-03 06:33:44', 1),
(29, 'NN202505083406', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 24780.00, 0.00, NULL, '2025-05-08 06:25:47', '2025-07-03 06:33:44', 1),
(30, 'NN202505088831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-08 06:26:31', '2025-07-03 06:33:44', 1),
(31, 'NN202505089371', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-08 06:29:27', '2025-07-03 06:33:44', 1),
(32, 'NN202505087737', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 42126.00, 0.00, NULL, '2025-05-08 06:55:16', '2025-07-03 06:33:44', 1),
(33, 'NN202505084831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 17346.00, 0.00, NULL, '2025-05-08 07:17:47', '2025-07-03 06:33:44', 1),
(34, 'NN202505091470', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-09 04:28:06', '2025-07-03 06:33:44', 1),
(35, 'NN202505099910', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:28:22', '2025-07-03 06:33:44', 1),
(36, 'NN202505091269', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 19824.00, 0.00, NULL, '2025-05-09 05:52:44', '2025-07-03 06:33:44', 1),
(37, 'NN202505098051', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 4956.00, 0.00, NULL, '2025-05-09 05:55:22', '2025-07-03 06:33:44', 1),
(38, 'NN202505093826', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:57:15', '2025-07-03 06:33:44', 1),
(39, 'NN202505097713', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 2478.00, 0.00, NULL, '2025-05-09 05:59:46', '2025-07-03 06:33:44', 1),
(40, 'NN202505097597', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', NULL, NULL, 12390.00, 0.00, NULL, '2025-05-09 15:17:09', '2025-07-03 06:33:44', 1),
(41, 'NN202505094713', 5, 'erwerewr', 'rewrewr', 1, 'paid', 'rewrewr, ewrwer, rwerewr, Neyy', NULL, NULL, 13500.14, 0.00, NULL, '2025-05-09 16:16:19', '2025-07-03 06:33:44', 1),
(42, 'NN202506233246', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 13367.04, 0.00, NULL, '2025-06-23 05:47:25', '2025-07-03 06:33:44', 1),
(43, 'NN202506235573', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 1462.26, 0.00, NULL, '2025-06-23 05:49:19', '2025-07-03 06:33:44', 1),
(44, 'NN202506232914', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 1462.26, 0.00, NULL, '2025-06-23 05:50:51', '2025-07-03 06:33:44', 1),
(45, 'NN202506233562', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2924.51, 0.00, NULL, '2025-06-23 06:02:42', '2025-07-03 06:33:44', 1),
(46, 'NN202506233641', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2924.51, 0.00, NULL, '2025-06-23 06:39:56', '2025-07-03 06:33:44', 1),
(47, 'NN202506236885', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruw1, Near khola, Inaruwa, 1, Nepal', NULL, NULL, 2511.04, 0.00, NULL, '2025-06-23 06:48:05', '2025-07-03 06:33:44', 1),
(62, 'NN202506231332', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'processing', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '', 2511.04, 0.00, '', '2025-06-23 10:05:12', '2025-07-03 06:33:44', 1),
(63, 'NN202506236670', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'paid', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', 'test', '', 1110.14, 0.00, '', '2025-06-23 10:08:03', '2025-07-03 06:33:44', 1),
(64, 'NN202506235259', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'pending', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '434234342', 1110.14, 0.00, 'payment_1750674338_4594.png', '2025-06-23 10:10:38', '2025-07-03 06:33:44', 2),
(65, 'NN202506269428', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'shipped', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', 'shop', '', 1110.14, 0.00, '', '2025-06-26 13:49:51', '2025-07-03 06:33:44', 1),
(66, 'NN202506271176', 3, 'Prasanga Pokharel', '9765470926', 1, 'cancelled', 'Inaruwa, 1, Inaruwa, 1, Nepal', '', '', 1235.00, 0.00, NULL, '2025-06-27 14:52:06', '2025-07-03 06:33:44', 1),
(67, 'NN202506277198', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'cancelled', 'Inaruwa-1, SUnsari, Near khola, Inaruwa, 1, Nepal', '', '', 1235.00, 0.00, NULL, '2025-06-27 14:56:50', '2025-07-03 06:33:44', 1),
(68, 'NN202506273345', 3, 'Prasanga Pokharel', '9765470926', 1, 'paid', 'the , the , the , 1, Nepal', '', '', 1100.00, 0.00, NULL, '2025-06-27 15:50:44', '2025-07-03 06:33:44', 1),
(69, 'NTX202507023317', 3, 'Lila Glass', '+1 (547) 137-1217', 1, 'pending', '389 Clarendon Street, Nobis molestias dolo, Laboriosam doloribu, Rerum quos dolores r Ipsum nemo sint nul', 'Commodi sed dolor do', 'Totam id voluptatum ', 2600.00, 0.00, '', '2025-07-02 06:36:37', '2025-07-03 06:33:44', 1),
(70, 'NTX202507022411', 3, 'Mary Cruz', '+1 (596) 964-3661', 1, 'pending', '348 West White First Avenue, Officia tempora cupi, Omnis culpa at exerc, Corrupti et ut erro Tempore sint a eaqu', 'Eum similique ea et ', '', 1300.00, 0.00, '', '2025-07-02 06:38:50', '2025-07-03 06:33:44', 1),
(71, 'NTX202507026550', 3, 'rwerewrew', '3244323432', 1, 'pending', 'rssfasfs, fafssaf, fasff ', '', '', 1200.00, 0.00, '', '2025-07-02 10:05:08', '2025-07-03 06:33:44', 1),
(72, 'NTX202507029514', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '22222', 1300.00, 0.00, '1751450902_bank.png', '2025-07-02 10:08:22', '2025-07-03 06:33:44', 2),
(73, 'NTX202507026372', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 1300.00, 0.00, '', '2025-07-02 10:41:43', '2025-07-03 06:33:44', 1),
(74, 'NTX202507027866', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 8900.00, 0.00, '', '2025-07-02 12:23:52', '2025-07-03 06:33:44', 1),
(75, 'NTX202507024731', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 8900.00, 0.00, '', '2025-07-02 12:30:59', '2025-07-03 06:33:44', 1),
(76, 'NTX202507026017', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 1200.00, 0.00, '', '2025-07-02 13:37:11', '2025-07-03 06:33:44', 1),
(77, 'NTX202507031740', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '34543543', 8800.00, 0.00, '1751520558_Screenshot 2025-06-20 112550.png', '2025-07-03 05:29:18', '2025-07-03 06:33:44', 2),
(78, 'NTX202507037803', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '443343', 8900.00, 0.00, '', '2025-07-03 05:30:12', '2025-07-03 06:33:44', 2),
(79, 'NTX202507033724', 3, 'Prasanga Raman Pokharel', '9705470926', 2, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '34543543', 8900.00, 0.00, '', '2025-07-03 05:36:13', '2025-07-03 06:33:44', 2),
(80, 'NTX202507036333', 3, 'Prasanga Raman Pokharel', '9705470926', NULL, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 17800.00, 0.00, '', '2025-07-03 06:33:51', '2025-07-03 06:33:51', NULL),
(81, 'NTX202507034588', 3, 'Prasanga Raman Pokharel', '9705470926', NULL, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '34543543', 8900.00, 0.00, '1751524654_Screenshot 2025-06-20 124043.png', '2025-07-03 06:37:34', '2025-07-03 06:47:02', NULL),
(82, 'NTX202507035833', 3, 'Prasanga Raman Pokharel', '9705470926', NULL, 'cancelled', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '34543543', 1239.20, 0.00, '1751524729_Screenshot 2025-06-20 124043.png', '2025-07-03 06:38:49', '2025-07-03 06:46:49', NULL),
(83, 'NTX202507034827', 3, 'Prasanga Raman Pokharel', '9705470926', 4, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '34543543', 2116.80, 0.00, '1751525192_Screenshot 2025-06-20 124043.png', '2025-07-03 06:46:32', '2025-07-03 06:46:32', NULL),
(84, 'NTX202507033578', 3, 'Prasanga Raman Pokharel', '9705470926', 3, 'paid', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 8900.00, 0.00, '', '2025-07-03 06:53:40', '2025-07-03 11:07:54', NULL),
(85, 'NTX202507031056', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 1300.00, 0.00, '', '2025-07-03 07:38:33', '2025-07-03 11:46:24', NULL),
(86, 'NTX202507034186', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 8900.00, 0.00, '', '2025-07-03 13:21:53', '2025-07-03 13:21:53', NULL),
(87, 'NTX202507058686', 3, 'Prasanga Raman Pokhares', '9705470926', 1, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 1916.80, 0.00, '', '2025-07-05 07:14:49', '2025-07-05 07:14:49', NULL),
(88, 'NTX202507059208', 3, 'Prasanga Raman Pokharel', '9705470926', 1, 'pending', 'Inaruwa-1, SUnsari, Inaruwa, 1 ', '', '', 1100.00, 0.00, '', '2025-07-05 07:19:00', '2025-07-05 07:19:00', NULL),
(89, 'NTX202507065674', 8, 'rakesh Niraula', '9899929929', 1, 'paid', 'namuna tole, inaruwa, 1 ', '', '', 5900.00, 0.00, '', '2025-07-06 07:06:32', '2025-07-06 07:07:44', NULL),
(90, 'NTX202507068308', 5, 'erwerewr', 'rewrewr', 1, 'paid', 'rewrewr, ewrwer, rwerewr ', '', '', 5900.00, 0.00, '', '2025-07-06 07:09:34', '2025-07-06 07:09:50', NULL),
(91, 'NTX202507061546', 7, 'Stone Hutchinson', '+1 (948) 181-1034', 1, 'paid', '95 East New Court, Aut quae qui quia oc, Asperiores id vero ', '', '', 5900.00, 0.00, '', '2025-07-06 07:11:35', '2025-07-06 07:11:46', NULL);

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
(95, 76, 39, 1, 1300.00, 1300.00, 'NTX202507026017'),
(96, 77, 40, 1, 8900.00, 8900.00, 'NTX202507031740'),
(97, 78, 40, 1, 8900.00, 8900.00, 'NTX202507037803'),
(98, 79, 40, 1, 8900.00, 8900.00, 'NTX202507033724'),
(99, 80, 40, 2, 8900.00, 17800.00, 'NTX202507036333'),
(100, 81, 40, 1, 8900.00, 8900.00, 'NTX202507034588'),
(101, 82, 36, 1, 1239.20, 1239.20, 'NTX202507035833'),
(102, 83, 30, 1, 2116.80, 2116.80, 'NTX202507034827'),
(103, 84, 40, 1, 8900.00, 8900.00, 'NTX202507033578'),
(104, 85, 39, 1, 1300.00, 1300.00, 'NTX202507031056'),
(105, 86, 40, 1, 8900.00, 8900.00, 'NTX202507034186'),
(106, 87, 30, 1, 2116.80, 2116.80, 'NTX202507058686'),
(107, 88, 39, 1, 1300.00, 1300.00, 'NTX202507059208'),
(108, 89, 40, 1, 8900.00, 8900.00, 'NTX202507065674'),
(109, 90, 40, 1, 8900.00, 8900.00, 'NTX202507068308'),
(110, 91, 40, 1, 8900.00, 8900.00, 'NTX202507061546');

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `type` enum('manual','digital','cod') NOT NULL DEFAULT 'digital',
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `supported_currencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supported_currencies`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_test_mode` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `type`, `description`, `logo`, `supported_currencies`, `parameters`, `is_active`, `is_test_mode`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Cash on Delivery', 'cod', 'cod', 'Pay when you receive your order', NULL, NULL, '{\"fee_type\": \"fixed\", \"fee_amount\": 0}', 1, 0, 1, '2025-07-03 05:48:02', '2025-07-03 13:13:51'),
(2, 'Khalti', 'khalti', 'digital', 'Pay using Khalti digital wallet', NULL, NULL, '{\"public_key\": \"\", \"secret_key\": \"\", \"webhook_url\": \"\"}', 0, 0, 2, '2025-07-03 05:48:02', '2025-07-03 08:22:07'),
(3, 'MyPay', 'mypay', 'digital', 'Pay using MyPay digital wallet', NULL, NULL, '{\"merchant_username\": \"\", \"merchant_password\": \"\", \"merchant_id\": \"\", \"api_key\": \"\"}', 0, 0, 3, '2025-07-03 05:48:02', '2025-07-06 06:45:38'),
(4, 'Bank Transfer', 'bank_transfer', 'manual', 'Pay via bank transfer', NULL, NULL, '{\"bank_name\":\"Nabil Bank Limited\",\"account_number\":\"1234567890123455\",\"account_name\":\"NutriNexus Pvt. Ltd.\",\"branch\":\"New Road, Kathmandu\",\"swift_code\":\"\"}', 1, 0, 4, '2025-07-03 05:48:02', '2025-07-03 06:37:20'),
(6, 'ESEWA', 'esewa', 'digital', 'a test', NULL, NULL, '{\"public_key\":\"\",\"secret_key\":\"8gBm\\/:&EnhH.1\\/q\",\"merchant_id\":\"EPAYTEST\",\"api_key\":\"\",\"webhook_url\":\"http:\\/\\/192.168.1.74:8000\\/esewa\\/webhook\",\"merchant_username\":\"\",\"merchant_password\":\"\"}', 0, 0, 0, '2025-07-03 06:52:54', '2025-07-03 06:53:58');

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
  `active` tinyint(1) DEFAULT 1,
  `gateway_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`, `active`, `gateway_id`) VALUES
(1, 'Cash on Delivery', 'Pay when you receive your order', 1, '2025-04-21 01:59:59', '2025-07-03 05:48:02', 1, 1),
(2, 'Khalti', 'Pay using Khalti digital wallet', 1, '2025-04-21 01:59:59', '2025-07-03 05:48:02', 1, 2),
(3, 'eSewa', 'Pay using eSewa digital wallet', 1, '2025-04-21 01:59:59', '2025-04-21 01:59:59', 1, NULL),
(4, 'Bank Transfer', 'Pay via bank transfer', 1, '2025-04-21 01:59:59', '2025-07-03 05:48:02', 1, 4);

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
(40, 'Cellucor C4 The Original Explosive Pre-Workout, 360 g (0.80 lb)', 'cellucor-c4-the-original-explosive-pre-workout-360-g-0-80-lb', 'Global C4 Pre-Workout Protein Powder in India is the Global No.1 Brand known to build high energy levels for the next workout sessions to perform exploits. It is India’s one of the most explosive and rich supplement powders that leads to wonderful bodybuilding sessions and gives excellent results for exercise and training levels. It is also used for powerful, healthy physique building and performance. You can now easily depend upon Cellucor C4 Pre-Workout Original Protein Powder.\r\n\r\nThree Things you can Expect From Cellucor4 Supplement Powder and that is -\r\n\r\n1. Rich and High Amount of Energy\r\n2. Muscle Building Support\r\n3. Incredible Work Outs\r\n\r\nImportant Advantages and Benefits of Cellucor 4 Original\r\n\r\nIncreases Muscle and Body Growth in the Body\r\n\r\nCellucor C4 Pre –Workout contains Carnosine, a healthy amount of regulated acid that builds up muscles, it helps to build muscle by strengthening the body fully. Therefore the supplement of Beta-alanine supplements boosts the production of carnosine and gives excellent performance to the body for excellent physical activity.\r\n\r\nGives Excellent Immunity and Stamina for More Physical Exercise\r\n\r\nThe good amount of Creatine Nitrate present in the mix protein powder blend benefits you for high performance with excellent stamina and immunity counts. It removes all fatigue from the body and brings a great number of energy levels for much exercising and relaxation before working out. To keep your muscle growth enduring, you need to eat well and take Cellucor C4 on time before working out. This will surely help you keep weakness away from you!\r\n\r\nRich Amount of Calories\r\n\r\nCalories help you keep your energy level high. Without calories, nobody can survive or have a healthy lifestyle. Without energy levels, your cells in the body will be weak and die easily because of a lack of calories to maintain excellent function and growth of your cells, tissues, organs, and even over body physique. Cellucor C4 has a complete amount of calories which you require for your body.\r\nGood Amount of Carbohydrates\r\n\r\nCarbohydrates are the most important source for your body; whatever you eat or drink that has a healthy amount of food for you, it helps you be full, adds on the best calories, and supports you with high energy levels. And the c4 supplement has all the required amount of carbohydrates for your body that helps you for excessive workouts and builds a unique physique that you have planned for your body weight gains.\r\n\r\nImportant to Note*\r\n\r\nCarbohydrates have enough energy and sources which fuels up your mind, kidneys, heart muscles, and all the nervous system in the body. It keeps your lungs strong for healthy oxygen flow. Cellucor C4 available near you is easy to order and purchase with free delivery online in Mumbai, India. Don’t miss this product for healthiness for your pre-workout.\r\n\r\nVitamin C (Ascorbic Acid)\r\n\r\nThe Cellucor C4 has a good amount of Vitamin C that prevents people from having bad diet structures in the body. Now, if you ask what happens when there is less Vitamin C, it brings weakness. Inflammation of the gums can also cause bad purple marks on body parts like skin, wounds, and the corkscrew hairs. Also, less Vitamin C leads to poor wound healing. So knowing that Vitamin C is loaded in this product, Cellucor c4 is wellness for all the person who is into exercising, etc.\r\n\r\nOther beneficial sources for a healthy body are Niacin, Vitamin B, Folic Acid, Vitamin B 12, and Calcium.\r\n\r\nThe other Three Essential and Most Explosive Energy Sources are –\r\n\r\n· Beta-Alanine: Decreases and Removes Fatigue From the Body.\r\n· Creatinine Nitrate: Overall maintains and gives more strength for excellent performance.\r\n· Arginine AKG: Controls the hormonal balances and lowers the body\'s inflammation and more.\r\nPeople Also Ask Us Questions about CELLUCOR C4 Pre-Workout Original, 0.80 lbs, 360 g, 60 Servings\r\n\r\nQ1. Why is CELLUCOR C4 different From Other Protein Powder?\r\n\r\nCellucor C4 is a very powerful protein powder that contains an excellent and high amount of energy sources for excessive workout sessions. It has the best sources like Vitamins C, Vitamin B 6 and Vitamin B 12 and many more. It also has the three main essential sources like Beta Alanine, Creatine Nitrate and Arginine AKG.\r\n\r\nQ2. Is Cellucor C4 Healthy?\r\n\r\nCellucor C4 has the highest amount of caffeine that is up to 200mg. As long as you drin', 8900.00, 5900.00, 20, 'Pre-Workout', '360 g', '60serving', 0, 'watermelon', NULL, 0, 1, '2025-07-02 11:20:27', '2025-07-02 11:20:27'),
(41, 'PERFECT SPORTS Ultra Fuel 100% Grass-Fed Wey Protein', 'perfect-sports-ultra-fuel-100-grass-fed-wey-protein', 'WHY ULTRA-FUEL?: We wanted to make your protein choice simple. ULTRA FUEL 100% Grass-Fed Whey Protein delivers a pure protein that checks all the boxes with simple, easy to understand ingredients. With 24 grams of pure protein and less than 2 grams of carbs, you get a pure, concentrated protein without all the carbs and fat normally found in milk.\r\nEVERYTHING GOOD, NOTHING BAD: ULTRA FUEL ensures you get all the natural benefits without the artificial additives or excess processing. It contains no aspartame, artificial colors, or banned substances, making it an excellent choice for athletes and health enthusiasts seeking to fuel their goals with a clean, high-quality protein.\r\nUNDENATURED PROTEIN = MORE PROTEIN: Cold-temperature cross-flow micro-filtration effectively concentrates the protein while maintaining its original native shape and function. These naturally occurring whey protein fractions indicate that it’s in its native form and has not been deformed by excessive processing such as heat, acids or enzymes.\r\nCOMPLETE AMINO PROFILE: Amino Acids are what every protein is made of. ULTRA FUEL protein is a 100% complete protein that is high in EAAs (the essential amino acids), meaning it contains all the essential amino acids that you need to get from your diet. Fuel your gains with 11 g of EAAs in every serving!\r\nOUR STORY: We have been dedicated to providing top pros and athletes with the absolute highest quality nutritional supplements for over 20 years! Since 2003, Perfect Sports has been the choice for the most dedicated and discerning physique transformation athletes who demanded the very best.', 8900.00, 8150.00, 10, 'Protein', '2kg', '80', 0, 'Vanilla', NULL, 0, 0, '2025-07-03 07:40:07', '2025-07-03 07:40:07');

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
(19, 40, 'https://dukaan.b-cdn.net/2000x2000/webp/media/864ac30f-8e10-4817-bea5-57ab547193ff.jpg', 0, 1, '2025-07-02 11:20:28', '2025-07-02 11:20:28'),
(20, 41, 'https://m.media-amazon.com/images/I/51gza6tlzfL._AC_SL1000_.jpg', 1, 0, '2025-07-03 07:40:09', '2025-07-03 07:40:09'),
(21, 41, 'https://m.media-amazon.com/images/I/61GTqRaR+OL._AC_SL1000_.jpg', 0, 1, '2025-07-03 07:40:09', '2025-07-03 07:40:09'),
(22, 41, 'https://m.media-amazon.com/images/I/61t34xyOasL._AC_SL1000_.jpg', 0, 2, '2025-07-03 07:40:09', '2025-07-03 07:40:09'),
(23, 41, 'https://m.media-amazon.com/images/I/71I+cdlB3YL._AC_SL1500_.jpg', 0, 3, '2025-07-03 07:40:09', '2025-07-03 07:40:09');

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
(5, 3, 76, 120.00, 'paid', '2025-07-02 13:23:06', '2025-07-02 13:23:06'),
(6, 3, 85, 130.00, 'paid', '2025-07-03 11:31:24', '2025-07-03 11:31:24'),
(7, 7, 89, 590.00, 'paid', '2025-07-06 06:52:44', '2025-07-06 06:52:44'),
(8, 3, 91, 590.00, 'paid', '2025-07-06 06:56:46', '2025-07-06 06:56:46');

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
(8, 41, 3, 2, 'sdsddsdsdsds', '2025-07-04 12:06:14', '2025-07-04 12:06:14'),
(9, 40, 3, 2, 'testsdsdsdsd', '2025-07-04 12:14:33', '2025-07-04 12:14:33'),
(10, 34, 3, 1, 'niceeuuuuj', '2025-07-05 06:57:04', '2025-07-05 06:57:04'),
(11, 39, 3, 2, 'niceeeeeeop', '2025-07-05 15:50:00', '2025-07-05 15:50:00');

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
-- Table structure for table `sms_ab_tests`
--

CREATE TABLE `sms_ab_tests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `template_a_id` int(11) NOT NULL,
  `template_b_id` int(11) NOT NULL,
  `traffic_split` int(11) DEFAULT 50,
  `winner_template_id` int(11) DEFAULT NULL,
  `status` enum('draft','running','completed','paused') DEFAULT 'draft',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_analytics`
--

CREATE TABLE `sms_analytics` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `total_sent` int(11) DEFAULT 0,
  `total_delivered` int(11) DEFAULT 0,
  `total_failed` int(11) DEFAULT 0,
  `total_bounced` int(11) DEFAULT 0,
  `total_cost` decimal(10,4) DEFAULT 0.0000,
  `delivery_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_automation_rules`
--

CREATE TABLE `sms_automation_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `trigger_event` enum('user_registration','cart_abandoned','order_placed','order_shipped','order_delivered','no_purchase_30_days','birthday','product_viewed','product_restocked','wishlist_added') NOT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `delay_minutes` int(11) DEFAULT 0,
  `template_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `max_sends_per_user` int(11) DEFAULT 1,
  `cooldown_hours` int(11) DEFAULT 24,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_automation_rules`
--

INSERT INTO `sms_automation_rules` (`id`, `name`, `trigger_event`, `conditions`, `delay_minutes`, `template_id`, `is_active`, `max_sends_per_user`, `cooldown_hours`, `created_at`, `updated_at`) VALUES
(1, 'Welcome New Users', 'user_registration', '{}', 5, 1, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(2, 'Cart Abandonment 15min', 'cart_abandoned', '{\"min_cart_value\": 25}', 15, 2, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(3, 'Cart Abandonment 2hr', 'cart_abandoned', '{\"min_cart_value\": 25}', 120, 3, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(4, 'Cart Abandonment 24hr', 'cart_abandoned', '{\"min_cart_value\": 50}', 1440, 4, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(5, 'Order Confirmation', 'order_placed', '{}', 2, 5, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(6, 'Order Shipped', 'order_shipped', '{}', 0, 6, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(7, 'Order Delivered', 'order_delivered', '{}', 60, 7, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(8, 'Win Back Campaign', 'no_purchase_30_days', '{\"last_order_min_value\": 50}', 0, 8, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(9, 'Birthday Campaign', 'birthday', '{}', 0, 9, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(10, 'Restock Notifications', 'product_restocked', '{}', 0, 10, 1, 1, 24, '2025-07-06 05:33:03', '2025-07-06 05:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `sms_automation_triggers`
--

CREATE TABLE `sms_automation_triggers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `automation_rule_id` int(11) NOT NULL,
  `trigger_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trigger_data`)),
  `triggered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `sms_queue_id` int(11) DEFAULT NULL,
  `status` enum('pending','processed','skipped','failed') DEFAULT 'pending',
  `skip_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_blacklist`
--

CREATE TABLE `sms_blacklist` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `reason` enum('user_request','spam_report','invalid_number','carrier_block','compliance') NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_campaigns`
--

CREATE TABLE `sms_campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('blast','automated','triggered','drip') NOT NULL,
  `target_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_audience`)),
  `template_id` int(11) DEFAULT NULL,
  `schedule_type` enum('immediate','scheduled','recurring') NOT NULL DEFAULT 'immediate',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `cron_expression` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','paused','completed','cancelled') NOT NULL DEFAULT 'draft',
  `total_recipients` int(11) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `delivered_count` int(11) DEFAULT 0,
  `failed_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('queued','sent','delivered','failed','bounce','spam') NOT NULL DEFAULT 'queued',
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `cost` decimal(10,4) DEFAULT 0.0000,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `user_id`, `phone_number`, `template_id`, `campaign_id`, `message`, `status`, `provider_response`, `cost`, `error_message`, `sent_at`, `delivered_at`, `created_at`) VALUES
(1, NULL, '9765470926', 1, NULL, 'Hi Prasanga! Welcome to Nutrinexas! Get 10% off your first order with code WELCOME10. Shop now: http://192.168.1.74:8000', 'failed', NULL, 0.0100, 'Unknown API error', '2025-07-06 05:34:32', NULL, '2025-07-06 05:34:32'),
(2, NULL, '9765470926', 5, NULL, 'Thanks Prasanga! Your order ##NTX202507059208  has been confirmed. Total: ₹1,300.00. Track: http://192.168.1.74:8000/orders/track', 'failed', '{\"message\":\"ERR: INVALID API KEY\"}', 0.0000, 'ERR: INVALID API KEY', '2025-07-06 06:09:37', NULL, '2025-07-06 06:09:37'),
(3, NULL, '9765470926', 1, NULL, 'Hi Prasanga! Welcome to Nutrinexas! Get 10% off your first order with code WELCOME10. Shop now: http://192.168.1.74:8000', 'failed', '{\"message\":\"ERR: INVALID API KEY\"}', 0.0000, 'ERR: INVALID API KEY', '2025-07-06 06:10:47', NULL, '2025-07-06 06:10:47'),
(4, NULL, '9765470926', 1, NULL, 'Hi Prasanga! Welcome to Nutrinexas! Get 10% off your first order with code WELCOME10. Shop now: http://192.168.1.74:8000', 'failed', NULL, 0.0100, 'Unknown API error', '2025-07-06 11:56:21', NULL, '2025-07-06 11:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `sms_queue`
--

CREATE TABLE `sms_queue` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `automation_rule_id` int(11) DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `priority` int(11) DEFAULT 1,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

CREATE TABLE `sms_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('welcome','abandoned_cart','order_confirmation','shipping','delivery','review_request','win_back','birthday','promotional','restock','loyalty','upsell','cross_sell') NOT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `name`, `category`, `content`, `variables`, `is_active`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'Welcome New User', 'welcome', 'Hi {first_name}! Welcome to {store_name}! Get 10% off your first order with code WELCOME10. Shop now: {shop_url}', '[\"first_name\", \"store_name\", \"shop_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:40'),
(2, 'Abandoned Cart 15min', 'abandoned_cart', 'Hey {first_name}, you left {item_count} items in your cart! Complete your order now: {cart_url}', '[\"first_name\", \"item_count\", \"cart_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(3, 'Abandoned Cart 2hr', 'abandoned_cart', '{first_name}, still thinking about {product_name}? Here\'s 5% off to complete your order: {cart_url} Code: CART5', '[\"first_name\", \"product_name\", \"cart_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(4, 'Abandoned Cart 24hr', 'abandoned_cart', 'Last chance! Your cart expires soon. Get 15% off: {cart_url} Code: SAVE15', '[\"cart_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(5, 'Order Confirmation', 'order_confirmation', 'Thanks {first_name}! Your order #{order_id} has been confirmed. Total: {total_amount}. Track: {tracking_url}', '[\"first_name\", \"order_id\", \"total_amount\", \"tracking_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(6, 'Shipped Order', 'shipping', 'Great news {first_name}! Your order #{order_id} has shipped. Track: {tracking_url}', '[\"first_name\", \"order_id\", \"tracking_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(7, 'Delivered Order', 'delivery', 'Your order #{order_id} has been delivered! How was your experience? Rate us: {review_url}', '[\"order_id\", \"review_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(8, 'Win Back 30 Days', 'win_back', 'We miss you {first_name}! Come back and get 20% off your next order: {shop_url} Code: COMEBACK20', '[\"first_name\", \"shop_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(9, 'Birthday Offer', 'birthday', 'Happy Birthday {first_name}! 🎉 Here\'s a special 25% off gift: {shop_url} Code: BIRTHDAY25', '[\"first_name\", \"shop_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(10, 'Restock Alert', 'restock', 'Good news {first_name}! {product_name} is back in stock. Get yours now: {product_url}', '[\"first_name\", \"product_name\", \"product_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(11, 'Loyalty Reward', 'loyalty', 'Congratulations {first_name}! You\'ve earned {points} points. Redeem them: {rewards_url}', '[\"first_name\", \"points\", \"rewards_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03'),
(12, 'Upsell Offer', 'upsell', 'Perfect match for your recent purchase! Get {product_name} at 15% off: {product_url}', '[\"product_name\", \"product_url\"]', 1, 1, '2025-07-06 05:33:03', '2025-07-06 05:33:03');

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
(10, 3, -100.00, 'withdrawal', 8, 'withdrawal', 'Withdrawal request #8', -984.10, '2025-07-02 13:52:38'),
(11, 3, -100.00, 'withdrawal', 9, 'withdrawal', 'Withdrawal request #9', -1084.10, '2025-07-03 07:28:38'),
(12, 3, -100.00, 'withdrawal', 10, 'withdrawal', 'Withdrawal request #10', -1184.10, '2025-07-03 08:13:47');

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
(3, 'Prasanga741', 'prasangaramanpokharel@gmail.com', '$2y$10$PMW5yaBnJlSDxpTwvdbh7uRtyMMCsoj5AWbNPvA9S3mWCWVxC49HG', NULL, '9765470926', 'admin', '6816dab3e50ac', NULL, 990.90, '21860651dbc5718071151c4a94ac3a3389ee3dac844e5e0c1a1ed762910036fd', '2025-07-01 12:44:01', '2025-05-04 03:10:43', '2025-07-06 06:56:46', 'Prasanga', 'Pokharel'),
(4, 'umesh741', 'incpractical@gmail.com', '$2y$10$y98eHeqK54fKzyQj.lvktOAzmiC.DMfYpdxAD5ASTL0mWjdszJkuS', NULL, NULL, 'customer', '681721245ddb8', 3, 0.00, NULL, NULL, '2025-05-04 08:11:16', '2025-07-01 03:43:11', 'Umesh', 'Pokharel'),
(5, 'jayapokharel659', 'jaya@gmail.com', '$2y$10$aVbO62KAftm9s6wlFZv5jOddwKCQ6GhA5Gu70GCNlhvvlwtqdEk.2', 'Jaya Pokharel', '981138848', 'customer', '1d1cbbe8', NULL, 0.00, NULL, NULL, '2025-05-09 15:40:06', '2025-05-09 15:40:06', 'Jaya', 'Pokharel'),
(6, 'prasangapokharel366', 'prasangaraman@gmail.com', '$2y$10$jOzC3E7dx4QVo0y0ISysb..mPCAtnwHuSVEJ67VVt1IhKaztkrDh2', 'Prasanga Pokharel', '9765470926', 'customer', 'a8311d95', NULL, 0.00, NULL, NULL, '2025-06-23 05:06:34', '2025-06-23 10:22:28', 'Prasanga', 'Pokharel'),
(7, 'jayapokharel151', 'incpractical@gamil.com', '$2y$10$a74hYlh4raDu/bhTn3gh6e1DK6pXhYFC47RmU.tKYvcZDiZvw0P3a', 'Jaya Pokharel', '984212529', 'admin', '5a189707', 3, 590.00, NULL, NULL, '2025-07-02 12:05:27', '2025-07-06 06:52:44', 'Jaya', 'Pokharel'),
(8, 'umeshpokharel147', 'wizardvictor14@gmail.com', '$2y$10$o.0qr2D.cCAb4rGxbpf0ceG/M8EZa3tR.Dp2qQpMw48C7gdtvQbFS', 'Umesh Pokharel', '9842023379', 'customer', '8cb4di', 7, 0.00, NULL, NULL, '2025-07-02 12:26:18', '2025-07-02 12:26:18', 'Umesh', 'Pokharel'),
(9, 'prabidhisolution450', 'prashanna787898@gmail.com', '$2y$10$D2MxQzbAodALIXPKAxQzS.wUAbJ0khheKobKwTJfdYsd.X/Mtfqg6', 'Prabidhi Solution', '9842023389', 'customer', '60f744', NULL, 0.00, NULL, NULL, '2025-07-02 12:53:03', '2025-07-02 12:53:03', 'Prabidhi', 'Solution');

-- --------------------------------------------------------

--
-- Table structure for table `user_sms_preferences`
--

CREATE TABLE `user_sms_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `is_subscribed` tinyint(1) DEFAULT 1,
  `marketing_consent` tinyint(1) DEFAULT 0,
  `transactional_consent` tinyint(1) DEFAULT 1,
  `categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`categories`)),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(24, 3, 40, '2025-07-05 15:15:08');

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
(8, 3, 100.00, 'Esewa', '[]', 'completed', '', '2025-07-02 13:52:38', '2025-07-02 13:52:54'),
(9, 3, 100.00, 'Esewa', '[]', 'completed', '', '2025-07-03 07:28:38', '2025-07-03 08:05:42'),
(10, 3, 100.00, 'bank_transfer', '{\"account_name\":\"Nabil\",\"account_number\":\"64363464\",\"bank_name\":\"NIC\",\"ifsc_code\":\"REstrr\"}', 'completed', '', '2025-07-03 08:13:47', '2025-07-03 08:15:30');

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
-- Indexes for table `gateway_currencies`
--
ALTER TABLE `gateway_currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_gateway_currency` (`gateway_id`,`currency_code`);

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
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gateway_id` (`gateway_id`);

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
-- Indexes for table `sms_ab_tests`
--
ALTER TABLE `sms_ab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_id` (`campaign_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `template_a_id` (`template_a_id`),
  ADD KEY `template_b_id` (`template_b_id`),
  ADD KEY `winner_template_id` (`winner_template_id`);

--
-- Indexes for table `sms_analytics`
--
ALTER TABLE `sms_analytics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_campaign` (`date`,`campaign_id`,`template_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_campaign_id` (`campaign_id`),
  ADD KEY `idx_template_id` (`template_id`);

--
-- Indexes for table `sms_automation_rules`
--
ALTER TABLE `sms_automation_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trigger_event` (`trigger_event`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `sms_automation_triggers`
--
ALTER TABLE `sms_automation_triggers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_automation_rule_id` (`automation_rule_id`),
  ADD KEY `idx_triggered_at` (`triggered_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `sms_queue_id` (`sms_queue_id`),
  ADD KEY `idx_automation_triggers_user_processed` (`user_id`,`processed_at`);

--
-- Indexes for table `sms_blacklist`
--
ALTER TABLE `sms_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_phone_number` (`phone_number`),
  ADD KEY `idx_reason` (`reason`);

--
-- Indexes for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_phone_number` (`phone_number`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_campaign_id` (`campaign_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_sms_logs_user_status` (`user_id`,`status`);

--
-- Indexes for table `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `automation_rule_id` (`automation_rule_id`),
  ADD KEY `idx_sms_queue_scheduled_status` (`scheduled_at`,`status`);

--
-- Indexes for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

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
-- Indexes for table `user_sms_preferences`
--
ALTER TABLE `user_sms_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_phone` (`user_id`,`phone_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_phone_number` (`phone_number`),
  ADD KEY `idx_subscribed` (`is_subscribed`),
  ADD KEY `idx_user_preferences_marketing` (`marketing_consent`,`is_subscribed`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT for table `gateway_currencies`
--
ALTER TABLE `gateway_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `khalti_payments`
--
ALTER TABLE `khalti_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sms_ab_tests`
--
ALTER TABLE `sms_ab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_analytics`
--
ALTER TABLE `sms_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_automation_rules`
--
ALTER TABLE `sms_automation_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sms_automation_triggers`
--
ALTER TABLE `sms_automation_triggers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_blacklist`
--
ALTER TABLE `sms_blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sms_queue`
--
ALTER TABLE `sms_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_templates`
--
ALTER TABLE `sms_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_sms_preferences`
--
ALTER TABLE `user_sms_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `gateway_currencies`
--
ALTER TABLE `gateway_currencies`
  ADD CONSTRAINT `gateway_currencies_ibfk_1` FOREIGN KEY (`gateway_id`) REFERENCES `payment_gateways` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`gateway_id`) REFERENCES `payment_gateways` (`id`);

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
-- Constraints for table `sms_ab_tests`
--
ALTER TABLE `sms_ab_tests`
  ADD CONSTRAINT `sms_ab_tests_ibfk_1` FOREIGN KEY (`template_a_id`) REFERENCES `sms_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_ab_tests_ibfk_2` FOREIGN KEY (`template_b_id`) REFERENCES `sms_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_ab_tests_ibfk_3` FOREIGN KEY (`winner_template_id`) REFERENCES `sms_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_automation_rules`
--
ALTER TABLE `sms_automation_rules`
  ADD CONSTRAINT `sms_automation_rules_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `sms_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_automation_triggers`
--
ALTER TABLE `sms_automation_triggers`
  ADD CONSTRAINT `sms_automation_triggers_ibfk_1` FOREIGN KEY (`automation_rule_id`) REFERENCES `sms_automation_rules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_automation_triggers_ibfk_2` FOREIGN KEY (`sms_queue_id`) REFERENCES `sms_queue` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_campaigns`
--
ALTER TABLE `sms_campaigns`
  ADD CONSTRAINT `sms_campaigns_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `sms_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `sms_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD CONSTRAINT `sms_queue_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `sms_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_queue_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `sms_campaigns` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_queue_ibfk_3` FOREIGN KEY (`automation_rule_id`) REFERENCES `sms_automation_rules` (`id`) ON DELETE SET NULL;

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

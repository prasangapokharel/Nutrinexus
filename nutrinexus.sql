-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 06:39 PM
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
-- Database: `nutrinexus`
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
(1, 3, 'rwerewrew', '3244323432', 'rssfasfs', 'fafasff', 'fafssaf', 'fasff', 'afsfa', 'India', 1, '2025-05-04 06:00:02', '2025-05-04 06:00:02'),
(2, 4, 'Marsden Franklin', '+1 (412) 114-2873', '46 Fabien Avenue', 'Ut laborum Quia duc', 'Minus nostrum ullam', 'Ut qui nulla officia', 'Velit velit sed qui', 'Reprehenderit archit', 1, '2025-05-04 08:11:36', '2025-05-04 08:11:36'),
(3, 5, 'erwerewr', 'rewrewr', 'rewrewr', 'rewrwer', 'ewrwer', 'rwerewr', 'werwer', 'Neyy', 1, '2025-05-09 16:15:45', '2025-05-09 16:15:45');

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
(5, 3, 'Withdrawal Request Submitted', 'Your withdrawal request for ₹1,000.00 has been submitted and is being processed.', 'withdrawal_request', 3, 1, '2025-05-09 04:29:08');

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
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice`, `user_id`, `customer_name`, `contact_no`, `payment_method_id`, `status`, `address`, `total_amount`, `delivery_fee`, `payment_screenshot`, `created_at`, `updated_at`) VALUES
(19, 'NN202505083038', 3, 'rwerewrew', '3244323432', 1, 'paid', 'rssfasfs, fafssaf, fasff, India', 17346.00, 0.00, NULL, '2025-05-08 05:00:16', '2025-05-08 05:00:16'),
(27, 'NN202505082554', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'paid', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', 14868.00, 0.00, NULL, '2025-05-08 05:49:55', '2025-05-08 05:40:51'),
(28, 'NN202505082021', 4, 'Marsden Franklin', '+1 (412) 114-2873', 1, 'processing', '46 Fabien Avenue, Minus nostrum ullam, Ut qui nulla officia, Reprehenderit archit', 2478.00, 0.00, NULL, '2025-05-08 05:56:36', '2025-05-08 05:56:36'),
(29, 'NN202505083406', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 24780.00, 0.00, NULL, '2025-05-08 06:25:47', '2025-05-08 06:25:47'),
(30, 'NN202505088831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 12390.00, 0.00, NULL, '2025-05-08 06:26:31', '2025-05-08 06:26:31'),
(31, 'NN202505089371', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 12390.00, 0.00, NULL, '2025-05-08 06:29:27', '2025-05-08 06:29:27'),
(32, 'NN202505087737', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 42126.00, 0.00, NULL, '2025-05-08 06:55:16', '2025-05-08 06:55:16'),
(33, 'NN202505084831', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 17346.00, 0.00, NULL, '2025-05-08 07:17:47', '2025-05-08 07:17:47'),
(34, 'NN202505091470', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 12390.00, 0.00, NULL, '2025-05-09 04:28:06', '2025-05-09 04:28:06'),
(35, 'NN202505099910', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 2478.00, 0.00, NULL, '2025-05-09 05:28:22', '2025-05-09 05:28:22'),
(36, 'NN202505091269', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 19824.00, 0.00, NULL, '2025-05-09 05:52:44', '2025-05-09 05:52:44'),
(37, 'NN202505098051', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 4956.00, 0.00, NULL, '2025-05-09 05:55:22', '2025-05-09 05:55:22'),
(38, 'NN202505093826', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 2478.00, 0.00, NULL, '2025-05-09 05:57:15', '2025-05-09 05:57:15'),
(39, 'NN202505097713', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 2478.00, 0.00, NULL, '2025-05-09 05:59:46', '2025-05-09 05:59:46'),
(40, 'NN202505097597', 3, 'rwerewrew', '3244323432', 1, 'processing', 'rssfasfs, fafssaf, fasff, India', 12390.00, 0.00, NULL, '2025-05-09 15:17:09', '2025-05-09 15:17:09'),
(41, 'NN202505094713', 5, 'erwerewr', 'rewrewr', 1, 'processing', 'rewrewr, ewrwer, rwerewr, Neyy', 13500.14, 0.00, NULL, '2025-05-09 16:16:19', '2025-05-09 16:16:19');

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
(34, 27, 32, 1, 10500.00, 10500.00, 'NN202505082554'),
(35, 28, 30, 1, 2100.00, 2100.00, 'NN202505082021'),
(36, 29, 32, 2, 10500.00, 21000.00, 'NN202505083406'),
(37, 30, 32, 1, 10500.00, 10500.00, 'NN202505088831'),
(38, 31, 32, 1, 10500.00, 10500.00, 'NN202505089371'),
(39, 32, 32, 3, 10500.00, 31500.00, 'NN202505087737'),
(40, 32, 30, 2, 2100.00, 4200.00, 'NN202505087737'),
(41, 33, 30, 2, 2100.00, 4200.00, 'NN202505084831'),
(42, 33, 32, 1, 10500.00, 10500.00, 'NN202505084831'),
(43, 34, 32, 1, 10500.00, 10500.00, 'NN202505091470'),
(44, 35, 30, 1, 2100.00, 2100.00, 'NN202505099910'),
(45, 36, 32, 1, 10500.00, 10500.00, 'NN202505091269'),
(46, 36, 30, 3, 2100.00, 6300.00, 'NN202505091269'),
(47, 37, 30, 2, 2100.00, 4200.00, 'NN202505098051'),
(48, 38, 30, 1, 2100.00, 2100.00, 'NN202505093826'),
(49, 39, 30, 1, 2100.00, 2100.00, 'NN202505097713'),
(50, 40, 32, 1, 10500.00, 10500.00, 'NN202505097597'),
(51, 41, 32, 1, 10500.00, 10500.00, 'NN202505094713'),
(52, 41, 33, 1, 940.80, 940.80, 'NN202505094713');

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
(30, 'Hk Vital Collagen', 'hk-vital-collagen-681d92eaf1099', 'Clinically proven to reduce fine lines and wrinkles by 48% in 8 weeks* \r\n    4X smoother skin in 8 weeks*\r\n    87% users experienced glowing skin in 4 weeks**', 2116.80, NULL, 248, 'Protein', NULL, NULL, 0, NULL, 'https://img4.hkrtcdn.com/38845/prd_3884453-HK-Vitals-Skin-Radiance-Collagen-Marine-Collagen-200-g-Orange_o.jpg', 0, 0, '2025-05-07 13:35:00', '2025-05-09 16:10:46'),
(32, 'Avvatar Whey Protein | 2 Kg | Malai Kulfi Flavour', 'avvatar-whey-protein-2-kg-malai-kulfi-flavour-681d92f59a6d4', 'Unlock your true potential with Avvatar Whey Protein, a powerhouse packed with an impressive 28 grams of protein per 35 grams of rounded scoop.\r\n\r\nUnique Blend Of Whey Protein Isolate & Concentrate\r\nOur unique blend is carefully formulated to provide fast-absorbing whey protein isolate for rapid muscle recovery, along with the sustained-release benefits of whey protein concentrate for prolonged nourishment. \r\n\r\n100% Natural Flavours\r\nAvvatar Whey Protein is crafted with only the finest natural ingredients and without any artificial colours and fillers. \r\n\r\nMade From 100% Cow Milk \r\nAvvatar Whey protein is made from fresh cow’s milk, 100% Vegetarian, and manufactured with multiple stringent quality tests. \r\n\r\n100% Truly Vegetarian\r\nDistinguishing ourselves from others, we use microbial enzymes instead of non-veg rennet in making our protein powders hence making our protein products vegetarian in the true sense.', 9200.00, NULL, 252, 'Protein', NULL, NULL, 0, NULL, 'https://www.avvatarindia.com/images/product_images/1697552226_FOP.jpg', 0, 0, '2025-05-07 13:53:47', '2025-05-09 16:16:19'),
(33, 'RiteBite Max Protein Daily Choco Berry Protein Bar 300g', 'ritebite-max-protein-choco-berry-300g-682e7f123456', '100% vegetarian protein bars with 10g protein per bar. Supports energy, fitness, and immunity. Choco berry flavor.', 940.80, NULL, 99, 'Protein', NULL, NULL, 0, NULL, 'https://img.drz.lazcdn.com/g/kf/Sd8d88b74c8f44a5194074ce38800dcbcX.jpg_720x720q80.jpg', 163, 0, '2025-05-09 16:10:46', '2025-05-09 16:16:19'),
(34, 'ASITIS ATOM Isolate Whey Protein 1kg Chocolate', 'asitis-atom-isolate-whey-protein-1kg-chocolate-682e7f123457', 'Isolate whey protein with 30g protein, 6.1g BCAA, and 13g EAA per serving. Chocolate flavor, supports muscle growth.', 4160.00, NULL, 0, 'Protein', NULL, NULL, 0, NULL, 'https://laz-img-sg.alicdn.com/p/645ba7e8c19b0091d075c5f45c2dce27.jpg', 15, 0, '2025-05-09 16:10:46', '2025-05-09 16:12:54'),
(35, 'Wellcore Creatine Monohydrate 307g Fruit Fusion', 'wellcore-creatine-monohydrate-307g-fruit-fusion-682e7f123458', 'Lab-tested creatine monohydrate to support athletic performance and power. Fruit fusion flavor.', 2128.00, NULL, 100, 'Creatine', NULL, NULL, 0, NULL, 'https://img.drz.lazcdn.com/g/kf/S3b1324339578494d852861d350077c9eg.jpg_720x720q80.jpg', 20, 0, '2025-05-09 16:10:46', '2025-05-09 16:12:34'),
(36, 'Wellcore Electrolytes Miami Thunder 200g', 'wellcore-electrolytes-miami-thunder-200g-682e7f123459', 'Sugar-free electrolyte drink powder with 5 vital electrolytes (Na, Mg, Ca, K, PO4). Fat-fuel-powered, keto-friendly, Miami Thunder flavor.', 1239.20, NULL, 100, 'Electrolytes', NULL, NULL, 0, NULL, 'https://img.drz.lazcdn.com/static/np/p/33ceb9a8bc153dff89726a0bd0436a9c.jpg_720x720q80.jpg', 0, 0, '2025-05-09 16:10:46', '2025-05-09 16:11:24');

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
(2, 3, 27, 743.40, 'paid', '2025-05-08 05:40:51', '2025-05-08 05:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 3, -1000.00, 'withdrawal', 3, 'withdrawal', 'Withdrawal request #3', 0.90, '2025-05-09 04:29:08');

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
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '9876543210', 'admin', 'ADMIN123', NULL, 0.00, NULL, NULL, '2025-05-04 06:30:00', '2025-05-04 06:30:00', '', ''),
(2, 'customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Customer', '9876543211', 'customer', 'CUST123', NULL, 0.00, NULL, NULL, '2025-05-04 06:30:00', '2025-05-04 06:30:00', '', ''),
(3, 'Prasanga741', 'prasangaramanpokharel@gmail.com', '$2y$10$sJnyHyPC5urmL.osSNYP8e1ON9OGVxH9oEVwhJqmZOrsdiBa40FVC', NULL, '', 'admin', '6816dab3e50ac', NULL, 0.90, NULL, NULL, '2025-05-04 03:10:43', '2025-05-09 06:02:16', 'Prasanga', 'Pokharel'),
(4, 'umesh741', 'incpractical@gmail.com', '$2y$10$y98eHeqK54fKzyQj.lvktOAzmiC.DMfYpdxAD5ASTL0mWjdszJkuS', NULL, NULL, 'customer', '681721245ddb8', 3, 0.00, NULL, NULL, '2025-05-04 08:11:16', '2025-05-09 15:40:37', 'Umesh', 'Pokharel'),
(5, 'jayapokharel659', 'jaya@gmail.com', '$2y$10$aVbO62KAftm9s6wlFZv5jOddwKCQ6GhA5Gu70GCNlhvvlwtqdEk.2', 'Jaya Pokharel', '981138848', 'customer', '1d1cbbe8', NULL, 0.00, NULL, NULL, '2025-05-09 15:40:06', '2025-05-09 15:40:06', 'Jaya', 'Pokharel');

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
(14, 3, 30, '2025-05-09 04:59:16'),
(15, 5, 33, '2025-05-09 16:20:46');

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
(2, 3, 362.00, 'upi', '{\"upi_id\":\"Prasanga Raman Pokharel\"}', 'pending', NULL, '2025-05-08 06:56:21', '2025-05-08 06:56:21'),
(3, 3, 1000.00, 'upi', '{\"upi_id\":\"985454354\"}', 'completed', '', '2025-05-09 04:29:08', '2025-05-09 04:30:11');

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
-- Indexes for table `delivery_charges`
--
ALTER TABLE `delivery_charges`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `payment_method_id` (`payment_method_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_charges`
--
ALTER TABLE `delivery_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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

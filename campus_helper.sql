-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 08:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_helper`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Academic Writing', 'academic-writing', 'pen', 'Essays, reports, research papers'),
(2, 'Programming & Tech', 'programming', 'code', 'Coding, web dev, apps'),
(3, 'Design & Creative', 'design', 'palette', 'Logos, posters, UI/UX'),
(4, 'Tutoring', 'tutoring', 'book', 'Subject tutoring and coaching'),
(5, 'Translation', 'translation', 'globe', 'Language translation services'),
(6, 'Video & Animation', 'video', 'film', 'Video editing and animation'),
(7, 'Data & Research', 'data', 'chart', 'Data analysis and research'),
(8, 'Other Services', 'other', 'star', 'Any other campus services');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `service_id`, `content`, `is_read`, `created_at`) VALUES
(1, 4, 3, 3, 'hi im interested', 0, '2026-05-14 20:07:25'),
(2, 5, 4, 6, 'vgvyythibutv', 0, '2026-05-14 20:09:39'),
(3, 4, 3, 3, 'Hello, i\'m interested', 0, '2026-05-18 09:44:17'),
(4, 6, 4, 6, 'Hieekllkineifbifbi', 0, '2026-05-18 09:45:21'),
(5, 6, 4, 8, 'gvvtbyivytvubty', 0, '2026-05-25 09:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `seller_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','in_progress','completed','cancelled','disputed') DEFAULT 'pending',
  `requirements` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `service_id`, `buyer_id`, `seller_id`, `amount`, `platform_fee`, `seller_earnings`, `status`, `requirements`, `delivery_date`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 3, 30.00, 1.50, 28.50, 'in_progress', 'hfvubibi', '2026-05-15', '2026-05-14 20:07:42', '2026-05-14 20:07:49'),
(2, 3, 4, 3, 30.00, 1.50, 28.50, 'in_progress', 'i need a tutor', '2026-05-22', '2026-05-21 10:40:29', '2026-05-21 10:40:38'),
(3, 3, 4, 3, 30.00, 1.50, 28.50, 'in_progress', 'gyuvv', '2026-05-26', '2026-05-25 08:02:38', '2026-05-25 08:57:46'),
(4, 3, 4, 3, 30.00, 1.50, 28.50, 'in_progress', 'h ghvgb', '2026-05-26', '2026-05-25 08:27:20', '2026-05-25 08:57:39'),
(5, 3, 4, 3, 30.00, 1.50, 28.50, 'in_progress', 'h ghvgb', '2026-05-26', '2026-05-25 08:27:26', '2026-05-25 08:27:31'),
(6, 8, 6, 4, 35.00, 1.75, 33.25, 'completed', 'yfyfyrxrxrtvtuctr', '2026-05-28', '2026-05-25 08:45:08', '2026-05-25 08:45:50'),
(7, 8, 6, 4, 35.00, 1.75, 33.25, 'completed', 'vtytvyigu7', '2026-05-28', '2026-05-25 08:56:49', '2026-05-25 08:57:52'),
(8, 7, 6, 4, 30000000.00, 1500000.00, 28500000.00, 'completed', 'ccyfuyfyfiy', '2026-05-28', '2026-05-25 09:05:06', '2026-05-25 09:05:31'),
(9, 8, 6, 4, 35.00, 1.75, 33.25, 'completed', 'vtvuyibyibi', '2026-05-28', '2026-05-25 09:17:13', '2026-05-25 09:32:02'),
(10, 8, 6, 4, 35.00, 1.75, 33.25, 'completed', 'gvuvtcrtyufy', '2026-05-28', '2026-05-25 09:33:26', '2026-05-25 09:37:04'),
(11, 6, 7, 4, 300000.00, 15000.00, 285000.00, 'in_progress', 'urm nk order plss', '2026-06-15', '2026-06-12 16:57:00', '2026-06-12 16:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) DEFAULT 'simulated',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payer_id`, `amount`, `method`, `transaction_ref`, `status`, `paid_at`, `created_at`) VALUES
(1, 1, 4, 30.00, 'simulated', 'CH-6A05BB15130DD', 'success', '2026-05-14 20:07:49', '2026-05-14 20:07:49'),
(2, 2, 4, 30.00, 'simulated', 'CH-6A0E70A61FABB', 'success', '2026-05-21 10:40:38', '2026-05-21 10:40:38'),
(3, 5, 4, 30.00, 'simulated', 'CH-6A13977377000', 'success', '2026-05-25 08:27:31', '2026-05-25 08:27:31'),
(4, 6, 6, 35.00, 'simulated', 'CH-6A139B97C0690', 'success', '2026-05-25 08:45:11', '2026-05-25 08:45:11'),
(5, 7, 6, 35.00, 'simulated', 'CH-6A139E56201FF', 'success', '2026-05-25 08:56:54', '2026-05-25 08:56:54'),
(6, 4, 4, 30.00, 'simulated', 'CH-6A139E834CE76', 'success', '2026-05-25 08:57:39', '2026-05-25 08:57:39'),
(7, 3, 4, 30.00, 'simulated', 'CH-6A139E8A50DE0', 'success', '2026-05-25 08:57:46', '2026-05-25 08:57:46'),
(8, 8, 6, 30000000.00, 'simulated', 'CH-6A13A0465FE88', 'success', '2026-05-25 09:05:10', '2026-05-25 09:05:10'),
(9, 10, 6, 35.00, 'simulated', 'CH-6A13A6EB0590A', '', '2026-05-25 09:33:31', '2026-05-25 09:33:31'),
(10, 11, 7, 300000.00, 'simulated', 'CH-6A2BC9E3E5619', '', '2026-06-12 16:57:07', '2026-06-12 16:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `order_id`, `reviewer_id`, `seller_id`, `service_id`, `rating`, `comment`, `created_at`) VALUES
(1, 6, 6, 4, 8, 5, 'vyutftbyiyu', '2026-05-25 08:56:08'),
(2, 9, 6, 4, 8, 1, 'no receipt', '2026-05-25 09:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `delivery_days` int(11) NOT NULL DEFAULT 3,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` enum('active','paused','deleted') DEFAULT 'active',
  `total_orders` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `seller_id`, `category_id`, `title`, `description`, `price`, `delivery_days`, `thumbnail`, `status`, `total_orders`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Professional Essay Writing (1000 words)', 'I will write high-quality academic essays for any subject. Plagiarism-free, properly cited, and well-structured.', 45.00, 3, NULL, 'active', 0, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(2, 2, 2, 'Build You a React Website', 'Full responsive website using React. Clean code, mobile-friendly, deployed and ready to use.', 120.00, 7, NULL, 'active', 0, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(3, 3, 4, 'Math & Statistics Tutoring (1 Hour)', 'One-on-one tutoring session via video call. All levels from Form 5 to university.', 30.00, 1, NULL, 'active', 5, '2026-05-14 19:49:31', '2026-05-25 08:57:46'),
(4, 1, 3, 'Logo Design for Student Projects', 'Modern logo design with 3 revisions included. PNG + AI files delivered.', 35.00, 2, NULL, 'active', 0, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(5, 2, 7, 'Data Analysis with Python/Excel', 'I will clean, analyze and visualize your dataset. Include charts and written summary.', 60.00, 4, NULL, 'active', 0, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(6, 4, 2, 'yctvtvy', 'ctcyvyugyfyrdrfyuutc6', 300000.00, 3, NULL, 'active', 1, '2026-05-14 20:08:25', '2026-06-12 16:57:07'),
(7, 4, 6, 'I will draw muka arman', 'as the titlte', 30000000.00, 3, NULL, 'active', 1, '2026-05-18 09:46:18', '2026-05-25 09:05:10'),
(8, 4, 2, 'hcfhvjuvyt', 'hvytvytytv', 35.00, 3, NULL, 'active', 3, '2026-05-25 08:44:25', '2026-05-25 09:33:31'),
(9, 7, 6, 'video editing', 'pakai adobe premier pro', 30.00, 3, NULL, 'active', 0, '2026-06-12 16:58:21', '2026-06-12 16:58:21');

-- --------------------------------------------------------

--
-- Table structure for table `service_reports`
--

CREATE TABLE `service_reports` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','removed') NOT NULL DEFAULT 'pending',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_users`
--

CREATE TABLE `staff_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'support',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_users`
--

INSERT INTO `staff_users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$HGsfKYU7puI00689hmK9a.NUNPsGE1CaKvvMlXzqf/JuVG1q7VErW', 'support', '2026-05-25 10:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `sender_type` enum('user','staff') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `ticket_id`, `sender_type`, `sender_id`, `message`, `created_at`) VALUES
(1, 4, 'staff', 1, 'hello, how can i help you', '2026-05-25 10:09:32'),
(2, 3, 'staff', 1, 'hillo', '2026-05-25 10:09:48'),
(3, 2, 'staff', 1, 'hi', '2026-05-25 10:09:54'),
(4, 1, 'staff', 1, 'hola', '2026-05-25 10:10:02'),
(5, 5, 'user', 6, 'i need help', '2026-05-25 10:20:22'),
(6, 5, 'staff', 1, 'you suck', '2026-05-25 10:24:22'),
(7, 5, 'staff', 1, 'screw you', '2026-05-25 10:24:37'),
(8, 6, 'user', 6, 'tcrfytftuf', '2026-05-25 10:27:50'),
(9, 7, 'user', 6, 'vtcrfiutyc', '2026-05-25 11:02:22'),
(10, 8, 'user', 6, 'fcytvtuoyuct7c', '2026-05-25 11:07:12'),
(11, 8, 'user', 6, 'hello', '2026-05-25 11:07:19'),
(12, 8, 'staff', 1, 'i see you', '2026-05-25 11:07:47'),
(13, 9, 'user', 6, 'cytcfyugt796tyvtuvt7', '2026-05-25 11:12:26'),
(14, 9, 'user', 6, 'im gay', '2026-05-25 11:12:43'),
(15, 9, 'user', 6, 'help', '2026-05-25 11:12:47'),
(16, 9, 'user', 6, 'done', '2026-05-25 11:13:04'),
(17, 9, 'staff', 1, 'stop i hate you so much', '2026-05-25 11:13:30'),
(18, 9, 'user', 6, 'I love you too baby', '2026-05-25 11:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','scam_report','payment_issue','other') DEFAULT 'general',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `staff_response` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `message`, `type`, `status`, `staff_response`, `created_at`, `updated_at`) VALUES
(1, 4, 'Help Request from CampusHelper', 'pjenjniueune', 'general', 'open', NULL, '2026-05-14 20:11:08', '2026-05-14 20:11:08'),
(2, 4, 'Help Request from CampusHelper', 'Muka arman terlalu lawa tak leh draw', 'scam_report', 'open', NULL, '2026-05-18 09:47:09', '2026-05-18 09:47:09'),
(3, 4, 'Help Request from CampusHelper', 'wbwibgwhueonfo', 'general', 'open', NULL, '2026-05-18 09:48:12', '2026-05-18 09:48:12'),
(4, 4, 'Help Request from CampusHelper', 'vtcytuf86d6ftyf6fytfyircrv56fftyf65f', 'general', 'closed', NULL, '2026-05-25 09:46:26', '2026-05-25 10:49:42'),
(5, 6, 'Help Request from CampusHelper', '', 'general', 'closed', NULL, '2026-05-25 10:20:22', '2026-05-25 10:24:48'),
(6, 6, 'Help Request from CampusHelper', '', 'general', 'closed', NULL, '2026-05-25 10:27:50', '2026-05-25 10:49:36'),
(7, 6, 'Help Request from CampusHelper', '', 'general', 'open', NULL, '2026-05-25 11:02:22', '2026-05-25 11:02:22'),
(8, 6, 'Help Request from CampusHelper', '', 'general', 'open', NULL, '2026-05-25 11:07:12', '2026-05-25 11:07:12'),
(9, 6, 'Help Request from CampusHelper', '', 'general', 'open', NULL, '2026-05-25 11:12:26', '2026-05-25 11:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `role` enum('buyer','seller','both','admin') DEFAULT 'both',
  `balance` decimal(10,2) DEFAULT 0.00,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `avatar`, `bio`, `university`, `role`, `balance`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 'ali_hassan', 'ali@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ali Hassan', NULL, NULL, 'University of Malaya', 'both', 0.00, 1, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(2, 'sarah_chen', 'sarah@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Chen', NULL, NULL, 'UTM', 'both', 0.00, 1, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(3, 'raj_kumar', 'raj@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Raj Kumar', NULL, NULL, 'UPM', 'seller', 0.00, 1, '2026-05-14 19:49:31', '2026-05-14 19:49:31'),
(4, 'Rahmanzzz', 'officialrahman.aziz@gmail.com', '$2y$10$ZLlOXIbyTuSUJUYaBMCAvu0yw2dTRZd1uQNWXCo5XcbFS/udLBUL.', 'Rahman AZIZ', NULL, 'jvgrtctryy', 'UTM', 'both', 0.00, 0, '2026-05-14 20:06:48', '2026-05-25 09:37:13'),
(5, 'Rahman', 'grimplayz89@gmail.com', '$2y$10$zgK6xmyftz5HpFiYCxmYze4HrmclT8DP3AEeF4t1Bm33yLeTHE8ZK', 'Stein Fordman', NULL, NULL, 'UTM', 'both', 0.00, 0, '2026-05-14 20:09:24', '2026-05-14 20:09:24'),
(6, 'daddyaziz', 'rahmangais@gmail.com', '$2y$10$tD3Q4xBnAnFFtHmEQ9o9kugKluYNDCWcv6Tb2y1I9qS33jhOfkwmu', 'Rahman AZIZ', NULL, NULL, 'UTM', 'both', 0.00, 0, '2026-05-18 09:45:04', '2026-05-18 09:45:04'),
(7, 'arman', 'armansyahf106@gmail.com', '$2y$10$jzh4yeos9yZ4z/OvsXn/JeM1sLFuOnE8wTCib8qxpOsYML3JnJ2AW', 'armansyah', NULL, NULL, 'UTM', 'both', 0.00, 0, '2026-06-12 16:30:27', '2026-06-12 16:30:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `payer_id` (`payer_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `service_reports`
--
ALTER TABLE `service_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `staff_users`
--
ALTER TABLE `staff_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `sender_type` (`sender_type`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `service_reports`
--
ALTER TABLE `service_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_users`
--
ALTER TABLE `staff_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `service_reports`
--
ALTER TABLE `service_reports`
  ADD CONSTRAINT `service_reports_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_reports_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `staff_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

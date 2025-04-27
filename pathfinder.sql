-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 06:47 PM
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
-- Database: `pathfinder`
--
CREATE DATABASE IF NOT EXISTS `pathfinder` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pathfinder`;

-- --------------------------------------------------------

--
-- Table structure for table `chat_users`
--

CREATE TABLE `chat_users` (
  `id` int(11) NOT NULL,
  `employer_name` varchar(255) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `application_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_users`
--

INSERT INTO `chat_users` (`id`, `employer_name`, `listing_id`, `application_date`) VALUES
(1, 'Micheal', 0, '0000-00-00 00:00:00'),
(2, 'Micheal Jackson', 1, '2025-04-27 09:21:07'),
(3, 'Sunny Daryl', 6, '2025-04-27 09:21:50'),
(4, 'Sunny Daryl', 5, '2025-04-27 09:42:21'),
(5, 'Sunny Daryl', 5, '2025-04-27 09:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `RatingCount` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `rating`, `verified`, `RatingCount`) VALUES
(1, 1, 4.60, 1, 4),
(2, 4, 4.20, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `employer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`employer_id`, `user_id`) VALUES
(1, 2),
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(200) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `event_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fulltime`
--

CREATE TABLE `fulltime` (
  `listing_id` int(11) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `listing_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `listed_date` datetime NOT NULL DEFAULT current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `job_type` enum('full time','part time') NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`listing_id`, `title`, `description`, `employee_id`, `employer_id`, `listed_date`, `location`, `job_type`, `hidden`) VALUES
(1, 'Lawn Moving', 'Mow my front yard', 1, 1, '2025-04-27 09:12:54', 'Downtown San Jose', '', 0),
(2, 'Moving Helper', 'My family is moving and could use some help with it', 1, 1, '2025-04-27 09:13:45', 'Sunnyvale', '', 0),
(3, 'Barista', 'Working as a barista in my coffee shop', 2, 1, '2025-04-27 09:14:37', 'Sunnyvale', 'part time', 0),
(4, 'Cleaner', 'Help clean the kitchen of my house', 1, 2, '2025-04-27 09:15:52', 'Mountain View', '', 0),
(5, 'Plumber', 'Need a full time worker to help with plumbing', 1, 2, '2025-04-27 09:16:31', 'Mountain View', 'full time', 0),
(6, 'Server', 'Need help in the restaurant, short on staff', 1, 2, '2025-04-27 09:17:22', 'Mountain View', 'part time', 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `message_text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender`, `recipient`, `message_text`, `timestamp`, `is_read`) VALUES
(1, 'Micheal', 'user', 'Hello user', '2025-04-28 01:19:03', 0),
(2, 'Micheal', 'user', 'Thank you for applying to our position! We\'ve received your application and will review it shortly. Do you have any questions about the role?', '2025-04-28 01:19:05', 0),
(3, 'user', 'Micheal', 'Hi, I saw the listing about the lawn moving service and I\'m interested!', '2025-04-28 01:19:55', 0),
(4, 'Sunny Daryl', 'user', 'Thank you for applying to our position! We\'ve received your application and will review it shortly. Do you have any questions about the role?\r\n', '2025-04-27 16:42:21', 0),
(5, 'user', 'Sunny Daryl', 'Please Check Out my Profile! (Embedded Link to Profile)', '2025-04-27 16:42:21', 0),
(6, 'user', 'Micheal', 'jhd', '2025-04-28 01:42:45', 0),
(7, 'Sunny Daryl', 'user', 'Thank you for applying to our position! We\'ve received your application and will review it shortly. Do you have any questions about the role?\r\n', '2025-04-27 16:45:01', 0),
(8, 'user', 'Sunny Daryl', 'Please Check Out my Profile! (Embedded Link to Profile)', '2025-04-27 16:45:01', 0),
(9, 'user', 'Micheal', 'Hello Im interested in a job.', '2025-04-28 01:45:21', 0);

-- --------------------------------------------------------

--
-- Table structure for table `parttime`
--

CREATE TABLE `parttime` (
  `listing_id` int(11) NOT NULL,
  `estimated_pay` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('employer','employee') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `profile_pic`, `role`) VALUES
(1, 'Billie', 'Jeans', 'billiejeans@gmail.com', '$2y$10$5bo8b3XnLP3yzLorOO3gIerJZobj9W4kGryfSOgDIn3m9332x/tM6', 'uploads/362f990cfe4ba472.png', 'employee'),
(2, 'Micheal', 'Jackson', 'micheal@gmail.com', '$2y$10$yyxHFQPNIe6DwVuKOKuT1Oac1EasTDDULYv4.leO.pjctA0wTZ1za', 'default_avatar.jpg', 'employer'),
(3, 'Sunny', 'Daryl', 'sunny@gmail.com', '$2y$10$TpyNLGVeC8no5cCg0NoNg.PGDc1DkUre28tCMPnTnX6wkkJ3Dwr7S', 'default_avatar.jpg', 'employer'),
(4, 'Johnson', 'Josh', 'john@gmail.com', '$2y$10$Gw18QYXYO5Xz5bfOM5MIPO2bE9/l/GfQLQYYoojlKmiHSrB3Zc3Xu', 'default_avatar.jpg', 'employee');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_users_after_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  IF NEW.role = 'employee' THEN
    INSERT INTO employees (user_id)
    VALUES (NEW.user_id);
  ELSEIF NEW.role = 'employer' THEN
    INSERT INTO employers (user_id)
    VALUES (NEW.user_id);
  END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_users`
--
ALTER TABLE `chat_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`employer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `fulltime`
--
ALTER TABLE `fulltime`
  ADD PRIMARY KEY (`listing_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `fk_listings_employee` (`employee_id`),
  ADD KEY `fk_listings_employer` (`employer_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender` (`sender`,`recipient`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `parttime`
--
ALTER TABLE `parttime`
  ADD PRIMARY KEY (`listing_id`);

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
-- AUTO_INCREMENT for table `chat_users`
--
ALTER TABLE `chat_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `employer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `fk_employers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `fulltime`
--
ALTER TABLE `fulltime`
  ADD CONSTRAINT `fk_fulltime_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `fk_listings_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_listings_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`employer_id`) ON DELETE CASCADE;

--
-- Constraints for table `parttime`
--
ALTER TABLE `parttime`
  ADD CONSTRAINT `fk_parttime_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 05:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_services_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `image`, `date_posted`) VALUES
(18, 'TEST', '', 'Screenshot 2025-02-03 174608.png', '2025-03-12 20:36:46'),
(26, 'Tesing lang', '', 'Screenshot 2025-02-03 174110.png', '2025-03-13 02:27:30'),
(28, 'Wala lang', '', 'Screenshot 2025-03-08 105220.png', '2025-03-19 05:31:19'),
(29, 'Announcement!!', '', 'Screenshot 2025-01-27 092517.png', '2025-03-19 06:24:42'),
(30, 'Fighting!!', '', 'images.jpg', '2025-04-02 05:33:57'),
(31, 'Sample lang', '', 'Screenshot 2025-03-14 091414.png', '2025-04-03 02:31:05'),
(32, 'walang', '', 'istockphoto-528888367-612x612.jpg', '2025-04-03 07:44:16');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('pending','approved','completed') DEFAULT 'pending',
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `student_id`, `user_id`, `appointment_date`, `status`, `reason`, `created_at`, `updated_at`, `admin_message`) VALUES
(43, 'GAB2022-00258', 'Guidance01', '2025-04-04 12:04:00', 'approved', 'Academic problems', '2025-04-03 04:01:48', '2025-04-03 04:10:33', 'San kana'),
(44, 'GAB2022-00291', 'Guidance01', '2025-04-10 15:38:00', 'pending', 'Academic problem po ', '2025-04-03 07:38:42', '2025-04-03 07:39:43', 'punta ka dito problemahin natin parehas'),
(45, 'Gab2022-00259', 'Guidance01', '2025-04-04 17:59:00', 'approved', 'sinuntok ako ni shane', '2025-04-03 07:53:56', '2025-04-03 07:55:23', 'Suntukin mo rin');

-- --------------------------------------------------------

--
-- Table structure for table `counseling_reports`
--

CREATE TABLE `counseling_reports` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `report_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grievances`
--

CREATE TABLE `grievances` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','resolved','rejected') DEFAULT 'pending',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolution_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grievances`
--

INSERT INTO `grievances` (`id`, `user_id`, `title`, `description`, `status`, `submission_date`, `resolution_date`) VALUES
(1, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 06:35:08', '2025-04-03 07:26:13'),
(2, 'admin01', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 06:59:25', '2025-04-03 07:22:45'),
(3, 'admin01', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 07:05:25', '2025-04-03 07:22:42'),
(4, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 07:10:44', '2025-04-03 07:22:28'),
(5, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 07:18:29', '2025-04-03 07:22:43'),
(6, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'resolved', '2025-04-03 07:22:30', '2025-04-03 07:22:44'),
(7, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'pending', '2025-04-03 07:22:48', NULL),
(8, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'pending', '2025-04-03 07:28:09', NULL),
(9, 'GAB2022-00258', 'sdf', 'sdfsfsdf', 'pending', '2025-04-03 07:28:48', NULL),
(10, 'Gab2022-00259', 'Binagsak ni Sir', 'Di daw nakapag comply pero may valid reason!', 'pending', '2025-04-03 08:00:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `request_type` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `student_id`, `request_type`, `status`, `created_at`) VALUES
(1, '12345', 'Certificate of Enrollment', 'Pending', '2025-03-19 06:37:43'),
(2, '12345', 'TOR', 'Pending', '2025-03-19 06:42:57'),
(3, '12345', 'TOR', 'Pending', '2025-03-19 06:48:43'),
(4, '12345', 'TOR', 'Pending', '2025-03-31 06:35:59'),
(8, 'GAB2022-00258', 'Transcript of Records', 'Approved', '2025-04-01 14:22:54'),
(9, 'GAB2022-00258', 'Transcript of Records', 'Pending', '2025-04-01 14:23:43'),
(10, 'GAB2022-00258', 'Transcript of Records', 'Approved', '2025-04-03 03:06:40'),
(11, 'GAB2022-00291', 'Transcript of Records', 'Approved', '2025-04-03 07:38:58'),
(12, 'GAB2022-00259', 'Transcript of Records', 'Approved', '2025-04-03 07:52:40');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_beds` int(11) NOT NULL,
  `occupied_beds` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `total_beds`, `occupied_beds`, `image`) VALUES
(6, 'Room 1', 5, 3, 'uploads/download (1).jpg'),
(7, 'Room2', 6, 2, 'uploads/images.jpg'),
(8, 'Room 3', 4, 1, 'uploads/images.jpg'),
(9, 'Room 4', 4, 1, 'uploads/download (1).jpg');

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `eligibility` text NOT NULL,
  `deadline` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `name`, `description`, `eligibility`, `deadline`, `created_at`, `status`) VALUES
(4, 'dsfs', 'sfsd', 'fdf', '2025-04-25', '2025-04-03 05:10:04', 'pending'),
(5, 'fds', 'lsdlkfsldf', 'kmlsdfmsl', '2025-04-11', '2025-04-03 05:14:29', 'pending'),
(7, 'Abot kamay', 'Sponsored by President L!', 'For PWD\'s Only', '2025-05-08', '2025-04-03 06:01:52', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `scholarship_applications`
--

CREATE TABLE `scholarship_applications` (
  `id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholarship_applications`
--

INSERT INTO `scholarship_applications` (`id`, `scholarship_id`, `user_id`, `application_date`, `status`) VALUES
(4, 7, 'GAB2022-00258', '2025-04-03 06:24:39', 'approved'),
(5, 4, 'GAB2022-00258', '2025-04-03 07:06:46', 'pending'),
(6, 7, 'GAB2022-00291', '2025-04-03 07:38:18', 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `student_room_applications`
--

CREATE TABLE `student_room_applications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `room_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `applied_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_room_applications`
--

INSERT INTO `student_room_applications` (`id`, `user_id`, `room_id`, `message`, `status`, `applied_at`) VALUES
(33, 'Dormitory01', 6, 'How much', 'Approved', '2025-04-02 11:53:44'),
(34, 'Dormitory01', 7, '', 'Approved', '2025-04-02 12:00:58'),
(35, 'GAB2022-00258', 6, 'how much po', 'Approved', '2025-04-02 13:31:12'),
(36, 'GAB2022-00258', 9, 'how much po', 'Approved', '2025-04-02 14:25:21'),
(37, 'Dormitory01', 8, 'dfsdfsd', 'Approved', '2025-04-02 14:36:49'),
(38, 'GAB2022-00258', 9, 'sdfsd', 'Approved', '2025-04-02 14:40:02'),
(39, 'GAB2022-00258', 8, 'Libre po ba?', 'Approved', '2025-04-02 14:47:07'),
(40, 'GAB2022-00271', 8, 'sfds', 'Rejected', '2025-04-02 15:52:37'),
(41, 'GAB2022-00258', 6, 'Magkano po?', 'Approved', '2025-04-03 10:32:33'),
(42, 'GAB2022-00271', 7, 'Malawak po ba?', 'Pending', '2025-04-03 10:36:46'),
(43, 'GAB2022-00291', 6, 'gkhj', 'Approved', '2025-04-03 15:35:58'),
(44, 'GAB2022-00291', 7, 'magkano ba kupals', 'Pending', '2025-04-03 15:45:39'),
(45, 'Gab2022-00259', 7, 'magkano kups', 'Approved', '2025-04-03 15:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `birth_date` date NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `religion` varchar(50) NOT NULL,
  `biological_sex` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `current_address` varchar(255) NOT NULL,
  `permanent_address` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `mother_name` varchar(50) NOT NULL,
  `mother_work` varchar(50) NOT NULL,
  `mother_contact` varchar(20) NOT NULL,
  `father_name` varchar(50) NOT NULL,
  `father_work` varchar(50) NOT NULL,
  `father_contact` varchar(20) NOT NULL,
  `siblings_count` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `date_registered` datetime DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `year` int(11) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `nationality`, `religion`, `biological_sex`, `email`, `phone`, `current_address`, `permanent_address`, `role`, `password_hash`, `mother_name`, `mother_work`, `mother_contact`, `father_name`, `father_work`, `father_contact`, `siblings_count`, `unit`, `last_login`, `date_registered`, `profile_picture`, `status`, `year`, `section`, `course`, `department`) VALUES
('admin01', 'John', NULL, 'Doe', '1990-01-01', 'Filipino', 'Christian', 'Male', 'admin@example.com', '1234567890', '123 Admin St', '456 Permanent Address St', 'Power Admin', '$2y$10$H2aXoP3v21owUpi9EjZpJul4HjbvU1g80190nFXHr1q/pCWr9WcEi', 'Jane Doe', 'Teacher', '0987654321', 'John Doe', 'Engineer', '1230987654', 3, NULL, NULL, '2025-03-27 22:23:04', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('ADM_67e683402a324', 'sdfnsdknf', 'nsmd,fn', 'lnfsdkfnskdn', '2025-03-04', '', '', '', 'dkdsm2@gmail.com', '0978456314', 'dfskdjfskj', 'jbsdjfbskfnk', 'Student', '$2y$10$3Vea4r2KxXa2AARJHuRRCuY8xzyHyKSosbzFlhJiAhzOf/gED17i2', 'sdfnksn', '', '', 'nskjdfns', '', '', 0, 'Dormitory', NULL, '2025-03-28 19:08:48', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('ADM_67e68827e81d7', 'sdjfnk', 'njsdnkfj', 'nkjsdnfksdnfk', '2025-03-11', '', '', '', 'sdjfnjksdfnk2@gmail.com', '0798546321', 'sdfkjsdfkjsd', 'kljnjksdfieuhk', 'Student', '$2y$10$tfS2jakzj5y0HvRTHO0CH.dnt9YumArtP81ZUknSS8EA0naFQIS66', 'sdfnskdnfk', '', '', 'jnksdjfnksdn', '', '', 0, 'Dormitory', NULL, '2025-03-28 19:29:43', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Dorm123', 'sdfnsdkfjn', 'nsdkfjsn', 'kjnfkdsjnf', '2025-03-18', '', '', '', 'dskfnskdnf2@gmail.com', '09756412358', 'sdjfklj', 'kjsdkjfnkjd', 'Student', '$2y$10$CH.jZQU.BNAvthuwPEVxQ.EXoasIiFFCzWl/kUwYFV8Jw62gUG9kq', 'sdfn', '', '', 'klsldkfn', '', '', 0, 'Dormitory', NULL, '2025-03-28 19:33:31', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Dormitory01', 'Juan', 'Last', 'Song', '2025-03-19', 'filipino', 'wesleyan', 'Male', 'juan02@gmail.com', '09756315487', 'Homeless', 'Homeless', 'Dormitory Admin', '$2y$10$ZnsVQ29PizgLji8nqjICM.grJZlqXRdYAQMmzBfjXxnPnO5GPQ14u', 'Laika', '', '', 'Ralp', '', '', 0, 'Guidance', NULL, '2025-03-31 19:29:28', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('GAB2022-00258', 'Jessie ', 'De Guzman', 'Javier', '2001-02-05', 'Filipino', 'Catholic', 'Male', 'javierjessie02@gmail.com', '09701114903', 'South Poblacion Gabaldon Nueva Ecija', 'Bacong, Umiray, Dingalan, Aurora', 'Student', '$2y$10$mnCIIltXnw3vsZ5OW7NPo.hhLve6VkjsGOIjiWu6wFVhpWo4mjPde', 'Malou Javier', 'Housewife', '09784563241', 'Arnie Javier', 'Driver', '09854763254', 4, NULL, NULL, '2025-03-29 17:37:23', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Gab2022-00259', 'Lavizares ', 'Domingo', 'Frayres III', '2004-05-17', 'Filipino', 'Mistica', 'Male', 'lavizaresf@gmail.com', '09128325757', 'Cuyapa, Gabaldon. Nueva ecija', 'Cuyapa, Gabaldon Nueva Ecija', 'Student', '$2y$10$Vz8Ppzpv6xMk4Oq8bX7k2eH7YL11rNmSiSdV9gRRm.sHReqLUTGzC', 'Mercedes', 'OFW', '09123456789', 'Lavizares', 'N/A', '09234567890', 2, NULL, NULL, '2025-04-03 15:50:55', NULL, 'Inactive', 3, 'B', 'BIST', NULL),
('GAB2022-00271', 'Brando', 'Mendoza', 'Verganos', '2003-08-14', 'Filipino', 'Wesleyan', 'Male', 'verganosbrando555@gmail.com', '09303562427', 'Ligaya', 'Ligaya', 'Student', '$2y$10$UVfICg7VsmBCL/m0VXs00e81pqtTpz6Pr/Kd7vBvzm5shRcPMuUYq', 'Dolly Mendoza', 'Housewife', '09756482456', 'Benjamin Verganos', 'Contractor', '09785463254', 3, NULL, NULL, '2025-04-02 10:23:14', NULL, 'Inactive', 3, 'B', 'BIST', NULL),
('GAB2022-00291', 'SHANE', 'G', 'HGD', '2004-06-10', 'filipino', 'INC', 'Female', 'flororitashane@gmail.com', '09066832584', 'ibona', 'ibona', 'Student', '$2y$10$wCTquK/f4V.623lZhbyBc.ZC1kAHl31jFkKGq/0qnhApcroWCGE0q', 'sofia', 'Housewife', '09658746215', 'romeo', 'farmers', '09765432578', 5, NULL, NULL, '2025-04-03 15:35:15', NULL, 'Inactive', 3, 'B', 'BIST', NULL),
('GAB2022-00685', '', 'Noma', '', '0000-00-00', '', '', 'Female', 'arnykayeadobe@gmail.com', '09687969271', '', '', 'Student', '$2y$10$NCmNOSWTeCp.XLtazF7su.ZbDV9QVTZMHgK4io4gvzAHMP8yQL9n2', 'Cindy N. Adobe', 'Housewife', '09384528909', 'efren M. Adobe', 'Driver', 'NA', 3, NULL, NULL, '2025-03-27 15:31:52', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Guidance01', 'Mila', 'Laso', 'Lambo', '2025-05-08', '', '', '', 'Mila023@gmail.com', '09756482384', 'Umiray', 'Umiray', 'Guidance Admin', '$2y$10$dX.X47cJ9oz.UyzR62eBwOf7PFhoTzkWD3WnMqRocm20HFeWKL1M6', 'Kimmy', '', '', 'Lando', '', '', 0, 'Guidance Admin', NULL, '2025-04-01 11:37:50', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Registrar01', 'Rarnar', 'Larn', 'Gard', '2025-04-17', '', '', '', 'ragnar033@gmail.com', '09756482364', 'Cuyapa', 'Cuyapa, Gabaldon Nueva Ecija', 'Registrar Admin', '$2y$10$y7Dqj2mq234NMYSOV.UvPe/bbRAbCbun8w3U/iVzDGbOwYBWiMibu', 'Linda', '', '', 'Sandy', '', '', 0, 'Registrar Admin', NULL, '2025-04-01 11:35:26', NULL, 'Inactive', NULL, NULL, NULL, NULL),
('Schorlarship01', 'Sky', 'Lagman', 'Berdugo', '1999-10-15', '', '', '', 'Sky01@gmail.com', '09458763542', 'Ligaya', 'Cuyapa, Gabaldon Nueva Ecija', 'Scholarship Admin', '$2y$10$ER41m2pS/cybKbGxtAvDxeA7L0O50G1TesZUnuf0XBdHJ9W59O3v2', 'Melinda Berdugo', '', '', 'Melandro Berdugo', '', '', 0, 'Scholarship Admin', NULL, '2025-04-02 13:38:30', NULL, 'Inactive', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `counseling_reports`
--
ALTER TABLE `counseling_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `grievances`
--
ALTER TABLE `grievances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholarship_applications`
--
ALTER TABLE `scholarship_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scholarship_id` (`scholarship_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_room_applications`
--
ALTER TABLE `student_room_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `counseling_reports`
--
ALTER TABLE `counseling_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grievances`
--
ALTER TABLE `grievances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `scholarship_applications`
--
ALTER TABLE `scholarship_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_room_applications`
--
ALTER TABLE `student_room_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `counseling_reports`
--
ALTER TABLE `counseling_reports`
  ADD CONSTRAINT `counseling_reports_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grievances`
--
ALTER TABLE `grievances`
  ADD CONSTRAINT `grievances_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `scholarship_applications`
--
ALTER TABLE `scholarship_applications`
  ADD CONSTRAINT `scholarship_applications_ibfk_1` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`),
  ADD CONSTRAINT `scholarship_applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `student_room_applications`
--
ALTER TABLE `student_room_applications`
  ADD CONSTRAINT `student_room_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_room_applications_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

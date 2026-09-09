-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 05:41 PM
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
-- Database: `barangay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('Health','Emergency','Event','Public Notice') NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `posted_by` int(11) DEFAULT NULL,
  `likes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `image`, `category`, `date_posted`, `posted_by`, `likes`) VALUES
(1, 'Barangay Clean-Up Drive', 'All residents are encouraged to participate in the scheduled clean-up drive on May 10, 2026, starting at 6:00 AM. Please bring cleaning materials.', 'cleanup.jpg', 'Emergency', '2026-05-03 04:15:15', 1, 4),
(4, 'Barangay Ayuda', 'Good Day Everyone! There will be an incoming \"Ayuda Event\" in our Barangay. Please be informed that on May 20, 2026, all of the beneficiaries are invited to the Barangay Court in Granja Barangay Hall.', '1778637246_ayuda.jpg', 'Event', '2026-05-13 01:54:06', 2, 4),
(6, 'Libreng Tuli', 'Halina sa ating libreng patuli na gaganapin bukas!', '1778761271_images.jpg', 'Public Notice', '2026-05-14 12:21:11', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `announcement_likes`
--

CREATE TABLE `announcement_likes` (
  `like_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement_likes`
--

INSERT INTO `announcement_likes` (`like_id`, `announcement_id`, `user_id`, `created_at`) VALUES
(2, 1, 8, '2026-05-13 02:33:51'),
(3, 4, 8, '2026-05-13 02:38:49'),
(4, 4, 7, '2026-05-14 09:03:07'),
(5, 1, 7, '2026-05-14 09:03:10'),
(6, 4, 10, '2026-05-14 12:22:03'),
(7, 6, 10, '2026-05-14 12:22:05'),
(8, 4, 11, '2026-05-18 12:22:40'),
(9, 1, 11, '2026-05-18 12:22:43'),
(12, 1, 10, '2026-05-19 01:28:16');

-- --------------------------------------------------------

--
-- Table structure for table `establishments`
--

CREATE TABLE `establishments` (
  `establishment_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishments`
--

INSERT INTO `establishments` (`establishment_id`, `name`, `type`, `image`, `owner_name`, `location`, `contact`, `status`, `date_added`) VALUES
(2, 'Community Park Court', 'Basketball Court', '1778635826_1e0c6315-fecc-4ea2-a4d4-e605e37d314b.jpg', 'Hon. Cornel', 'Inside the Community Park', '09123456789', 'Active', '2026-05-13 01:30:26'),
(3, 'Kid Community Park', 'Park', '1778635886_931df9d4-f309-4281-9534-7a4d5607060a.jpg', 'Hon. Cornel', 'Inside the Community Park', '09123456789', 'Active', '2026-05-13 01:31:26'),
(4, 'John Car Rental Store', 'Car Shop', '1778761454_8c9c36e7defe036eae5b1627f0341fed (1).jpg', 'John Benedict Javier', 'Beside Lipa City Community Park', '09124563411', 'Active', '2026-05-14 12:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `establishment_requests`
--

CREATE TABLE `establishment_requests` (
  `request_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `establishment_type` varchar(100) NOT NULL,
  `location` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `validid` varchar(255) NOT NULL,
  `status` enum('Pending','Under Review','Approved','Rejected','Done') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `establishment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `establishment_requests`
--

INSERT INTO `establishment_requests` (`request_id`, `resident_id`, `establishment_type`, `location`, `image_path`, `validid`, `status`, `remarks`, `date_submitted`, `establishment_id`) VALUES
(5, 7, 'Basketball Court', 'Inside the Community Park', '1778635826_1e0c6315-fecc-4ea2-a4d4-e605e37d314b.jpg', '1779112792_national-ID.png', 'Done', 'Okay, please proceed to the Barangay Hall.', '2026-05-13 02:34:07', 2),
(14, 6, 'Basketball Court', 'Inside the Community Park', '1778760383_media_1f813acb9bc2a2aa8e44903f417691fbdd4b3a203.png', '1779112792_national-ID.png', 'Pending', NULL, '2026-05-14 12:06:23', 2),
(15, 9, 'Park', 'Inside the Community Park', '1778761341_media_1f813acb9bc2a2aa8e44903f417691fbdd4b3a203.png', '1779112792_national-ID.png', 'Done', 'Okay, please proceed to the Barangay Hall.', '2026-05-14 12:22:21', 3),
(16, 6, 'Car Shop', 'Beside Lipa City Community Park', '1779100107_media_1f813acb9bc2a2aa8e44903f417691fbdd4b3a203.png', '1779112792_national-ID.png', 'Rejected', 'Sorry, the establishment is now inactive for a while.', '2026-05-18 10:28:27', 4),
(17, 9, 'Basketball Court', 'Inside the Community Park', '1779112792_media_1f813acb9bc2a2aa8e44903f417691fbdd4b3a203.png', '1779112792_national-ID.png', 'Done', 'Okay guys, this is good!', '2026-05-18 13:59:52', 2),
(18, 9, 'Basketball Court', 'Inside the Community Park', '1779153468_national-ID.png', '1779153468_national-ID.png', 'Pending', NULL, '2026-05-19 01:17:48', 2);

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `ownership` enum('Home Owner','Boarder','Renter') NOT NULL,
  `present_address` text DEFAULT NULL,
  `address_type` enum('Owner','Boarder') DEFAULT NULL,
  `provincial_address` text DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `birthplace` varchar(255) DEFAULT NULL,
  `height` varchar(10) DEFAULT NULL,
  `weight` varchar(10) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `house_number` varchar(50) DEFAULT NULL,
  `household_id` int(11) DEFAULT NULL,
  `status` enum('Registered','Not Registered') NOT NULL DEFAULT 'Not Registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`resident_id`, `first_name`, `middle_name`, `last_name`, `ownership`, `present_address`, `address_type`, `provincial_address`, `gender`, `civil_status`, `birthdate`, `birthplace`, `height`, `weight`, `contact_number`, `email_address`, `religion`, `occupation`, `house_number`, `household_id`, `status`, `created_at`) VALUES
(5, 'Benedict', 'Mitra', 'Javier', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Brgy. Syete, Granja, Lipa City, Batangas', 'Male', 'Married', '2004-06-09', 'Lipa City', '160', '48', '09124563411', 'johnbenedictjavier15@gmail.com', 'eqw', 'new', '1025', 0, 'Registered', '2026-05-06 05:53:43'),
(6, 'Rhena Gylle', 'Morkon', 'Loste', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Brgy. Syete, Granja, Lipa City, Batangas', 'Female', 'Single', '2005-11-11', 'Lipa City', '160', '48', '09124563411', 'johnbenedictjavier15@gmail.com', 'Catholic', 'Student', '1026', 0, 'Registered', '2026-05-10 09:17:40'),
(7, 'Bernie', 'Osmillo', 'Javier', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Brgy. Syete, Granja, Lipa City, Batangas', 'Male', 'Married', '1978-07-26', 'Marawoy, Lipa City, Batangas', '148', '50', '09124563411', 'berniejavier@gmail.com', 'Roman Catholic', 'Driver', '1027', 0, 'Registered', '2026-05-13 02:27:09'),
(8, 'Alan', 'Loslo', 'Resaba', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Brgy. Syete, Granja, Lipa City, Batangas', 'Male', 'Married', '2001-12-12', 'Lipa City', '148', '48', '09123456789', 'alan@gmail.com', '89', '9', '1028', 0, 'Registered', '2026-05-13 04:59:54'),
(9, 'Jessica Marie', 'Loslo', 'Resaba', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Brgy. Syete, Granja, Lipa City, Batangas', 'Female', 'Single', '2008-12-12', 'Lipa City', '160', '50', '09124563411', 'johnbenedictjavier15@gmail.com', 'Roman Catholic', 'Student', '1029', 0, 'Registered', '2026-05-14 12:18:59'),
(10, 'Prince', 'Berde', 'Dimaculangan', 'Home Owner', 'Brgy. Syete, Granja, Lipa City, Batangas', '', 'Granja Lipa City', 'Male', 'Single', '2009-12-12', 'Lipa City', '160', '48', '09124563411', 'johnbenedictjavier15@gmail.com', 'Roman Catholic', 'Student', '1030', 0, 'Registered', '2026-05-18 12:02:06'),
(11, 'Olarte', 'Berde', 'Dimaculangan', 'Home Owner', 'Granja Lipa City', '', 'Granja Lipa City', 'Male', 'Single', '2009-12-12', 'Lipa City', '160', '48', '09124563411', 'johnbenedictjavier15@gmail.com', 'Roman Catholic', 'Student', '1030', 0, 'Not Registered', '2026-05-18 12:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `resident_education`
--

CREATE TABLE `resident_education` (
  `edu_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_education`
--

INSERT INTO `resident_education` (`edu_id`, `resident_id`, `level`, `school_name`, `school_address`) VALUES
(62, 7, 'Elementary', 'Inosloban INHS', 'Inosloban, Lipa City'),
(63, 7, 'Highschool', 'Inosloban Marawoy INHS', 'Inosloban, Lipa City'),
(64, 8, 'Elementary', 'JKOMES', '67'),
(65, 8, 'Highschool', '78', '78'),
(66, 8, 'Vocational', '768', '7'),
(67, 8, 'College', '87678', '76'),
(72, 6, 'Elementary', 'JKOMES', 'Antipolo'),
(73, 6, 'Highschool', 'Pinagkawitan', 'Pinagkawitan'),
(74, 6, 'Vocational', 'STI College Lipa', 'Lipa'),
(75, 6, 'College', 'Lipa City Colleges', 'Lipa'),
(76, 9, 'Elementary', 'ewq', 'qwe'),
(77, 9, 'Highschool', 'eqw', 'ewq'),
(78, 9, 'Vocational', 'qweq', 'eqwq'),
(79, 9, 'College', 'ewq', 'wqwe'),
(80, 5, 'Elementary', 'JKOMES', 'Lipa'),
(81, 5, 'Highschool', 'new', 'k'),
(82, 5, 'Vocational', 'e', 'e'),
(83, 5, 'College', 'e', 'e'),
(84, 11, 'Elementary', 'JKOMES', '123');

-- --------------------------------------------------------

--
-- Table structure for table `resident_employment`
--

CREATE TABLE `resident_employment` (
  `emp_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `employer_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_employment`
--

INSERT INTO `resident_employment` (`emp_id`, `resident_id`, `duration`, `company`, `employer_address`) VALUES
(26, 8, '87', '8', '998'),
(30, 6, '12', 'Amiya Rosa', 'Sampaguita'),
(31, 6, '5', 'Jolibee', 'Lipa'),
(32, 6, '12', 'MCDO', 'Lipa'),
(33, 9, '12', 'we', 'ewd'),
(34, 9, '2312', 'dwe', 'deq'),
(35, 5, '12', 'dhwefh', 'we'),
(37, 11, '2312', 'dwe', 'deq'),
(38, 10, '2312', 'dwe', 'deq');

-- --------------------------------------------------------

--
-- Table structure for table `resident_occupants`
--

CREATE TABLE `resident_occupants` (
  `occ_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `position_in_family` varchar(100) DEFAULT NULL,
  `gender` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` int(25) NOT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_occupants`
--

INSERT INTO `resident_occupants` (`occ_id`, `resident_id`, `full_name`, `position_in_family`, `gender`, `age`, `birthdate`, `contact_number`, `civil_status`, `occupation`) VALUES
(33, 7, 'Javier, Maylin Mitra', 'Wife', 'Female', 50, '1976-01-10', 0, 'Married', 'Housewife'),
(34, 8, 'Resaba, Emely Loslo', 'Wife', 'Female', 27, '1998-12-12', 0, 'Married', 'Housewife'),
(35, 8, 'Resaba, Elma Loslo', 'Daughter', 'Female', 21, '2004-12-12', 0, 'Single', 'Student'),
(36, 8, 'Resaba, Evelyn Loslo', 'Daughter', 'Female', 19, '2006-12-12', 0, 'Single', 'Student'),
(37, 8, 'Resaba, Neriza Loslo', 'Daughter', 'Female', 18, '2007-12-12', 0, 'Single', 'Student'),
(38, 8, 'Resaba, Mary Grace Loslo', 'Daughter', 'Female', 15, '2010-12-12', 0, 'Single', 'Student'),
(41, 6, 'Resaba, Jessica Marie Loslo', 'Friend', 'Female', 47, '1978-11-11', 0, 'Single', 'Student'),
(42, 6, 'Abrenica, Aerol Justine Macuha', 'Friend', 'Male', 24, '2001-12-12', 0, 'Single', 'Student'),
(43, 9, 'Honeral, Jason Madia', 'Live In', 'Male', 21, '2004-12-12', 0, 'Single', 'Student'),
(44, 5, 'Javier, Rose Ann Dela Cruz', 'Wife', 'Female', 15, '2010-07-23', 0, 'Married', 'Housewife'),
(45, 5, 'Javier, Lemon Dela Cruz', 'Son', 'Male', 13, '2012-12-12', 0, 'Single', 'Student'),
(46, 5, 'Javier, Cookie Dela Cruz', 'Son', 'Male', 13, '2012-12-12', 0, 'Single', 'Student'),
(48, 11, 'Dimaculangan, Benedict Agustin', 'Siblings', 'Male', 21, '2004-12-12', 0, 'Single', 'Student'),
(49, 10, 'Dimaculangan, Ara Agustin', 'Siblings', 'Male', 21, '2004-12-12', 0, '', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','resident') DEFAULT 'resident',
  `resident_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `resident_id`) VALUES
(1, 'benedict', 'ben123', 'resident', 1),
(2, 'admin', 'admin', 'admin', 1),
(6, 'newaccount', 'newaccount', 'resident', 5),
(7, 'Loste', 'loste', 'resident', 6),
(8, 'bernie', 'bernie', 'resident', 7),
(9, 'alan@gmail.com', 'alan', 'resident', 8),
(10, 'jessica', 'jessica', 'resident', 9),
(11, 'Dimaculangan', 'dimacu', 'resident', 10),
(12, 'olarte123', 'olarte', 'resident', 11);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `announcement_likes`
--
ALTER TABLE `announcement_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `announcement_id` (`announcement_id`,`user_id`);

--
-- Indexes for table `establishments`
--
ALTER TABLE `establishments`
  ADD PRIMARY KEY (`establishment_id`);

--
-- Indexes for table `establishment_requests`
--
ALTER TABLE `establishment_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`);

--
-- Indexes for table `resident_education`
--
ALTER TABLE `resident_education`
  ADD PRIMARY KEY (`edu_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `resident_employment`
--
ALTER TABLE `resident_employment`
  ADD PRIMARY KEY (`emp_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `resident_occupants`
--
ALTER TABLE `resident_occupants`
  ADD PRIMARY KEY (`occ_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `resident_id` (`resident_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `announcement_likes`
--
ALTER TABLE `announcement_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `establishments`
--
ALTER TABLE `establishments`
  MODIFY `establishment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `establishment_requests`
--
ALTER TABLE `establishment_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `resident_education`
--
ALTER TABLE `resident_education`
  MODIFY `edu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `resident_employment`
--
ALTER TABLE `resident_employment`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `resident_occupants`
--
ALTER TABLE `resident_occupants`
  MODIFY `occ_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `establishment_requests`
--
ALTER TABLE `establishment_requests`
  ADD CONSTRAINT `establishment_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_education`
--
ALTER TABLE `resident_education`
  ADD CONSTRAINT `resident_education_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_employment`
--
ALTER TABLE `resident_employment`
  ADD CONSTRAINT `resident_employment_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

--
-- Constraints for table `resident_occupants`
--
ALTER TABLE `resident_occupants`
  ADD CONSTRAINT `resident_occupants_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

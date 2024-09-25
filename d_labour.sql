-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2024 at 03:50 PM
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
-- Database: `d_labour`
--

-- --------------------------------------------------------

--
-- Table structure for table `hires`
--

CREATE TABLE `hires` (
  `hire_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `hire_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hires`
--

INSERT INTO `hires` (`hire_id`, `client_id`, `labour_id`, `hire_date`, `status`) VALUES
(12, 22, 19, '2024-09-23 19:54:48', 'active'),
(14, 15, 7, '2024-09-24 18:06:04', 'active'),
(16, 15, 19, '2024-09-24 23:02:33', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `job_post_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`application_id`, `labour_id`, `job_post_id`, `status`, `applied_at`) VALUES
(1, 7, 10, 'pending', '2024-09-23 21:02:10'),
(2, 7, 14, 'pending', '2024-09-23 21:18:38'),
(3, 7, 24, 'pending', '2024-09-24 10:09:26'),
(4, 7, 26, 'pending', '2024-09-24 10:09:37'),
(5, 7, 31, 'pending', '2024-09-24 10:09:49'),
(6, 19, 16, 'pending', '2024-09-24 18:38:16'),
(7, 19, 30, 'pending', '2024-09-24 20:15:02'),
(8, 19, 31, 'pending', '2024-09-24 20:15:10'),
(9, 6, 32, 'pending', '2024-09-24 23:47:53'),
(10, 6, 31, 'pending', '2024-09-24 23:48:01'),
(11, 16, 25, 'pending', '2024-09-24 23:51:43'),
(12, 16, 24, 'pending', '2024-09-24 23:51:50'),
(13, 16, 26, 'pending', '2024-09-24 23:51:53'),
(14, 16, 32, 'pending', '2024-09-24 23:52:07'),
(15, 16, 31, 'pending', '2024-09-24 23:52:11');

-- --------------------------------------------------------

--
-- Table structure for table `job_post`
--

CREATE TABLE `job_post` (
  `post_ID` int(11) NOT NULL,
  `jobTitle` varchar(20) NOT NULL,
  `salary` int(11) NOT NULL,
  `detail` varchar(60) NOT NULL,
  `city` varchar(20) NOT NULL,
  `location` varchar(30) NOT NULL,
  `createdDate` int(11) NOT NULL,
  `impath` varchar(200) NOT NULL,
  `owner` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_post`
--

INSERT INTO `job_post` (`post_ID`, `jobTitle`, `salary`, `detail`, `city`, `location`, `createdDate`, `impath`, `owner`) VALUES
(10, 'Carpenter', 1200, 'Seeking a skilled carpenter for furniture repair and custom ', 'Indore', 'M-22 , Bhavarkua ,Indore', 0, '../Shared/images/download (4).jpeg', 9),
(14, 'Labour', 4844, 'Need three labour for lifting construction work nvnoewe ngno', 'Indore', 'M-22 , Bhavarkua ,Indore', 0, '../Shared/images/download (5).jpeg', 6),
(15, 'Cleaner', 4555, 'fmdobmkoefmkom komkovmkobmkoerkbk mkjgnerkjrg ekm km kj gnke', 'Indore', 'M-22 , Bhavarkua ,Indore', 0, '../Shared/images/download (5).jpeg', 6),
(16, 'Sweeper', 3300, 'gmom km gkle mkemk mk ekg kg ekg ekg ek rkgerkjgnerkjg nerk ', 'Bhopal', 'near bhopal chowk', 0, '../Shared/images/download (5).jpeg', 10),
(18, 'Plumber', 55000, 'Whole house work including all detail ', 'Indore', 'M-22 , Bhavarkua ,Indore', 0, '../Shared/images/download (3).jpeg', 13),
(21, 'Carpenter', 250, 'fnewi iewi ijewng jerwn gk wn ewn jnhgjern gjtrnherjagn trjt', 'Indore', 'Vallabh nagar ,Indore', 0, '../Shared/images/download (4).jpeg', 18),
(24, 'Labour', 4844, 'Need three labour for lifting construction work nvnoewe ngno', 'Indore', 'M-22 , Bhavarkua ,Indore', 0, '../Shared/images/download (5).jpeg', 6),
(25, 'Cleaner', 4555, 'fmdobmkoefmkom komkovmkobmkoerkbk mkjgnerkjrg ekm km kj gnke', 'Dewas', 'Dewas', 0, '../Shared/images/download (5).jpeg', 6),
(26, 'Sweeper', 3300, 'gmom km gkle mkemk mk ekg kg ekg ekg ek rkgerkjgnerkjg nerk ', 'Dewas', 'Dewas', 0, '../Shared/images/download (5).jpeg', 10),
(27, 'Carpenter', 250, 'fnewi iewi ijewng jerwn gk wn ewn jnhgjern gjtrnherjagn trjt', 'Indore', 'Vallabh nagar ,Indore', 0, '../Shared/images/download (4).jpeg', 18),
(30, 'Carpenter', 5400, 'A Proffessional team of $ carpenter is needed in three day f', 'Ujjain', 'Ramghar ,Mahakaleshwar mandir', 0, '../Shared/images/Screenshot 2023-12-30 142647.png', 15),
(31, 'Editor', 5000, 'A Fine Editor is needed for my youtube Channel past work is ', 'Indore', 'M-22 , Geeta Bhavan ,Indore', 0, '../Shared/images/Screenshot 2024-02-03 190743.png', 22),
(32, 'Social Medial Manage', 45000, 'We are seeking a creative Social Media Manager to develop an', 'Indore', 'Youth Congress ,Geeta Bhavan', 0, '../Shared/images/mediamanager.jpeg', 24);

-- --------------------------------------------------------

--
-- Table structure for table `lab_post`
--

CREATE TABLE `lab_post` (
  `l_post_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `workType` varchar(20) NOT NULL,
  `experience` varchar(11) NOT NULL,
  `salary` varchar(10) NOT NULL,
  `location` varchar(30) NOT NULL,
  `city` varchar(30) NOT NULL,
  `impath` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_post`
--

INSERT INTO `lab_post` (`l_post_ID`, `user_ID`, `workType`, `experience`, `salary`, `location`, `city`, `impath`) VALUES
(11, 19, 'painter', '60 year', '4020', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesScreenshot 2024-09-08 220143.png'),
(17, 6, 'carpenter', '10 Year', '5000', 'Mata ji ki tekri ,Vali gali', 'painter', '../Shared/images/L_imagesdownload (4).jpeg'),
(19, 6, 'painter', '60 year', '5555', 'M-22 , Bhavarkua ,Indore', 'bhopal', '../Shared/images/L_imagesScreenshot 2023-12-31 163522.png'),
(21, 7, 'mason', '10 Year', '5555', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesmason.jpeg'),
(22, 19, 'carpenter', '75', '450', 'Geeta bhavan ,Indore', 'indore', '../Shared/images/L_imagescarpenter.jpg'),
(23, 16, 'painter', '10 Year', '3300', 'Bhpali nagar ,near bhopal chow', 'bhopal', '../Shared/images/L_imagespainter.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `labour_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `review` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_ID` int(11) NOT NULL,
  `user_name` varchar(20) NOT NULL,
  `email_id` varchar(30) NOT NULL,
  `password` varchar(100) NOT NULL,
  `mobile_no` bigint(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_type` varchar(10) NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_ID`, `user_name`, `email_id`, `password`, `mobile_no`, `date_created`, `user_type`) VALUES
(6, 'Prakhar Gupta', 'par07ta@gruail.com', '123456', 6263610238, '2024-09-09 15:58:38', 'Labour'),
(7, 'Ramdev', 'ramdev123@gmail.com', '1234', 1234567890, '2024-08-27 16:12:38', 'Labour'),
(9, 'Pankaj Dhakad', 'parman@gmail.com', '1234', 1111111111, '2024-09-01 12:00:38', 'User'),
(10, 'Lakhan jadav', 'devraj2@gmail.com', '1234', 6263610000, '2024-09-02 06:56:19', 'User'),
(13, 'arun parmar', 'arunp456@gmail.com', 'Tarun*123', 9993297301, '2024-09-03 08:53:00', 'User'),
(15, 'Priyanka ', '123@gmail.com', '123456', 1234567891, '2024-09-03 09:14:50', 'User'),
(16, 'Mohan', 'fmdfbmdkf@gmail.com', '123456', 6263610233, '2024-09-09 06:42:35', 'Labour'),
(18, 'Shulabh', '12@gmail.com', '1234', 1231231234, '2024-09-10 09:32:07', 'User'),
(19, 'Narendra Modi', 'bjp2024@gmail.com', '1234', 4204204200, '2024-09-10 18:50:09', 'Labour'),
(21, 'prateek', 'prateek@gmail.com', '1', 1234567866, '2024-09-11 11:15:03', 'User'),
(22, 'Mahendra Khelwal', '123@gmail.comm', '1234', 1472583690, '2024-09-20 19:53:05', 'User'),
(23, 'rawaaan', 'lanka@gmail.com', '123123', 1231231231, '2024-09-20 22:48:53', 'User'),
(24, 'Rahul Gandhi', 'congress@2029', '123456', 9999988888, '2024-09-24 23:40:53', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hires`
--
ALTER TABLE `hires`
  ADD PRIMARY KEY (`hire_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `labour_id` (`labour_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `labour_id` (`labour_id`),
  ADD KEY `job_post_id` (`job_post_id`);

--
-- Indexes for table `job_post`
--
ALTER TABLE `job_post`
  ADD PRIMARY KEY (`post_ID`),
  ADD KEY `fk_user_job_post` (`owner`);

--
-- Indexes for table `lab_post`
--
ALTER TABLE `lab_post`
  ADD PRIMARY KEY (`l_post_ID`),
  ADD KEY `fk_user_lab_post` (`user_ID`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `labour_id` (`labour_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `email_id` (`email_id`),
  ADD UNIQUE KEY `mobile_no` (`mobile_no`),
  ADD UNIQUE KEY `User_ID` (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hires`
--
ALTER TABLE `hires`
  MODIFY `hire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `job_post`
--
ALTER TABLE `job_post`
  MODIFY `post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `lab_post`
--
ALTER TABLE `lab_post`
  MODIFY `l_post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hires`
--
ALTER TABLE `hires`
  ADD CONSTRAINT `hires_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `user` (`User_ID`),
  ADD CONSTRAINT `hires_ibfk_2` FOREIGN KEY (`labour_id`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`labour_id`) REFERENCES `user` (`User_ID`),
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`job_post_id`) REFERENCES `job_post` (`post_ID`);

--
-- Constraints for table `job_post`
--
ALTER TABLE `job_post`
  ADD CONSTRAINT `fk_user_job_post` FOREIGN KEY (`owner`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lab_post`
--
ALTER TABLE `lab_post`
  ADD CONSTRAINT `fk_user_lab_post` FOREIGN KEY (`user_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`labour_id`) REFERENCES `lab_post` (`user_ID`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

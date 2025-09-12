-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2024 at 01:43 AM
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
(19, 15, 19, '2024-10-08 20:02:40', 'active');

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
(15, 16, 31, 'pending', '2024-09-24 23:52:11'),
(16, 6, 25, 'pending', '2024-09-27 06:31:16'),
(17, 6, 10, 'pending', '2024-09-27 06:55:41'),
(18, 6, 14, 'pending', '2024-10-08 18:51:37');

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
(22, 19, 'carpenter', '75', '450', 'Geeta bhavan ,Indore', 'indore', '../Shared/images/L_imagescarpenter.jpg'),
(23, 16, 'painter', '10 Year', '3300', 'Bhpali nagar ,near bhopal chow', 'bhopal', '../Shared/images/L_imagespainter.jpeg'),
(24, 7, 'mason', '10 Year', '5500', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesL_imagesmason.jpeg'),
(40, 41, 'Plumber', '3', '3500', 'Maharana Pratap Nagar, Bhopal', 'Bhopal', '../Shared/images/L_images/plumber.jpg'),
(41, 42, 'Mason', '5', '4000', 'Vijay Nagar, Indore', 'Indore', '../Shared/images/L_images/mason.jpg'),
(42, 43, 'Carpenter', '2', '3700', 'Freeganj, Ujjain', 'Ujjain', '../Shared/images/L_images/carpenter.jpg'),
(43, 44, 'Electrician', '4', '4500', 'Subhash Nagar, Kota', 'Kota', '../Shared/images/L_images/electrician.jpg'),
(44, 45, 'Painter', '6', '4200', 'Civil Lines, Jaipur', 'Jaipur', '../Shared/images/L_images/painter.jpg'),
(45, 46, 'Welder', '3', '3800', 'Ranital, Jabalpur', 'Jabalpur', '../Shared/images/L_images/welder.jpg'),
(46, 47, 'Plumber', '5', '3600', 'Govindpuri, Delhi', 'Delhi', '../Shared/images/L_images/plumber.jpg'),
(47, 48, 'Mason', '2', '4000', 'Old City, Bhopal', 'Bhopal', '../Shared/images/L_images/mason.jpg'),
(48, 49, 'Carpenter', '6', '3700', 'Rajwada, Indore', 'Indore', '../Shared/images/L_images/carpenter.jpg'),
(49, 50, 'Electrician', '3', '4500', 'Nanakheda, Ujjain', 'Ujjain', '../Shared/images/L_images/electrician.jpg'),
(50, 51, 'Painter', '4', '4200', 'Dadabari, Kota', 'Kota', '../Shared/images/L_images/painter.jpg'),
(51, 52, 'Welder', '5', '3800', 'Vaishali Nagar, Jaipur', 'Jaipur', '../Shared/images/L_images/welder.jpg'),
(52, 53, 'Plumber', '2', '3500', 'Madan Mahal, Jabalpur', 'Jabalpur', '../Shared/images/L_images/plumber.jpg'),
(53, 54, 'Mason', '4', '4000', 'Connaught Place, Delhi', 'Delhi', '../Shared/images/L_images/mason.jpg'),
(54, 55, 'Carpenter', '3', '3700', 'Ashoka Garden, Bhopal', 'Bhopal', '../Shared/images/L_images/carpenter.jpg'),
(55, 56, 'Electrician', '6', '4500', 'Palasia, Indore', 'Indore', '../Shared/images/L_images/electrician.jpg'),
(56, 57, 'Painter', '2', '4200', 'Vikram Nagar, Ujjain', 'Ujjain', '../Shared/images/L_images/painter.jpg'),
(57, 58, 'Welder', '5', '3800', 'Kota Junction, Kota', 'Kota', '../Shared/images/L_images/welder.jpg'),
(58, 59, 'Plumber', '3', '3500', 'MI Road, Jaipur', 'Jaipur', '../Shared/images/L_images/plumber.jpg'),
(59, 60, 'Mason', '4', '4000', 'Wright Town, Jabalpur', 'Jabalpur', '../Shared/images/L_images/mason.jpg'),
(60, 61, 'Carpenter', '6', '3700', 'Karol Bagh, Delhi', 'Delhi', '../Shared/images/L_images/carpenter.jpg'),
(61, 62, 'Electrician', '2', '4500', 'MP Nagar, Bhopal', 'Bhopal', '../Shared/images/L_images/electrician.jpg'),
(62, 63, 'Painter', '5', '4200', 'Vijay Nagar, Indore', 'Indore', '../Shared/images/L_images/painter.jpg'),
(63, 64, 'Welder', '3', '3800', 'Dewas Road, Ujjain', 'Ujjain', '../Shared/images/L_images/welder.jpg'),
(64, 65, 'Plumber', '4', '3500', 'Chambal Garden, Kota', 'Kota', '../Shared/images/L_images/plumber.jpg'),
(65, 66, 'Mason', '2', '4000', 'C-Scheme, Jaipur', 'Jaipur', '../Shared/images/L_images/mason.jpg'),
(66, 67, 'Carpenter', '3', '3700', 'Napier Town, Jabalpur', 'Jabalpur', '../Shared/images/L_images/carpenter.jpg'),
(67, 68, 'Electrician', '6', '4500', 'Laxmi Nagar, Delhi', 'Delhi', '../Shared/images/L_images/electrician.jpg'),
(68, 69, 'Painter', '2', '4200', 'Arera Colony, Bhopal', 'Bhopal', '../Shared/images/L_images/painter.jpg'),
(69, 70, 'Welder', '4', '3800', 'MIG Colony, Indore', 'Indore', '../Shared/images/L_images/welder.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `hire_id` int(11) NOT NULL,
  `labour_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `hire_id`, `labour_id`, `client_id`, `rating`, `review`, `created_at`) VALUES
(8, 14, 7, 15, 4, 'very good ', '2024-10-08 22:13:56'),
(9, 19, 19, 15, 4, 'dewrv erg er', '2024-10-08 22:14:29'),
(10, 14, 7, 15, 2, 'inijn', '2024-10-08 22:15:28');

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
(24, 'Rahul Gandhi', 'congress@2029', '123456', 9999988888, '2024-09-24 23:40:53', 'User'),
(41, 'Mohan Sharma', 'mohan.sharma@gmail.com', 'p@ssw0rd', 6263610299, '2024-09-09 01:12:35', 'Labour'),
(42, 'Rajesh Kumar', 'rajesh.kumar@gmail.com', 'password1', 6263610234, '2024-09-09 02:12:35', 'Labour'),
(43, 'Suresh Yadav', 'suresh.yadav@gmail.com', 'securepass', 6263610235, '2024-09-09 03:12:35', 'Labour'),
(44, 'Vikram Singh', 'vikram.singh@gmail.com', 'vikram123', 6263610236, '2024-09-09 04:12:35', 'Labour'),
(45, 'Manoj Verma', 'manoj.verma@gmail.com', 'vermapass', 6263610237, '2024-09-09 05:12:35', 'Labour'),
(46, 'Ravi Gupta', 'ravi.gupta@gmail.com', 'ravigupta', 6263610298, '2024-09-09 06:12:35', 'Labour'),
(47, 'Dinesh Kumar', 'dinesh.kumar@gmail.com', 'dineshpass', 6263610239, '2024-09-09 07:12:35', 'Labour'),
(48, 'Sanjay Patel', 'sanjay.patel@gmail.com', 'sanjay456', 6263610240, '2024-09-09 08:12:35', 'Labour'),
(49, 'Arvind Choudhary', 'arvind.choudhary@gmail.com', 'choudhary7', 6263610241, '2024-09-09 09:12:35', 'Labour'),
(50, 'Mukesh Tiwari', 'mukesh.tiwari@gmail.com', 'tiwari789', 6263610242, '2024-09-09 10:12:35', 'Labour'),
(51, 'Sunil Yadav', 'sunil.yadav@gmail.com', 'yadavsecure', 6263610243, '2024-09-09 11:12:35', 'Labour'),
(52, 'Naresh Sharma', 'naresh.sharma@gmail.com', 'nareshpass', 6263610244, '2024-09-09 12:12:35', 'Labour'),
(53, 'Ashok Mehta', 'ashok.mehta@gmail.com', 'mehtaPass6', 6263610245, '2024-09-09 13:12:35', 'Labour'),
(54, 'Gopal Thakur', 'gopal.thakur@gmail.com', 'thakur@99', 6263610246, '2024-09-09 14:12:35', 'Labour'),
(55, 'Mahesh Chauhan', 'mahesh.chauhan@gmail.com', 'chauhan76', 6263610247, '2024-09-09 15:12:35', 'Labour'),
(56, 'Ramesh Patel', 'ramesh.patel@gmail.com', 'patel@123', 6263610248, '2024-09-09 16:12:35', 'Labour'),
(57, 'Ajay Singh', 'ajay.singh@gmail.com', 'singh!234', 6263610249, '2024-09-09 17:12:35', 'Labour'),
(58, 'Kiran Chauhan', 'kiran.chauhan@gmail.com', 'chauhan67', 6263610250, '2024-09-09 18:12:35', 'Labour'),
(59, 'Rohit Yadav', 'rohit.yadav@gmail.com', 'rohitPass6', 6263610251, '2024-09-09 19:12:35', 'Labour'),
(60, 'Anil Kumar', 'anil.kumar@gmail.com', 'kumar1234', 6263610252, '2024-09-09 20:12:35', 'Labour'),
(61, 'Vijay Patel', 'vijay.patel@gmail.com', 'patelPass7', 6263610253, '2024-09-09 21:12:35', 'Labour'),
(62, 'Kishore Gupta', 'kishore.gupta@gmail.com', 'guptaSecure', 6263610254, '2024-09-09 22:12:35', 'Labour'),
(63, 'Pankaj Yadav', 'pankaj.yadav@gmail.com', 'yadav9999', 6263610255, '2024-09-09 23:12:35', 'Labour'),
(64, 'Narendra Mehta', 'narendra.mehta@gmail.com', 'mehta@99', 6263610256, '2024-09-10 00:12:35', 'Labour'),
(65, 'Deepak Sharma', 'deepak.sharma@gmail.com', 'deepak!123', 6263610257, '2024-09-10 01:12:35', 'Labour'),
(66, 'Santosh Kumar', 'santosh.kumar@gmail.com', 'kumar5678', 6263610258, '2024-09-10 02:12:35', 'Labour'),
(67, 'Harish Verma', 'harish.verma@gmail.com', 'verma4321', 6263610259, '2024-09-10 03:12:35', 'Labour'),
(68, 'Vinod Sharma', 'vinod.sharma@gmail.com', 'vinod2345', 6263610260, '2024-09-10 04:12:35', 'Labour'),
(69, 'Balbir Singh', 'balbir.singh@gmail.com', 'singh2024', 6263610261, '2024-09-10 05:12:35', 'Labour'),
(70, 'Suraj Verma', 'suraj.verma@gmail.com', 'vermaPass!', 6263610262, '2024-09-10 06:12:35', 'Labour');

-- --------------------------------------------------------

--
-- Table structure for table `work_posts`
--

CREATE TABLE `work_posts` (
  `work_post_id` int(11) NOT NULL,
  `labour_ID` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_posts`
--

INSERT INTO `work_posts` (`work_post_id`, `labour_ID`, `description`, `image`, `created_at`) VALUES
(4, 7, '**Title** : Carpenter for Custom Woodwork\r\n\r\nDescription:\r\nI am an experienced carpenter with over 5 years of hands-on work. I create custom furniture, cabinets, and outdoor structures. My services include making tailored furniture pieces, building shelves, and constructing decks. I ensure everything is done to high standards and on time. Let me help you bring your woodwork ideas to life!', 'carpenter.jpg', '2024-10-08 22:56:13'),
(5, 7, 'cdgkjp ogek g ekgm k bk nkj bnjn', 'skyline-oasis (1).png', '2024-10-08 23:08:30');

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
  ADD KEY `hire_id` (`hire_id`),
  ADD KEY `labour_id` (`labour_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `email_id` (`email_id`),
  ADD UNIQUE KEY `mobile_no` (`mobile_no`),
  ADD UNIQUE KEY `User_ID` (`user_ID`);

--
-- Indexes for table `work_posts`
--
ALTER TABLE `work_posts`
  ADD PRIMARY KEY (`work_post_id`),
  ADD KEY `labour_ID` (`labour_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hires`
--
ALTER TABLE `hires`
  MODIFY `hire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `job_post`
--
ALTER TABLE `job_post`
  MODIFY `post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `lab_post`
--
ALTER TABLE `lab_post`
  MODIFY `l_post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `work_posts`
--
ALTER TABLE `work_posts`
  MODIFY `work_post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`hire_id`) REFERENCES `hires` (`hire_id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`labour_id`) REFERENCES `user` (`User_ID`),
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `work_posts`
--
ALTER TABLE `work_posts`
  ADD CONSTRAINT `work_posts_ibfk_1` FOREIGN KEY (`labour_ID`) REFERENCES `user` (`User_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

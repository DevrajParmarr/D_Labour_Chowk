-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2024 at 01:17 AM
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
(28, 'Carpenter', 5500, 'abc abc abc abc .............vdvdvgg  grhtrhngfgf', 'Indore', 'M-2 , Bhavarkua ,Indore', 0, '../Shared/images/download (5).jpeg', 15),
(30, 'Carpenter', 5400, 'A Proffessional team of $ carpenter is needed in three day f', 'Ujjain', 'Ramghar ,Mahakaleshwar mandir', 0, '../Shared/images/Screenshot 2023-12-30 142647.png', 15);

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
(6, 17, 'Plumber', '5', '600', 'M-22 , Bhavarkua ,Indore', 'Indore', '../Shared/images/L_imagesdownload (4).jpeg'),
(7, 17, 'Carpenter', '10 Year', '600', 'M-22 , Bhavarkua ,Indore', 'Indore', '../Shared/images/L_imagesdownload (4).jpeg'),
(8, 12, 'indore', '5', '3300', '', 'Indorefdhd', '../Shared/images/L_images'),
(11, 19, 'painter', '60 year', '4020', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesScreenshot 2024-09-08 220143.png'),
(12, 17, 'Plumber', '5', '600', 'M-22 , Bhavarkua ,Indore', 'Indore', '../Shared/images/L_imagesdownload (4).jpeg'),
(13, 17, 'Carpenter', '10 Year', '600', 'M-22 , Bhavarkua ,Indore', 'Indore', '../Shared/images/L_imagesdownload (4).jpeg'),
(14, 12, 'indore', '5', '3300', '', 'Indorefdhd', '../Shared/images/L_images'),
(15, 19, 'painter', '60 year', '4020', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesScreenshot 2024-09-08 220143.png'),
(17, 6, 'carpenter', '10 Year', '5000', 'Mata ji ki tekri ,Vali gali', 'painter', '../Shared/images/L_imagesdownload (4).jpeg'),
(18, 6, 'electrician', '10 Year', '3300', 'M-22 , Bhavarkua ,Indore', 'indore', '../Shared/images/L_imagesdownload.jpeg'),
(19, 6, 'painter', '60 year', '5555', 'M-22 , Bhavarkua ,Indore', 'bhopal', '../Shared/images/L_imagesScreenshot 2023-12-31 163522.png'),
(20, 6, 'painter', '60 year', '5555', 'M-22 , Bhavarkua ,Indore', 'bhopal', '../Shared/images/L_imagesScreenshot 2023-12-31 163522.png');

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
(12, 'Mahi ', 'parsac07ta@gruail.com', '1234', 5555555555, '2024-09-03 08:17:58', 'Labour'),
(13, 'arun parmar', 'arunp456@gmail.com', 'Tarun*123', 9993297301, '2024-09-03 08:53:00', 'User'),
(15, 'Priyanka ', '123@gmail.com', '123456', 1234567891, '2024-09-03 09:14:50', 'User'),
(16, 'Mohan', 'fmdfbmdkf@gmail.com', '123456', 6263610233, '2024-09-09 06:42:35', 'Labour'),
(18, 'Shulabh', '12@gmail.com', '1234', 1231231234, '2024-09-10 09:32:07', 'User'),
(19, 'Narendra Modi', 'bjp2024@gmail.com', '1234', 4204204200, '2024-09-10 18:50:09', 'Labour'),
(21, 'prateek', 'prateek@gmail.com', '1', 1234567866, '2024-09-11 11:15:03', 'User'),
(22, 'Mahendra Khelwal', '123@gmail.comm', '1234', 1472583690, '2024-09-20 19:53:05', 'User'),
(23, 'rawaaan', 'lanka@gmail.com', '123123', 1231231231, '2024-09-20 22:48:53', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_post`
--
ALTER TABLE `job_post`
  ADD PRIMARY KEY (`post_ID`);

--
-- Indexes for table `lab_post`
--
ALTER TABLE `lab_post`
  ADD PRIMARY KEY (`l_post_ID`);

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
-- AUTO_INCREMENT for table `job_post`
--
ALTER TABLE `job_post`
  MODIFY `post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `lab_post`
--
ALTER TABLE `lab_post`
  MODIFY `l_post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

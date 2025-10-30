-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 01:33 PM
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
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `comID` int(11) NOT NULL,
  `comName` varchar(255) DEFAULT NULL,
  `comPerson` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`comID`, `comName`, `comPerson`) VALUES
(1, 'B CORP', 'Diane Cruz'),
(2, 'B CORP', 'Diane Cruz'),
(3, 'M CORP', 'LOPE');

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE `email` (
  `emailID` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email`
--

INSERT INTO `email` (`emailID`, `email`) VALUES
(3, 'karlpineda@gmail.com'),
(4, 'karlpineda@gmail.com'),
(14, 'karlouispineda@gmail.com'),
(15, 'karlouispineda@gmail.com'),
(16, 'dianecruz.0922@gmail.com'),
(17, 'dianecruz.0922@gmail.com'),
(18, 'karlouispineda@gmail.com'),
(19, 'kay@gmail.com'),
(20, 'lope@gmail.com'),
(21, 'justinia@gmail.com'),
(22, 'tyleryuzzen654@gmail.com'),
(23, 'adads@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `empID` int(11) NOT NULL,
  `empNum` varchar(255) NOT NULL,
  `empName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`empID`, `empNum`, `empName`) VALUES
(2, '23A32', 'Karl'),
(3, '23A32', 'karl'),
(13, '23A32', 'karl'),
(14, '23A32', 'karl'),
(15, '23A32', 'Karl Pineda'),
(16, '12A21', 'Kay'),
(17, 'IO23', 'JUSTINIA'),
(18, 'TY123', 'Tyler'),
(19, '2323', 'Aaron William Natividad');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `expiration` date DEFAULT NULL,
  `max_stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `category`, `quantity`, `price`, `expiration`, `max_stock`) VALUES
(88, 'duaptas', 'TabletW', 23, 23.00, '2025-11-15', 23),
(89, 'coviddd', 'asda', 25, 20.00, '2025-11-07', 125);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `max_stock` int(11) DEFAULT 0,
  `status` enum('In Stock','Low Stock','Out of Stock') DEFAULT 'In Stock',
  `location` varchar(100) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otpverify`
--

CREATE TABLE `otpverify` (
  `otpID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `emailID` int(11) NOT NULL,
  `otpCode` varchar(10) NOT NULL,
  `verification` tinyint(1) NOT NULL,
  `verifyDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otpverify`
--

INSERT INTO `otpverify` (`otpID`, `userID`, `emailID`, `otpCode`, `verification`, `verifyDate`) VALUES
(1, 11, 21, '9648859406', 0, '2025-10-19 09:56:59'),
(2, 12, 22, '1483998075', 1, '2025-10-19 09:59:38'),
(3, 12, 22, '0977617354', 1, '2025-10-19 10:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `price` decimal(12,2) DEFAULT 0.00,
  `expiration` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `category`, `quantity`, `price`, `expiration`) VALUES
(1, 'P001', 'Paracetamol 500mg', 'Medicine', 25, 10.50, '2025-12-01'),
(2, 'P002', 'Amoxicillin 250mg', 'Medicine', 5, 15.00, '2025-11-10'),
(3, 'P003', 'Vitamin C', 'Supplement', 20, 8.75, '2026-02-01'),
(4, 'P004', 'Cough Syrup', 'Medicine', 0, 55.00, '2025-09-01');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `requester` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `request_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `item_id`, `item_name`, `quantity`, `requester`, `status`, `request_date`) VALUES
(1, 1, 'Paracetamol 500mg', 2, 'Juan Dela Cruz', 'Pending', '2025-10-29 21:34:36'),
(2, 3, 'Vitamin C', 5, 'Juan Dela Cruz', 'Approved', '2025-10-29 21:34:36'),
(3, 2, 'Amoxicillin 250mg', 3, 'Juan Dela Cruz', 'Pending', '2025-10-29 21:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) DEFAULT NULL,
  `contact_info` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `type` enum('In','Out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userinfo`
--

CREATE TABLE `userinfo` (
  `infoID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `empID` int(11) DEFAULT NULL,
  `comID` int(11) DEFAULT NULL,
  `emailID` int(11) DEFAULT NULL,
  `cont_num` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userinfo`
--

INSERT INTO `userinfo` (`infoID`, `userID`, `empID`, `comID`, `emailID`, `cont_num`) VALUES
(7, 7, NULL, 2, 17, '09685699706'),
(8, 8, 15, NULL, 18, '09685699706'),
(9, 9, 16, NULL, 19, '09685699706'),
(10, 10, NULL, 3, 20, '09685699706'),
(11, 11, 17, NULL, 21, '09685699706'),
(12, 12, 18, NULL, 22, '09685699706'),
(13, 13, 19, NULL, 23, '0909092392');

-- --------------------------------------------------------

--
-- Table structure for table `userroles`
--

CREATE TABLE `userroles` (
  `roleID` int(11) NOT NULL,
  `roleName` enum('Admin','Employee','Supplier') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userroles`
--

INSERT INTO `userroles` (`roleID`, `roleName`) VALUES
(1, 'Admin'),
(2, 'Employee'),
(3, 'Supplier');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `roleID` int(11) NOT NULL,
  `dateCreated` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Active','Disabled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `username`, `password`, `roleID`, `dateCreated`, `status`) VALUES
(7, 'dianecruz.0922@gmail.com', 'Diane123', 3, '2025-10-19 08:52:40', 'Active'),
(8, 'karlouispineda@gmail.com', 'Karl123', 2, '2025-10-19 08:53:53', 'Active'),
(9, 'kay@gmail.com', 'Kay123', 2, '2025-10-19 09:14:17', 'Active'),
(10, 'lope@gmail.com', 'Lope133', 3, '2025-10-19 09:14:59', 'Active'),
(11, 'justinia@gmail.com', 'Justinia123', 2, '2025-10-19 09:56:44', 'Active'),
(12, 'tyleryuzzen654@gmail.com', 'Holut^543', 2, '2025-10-19 09:58:16', 'Active'),
(13, 'adads@gmail.com', '12345', 2, '2025-10-25 16:08:48', 'Active'),
(14, 'admin', 'admin123', 1, '2025-10-25 18:33:02', 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`comID`);

--
-- Indexes for table `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`emailID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`empID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `otpverify`
--
ALTER TABLE `otpverify`
  ADD PRIMARY KEY (`otpID`),
  ADD KEY `FK_user` (`userID`),
  ADD KEY `emailID` (`emailID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD PRIMARY KEY (`infoID`),
  ADD KEY `FK_users` (`userID`),
  ADD KEY `FK_employee` (`empID`),
  ADD KEY `FK_company` (`comID`),
  ADD KEY `FK_email` (`emailID`);

--
-- Indexes for table `userroles`
--
ALTER TABLE `userroles`
  ADD PRIMARY KEY (`roleID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD KEY `FK_role` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `comID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email`
--
ALTER TABLE `email`
  MODIFY `emailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `empID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otpverify`
--
ALTER TABLE `otpverify`
  MODIFY `otpID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userinfo`
--
ALTER TABLE `userinfo`
  MODIFY `infoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `userroles`
--
ALTER TABLE `userroles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `otpverify`
--
ALTER TABLE `otpverify`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `otpverify_ibfk_1` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD CONSTRAINT `FK_company` FOREIGN KEY (`comID`) REFERENCES `company` (`comID`),
  ADD CONSTRAINT `FK_email` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`),
  ADD CONSTRAINT `FK_employee` FOREIGN KEY (`empID`) REFERENCES `employee` (`empID`),
  ADD CONSTRAINT `FK_users` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_role` FOREIGN KEY (`roleID`) REFERENCES `userroles` (`roleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`CategoryID`, `Category_Name`) VALUES
(1, 'Analgesics / Antipyretics'),
(2, 'Antibiotics / Antibacterials'),
(3, 'Antifungals'),
(4, 'Antivirals'),
(5, 'Antihistamines / Antiallergics'),
(6, 'Antacids / Antiulcerants'),
(7, 'Antihypertensives'),
(8, 'Antidiabetics'),
(9, 'Vitamins / Supplements'),
(10, 'Respiratory Medicines'),
(11, 'Cardiovascular Drugs'),
(12, 'CNS Drugs (Neurologic / Psychiatric)'),
(13, 'Gastrointestinal Medicines'),
(14, 'Dermatologicals'),
(15, 'Eye / Ear Preparations'),
(16, 'Hormones / Endocrine'),
(17, 'Vaccines / Biologicals'),
(18, 'Emergency / First Aid'),
(19, 'OTC (Over-the-Counter)'),
(20, 'Controlled Drugs'),
(21, 'Medical Supplies');

CREATE TABLE `company` (
  `comID` int(11) NOT NULL,
  `comName` varchar(255) NOT NULL,
  `comPerson` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `email` (
  `emailID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `employee` (
  `empID` int(11) NOT NULL,
  `empNum` varchar(255) NOT NULL,
  `empName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `SKU` varchar(100) DEFAULT NULL,
  `BatchNum` varchar(255) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `ExpirationDate` date DEFAULT NULL,
  `Status` varchar(50) NOT NULL DEFAULT 'In Stock',
  `DateUpdated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `inventory` (`InventoryID`, `ProductID`, `SKU`, `BatchNum`, `Quantity`, `ExpirationDate`, `Status`, `DateUpdated`) VALUES
(20, 1, 'PARA-251112-688', 'PARA-25/12/1', 30, '2025-12-01', 'In Stock', '2025-11-21 22:03:58'),
(21, 2, 'AMOX-251112-955', 'AMOX-12/02/2025', 57, '2025-12-02', 'In Stock', '2025-11-21 22:04:27'),
(22, 3, 'CLOT-251112-255', 'CLOT-/01/01/26', 30, NULL, 'In Stock', '2025-11-12 16:33:34'),
(23, 4, 'IBUP-251120-925', 'IBUP-12/04/2025', 50, '2025-12-04', 'In Stock', '2025-11-20 13:49:08'),
(24, 2, 'AMOX-251120-256', '001', 34, '2026-04-23', 'In Stock', '2025-11-20 14:19:45'),
(25, 11, 'VALC-251120-870', '', 2, '2026-01-30', 'Low Stock', '2025-11-20 14:50:47'),
(26, 4, 'IBUP-251120-237', '001', 70, '2025-11-20', 'In Stock', '2025-11-20 22:50:23'),
(27, 6, 'DOXY-251120-266', '009', 80, '2025-12-06', 'In Stock', '2025-11-20 22:51:07'),
(28, 1, 'PARA-251121-687', '010', 1, '2026-03-12', 'Low Stock', '2025-11-22 04:01:46'),
(29, 18, 'PANT-251121-901', '', 0, '2025-11-27', 'Out of Stock', '2025-11-22 05:22:54');

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `header` varchar(255) NOT NULL,
  `preview` text DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `batch` varchar(100) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_sent` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `type` enum('message','lowstock') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notification_state` (
  `id` int(11) NOT NULL,
  `last_message_count` int(11) NOT NULL DEFAULT 0,
  `last_lowstock_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notification_state` (`id`, `last_message_count`, `last_lowstock_count`) VALUES
(1, 0, 2);

CREATE TABLE `otpverify` (
  `otpID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `emailID` int(11) NOT NULL,
  `otpCode` varchar(10) NOT NULL,
  `verification` tinyint(1) NOT NULL DEFAULT 0,
  `verifyDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pending_orders` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `order_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`order_data`)),
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `UnitID` int(11) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Min_stock` int(11) NOT NULL,
  `Max_stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`ProductID`, `ProductName`, `CategoryID`, `UnitID`, `Price`, `Min_stock`, `Max_stock`) VALUES
(1, 'Paracetamol 500mg', 1, 1, 3.00, 10, 100),
(2, 'Amoxicillin 500mg', 2, 2, 7.50, 10, 100),
(3, 'Clotrimazole 1% Cream', 3, 3, 75.00, 5, 30),
(4, 'Ibuprofren 200mg', 1, 1, 4.50, 10, 80),
(5, 'Mefenamic Acit 500mg', 1, 1, 6.00, 8, 60),
(6, 'Doxyxyline  100mg', 2, 2, 12.00, 8, 80),
(7, 'Cefalexin 500mg', 2, 2, 15.00, 8, 80),
(8, 'Ketoconazole 2% Cream', 3, 3, 90.00, 5, 25),
(9, 'Miconazole 2% Ointment', 3, 3, 82.00, 5, 20),
(10, 'Acyclovir 400mg', 4, 1, 18.00, 5, 40),
(11, 'Valcyclovir 500mg', 4, 1, 30.00, 4, 30),
(12, 'Oseltamivir 75mg', 4, 1, 27.00, 3, 20),
(13, 'Cetirizine 10mg', 5, 1, 6.00, 10, 100),
(14, 'loratadine 10mg', 5, 1, 6.00, 8, 80),
(15, 'Diphenhydramine 25mg', 5, 1, 5.52, 8, 60),
(16, 'Omeprazole 20mg', 6, 1, 10.50, 10, 100),
(17, 'Ranitidine 150mg', 6, 1, 9.00, 8, 80),
(18, 'Pantoprazole 40mg', 6, 1, 13.50, 8, 80);


CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `requester` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `request_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `requests` (`request_id`, `ProductID`, `quantity`, `requester`, `status`, `request_date`) VALUES
(5, 2, 1, 'Anonymous', 'Pending', '2025-11-20 14:20:00'),
(6, 3, 1, 'Anonymous', 'Pending', '2025-11-20 14:20:00'),
(7, 4, 1, 'Anonymous', 'Pending', '2025-11-20 14:20:00'),
(8, 2, 1, 'Anonymous', 'Pending', '2025-11-20 14:43:42');


CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_info` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `units` (
  `UnitID` int(11) NOT NULL,
  `UnitName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `units` (`UnitID`, `UnitName`) VALUES
(1, 'Tablet'),
(2, 'Capsule'),
(3, 'Tube'),
(4, 'Inhaler'),
(5, 'Bottle'),
(6, 'Vial'),
(7, 'Pack'),
(8, 'Box');

CREATE TABLE `userinfo` (
  `infoID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `empID` int(11) DEFAULT NULL,
  `comID` int(11) DEFAULT NULL,
  `emailID` int(11) NOT NULL,
  `cont_num` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `userroles` (
  `roleID` int(11) NOT NULL,
  `roleName` enum('Admin','Employee','Supplier') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `roleID` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Active','Disabled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`);

ALTER TABLE `company`
  ADD PRIMARY KEY (`comID`);

ALTER TABLE `email`
  ADD PRIMARY KEY (`emailID`);

ALTER TABLE `employee`
  ADD PRIMARY KEY (`empID`);

ALTER TABLE `inventory`
  ADD PRIMARY KEY (`InventoryID`),
  ADD UNIQUE KEY `uq_inventory_sku` (`SKU`),
  ADD KEY `idx_inventory_product` (`ProductID`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`);

ALTER TABLE `notification_state`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `otpverify`
  ADD PRIMARY KEY (`otpID`),
  ADD KEY `fk_otp_user` (`userID`),
  ADD KEY `fk_otp_email` (`emailID`);

ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `idx_products_category` (`CategoryID`),
  ADD KEY `fk_products_units` (`UnitID`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_requests_product` (`ProductID`);

ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

ALTER TABLE `units`
  ADD PRIMARY KEY (`UnitID`);

ALTER TABLE `userinfo`
  ADD PRIMARY KEY (`infoID`),
  ADD KEY `fk_info_user` (`userID`),
  ADD KEY `fk_info_emp` (`empID`),
  ADD KEY `fk_info_com` (`comID`),
  ADD KEY `fk_info_email` (`emailID`);

ALTER TABLE `userroles`
  ADD PRIMARY KEY (`roleID`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_users_role` (`roleID`);

ALTER TABLE `categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

ALTER TABLE `company`
  MODIFY `comID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email`
  MODIFY `emailID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `employee`
  MODIFY `empID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `inventory`
  MODIFY `InventoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `otpverify`
  MODIFY `otpID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pending_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `units`
  MODIFY `UnitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `userinfo`
  MODIFY `infoID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `userroles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `otpverify`
  ADD CONSTRAINT `fk_otp_email` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_units` FOREIGN KEY (`UnitID`) REFERENCES `units` (`UnitID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `requests`
  ADD CONSTRAINT `fk_requests_product` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `userinfo`
  ADD CONSTRAINT `fk_info_com` FOREIGN KEY (`comID`) REFERENCES `company` (`comID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_info_email` FOREIGN KEY (`emailID`) REFERENCES `email` (`emailID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_info_emp` FOREIGN KEY (`empID`) REFERENCES `employee` (`empID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_info_user` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`roleID`) REFERENCES `userroles` (`roleID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;


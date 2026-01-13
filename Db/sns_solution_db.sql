-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 01, 2026 at 08:31 AM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 5.6.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sns_solution_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `address` text,
  `contact_number` varchar(20) DEFAULT NULL,
  `alt_contact_number` varchar(20) DEFAULT NULL,
  `client_origin` enum('INHOUSE','OUTHOUSE') DEFAULT 'INHOUSE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `address`, `contact_number`, `alt_contact_number`, `client_origin`, `created_at`) VALUES
(1, 'Sun & Sun Jewellers', 'Sadar Bazar', '', '', 'INHOUSE', '2025-12-31 06:25:03'),
(2, 'Sharma Ayurvedic Pharmacy', 'Raipur', '', '', 'INHOUSE', '2025-12-31 07:27:07');

-- --------------------------------------------------------

--
-- Stand-in structure for view `client_view`
-- (See below for the actual view)
--
CREATE TABLE `client_view` (
`client_id` int(11)
,`client_name` varchar(150)
,`address` text
,`contact_number` varchar(20)
,`alt_contact_number` varchar(20)
,`client_origin` enum('INHOUSE','OUTHOUSE')
,`created_at` timestamp
,`no_of_project` bigint(21)
,`no_of_sm` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `service_type` varchar(10) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `billing_period` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `generated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `client_id`, `invoice_date`, `total_amount`, `service_type`, `ref_id`, `billing_period`, `amount`, `generated_date`) VALUES
(1, 'SNS/SM/014', 1, '2025-12-31', '10900.00', NULL, NULL, NULL, NULL, '2025-12-31 06:26:18'),
(2, 'SNSS/IH/P/001', 2, '2025-12-31', '10000.00', NULL, NULL, NULL, NULL, '2025-12-31 07:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`item_id`, `invoice_id`, `description`, `amount`, `qty`) VALUES
(60, 1, 'Social Media Management Charge', '7500.00', 1),
(61, 1, 'Gold Rate Video', '3400.00', 17),
(68, 2, 'SAP Billing Application', '10000.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `project_name` varchar(200) DEFAULT NULL,
  `project_type` varchar(50) DEFAULT NULL,
  `amc_base_amount` decimal(10,2) DEFAULT '0.00',
  `amc_amount` decimal(10,2) DEFAULT '0.00',
  `next_renewal_date` date DEFAULT NULL,
  `manager_name` varchar(100) DEFAULT NULL,
  `manager_contact_no` varchar(20) DEFAULT NULL,
  `tech_name` varchar(100) DEFAULT NULL,
  `current_version` varchar(20) DEFAULT '1.0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `client_id`, `project_name`, `project_type`, `amc_base_amount`, `amc_amount`, `next_renewal_date`, `manager_name`, `manager_contact_no`, `tech_name`, `current_version`) VALUES
(1, 2, 'SAP Billing Application', 'Web App', '10000.00', '0.00', '2026-12-20', 'Banty Bhaiya', '9981123100', 'Core PHP', '5.0');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `reminder_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `remark` text,
  `reminder_date` date NOT NULL,
  `reminder_type` enum('ONETIME','WEEKLY','MONTHLY','YEARLY') DEFAULT 'ONETIME',
  `status` enum('PENDING','COMPLETED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `service_type_tbl`
--

CREATE TABLE `service_type_tbl` (
  `id` int(11) NOT NULL,
  `service_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_type_tbl`
--

INSERT INTO `service_type_tbl` (`id`, `service_name`) VALUES
(1, 'Website'),
(2, 'Web App'),
(3, 'Mobile App'),
(4, 'Desktop App');

-- --------------------------------------------------------

--
-- Table structure for table `smm_services`
--

CREATE TABLE `smm_services` (
  `smm_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `base_charge` decimal(10,2) DEFAULT '0.00',
  `management_charge` decimal(10,2) DEFAULT NULL,
  `ad_budget_amount` decimal(10,2) DEFAULT '0.00',
  `ad_description` text,
  `post_quantity` int(11) DEFAULT NULL,
  `next_renewal_date` date DEFAULT NULL,
  `manager_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `smm_services`
--

INSERT INTO `smm_services` (`smm_id`, `client_id`, `base_charge`, `management_charge`, `ad_budget_amount`, `ad_description`, `post_quantity`, `next_renewal_date`, `manager_name`) VALUES
(1, 1, '7500.00', NULL, '0.00', '', NULL, '2026-01-31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$dmktwatlx/TMhNWvMSeO6.rJpEHhSkbOCbRbswI6DH/Kvy7QgUChK');

-- --------------------------------------------------------

--
-- Structure for view `client_view`
--
DROP TABLE IF EXISTS `client_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `client_view`  AS  select `c`.`client_id` AS `client_id`,`c`.`client_name` AS `client_name`,`c`.`address` AS `address`,`c`.`contact_number` AS `contact_number`,`c`.`alt_contact_number` AS `alt_contact_number`,`c`.`client_origin` AS `client_origin`,`c`.`created_at` AS `created_at`,(select count(0) from `projects` `p` where (`p`.`client_id` = `c`.`client_id`)) AS `no_of_project`,(select count(0) from `smm_services` `s` where (`s`.`client_id` = `c`.`client_id`)) AS `no_of_sm` from `clients` `c` order by `c`.`client_id` desc ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`reminder_id`);

--
-- Indexes for table `service_type_tbl`
--
ALTER TABLE `service_type_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smm_services`
--
ALTER TABLE `smm_services`
  ADD PRIMARY KEY (`smm_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_type_tbl`
--
ALTER TABLE `service_type_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `smm_services`
--
ALTER TABLE `smm_services`
  MODIFY `smm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `smm_services`
--
ALTER TABLE `smm_services`
  ADD CONSTRAINT `smm_services_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

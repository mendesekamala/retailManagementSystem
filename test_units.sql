-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 09:40 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_units`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `date_created` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `company_name`, `owner_name`, `date_created`) VALUES
(1, 'Chiades Co', 'mendes kamala', '2024-11-06'),
(2, 'kamala Co', 'kamala.mendes', '2024-11-09');

-- --------------------------------------------------------

--
-- Table structure for table `debt_payments`
--

CREATE TABLE `debt_payments` (
  `debt_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `due_amount` decimal(10,2) NOT NULL,
  `name` varchar(255) NOT NULL,
  `debtor_creditor` enum('debtor','creditor') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `debt_payments`
--

INSERT INTO `debt_payments` (`debt_id`, `company_id`, `created_by`, `transaction_id`, `total`, `due_amount`, `name`, `debtor_creditor`, `date_created`) VALUES
(1, 1, 2, 170, '70000.00', '70000.00', 'kamala', 'creditor', '2025-05-06 17:17:50'),
(2, 1, 2, 192, '37000.00', '37000.00', 'jay', 'debtor', '2025-05-16 18:01:53'),
(3, 1, 2, 193, '10000.00', '10000.00', 'jackie', 'debtor', '2025-05-16 19:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `methods_used`
--

CREATE TABLE `methods_used` (
  `meth_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `partial_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `methods_used`
--

INSERT INTO `methods_used` (`meth_id`, `transaction_id`, `payment_method`, `partial_amount`, `total_amount`) VALUES
(39, 106, 'cash', '200000.00', '200000.00'),
(42, 109, 'cash', '2500.00', '2500.00'),
(43, 110, 'cash', '92500.00', '92500.00'),
(44, 111, 'cash', '175000.00', '175000.00'),
(45, 113, 'cash', '32000.00', '32000.00'),
(46, 114, 'NMB', '40000.00', '40000.00'),
(47, 116, 'NMB', '350000.00', '350000.00'),
(48, 117, 'cash', '54000.00', '54000.00'),
(49, 118, 'cash', '32000.00', '32000.00'),
(50, 119, 'cash', '1500.00', '1500.00'),
(51, 120, 'cash', '40000.00', '40000.00'),
(52, 121, 'cash', '47000.00', '47000.00'),
(53, 122, 'NMB', '19200.00', '19200.00'),
(54, 123, 'cash', '3000.00', '3000.00'),
(55, 124, 'tigo pesa', '35000.00', '35000.00'),
(56, 125, 'cash', '31000.00', '31000.00'),
(57, 126, 'cash', '40000.00', '40000.00'),
(58, 127, 'cash', '500.00', '500.00'),
(59, 128, 'cash', '66000.00', '66000.00'),
(60, 129, 'cash', '33000.00', '33000.00'),
(61, 130, 'cash', '175000.00', '175000.00'),
(62, 131, 'cash', '40000.00', '40000.00'),
(63, 132, 'cash', '10000.00', '10000.00'),
(64, 133, 'tigo pesa', '15000.00', '15000.00'),
(65, 134, 'cash', '40000.00', '40000.00'),
(66, 135, 'mpesa', '31000.00', '31000.00'),
(67, 136, 'NMB', '100000.00', '100000.00'),
(68, 137, 'cash', '10000.00', '16000.00'),
(69, 137, 'mpesa', '6000.00', '16000.00'),
(70, 139, 'cash', '40000.00', '40000.00'),
(73, 142, 'cash', '10000.00', '10000.00'),
(74, 143, 'cash', '2000.00', '2000.00'),
(75, 144, 'cash', '1000.00', '2000.00'),
(76, 144, 'NMB', '1000.00', '2000.00'),
(77, 146, 'NMB', '80000.00', '80000.00'),
(78, 147, 'cash', '48000.00', '48000.00'),
(79, 148, 'NMB', '40000.00', '40000.00'),
(80, 149, 'NMB', '31000.00', '31000.00'),
(81, 150, 'NMB', '-31000.00', '31000.00'),
(82, 151, 'NMB', '300000.00', '300000.00'),
(83, 152, 'cash', '4800.00', '4800.00'),
(84, 153, 'cash', '48000.00', '48000.00'),
(85, 154, 'cash', '-48000.00', '48000.00'),
(86, 155, 'cash', '44000.00', '44000.00'),
(87, 156, 'cash', '-44000.00', '44000.00'),
(88, 157, 'cash', '105000.00', '105000.00'),
(89, 158, 'cash', '450000.00', '450000.00'),
(90, 160, 'tigo pesa', '144000.00', '144000.00'),
(91, 165, 'NMB', '500000.00', '500000.00'),
(92, 166, 'NMB', '420000.00', '420000.00'),
(93, 169, 'debt', '22500.00', '22500.00'),
(94, 171, 'cash', '91200.00', '91200.00'),
(95, 172, 'cash', '2500.00', '2500.00'),
(96, 173, 'cash', '32000.00', '32000.00'),
(97, 174, 'NMB', '40000.00', '40000.00'),
(98, 175, 'NMB', '40000.00', '40000.00'),
(99, 176, 'NMB', '-40000.00', '40000.00'),
(100, 177, 'NMB', '-40000.00', '40000.00'),
(101, 178, 'cash', '40000.00', '40000.00'),
(102, 179, 'cash', '50000.00', '50000.00'),
(103, 180, 'cash', '14800.00', '14800.00'),
(104, 181, 'cash', '60000.00', '60000.00'),
(105, 182, 'cash', '64000.00', '64000.00'),
(106, 183, 'NMB', '1000000.00', '1000000.00'),
(107, 184, 'NMB', '93000.00', '93000.00'),
(108, 185, 'cash', '48000.00', '48000.00'),
(109, 186, 'NMB', '280000.00', '280000.00'),
(110, 187, 'NMB', '12000.00', '12000.00'),
(111, 188, 'cash', '30000.00', '30000.00'),
(112, 189, 'cash', '15000.00', '15000.00'),
(113, 190, 'cash', '10000.00', '10000.00'),
(114, 191, 'cash', '42000.00', '42000.00'),
(115, 192, 'cash', '50000.00', '87000.00'),
(116, 193, 'cash', '30000.00', '40000.00');

-- --------------------------------------------------------

--
-- Table structure for table `money`
--

CREATE TABLE `money` (
  `money_id` int(11) NOT NULL,
  `company_id` int(10) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `cash` decimal(10,2) DEFAULT 0.00,
  `NMB` decimal(10,2) DEFAULT 0.00,
  `CRDB` decimal(10,2) DEFAULT 0.00,
  `NBC` decimal(10,2) DEFAULT 0.00,
  `mpesa` decimal(10,2) DEFAULT 0.00,
  `tigo_pesa` decimal(10,2) DEFAULT 0.00,
  `airtel_money` decimal(10,2) DEFAULT 0.00,
  `halo_pesa` decimal(10,2) DEFAULT 0.00,
  `azam_pesa` decimal(10,2) DEFAULT 0.00,
  `debt` decimal(10,2) DEFAULT 0.00,
  `creditors` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `money`
--

INSERT INTO `money` (`money_id`, `company_id`, `money`, `cash`, `NMB`, `CRDB`, `NBC`, `mpesa`, `tigo_pesa`, `airtel_money`, `halo_pesa`, `azam_pesa`, `debt`, `creditors`) VALUES
(1, 1, '0.00', '390300.00', '735400.00', '0.00', '0.00', '310000.00', '164000.00', '0.00', '0.00', '0.00', '26000.00', '0.00'),
(2, 2, '0.00', '383000.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `orderNo` varchar(255) DEFAULT NULL,
  `company_id` int(100) NOT NULL,
  `created_by` int(100) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `time` datetime DEFAULT current_timestamp(),
  `status` enum('created','sent','delivered','cancelled') DEFAULT 'created',
  `customer_name` varchar(255) NOT NULL,
  `profit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `orderNo`, `company_id`, `created_by`, `total`, `time`, `status`, `customer_name`, `profit`) VALUES
(162, '18/04/001', 1, 2, '32000.00', '2025-04-18 16:22:41', 'created', 'messi', '5000.00'),
(163, '18/04/002', 1, 2, '40000.00', '2025-04-18 16:26:35', 'created', 'mende', '5000.00'),
(164, '19/04/003', 1, 2, '32000.00', '2025-04-19 18:03:25', 'created', 'riri', '5000.00'),
(165, '19/04/004', 1, 2, '40000.00', '2025-04-19 18:31:43', 'created', 'lazaro', '5000.00'),
(166, '20/04/001', 1, 2, '47000.00', '2025-04-20 18:41:20', 'created', 'lavu', '5000.00'),
(167, '20/04/002', 1, 2, '19200.00', '2025-04-20 18:42:42', 'created', 'mapato', '2400.00'),
(168, '21/04/001', 1, 2, '31000.00', '2025-04-21 18:50:41', 'created', 'ovad', '3000.00'),
(169, '21/04/002', 1, 2, '40000.00', '2025-04-21 18:51:17', 'created', 'messi', '5000.00'),
(170, '22/04/001', 1, 2, '66000.00', '2025-04-22 18:57:46', 'created', 'kamala', '5100.00'),
(171, '22/04/002', 1, 2, '33000.00', '2025-04-22 18:58:40', 'created', 'paul', '3500.00'),
(172, '23/04/001', 1, 2, '40000.00', '2025-04-23 19:03:44', 'created', 'neymar', '5000.00'),
(173, '23/04/002', 1, 2, '10000.00', '2025-04-23 19:04:34', 'created', 'jay', '1250.00'),
(174, '24/04/001', 1, 2, '40000.00', '2025-04-24 19:13:39', 'created', 'hassan', '5000.00'),
(175, '24/04/002', 1, 2, '31000.00', '2025-04-24 19:15:38', 'created', 'faisal', '3000.00'),
(176, '25/04/001', 1, 2, '16000.00', '2025-04-25 22:30:10', 'created', 'ibra', '2000.00'),
(177, '26/04/001', 1, 2, '40000.00', '2025-04-26 08:15:05', 'created', 'messi', '5000.00'),
(180, '26/04/004', 1, 2, '10000.00', '2025-04-26 08:18:27', 'created', '', '1250.00'),
(181, '26/04/005', 1, 2, '2000.00', '2025-04-26 08:26:39', 'created', '', '250.00'),
(182, '26/04/004', 1, 2, '2000.00', '2025-04-26 10:40:23', 'created', 'shine', '250.00'),
(183, '27/04/001', 1, 2, '80000.00', '2025-04-27 17:38:16', 'sent', 'mendree', '10000.00'),
(184, '28/04/002', 1, 2, '48000.00', '2025-04-28 17:39:23', 'delivered', 'meme', '8000.00'),
(185, '29/04/001', 1, 2, '31000.00', '2025-04-29 17:46:18', 'cancelled', 'almandra', '3000.00'),
(186, '29/04/002', 1, 2, '4800.00', '2025-04-29 18:09:17', 'created', 'eladia', '600.00'),
(187, '29/04/003', 1, 2, '48000.00', '2025-04-29 18:10:49', 'cancelled', 'pipo', '8000.00'),
(188, '29/04/004', 1, 2, '44000.00', '2025-04-29 18:53:53', 'cancelled', 'khe', '5500.00'),
(189, '07/05/001', 1, 2, '91200.00', '2025-05-07 11:18:55', 'created', 'jesca', '12400.00'),
(190, '09/05/001', 1, 2, '32000.00', '2025-05-09 22:24:27', 'created', 'messi', '5000.00'),
(191, '09/05/002', 1, 2, '40000.00', '2025-05-09 22:25:05', 'cancelled', 'salama', '5000.00'),
(192, '09/05/003', 1, 2, '40000.00', '2025-05-09 22:25:34', 'cancelled', 'salama', '5000.00'),
(193, '09/05/004', 1, 2, '40000.00', '2025-05-09 22:27:34', 'created', 'you', '5000.00'),
(194, '10/05/005', 1, 2, '14800.00', '2025-05-10 01:15:46', 'created', 'ashura', '1850.00'),
(195, '10/05/002', 1, 2, '60000.00', '2025-05-10 01:18:19', 'created', 'side', '6000.00'),
(196, '11/05/001', 1, 2, '64000.00', '2025-05-11 01:04:53', 'created', 'kamala', '8000.00'),
(197, '13/05/001', 1, 2, '48000.00', '2025-05-13 20:33:59', 'created', 'messi', '8000.00'),
(198, '13/05/002', 1, 2, '12000.00', '2025-05-13 20:38:23', 'created', 'mamaLove', '2000.00'),
(199, '16/05/001', 2, 14, '30000.00', '2025-05-16 20:29:44', 'created', 'neymar', '9000.00'),
(200, '16/05/001', 1, 2, '87000.00', '2025-05-16 21:01:53', 'created', 'jay', '11500.00'),
(201, '16/05/002', 1, 2, '40000.00', '2025-05-16 22:33:58', 'created', 'jackie', '5000.00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `created_by` int(100) DEFAULT NULL,
  `company_id` int(100) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `sold_in` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('created','sent','delivered','cancelled') DEFAULT 'created',
  `buying_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `sum` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `unit_id`, `created_by`, `company_id`, `name`, `sold_in`, `quantity`, `status`, `buying_price`, `selling_price`, `sum`) VALUES
(163, 162, 6, NULL, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(164, 163, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(165, 164, 6, NULL, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(166, 165, 3, NULL, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(167, 166, 2, NULL, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '31000.00'),
(168, 166, 3, NULL, 2, 1, 'jamaa (mche)', 'mche', 8, 'created', '1750.00', '2000.00', '16000.00'),
(169, 167, 1, NULL, 2, 1, '25kgs sembe (kg)', 'kg', 12, 'created', '1400.00', '1600.00', '19200.00'),
(170, 168, 2, NULL, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '31000.00'),
(171, 169, 3, NULL, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(172, 170, 2, NULL, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '31000.00'),
(173, 170, 8, NULL, 2, 1, 'mo xtra', 'whole', 7, 'created', '4700.00', '5000.00', '35000.00'),
(174, 171, 12, NULL, 2, 1, 'mo chungwa', 'whole', 5, 'created', '4500.00', '5000.00', '25000.00'),
(175, 171, 1, NULL, 2, 1, '25kgs sembe (kg)', 'kg', 5, 'created', '1400.00', '1600.00', '8000.00'),
(176, 172, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(177, 173, 3, NULL, 2, 1, 'jamaa (mche)', 'mche', 5, 'created', '1750.00', '2000.00', '10000.00'),
(178, 174, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(179, 175, 2, NULL, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '31000.00'),
(180, 176, 1, NULL, 2, 1, '25kgs sembe (kg)', 'kg', 10, 'created', '1400.00', '1600.00', '16000.00'),
(181, 177, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(184, 180, 3, NULL, 2, 1, 'jamaa (mche)', 'mche', 5, 'created', '1750.00', '2000.00', '10000.00'),
(185, 181, 3, NULL, 2, 1, 'jamaa (mche)', 'mche', 1, 'created', '1750.00', '2000.00', '2000.00'),
(186, 182, 3, NULL, 2, 1, 'jamaa (mche)', 'mche', 1, 'created', '1750.00', '2000.00', '2000.00'),
(187, 183, 1, NULL, 2, 1, '25kgs sembe', 'whole', 2, 'created', '35000.00', '40000.00', '80000.00'),
(188, 184, 14, NULL, 2, 1, 'grand malt', 'whole', 1, 'created', '40000.00', '48000.00', '48000.00'),
(189, 185, 2, NULL, 2, 1, 'ngano', 'whole', 1, 'cancelled', '28000.00', '31000.00', '31000.00'),
(190, 186, 1, NULL, 2, 1, '25kgs sembe (kg)', 'kg', 3, 'created', '1400.00', '1600.00', '4800.00'),
(191, 187, 14, NULL, 2, 1, 'grand malt', 'whole', 1, 'cancelled', '40000.00', '48000.00', '48000.00'),
(192, 188, 3, 4, 2, 1, 'jamaa (mche)', 'mche', 2, 'cancelled', '1750.00', '2000.00', '4000.00'),
(193, 188, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'cancelled', '35000.00', '40000.00', '40000.00'),
(194, 189, 6, NULL, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(195, 189, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(196, 189, 1, 1, 2, 1, '25kgs sembe (kg)', 'kg', 12, 'created', '1400.00', '1600.00', '19200.00'),
(197, 190, 6, NULL, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(198, 191, 3, NULL, 2, 1, 'jamaa', 'whole', 1, 'cancelled', '35000.00', '40000.00', '40000.00'),
(199, 192, 3, NULL, 2, 1, 'jamaa', 'whole', 1, 'cancelled', '35000.00', '40000.00', '40000.00'),
(200, 193, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(201, 194, 3, 4, 2, 1, 'jamaa (mche)', 'mche', 5, 'created', '1750.00', '2000.00', '10000.00'),
(202, 194, 1, 1, 2, 1, '25kgs sembe (kg)', 'kg', 3, 'created', '1400.00', '1600.00', '4800.00'),
(203, 195, 12, NULL, 2, 1, 'mo chungwa', 'whole', 12, 'created', '4500.00', '5000.00', '60000.00'),
(204, 196, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(205, 196, 3, 4, 2, 1, 'jamaa (mche)', 'mche', 12, 'created', '1750.00', '2000.00', '24000.00'),
(206, 197, 14, NULL, 2, 1, 'grand malt', 'whole', 1, 'created', '40000.00', '48000.00', '48000.00'),
(207, 198, 14, 8, 2, 1, 'grand malt (quarter_catton)', 'quarter_catton', 1, 'created', '10000.00', '12000.00', '12000.00'),
(208, 199, 13, NULL, 14, 2, 'santa lucia', 'whole', 1, 'created', '21000.00', '30000.00', '30000.00'),
(209, 200, 6, NULL, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(210, 200, 12, NULL, 2, 1, 'mo chungwa', 'whole', 3, 'created', '4500.00', '5000.00', '15000.00'),
(211, 200, 1, NULL, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(212, 201, 3, NULL, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `method_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `cash` varchar(3) DEFAULT 'yes',
  `NMB` varchar(3) DEFAULT 'no',
  `CRDB` varchar(3) DEFAULT 'no',
  `NBC` varchar(3) DEFAULT 'no',
  `mpesa` varchar(3) DEFAULT 'no',
  `airtel_money` varchar(3) DEFAULT 'no',
  `tigo_pesa` varchar(3) DEFAULT 'no',
  `halo_pesa` varchar(3) DEFAULT 'no',
  `azam_pesa` varchar(3) DEFAULT 'no',
  `debt` varchar(3) DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`method_id`, `created_by`, `company_id`, `cash`, `NMB`, `CRDB`, `NBC`, `mpesa`, `airtel_money`, `tigo_pesa`, `halo_pesa`, `azam_pesa`, `debt`) VALUES
(1, 2, 1, 'yes', 'yes', 'no', 'no', 'yes', 'no', 'yes', 'no', 'no', 'yes'),
(3, 14, 2, 'yes', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `company_id` int(100) DEFAULT NULL,
  `created_by` int(100) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `quantified` varchar(20) DEFAULT NULL,
  `under_stock_reminder` decimal(10,2) DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `company_id`, `created_by`, `name`, `quantity`, `quantified`, `under_stock_reminder`, `buying_price`, `selling_price`, `image_path`) VALUES
(1, 1, 2, '25kgs sembe', '4.72', 'sacks', '15.00', '35000.00', '40000.00', NULL),
(2, 1, 2, 'ngano', '9.00', 'sack', '15.00', '28000.00', '31000.00', NULL),
(3, 1, 2, 'jamaa', '6.15', 'catton', '10.00', '35000.00', '40000.00', NULL),
(6, 1, 2, 'santa lucia', '12.00', 'box', '7.00', '27000.00', '32000.00', NULL),
(7, 1, 2, 'azam nazi', '25.00', 'box', '7.00', '12000.00', '15000.00', NULL),
(8, 1, 2, 'mo xtra', '16.00', 'catton', '30.00', '4700.00', '5000.00', NULL),
(9, 1, 2, '25kgs sukari', '30.00', 'sack', '10.00', '45000.00', '50000.00', NULL),
(11, 1, 2, 'nice biscuit', '10.00', 'box', '10.00', '7500.00', '8000.00', NULL),
(12, 1, 2, 'mo chungwa', '109.00', 'catton', '25.00', '4500.00', '5000.00', NULL),
(13, 2, 14, 'santa lucia', '4.00', 'catton', '10.00', '21000.00', '30000.00', NULL),
(14, 1, 2, 'grand malt', '6.75', 'catton', '5.00', '40000.00', '48000.00', NULL),
(15, 1, 2, 'konyagi kbw', '1.00', 'catton', '3.00', '144000.00', '180000.00', 'uploads/products/product_68120ec702ad4.jpg'),
(18, 1, 2, 'tiger battery', '3.00', 'catton', '2.00', '31000.00', '40000.00', 'uploads/products/product_68206d5b75bd5.jpeg'),
(19, 1, 2, 'softcare', '0.00', 'catton', '5.00', '37000.00', '45000.00', 'uploads/products/product_6823b343d1231.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `date_made` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `date_made`, `total`) VALUES
(1, '2024-10-11 23:18:26', '175000.00'),
(2, '2024-11-01 15:46:43', '540000.00');

-- --------------------------------------------------------

--
-- Table structure for table `purchases_items`
--

CREATE TABLE `purchases_items` (
  `purchase_item_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `buying_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `date_made` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT 0.00,
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchases_items`
--

INSERT INTO `purchases_items` (`purchase_item_id`, `company_id`, `created_by`, `product_id`, `quantity`, `buying_price`, `selling_price`, `date_made`, `total`, `supplier_name`) VALUES
(1, 1, 2, 1, 5, '35000.00', '40000.00', '2024-10-11 23:18:26', '175000.00', NULL),
(2, 1, 2, 6, 20, '27000.00', '32000.00', '2024-11-01 15:46:43', '540000.00', NULL),
(3, 1, 2, 11, 10, '7500.00', '8000.00', '2024-11-06 21:36:29', '75000.00', NULL),
(4, 1, 2, 8, 15, '4700.00', '5000.00', '2024-11-29 11:00:30', '70500.00', NULL),
(5, 1, 2, 1, 100, '35000.00', '40000.00', '2024-11-29 11:01:29', '3500000.00', NULL),
(6, 1, 2, 9, 30, '45000.00', '50000.00', '2024-11-29 11:02:41', '1350000.00', NULL),
(7, 1, 2, 12, 30, '4500.00', '5000.00', '2024-11-29 11:03:05', '135000.00', NULL),
(8, 1, 2, 8, 8, '4700.00', '5000.00', '2024-11-29 11:25:57', '37600.00', NULL),
(9, 1, 2, 1, 50, '35000.00', '40000.00', '2024-11-29 14:14:34', '1750000.00', NULL),
(10, 1, 2, 1, 50, '35000.00', '40000.00', '2024-11-29 14:33:06', '1750000.00', NULL),
(11, 1, 2, 1, 5, '35000.00', '40000.00', '2024-11-29 14:46:46', '175000.00', NULL),
(12, 1, 2, 1, 10, '35000.00', '40000.00', '2024-11-29 15:17:40', '350000.00', NULL),
(13, 1, 2, 1, 5, '35000.00', '40000.00', '2024-11-29 15:24:54', '175000.00', NULL),
(14, 1, 2, 2, 10, '28000.00', '31000.00', '2024-12-11 17:41:10', '280000.00', NULL),
(15, 1, 2, 3, 1, '35000.00', '40000.00', '2024-12-11 17:45:12', '35000.00', NULL),
(16, 1, 2, 1, 1, '35000.00', '40000.00', '2024-12-11 17:46:06', '35000.00', NULL),
(17, 1, 2, 1, 1, '35000.00', '40000.00', '2024-12-11 18:03:31', '35000.00', NULL),
(25, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:39:54', '105000.00', NULL),
(26, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:43:20', '105000.00', NULL),
(28, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:56:39', '105000.00', NULL),
(29, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:00:35', '105000.00', NULL),
(30, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:02:39', '105000.00', NULL),
(31, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:17:50', '105000.00', NULL),
(32, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:18:54', '105000.00', NULL),
(33, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 21:30:53', '105000.00', NULL),
(34, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 21:54:43', '105000.00', NULL),
(35, 1, 2, 1, 2, '35000.00', '40000.00', '2025-03-07 21:57:22', '70000.00', NULL),
(43, 1, 2, 1, 1, '35000.00', '40000.00', '2025-03-09 10:25:37', '35000.00', NULL),
(44, 1, 2, 6, 1, '27000.00', '32000.00', '2025-04-09 08:22:12', '27000.00', NULL),
(45, 1, 2, 14, 1, '40000.00', '48000.00', '2025-04-10 13:38:11', '40000.00', NULL),
(46, 1, 2, 14, 1, '40000.00', '48000.00', '2025-04-10 20:27:51', '40000.00', NULL),
(47, 1, 2, 14, 5, '40000.00', '48000.00', '2025-04-10 22:49:10', '200000.00', NULL),
(48, 1, 2, 1, 5, '35000.00', '40000.00', '2025-04-22 17:45:15', '175000.00', NULL),
(50, 1, 2, 1, 10, '35000.00', '40000.00', '2025-04-25 17:29:17', '350000.00', NULL),
(51, 1, 2, 6, 2, '27000.00', '32000.00', '2025-04-25 18:02:18', '54000.00', NULL),
(52, 1, 2, 1, 1, '35000.00', '40000.00', '2025-04-25 18:43:42', '35000.00', NULL),
(53, 1, 2, 3, 5, '35000.00', '40000.00', '2025-04-25 18:59:37', '175000.00', NULL),
(54, 1, 2, 14, 1, '40000.00', '48000.00', '2025-04-27 17:39:56', '40000.00', NULL),
(55, 1, 2, 1, 3, '35000.00', '40000.00', '2025-05-02 06:27:36', '105000.00', NULL),
(56, 1, 2, 12, 100, '4500.00', '5000.00', '2025-05-02 13:27:02', '450000.00', NULL),
(58, 1, 2, 15, 1, '144000.00', '180000.00', '2025-05-02 13:31:41', '144000.00', NULL),
(59, 1, 2, 1, 12, '35000.00', '40000.00', '2025-05-02 18:17:42', '420000.00', NULL),
(60, 1, 2, 12, 5, '4500.00', '5000.00', '2025-05-06 19:54:17', '22500.00', 'mendree'),
(61, 1, 2, 1, 2, '35000.00', '40000.00', '2025-05-06 20:17:50', '70000.00', 'kamala'),
(62, 1, 2, 18, 3, '31000.00', '40000.00', '2025-05-11 12:30:18', '93000.00', 'ahmida'),
(63, 1, 2, 14, 7, '40000.00', '48000.00', '2025-05-13 20:35:39', '280000.00', 'ahmida'),
(64, 2, 14, 13, 2, '21000.00', '30000.00', '2025-05-16 20:32:46', '42000.00', 'messi');

-- --------------------------------------------------------

--
-- Table structure for table `quantity_destroyed`
--

CREATE TABLE `quantity_destroyed` (
  `qnt_dstr_id` int(11) NOT NULL,
  `created_by` int(100) DEFAULT NULL,
  `company_id` int(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity_destroyed` int(11) NOT NULL,
  `date_destroyed` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quantity_destroyed`
--

INSERT INTO `quantity_destroyed` (`qnt_dstr_id`, `created_by`, `company_id`, `product_id`, `name`, `quantity_destroyed`, `date_destroyed`) VALUES
(3, 2, 1, 1, '25kgs sembe', 1, '2024-10-13 17:38:36'),
(7, 2, 1, 1, '25kgs sembe', 1, '2025-04-25 06:36:33'),
(8, 2, 1, 1, '25kgs sembe', 1, '2025-04-25 22:32:09'),
(9, 2, 1, 3, 'jamaa', 1, '2025-04-26 10:41:42'),
(10, 2, 1, 1, '25kgs sembe', 12, '2025-05-02 17:36:14'),
(11, 2, 1, 12, 'mo chungwa', 1, '2025-05-02 17:38:11'),
(12, 2, 1, 12, 'mo chungwa', 1, '2025-05-02 17:57:46'),
(13, 2, 1, 1, '25kgs sembe', 1, '2025-05-02 19:31:18'),
(14, 2, 1, 12, 'mo chungwa', 1, '2025-05-02 19:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `company_owner` enum('yes','no') DEFAULT 'no',
  `cashier` enum('yes','no') DEFAULT 'no',
  `store_keeper` enum('yes','no') DEFAULT 'no',
  `delivery_man` enum('yes','no') DEFAULT 'no',
  `admin` enum('yes','no') DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `user_id`, `company_owner`, `cashier`, `store_keeper`, `delivery_man`, `admin`) VALUES
(2, 2, 'yes', 'no', 'no', 'no', 'no'),
(5, 5, 'no', 'yes', 'no', 'no', 'no'),
(6, 6, 'no', 'no', 'yes', 'no', 'no'),
(7, 7, 'no', 'no', 'no', 'yes', 'no'),
(11, 11, 'no', 'no', 'no', 'no', 'yes'),
(14, 14, 'yes', 'no', 'no', 'no', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `transType_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `date_made` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transType_id`, `company_id`, `created_by`, `transaction_type`, `amount`, `description`, `date_made`) VALUES
(106, 47, 1, 2, 'purchase', '200000.00', 'purchase', '2025-04-10 19:49:10'),
(109, NULL, 1, 2, 'drawings', '2500.00', 'mombe', '2025-04-17 18:37:40'),
(110, NULL, 1, 2, 'expenses', '92500.00', 'rent', '2025-04-17 18:39:52'),
(111, 48, 1, 2, 'purchase', '175000.00', 'purchase', '2025-04-18 14:45:15'),
(112, 7, 1, 2, 'destruction', '35000.00', 'Destruction loss', '2025-04-18 03:36:33'),
(113, 162, 1, 2, 'sale', '32000.00', 'sale', '2025-04-18 13:22:41'),
(114, 163, 1, 2, 'sale', '40000.00', 'sale', '2025-04-18 13:26:36'),
(116, 50, 1, 2, 'purchase', '350000.00', 'purchase', '2025-04-18 14:29:17'),
(117, 51, 1, 2, 'purchase', '54000.00', 'purchase', '2025-04-19 15:02:18'),
(118, 164, 1, 2, 'sale', '32000.00', 'sale', '2025-04-19 15:03:25'),
(119, NULL, 1, 2, 'drawings', '1500.00', 'sara', '2025-04-19 15:31:03'),
(120, 165, 1, 2, 'sale', '40000.00', 'sale', '2025-04-19 15:31:43'),
(121, 166, 1, 2, 'sale', '47000.00', 'sale', '2025-04-20 15:41:20'),
(122, 167, 1, 2, 'sale', '19200.00', 'sale', '2025-04-20 15:42:43'),
(123, NULL, 1, 2, 'expenses', '3000.00', 'taka', '2025-04-20 15:43:17'),
(124, 52, 1, 2, 'purchase', '35000.00', 'purchase', '2025-04-20 15:43:42'),
(125, 168, 1, 2, 'sale', '31000.00', 'sale', '2025-04-21 15:50:41'),
(126, 169, 1, 2, 'sale', '40000.00', 'sale', '2025-04-21 15:51:17'),
(127, NULL, 1, 2, 'drawings', '500.00', 'simwe', '2025-04-21 15:52:50'),
(128, 170, 1, 2, 'sale', '66000.00', 'sale', '2025-04-22 15:57:46'),
(129, 171, 1, 2, 'sale', '33000.00', 'sale', '2025-04-22 15:58:40'),
(130, 53, 1, 2, 'purchase', '175000.00', 'purchase', '2025-04-22 15:59:37'),
(131, 172, 1, 2, 'sale', '40000.00', 'sale', '2025-04-23 16:03:44'),
(132, 173, 1, 2, 'sale', '10000.00', 'sale', '2025-04-23 16:04:34'),
(133, NULL, 1, 2, 'expenses', '15000.00', 'door maintanance', '2025-04-23 16:12:22'),
(134, 174, 1, 2, 'sale', '40000.00', 'sale', '2025-04-24 16:13:39'),
(135, 175, 1, 2, 'sale', '31000.00', 'sale', '2025-04-24 16:15:38'),
(136, NULL, 1, 2, 'add_capital', '100000.00', 'chiades', '2025-04-24 16:16:27'),
(137, 176, 1, 2, 'sale', '16000.00', 'sale', '2025-04-25 19:30:10'),
(138, 8, 1, 2, 'destruction', '35000.00', 'Destruction loss', '2025-04-25 19:32:09'),
(139, 177, 1, 2, 'sale', '40000.00', 'sale', '2025-04-26 05:15:05'),
(142, 180, 1, 2, 'sale', '10000.00', 'sale', '2025-04-26 05:18:27'),
(143, 181, 1, 2, 'sale', '2000.00', 'sale', '2025-04-26 05:26:39'),
(144, 182, 1, 2, 'sale', '2000.00', 'sale', '2025-04-26 07:40:23'),
(145, 9, 1, 2, 'destruction', '35000.00', 'Destruction loss', '2025-04-26 07:41:42'),
(146, 183, 1, 2, 'sale', '80000.00', 'sale', '2025-04-27 14:38:16'),
(147, 184, 1, 2, 'sale', '48000.00', 'sale', '2025-04-28 14:39:23'),
(148, 54, 1, 2, 'purchase', '40000.00', 'purchase', '2025-04-27 14:39:56'),
(149, 185, 1, 2, 'sale', '31000.00', 'sale', '2025-04-29 14:46:18'),
(150, 185, 1, 2, 'refund', '-31000.00', 'sale_cancelled', '2025-04-29 14:48:08'),
(151, NULL, 1, 2, 'drawings', '300000.00', 'chiadesi', '2025-04-29 15:00:38'),
(152, 186, 1, 2, 'sale', '4800.00', 'sale', '2025-04-29 15:09:18'),
(153, 187, 1, 2, 'sale', '48000.00', 'sale', '2025-04-29 15:10:49'),
(154, 187, 1, 2, 'refund', '-48000.00', 'sale_cancelled', '2025-04-29 15:13:58'),
(155, 188, 1, 2, 'sale', '44000.00', 'sale', '2025-04-29 15:53:53'),
(156, 188, 1, 2, 'refund', '-44000.00', 'sale_cancelled', '2025-04-29 15:54:19'),
(157, 55, 1, 2, 'purchase', '105000.00', 'purchase', '2025-05-02 03:27:36'),
(158, 56, 1, 2, 'purchase', '450000.00', 'purchase', '2025-05-02 10:27:02'),
(160, 58, 1, 2, 'purchase', '144000.00', 'purchase', '2025-05-02 10:31:41'),
(161, 10, 1, 2, 'destruction', '420000.00', 'Destruction loss', '2025-05-02 14:36:14'),
(162, 4, 1, 2, 'destruction', '16800.00', 'Destruction loss', '2025-05-02 14:36:14'),
(163, 11, 1, 2, 'destruction', '4500.00', 'Destruction loss', '2025-05-02 14:38:11'),
(164, 12, 1, 2, 'destruction', '4500.00', 'Destruction loss', '2025-05-02 14:57:46'),
(165, NULL, 1, 2, 'add_capital', '500000.00', 'Chiades', '2025-05-02 15:09:52'),
(166, 59, 1, 2, 'purchase', '420000.00', 'purchase', '2025-05-02 15:17:42'),
(167, 13, 1, 2, 'destruction', '35000.00', 'Destruction loss', '2025-05-02 16:31:18'),
(168, 14, 1, 2, 'destruction', '4500.00', 'Destruction loss', '2025-05-02 16:34:32'),
(169, 60, 1, 2, 'purchase', '22500.00', 'purchase', '2025-05-06 16:54:17'),
(170, 61, 1, 2, 'purchase', '70000.00', 'purchase', '2025-05-06 17:17:50'),
(171, 189, 1, 2, 'sale', '91200.00', 'sale', '2025-05-07 08:18:55'),
(172, NULL, 1, 2, 'drawings', '2500.00', 'mombe chipsi', '2025-05-07 08:25:29'),
(173, 190, 1, 2, 'sale', '32000.00', 'sale', '2025-05-09 19:24:27'),
(174, 191, 1, 2, 'sale', '40000.00', 'sale', '2025-05-09 19:25:05'),
(175, 192, 1, 2, 'sale', '40000.00', 'sale', '2025-05-09 19:25:34'),
(176, 192, 1, 2, 'refund', '-40000.00', 'sale_cancelled', '2025-05-09 19:26:46'),
(177, 191, 1, 2, 'refund', '-40000.00', 'sale_cancelled', '2025-05-09 19:27:01'),
(178, 193, 1, 2, 'sale', '40000.00', 'sale', '2025-05-09 19:27:34'),
(179, NULL, 1, 2, 'drawings', '50000.00', 'printing', '2025-05-09 21:03:55'),
(180, 194, 1, 2, 'sale', '14800.00', 'sale', '2025-05-09 22:15:46'),
(181, 195, 1, 2, 'sale', '60000.00', 'sale', '2025-05-09 22:18:19'),
(182, 196, 1, 2, 'sale', '64000.00', 'sale', '2025-05-10 22:04:53'),
(183, NULL, 1, 2, 'add_capital', '1000000.00', 'chiades', '2025-05-10 22:06:40'),
(184, 62, 1, 2, 'purchase', '93000.00', 'purchase', '2025-05-11 09:30:18'),
(185, 197, 1, 2, 'sale', '48000.00', 'sale', '2025-05-13 17:34:00'),
(186, 63, 1, 2, 'purchase', '280000.00', 'purchase', '2025-05-13 17:35:39'),
(187, 198, 1, 2, 'sale', '12000.00', 'sale', '2025-05-13 17:38:23'),
(188, 199, 2, 14, 'sale', '30000.00', 'sale', '2025-05-16 17:29:44'),
(189, NULL, 2, 14, 'drawings', '15000.00', 'outings', '2025-05-16 17:31:10'),
(190, NULL, 2, 14, 'add_capital', '10000.00', 'kamala', '2025-05-16 17:32:05'),
(191, 64, 2, 14, 'purchase', '42000.00', 'purchase', '2025-05-16 17:32:46'),
(192, 200, 1, 2, 'sale', '87000.00', 'sale', '2025-05-16 18:01:53'),
(193, 201, 1, 2, 'sale', '40000.00', 'sale', '2025-05-16 19:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `per_single_quantity` decimal(10,2) DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `available_units` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `product_id`, `company_id`, `created_by`, `name`, `per_single_quantity`, `buying_price`, `selling_price`, `available_units`) VALUES
(1, 1, 1, 2, 'kg', '25.00', '1400.00', '1600.00', '233.00'),
(2, 1, 1, 2, 'half_kg', '50.00', '700.00', '800.00', '466.00'),
(3, 1, 1, 2, 'quarter_kg', '100.00', '350.00', '400.00', '932.00'),
(4, 3, 1, 2, 'mche', '20.00', '1750.00', '2000.00', '160.00'),
(5, 3, 1, 2, 'nusu_mche', '40.00', '875.00', '1000.00', '320.00'),
(6, 13, 2, 14, 'pack', '30.00', '800.00', '1000.00', '60.00'),
(7, 14, 1, 2, 'half_catton', '2.00', '20000.00', '24000.00', '18.00'),
(8, 14, 1, 2, 'quarter_catton', '4.00', '10000.00', '12000.00', '36.00'),
(9, 9, 1, 2, 'kg', '25.00', '1800.00', '2000.00', '750.00');

-- --------------------------------------------------------

--
-- Table structure for table `units_destroyed`
--

CREATE TABLE `units_destroyed` (
  `unt_dstr_id` int(11) NOT NULL,
  `created_by` int(100) DEFAULT NULL,
  `company_id` int(100) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `units_destroyed` int(11) NOT NULL,
  `date_destroyed` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `units_destroyed`
--

INSERT INTO `units_destroyed` (`unt_dstr_id`, `created_by`, `company_id`, `unit_id`, `product_id`, `name`, `units_destroyed`, `date_destroyed`) VALUES
(3, 2, 1, 1, 1, '25kgs sembe', 12, '2024-10-13 17:24:58'),
(4, 2, 1, 1, 1, '25kgs sembe', 12, '2025-05-02 17:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneNo` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `company_id`, `username`, `firstName`, `lastName`, `email`, `phoneNo`, `password`) VALUES
(2, 1, 'mendes.kamala', 'mendes', 'kamala', 'mendesekamala@gmail.com', '0715200400', '$2y$10$iQ2aB9n.0E3ItYMIpYU.vOkE0jJdGJwqgkzPI00LDzi8cj3.MdMeC'),
(5, 1, 'cashier.cashier', 'cashier', 'cashier', 'cashier@gmail.com', '0715200040', '$2y$10$LfDJI00bpid2.uh7jhLoDuewIT.iamrcEvtCWzJ6jkwEkup1qPQ2m'),
(6, 1, 'store.keeper', 'store', 'keeper', 'storekeeper@gmail.com', '0715200400', '$2y$10$LHepmERdwlAwIdnJkVQiLekogbvaBbPpDpOA1GIEsSL5PzfwL07mC'),
(7, 1, 'delivery.man', 'delivery', 'man', 'deliveryman@gmail.com', '0715200400', '$2y$10$A4ApcgIMhaOBezmfJvqFme9ye/QzNnMli/FFzxGgAreiCtP.W1N16'),
(11, 1, 'lilian.kabigumila', 'lilian', 'kabigumila', 'liliankabigumila@gmail.com', '0715200400', '$2y$10$54ShdxvwuUS0ccwClhMBJ.gGPUDpNK7GG5f65bOEAmwmxY42F3diC'),
(14, 2, 'kamala.mendes', '', '', 'kamalamendes@gmail.com', '', '$2y$10$9eOzV8VR/HqyuePB7Jf2LO4l6v2iF.4cuguLkI4.NWO7UKIdoraBO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD PRIMARY KEY (`debt_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `methods_used`
--
ALTER TABLE `methods_used`
  ADD PRIMARY KEY (`meth_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `money`
--
ALTER TABLE `money`
  ADD PRIMARY KEY (`money_id`),
  ADD KEY `fk_company_id` (`company_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`method_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_products_created_by` (`created_by`),
  ADD KEY `fk_products_company_id` (`company_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`);

--
-- Indexes for table `purchases_items`
--
ALTER TABLE `purchases_items`
  ADD PRIMARY KEY (`purchase_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quantity_destroyed`
--
ALTER TABLE `quantity_destroyed`
  ADD PRIMARY KEY (`qnt_dstr_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `units_destroyed`
--
ALTER TABLE `units_destroyed`
  ADD PRIMARY KEY (`unt_dstr_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `debt_payments`
--
ALTER TABLE `debt_payments`
  MODIFY `debt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `methods_used`
--
ALTER TABLE `methods_used`
  MODIFY `meth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `money`
--
ALTER TABLE `money`
  MODIFY `money_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchases_items`
--
ALTER TABLE `purchases_items`
  MODIFY `purchase_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `quantity_destroyed`
--
ALTER TABLE `quantity_destroyed`
  MODIFY `qnt_dstr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `units_destroyed`
--
ALTER TABLE `units_destroyed`
  MODIFY `unt_dstr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD CONSTRAINT `debt_payments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `debt_payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `debt_payments_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`);

--
-- Constraints for table `methods_used`
--
ALTER TABLE `methods_used`
  ADD CONSTRAINT `methods_used_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE;

--
-- Constraints for table `money`
--
ALTER TABLE `money`
  ADD CONSTRAINT `fk_company_id` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_company_id` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `purchases_items`
--
ALTER TABLE `purchases_items`
  ADD CONSTRAINT `purchases_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `purchases_items_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  ADD CONSTRAINT `purchases_items_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `quantity_destroyed`
--
ALTER TABLE `quantity_destroyed`
  ADD CONSTRAINT `quantity_destroyed_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `units_destroyed`
--
ALTER TABLE `units_destroyed`
  ADD CONSTRAINT `units_destroyed_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  ADD CONSTRAINT `units_destroyed_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

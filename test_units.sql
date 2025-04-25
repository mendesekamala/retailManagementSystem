-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2025 at 04:58 PM
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
(35, 102, 'cash', '24000.00', '24000.00'),
(36, 103, 'tigo pesa', '48000.00', '48000.00'),
(37, 104, 'NMB', '24000.00', '24000.00'),
(38, 105, 'NMB', '48000.00', '48000.00'),
(39, 106, 'cash', '200000.00', '200000.00'),
(40, 107, 'NMB', '104000.00', '104000.00'),
(41, 108, 'NMB', '104000.00', '104000.00'),
(42, 109, 'cash', '2500.00', '2500.00'),
(43, 110, 'cash', '92500.00', '92500.00'),
(44, 111, 'cash', '175000.00', '175000.00');

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
(1, 1, '0.00', '225000.00', '466200.00', '0.00', '0.00', '273000.00', '358000.00', '0.00', '0.00', '0.00', '26000.00', '0.00'),
(2, 2, '0.00', '400000.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00');

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
(138, '24/03/001', 1, 2, '40000.00', '2025-03-24 22:16:50', 'cancelled', 'new', '5000.00'),
(139, '09/04/001', 1, 2, '32000.00', '2025-04-09 08:20:14', 'created', 'messi', '5000.00'),
(140, '10/04/001', 1, 2, '71000.00', '2025-04-10 10:08:46', 'created', 'messi', '8000.00'),
(141, '10/04/002', 1, 2, '64000.00', '2025-04-10 13:46:58', 'created', 'kamala', '9000.00'),
(146, '10/04/002', 1, 2, '88000.00', '2025-04-10 18:57:39', 'created', 'kamala', '88000.00'),
(147, '10/04/002', 1, 2, '88000.00', '2025-04-10 18:57:40', 'created', 'kamala', '88000.00'),
(150, '09/04/001', 1, 2, '64000.00', '2025-04-10 19:16:13', 'created', 'messi', '64000.00'),
(151, '09/04/001', 1, 2, '64000.00', '2025-04-10 19:16:13', 'created', 'messi', '64000.00'),
(154, '10/04/001', 1, 2, '111000.00', '2025-04-10 19:55:09', 'created', 'messi', '111000.00'),
(155, '10/04/002', 1, 2, '64000.00', '2025-04-10 20:15:43', 'created', 'kamala', '64000.00'),
(156, '10/04/009', 1, 2, '24000.00', '2025-04-10 20:29:57', 'created', 'kma', '4000.00'),
(157, '10/04/009', 1, 2, '48000.00', '2025-04-10 20:31:06', 'created', 'kma', '48000.00'),
(158, '10/04/011', 1, 2, '24000.00', '2025-04-10 20:49:21', 'created', 'me', '4000.00'),
(159, '10/04/011', 1, 2, '48000.00', '2025-04-10 20:50:47', 'created', 'me', '8000.00'),
(160, '10/04/002', 1, 2, '104000.00', '2025-04-10 23:04:51', 'created', 'kamala', '104000.00'),
(161, '10/04/002', 1, 2, '104000.00', '2025-04-10 23:04:51', 'created', 'kamala', '104000.00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
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

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `created_by`, `company_id`, `name`, `sold_in`, `quantity`, `status`, `buying_price`, `selling_price`, `sum`) VALUES
(123, 138, 1, 2, 1, '25kgs sembe', 'whole', 1, 'cancelled', '35000.00', '40000.00', '40000.00'),
(124, 139, 6, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(125, 140, 2, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '31000.00'),
(126, 140, 3, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(127, 141, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(128, 141, 14, 2, 1, 'grand malt (half_catton)', 'half_catton', 1, 'created', '20000.00', '24000.00', '24000.00'),
(137, 146, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '0.00', '40000.00', '40000.00'),
(138, 146, 14, 2, 1, 'grand malt', 'half_catton', 2, 'created', '0.00', '24000.00', '48000.00'),
(139, 147, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '0.00', '40000.00', '40000.00'),
(140, 147, 14, 2, 1, 'grand malt', 'half_catton', 2, 'created', '0.00', '24000.00', '48000.00'),
(145, 150, 6, 2, 1, 'santa lucia', 'whole', 2, 'created', '0.00', '32000.00', '64000.00'),
(146, 151, 6, 2, 1, 'santa lucia', 'whole', 2, 'created', '0.00', '32000.00', '64000.00'),
(151, 154, 2, 2, 1, 'ngano', 'whole', 1, 'created', '0.00', '31000.00', '31000.00'),
(152, 154, 3, 2, 1, 'jamaa', 'whole', 2, 'created', '0.00', '40000.00', '80000.00'),
(153, 155, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '0.00', '40000.00', '40000.00'),
(154, 155, 14, 2, 1, 'grand malt', 'half_catton', 1, 'created', '0.00', '24000.00', '24000.00'),
(155, 156, 14, 2, 1, 'grand malt (half_catton)', 'half_catton', 1, 'created', '20000.00', '24000.00', '24000.00'),
(156, 157, 14, 2, 1, 'grand malt', 'half_catton', 2, 'created', '0.00', '24000.00', '48000.00'),
(157, 158, 14, 2, 1, 'grand malt (half_catton)', 'half_catton', 1, 'created', '20000.00', '24000.00', '24000.00'),
(158, 159, 14, 2, 1, 'grand malt', 'half_catton', 2, 'created', '20000.00', '24000.00', '48000.00'),
(159, 160, 1, 2, 1, '25kgs sembe', 'whole', 2, 'created', '0.00', '40000.00', '80000.00'),
(160, 160, 14, 2, 1, 'grand malt', 'half_catton', 1, 'created', '0.00', '24000.00', '24000.00'),
(161, 161, 1, 2, 1, '25kgs sembe', 'whole', 2, 'created', '0.00', '40000.00', '80000.00'),
(162, 161, 14, 2, 1, 'grand malt', 'half_catton', 1, 'created', '0.00', '24000.00', '24000.00');

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
  `selling_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `company_id`, `created_by`, `name`, `quantity`, `quantified`, `under_stock_reminder`, `buying_price`, `selling_price`) VALUES
(1, 1, 2, '25kgs sembe', '7.00', 'sacks', '15.00', '35000.00', '40000.00'),
(2, 1, 2, 'ngano', '13.00', 'sack', '15.00', '28000.00', '31000.00'),
(3, 1, 2, 'jamaa', '7.25', 'catton', '10.00', '35000.00', '40000.00'),
(6, 1, 2, 'santa lucia', '15.00', 'box', '7.00', '27000.00', '32000.00'),
(7, 1, 2, 'azam nazi', '25.00', 'box', '7.00', '12000.00', '15000.00'),
(8, 1, 2, 'mo xtra', '23.00', 'catton', '30.00', '4700.00', '5000.00'),
(9, 1, 2, '25kgs sukari', '30.00', 'sack', '10.00', '45000.00', '50000.00'),
(11, 1, 2, 'nice biscuit', '10.00', 'box', '10.00', '7500.00', '8000.00'),
(12, 1, 2, 'mo chungwa', '27.00', 'catton', '25.00', '4500.00', '5000.00'),
(13, 2, 14, 'santa lucia', '3.00', 'catton', '10.00', '21000.00', '30000.00'),
(14, 1, 2, 'grand malt', '1.00', 'catton', '5.00', '40000.00', '48000.00');

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
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchases_items`
--

INSERT INTO `purchases_items` (`purchase_item_id`, `company_id`, `created_by`, `product_id`, `quantity`, `buying_price`, `selling_price`, `date_made`, `total`) VALUES
(1, 1, 2, 1, 5, '35000.00', '40000.00', '2024-10-11 23:18:26', '175000.00'),
(2, 1, 2, 6, 20, '27000.00', '32000.00', '2024-11-01 15:46:43', '540000.00'),
(3, 1, 2, 11, 10, '7500.00', '8000.00', '2024-11-06 21:36:29', '75000.00'),
(4, 1, 2, 8, 15, '4700.00', '5000.00', '2024-11-29 11:00:30', '70500.00'),
(5, 1, 2, 1, 100, '35000.00', '40000.00', '2024-11-29 11:01:29', '3500000.00'),
(6, 1, 2, 9, 30, '45000.00', '50000.00', '2024-11-29 11:02:41', '1350000.00'),
(7, 1, 2, 12, 30, '4500.00', '5000.00', '2024-11-29 11:03:05', '135000.00'),
(8, 1, 2, 8, 8, '4700.00', '5000.00', '2024-11-29 11:25:57', '37600.00'),
(9, 1, 2, 1, 50, '35000.00', '40000.00', '2024-11-29 14:14:34', '1750000.00'),
(10, 1, 2, 1, 50, '35000.00', '40000.00', '2024-11-29 14:33:06', '1750000.00'),
(11, 1, 2, 1, 5, '35000.00', '40000.00', '2024-11-29 14:46:46', '175000.00'),
(12, 1, 2, 1, 10, '35000.00', '40000.00', '2024-11-29 15:17:40', '350000.00'),
(13, 1, 2, 1, 5, '35000.00', '40000.00', '2024-11-29 15:24:54', '175000.00'),
(14, 1, 2, 2, 10, '28000.00', '31000.00', '2024-12-11 17:41:10', '280000.00'),
(15, 1, 2, 3, 1, '35000.00', '40000.00', '2024-12-11 17:45:12', '35000.00'),
(16, 1, 2, 1, 1, '35000.00', '40000.00', '2024-12-11 17:46:06', '35000.00'),
(17, 1, 2, 1, 1, '35000.00', '40000.00', '2024-12-11 18:03:31', '35000.00'),
(25, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:39:54', '105000.00'),
(26, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:43:20', '105000.00'),
(28, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 03:56:39', '105000.00'),
(29, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:00:35', '105000.00'),
(30, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:02:39', '105000.00'),
(31, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:17:50', '105000.00'),
(32, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 04:18:54', '105000.00'),
(33, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 21:30:53', '105000.00'),
(34, 1, 2, 1, 3, '35000.00', '40000.00', '2025-03-07 21:54:43', '105000.00'),
(35, 1, 2, 1, 2, '35000.00', '40000.00', '2025-03-07 21:57:22', '70000.00'),
(43, 1, 2, 1, 1, '35000.00', '40000.00', '2025-03-09 10:25:37', '35000.00'),
(44, 1, 2, 6, 1, '27000.00', '32000.00', '2025-04-09 08:22:12', '27000.00'),
(45, 1, 2, 14, 1, '40000.00', '48000.00', '2025-04-10 13:38:11', '40000.00'),
(46, 1, 2, 14, 1, '40000.00', '48000.00', '2025-04-10 20:27:51', '40000.00'),
(47, 1, 2, 14, 5, '40000.00', '48000.00', '2025-04-10 22:49:10', '200000.00'),
(48, 1, 2, 1, 5, '35000.00', '40000.00', '2025-04-22 17:45:15', '175000.00');

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

INSERT INTO `quantity_destroyed` (`id`, `created_by`, `company_id`, `product_id`, `name`, `quantity_destroyed`, `date_destroyed`) VALUES
(3, 2, 1, 1, '25kgs sembe', 1, '2024-10-13 17:38:36');

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
(102, 156, 1, 2, 'sale', '24000.00', '', '2025-04-10 17:29:57'),
(103, 157, 1, 2, 'sale', '48000.00', '', '2025-04-10 17:31:06'),
(104, 158, 1, 2, 'sale', '24000.00', '', '2025-04-10 17:49:21'),
(105, 159, 1, 2, 'sale', '48000.00', '', '2025-04-10 17:50:47'),
(106, 47, 1, 2, 'purchase', '200000.00', '', '2025-04-10 19:49:10'),
(107, 160, 1, 2, 'sale', '104000.00', '', '2025-04-10 20:04:51'),
(108, 161, 1, 2, 'sale', '104000.00', '', '2025-04-10 20:04:51'),
(109, NULL, 1, 2, 'drawings', '2500.00', 'mombe', '2025-04-17 18:37:40'),
(110, NULL, 1, 2, 'expenses', '92500.00', 'rent', '2025-04-17 18:39:52'),
(111, 48, 1, 2, 'purchase', '175000.00', '', '2025-04-22 14:45:15');

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
(1, 1, 1, 2, 'kg', '25.00', '1400.00', '1600.00', '375.00'),
(2, 1, 1, 2, 'half_kg', '50.00', '700.00', '800.00', '750.00'),
(3, 1, 1, 2, 'quarter_kg', '100.00', '350.00', '400.00', '1500.00'),
(4, 3, 1, 2, 'mche', '20.00', '1750.00', '2000.00', '200.00'),
(5, 3, 1, 2, 'nusu mche', '40.00', '875.00', '1000.00', '400.00'),
(6, 13, 2, 14, 'pack', '30.00', '800.00', '1000.00', '0.00'),
(7, 14, 1, 2, 'half_catton', '2.00', '20000.00', '24000.00', '2.00'),
(8, 14, 1, 2, 'quarter_catton', '4.00', '10000.00', '12000.00', '4.00'),
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

INSERT INTO `units_destroyed` (`id`, `created_by`, `company_id`, `unit_id`, `product_id`, `name`, `units_destroyed`, `date_destroyed`) VALUES
(3, 2, 1, 1, 1, '25kgs sembe', 12, '2024-10-13 17:24:58');

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
  ADD PRIMARY KEY (`id`),
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
  ADD PRIMARY KEY (`id`),
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
-- AUTO_INCREMENT for table `methods_used`
--
ALTER TABLE `methods_used`
  MODIFY `meth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `money`
--
ALTER TABLE `money`
  MODIFY `money_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchases_items`
--
ALTER TABLE `purchases_items`
  MODIFY `purchase_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `quantity_destroyed`
--
ALTER TABLE `quantity_destroyed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `units_destroyed`
--
ALTER TABLE `units_destroyed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

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

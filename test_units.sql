-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2024 at 03:58 PM
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
(1, 1, '0.00', '305000.00', '0.00', '0.00', '0.00', '400.00', '0.00', '0.00', '0.00', '0.00', '2000.00', '0.00'),
(2, 2, '0.00', '-4859500.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `company_id` int(100) NOT NULL,
  `created_by` int(100) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `time` datetime DEFAULT current_timestamp(),
  `status` enum('created','sent','delivered','cancelled') DEFAULT 'created',
  `customer_name` varchar(255) NOT NULL,
  `payment_method` enum('cash','debt') NOT NULL,
  `debt_amount` decimal(10,2) DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `company_id`, `created_by`, `total`, `time`, `status`, `customer_name`, `payment_method`, `debt_amount`, `profit`) VALUES
(87, 1, 2, '144000.00', '2024-10-30 00:05:48', 'cancelled', 'kitic', 'cash', '0.00', '20000.00'),
(88, 1, 2, '144000.00', '2024-10-30 00:11:39', 'cancelled', 'mudathir', 'cash', '0.00', '20000.00'),
(89, 1, 2, '168000.00', '2024-10-30 00:17:27', 'created', 'chama', 'cash', '0.00', '25000.00'),
(90, 1, 2, '126000.00', '2024-10-30 13:45:00', 'sent', 'jay', 'cash', '0.00', '18750.00'),
(91, 1, 2, '31000.00', '2024-10-31 10:36:36', 'created', 'messi', 'debt', '20000.00', '3000.00'),
(92, 1, 2, '198000.00', '2024-10-31 21:07:59', 'delivered', 'kiss', 'cash', '0.00', '26000.00'),
(93, 1, 2, '83200.00', '2024-10-31 23:02:36', 'created', 'mendes', 'cash', '0.00', '12400.00'),
(94, 1, 2, '105200.00', '2024-11-04 23:14:14', 'created', 'mimi', 'cash', '0.00', '11400.00'),
(95, 1, 2, '40000.00', '2024-11-06 22:38:38', 'created', 'lov', 'cash', '0.00', '5000.00'),
(96, 1, 11, '32000.00', '2024-11-16 10:46:42', 'created', 'mimi', 'cash', '0.00', '5000.00'),
(97, 2, 14, '90000.00', '2024-11-16 10:56:21', 'created', 'messi', 'cash', '0.00', '27000.00'),
(100, 1, 2, '40000.00', '2024-11-18 22:13:35', 'created', 'hanama', 'cash', '0.00', '5000.00'),
(101, 1, 2, '40000.00', '2024-11-21 20:10:29', 'created', 'kaka', '', '40000.00', '5000.00'),
(102, 1, 2, '72000.00', '2024-11-21 20:51:23', 'created', 'ki aziz', '', '0.00', '10000.00'),
(103, 1, 2, '95000.00', '2024-11-29 11:04:08', 'created', 'mengelee', 'cash', '95000.00', '11500.00'),
(104, 1, 2, '40000.00', '2024-11-29 15:15:51', 'created', 'messi', 'cash', '40000.00', '5000.00'),
(105, 1, 2, '40000.00', '2024-11-29 15:16:32', 'created', 'messi', 'cash', '40000.00', '5000.00'),
(106, 1, 2, '40000.00', '2024-11-29 15:17:09', 'created', 'messi', 'cash', '40000.00', '5000.00'),
(107, 1, 2, '40000.00', '2024-11-29 17:55:11', 'created', 'messi', 'cash', '40000.00', '5000.00');

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
(55, 87, 6, 2, 1, 'santa lucia', 'whole', 2, 'cancelled', '27000.00', '32000.00', '64000.00'),
(56, 87, 3, 2, 1, 'jamaa', 'whole', 2, 'cancelled', '35000.00', '40000.00', '80000.00'),
(57, 88, 6, 2, 1, 'santa lucia', 'whole', 2, 'cancelled', '27000.00', '32000.00', '64000.00'),
(58, 88, 3, 2, 1, 'jamaa', 'whole', 2, 'cancelled', '35000.00', '40000.00', '80000.00'),
(59, 89, 1, 2, 1, '25kgs sembe (kg)', 'kg', 25, 'created', '1400.00', '1600.00', '32000.00'),
(60, 89, 6, 2, 1, 'santa lucia', 'whole', 4, 'created', '27000.00', '32000.00', '64000.00'),
(61, 90, 3, 2, 1, 'jamaa (mche)', 'mche', 15, 'sent', '1750.00', '2000.00', '20000.00'),
(62, 90, 6, 2, 1, 'santa lucia', 'whole', 3, 'sent', '27000.00', '32000.00', '96000.00'),
(64, 91, 2, 2, 1, 'ngano', 'whole', 1, 'created', '28000.00', '31000.00', '62000.00'),
(66, 92, 2, 2, 1, 'ngano', 'whole', 2, 'delivered', '28000.00', '31000.00', '62000.00'),
(67, 92, 6, 2, 1, 'santa lucia', 'whole', 3, 'delivered', '27000.00', '32000.00', '64000.00'),
(68, 92, 1, 2, 1, '25kgs sembe', 'whole', 1, 'delivered', '35000.00', '40000.00', '40000.00'),
(69, 93, 1, 2, 1, '25kgs sembe (kg)', 'kg', 12, 'created', '1400.00', '1600.00', '19200.00'),
(70, 93, 6, 2, 1, 'santa lucia', 'whole', 2, 'created', '27000.00', '32000.00', '64000.00'),
(71, 94, 2, 2, 1, 'ngano', 'whole', 2, 'created', '28000.00', '31000.00', '62000.00'),
(72, 94, 3, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(73, 94, 1, 2, 1, '25kgs sembe (kg)', 'kg', 2, 'created', '1400.00', '1600.00', '3200.00'),
(74, 95, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(77, 96, 6, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(78, 97, 13, 14, 2, 'santa lucia', 'whole', 3, 'created', '21000.00', '30000.00', '60000.00'),
(79, 100, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(80, 101, 3, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(81, 102, 3, 2, 1, 'jamaa', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(82, 102, 6, 2, 1, 'santa lucia', 'whole', 1, 'created', '27000.00', '32000.00', '32000.00'),
(83, 103, 1, 2, 1, '25kgs sembe', 'whole', 2, 'created', '35000.00', '40000.00', '80000.00'),
(84, 103, 12, 2, 1, 'mo chungwa', 'whole', 3, 'created', '4500.00', '5000.00', '15000.00'),
(85, 104, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(86, 105, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(87, 106, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00'),
(88, 107, 1, 2, 1, '25kgs sembe', 'whole', 1, 'created', '35000.00', '40000.00', '40000.00');

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
(1, 2, 1, 'yes', 'yes', 'no', 'no', 'yes', 'no', 'no', 'no', 'no', 'yes'),
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
(1, 1, 2, '25kgs sembe', '16.00', 'sacks', '15.00', '35000.00', '40000.00'),
(2, 1, 2, 'ngano', '5.00', 'sack', '15.00', '28000.00', '31000.00'),
(3, 1, 2, 'jamaa', '11.25', 'catton', '10.00', '35000.00', '40000.00'),
(6, 1, 2, 'santa lucia', '20.00', 'box', '7.00', '27000.00', '32000.00'),
(7, 1, 2, 'azam nazi', '25.00', 'box', '7.00', '12000.00', '15000.00'),
(8, 1, 2, 'mo xtra', '23.00', 'catton', '30.00', '4700.00', '5000.00'),
(9, 1, 2, '25kgs sukari', '30.00', 'sack', '10.00', '45000.00', '50000.00'),
(11, 1, 2, 'nice biscuit', '10.00', 'box', '10.00', '7500.00', '8000.00'),
(12, 1, 2, 'mo chungwa', '27.00', 'catton', '25.00', '4500.00', '5000.00'),
(13, 2, 14, 'santa lucia', '-3.00', 'catton', '10.00', '21000.00', '30000.00');

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
  `item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `buying_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `date_made` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchases_items`
--

INSERT INTO `purchases_items` (`item_id`, `product_id`, `product_name`, `quantity`, `buying_price`, `selling_price`, `date_made`, `total`) VALUES
(1, 1, '25kgs sembe', 5, '35000.00', '40000.00', '2024-10-11 23:18:26', '175000.00'),
(2, 6, 'santa lucia', 20, '27000.00', '32000.00', '2024-11-01 15:46:43', '540000.00'),
(3, 11, 'nice biscuit', 10, '7500.00', '8000.00', '2024-11-06 21:36:29', '75000.00'),
(4, 8, 'mo xtra', 15, '4700.00', '5000.00', '2024-11-29 11:00:30', '70500.00'),
(5, 1, '25kgs sembe', 100, '35000.00', '40000.00', '2024-11-29 11:01:29', '3500000.00'),
(6, 9, '25kgs sukari', 30, '45000.00', '50000.00', '2024-11-29 11:02:41', '1350000.00'),
(7, 12, 'mo chungwa', 30, '4500.00', '5000.00', '2024-11-29 11:03:05', '135000.00'),
(8, 8, 'mo xtra', 8, '4700.00', '5000.00', '2024-11-29 11:25:57', '37600.00'),
(9, 1, '25kgs sembe', 50, '35000.00', '40000.00', '2024-11-29 14:14:34', '1750000.00'),
(10, 1, '25kgs sembe', 50, '35000.00', '40000.00', '2024-11-29 14:33:06', '1750000.00'),
(11, 1, '25kgs sembe', 5, '35000.00', '40000.00', '2024-11-29 14:46:46', '175000.00'),
(12, 1, '25kgs sembe', 10, '35000.00', '40000.00', '2024-11-29 15:17:40', '350000.00'),
(13, 1, '25kgs sembe', 5, '35000.00', '40000.00', '2024-11-29 15:24:54', '175000.00');

-- --------------------------------------------------------

--
-- Table structure for table `quantity_destroyed`
--

CREATE TABLE `quantity_destroyed` (
  `id` int(11) NOT NULL,
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

INSERT INTO `transactions` (`transaction_id`, `company_id`, `created_by`, `transaction_type`, `amount`, `description`, `date_made`) VALUES
(1, 1, 2, 'drawings', '5000.00', 'dew', '2024-10-12 06:43:47'),
(2, 1, 2, 'drawings', '5000.00', 'dew', '2024-10-12 06:45:03'),
(3, 1, 2, 'drawings', '5000.00', 'me', '2024-10-12 06:45:54'),
(4, 1, 2, 'drawings', '5000.00', 'mendes', '2024-10-12 06:53:17'),
(5, 1, 2, 'drawings', '5000.00', 'ki', '2024-10-12 07:07:49'),
(6, 1, 2, 'add_capital', '50000.00', 'ha', '2024-10-12 07:10:45'),
(7, 1, 2, 'add_capital', '50000.00', 'ha', '2024-10-07 07:45:16'),
(8, 1, 2, 'add_capital', '400000.00', 'salary', '2024-11-02 20:53:38'),
(9, 1, 2, 'drawings', '100000.00', 'outings', '2024-11-04 11:02:33'),
(10, 1, 2, 'drawings', '51000.00', 'kamala', '2024-11-16 17:33:58'),
(11, 1, 2, 'add_capital', '5000000.00', 'adding', '2024-11-29 08:02:04'),
(12, 1, 2, 'sale', '95000.00', 'mengelee', '2024-11-29 08:04:08'),
(13, 1, 2, 'drawings', '2000.00', 'mende', '2024-11-29 10:06:47'),
(16, 1, 2, 'purchase', '1750000.00', '25kgs sembe', '2024-11-29 11:14:34'),
(17, 1, 2, 'purchase', '1750000.00', '25kgs sembe', '2024-11-29 11:33:06'),
(18, 1, 2, 'add_capital', '3000000.00', 'db', '2024-11-29 11:33:46'),
(19, 1, 2, 'purchase', '175000.00', '25kgs sembe', '2024-11-29 11:46:46'),
(20, 1, 2, 'add_capital', '300000.00', 'add', '2024-11-29 11:48:01'),
(21, 1, 2, 'drawings', '4500.00', 'messi', '2024-11-29 12:09:50'),
(22, 1, 2, 'sale', '40000.00', 'messi', '2024-11-29 12:15:51'),
(23, 1, 2, 'sale', '40000.00', 'messi', '2024-11-29 12:16:32'),
(24, 1, 2, 'sale', '40000.00', 'messi', '2024-11-29 12:17:10'),
(25, 1, 2, 'purchase', '350000.00', '25kgs sembe', '2024-11-29 12:17:40'),
(26, 1, 2, 'add_capital', '500000.00', 'mme', '2024-11-29 12:17:56'),
(27, 1, 2, 'purchase', '175000.00', '25kgs sembe', '2024-11-29 12:24:54'),
(28, 1, 2, 'sale', '40000.00', 'messi', '2024-11-29 14:55:11');

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
(1, 1, 1, 2, 'kg', '25.00', '1400.00', '1600.00', '400.00'),
(2, 1, 1, 2, 'half_kg', '50.00', '700.00', '800.00', '800.00'),
(3, 1, 1, 2, 'quarter_kg', '100.00', '350.00', '400.00', '1600.00'),
(4, 3, 1, 2, 'mche', '20.00', '1750.00', '2000.00', '220.00'),
(5, 3, 1, 2, 'nusu mche', '40.00', '875.00', '1000.00', '440.00'),
(6, 13, 2, 14, 'pack', '30.00', '800.00', '1000.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `units_destroyed`
--

CREATE TABLE `units_destroyed` (
  `id` int(11) NOT NULL,
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
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT for table `money`
--
ALTER TABLE `money`
  MODIFY `money_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchases_items`
--
ALTER TABLE `purchases_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `purchases_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

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

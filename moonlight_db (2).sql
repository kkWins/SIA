-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2024 at 09:02 AM
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
-- Database: `moonlight_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `DEPT_ID` int(11) NOT NULL,
  `DEPT_NAME` varchar(255) NOT NULL,
  `DEPT_DESC` varchar(255) NOT NULL,
  `EMP_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPT_ID`, `DEPT_NAME`, `DEPT_DESC`, `EMP_ID`) VALUES
(1, 'Finance', 'Finance department description lorem', 1),
(2, 'Inventory', 'Inventory department description lorem', 2),
(3, 'Labor', 'Labor Department Description Lorem', 3);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EMP_ID` int(11) NOT NULL,
  `EMP_FNAME` varchar(50) NOT NULL,
  `EMP_LNAME` varchar(50) NOT NULL,
  `EMP_MNAME` varchar(30) NOT NULL,
  `EMP_ADDRESS` varchar(255) NOT NULL,
  `EMP_EMAIL` varchar(255) NOT NULL,
  `EMP_NUMBER` int(11) NOT NULL,
  `EMP_POSITION` varchar(255) NOT NULL,
  `EMP_STATUS` varchar(255) NOT NULL,
  `EMP_PASSWORD` varchar(12) NOT NULL,
  `DEPT_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EMP_ID`, `EMP_FNAME`, `EMP_LNAME`, `EMP_MNAME`, `EMP_ADDRESS`, `EMP_EMAIL`, `EMP_NUMBER`, `EMP_POSITION`, `EMP_STATUS`, `EMP_PASSWORD`, `DEPT_ID`) VALUES
(1, 'Dime', 'Darrell', 'Bag', 'QWE RELOCATION, EWQ CITY', 'dimebag@gmail.com', 1, 'Manager', '1', '123456789', 1),
(2, 'Kirk', 'Reaper', 'Hammett', 'ASD RELOCATION, DSA CITY', 'kirkreaper@gmail.com', 2, 'Staff', '0', '123456789', 1),
(3, 'Lebron', 'Semaj', 'Lakers', 'ZXC RELOCATION, CZX CITY', 'lebronsemaj@gmail.com', 3, 'Manager', '1', '123456789', 2),
(4, 'Stepping', 'Curry', 'Golden', 'RTY RELOCATION, YTR CITY', 'steppingcurry@gmail.com', 4, 'Staff', '1', '123456789', 2),
(5, 'Pancit', 'Canton', 'Hang', 'FGH RELOCATION, HGF CITY', 'pancithang@gmail.com', 5, 'Manager', '0', '123456789', 3),
(6, 'Ham', 'Bur', 'Ger', 'VBN RELOCATION, NVB CITY', 'burger@gmail.com', 6, 'Staff', '0', '123456789', 3);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `INV_ID` int(11) NOT NULL,
  `INV_QUANTITY` int(11) NOT NULL,
  `INV_MODEL_NAME` varchar(255) NOT NULL,
  `INV_BRAND` varchar(255) NOT NULL,
  `INV_LOCATION` varchar(255) NOT NULL,
  `INV_DATE_CREATED` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`INV_ID`, `INV_QUANTITY`, `INV_MODEL_NAME`, `INV_BRAND`, `INV_LOCATION`, `INV_DATE_CREATED`) VALUES
(1, 123, 'Pro max ultra gamma', 'Totoya', 'f3', '0000-00-00'),
(2, 123, 'Galaxy Stars Wow', '123', '123', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `item_list`
--

CREATE TABLE `item_list` (
  `IT_ID` int(11) NOT NULL,
  `IT_QUANTITY` int(11) NOT NULL,
  `IT_DATE` date NOT NULL,
  `IT_DESCRIPTION` varchar(255) NOT NULL,
  `INV_ID` int(11) NOT NULL,
  `PR_ID` int(11) NOT NULL,
  `RF_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_list`
--

INSERT INTO `item_list` (`IT_ID`, `IT_QUANTITY`, `IT_DATE`, `IT_DESCRIPTION`, `INV_ID`, `PR_ID`, `RF_ID`) VALUES
(1, 123, '2024-12-20', '123', 1, 13, 0),
(2, 22, '2024-12-21', '22', 2, 14, 0),
(3, 123, '2024-12-21', '123', 1, 14, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `PD_ID` int(11) NOT NULL,
  `PD_SUPPLIER_NAME` varchar(255) NOT NULL,
  `PD_PAYMENT_TYPE` varchar(50) NOT NULL,
  `PD_AMMOUNT` int(11) NOT NULL,
  `PORF_ID` int(11) NOT NULL,
  `EMP_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_deposit`
--

CREATE TABLE `po_deposit` (
  `DP_ID` int(11) NOT NULL,
  `DP_QUANTITY` int(11) NOT NULL,
  `DP_DATE` date NOT NULL,
  `DP_DESCRIPTION` varchar(255) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `RF_ID` int(11) NOT NULL,
  `INV_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `PO_ID` int(11) NOT NULL,
  `PO_DATE` date NOT NULL,
  `PO_QUANTITY` int(11) NOT NULL,
  `PO_SUPPLIER_NAME` varchar(255) NOT NULL,
  `PO_SUPPLIER_ADDRESS` varchar(255) NOT NULL,
  `PO_STATUS` varchar(50) NOT NULL,
  `INV_ID` int(11) NOT NULL,
  `PRF_ID` int(11) NOT NULL,
  `EMP_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_or_requisition_form`
--

CREATE TABLE `purchase_or_requisition_form` (
  `PRF_ID` int(11) NOT NULL,
  `PRF_DATE` date NOT NULL,
  `PRF_STATUS` varchar(255) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `AP_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_or_requisition_form`
--

INSERT INTO `purchase_or_requisition_form` (`PRF_ID`, `PRF_DATE`, `PRF_STATUS`, `EMP_ID`, `AP_ID`) VALUES
(13, '2024-12-20', 'Pending', 2, 0),
(14, '2024-12-21', 'Pending', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rf_withdrawal`
--

CREATE TABLE `rf_withdrawal` (
  `WD_ID` int(11) NOT NULL,
  `WD_QUANTITY` int(11) NOT NULL,
  `WD_DATE` date NOT NULL,
  `WD_DESCRIPTION` varchar(255) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `PRF_ID` int(11) NOT NULL,
  `INV_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal`
--

CREATE TABLE `withdrawal` (
  `WDL_ID` int(11) NOT NULL,
  `WDL_QUANTITY` int(11) NOT NULL,
  `WDL_DATE` date NOT NULL,
  `WDL_REASON` varchar(255) NOT NULL,
  `EMP_ID` int(11) NOT NULL,
  `INV_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`DEPT_ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EMP_ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`INV_ID`);

--
-- Indexes for table `item_list`
--
ALTER TABLE `item_list`
  ADD PRIMARY KEY (`IT_ID`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`PD_ID`);

--
-- Indexes for table `po_deposit`
--
ALTER TABLE `po_deposit`
  ADD PRIMARY KEY (`DP_ID`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`PO_ID`);

--
-- Indexes for table `purchase_or_requisition_form`
--
ALTER TABLE `purchase_or_requisition_form`
  ADD PRIMARY KEY (`PRF_ID`);

--
-- Indexes for table `rf_withdrawal`
--
ALTER TABLE `rf_withdrawal`
  ADD PRIMARY KEY (`WD_ID`);

--
-- Indexes for table `withdrawal`
--
ALTER TABLE `withdrawal`
  ADD PRIMARY KEY (`WDL_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `DEPT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `EMP_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `INV_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `item_list`
--
ALTER TABLE `item_list`
  MODIFY `IT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `PD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_deposit`
--
ALTER TABLE `po_deposit`
  MODIFY `DP_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `PO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_or_requisition_form`
--
ALTER TABLE `purchase_or_requisition_form`
  MODIFY `PRF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rf_withdrawal`
--
ALTER TABLE `rf_withdrawal`
  MODIFY `WD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawal`
--
ALTER TABLE `withdrawal`
  MODIFY `WDL_ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

	/* **********************************************************************
	* Artemis version 1.0													*
	*************************************************************************
	* Copyright (c) 2007-2018 Ricky Kurniawan ( FireSnakeR )				*
	*************************************************************************
	* This file is part of Artemis.											*
	*																		*
    * Artemis is free software: you can redistribute it and/or modify		*
    * it under the terms of the GNU General Public License as published by	*
    * the Free Software Foundation, either version 3 of the License, or		*
    * (at your option) any later version.									*
	*																		*
    * Artemis is distributed in the hope that it will be useful,			*
    * but WITHOUT ANY WARRANTY; without even the implied warranty of		*
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the			*
    * GNU General Public License for more details.							*
	*																		*
    * You should have received a copy of the GNU General Public License		*
    * along with Artemis.  If not, see <http://www.gnu.org/licenses/>.		*
    * 																		*
    ********************************************************************** */

-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 15, 2015 at 04:24 PM
-- Server version: 5.5.44-0+deb8u1
-- PHP Version: 5.6.9-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `FSR_artemis`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE IF NOT EXISTS `bank` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bank_deposit`
--

CREATE TABLE IF NOT EXISTS `bank_deposit` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `bank_ID` int(10) unsigned NOT NULL,
  `salesPayment_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `Notes` varchar(255) NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Price` double(20,2) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=442 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE IF NOT EXISTS `client` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=985 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clientOutlet`
--

CREATE TABLE IF NOT EXISTS `clientOutlet` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `client_ID` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=153 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deposit`
--

CREATE TABLE IF NOT EXISTS `deposit` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `salesPayment_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `Notes` varchar(255) NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Price` double(20,2) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=3204 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE IF NOT EXISTS `employee` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `employeeOutlet`
--

CREATE TABLE IF NOT EXISTS `employeeOutlet` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `employee_ID` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=188 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE IF NOT EXISTS `expenses` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `expenses_category_ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Notes` text NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Price` double(20,2) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=5368 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `expenses_category`
--

CREATE TABLE IF NOT EXISTS `expenses_category` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios`
--

CREATE TABLE IF NOT EXISTS `mkios` (
`ID` int(10) unsigned NOT NULL,
  `KodeWH` varchar(255) NOT NULL,
  `KodeSales` varchar(255) NOT NULL,
  `CustomerGroup` varchar(255) NOT NULL,
  `NamaCust` varchar(255) NOT NULL,
  `TxPeriod` date NOT NULL,
  `KodeTerminal` varchar(255) NOT NULL,
  `NoHP` varchar(255) NOT NULL,
  `Subtotal` float(20,2) NOT NULL,
  `S005` int(11) NOT NULL,
  `S010` int(11) NOT NULL,
  `S020` int(11) NOT NULL,
  `S025` int(11) NOT NULL,
  `S050` int(11) NOT NULL,
  `S100` int(11) NOT NULL,
  `VTS_DocNumber` varchar(255) NOT NULL,
  `TxPeriodText` text NOT NULL,
  `SubtotalText` text NOT NULL,
  `FinanceStatus` tinyint(4) NOT NULL COMMENT '1=verified',
  `FinanceNotes` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=101709 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios_bank_deposit`
--

CREATE TABLE IF NOT EXISTS `mkios_bank_deposit` (
`ID` int(10) unsigned NOT NULL,
  `mkios_payment_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `bank_ID` int(10) unsigned NOT NULL,
  `Notes` varchar(255) NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Price` double(20,2) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=2640 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios_deposit`
--

CREATE TABLE IF NOT EXISTS `mkios_deposit` (
`ID` int(10) unsigned NOT NULL,
  `mkios_payment_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `Notes` varchar(255) NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Price` double(20,2) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=2158 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios_payment`
--

CREATE TABLE IF NOT EXISTS `mkios_payment` (
`ID` int(10) unsigned NOT NULL,
  `mkios_ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Amount` float(20,4) unsigned NOT NULL,
  `Notes` text NOT NULL,
  `IsCash` tinyint(3) unsigned NOT NULL,
  `bank_ID` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=4747 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios_price`
--

CREATE TABLE IF NOT EXISTS `mkios_price` (
`ID` int(10) unsigned NOT NULL,
  `Type` tinyint(4) NOT NULL COMMENT '0 = Buy or 1 = Sell',
  `S005` int(10) unsigned NOT NULL,
  `S010` int(10) unsigned NOT NULL,
  `S020` int(10) unsigned NOT NULL,
  `S025` int(10) unsigned NOT NULL,
  `S050` int(10) unsigned NOT NULL,
  `S100` int(10) unsigned NOT NULL,
  `EffectiveDate` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mkios_purchase`
--

CREATE TABLE IF NOT EXISTS `mkios_purchase` (
`ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `S005` int(10) unsigned NOT NULL,
  `S010` int(10) unsigned NOT NULL,
  `S020` int(10) unsigned NOT NULL,
  `S025` int(10) unsigned NOT NULL,
  `S050` int(10) unsigned NOT NULL,
  `S100` int(10) unsigned NOT NULL,
  `Notes` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=171 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
`ID` int(10) unsigned NOT NULL,
  `Description` text NOT NULL,
  `Created` datetime NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `outlet`
--

CREATE TABLE IF NOT EXISTS `outlet` (
`ID` int(10) unsigned NOT NULL,
  `master_outlet_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(4) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Address` text NOT NULL,
  `Phone` varchar(64) NOT NULL,
  `Fax` varchar(64) NOT NULL,
  `Status` int(11) NOT NULL,
  `Viewable` tinyint(4) NOT NULL,
  `AllowPurchase` tinyint(3) unsigned NOT NULL,
  `Deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Created` datetime NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `paymentType`
--

CREATE TABLE IF NOT EXISTS `paymentType` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `IsCash` tinyint(1) NOT NULL DEFAULT '0',
  `PLNoCount` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
`ID` int(10) unsigned NOT NULL,
  `productCategory_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `Name` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `Price` double(20,2) NOT NULL,
  `Description` text COLLATE latin1_general_ci NOT NULL,
  `Image` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `Thumbs` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `Driver` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `Viewable` tinyint(1) NOT NULL DEFAULT '0',
  `ViewPriority` tinyint(3) unsigned NOT NULL DEFAULT '99',
  `Deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Created` datetime NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=254 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

CREATE TABLE IF NOT EXISTS `product_category` (
`ID` int(10) unsigned NOT NULL,
  `parent_ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `Created` datetime NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE IF NOT EXISTS `purchase` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `paymentType_ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Notes` text NOT NULL,
  `VerifyNotes` text NOT NULL,
  `Status` int(11) NOT NULL,
  `verified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Created` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2001 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_detail`
--

CREATE TABLE IF NOT EXISTS `purchase_detail` (
`ID` int(10) unsigned NOT NULL,
  `purchase_ID` int(10) unsigned NOT NULL,
  `product_ID` int(10) unsigned NOT NULL,
  `Quantity` int(10) unsigned NOT NULL,
  `Price` double(20,4) NOT NULL,
  `SnStart` text NOT NULL,
  `SnEnd` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=5230 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE IF NOT EXISTS `sales` (
`ID` int(10) unsigned NOT NULL,
  `number` varchar(16) NOT NULL,
  `sales_order_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `outlet_ID` int(10) unsigned NOT NULL,
  `employee_ID` int(10) unsigned NOT NULL,
  `client_ID` int(10) unsigned NOT NULL,
  `paymentType_ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Notes` text NOT NULL,
  `FinanceNotes` text NOT NULL,
  `ajaxPostID` varchar(255) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `IsPaid` tinyint(3) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=20859 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales_detail`
--

CREATE TABLE IF NOT EXISTS `sales_detail` (
`ID` int(10) unsigned NOT NULL,
  `sales_order_detail_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `sales_ID` int(10) unsigned NOT NULL,
  `product_ID` int(10) unsigned NOT NULL,
  `Quantity` int(10) unsigned NOT NULL,
  `Discount` double(10,4) unsigned NOT NULL,
  `Price` double(20,4) unsigned NOT NULL,
  `ajaxPostID` varchar(255) NOT NULL,
  `SnStart` text NOT NULL,
  `SnEnd` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=36328 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order`
--

CREATE TABLE IF NOT EXISTS `sales_order` (
`ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL,
  `employee_ID` int(10) unsigned NOT NULL,
  `client_ID` int(10) unsigned NOT NULL,
  `paymentType_ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Notes` text NOT NULL,
  `FinanceNotes` text NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_detail`
--

CREATE TABLE IF NOT EXISTS `sales_order_detail` (
`ID` int(10) unsigned NOT NULL,
  `sales_order_ID` int(10) unsigned NOT NULL,
  `product_ID` int(10) unsigned NOT NULL,
  `Quantity` int(10) unsigned NOT NULL,
  `Discount` double(10,4) unsigned NOT NULL,
  `Price` double(20,4) unsigned NOT NULL,
  `SnStart` text NOT NULL,
  `SnEnd` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales_payment`
--

CREATE TABLE IF NOT EXISTS `sales_payment` (
`ID` int(10) unsigned NOT NULL,
  `sales_ID` int(10) unsigned NOT NULL,
  `Date` date NOT NULL,
  `Amount` float(20,4) unsigned NOT NULL,
  `Notes` text NOT NULL,
  `IsCash` tinyint(3) unsigned NOT NULL,
  `bank_ID` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1448 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE IF NOT EXISTS `supplier` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transfer`
--

CREATE TABLE IF NOT EXISTS `transfer` (
`ID` int(10) unsigned NOT NULL,
  `From_outlet_ID` int(10) unsigned NOT NULL,
  `To_outlet_ID` int(10) unsigned NOT NULL,
  `Notes` varchar(255) NOT NULL,
  `Date` date NOT NULL,
  `Status` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=11742 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transferOutlet`
--

CREATE TABLE IF NOT EXISTS `transferOutlet` (
`ID` int(10) unsigned NOT NULL,
  `From_outlet_ID` int(10) unsigned NOT NULL,
  `To_outlet_ID` int(10) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=243 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_detail`
--

CREATE TABLE IF NOT EXISTS `transfer_detail` (
`ID` int(10) unsigned NOT NULL,
  `transfer_ID` int(10) unsigned NOT NULL,
  `product_ID` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `SnStart` text NOT NULL,
  `SnEnd` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=19160 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`ID` int(10) unsigned NOT NULL,
  `Name` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `Level` enum('1','2','3') COLLATE latin1_general_ci NOT NULL,
  `IsFinance` int(10) unsigned NOT NULL DEFAULT '0',
  `outlet_ID` int(10) unsigned NOT NULL,
  `Username` varchar(16) COLLATE latin1_general_ci NOT NULL,
  `Password` varchar(64) COLLATE latin1_general_ci NOT NULL,
  `Email` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `Created` datetime NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=218 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userOutlet`
--

CREATE TABLE IF NOT EXISTS `userOutlet` (
`ID` int(10) unsigned NOT NULL,
  `user_ID` int(10) unsigned NOT NULL,
  `outlet_ID` int(10) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `verification`
--

CREATE TABLE IF NOT EXISTS `verification` (
`id` int(10) unsigned NOT NULL,
  `invoice_type` tinyint(3) unsigned NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `notes` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `VTS_Transactions`
--

CREATE TABLE IF NOT EXISTS `VTS_Transactions` (
  `ID` int(10) unsigned NOT NULL,
  `KodeWH` varchar(255) NOT NULL,
  `KodeSales` varchar(255) NOT NULL,
  `CustomerGroup` varchar(255) NOT NULL,
  `KodeCustomer` varchar(255) NOT NULL,
  `NamaCustomer` varchar(255) NOT NULL,
  `TxPeriod` date NOT NULL,
  `NoHP` varchar(255) NOT NULL,
  `KodeBarang` varchar(255) NOT NULL,
  `Jumlah` int(10) unsigned NOT NULL,
  `Harga` float(20,2) NOT NULL,
  `KodeTerminal` int(10) unsigned NOT NULL,
  `DocNumber` varchar(255) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `StatusTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `bank_deposit`
--
ALTER TABLE `bank_deposit`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `clientOutlet`
--
ALTER TABLE `clientOutlet`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `deposit`
--
ALTER TABLE `deposit`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `employeeOutlet`
--
ALTER TABLE `employeeOutlet`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `expenses_category`
--
ALTER TABLE `expenses_category`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios`
--
ALTER TABLE `mkios`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios_bank_deposit`
--
ALTER TABLE `mkios_bank_deposit`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios_deposit`
--
ALTER TABLE `mkios_deposit`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios_payment`
--
ALTER TABLE `mkios_payment`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios_price`
--
ALTER TABLE `mkios_price`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `mkios_purchase`
--
ALTER TABLE `mkios_purchase`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `outlet`
--
ALTER TABLE `outlet`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `paymentType`
--
ALTER TABLE `paymentType`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product_category`
--
ALTER TABLE `product_category`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `purchase_detail`
--
ALTER TABLE `purchase_detail`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sales_detail`
--
ALTER TABLE `sales_detail`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sales_order`
--
ALTER TABLE `sales_order`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sales_order_detail`
--
ALTER TABLE `sales_order_detail`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sales_payment`
--
ALTER TABLE `sales_payment`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transfer`
--
ALTER TABLE `transfer`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `transferOutlet`
--
ALTER TABLE `transferOutlet`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `transfer_detail`
--
ALTER TABLE `transfer_detail`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `userOutlet`
--
ALTER TABLE `userOutlet`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `verification`
--
ALTER TABLE `verification`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `VTS_Transactions`
--
ALTER TABLE `VTS_Transactions`
 ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `bank_deposit`
--
ALTER TABLE `bank_deposit`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=442;
--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=985;
--
-- AUTO_INCREMENT for table `clientOutlet`
--
ALTER TABLE `clientOutlet`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=153;
--
-- AUTO_INCREMENT for table `deposit`
--
ALTER TABLE `deposit`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3204;
--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=135;
--
-- AUTO_INCREMENT for table `employeeOutlet`
--
ALTER TABLE `employeeOutlet`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=188;
--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5368;
--
-- AUTO_INCREMENT for table `expenses_category`
--
ALTER TABLE `expenses_category`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `mkios`
--
ALTER TABLE `mkios`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=101709;
--
-- AUTO_INCREMENT for table `mkios_bank_deposit`
--
ALTER TABLE `mkios_bank_deposit`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2640;
--
-- AUTO_INCREMENT for table `mkios_deposit`
--
ALTER TABLE `mkios_deposit`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2158;
--
-- AUTO_INCREMENT for table `mkios_payment`
--
ALTER TABLE `mkios_payment`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4747;
--
-- AUTO_INCREMENT for table `mkios_price`
--
ALTER TABLE `mkios_price`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `mkios_purchase`
--
ALTER TABLE `mkios_purchase`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=171;
--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `outlet`
--
ALTER TABLE `outlet`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=86;
--
-- AUTO_INCREMENT for table `paymentType`
--
ALTER TABLE `paymentType`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=254;
--
-- AUTO_INCREMENT for table `product_category`
--
ALTER TABLE `product_category`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2001;
--
-- AUTO_INCREMENT for table `purchase_detail`
--
ALTER TABLE `purchase_detail`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5230;
--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20859;
--
-- AUTO_INCREMENT for table `sales_detail`
--
ALTER TABLE `sales_detail`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=36328;
--
-- AUTO_INCREMENT for table `sales_order`
--
ALTER TABLE `sales_order`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT for table `sales_order_detail`
--
ALTER TABLE `sales_order_detail`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=36;
--
-- AUTO_INCREMENT for table `sales_payment`
--
ALTER TABLE `sales_payment`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1448;
--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `transfer`
--
ALTER TABLE `transfer`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11742;
--
-- AUTO_INCREMENT for table `transferOutlet`
--
ALTER TABLE `transferOutlet`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=243;
--
-- AUTO_INCREMENT for table `transfer_detail`
--
ALTER TABLE `transfer_detail`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19160;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=218;
--
-- AUTO_INCREMENT for table `userOutlet`
--
ALTER TABLE `userOutlet`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT for table `verification`
--
ALTER TABLE `verification`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=152;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

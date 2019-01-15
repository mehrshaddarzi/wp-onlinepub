-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 15, 2019 at 10:24 AM
-- Server version: 5.6.41
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `onlinepu_order`
--

-- --------------------------------------------------------

--
-- Table structure for table `z_factor`
--

CREATE TABLE `z_factor` (
  `id` bigint(48) NOT NULL,
  `user_id` bigint(48) NOT NULL,
  `order_id` bigint(48) NOT NULL,
  `date` datetime NOT NULL,
  `type` int(3) NOT NULL COMMENT '1=pishfactor/ 2=factor',
  `payment_status` int(3) NOT NULL COMMENT '1=nashode/2=shode',
  `price` bigint(48) NOT NULL COMMENT 'مبلغ قابل پرداخت',
  `discount_percent` int(10) NOT NULL COMMENT 'درصد تخفیف',
  `price_main` int(48) NOT NULL COMMENT 'قیمت بدون تخفیف',
  `read_user` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `z_factor_item`
--

CREATE TABLE `z_factor_item` (
  `id` bigint(48) NOT NULL,
  `factor_id` bigint(48) NOT NULL,
  `item` text CHARACTER SET utf8 NOT NULL,
  `price` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `z_order`
--

CREATE TABLE `z_order` (
  `id` bigint(48) NOT NULL,
  `user_id` bigint(48) NOT NULL,
  `date` datetime NOT NULL,
  `entry_id` bigint(48) NOT NULL,
  `form_id` bigint(48) NOT NULL,
  `title` text CHARACTER SET utf8 NOT NULL COMMENT 'عنوان سفارش گرفته شده از گراویتی',
  `status` int(10) NOT NULL COMMENT '-در حال بررسی اولیه -صدور پیش فاکتور تایید پردخت  - تایید انجام سفارش -در حال انجام کار - ارسال برای بازبینی - صدور فاکتور تایید واریز  - اتمام پروژه'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `z_payment`
--

CREATE TABLE `z_payment` (
  `id` bigint(48) NOT NULL,
  `user_id` bigint(48) NOT NULL,
  `type` int(2) NOT NULL COMMENT '1=online/2=fishbanki',
  `status` int(2) NOT NULL COMMENT '1=nashode/2=shode',
  `factor_id` bigint(48) NOT NULL,
  `price` int(20) NOT NULL,
  `date` datetime NOT NULL,
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `z_ticket`
--

CREATE TABLE `z_ticket` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `create_date` datetime NOT NULL,
  `title` text NOT NULL,
  `comment` text NOT NULL,
  `sender` varchar(255) NOT NULL COMMENT 'admin | user',
  `read_admin` int(10) NOT NULL COMMENT '0noread |1=read',
  `read_user` int(10) NOT NULL COMMENT '0noread |1=read',
  `file` varchar(255) NOT NULL,
  `chat_id` int(48) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `z_ticket_close`
--

CREATE TABLE `z_ticket_close` (
  `id` bigint(20) NOT NULL,
  `chat_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `z_factor`
--
ALTER TABLE `z_factor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_factor_item`
--
ALTER TABLE `z_factor_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_order`
--
ALTER TABLE `z_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_payment`
--
ALTER TABLE `z_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_ticket`
--
ALTER TABLE `z_ticket`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `z_factor`
--
ALTER TABLE `z_factor`
  MODIFY `id` bigint(48) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `z_factor_item`
--
ALTER TABLE `z_factor_item`
  MODIFY `id` bigint(48) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `z_order`
--
ALTER TABLE `z_order`
  MODIFY `id` bigint(48) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `z_payment`
--
ALTER TABLE `z_payment`
  MODIFY `id` bigint(48) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `z_ticket`
--
ALTER TABLE `z_ticket`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

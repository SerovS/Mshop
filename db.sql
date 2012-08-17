-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 02, 2012 at 11:17 AM
-- Server version: 5.1.61
-- PHP Version: 5.3.2-1ubuntu4.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `biokey52`
--

-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_brand`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_brand` (
  `id_content` int(11) NOT NULL,
  `id_brand` int(11) NOT NULL,
  PRIMARY KEY (`id_content`),
  KEY `id_brand` (`id_brand`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `biokey52_mshop_brand`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_content`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pagetitle` varchar(255) NOT NULL DEFAULT '',
  `longtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) DEFAULT '',
  `published` int(1) NOT NULL DEFAULT '0',
  `pub_date` int(20) NOT NULL DEFAULT '0',
  `parent` int(10) NOT NULL DEFAULT '0',
  `isfolder` int(1) NOT NULL DEFAULT '0',
  `introtext` text COMMENT 'Used to provide quick summary of the document',
  `content` mediumtext,
  `template` int(10) NOT NULL DEFAULT '1',
  `menuindex` int(10) NOT NULL DEFAULT '0',
  `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide document from menu',
  `richtext` tinyint(1) NOT NULL DEFAULT '1',
  `editedon` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY `aliasidx` (`alias`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Contains the site document tree.' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `biokey52_mshop_content`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_external_ids`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_external_ids` (
  `id_content` int(11) NOT NULL,
  `id_external` int(11) NOT NULL,
  PRIMARY KEY (`id_content`,`id_external`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `biokey52_mshop_external_ids`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_orders`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_delivery` int(11) DEFAULT NULL,
  `delivery_price` float(10,2) NOT NULL DEFAULT '0.00',
  `id_payment` int(11) DEFAULT NULL,
  `payment_status` int(11) NOT NULL DEFAULT '0',
  `payment_date` datetime NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_details` text NOT NULL,
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `comment` varchar(1024) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `tracking_num` varchar(255) DEFAULT NULL,
  `products_details` text NOT NULL,
  `ip` varchar(15) NOT NULL,
  `send_date` datetime DEFAULT NULL,
  `edit_date` datetime DEFAULT NULL,
  `recall_date` datetime DEFAULT NULL,
  `currency` varchar(15) NOT NULL,
  `price` double NOT NULL,
  `access_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`id_user`),
  KEY `date` (`create_date`),
  KEY `status` (`status`),
  KEY `code` (`tracking_num`),
  KEY `payment_status` (`payment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `biokey52_mshop_orders`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_properties`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_properties` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `in_product` int(1) DEFAULT NULL,
  `in_filter` int(1) DEFAULT NULL,
  `in_compare` int(1) DEFAULT NULL,
  `enabled` int(1) NOT NULL DEFAULT '1',
  `options` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `biokey52_mshop_properties`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_properties2cat`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_properties2cat` (
  `id_property` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  PRIMARY KEY (`id_property`,`id_content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `biokey52_mshop_properties2cat`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_properties_values`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_properties_values` (
  `id_content` int(11) NOT NULL,
  `id_property` int(11) NOT NULL,
  `value` varchar(512) NOT NULL,
  PRIMARY KEY (`id_content`,`id_property`),
  KEY `value` (`value`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `biokey52_mshop_properties_values`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_tmplvar_contentvalues`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_tmplvar_contentvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
  `contentid` int(10) NOT NULL DEFAULT '0' COMMENT 'Site Content Id',
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_tmplvarid` (`tmplvarid`),
  KEY `idx_id` (`contentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `biokey52_mshop_tmplvar_contentvalues`
--


-- --------------------------------------------------------

--
-- Table structure for table `biokey52_mshop_variant`
--

CREATE TABLE IF NOT EXISTS `biokey52_mshop_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_content` int(11) NOT NULL,
  `article` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` float(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `biokey52_mshop_variant`
--


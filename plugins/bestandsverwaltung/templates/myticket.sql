-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 17, 2012 at 07:23 PM
-- Server version: 5.1.61
-- PHP Version: 5.3.5-1ubuntu7.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `myticket`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `rank` int(10) NOT NULL,
  `group` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `ticket` int(20) NOT NULL,
  `notice` int(20) NOT NULL,
  `file` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `size` int(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_changelog`
--

CREATE TABLE IF NOT EXISTS `ticket_changelog` (
  `ticket` int(10) NOT NULL,
  `option` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `date` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_form`
--

CREATE TABLE IF NOT EXISTS `ticket_form` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `element` varchar(255) NOT NULL,
  `option` varchar(255) NOT NULL,
  `rank` int(10) NOT NULL,
  `default` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_notices`
--

CREATE TABLE IF NOT EXISTS `ticket_notices` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) DEFAULT NULL,
  `date` int(20) DEFAULT NULL,
  `ticket` int(10) NOT NULL,
  `notice` text NOT NULL,
  `private` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_tickets`
--

CREATE TABLE IF NOT EXISTS `ticket_tickets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created` int(20) NOT NULL,
  `subject` varchar(140) NOT NULL,
  `description` text NOT NULL,
  `comment` varchar(255) NOT NULL,
  `category` int(10) DEFAULT NULL,
  `priority` int(10) DEFAULT NULL,
  `status` int(10) DEFAULT NULL,
  `severity` int(10) DEFAULT NULL,
  `project` int(10) DEFAULT NULL,
  `platform` int(10) DEFAULT NULL,
  `reporter` varchar(255) DEFAULT NULL,
  `reporter_email` varchar(255) DEFAULT NULL,
  `reporter_salutation` varchar(3) DEFAULT NULL,
  `reporter_forename` varchar(255) DEFAULT NULL,
  `reporter_lastname` varchar(255) DEFAULT NULL,
  `reporter_firm` varchar(255) DEFAULT NULL,
  `reporter_office` varchar(255) DEFAULT NULL,
  `reporter_phone` varchar(255) DEFAULT NULL,
  `supporter` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `updated` int(20) NOT NULL,
  `updater` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(255) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `salutation` varchar(3) DEFAULT NULL,
  `forename` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `firm` varchar(255) DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `phone` int(30) DEFAULT NULL,
  `cellphone` int(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  UNIQUE KEY `name` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users2groups`
--

CREATE TABLE IF NOT EXISTS `users2groups` (
  `login` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

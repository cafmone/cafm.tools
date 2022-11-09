-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 22. Jan 2020 um 17:25
-- Server-Version: 5.7.28-0ubuntu0.18.04.4
-- PHP-Version: 7.2.24-0ubuntu0.18.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ticket`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `row` int(10) NOT NULL,
  `ticket` int(20) DEFAULT NULL,
  `notice` int(20) DEFAULT NULL,
  `file` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `size` int(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticket_changelog`
--

CREATE TABLE `ticket_changelog` (
  `ticket` int(10) NOT NULL,
  `option` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `date` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticket_form`
--

CREATE TABLE `ticket_form` (
  `id` int(10) NOT NULL,
  `element` varchar(255) NOT NULL,
  `option` varchar(255) NOT NULL,
  `rank` int(10) NOT NULL,
  `default` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticket_notices`
--

CREATE TABLE `ticket_notices` (
  `id` int(10) NOT NULL,
  `login` varchar(255) DEFAULT NULL,
  `date` int(20) DEFAULT NULL,
  `ticket` int(10) NOT NULL,
  `notice` text NOT NULL,
  `private` int(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticket_tickets`
--

CREATE TABLE `ticket_tickets` (
  `id` int(10) NOT NULL,
  `created` int(20) NOT NULL,
  `subject` varchar(140) NOT NULL,
  `description` text NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
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
  `updated` int(20) DEFAULT NULL,
  `updater` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`row`);

--
-- Indizes für die Tabelle `ticket_form`
--
ALTER TABLE `ticket_form`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ticket_notices`
--
ALTER TABLE `ticket_notices`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ticket_tickets`
--
ALTER TABLE `ticket_tickets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `row` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT für Tabelle `ticket_form`
--1
ALTER TABLE `ticket_form`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT für Tabelle `ticket_notices`
--
ALTER TABLE `ticket_notices`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT für Tabelle `ticket_tickets`
--
ALTER TABLE `ticket_tickets`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

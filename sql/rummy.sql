-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2020 at 05:38 PM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rummy`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cards`
--

CREATE TABLE `tbl_cards` (
  `id` int(11) NOT NULL,
  `card_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_cards`
--

INSERT INTO `tbl_cards` (`id`, `card_name`) VALUES
(1, 'AOFhearts'),
(2, '2OFhearts'),
(3, '3OFhearts'),
(4, '4OFhearts'),
(5, '5OFhearts'),
(6, '6OFhearts'),
(7, '7OFhearts'),
(8, '8OFhearts'),
(9, '9OFhearts'),
(10, '10OFhearts'),
(11, 'JOFhearts'),
(12, 'QOFhearts'),
(13, 'KOFhearts'),
(14, 'AOFdiams'),
(15, '2OFdiams'),
(16, '3OFdiams'),
(17, '4OFdiams'),
(18, '5OFdiams'),
(19, '6OFdiams'),
(20, '7OFdiams'),
(21, '8OFdiams'),
(22, '9OFdiams'),
(23, '10OFdiams'),
(24, 'JOFdiams'),
(25, 'QOFdiams'),
(26, 'KOFdiams'),
(27, 'AOFspades'),
(28, '2OFspades'),
(29, '3OFspades'),
(30, '4OFspades'),
(31, '5OFspades'),
(32, '6OFspades'),
(33, '7OFspades'),
(34, '8OFspades'),
(35, '9OFspades'),
(36, '10OFspades'),
(37, 'JOFspades'),
(38, 'QOFspades'),
(39, 'KOFspades'),
(40, 'AOFclubs'),
(41, '2OFclubs'),
(42, '3OFclubs'),
(43, '4OFclubs'),
(44, '5OFclubs'),
(45, '6OFclubs'),
(46, '7OFclubs'),
(47, '8OFclubs'),
(48, '9OFclubs'),
(49, '10OFclubs'),
(50, 'JOFclubs'),
(51, 'QOFclubs'),
(52, 'KOFclubs'),
(53, 'Joker'),
(54, 'Joke');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gameplay`
--

CREATE TABLE `tbl_gameplay` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `initial_cards` text NOT NULL,
  `ongoing_cards` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gameplay_log`
--

CREATE TABLE `tbl_gameplay_log` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `log` varchar(500) NOT NULL,
  `pick` varchar(500) DEFAULT NULL,
  `drop` varchar(500) DEFAULT NULL,
  `source` varchar(500) NOT NULL,
  `ongoing_cards` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_games`
--

CREATE TABLE `tbl_games` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `table_no` int(11) NOT NULL,
  `no_of_players` tinyint(4) NOT NULL,
  `game_status` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_cards`
--
ALTER TABLE `tbl_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_gameplay`
--
ALTER TABLE `tbl_gameplay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_gameplay_log`
--
ALTER TABLE `tbl_gameplay_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_games`
--
ALTER TABLE `tbl_games`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cards`
--
ALTER TABLE `tbl_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `tbl_gameplay`
--
ALTER TABLE `tbl_gameplay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_gameplay_log`
--
ALTER TABLE `tbl_gameplay_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_games`
--
ALTER TABLE `tbl_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

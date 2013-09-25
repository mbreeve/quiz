-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 15, 2013 at 12:07 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sorede5_sts`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE IF NOT EXISTS `answer` (
  `idQuestion` int(10) unsigned NOT NULL,
  `reply` varchar(40) NOT NULL,
  `truth` tinyint(3) unsigned DEFAULT NULL,
  `optnum` tinyint(3) DEFAULT NULL COMMENT 'used for multichoice questions',
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`idQuestion`, `reply`, `truth`, `optnum`) VALUES
(1, 'a1', NULL, NULL),
(2, 'aaa', NULL, NULL),
(3, 'aaaa', NULL, NULL),
(4, 'a num1', NULL, NULL),
(5, 'a', NULL, NULL),
(6, 'aaaaaaaaaaaaaaaaaaaaaa', NULL, NULL),
(7, 'yours', NULL, NULL),
(8, 'no poiuyt', NULL, NULL),
(9, 'he', NULL, NULL),
(10, 'a11', NULL, NULL),
(11, 'vv', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `keyword`
--

CREATE TABLE IF NOT EXISTS `keyword` (
  `idOwner` int(10) unsigned NOT NULL,
  `indexOwner` tinyint(3) unsigned NOT NULL,
  `theWord` varchar(20) NOT NULL,
  PRIMARY KEY (`idOwner`,`indexOwner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `keyword`
--

INSERT INTO `keyword` (`idOwner`, `indexOwner`, `theWord`) VALUES
(3, 1, 'martin'),
(3, 2, 'not a pig'),
(3, 3, 'mbr');

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE IF NOT EXISTS `owner` (
  `idUser` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE IF NOT EXISTS `question` (
  `idQuestion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `query` varchar(40) NOT NULL,
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`idQuestion`, `query`) VALUES
(1, 'q1'),
(2, 'qqq'),
(3, 'qqqq'),
(4, 'q num1'),
(5, 'q'),
(6, 'qqqqqqqqqqqqqqqqqqq'),
(7, 'mine'),
(8, 'poiuyt'),
(9, 'who'),
(10, 'q11'),
(11, 'vv');

-- --------------------------------------------------------

--
-- Table structure for table `question_test`
--

CREATE TABLE IF NOT EXISTS `question_test` (
  `idQuestion` int(10) unsigned NOT NULL,
  `idTest` int(10) unsigned NOT NULL,
  `sequence` int(10) unsigned DEFAULT NULL COMMENT 'Used to order questions within a group',
  PRIMARY KEY (`idQuestion`,`idTest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `question_test`
--

INSERT INTO `question_test` (`idQuestion`, `idTest`, `sequence`) VALUES
(1, 0, NULL),
(2, 3, NULL),
(3, 3, NULL),
(4, 3, NULL),
(5, 3, NULL),
(6, 3, NULL),
(7, 3, NULL),
(8, 3, NULL),
(9, 3, NULL),
(10, 3, NULL),
(11, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE IF NOT EXISTS `test` (
  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idOwner` int(10) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `descr` varchar(80) DEFAULT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`idTest`),
  UNIQUE KEY `idOwner` (`idOwner`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`idTest`, `idOwner`, `name`, `descr`, `added`) VALUES
(1, 3, 'mbr''s', 'the first of many', '2013-08-14 15:14:14'),
(3, 3, 'ongobongo', 'naughty', '2013-08-14 15:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `test_key`
--

CREATE TABLE IF NOT EXISTS `test_key` (
  `idTest` int(10) unsigned NOT NULL,
  `indexOwner` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `idGroup` (`idTest`,`indexOwner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `test_key`
--

INSERT INTO `test_key` (`idTest`, `indexOwner`) VALUES
(1, 1),
(3, 1),
(3, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `idUser` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstName` varchar(20) NOT NULL,
  `lastName` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `emailAddr` varchar(80) NOT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `actCode` char(32) DEFAULT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`idUser`),
  KEY `login` (`emailAddr`,`password`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`idUser`, `firstName`, `lastName`, `password`, `emailAddr`, `level`, `actCode`, `added`) VALUES
(1, 'super', 'user', 'a933b2d74681bcb3bb7b01f661a907752259fb70', 'super@x.com', 15, NULL, '2013-08-14 15:11:56'),
(2, 'admin', 'user', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin@x.com', 14, NULL, '2013-08-14 15:11:56'),
(3, 'teacher', 'user', '4a82cb6db537ef6c5b53d144854e146de79502e8', 'teacher@x.com', 5, NULL, '2013-08-14 15:11:57'),
(4, 'learner', 'user', 'b879c6e092ce6406eb1f806bf3757e49981974a7', 'learner@x.com', 4, NULL, '2013-08-14 15:11:57'),
(5, 'student', 'user', '204036a1ef6e7360e536300ea78c6aeb4a9333dd', 'student@x.com', 3, NULL, '2013-08-14 15:11:57'),
(6, 'browser', 'user', 'ef98362b8a6b0c8cd804b0d227aa1ffeaba89786', 'browser@x.com', 2, NULL, '2013-08-14 15:11:57');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

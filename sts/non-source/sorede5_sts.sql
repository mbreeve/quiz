-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 25, 2013 at 04:06 PM
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
  `reply` varchar(40) NOT NULL COMMENT 'the answer/option text',
  `optNum` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'used for multichoice questions',
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`idQuestion`, `reply`, `optNum`) VALUES
(1, 'a1', 0),
(2, 'aa', 0),
(3, 'm', 0),
(4, 'aa', 0),
(5, 'a1', 0);

-- --------------------------------------------------------

--
-- Table structure for table `keyword`
--

CREATE TABLE IF NOT EXISTS `keyword` (
  `idSetter` int(10) unsigned NOT NULL,
  `indexSetter` tinyint(3) unsigned NOT NULL,
  `theWord` varchar(20) NOT NULL,
  PRIMARY KEY (`idSetter`,`indexSetter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `keyword`
--

INSERT INTO `keyword` (`idSetter`, `indexSetter`, `theWord`) VALUES
(3, 1, 'martin'),
(3, 2, 'fred'),
(3, 3, 'newp'),
(3, 4, 'reevex'),
(3, 5, 'poiuyt'),
(3, 6, 'delete'),
(3, 7, 'kw of doom'),
(3, 8, 'bongobongo'),
(3, 9, 'plonker'),
(3, 10, 'yet another'),
(3, 11, 'jumbo');

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE IF NOT EXISTS `question` (
  `idQuestion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `query` varchar(40) NOT NULL COMMENT 'the question text',
  `correct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'correct option for multiple choice',
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`idQuestion`, `query`, `correct`) VALUES
(1, 'q1', 0),
(2, 'qq', 0),
(3, 'm', 0),
(4, 'qq', 0),
(5, 'q1', 0);

-- --------------------------------------------------------

--
-- Table structure for table `setter`
--

CREATE TABLE IF NOT EXISTS `setter` (
  `idUser` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE IF NOT EXISTS `test` (
  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idSetter` int(10) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `descr` varchar(80) DEFAULT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`idTest`),
  UNIQUE KEY `Setter` (`idSetter`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`idTest`, `idSetter`, `name`, `descr`, `added`) VALUES
(8, 3, 'mango', 'descriptio', '2013-08-20 13:47:42'),
(10, 3, 'mbr''s', 'poo-eee', '2013-08-28 14:54:03'),
(11, 3, 'new test', 'descr', '2013-09-10 15:10:24'),
(12, 3, 'another new test ', 'nope', '2013-09-23 13:17:26');

-- --------------------------------------------------------

--
-- Table structure for table `test_key`
--

CREATE TABLE IF NOT EXISTS `test_key` (
  `idTest` int(10) unsigned NOT NULL,
  `indexSetter` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `Test` (`idTest`,`indexSetter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `test_key`
--

INSERT INTO `test_key` (`idTest`, `indexSetter`) VALUES
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(8, 8),
(8, 9),
(10, 4),
(10, 8),
(10, 9),
(11, 1),
(11, 3),
(11, 8),
(11, 9),
(12, 8),
(12, 10);

-- --------------------------------------------------------

--
-- Table structure for table `test_question`
--

CREATE TABLE IF NOT EXISTS `test_question` (
  `idQuestion` int(10) unsigned NOT NULL,
  `idTest` int(10) unsigned NOT NULL,
  `sequence` double NOT NULL DEFAULT '0' COMMENT 'order questions in order',
  PRIMARY KEY (`idQuestion`,`idTest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `test_question`
--

INSERT INTO `test_question` (`idQuestion`, `idTest`, `sequence`) VALUES
(1, 1, 0),
(2, 1, 0),
(3, 1, 0),
(4, 1, 0),
(5, 8, 0);

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
(1, 'super', 'user', 'a933b2d74681bcb3bb7b01f661a907752259fb70', 'super@x.com', 15, NULL, '2013-08-15 15:32:41'),
(2, 'admin', 'user', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin@x.com', 14, NULL, '2013-08-15 15:32:41'),
(3, 'teacher', 'user', '4a82cb6db537ef6c5b53d144854e146de79502e8', 'teacher@x.com', 5, NULL, '2013-08-15 15:32:41'),
(4, 'learner', 'user', 'b879c6e092ce6406eb1f806bf3757e49981974a7', 'learner@x.com', 4, NULL, '2013-08-15 15:32:41'),
(5, 'student', 'user', '204036a1ef6e7360e536300ea78c6aeb4a9333dd', 'student@x.com', 3, NULL, '2013-08-15 15:32:42'),
(6, 'browser', 'user', 'ef98362b8a6b0c8cd804b0d227aa1ffeaba89786', 'browser@x.com', 2, NULL, '2013-08-15 15:32:42');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

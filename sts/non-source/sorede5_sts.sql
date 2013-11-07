	-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 04, 2013 at 12:31 PM
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
  `reply` tinytext NOT NULL COMMENT 'the answer/option text',
  `optNum` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'used for multichoice questions',
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(3, 1, 'poiuyt'),
(3, 2, 'first'),
(3, 3, 'second'),
(3, 4, 'fifth'),
(3, 5, 'sixth');

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE IF NOT EXISTS `question` (
  `idQuestion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  `query` tinytext NOT NULL COMMENT 'the question text',
  `correct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'correct option for multiple choice',
  PRIMARY KEY (`idQuestion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `setter`
--

CREATE TABLE IF NOT EXISTS `setter` (
  `idSetter` int(10) unsigned NOT NULL,
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  PRIMARY KEY (`idSetter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `setter`
--

INSERT INTO `setter` (`idSetter`, `identifier`) VALUES
(1, 'super@x.com'),
(2, 'admin@x.com'),
(3, 'setter@x.com'),
(4, 'learner@x.com'),
(5, 'student@x.com'),
(6, 'browser@x.com');

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE IF NOT EXISTS `test` (
  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idSetter` int(10) unsigned NOT NULL,
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  `descr` tinytext,
  `added` datetime NOT NULL,
  PRIMARY KEY (`idTest`),
  UNIQUE KEY `Setter` (`idSetter`,`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`idTest`, `idSetter`, `identifier`, `descr`, `added`) VALUES
(1, 3, 'one', '1', '2013-11-03 14:48:29'),
(2, 3, 'two', '2', '2013-11-03 19:16:42'),
(3, 3, 'three', '3', '2013-11-03 19:50:44'),
(4, 3, 'four', '4', '2013-11-03 19:58:06'),
(6, 3, 'five', '5', '2013-11-03 20:09:50'),
(7, 3, 'six', '6', '2013-11-03 21:02:56');

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
(1, 1),
(1, 2),
(2, 1),
(2, 3),
(6, 1),
(6, 4),
(7, 1),
(7, 5);

-- --------------------------------------------------------

--
-- Table structure for table `test_question`
--

CREATE TABLE IF NOT EXISTS `test_question` (
  `idQuestion` int(10) unsigned NOT NULL,
  `idTest` int(10) unsigned NOT NULL,
  `sequence` double NOT NULL COMMENT 'order of questions within test',
  PRIMARY KEY (`idQuestion`,`idTest`),
  KEY `idTest` (`idTest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(1, 'super', 'user', 'a933b2d74681bcb3bb7b01f661a907752259fb70', 'super@x.com', 15, NULL, '2013-11-03 14:47:36'),
(2, 'admin', 'user', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin@x.com', 14, NULL, '2013-11-03 14:47:36'),
(3, 'setter', 'user', '02ac648906a6828f331d73e868fceca24e06da77', 'setter@x.com', 5, NULL, '2013-11-03 14:47:37'),
(4, 'learner', 'user', 'b879c6e092ce6406eb1f806bf3757e49981974a7', 'learner@x.com', 4, NULL, '2013-11-03 14:47:37'),
(5, 'student', 'user', '204036a1ef6e7360e536300ea78c6aeb4a9333dd', 'student@x.com', 3, NULL, '2013-11-03 14:47:37'),
(6, 'browser', 'user', 'ef98362b8a6b0c8cd804b0d227aa1ffeaba89786', 'browser@x.com', 2, NULL, '2013-11-03 14:47:37');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`idQuestion`) REFERENCES `question` (`idQuestion`) ON DELETE CASCADE;

--
-- Constraints for table `keyword`
--
ALTER TABLE `keyword`
  ADD CONSTRAINT `keyword_ibfk_1` FOREIGN KEY (`idSetter`) REFERENCES `setter` (`idSetter`) ON DELETE CASCADE;

--
-- Constraints for table `setter`
--
ALTER TABLE `setter`
  ADD CONSTRAINT `setter_ibfk_2` FOREIGN KEY (`idSetter`) REFERENCES `user` (`idUser`) ON DELETE CASCADE;

--
-- Constraints for table `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `test_ibfk_3` FOREIGN KEY (`idSetter`) REFERENCES `setter` (`idSetter`) ON DELETE CASCADE;

--
-- Constraints for table `test_key`
--
ALTER TABLE `test_key`
  ADD CONSTRAINT `test_key_ibfk_4` FOREIGN KEY (`idTest`) REFERENCES `test` (`idTest`) ON DELETE CASCADE;

--
-- Constraints for table `test_question`
--
ALTER TABLE `test_question`
  ADD CONSTRAINT `test_question_ibfk_2` FOREIGN KEY (`idTest`) REFERENCES `test` (`idTest`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_question_ibfk_1` FOREIGN KEY (`idQuestion`) REFERENCES `question` (`idQuestion`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

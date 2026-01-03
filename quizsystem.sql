-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 22, 2024 at 06:57 AM
-- Server version: 5.7.36
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quizsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `correctAnswer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `question`, `options`, `correctAnswer`) VALUES
(1, 'Who won the FIFA World Cup in 2018?', 'Brazil,France,Germany,Spain', '1'),
(2, 'Which player holds the record for the most goals in the Premier League?', 'Alan Shearer,Wayne Rooney,Thierry Henry,Sergio Agüero', '0'),
(3, 'Which country is known as the `Home of Football`?', 'Brazil,Italy,England,Spain', '2'),
(4, 'What is the maximum number of players a football team can have on the field at any time?', '11,12,10,9', '0'),
(5, 'Which team won the UEFA Champions League in 2005 in a dramatic comeback against AC Milan?', 'Liverpool,Manchester United,Chelsea,Barcelona', '0'),
(6, 'Which player is known as `The King of Football`?', 'Lionel Messi,Cristiano Ronaldo,Pele,Diego Maradona', '2'),
(7, 'What color card does a referee show to indicate a player is being warned?', 'Red,Yellow,Green,Blue', '1'),
(8, 'Which country won the first ever FIFA World Cup in 1930?', 'Uruguay,Brazil,Argentina,Italy', '0'),
(9, 'How long is a standard professional football match?', '90 minutes,80 minutes,100 minutes,120 minutes', '0'),
(10, 'Which football club is known as `The Red Devils`?', 'Arsenal,Liverpool,Manchester United,Chelsea', '2'),
(11, 'Who is the only player to have won three European Golden Shoes?', 'Cristiano Ronaldo,Lionel Messi,Robert Lewandowski,Kylian Mbappe', '1'),
(12, 'In which year was the UEFA Champions League founded?', '1955,1960,1970,1985', '0'),
(13, 'Which player is known for the famous `bicycle kick`?', 'Cristiano Ronaldo,Ronaldinho,Lionel Messi,Zlatan Ibrahimovic', '3');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(199) DEFAULT NULL,
  `email` varchar(199) DEFAULT NULL,
  `password` varchar(199) DEFAULT NULL,
  `type` varchar(199) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `type`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', 'secret', 'admin', 1, NULL, NULL, NULL),
(4, 'User', 'user@gmail.com', 'user@123', 'user', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz_result`
--

DROP TABLE IF EXISTS `user_quiz_result`;
CREATE TABLE IF NOT EXISTS `user_quiz_result` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `correct_answer` int(11) DEFAULT NULL,
  `wrong_answer` int(11) DEFAULT NULL,
  `incomplete_answer` int(11) DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_quiz_result`
--

INSERT INTO `user_quiz_result` (`id`, `user_id`, `correct_answer`, `wrong_answer`, `incomplete_answer`, `result_date`) VALUES
(2, 4, 3, 7, 0, '2024-01-22');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

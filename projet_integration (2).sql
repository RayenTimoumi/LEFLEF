-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 12, 2025 at 08:30 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projet_integration`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', 'admin', 'super_admin', NULL, '2025-04-26 14:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
CREATE TABLE IF NOT EXISTS `institutions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text,
  `average_time` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`id`, `name`, `logo`, `description`, `average_time`, `created_at`) VALUES
(1, 'CNAM', '680d0fc6891c8.jpg', 'Caisse Nationale d\'Assurance Maladie', 15, '2025-04-26 14:46:28'),
(2, 'CNSS', '680d0f1c93cfd.png', 'Caisse Nationale de Sécurité Sociale', 18, '2025-04-26 14:46:28'),
(3, 'Tunisie Telecom', '680d0f2f93fc9.png', 'Service des télécommunications', 22, '2025-04-26 14:46:28'),
(4, 'Orange Tunisie', '680d0f26c0450.png', 'Opérateur téléphonique', 20, '2025-04-26 14:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `cin` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `societe_type` enum('pub','prv') NOT NULL,
  `institution_id` int NOT NULL,
  `service_id` int NOT NULL,
  `institution` varchar(100) NOT NULL,
  `service` varchar(100) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `ticket_number` varchar(10) NOT NULL,
  `queue_position` int DEFAULT '0',
  `notifications` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('waiting','processing','completed') DEFAULT 'waiting',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `name`, `cin`, `phone`, `email`, `societe_type`, `institution_id`, `service_id`, `institution`, `service`, `reservation_date`, `reservation_time`, `ticket_number`, `queue_position`, `notifications`, `created_at`, `status`, `updated_at`) VALUES
(11, 'jasser kafsi', '1231231', '+53 01 56 62', 'kafsijasser2@gmail.com', 'prv', 4, 7, 'Orange Tunisie', 'Forfaits', '2025-05-23', '15:00:00', 'T-3746', 1, 1, '2025-05-12 20:19:00', 'completed', '2025-05-12 20:20:13'),
(10, 'jasser kafsi', '1231231', '+53 01 56 62', 'kafsijasser2@gmail.com', 'pub', 2, 3, 'CNSS', 'Pensions', '2025-05-24', '10:00:00', 'T-2722', 1, 1, '2025-05-12 20:07:35', 'waiting', '2025-05-12 20:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `institution_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `average_time` int DEFAULT '15',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `institution_id`, `name`, `description`, `created_at`, `average_time`) VALUES
(1, 1, 'Etat Civil', 'Services liés à l\'état civil', '2025-05-03 12:10:09', 15),
(2, 1, 'Assurance Maladie', 'Services de remboursement', '2025-05-03 12:10:09', 15),
(3, 2, 'Pensions', 'Services de retraite', '2025-05-03 12:10:09', 15),
(4, 2, 'Assurance Chômage', 'Services pour chômeurs', '2025-05-03 12:10:09', 15),
(6, 3, 'Mobile', 'Services mobiles', '2025-05-03 12:10:09', 15),
(7, 4, 'Forfaits', 'Gestion des forfaits', '2025-05-03 12:10:09', 15);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'system_name', 'gestion file d\'attend'),
(2, 'open_time', '08:00'),
(3, 'close_time', '17:00'),
(4, 'slot_duration', '15');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `adresse` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `nom`, `date_naissance`, `ville`, `code_postal`, `mot_de_passe`, `adresse`, `created_at`) VALUES
(2, 'kafsijasser2@gmail.com', 'jasser kafsi', '2004-07-04', 'sousse', '5070', '$2y$10$9o7gJxv0AU0KNlA8hOqwqereIEP12KetXQm/aOXjW5FrsRTARVbRm', 'sousse', '2025-05-12 19:23:58');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

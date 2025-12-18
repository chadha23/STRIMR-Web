-- ============================================
-- SQL Migration: Add Events and Reservations Tables
-- Run this script on your existing 'strimr' database
-- ============================================

USE strimr;

-- --------------------------------------------------------
-- Table structure for `events`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `events` (
  `id_event` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('tournoi','stream_special','rencontre','annonce','autre','game','workshop','social') DEFAULT 'autre',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('upcoming','live','finished','cancelled') DEFAULT 'upcoming',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `reservations`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `reservations` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `reservation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id_reservation`),
  KEY `fk_reservation_event` (`event_id`),
  CONSTRAINT `fk_reservation_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Sample Data (Optional - you can comment this out)
-- --------------------------------------------------------

-- Sample Events
INSERT INTO `events` (`user_id`, `title`, `description`, `event_type`, `start_date`, `end_date`, `status`) VALUES
(1, 'Community Game Night', 'Join us for an evening of fun games and friendly competition. All skill levels welcome!', 'game', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY) + INTERVAL 3 HOUR, 'upcoming'),
(1, 'Design Workshop: UI/UX Basics', 'Learn the fundamentals of user interface and user experience design in this hands-on workshop.', 'workshop', DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY) + INTERVAL 3 HOUR, 'upcoming'),
(1, 'Coding Challenge: Build a Discord Clone', 'Real-time coding session where we build features together. Bring your questions and ideas!', 'stream_special', NOW(), DATE_ADD(NOW(), INTERVAL 4 HOUR), 'live'),
(1, 'Music Production Masterclass', 'Learn advanced production techniques from industry professionals.', 'workshop', DATE_ADD(NOW(), INTERVAL 21 DAY), DATE_ADD(NOW(), INTERVAL 21 DAY) + INTERVAL 3 HOUR, 'upcoming');

-- ============================================
-- Migration Complete!
-- ============================================


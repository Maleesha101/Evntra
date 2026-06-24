
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `competition_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bookmark` (`user_id`,`competition_id`),
  KEY `idx_bookmarks_user` (`user_id`),
  KEY `idx_bookmarks_competition` (`competition_id`),
  CONSTRAINT `fk_bookmarks_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
INSERT INTO `bookmarks` VALUES (1,1,2,'2026-05-11 10:05:00'),(2,1,5,'2026-05-12 12:05:00'),(3,4,10,'2026-06-24 03:44:05'),(4,4,7,'2026-06-24 04:00:37');
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `competitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organizer_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('CTF','Hackathon','Robotics','Gaming','Coding','AI/ML','Other') NOT NULL DEFAULT 'Other',
  `banner_image` varchar(255) DEFAULT NULL,
  `registration_start` datetime NOT NULL,
  `registration_end` datetime NOT NULL,
  `event_start` datetime NOT NULL,
  `event_end` datetime NOT NULL,
  `venue` varchar(255) NOT NULL,
  `max_participants` int(10) unsigned DEFAULT NULL,
  `team_size_min` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `team_size_max` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `prize_pool` varchar(255) DEFAULT NULL,
  `eligibility` text DEFAULT NULL,
  `status` enum('draft','pending','published','ongoing','completed','cancelled') NOT NULL DEFAULT 'draft',
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_competitions_organizer` (`organizer_id`),
  KEY `idx_competitions_category` (`category`),
  KEY `idx_competitions_status` (`status`),
  KEY `idx_competitions_event_start` (`event_start`),
  KEY `idx_competitions_registration_end` (`registration_end`),
  CONSTRAINT `fk_competitions_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `competitions` WRITE;
/*!40000 ALTER TABLE `competitions` DISABLE KEYS */;
INSERT INTO `competitions` VALUES (1,2,'Campus CTF 2026','campus-ctf-2026','A flagship capture-the-flag challenge focused on web, crypto, and reverse engineering.','CTF',NULL,'2026-05-01 08:00:00','2026-05-20 23:59:00','2026-05-25 09:00:00','2026-05-25 18:00:00','Online',120,1,4,'$2,500 cash + trophies','Open to all undergraduate and postgraduate students.','published',95,'2026-04-18 10:00:00','2026-06-23 07:50:30'),(2,2,'Hack the Future','hack-the-future','72-hour innovation sprint for social good and emerging tech prototypes.','Hackathon',NULL,'2026-05-04 08:00:00','2026-05-28 23:59:00','2026-05-29 09:00:00','2026-05-31 21:00:00','Innovation Hall',240,2,5,'$5,000 grand prize','Teams of 2 to 5 students.','published',181,'2026-04-20 11:15:00','2026-06-24 01:47:17'),(3,2,'RoboRumble','roborumble','Build and battle autonomous robots in timed arena challenges.','Robotics',NULL,'2026-05-10 08:00:00','2026-06-05 23:59:00','2026-06-08 10:00:00','2026-06-09 17:00:00','Engineering Lab 3',40,2,4,'$1,500 robotics kit vouchers','Engineering and computing students encouraged.','published',74,'2026-04-21 12:00:00','2026-06-23 07:50:30'),(4,2,'GameVerse Arena','gameverse-arena','Competitive esports ladder with on-campus finals and live commentary.','Gaming',NULL,'2026-05-11 08:00:00','2026-05-26 23:59:00','2026-05-30 12:00:00','2026-05-30 18:00:00','Student Center Arena',64,1,1,'$1,000 prize pool','Registered students only.','published',210,'2026-04-22 13:30:00','2026-06-23 07:50:30'),(5,2,'CodeSprint X','codesprint-x','A fast-paced algorithm and debugging challenge for developers of all levels.','Coding',NULL,'2026-05-12 08:00:00','2026-06-01 23:59:00','2026-06-03 09:00:00','2026-06-03 17:00:00','Computer Lab 1',150,1,1,'Internship interviews + gadgets','All active students are eligible.','published',124,'2026-04-23 14:05:00','2026-06-24 05:54:06'),(6,2,'AI Insight Challenge','ai-insight-challenge','Solve a real-world forecasting problem using modern machine learning techniques.','AI/ML',NULL,'2026-05-15 08:00:00','2026-06-07 23:59:00','2026-06-10 09:00:00','2026-06-11 19:00:00','Data Science Center',NULL,2,5,'$3,000 + cloud credits','Open to students with basic ML knowledge.','pending',44,'2026-04-24 15:00:00','2026-06-23 07:50:30'),(7,2,'ByteCraft Hackathon 2026','bytecraft-hackathon-2026','A 48-hour challenge to design and develop software solutions addressing local community issues.','Hackathon',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-07-20 09:00:00','2026-07-22 17:00:00','Main IT Lab & Online',100,2,4,'$3,000 cash prizes','All enrolled university students.','published',2,'2026-06-24 03:36:35','2026-06-24 05:49:34'),(8,2,'SecCon CTF Quals','seccon-ctf-quals','Jeopardy-style capture the flag competition focusing on binary exploitation, web security, and reverse engineering.','CTF',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-07-28 09:00:00','2026-07-29 18:00:00','Online',300,1,5,'Digital badges & premium vouchers','Open globally to all academic teams.','published',1,'2026-06-24 03:36:35','2026-06-24 05:52:47'),(9,2,'Autonomous Drone Race','autonomous-drone-race','Program your custom drone to navigate through a complex indoor obstacle course autonomously.','Robotics',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-08-25 10:00:00','2026-08-26 16:00:00','Campus Gymnasium',30,2,3,'High-end drone hardware kits','Teams of engineering students.','published',1,'2026-06-24 03:36:35','2026-06-24 06:03:09'),(10,2,'Valorant Uni-Cup','valorant-uni-cup','Represent your faculty and compete in the ultimate Valorant tournament. Streamed live with professional shoutcasting.','Gaming',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-08-05 13:00:00','2026-08-07 20:00:00','Gaming Lounge & Discord',16,5,5,'$1,200 gaming gear vouchers','Teams of 5 players from the same faculty.','published',1,'2026-06-24 03:36:35','2026-06-24 04:43:18'),(11,2,'LeetCode Speed-Run','leetcode-speed-run','Solve 5 algorithmic challenges as quickly as possible. Points awarded for speed, space/time complexity, and correctness.','Coding',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-07-08 14:00:00','2026-07-08 16:00:00','Computer Labs 1-4',200,1,1,'Mechanical keyboards & licenses','All undergraduate students.','published',1,'2026-06-24 03:36:35','2026-06-24 05:53:19'),(12,2,'DeepLearning Generative Art','deeplearning-generative-art','Use state-of-the-art diffusion models and GANs to generate creative art pieces based on secret university-themed prompts.','AI/ML',NULL,'2026-06-20 00:00:00','2026-07-20 23:59:00','2026-08-10 09:00:00','2026-08-11 18:00:00','Data Labs',50,1,2,'NVIDIA GPU + Cloud computing credits','Open to AI/ML club members.','published',0,'2026-06-24 03:36:35','2026-06-24 04:43:18');
/*!40000 ALTER TABLE `competitions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `conflict_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conflict_flags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_a_id` int(10) unsigned NOT NULL,
  `competition_b_id` int(10) unsigned NOT NULL,
  `severity` enum('low','medium','high') NOT NULL,
  `flagged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_conflict_pair` (`competition_a_id`,`competition_b_id`),
  KEY `fk_conflict_b` (`competition_b_id`),
  KEY `idx_conflict_severity` (`severity`),
  CONSTRAINT `fk_conflict_a` FOREIGN KEY (`competition_a_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_conflict_b` FOREIGN KEY (`competition_b_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `conflict_flags` WRITE;
/*!40000 ALTER TABLE `conflict_flags` DISABLE KEYS */;
INSERT INTO `conflict_flags` VALUES (1,1,4,'medium','2026-05-12 08:00:00'),(2,2,4,'high','2026-05-12 08:10:00'),(3,5,6,'low','2026-05-12 08:20:00');
/*!40000 ALTER TABLE `conflict_flags` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'Your registration for Campus CTF 2026 is confirmed.','/student/my-registrations.php',0,'2026-05-06 09:01:00'),(2,2,'Hack the Future has registrations waiting for review.','/organizer/manage-registrations.php',0,'2026-05-11 10:10:00'),(3,3,'Pending competition AI Insight Challenge needs approval.','/admin/approve-competitions.php',0,'2026-05-12 08:30:00'),(4,4,'Registration successful. for ByteCraft Hackathon 2026','/student/competition.php?slug=bytecraft-hackathon-2026',1,'2026-06-24 04:44:51'),(5,2,'Rusiru registered for ByteCraft Hackathon 2026','/student/competition.php?slug=bytecraft-hackathon-2026',0,'2026-06-24 04:44:51'),(6,4,'Registration successful. for Autonomous Drone Race','/student/competition.php?slug=autonomous-drone-race',1,'2026-06-24 06:03:22'),(7,2,'Rusiru registered for Autonomous Drone Race','/student/competition.php?slug=autonomous-drone-race',0,'2026-06-24 06:03:22');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_password_resets_user` (`user_id`),
  KEY `idx_password_resets_expires` (`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `status` enum('registered','waitlisted','cancelled') NOT NULL DEFAULT 'registered',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_registration` (`competition_id`,`user_id`),
  KEY `fk_registrations_team` (`team_id`),
  KEY `idx_registrations_competition` (`competition_id`),
  KEY `idx_registrations_user` (`user_id`),
  CONSTRAINT `fk_registrations_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
INSERT INTO `registrations` VALUES (1,1,1,NULL,'registered','2026-05-06 09:00:00'),(2,2,1,1,'registered','2026-05-11 10:00:00'),(3,4,1,NULL,'waitlisted','2026-05-12 16:00:00'),(4,7,4,3,'registered','2026-06-24 04:44:51'),(5,9,4,4,'registered','2026-06-24 06:03:22');
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `team_name` varchar(150) NOT NULL,
  `leader_id` int(10) unsigned NOT NULL,
  `invite_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_code` (`invite_code`),
  KEY `idx_teams_competition` (`competition_id`),
  KEY `idx_teams_leader` (`leader_id`),
  CONSTRAINT `fk_teams_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_teams_leader` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,2,'Byte Nomads',1,'BYTEN1','2026-05-10 10:00:00'),(2,2,'Compile Crew',1,'COMP23','2026-05-11 14:00:00'),(3,7,'Rusiru',4,'936764','2026-06-24 04:44:51'),(4,9,'Randika',4,'1F4D62','2026-06-24 06:03:22');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','organizer','admin') NOT NULL DEFAULT 'student',
  `university` varchar(200) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Alya Rahman','student@evntra.test','$2y$10$.sUlmUc6bQjZ9WzRwx3nbuz2hWVzQr4I2WwT/N2sLeVrWogKGsTfS','student','Evntra University',NULL,1,1,'2026-01-05 09:00:00'),(2,'Nabil Hassan','organizer@evntra.test','$2y$10$MdvzuaOGi9KeD311J.JVIu10yHCdy0N5t0QaDaxVNCGwcACfHMqXW','organizer','Evntra University',NULL,1,1,'2026-01-05 09:05:00'),(3,'Dr. Serena Cole','admin@evntra.test','$2y$10$9fKdbq9r.oOc5xh/HUwwTOepU/oBM8HcFqa9NJDAYSKEcAeAUtFQ.','admin','Evntra University',NULL,1,1,'2026-01-05 09:10:00'),(4,'Rusiru','rusirurandikakmv@gmail.com','$2y$10$H8K5djL/.C.wjztrlr3cOeLl5Uwvl4Od11HsxQwoMYYo8nobD.BEq','student','University Of Kelaniya',NULL,0,1,'2026-06-24 03:19:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, full_name, email, password_hash, role, university, email_verified, is_active, created_at) VALUES
(1, 'Alya Rahman', 'student@evntra.test', '$2y$10$EJO9/Ag.gtgU.tkxGCeoGuLN7mlAm72sQxM1RRBFiQd27dSbK0JTS', 'student', 'Evntra University', 1, 1, '2026-01-05 09:00:00'),
(2, 'Nabil Hassan', 'organizer@evntra.test', '$2y$10$MdvzuaOGi9KeD311J.JVIu10yHCdy0N5t0QaDaxVNCGwcACfHMqXW', 'organizer', 'Evntra University', 1, 1, '2026-01-05 09:05:00'),
(3, 'Maleesha Indunil', 'admin@evntra.test', '$2y$10$9fKdbq9r.oOc5xh/HUwwTOepU/oBM8HcFqa9NJDAYSKEcAeAUtFQ.', 'admin', 'Evntra University', 1, 1, '2026-01-05 09:10:00');

INSERT INTO competitions (id, organizer_id, title, slug, description, category, banner_image, registration_start, registration_end, event_start, event_end, venue, max_participants, team_size_min, team_size_max, prize_pool, eligibility, status, views, created_at) VALUES
(1, 2, 'Campus CTF 2026', 'campus-ctf-2026', 'A flagship capture-the-flag challenge focused on web, crypto, and reverse engineering.', 'CTF', NULL, '2026-05-01 08:00:00', '2026-05-20 23:59:00', '2026-05-25 09:00:00', '2026-05-25 18:00:00', 'Online', 120, 1, 4, '$2,500 cash + trophies', 'Open to all undergraduate and postgraduate students.', 'published', 95, '2026-04-18 10:00:00'),
(2, 2, 'Hack the Future', 'hack-the-future', '72-hour innovation sprint for social good and emerging tech prototypes.', 'Hackathon', NULL, '2026-05-04 08:00:00', '2026-05-28 23:59:00', '2026-05-29 09:00:00', '2026-05-31 21:00:00', 'Innovation Hall', 240, 2, 5, '$5,000 grand prize', 'Teams of 2 to 5 students.', 'published', 180, '2026-04-20 11:15:00'),
(3, 2, 'RoboRumble', 'roborumble', 'Build and battle autonomous robots in timed arena challenges.', 'Robotics', NULL, '2026-05-10 08:00:00', '2026-06-05 23:59:00', '2026-06-08 10:00:00', '2026-06-09 17:00:00', 'Engineering Lab 3', 40, 2, 4, '$1,500 robotics kit vouchers', 'Engineering and computing students encouraged.', 'published', 74, '2026-04-21 12:00:00'),
(4, 2, 'GameVerse Arena', 'gameverse-arena', 'Competitive esports ladder with on-campus finals and live commentary.', 'Gaming', NULL, '2026-05-11 08:00:00', '2026-05-26 23:59:00', '2026-05-30 12:00:00', '2026-05-30 18:00:00', 'Student Center Arena', 64, 1, 1, '$1,000 prize pool', 'Registered students only.', 'published', 210, '2026-04-22 13:30:00'),
(5, 2, 'CodeSprint X', 'codesprint-x', 'A fast-paced algorithm and debugging challenge for developers of all levels.', 'Coding', NULL, '2026-05-12 08:00:00', '2026-06-01 23:59:00', '2026-06-03 09:00:00', '2026-06-03 17:00:00', 'Computer Lab 1', 150, 1, 1, 'Internship interviews + gadgets', 'All active students are eligible.', 'published', 122, '2026-04-23 14:05:00'),
(6, 2, 'AI Insight Challenge', 'ai-insight-challenge', 'Solve a real-world forecasting problem using modern machine learning techniques.', 'AI/ML', NULL, '2026-05-15 08:00:00', '2026-06-07 23:59:00', '2026-06-10 09:00:00', '2026-06-11 19:00:00', 'Data Science Center', NULL, 2, 5, '$3,000 + cloud credits', 'Open to students with basic ML knowledge.', 'pending', 44, '2026-04-24 15:00:00');

INSERT INTO teams (id, competition_id, team_name, leader_id, invite_code, created_at) VALUES
(1, 2, 'Byte Nomads', 1, 'BYTEN1', '2026-05-10 10:00:00'),
(2, 2, 'Compile Crew', 1, 'COMP23', '2026-05-11 14:00:00');

INSERT INTO registrations (id, competition_id, user_id, team_id, status, registered_at) VALUES
(1, 1, 1, NULL, 'registered', '2026-05-06 09:00:00'),
(2, 2, 1, 1, 'registered', '2026-05-11 10:00:00'),
(3, 4, 1, NULL, 'waitlisted', '2026-05-12 16:00:00');

INSERT INTO bookmarks (id, user_id, competition_id, created_at) VALUES
(1, 1, 2, '2026-05-11 10:05:00'),
(2, 1, 5, '2026-05-12 12:05:00');

INSERT INTO notifications (id, user_id, message, link, is_read, created_at) VALUES
(1, 1, 'Your registration for Campus CTF 2026 is confirmed.', '/student/my-registrations.php', 0, '2026-05-06 09:01:00'),
(2, 2, 'Hack the Future has registrations waiting for review.', '/organizer/manage-registrations.php', 0, '2026-05-11 10:10:00'),
(3, 3, 'Pending competition AI Insight Challenge needs approval.', '/admin/approve-competitions.php', 0, '2026-05-12 08:30:00');

INSERT INTO conflict_flags (id, competition_a_id, competition_b_id, severity, flagged_at) VALUES
(1, 1, 4, 'medium', '2026-05-12 08:00:00'),
(2, 2, 4, 'high', '2026-05-12 08:10:00'),
(3, 5, 6, 'low', '2026-05-12 08:20:00');

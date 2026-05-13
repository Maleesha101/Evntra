SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS conflict_flags;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS competitions;
DROP TABLE IF EXISTS users;

SET foreign_key_checks = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'organizer', 'admin') NOT NULL DEFAULT 'student',
    university VARCHAR(200) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE competitions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    category ENUM('CTF', 'Hackathon', 'Robotics', 'Gaming', 'Coding', 'AI/ML', 'Other') NOT NULL DEFAULT 'Other',
    banner_image VARCHAR(255) DEFAULT NULL,
    registration_start DATETIME NOT NULL,
    registration_end DATETIME NOT NULL,
    event_start DATETIME NOT NULL,
    event_end DATETIME NOT NULL,
    venue VARCHAR(255) NOT NULL,
    max_participants INT UNSIGNED DEFAULT NULL,
    team_size_min TINYINT UNSIGNED NOT NULL DEFAULT 1,
    team_size_max TINYINT UNSIGNED NOT NULL DEFAULT 1,
    prize_pool VARCHAR(255) DEFAULT NULL,
    eligibility TEXT DEFAULT NULL,
    status ENUM('draft', 'pending', 'published', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_competitions_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_competitions_category (category),
    INDEX idx_competitions_status (status),
    INDEX idx_competitions_event_start (event_start),
    INDEX idx_competitions_registration_end (registration_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competition_id INT UNSIGNED NOT NULL,
    team_name VARCHAR(150) NOT NULL,
    leader_id INT UNSIGNED NOT NULL,
    invite_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teams_competition FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_teams_leader FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_teams_competition (competition_id),
    INDEX idx_teams_leader (leader_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competition_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    team_id INT UNSIGNED DEFAULT NULL,
    status ENUM('registered', 'waitlisted', 'cancelled') NOT NULL DEFAULT 'registered',
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registrations_competition FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_registrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_registrations_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY uniq_registration (competition_id, user_id),
    INDEX idx_registrations_competition (competition_id),
    INDEX idx_registrations_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookmarks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    competition_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_bookmark (user_id, competition_id),
    CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookmarks_competition FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_bookmarks_user (user_id),
    INDEX idx_bookmarks_competition (competition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conflict_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competition_a_id INT UNSIGNED NOT NULL,
    competition_b_id INT UNSIGNED NOT NULL,
    severity ENUM('low', 'medium', 'high') NOT NULL,
    flagged_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conflict_a FOREIGN KEY (competition_a_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_conflict_b FOREIGN KEY (competition_b_id) REFERENCES competitions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uniq_conflict_pair (competition_a_id, competition_b_id),
    INDEX idx_conflict_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(3, 'Dr. Serena Cole', 'admin@evntra.test', '$2y$10$9fKdbq9r.oOc5xh/HUwwTOepU/oBM8HcFqa9NJDAYSKEcAeAUtFQ.', 'admin', 'Evntra University', 1, 1, '2026-01-05 09:10:00');

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

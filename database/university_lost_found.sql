-- University Lost & Found Management System
-- "Lost Today, Find Tomorrow"
-- Database: university_lost_found
-- Compatible with MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.4+ (XAMPP default)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `university_lost_found` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `university_lost_found`;

-- --------------------------------------------------------
-- Table structure for `faculties`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `faculties`;
CREATE TABLE `faculties` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `departments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `faculty_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_departments_faculty` (`faculty_id`),
  CONSTRAINT `fk_departments_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `university_id` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'staff', 'officer', 'admin') NOT NULL DEFAULT 'student',
  `faculty_id` INT UNSIGNED NULL,
  `department_id` INT UNSIGNED NULL,
  `profile_image` VARCHAR(255) NULL DEFAULT 'default_avatar.png',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `reset_token` VARCHAR(100) NULL,
  `reset_token_expiry` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_users_faculty` (`faculty_id`),
  KEY `fk_users_department` (`department_id`),
  CONSTRAINT `fk_users_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-tag',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `locations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `building` VARCHAR(100) NULL,
  `campus` VARCHAR(100) NOT NULL DEFAULT 'Main Campus',
  `description` TEXT NULL,
  `latitude` DECIMAL(10, 8) NULL,
  `longitude` DECIMAL(11, 8) NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference_code` VARCHAR(30) NOT NULL UNIQUE,
  `type` ENUM('lost', 'found') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `brand` VARCHAR(100) NULL,
  `model` VARCHAR(100) NULL,
  `color` VARCHAR(50) NULL,
  `serial_number` VARCHAR(100) NULL,
  `identification_details` TEXT NULL,
  `date_lost` DATE NULL,
  `date_found` DATE NULL,
  `time_lost` TIME NULL,
  `time_found` TIME NULL,
  `location_id` INT UNSIGNED NOT NULL,
  `reported_by` INT UNSIGNED NOT NULL,
  `has_physical_possession` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('pending', 'approved', 'rejected', 'available', 'claimed', 'under_verification', 'ready_for_handover', 'returned', 'unclaimed', 'disposed', 'cancelled') NOT NULL DEFAULT 'pending',
  `privacy_level` ENUM('public', 'restricted') NOT NULL DEFAULT 'public',
  `approved_by` INT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_items_category` (`category_id`),
  KEY `fk_items_location` (`location_id`),
  KEY `fk_items_reporter` (`reported_by`),
  KEY `fk_items_approver` (`approved_by`),
  CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_items_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `fk_items_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `item_images`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `item_images`;
CREATE TABLE `item_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_images_item` (`item_id`),
  CONSTRAINT `fk_images_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `claims`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `claims`;
CREATE TABLE `claims` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_code` VARCHAR(30) NOT NULL UNIQUE,
  `item_id` INT UNSIGNED NOT NULL,
  `claimant_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending', 'under_review', 'approved', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_claims_item` (`item_id`),
  KEY `fk_claims_claimant` (`claimant_id`),
  KEY `fk_claims_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_claims_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claims_claimant` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claims_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `claim_answers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `claim_answers`;
CREATE TABLE `claim_answers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_id` INT UNSIGNED NOT NULL,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_claim_answers_claim` (`claim_id`),
  CONSTRAINT `fk_claim_answers_claim` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `storage_locations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `storage_locations`;
CREATE TABLE `storage_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `room` VARCHAR(50) NOT NULL,
  `shelf` VARCHAR(50) NOT NULL,
  `box` VARCHAR(50) NULL,
  `position` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'full', 'maintenance') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `item_storage`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `item_storage`;
CREATE TABLE `item_storage` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL UNIQUE,
  `storage_location_id` INT UNSIGNED NOT NULL,
  `received_by` INT UNSIGNED NOT NULL,
  `received_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item_storage_item` (`item_id`),
  KEY `fk_item_storage_location` (`storage_location_id`),
  KEY `fk_item_storage_receiver` (`received_by`),
  CONSTRAINT `fk_item_storage_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_storage_location` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`),
  CONSTRAINT `fk_item_storage_receiver` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `handovers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `handovers`;
CREATE TABLE `handovers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT UNSIGNED NOT NULL,
  `claim_id` INT UNSIGNED NOT NULL,
  `owner_id` INT UNSIGNED NOT NULL,
  `officer_id` INT UNSIGNED NOT NULL,
  `handover_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `owner_signature` TEXT NULL,
  `officer_signature` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_handovers_item` (`item_id`),
  KEY `fk_handovers_claim` (`claim_id`),
  KEY `fk_handovers_owner` (`owner_id`),
  KEY `fk_handovers_officer` (`officer_id`),
  CONSTRAINT `fk_handovers_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `fk_handovers_claim` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`),
  CONSTRAINT `fk_handovers_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_handovers_officer` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'system',
  `link` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `item_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_messages_sender` (`sender_id`),
  KEY `fk_messages_receiver` (`receiver_id`),
  KEY `fk_messages_item` (`item_id`),
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `potential_matches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `potential_matches`;
CREATE TABLE `potential_matches` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lost_item_id` INT UNSIGNED NOT NULL,
  `found_item_id` INT UNSIGNED NOT NULL,
  `match_score` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  `matched_factors` TEXT NULL,
  `status` ENUM('pending', 'viewed', 'dismissed', 'confirmed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_matches_lost` (`lost_item_id`),
  KEY `fk_matches_found` (`found_item_id`),
  CONSTRAINT `fk_matches_lost` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_matches_found` FOREIGN KEY (`found_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `audit_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NULL,
  `old_data` TEXT NULL,
  `new_data` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `system_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- ========================================================

-- System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('unclaimed_days', '90', 'Number of days before an unclaimed item is eligible for disposal/donation'),
('require_approval', '1', 'Require officer/admin approval before items become publicly visible'),
('max_images', '5', 'Maximum images allowed per reported item'),
('max_upload_size', '5242880', 'Maximum file upload size in bytes (5MB)'),
('match_threshold', '70', 'Score threshold percentage for triggering match notifications'),
('university_name', 'Metropolitan State University', 'University Display Name'),
('contact_email', 'lostfound@university.edu', 'Contact email for Lost & Found office'),
('office_location', 'Student Center, Ground Floor, Room 104', 'Physical office location');

-- Faculties
INSERT INTO `faculties` (`id`, `name`, `code`, `description`) VALUES
(1, 'Faculty of Computing & Information Technology', 'FCIT', 'Computer Science, Software Engineering, Information Systems'),
(2, 'Faculty of Engineering & Built Environment', 'FEBE', 'Civil, Electrical, Mechanical Engineering'),
(3, 'Faculty of Business & Economics', 'FBE', 'Accounting, Finance, Business Administration'),
(4, 'Faculty of Health Sciences & Medicine', 'FHSM', 'Nursing, Pharmacy, Biomedical Sciences');

-- Departments
INSERT INTO `departments` (`id`, `faculty_id`, `name`, `code`, `description`) VALUES
(1, 1, 'Department of Computer Science', 'CS', 'Core Computing & Algorithms'),
(2, 1, 'Department of Software Engineering', 'SE', 'Application Development & Systems'),
(3, 2, 'Department of Electrical Engineering', 'EE', 'Power & Electronics'),
(4, 2, 'Department of Mechanical Engineering', 'ME', 'Mechanics & Robotics'),
(5, 3, 'Department of Accounting & Finance', 'AF', 'Financial Management'),
(6, 4, 'Department of Pharmacy', 'PHARM', 'Pharmaceutical Sciences');

-- Categories
INSERT INTO `categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Electronics', 'Laptops, smartphones, tablets, chargers, headphones', 'bi-laptop'),
(2, 'Documents', 'Student IDs, driver licenses, passports, notes, certificates', 'bi-file-earmark-text'),
(3, 'Bags', 'Backpacks, totes, laptop sleeves, handbags', 'bi-bag'),
(4, 'Wallets', 'Wallets, coin purses, card holders, money clips', 'bi-wallet2'),
(5, 'Keys', 'Dorm keys, car keys, smart keyfobs, locker keys', 'bi-key'),
(6, 'Clothing', 'Jackets, hoodies, caps, scarves, lab coats', 'bi-tag'),
(7, 'Books', 'Textbooks, notebooks, library books, novels', 'bi-book'),
(8, 'Jewelry', 'Watches, rings, necklaces, bracelets', 'bi-gem'),
(9, 'Accessories', 'Water bottles, umbrellas, glasses, calculators', 'bi-sunglasses'),
(10, 'Other', 'Items not falling under the standard categories', 'bi-box-seam');

-- Locations
INSERT INTO `locations` (`id`, `name`, `building`, `campus`, `description`, `latitude`, `longitude`) VALUES
(1, 'Main Library', 'University Library Complex', 'Main Campus', 'Floors 1-4, quiet study areas and computer labs', 37.774929, -122.419416),
(2, 'Administration Building', 'Central Hall', 'Main Campus', 'Registrar, Financial Aid, Admissions Offices', 37.775300, -122.418800),
(3, 'Faculty of Computing', 'Alan Turing Building', 'Main Campus', 'Computer Labs, Lecture Halls A1-A4', 37.776100, -122.420200),
(4, 'Faculty of Engineering', 'Tesla Engineering Complex', 'North Campus', 'Workshops, Design Studios, Labs', 37.776800, -122.421500),
(5, 'Student Center', 'Student Union Building', 'Main Campus', 'Cafeteria, Club Rooms, Lost & Found Office', 37.774200, -122.419900),
(6, 'Central Cafeteria', 'Dining Hall A', 'Main Campus', 'Main dining area, food stalls, outdoor patio', 37.774500, -122.420800),
(7, 'Campus Mosque / Prayer Center', 'Faith & Reflection Center', 'West Campus', 'Prayer halls, ablution areas, courtyard', 37.773800, -122.421900),
(8, 'North Parking Area', 'Parking Structure 2', 'North Campus', 'Multi-level student and staff parking', 37.777200, -122.422500),
(9, 'Sports Complex & Gymnasium', 'Athletics Center', 'South Campus', 'Indoor courts, gym, swimming pool, bleachers', 37.773100, -122.418200),
(10, 'Science Lecture Theatre Block', 'Nobel Hall', 'Main Campus', 'Auditoriums LT1, LT2, LT3', 37.775800, -122.417900);

-- Storage Locations
INSERT INTO `storage_locations` (`id`, `name`, `room`, `shelf`, `box`, `position`, `description`) VALUES
(1, 'L&F Secure Vault A', 'Room 104', 'Shelf A (High Value)', 'Box 01', 'Position 01', 'For smartphones, laptops, jewelry, wallets'),
(2, 'L&F Secure Vault B', 'Room 104', 'Shelf A (High Value)', 'Box 02', 'Position 05', 'For tablets, smartwatches, luxury items'),
(3, 'General Storage Rack 1', 'Room 104', 'Shelf B (Backpacks)', 'Bin 01', 'Position 02', 'For bags, backpacks, laptop cases'),
(4, 'Document Filing Cabinet', 'Room 104', 'Shelf C (IDs & Cards)', 'Drawer 01', 'Folder A-Z', 'For student cards, licenses, folders'),
(5, 'Key Safe Locker', 'Room 104', 'Shelf D (Key Pegs)', 'Safe 01', 'Hook 14', 'For physical keys, dorm fobs, car remotes');

-- Demo Users (Passwords hashed with BCRYPT: Admin@12345, Officer@12345, Student@12345)
-- Admin: $2y$10$eE6i0j5l0jA8mX/u5q4W7.0m54l3P2pA5nK/hQ4B2sC6dE8fG0hKi -> Admin@12345
-- Using standard password_hash('Admin@12345', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `university_id`, `full_name`, `email`, `phone`, `password`, `role`, `faculty_id`, `department_id`, `status`, `email_verified`) VALUES
(1, 'ADM-2026-001', 'System Administrator', 'admin@university.local', '+1 (555) 019-2831', '$2y$10$Upm2ZgT222jM20d0fW02AOk5rKk8a3JgW/vO/37mR15P/C3n99N2a', 'admin', 1, 1, 'active', 1),
(2, 'OFF-2026-002', 'Officer Sarah Jenkins', 'officer@university.local', '+1 (555) 019-4455', '$2y$10$wE8Fz11vUaR8.W4E/uP20uXlWpL1/447V7O1G8B76/hR15P/C3n99', 'officer', 1, 2, 'active', 1),
(3, 'STU-2026-101', 'Alex Mercer', 'student@university.local', '+1 (555) 019-7788', '$2y$10$oY7b43V5QZ2R54x9W008y.m637e1.lQvU03b9P781L23P/C3n99N2', 'student', 1, 2, 'active', 1),
(4, 'STU-2026-102', 'Emma Watson', 'emma.watson@student.university.edu', '+1 (555) 019-9922', '$2y$10$oY7b43V5QZ2R54x9W008y.m637e1.lQvU03b9P781L23P/C3n99N2', 'student', 3, 5, 'active', 1),
(5, 'STF-2026-201', 'Dr. Robert Langdon', 'robert.langdon@faculty.university.edu', '+1 (555) 019-3344', '$2y$10$oY7b43V5QZ2R54x9W008y.m637e1.lQvU03b9P781L23P/C3n99N2', 'staff', 2, 3, 'active', 1);

-- Items
INSERT INTO `items` (`id`, `reference_code`, `type`, `title`, `description`, `category_id`, `brand`, `model`, `color`, `serial_number`, `identification_details`, `date_lost`, `date_found`, `time_lost`, `time_found`, `location_id`, `reported_by`, `has_physical_possession`, `status`, `privacy_level`, `approved_by`, `approved_at`) VALUES
(1, 'FOUND-2026-000001', 'found', 'Midnight Blue Apple MacBook Pro 14"', 'Found on a study desk on 3rd floor quiet zone. Space Grey finish with minor scratch on corner.', 1, 'Apple', 'MacBook Pro 14 (M2)', 'Midnight Blue', 'C02G8790MD6T', 'Private desktop sticker of Python logo, blue silicone keyboard protector', NULL, '2026-08-10', NULL, '14:30:00', 1, 2, 1, 'available', 'public', 2, '2026-08-10 15:00:00'),
(2, 'FOUND-2026-000002', 'found', 'Black Leather Trifold Wallet', 'Found near the coffee vending machine in Student Center. Contains university smartcard and cards.', 4, 'Tommy Hilfiger', 'Leather Trifold', 'Black', NULL, 'Cardholder has initials J.D. embossed inside with student ID card', NULL, '2026-08-12', NULL, '11:15:00', 5, 2, 1, 'available', 'public', 2, '2026-08-12 12:00:00'),
(3, 'FOUND-2026-000003', 'found', 'Sony WH-1000XM5 Wireless Headphones', 'Silver/Beige noise cancelling over-ear headphones left in Alan Turing lab 3.', 1, 'Sony', 'WH-1000XM5', 'Silver', 'S01-8849120', 'Has custom black zippered protective hardcase with carabiner clip', NULL, '2026-08-14', NULL, '16:45:00', 3, 2, 1, 'claimed', 'public', 2, '2026-08-14 17:30:00'),
(4, 'LOST-2026-000001', 'lost', 'Grey Herschel Little America Backpack', 'Left near the central cafeteria benches after lunch. Contains notebooks and a stainless water bottle.', 3, 'Herschel', 'Little America 25L', 'Grey', NULL, 'Key ring attached to front strap with small astronaut keychain and CS flash drive', '2026-08-13', NULL, '13:10:00', NULL, 6, 3, 0, 'approved', 'public', 2, '2026-08-13 14:00:00'),
(5, 'LOST-2026-000002', 'lost', 'Samsung Galaxy S23 Ultra', 'Phantom Black smartphone misplaced during lecture in LT1. Clear case with card slot.', 1, 'Samsung', 'Galaxy S23 Ultra', 'Phantom Black', 'SM918UZKAXAA', 'Lock screen picture of a golden retriever puppy, chipped top-right glass protector', '2026-08-15', NULL, '10:00:00', NULL, 10, 4, 0, 'approved', 'public', 2, '2026-08-15 10:45:00'),
(6, 'FOUND-2026-000004', 'found', 'Samsung Galaxy S23 Phantom Black', 'Found under seat in Nobel Hall Lecture Theatre 1.', 1, 'Samsung', 'Galaxy S23 Ultra', 'Phantom Black', 'SM918UZKAXAA', 'Clear protective case with small scratch', NULL, '2026-08-15', NULL, '11:30:00', 10, 2, 1, 'under_verification', 'public', 2, '2026-08-15 12:00:00'),
(7, 'FOUND-2026-000005', 'found', 'Set of 3 Keys with Toyota Fob', 'Found on bench near sports complex bleachers.', 5, 'Toyota', 'Smart Keyfob', 'Black / Silver', NULL, 'Red ribbon keychain with dorm room tag 304', NULL, '2026-08-16', NULL, '09:00:00', 9, 2, 1, 'available', 'public', 2, '2026-08-16 09:30:00');

-- Item Images
INSERT INTO `item_images` (`id`, `item_id`, `image_path`, `is_primary`) VALUES
(1, 1, 'macbook_pro.jpg', 1),
(2, 2, 'leather_wallet.jpg', 1),
(3, 3, 'sony_headphones.jpg', 1),
(4, 4, 'herschel_backpack.jpg', 1),
(5, 5, 'samsung_s23.jpg', 1),
(6, 6, 'samsung_found.jpg', 1),
(7, 7, 'toyota_keys.jpg', 1);

-- Physical Storage for found items
INSERT INTO `item_storage` (`id`, `item_id`, `storage_location_id`, `received_by`, `received_at`, `notes`) VALUES
(1, 1, 1, 2, '2026-08-10 15:10:00', 'Secured in Vault A, Box 01. Battery charged at 60%.'),
(2, 2, 1, 2, '2026-08-12 12:15:00', 'Secured in Vault A, Box 02 with ID cards preserved.'),
(3, 3, 2, 2, '2026-08-14 17:40:00', 'In hardcase. Awaiting claimant verification.'),
(4, 6, 1, 2, '2026-08-15 12:10:00', 'Secured in electronics vault. Match identified with report LOST-2026-000002.'),
(5, 7, 5, 2, '2026-08-16 09:45:00', 'Hung on safe hook 14.');

-- Claims
INSERT INTO `claims` (`id`, `claim_code`, `item_id`, `claimant_id`, `reason`, `status`, `reviewed_by`, `reviewed_at`) VALUES
(1, 'CLM-2026-000001', 3, 3, 'I left my Sony XM5 headphones in Alan Turing lab 3 after working on my capstone project.', 'under_review', 2, '2026-08-15 09:00:00'),
(2, 'CLM-2026-000002', 6, 4, 'I lost my Samsung Galaxy S23 Ultra in LT1 during morning biology lecture.', 'approved', 2, '2026-08-15 13:00:00');

-- Claim Answers
INSERT INTO `claim_answers` (`id`, `claim_id`, `question`, `answer`) VALUES
(1, 1, 'Where and when did you lose this item?', 'Alan Turing Computer Lab 3 on August 14 around 4:30 PM.'),
(2, 1, 'Describe a unique feature or marking not publicly visible.', 'Inside the custom case there is an orange 3.5mm audio adapter cable and my initials A.M. on the inner headband cushion.'),
(3, 2, 'Where and when did you lose this item?', 'Nobel Hall LT1 on August 15 at 10 AM.'),
(4, 2, 'Describe a unique feature or marking not publicly visible.', 'Wallpaper is my golden retriever puppy "Barnaby" and lock code ends with 49.');

-- Potential Matches
INSERT INTO `potential_matches` (`id`, `lost_item_id`, `found_item_id`, `match_score`, `matched_factors`, `status`) VALUES
(1, 5, 6, 94.50, 'Category: Electronics (20/20), Brand: Samsung (15/15), Model: Galaxy S23 Ultra (15/15), Color: Phantom Black (10/10), Location: LT1 (15/15), Date: Aug 15 (15/15), Description similarity (4.5/10)', 'confirmed');

-- Notifications
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `link`, `is_read`) VALUES
(1, 4, 'High Potential Match Detected! (94%)', 'A found Samsung Galaxy S23 Ultra closely matching your lost report LOST-2026-000002 has been turned in at the Lost & Found office.', 'match', 'item-details.php?ref=FOUND-2026-000004', 0),
(2, 4, 'Claim Approved: Ready for Handover', 'Your claim CLM-2026-000002 for Samsung Galaxy S23 Ultra has been approved! Visit Room 104 with your Student ID to collect.', 'claim_approved', 'my-claims.php', 0),
(3, 3, 'Claim Under Review', 'Officer Sarah Jenkins has begun reviewing your claim CLM-2026-000001 for Sony WH-1000XM5.', 'claim_update', 'my-claims.php', 1),
(4, 2, 'New Found Item Submitted', 'New item FOUND-2026-000005 reported by campus security at Sports Complex.', 'officer_alert', 'officer/report-view.php?id=7', 0);

-- Messages
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `item_id`, `message`, `is_read`) VALUES
(1, 4, 2, 6, 'Hello Officer, I submitted claim CLM-2026-000002. Can I bring my student ID and purchase invoice at 2 PM today?', 1),
(2, 2, 4, 6, 'Hello Emma, yes absolutely! Please come to Student Center Room 104 before 4:30 PM with your physical student card.', 0);

-- Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_data`, `new_data`, `ip_address`, `user_agent`) VALUES
(1, 1, 'system_init', 'database', 1, NULL, 'Initial database schema and seeds deployed', '127.0.0.1', 'System Installer'),
(2, 2, 'approve_report', 'item', 1, 'status: pending', 'status: available, approved_by: 2', '127.0.0.1', 'Mozilla/5.0 Chrome/128.0'),
(3, 2, 'assign_storage', 'item_storage', 1, NULL, 'Assigned item 1 to Vault A, Box 01', '127.0.0.1', 'Mozilla/5.0 Chrome/128.0'),
(4, 4, 'submit_claim', 'claim', 2, NULL, 'Claim CLM-2026-000002 submitted for item 6', '127.0.0.1', 'Mozilla/5.0 Chrome/128.0'),
(5, 2, 'approve_claim', 'claim', 2, 'status: pending', 'status: approved', '127.0.0.1', 'Mozilla/5.0 Chrome/128.0');

COMMIT;

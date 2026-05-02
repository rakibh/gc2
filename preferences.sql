/* IT Management System - User Preferences Table */
/* This table stores individual settings for each user. */

USE `gc2`;

CREATE TABLE IF NOT EXISTS `user_preferences` (
    `user_id` INT PRIMARY KEY,
    `theme` ENUM('light', 'dark') DEFAULT 'light',
    `timezone` VARCHAR(50) DEFAULT 'Asia/Dhaka',
    `time_format` ENUM('12', '24') DEFAULT '12',
    `desktop_notifications` BOOLEAN DEFAULT FALSE,
    `toast_position` ENUM('top-right', 'bottom-right') DEFAULT 'top-right',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_user_prefs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Initialize preferences for existing users */
INSERT IGNORE INTO `user_preferences` (`user_id`) SELECT `id` FROM `users`;

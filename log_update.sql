-- IT Management System - System Logs Upgrade Migration
-- This script upgrades the system_logs table and initializes log retention settings.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Recreate system_logs table with advanced schema
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `level` ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    `category` VARCHAR(50) DEFAULT 'system',
    `user_id` INT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `message` TEXT NOT NULL,
    `context` JSON,
    INDEX idx_log_level (`level`),
    INDEX idx_log_category (`category`),
    INDEX idx_log_user (`user_id`),
    INDEX idx_log_timestamp (`timestamp`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Initialize System Settings for Log Retention
-- Using modern MySQL syntax to avoid deprecation warnings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) 
VALUES ('log_retention_days', '90') AS new_vals
ON DUPLICATE KEY UPDATE `setting_value` = new_vals.setting_value;

INSERT INTO `system_settings` (`setting_key`, `setting_value`) 
VALUES ('enable_log_cleanup', '1') AS new_vals
ON DUPLICATE KEY UPDATE `setting_value` = new_vals.setting_value;

-- 3. Create Automated Cleanup Event
-- Note: Requires GLOBAL event_scheduler = ON in MySQL config
SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0;
DROP EVENT IF EXISTS `cleanup_system_logs`;
SET SQL_NOTES=@OLD_SQL_NOTES;
CREATE EVENT `cleanup_system_logs`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
COMMENT 'Automatically deletes system logs older than the configured retention period'
DO
  DELETE FROM `system_logs` 
  WHERE `timestamp` < NOW() - INTERVAL (
    SELECT CAST(IFNULL(setting_value, '90') AS UNSIGNED) 
    FROM `system_settings` 
    WHERE `setting_key` = 'log_retention_days'
  ) DAY;

SET FOREIGN_KEY_CHECKS = 1;

-- IT Management System - Session Timeout Setting (Modern Syntax)
-- This script adds the session_timeout setting to the system_settings table.

INSERT INTO `system_settings` (`setting_key`, `setting_value`) 
VALUES ('session_timeout', '60') AS new_vals
ON DUPLICATE KEY UPDATE `setting_value` = new_vals.setting_value;

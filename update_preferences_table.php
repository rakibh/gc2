<?php
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');

try {
    $db = Core\Database::getInstance();
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM user_preferences LIKE 'notification_types'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Adding notification_types column..." . PHP_EOL;
        $db->exec("ALTER TABLE user_preferences ADD COLUMN notification_types JSON DEFAULT NULL AFTER desktop_notifications");
        echo "Column added successfully." . PHP_EOL;
    } else {
        echo "Column notification_types already exists." . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

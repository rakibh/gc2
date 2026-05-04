<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = \Core\Database::getInstance()->getConnection();
    
    echo "--- Equipments ---\n";
    $equipments = $db->query("SELECT status, COUNT(*) as count FROM equipments GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    print_r($equipments);
    
    echo "\n--- Tasks ---\n";
    $tasks = $db->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    print_r($tasks);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

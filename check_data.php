<?php
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');
try {
    $db = Core\Database::getInstance()->getConnection();
    
    echo "--- Equipments Status ---" . PHP_EOL;
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM equipments GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['status']}: {$row['count']}" . PHP_EOL;
    }

    echo PHP_EOL . "--- Tasks Status ---" . PHP_EOL;
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['status']}: {$row['count']}" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

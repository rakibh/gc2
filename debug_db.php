<?php
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');
try {
    $db = Core\Database::getInstance();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

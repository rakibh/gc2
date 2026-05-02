<?php
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');
try {
    $db = Core\Database::getInstance();
    $cols = $db->query("DESCRIBE system_logs")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

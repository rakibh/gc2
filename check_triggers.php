<?php
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');
putenv('DB_HOST=127.0.0.1');
try {
    $db = Core\Database::getInstance();
    $triggers = $db->query("SHOW TRIGGERS")->fetchAll();
    echo "Triggers: " . count($triggers) . "\n";
    foreach ($triggers as $t) {
        print_r($t);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

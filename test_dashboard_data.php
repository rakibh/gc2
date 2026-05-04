<?php
require 'config/config.php';
require 'core/Database.php';
require 'core/Env.php';
Core\Env::load('.env');

require 'modules/dashboard/DashboardRepository.php';

try {
    $repo = new \Modules\Dashboard\DashboardRepository();
    $chartData = $repo->getChartData();
    
    echo "Chart Data:" . PHP_EOL;
    echo json_encode($chartData, JSON_PRETTY_PRINT) . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

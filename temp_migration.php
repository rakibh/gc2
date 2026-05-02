<?php
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Repository.php';
require_once 'modules/admin/AdminRepository.php';

use Modules\Admin\AdminRepository;

try {
    $adminRepo = new AdminRepository();
    $db = $adminRepo->getDb();
    $db->exec("ALTER TABLE equipments ADD COLUMN images JSON AFTER custom_data");
    echo "Migration successful: Added images column to equipments table.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}

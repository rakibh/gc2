<?php

declare(strict_types=1);

namespace Modules\Admin;

use Core\Repository;
use PDO;

class GlobalSearchRepository extends Repository
{
    /**
     * Perform a global search across multiple modules.
     */
    public function search(string $query): array
    {
        $term = '%' . $query . '%';
        $results = [];

        // 1. Search Equipment
        $stmt = $this->db->prepare("
            SELECT 'equipment' as type, id, CONCAT(brand, ' ', model) as title, serial_number as subtitle 
            FROM equipments 
            WHERE serial_number LIKE ? OR brand LIKE ? OR model LIKE ? OR name LIKE ?
            LIMIT 5
        ");
        $stmt->execute([$term, $term, $term, $term]);
        foreach ($stmt->fetchAll() as $row) {
            $row['url'] = 'index.php?route=view_equipment&id=' . $row['id'];
            $row['icon'] = 'bi-pc-display';
            $results[] = $row;
        }

        // 2. Search Tasks
        $stmt = $this->db->prepare("
            SELECT 'task' as type, id, title, status as subtitle 
            FROM tasks 
            WHERE title LIKE ? OR description LIKE ? OR tags LIKE ?
            LIMIT 5
        ");
        $stmt->execute([$term, $term, $term]);
        foreach ($stmt->fetchAll() as $row) {
            $row['url'] = 'index.php?route=view_task&id=' . $row['id'];
            $row['icon'] = 'bi-list-task';
            $results[] = $row;
        }

        // 3. Search Network
        $stmt = $this->db->prepare("
            SELECT 'network' as type, id, ip_address as title, cable_no as subtitle 
            FROM network_info 
            WHERE ip_address LIKE ? OR mac_address LIKE ? OR cable_no LIKE ? OR remarks LIKE ?
            LIMIT 5
        ");
        $stmt->execute([$term, $term, $term, $term]);
        foreach ($stmt->fetchAll() as $row) {
            $row['url'] = 'index.php?route=view_network&id=' . $row['id'];
            $row['icon'] = 'bi-diagram-3';
            $results[] = $row;
        }

        // 4. Search Users
        $stmt = $this->db->prepare("
            SELECT 'user' as type, id, username as title, role as subtitle 
            FROM users 
            WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ?
            LIMIT 5
        ");
        $stmt->execute([$term, $term, $term]);
        foreach ($stmt->fetchAll() as $row) {
            $row['url'] = 'index.php?route=profile&id=' . $row['id'];
            $row['icon'] = 'bi-person';
            $results[] = $row;
        }

        return $results;
    }
}

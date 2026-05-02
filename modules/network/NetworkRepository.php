<?php

declare(strict_types=1);

namespace Modules\Network;

use Core\Repository;
use PDO;
use Exception;

class NetworkRepository extends Repository
{
    /**
     * Get network records with pagination, sorting, and filtering.
     */
    public function getNetworks(int $page = 1, int $limit = 20, string $sortBy = 'created_at', string $sortDir = 'DESC', array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT n.*, e.brand, e.model, e.serial_number, et.name as type_name, e.id as equipment_id
            FROM network_info n
            LEFT JOIN equipment_network_map enm ON n.id = enm.network_id
            LEFT JOIN equipments e ON enm.equipment_id = e.id
            LEFT JOIN equipment_types et ON e.type_id = et.id
            WHERE 1=1
        ";
        
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (n.ip_address LIKE :search OR n.cable_no LIKE :search OR n.remarks LIKE :search OR e.serial_number LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'assigned') {
                $query .= " AND e.id IS NOT NULL";
            } elseif ($filters['status'] === 'unassigned') {
                $query .= " AND e.id IS NULL";
            }
        }

        // Sorting
        $allowedSort = ['ip_address', 'created_at', 'cable_no', 'patch_panel_no', 'switch_no', 'status'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
        
        // Handle 'status' sorting (assigned/unassigned)
        $orderClause = "n.$sortBy";
        if ($sortBy === 'status') {
            $orderClause = "CASE WHEN e.id IS NOT NULL THEN 'assigned' ELSE 'unassigned' END";
        }
        
        $query .= " ORDER BY $orderClause $sortDir LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $networks = $stmt->fetchAll();

        // Get total for pagination
        $countQuery = "
            SELECT COUNT(DISTINCT n.id) 
            FROM network_info n
            LEFT JOIN equipment_network_map enm ON n.id = enm.network_id
            LEFT JOIN equipments e ON enm.equipment_id = e.id
            WHERE 1=1
        ";
        // Re-apply same filters for count
        if (!empty($filters['search'])) {
            $countQuery .= " AND (n.ip_address LIKE :search OR n.cable_no LIKE :search OR n.remarks LIKE :search OR e.serial_number LIKE :search)";
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'assigned') { $countQuery .= " AND e.id IS NOT NULL"; }
            elseif ($filters['status'] === 'unassigned') { $countQuery .= " AND e.id IS NULL"; }
        }

        $countStmt = $this->db->prepare($countQuery);
        if (!empty($filters['search'])) {
            $countStmt->execute(['search' => '%' . $filters['search'] . '%']);
        } else {
            $countStmt->execute();
        }
        $total = (int)$countStmt->fetchColumn();

        return [
            'networks' => $networks,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get a single network record by ID.
     */
    public function getNetworkById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, e.id as equipment_id, e.brand, e.model, e.serial_number, et.name as type_name
            FROM network_info n
            LEFT JOIN equipment_network_map enm ON n.id = enm.network_id
            LEFT JOIN equipments e ON enm.equipment_id = e.id
            LEFT JOIN equipment_types et ON e.type_id = et.id
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create new network record.
     */
    public function createNetwork(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO network_info (ip_address, cable_no, patch_panel_no, patch_panel_port, patch_panel_location, switch_no, switch_port, switch_location, remarks)
                VALUES (:ip, :cable, :pp_no, :pp_port, :pp_loc, :sw_no, :sw_port, :sw_loc, :remarks)
            ");
            
            $stmt->execute([
                'ip' => $data['ip_address'],
                'cable' => $data['cable_no'] ?? null,
                'pp_no' => $data['patch_panel_no'] ?? null,
                'pp_port' => $data['patch_panel_port'] ?? null,
                'pp_loc' => $data['patch_panel_location'] ?? null,
                'sw_no' => $data['switch_no'] ?? null,
                'sw_port' => $data['switch_port'] ?? null,
                'sw_loc' => $data['switch_location'] ?? null,
                'remarks' => $data['remarks'] ?? null
            ]);

            $networkId = (int)$this->db->lastInsertId();

            if (!empty($data['equipment_id'])) {
                $this->assignEquipment($networkId, (int)$data['equipment_id']);
            }

            // Notify
            $notificationRepo = new \Modules\Notifications\NotificationRepository();
            $notificationRepo->createGlobal('network', "New network node added: " . $data['ip_address'], 'medium', ['network_id' => $networkId]);

            $this->db->commit();
            return $networkId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update network record.
     */
    public function updateNetwork(int $id, array $data): bool
    {
        $oldNetwork = $this->getNetworkById($id);
        if (!$oldNetwork) return false;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE network_info SET 
                ip_address = :ip, cable_no = :cable, 
                patch_panel_no = :pp_no, patch_panel_port = :pp_port, 
                patch_panel_location = :pp_loc,
                switch_no = :sw_no, switch_port = :sw_port,
                switch_location = :sw_loc,
                remarks = :remarks
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'ip' => $data['ip_address'],
                'cable' => $data['cable_no'] ?? null,
                'pp_no' => $data['patch_panel_no'] ?? null,
                'pp_port' => $data['patch_panel_port'] ?? null,
                'pp_loc' => $data['patch_panel_location'] ?? null,
                'sw_no' => $data['switch_no'] ?? null,
                'sw_port' => $data['switch_port'] ?? null,
                'sw_loc' => $data['switch_location'] ?? null,
                'remarks' => $data['remarks'] ?? null
            ]);

            // Sync Equipment Mapping
            $this->db->prepare("DELETE FROM equipment_network_map WHERE network_id = ?")->execute([$id]);
            if (!empty($data['equipment_id'])) {
                $this->assignEquipment($id, (int)$data['equipment_id']);
            } elseif ($oldNetwork['equipment_id']) {
                // Unassigned
                $notificationRepo = new \Modules\Notifications\NotificationRepository();
                $notificationRepo->createGlobal('network', "Equipment unassigned from network node: " . $data['ip_address'], 'medium', ['network_id' => $id]);
            }

            // Notify
            $notificationRepo = new \Modules\Notifications\NotificationRepository();
            $notificationRepo->createGlobal('network', "Network node updated: " . $data['ip_address'], 'medium', ['network_id' => $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Assign equipment to a network node.
     */
    public function assignEquipment(int $networkId, int $equipmentId): void
    {
        // First check if equipment is already assigned elsewhere (PRD: unassigned only)
        $this->db->prepare("DELETE FROM equipment_network_map WHERE equipment_id = ?")->execute([$equipmentId]);
        
        $stmt = $this->db->prepare("INSERT INTO equipment_network_map (network_id, equipment_id) VALUES (?, ?)");
        $stmt->execute([$networkId, $equipmentId]);

        // Notify
        $network = $this->db->query("SELECT ip_address FROM network_info WHERE id = $networkId")->fetch();
        if ($network) {
            $notificationRepo = new \Modules\Notifications\NotificationRepository();
            $notificationRepo->createGlobal('network', "Equipment assigned to network node: " . $network['ip_address'], 'medium', ['network_id' => $networkId, 'equipment_id' => $equipmentId]);
        }
    }

    /**
     * Delete network record (Only if NOT assigned to equipment).
     */
    public function deleteNetwork(int $id): bool
    {
        $network = $this->getNetworkById($id);
        if (!$network) return false;

        // Check assignment
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM equipment_network_map WHERE network_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Cannot delete network info assigned to an equipment. Unassign first.");
        }

        $stmt = $this->db->prepare("DELETE FROM network_info WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            $notificationRepo = new \Modules\Notifications\NotificationRepository();
            $notificationRepo->createGlobal('network', "Network node deleted: " . $network['ip_address'], 'medium');
        }

        return $result;
    }

    /**
     * Get unassigned equipment for dropdown.
     */
    public function getUnassignedEquipment(): array
    {
        $query = "
            SELECT e.id, e.serial_number, et.name as type_name, e.brand
            FROM equipments e
            JOIN equipment_types et ON e.type_id = et.id
            LEFT JOIN equipment_network_map enm ON e.id = enm.equipment_id
            WHERE enm.equipment_id IS NULL
        ";
        return $this->db->query($query)->fetchAll();
    }
}

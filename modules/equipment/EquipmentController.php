<?php

declare(strict_types=1);

namespace Modules\Equipment;

use Core\Session;
use Exception;

class EquipmentController
{
    private EquipmentRepository $equipmentRepository;
    private EquipmentBlockRepository $blockRepository;

    public function __construct()
    {
        $this->equipmentRepository = new EquipmentRepository();
        $this->blockRepository = new EquipmentBlockRepository();
    }

    /**
     * Manage Predefined Blocks (Admin only).
     */
    public function blocks(): array
    {
        if (Session::get('role') !== 'admin') {
            throw new Exception("Access denied.");
        }

        return [
            'title' => 'Manage Predefined Blocks',
            'view' => 'views/equipment/blocks.php',
            'data' => [
                'blocks' => $this->blockRepository->getAll()
            ]
        ];
    }

    /**
     * Save predefined block (AJAX).
     */
    public function saveBlock(array $data): array
    {
        if (Session::get('role') !== 'admin') {
            return ['success' => false, 'message' => 'Access denied.'];
        }

        if (!Session::validateCsrfToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        try {
            $this->blockRepository->save($data);
            return ['success' => true, 'message' => 'Predefined block saved.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete predefined block (AJAX).
     */
    public function deleteBlock(int $id): array
    {
        if (Session::get('role') !== 'admin') {
            return ['success' => false, 'message' => 'Access denied.'];
        }

        try {
            if ($this->blockRepository->isInUse($id)) {
                return [
                    'success' => false, 
                    'message' => 'Cannot delete block: It is currently attached to one or more Equipment Types. Please detach it first.'
                ];
            }

            $this->blockRepository->delete($id);
            return ['success' => true, 'message' => 'Block deleted.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * List equipment inventory.
     */
    public function list(): array
    {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'type_id' => $_GET['type_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $res = $this->equipmentRepository->getEquipments($filters, $page);

        return [
            'title' => 'Equipment Inventory',
            'view' => 'views/equipment/list.php',
            'data' => [
                'equipments' => $res['items'],
                'total' => $res['total'],
                'pages' => $res['pages'],
                'currentPage' => $page,
                'filters' => $filters,
                'types' => $this->equipmentRepository->getTypes()
            ]
        ];
    }

    /**
     * Show add equipment form.
     */
    public function add(): array
    {
        return [
            'title' => 'Add Equipment',
            'view' => 'views/equipment/add_edit.php',
            'data' => [
                'equipment' => null,
                'isEdit' => false,
                'types' => $this->equipmentRepository->getTypes(),
                'blocks' => $this->blockRepository->getAll()
            ]
        ];
    }

    /**
     * Show edit equipment form.
     */
    public function edit(int $id): array
    {
        $equipment = $this->equipmentRepository->getEquipmentById($id);
        if (!$equipment) throw new Exception("Equipment not found.");

        return [
            'title' => 'Edit Equipment: ' . $equipment['serial_number'],
            'view' => 'views/equipment/add_edit.php',
            'data' => [
                'equipment' => $equipment,
                'isEdit' => true,
                'types' => $this->equipmentRepository->getTypes(),
                'blocks' => $this->blockRepository->getAll()
            ]
        ];
    }

    /**
     * Save equipment (AJAX/FormData).
     */
    public function save(array $data): array
    {
        // For FormData, values like 'network' and 'custom_data' come as JSON strings
        if (is_string($data['network'] ?? null)) {
            $data['network'] = json_decode($data['network'], true);
        }
        if (is_string($data['custom_data'] ?? null)) {
            $data['custom_data'] = json_decode($data['custom_data'], true);
        }

        if (!Session::validateCsrfToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        try {
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $existingEquipment = $id ? $this->equipmentRepository->getEquipmentById($id) : null;
            $currentImages = $existingEquipment ? (json_decode($existingEquipment['images'] ?? '[]', true) ?: []) : [];

            // Handle Image Deletions
            if (!empty($data['images_to_delete'])) {
                $toDelete = is_string($data['images_to_delete']) ? json_decode($data['images_to_delete'], true) : $data['images_to_delete'];
                foreach ($toDelete as $img) {
                    if (($key = array_search($img, $currentImages)) !== false) {
                        unset($currentImages[$key]);
                        if (file_exists($img)) @unlink($img);
                    }
                }
                $currentImages = array_values($currentImages);
            }

            // 1. Validation: Unique Serial Number (Except N/A / NULL)
            if (!empty($data['serial_number']) && strtoupper($data['serial_number']) !== 'N/A') {
                $stmt = \Core\Database::getInstance()->prepare("SELECT id FROM equipments WHERE serial_number = ? AND id != ?");
                $stmt->execute([$data['serial_number'], $id ?? 0]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => "Serial Number \"{$data['serial_number']}\" is already registered."];
                }
            }

            // 2. Validation: Unique MAC Address (Except N/A / NULL)
            if (!empty($data['mac_address']) && strtoupper($data['mac_address']) !== 'N/A') {
                $stmt = \Core\Database::getInstance()->prepare("SELECT id FROM equipments WHERE mac_address = ? AND id != ?");
                $stmt->execute([$data['mac_address'], $id ?? 0]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => "MAC Address \"{$data['mac_address']}\" is already registered."];
                }
            }

            // Handle Warranty File Upload
            if (isset($_FILES['warranty_document']) && $_FILES['warranty_document']['error'] === UPLOAD_ERR_OK) {
                $upload = $_FILES['warranty_document'];
                $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    return ['success' => false, 'message' => 'Invalid file type. Only PDF and images allowed.'];
                }
                
                if ($upload['size'] > 10 * 1024 * 1024) {
                    return ['success' => false, 'message' => 'File too large (Max 10MB).'];
                }

                $targetDir = 'storage/uploads/warranty/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                
                $fileName = 'warranty_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($upload['tmp_name'], $targetDir . $fileName)) {
                    $data['warranty_file'] = $targetDir . $fileName;
                }
            }

            // Handle Equipment Photos (Gallery)
            if (!empty($_FILES['equipment_photos'])) {
                $photoDir = 'storage/uploads/equipment/';
                if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);

                $files = $_FILES['equipment_photos'];
                $newlyUploadedCount = 0;
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        // Total count check: existing + newly added in this loop
                        if (count($currentImages) >= 3) {
                            break; // Stop if we reached 3
                        }

                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png'];
                        
                        if (in_array($ext, $allowed)) {
                            if ($files['size'][$i] <= 5 * 1024 * 1024) { // 5MB limit
                                $fileName = 'equip_' . time() . '_' . uniqid() . '.' . $ext;
                                if (move_uploaded_file($files['tmp_name'][$i], $photoDir . $fileName)) {
                                    $currentImages[] = $photoDir . $fileName;
                                }
                            }
                        }
                    }
                }
            }
            $data['images'] = array_slice($currentImages, 0, 3); // Final safety cap

            $networkData = null;
            if (!empty($data['include_network']) && $data['include_network'] !== 'false' && !empty($data['network']['ip_address'])) {
                $networkData = $data['network'];
            }

            $this->equipmentRepository->saveEquipment($data, $networkData);
            return ['success' => true, 'message' => 'Equipment saved successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * View equipment details.
     */
    public function view(int $id): array
    {
        $equipment = $this->equipmentRepository->getEquipmentById($id);
        if (!$equipment) throw new Exception("Equipment not found.");

        return [
            'title' => 'Equipment Details',
            'view' => 'views/equipment/view.php',
            'data' => [
                'equipment' => $equipment
            ]
        ];
    }

    /**
     * Delete equipment (AJAX).
     */
    public function delete(int $id): array
    {
        try {
            $this->equipmentRepository->deleteEquipment($id);
            return ['success' => true, 'message' => 'Equipment deleted.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bulk delete equipment (AJAX).
     */
    public function bulkDelete(array $data): array
    {
        if (!Session::validateCsrfToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        try {
            $ids = $data['ids'] ?? [];
            if (empty($ids)) throw new Exception("No items selected.");

            $count = $this->equipmentRepository->bulkDelete($ids);
            return ['success' => true, 'message' => "$count items deleted."];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Manage Equipment Types (Admin only).
     */
    public function types(): array
    {
        if (Session::get('role') !== 'admin') {
            throw new Exception("Access denied.");
        }

        return [
            'title' => 'Manage Equipment Types',
            'view' => 'views/equipment/types.php',
            'data' => [
                'types' => $this->equipmentRepository->getTypes(),
                'blocks' => $this->blockRepository->getAll()
            ]
        ];
    }

    /**
     * Save equipment type (AJAX).
     */
    public function saveType(array $data): array
    {
        $adminRepo = new \Modules\Admin\AdminRepository();

        if (Session::get('role') !== 'admin') {
            $adminRepo->logEvent('warning', 'security', 'Unauthorized attempt to save equipment type by user: ' . Session::get('username'));
            return ['success' => false, 'message' => 'Access denied. Only administrators can manage equipment types.'];
        }

        if (!Session::validateCsrfToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token.'];
        }

        try {
            $this->equipmentRepository->saveType($data);
            return ['success' => true, 'message' => 'Equipment type saved.'];
        } catch (\Throwable $e) {
            $adminRepo->logEvent('error', 'equipment', 'Failed to save equipment type: ' . $e->getMessage(), $data);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete equipment type (AJAX).
     */
    public function deleteType(int $id): array
    {
        if (Session::get('role') !== 'admin') {
            return ['success' => false, 'message' => 'Access denied.'];
        }

        try {
            if ($this->equipmentRepository->isTypeInUse($id)) {
                return [
                    'success' => false, 
                    'message' => 'Cannot delete type: There are equipment assets assigned to this type. Please delete or reassign them first.'
                ];
            }

            $this->equipmentRepository->deleteType($id);
            return ['success' => true, 'message' => 'Equipment type deleted.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

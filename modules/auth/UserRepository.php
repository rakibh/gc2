<?php

declare(strict_types=1);

namespace Modules\Auth;

use Core\Repository;
use Core\Session;
use PDO;
use Exception;

class UserRepository extends Repository
{
    /**
     * Get users with pagination and sorting.
     */
    public function getUsers(int $page = 1, int $limit = 20, string $sortBy = 'created_at', string $sortDir = 'DESC'): array
    {
        $offset = ($page - 1) * $limit;
        $allowedSort = ['role', 'status', 'created_at', 'username', 'employee_id'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $stmt = $this->db->prepare("
            SELECT id, username, employee_id, first_name, last_name, email, phone, designation, profile_photo, role, status, created_at 
            FROM users 
            ORDER BY $sortBy $sortDir 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = $stmt->fetchAll();

        // Get total count for pagination
        $total = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        return [
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get user by ID with all fields.
     */
    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Get all active admin IDs.
     */
    public function getAdminIds(): array
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Create a new user with audit logging.
     */
    public function createUser(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, employee_id, password, first_name, last_name, email, phone, designation, profile_photo, role, status)
                VALUES (:username, :employee_id, :password, :first_name, :last_name, :email, :phone, :designation, :profile_photo, :role, :status)
            ");
            
            $stmt->execute([
                'username'    => $data['username'],
                'employee_id' => $data['employee_id'],
                'password'    => password_hash($data['password'], PASSWORD_DEFAULT),
                'first_name'  => $data['first_name'] ?? null,
                'last_name'   => $data['last_name'] ?? null,
                'email'       => $data['email'] ?? null,
                'phone'       => $data['phone'] ?? null,
                'designation' => $data['designation'] ?? null,
                'profile_photo' => $data['profile_photo'] ?? null,
                'role'        => $data['role'] ?? 'user',
                'status'      => $data['status'] ?? 'active'
            ]);

            $userId = (int)$this->db->lastInsertId();
            
            $this->logAudit('create', 'users', $userId, null, $data);
            
            // Notify Admins
            $adminIds = $this->getAdminIds();
            $notificationRepo = new \Modules\Notifications\NotificationRepository();
            $notificationRepo->createForUsers(
                $adminIds, 
                'user', 
                "New user added: " . $data['username'], 
                'medium', 
                ['user_id' => $userId]
            );

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update user and log changes.
     */
    public function updateUser(int $id, array $data): bool
    {
        $oldUser = $this->getUserById($id);
        if (!$oldUser) return false;

        $this->db->beginTransaction();
        try {
            $fields = [];
            $params = ['id' => $id];

            $updateable = [
                'username', 'employee_id', 'first_name', 'last_name', 
                'email', 'phone', 'designation', 'profile_photo', 
                'role', 'status', 'force_password_change'
            ];

            foreach ($updateable as $key) {
                // Check if key exists in data
                if (array_key_exists($key, $data)) {
                    $fields[] = "$key = :$key";
                    $params[$key] = $data[$key];
                }
            }

            if (!empty($data['password'])) {
                $fields[] = "password = :password";
                $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($fields)) {
                return true;
            }

            $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id");
            $stmt->execute($params);

            // Notify Admins if role changed
            if (isset($data['role']) && $oldUser['role'] !== $data['role']) {
                $adminIds = $this->getAdminIds();
                $notificationRepo = new \Modules\Notifications\NotificationRepository();
                $notificationRepo->createForUsers(
                    $adminIds, 
                    'user', 
                    "User role changed: " . $oldUser['username'] . " is now " . $data['role'], 
                    'high', 
                    ['user_id' => $id]
                );
            }

            // Calculate changes for audit log
            $newValues = array_intersect_key(array_merge($oldUser, $data), array_flip($updateable));
            $this->logAudit('update', 'users', $id, $oldUser, $newValues);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get revision history for a user.
     */
    public function getRevisionHistory(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.username as responsible_user
            FROM audit_logs a
            JOIN users u ON a.user_id = u.id
            WHERE a.target_table = 'users' AND a.target_id = ?
            ORDER BY a.created_at DESC
            LIMIT 15
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        if (!$user) return false;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                // Notify Admins
                $adminIds = $this->getAdminIds();
                $notificationRepo = new \Modules\Notifications\NotificationRepository();
                $notificationRepo->createForUsers(
                    $adminIds, 
                    'user', 
                    "User deleted: " . $user['username'], 
                    'high'
                );
                
                $this->logAudit('delete', 'users', $id, $user, null);
            }

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Log actions to audit_logs table.
     */
    public function logAudit(string $action, string $table, ?int $targetId, ?array $old, ?array $new): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, target_table, target_id, old_values, new_values)
                VALUES (:user_id, :action, :table, :target_id, :old, :new)
            ");
            
            $stmt->execute([
                'user_id'   => Session::get('user_id'),
                'action'    => $action,
                'table'     => $table,
                'target_id' => $targetId,
                'old'       => $old ? json_encode($old) : null,
                'new'       => $new ? json_encode($new) : null
            ]);
        } catch (Exception $e) {
            // Silently log error to PHP logs but don't stop the main transaction
            error_log("Audit Logging Failed: " . $e->getMessage());
        }
    }
}

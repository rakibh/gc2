<?php

declare(strict_types=1);

namespace Modules\Auth;

use Core\Repository;
use PDO;

class PreferencesRepository extends Repository
{
    /**
     * Get preferences for a specific user.
     */
    public function getByUserId(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prefs) {
                return $this->getDefaults($userId);
            }

            return $prefs;
        } catch (\PDOException $e) {
            // Table might not exist yet, return defaults
            return $this->getDefaults($userId);
        }
    }

    /**
     * Save or update user preferences.
     */
    public function save(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_preferences (user_id, theme, timezone, time_format, desktop_notifications, toast_position)
            VALUES (:user_id, :theme, :timezone, :time_format, :desktop_notifications, :toast_position) AS new_vals
            ON DUPLICATE KEY UPDATE 
                theme = new_vals.theme,
                timezone = new_vals.timezone,
                time_format = new_vals.time_format,
                desktop_notifications = new_vals.desktop_notifications,
                toast_position = new_vals.toast_position
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'theme' => $data['theme'] ?? 'light',
            'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
            'time_format' => $data['time_format'] ?? '12',
            'desktop_notifications' => isset($data['desktop_notifications']) ? (int)$data['desktop_notifications'] : 0,
            'toast_position' => $data['toast_position'] ?? 'top-right'
        ]);
    }

    /**
     * Default preferences.
     */
    private function getDefaults(int $userId): array
    {
        return [
            'user_id' => $userId,
            'theme' => 'light',
            'timezone' => 'Asia/Dhaka',
            'time_format' => '12',
            'desktop_notifications' => 0,
            'toast_position' => 'top-right'
        ];
    }
}

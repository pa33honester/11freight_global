<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    /**
     * Create an audit log entry.
     *
     * @param  User|int|null  $user
     * @param  string  $action
     * @param  string|null  $module
     * @param  mixed  $oldData
     * @param  mixed  $newData
     * @param  string|null  $ip
     * @return AuditLog
     */
    public function log($user, string $action, ?string $module = null, $oldData = null, $newData = null, ?string $ip = null): AuditLog
    {
        $userId = null;
        if ($user instanceof User) {
            $userId = $user->id;
        } elseif (is_numeric($user)) {
            $userId = (int) $user;
        }

        $entry = AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => $ip,
        ]);

        return $entry;
    }
}

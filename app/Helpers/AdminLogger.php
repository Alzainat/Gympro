<?php

namespace App\Helpers;

use App\Models\AdminLog;

class AdminLogger
{
    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $adminProfile = auth()->user()?->profile;

        if (! $adminProfile || $adminProfile->role !== 'admin') {
            return;
        }

        AdminLog::create([
            'admin_profile_id' => $adminProfile->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
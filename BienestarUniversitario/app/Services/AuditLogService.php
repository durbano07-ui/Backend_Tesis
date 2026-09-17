<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record an audit log entry.
     *
     * @param  \App\Models\User|null  $user
     * @param  string  $action
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array|null  $oldValues
     * @param  array|null  $newValues
     * @param  string|null  $ipAddress
     * @param  string|null  $userAgent
     * @return \App\Models\AuditLog
     */
    public static function record(
        $user,
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }
}

<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditLogService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        AuditLogService::record(
            auth()->user(),
            'created',
            $user,
            null,
            $user->getAttributes()
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $oldValues = $user->getOriginal();
        $newValues = $user->getChanges();

        // Remove non-auditable fields
        unset($oldValues['updated_at']);
        unset($newValues['updated_at']);

        AuditLogService::record(
            auth()->user(),
            'updated',
            $user,
            $oldValues,
            $newValues
        );
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        AuditLogService::record(
            auth()->user(),
            'deleted',
            $user,
            $user->getOriginal(),
            null
        );
    }
}

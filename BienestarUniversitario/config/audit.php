<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Log Retention Days
    |--------------------------------------------------------------------------
    |
    | Number of days to retain audit log entries. Set to null to retain
    | indefinitely. Consider setting a value for compliance and storage
    | management.
    |
    */

    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Array of fully qualified model class names that should NOT be audited.
    | Models in this list will not trigger audit log entries even if they
    | have the HasAuditLog trait.
    |
    */

    'excluded_models' => [
        // Example:
        // App\Models\Session::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Actions
    |--------------------------------------------------------------------------
    |
    | Actions that should not be logged. Common actions to exclude might
    | include read operations or bulk operations.
    |
    */

    'excluded_actions' => [
        'index',
        'show',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit User Resolver
    |--------------------------------------------------------------------------
    |
    | Callable that returns the current authenticated user making the action.
    | This allows flexibility in how user context is determined.
    |
    */

    'user_resolver' => function () {
        return auth()->user();
    },

];

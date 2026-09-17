<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_creates_audit_log_entry(): void
    {
        $user = User::factory()->create();

        $model = new AuditLog();
        $oldValues = ['name' => 'Old Name'];
        $newValues = ['name' => 'New Name'];

        $result = AuditLogService::record($user, 'updated', $model, $oldValues, $newValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'updated',
            'model_type' => AuditLog::class,
        ]);

        // Retrieve by ID to avoid conflicts with UserObserver-created logs
        $auditLog = AuditLog::find($result->id);
        $this->assertEquals(['name' => 'Old Name'], $auditLog->old_values);
        $this->assertEquals(['name' => 'New Name'], $auditLog->new_values);
    }

    public function test_record_handles_null_user(): void
    {
        $model = new AuditLog();
        $oldValues = null;
        $newValues = ['name' => 'Test'];

        AuditLogService::record(null, 'created', $model, $oldValues, $newValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'created',
        ]);
    }

    public function test_record_includes_ip_address_and_user_agent(): void
    {
        $user = User::factory()->create();
        $model = new AuditLog();

        $result = AuditLogService::record($user, 'deleted', $model, ['id' => 1], null, '192.168.1.1', 'Mozilla/5.0');

        // Retrieve by ID to avoid conflicts with UserObserver-created logs
        $auditLog = AuditLog::find($result->id);
        $this->assertEquals('192.168.1.1', $auditLog->ip_address);
        $this->assertEquals('Mozilla/5.0', $auditLog->user_agent);
    }
}

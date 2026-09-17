<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_roles_relationship(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'paciente', 'guard_name' => 'sanctum']);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('paciente'));
        $this->assertCount(1, $user->roles);
        $this->assertEquals('paciente', $user->roles->first()->name);
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();

        $role1 = Role::create(['name' => 'paciente', 'guard_name' => 'sanctum']);
        $role2 = Role::create(['name' => 'enfermero', 'guard_name' => 'sanctum']);

        $user->assignRole([$role1, $role2]);

        $this->assertTrue($user->hasRole('paciente'));
        $this->assertTrue($user->hasRole('enfermero'));
        $this->assertCount(2, $user->roles);
    }

    public function test_user_has_many_audit_logs_relationship(): void
    {
        $user = User::factory()->create();

        // User is created - check relationship exists
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->auditLogs());
    }

    public function test_user_casts_must_change_password_as_boolean(): void
    {
        $user = User::factory()->create(['must_change_password' => 1]);

        $this->assertIsBool($user->must_change_password);
        $this->assertTrue($user->must_change_password);
    }

    public function test_user_casts_activo_as_boolean(): void
    {
        $user = User::factory()->create(['activo' => 1]);

        $this->assertIsBool($user->activo);
        $this->assertTrue($user->activo);
    }

    public function test_inactive_user_has_activo_false(): void
    {
        $user = User::factory()->create(['activo' => false]);

        $this->assertFalse($user->activo);
    }
}

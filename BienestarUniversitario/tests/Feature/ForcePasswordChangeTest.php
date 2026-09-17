<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_temp_password_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('temp-password'),
            'activo' => true,
            'must_change_password' => true,
            'clave_temporal' => 'temp-password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@ueb.edu.ec',
            'password' => 'temp-password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'must_change_password' => true,
            ]);
    }

    public function test_temp_password_blocks_api_access(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('temp-password'),
            'activo' => true,
            'must_change_password' => true,
            'clave_temporal' => 'temp-password',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Try to access protected endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Password change required']);
    }

    public function test_password_change_clears_must_change_password_flag(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('temp-password'),
            'activo' => true,
            'must_change_password' => true,
            'clave_temporal' => 'temp-password',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Change password
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'temp-password',
                'password' => 'NewSecurePass123!',
                'password_confirmation' => 'NewSecurePass123!',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password updated']);

        // Verify flags are cleared
        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertNull($user->clave_temporal);
    }

    public function test_after_password_change_api_access_is_granted(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('temp-password'),
            'activo' => true,
            'must_change_password' => true,
            'clave_temporal' => 'temp-password',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Change password first
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'temp-password',
                'password' => 'NewSecurePass123!',
                'password_confirmation' => 'NewSecurePass123!',
            ]);

        // Create new token after password change
        $newToken = $user->createToken('new-token')->plainTextToken;

        // Try to access protected endpoint with new token
        $response = $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_password_change_endpoint_is_accessible_with_temp_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('temp-password'),
            'activo' => true,
            'must_change_password' => true,
            'clave_temporal' => 'temp-password',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Password change endpoint should NOT be blocked
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'temp-password',
                'password' => 'NewSecurePass123!',
                'password_confirmation' => 'NewSecurePass123!',
            ]);

        $response->assertStatus(200);
    }

    public function test_regular_user_without_temp_password_can_access_api(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('regular-password'),
            'activo' => true,
            'must_change_password' => false,
            'clave_temporal' => null,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Should be able to access protected endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }
}

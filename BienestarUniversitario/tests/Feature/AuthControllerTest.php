<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('secret123'),
            'activo' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@ueb.edu.ec',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'roles'],
                'token',
                'must_change_password',
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('secret123'),
            'activo' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@ueb.edu.ec',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_non_existent_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ghost@ueb.edu.ec',
            'password' => 'anypassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_success_with_ueb_email(): void
    {
        // Create the paciente role for registration
        Role::create(['name' => 'paciente', 'guard_name' => 'sanctum']);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'maria@ueb.edu.ec',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'roles'],
                'token',
                'must_change_password',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'maria@ueb.edu.ec',
        ]);

        $user = User::where('email', 'maria@ueb.edu.ec')->first();
        $this->assertTrue($user->hasRole('paciente'));
    }

    public function test_register_fails_with_non_ueb_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'maria@gmail.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseMissing('users', [
            'email' => 'maria@gmail.com',
        ]);
    }

    public function test_register_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'maria@ueb.edu.ec',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'maria@ueb.edu.ec',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'maria@ueb.edu.ec',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'activo' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }

    public function test_login_fails_for_disabled_user(): void
    {
        $user = User::factory()->create([
            'email' => 'john@ueb.edu.ec',
            'password' => Hash::make('secret123'),
            'activo' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@ueb.edu.ec',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}

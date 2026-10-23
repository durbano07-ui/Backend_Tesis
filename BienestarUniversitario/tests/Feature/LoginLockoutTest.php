<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * El bloqueo por intentos fallidos es individual: 5 fallos de una cuenta la bloquean solo a ella,
 * aunque otros usuarios inicien sesión desde la misma IP (NAT de la universidad / proxy).
 */
class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make('ClaveCorrecta123'),
            'activo' => true,
        ]);
    }

    private function login(string $email, string $password, string $ip = '200.10.10.10')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/v1/auth/login', ['email' => $email, 'password' => $password]);
    }

    public function test_five_failures_lock_only_that_account(): void
    {
        $this->makeUser('ana@ueb.edu.ec');
        $this->makeUser('luis@ueb.edu.ec');

        for ($i = 0; $i < 5; $i++) {
            $this->login('ana@ueb.edu.ec', 'incorrecta')->assertStatus(422);
        }

        // Ana queda bloqueada aunque ahora escriba bien su contraseña
        $this->login('ana@ueb.edu.ec', 'ClaveCorrecta123')
            ->assertStatus(422)
            ->assertJsonFragment(['Account temporarily locked due to too many failed attempts. Try again later.']);

        // Luis, desde la misma IP, entra sin problema
        $this->login('luis@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(200);
    }

    public function test_failures_of_different_users_from_same_ip_do_not_lock_anyone(): void
    {
        foreach (['u1', 'u2', 'u3', 'u4', 'u5', 'u6'] as $u) {
            $this->makeUser("{$u}@ueb.edu.ec");
            $this->login("{$u}@ueb.edu.ec", 'incorrecta')->assertStatus(422);
        }

        $this->login('u6@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(200);
    }

    public function test_successful_login_resets_the_failure_counter(): void
    {
        $this->makeUser('maria@ueb.edu.ec');

        for ($i = 0; $i < 4; $i++) {
            $this->login('maria@ueb.edu.ec', 'incorrecta');
        }
        $this->login('maria@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(200);

        // Tras entrar, otros 4 fallos no bloquean (el contador volvió a cero)
        for ($i = 0; $i < 4; $i++) {
            $this->login('maria@ueb.edu.ec', 'incorrecta');
        }
        $this->login('maria@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(200);
    }

    public function test_lock_expires_after_fifteen_minutes(): void
    {
        $this->makeUser('pedro@ueb.edu.ec');

        for ($i = 0; $i < 5; $i++) {
            $this->login('pedro@ueb.edu.ec', 'incorrecta');
        }
        $this->login('pedro@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(422);

        $this->travel(16)->minutes();

        $this->login('pedro@ueb.edu.ec', 'ClaveCorrecta123')->assertStatus(200);
    }

    public function test_real_client_ip_is_read_behind_the_proxy(): void
    {
        $this->makeUser('sofia@ueb.edu.ec');

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.5'])
            ->withHeader('X-Forwarded-For', '181.39.20.7')
            ->postJson('/api/v1/auth/login', ['email' => 'sofia@ueb.edu.ec', 'password' => 'ClaveCorrecta123'])
            ->assertStatus(200);

        $this->assertDatabaseHas('login_attempts', ['email' => 'sofia@ueb.edu.ec', 'ip_address' => '181.39.20.7']);
    }
}

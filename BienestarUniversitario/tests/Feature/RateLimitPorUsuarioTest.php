<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Con sesión iniciada el límite de peticiones es por usuario: los usuarios del campus comparten
 * la misma IP pública y la actualización automática de las pantallas no debe bloquearlos a todos.
 */
class RateLimitPorUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        putenv('RATE_LIMIT_REQUESTS_PER_MINUTE=5');
    }

    protected function tearDown(): void
    {
        putenv('RATE_LIMIT_REQUESTS_PER_MINUTE');
        parent::tearDown();
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function tokenFor(): string
    {
        $user = User::factory()->create([
            'password' => Hash::make('Una-clave-larga-segura-2026'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole('paciente');

        return 'Bearer ' . $user->createToken('t')->plainTextToken;
    }

    private function consultar(string $token)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => '181.39.20.7'])
            ->withHeader('Authorization', $token)
            ->getJson('/api/v1/notificaciones');
    }

    public function test_el_limite_de_un_usuario_no_bloquea_a_otro_de_la_misma_ip(): void
    {
        $ana = $this->tokenFor();
        $luis = $this->tokenFor();

        for ($i = 0; $i < 5; $i++) {
            $this->consultar($ana)->assertOk();
        }
        $this->consultar($ana)->assertStatus(429);

        // Luis sale por la misma IP del campus y no se ve afectado
        $this->consultar($luis)->assertOk();
    }
}

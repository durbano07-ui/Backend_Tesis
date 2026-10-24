<?php

namespace Tests\Feature;

use App\Models\SignosVitales;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Vista integrada del paciente: al consultar el historial de un paciente, cada profesional ve los
 * registros de todas las áreas, no solo los que él mismo creó.
 */
class HistorialIntegradoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('Una-clave-larga-segura-2026'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken];
    }

    public function test_el_medico_ocupacional_ve_los_signos_vitales_registrados_por_enfermeria(): void
    {
        $enfermero = $this->userWithRole('enfermero');
        $ocupacional = $this->userWithRole('medico_ocupacional');
        $paciente = $this->userWithRole('paciente');

        SignosVitales::create([
            'id_usuario_doctor' => $enfermero->id,
            'id_usuario_paciente' => $paciente->id,
            'fecha' => now()->toDateString(),
            'presion_arterial_sistolica' => 118,
            'presion_arterial_diastolica' => 76,
        ]);

        $this->withHeaders($this->bearer($ocupacional))
            ->getJson("/api/v1/enfermeria/signos-vitales?id_usuario_paciente={$paciente->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_sin_paciente_cada_profesional_solo_ve_sus_registros(): void
    {
        $enfermero = $this->userWithRole('enfermero');
        $otroEnfermero = $this->userWithRole('enfermero');
        $paciente = $this->userWithRole('paciente');

        SignosVitales::create([
            'id_usuario_doctor' => $otroEnfermero->id,
            'id_usuario_paciente' => $paciente->id,
            'fecha' => now()->toDateString(),
            'presion_arterial_sistolica' => 120,
            'presion_arterial_diastolica' => 80,
        ]);

        $this->withHeaders($this->bearer($enfermero))
            ->getJson('/api/v1/enfermeria/signos-vitales')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}

<?php

namespace Tests\Feature;

use App\Models\CitaMedica;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Reglas al agendar: una sola cita pendiente por área y nunca dos citas a la misma hora.
 */
class CitaReglasAgendamientoTest extends TestCase
{
    use RefreshDatabase;

    private User $paciente;
    private User $odontologo;
    private User $otroOdontologo;
    private User $medico;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Notification::fake();
        // Miércoles 21/10/2026, 07:00
        Carbon::setTestNow(Carbon::parse('2026-10-21 07:00:00'));

        $this->paciente = $this->userWithRole('paciente');
        $this->odontologo = $this->userWithRole('odontologo');
        $this->otroOdontologo = $this->userWithRole('odontologo');
        $this->medico = $this->userWithRole('medico_general');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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

    private function agendar(User $doctor, string $rol, string $fecha, string $hora)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->paciente->createToken('t')->plainTextToken)
            ->postJson('/api/v1/citas-medicas', [
                'id_usuario_doctor' => $doctor->id,
                'rol_doctor' => $rol,
                'fecha' => $fecha,
                'hora_inicio' => $hora,
                'motivo' => 'Control',
            ]);
    }

    public function test_no_se_agendan_dos_citas_pendientes_en_la_misma_area(): void
    {
        $this->agendar($this->odontologo, 'odontologo', '2026-10-22', '09:00')->assertCreated();

        // Otro horario y otro odontólogo: igual se rechaza mientras la primera siga pendiente
        $this->agendar($this->otroOdontologo, 'odontologo', '2026-10-23', '10:00')
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Ya tienes una cita pendiente en Odontología para el 22/10/2026 a las 09:00. Podrás agendar otra cuando esa cita se complete o si la cancelas.']);
    }

    public function test_tras_completar_la_cita_se_puede_agendar_otra_en_la_misma_area(): void
    {
        $this->agendar($this->odontologo, 'odontologo', '2026-10-22', '09:00')->assertCreated();
        CitaMedica::query()->update(['estado' => 'completada']);

        $this->agendar($this->odontologo, 'odontologo', '2026-10-26', '09:00')->assertCreated();
    }

    public function test_no_se_agendan_dos_citas_a_la_misma_hora_en_areas_distintas(): void
    {
        $this->agendar($this->odontologo, 'odontologo', '2026-10-22', '09:00')->assertCreated();

        $this->agendar($this->medico, 'medico_general', '2026-10-22', '09:00')->assertStatus(422);
        $this->agendar($this->medico, 'medico_general', '2026-10-22', '10:00')->assertCreated();
    }

    public function test_una_cita_con_margen_vencido_no_impide_agendar_otra(): void
    {
        CitaMedica::create([
            'id_usuario_paciente' => $this->paciente->id,
            'id_usuario_doctor' => $this->odontologo->id,
            'rol_doctor' => 'odontologo',
            'fecha' => '2026-10-20',
            'hora_inicio' => '09:00',
            'hora_fin' => '09:30',
            'estado' => 'programada',
        ]);

        $this->agendar($this->odontologo, 'odontologo', '2026-10-22', '09:00')->assertCreated();
    }
}

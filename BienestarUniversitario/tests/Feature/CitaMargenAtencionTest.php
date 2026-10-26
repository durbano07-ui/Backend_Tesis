<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\CitasMedicasController;
use App\Models\CitaMedica;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Margen de atención de las citas: se pueden completar el día de la cita hasta
 * CitaMedica::HORAS_MARGEN_ATENCION horas después de su hora de inicio; luego pasan a "no_asistio".
 */
class CitaMargenAtencionTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;
    private User $paciente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Notification::fake();
        // Miércoles 21/10/2026, 12:00 (hora de Ecuador)
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00'));

        $this->doctor = $this->userWithRole('medico_general');
        $this->paciente = $this->userWithRole('paciente');
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

    private function cita(string $fecha, string $hora, string $estado = 'programada'): CitaMedica
    {
        return CitaMedica::create([
            'id_usuario_paciente' => $this->paciente->id,
            'id_usuario_doctor' => $this->doctor->id,
            'rol_doctor' => 'medico_general',
            'fecha' => $fecha,
            'hora_inicio' => $hora,
            'hora_fin' => $hora,
            'estado' => $estado,
            'motivo' => 'Control',
        ]);
    }

    private function completar(CitaMedica $cita, array $data = [])
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->doctor->createToken('t')->plainTextToken)
            ->patchJson("/api/v1/citas-medicas/{$cita->id}/completar", $data);
    }

    public function test_se_completa_dentro_del_margen_aunque_el_paciente_llegue_tarde(): void
    {
        $cita = $this->cita('2026-10-21', '08:00'); // empezó hace 4 h

        $this->completar($cita, ['notas_doctor' => 'Llegó tarde, se atendió'])->assertOk();

        $cita->refresh();
        $this->assertSame('completada', $cita->estado);
        $this->assertSame('Llegó tarde, se atendió', $cita->notas_doctor);
    }

    public function test_pasado_el_margen_queda_como_no_asistio_y_no_se_puede_completar(): void
    {
        $cita = $this->cita('2026-10-21', '06:30'); // margen terminó a las 11:30

        $this->completar($cita)->assertStatus(422);

        $this->assertSame('no_asistio', $cita->fresh()->estado);
    }

    public function test_no_se_completa_una_cita_de_otro_dia_futuro(): void
    {
        $cita = $this->cita('2026-10-22', '09:00');

        $this->completar($cita)->assertStatus(422);

        $this->assertSame('programada', $cita->fresh()->estado);
    }

    public function test_atender_al_paciente_no_completa_su_cita_futura(): void
    {
        $futura = $this->cita('2026-10-23', '10:00');

        $creada = CitasMedicasController::syncAutoCita($this->doctor->id, $this->paciente->id, 'medico_general');

        $this->assertNotSame($futura->id, $creada->id);
        $this->assertSame('completada', $creada->estado);
        $this->assertSame('programada', $futura->fresh()->estado);
    }

    public function test_atencion_sin_cita_previa_queda_como_atencion_directa_sin_notificar(): void
    {
        Notification::fake();

        $cita = CitasMedicasController::syncAutoCita($this->doctor->id, $this->paciente->id, 'medico_general');

        // Cuenta para los reportes (cita completada) pero se marca como atención directa
        $this->assertSame('completada', $cita->estado);
        $this->assertSame(CitaMedica::ORIGEN_ATENCION_DIRECTA, $cita->fresh()->origen);
        Notification::assertNothingSent();

        // El paciente recibe el origen para que el frontend no genere el aviso
        $this->withHeader('Authorization', 'Bearer ' . $this->paciente->createToken('t')->plainTextToken)
            ->getJson('/api/v1/citas-medicas/mis-citas')
            ->assertOk()
            ->assertJsonFragment(['id' => $cita->id, 'origen' => 'atencion_directa']);
    }

    public function test_la_migracion_marca_las_atenciones_directas_existentes(): void
    {
        $migracion = require database_path('migrations/2026_10_25_000002_add_origen_to_citas_medicas_table.php');
        $migracion->down();

        // Datos como estaban antes de la migración (motivo y notas cifrados)
        $base = ['id_usuario_paciente' => $this->paciente->id, 'id_usuario_doctor' => $this->doctor->id, 'rol_doctor' => 'medico_general',
            'fecha' => '2026-10-20', 'hora_inicio' => '09:00', 'hora_fin' => '09:30', 'estado' => 'completada',
            'created_at' => now(), 'updated_at' => now()];
        $directa = \Illuminate\Support\Facades\DB::table('citas_medicas')->insertGetId($base + [
            'motivo' => \Illuminate\Support\Facades\Crypt::encryptString('Dolor de cabeza'),
            'notas_doctor' => \Illuminate\Support\Facades\Crypt::encryptString('Atención registrada directamente por el profesional médico'),
        ]);
        $agendada = \Illuminate\Support\Facades\DB::table('citas_medicas')->insertGetId($base + [
            'hora_inicio' => '11:00', 'hora_fin' => '11:30',
            'motivo' => \Illuminate\Support\Facades\Crypt::encryptString('Control'),
            'notas_doctor' => \Illuminate\Support\Facades\Crypt::encryptString('Paciente estable'),
        ]);

        $migracion->up();

        $this->assertSame('atencion_directa', CitaMedica::find($directa)->origen);
        $this->assertSame('agendada', CitaMedica::find($agendada)->origen);
    }

    public function test_cita_agendada_atendida_conserva_su_origen(): void
    {
        $hoy = $this->cita('2026-10-21', '10:00', 'confirmada');

        CitasMedicasController::syncAutoCita($this->doctor->id, $this->paciente->id, 'medico_general');

        $this->assertSame(CitaMedica::ORIGEN_AGENDADA, $hoy->fresh()->origen);
    }

    public function test_atender_al_paciente_completa_su_cita_de_hoy(): void
    {
        $hoy = $this->cita('2026-10-21', '10:00', 'confirmada');

        $cita = CitasMedicasController::syncAutoCita($this->doctor->id, $this->paciente->id, 'medico_general');

        $this->assertSame($hoy->id, $cita->id);
        $this->assertSame('completada', $hoy->fresh()->estado);
    }

    public function test_las_citas_pendientes_vencidas_aparecen_como_no_asistio(): void
    {
        $ayer = $this->cita('2026-10-20', '15:00', 'confirmada');
        $manana = $this->cita('2026-10-22', '09:00');

        $this->withHeader('Authorization', 'Bearer ' . $this->paciente->createToken('t')->plainTextToken)
            ->getJson('/api/v1/citas-medicas/mis-citas')
            ->assertOk();

        $this->assertSame('no_asistio', $ayer->fresh()->estado);
        $this->assertSame('programada', $manana->fresh()->estado);
    }

    public function test_el_paciente_ve_la_hora_limite_y_puede_avisar_que_no_asistira(): void
    {
        $cita = $this->cita('2026-10-21', '10:00'); // empezó hace 2 h; margen hasta las 15:00
        $token = 'Bearer ' . $this->paciente->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', $token)
            ->getJson('/api/v1/citas-medicas/mis-citas')
            ->assertOk()
            ->assertJsonPath('data.0.fin_margen_atencion', '2026-10-21T15:00:00');

        $this->withHeader('Authorization', $token)
            ->patchJson("/api/v1/citas-medicas/{$cita->id}/cancelar")
            ->assertOk();

        $this->assertSame('cancelada', $cita->fresh()->estado);
    }
}

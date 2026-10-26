<?php

namespace Tests\Feature;

use App\Models\DatosIdentificacion;
use App\Models\FichaMedicoocupacional;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Las fichas ocupacionales se guardan en el servidor y en Reportes se leen de ahí, con la
 * identificación real del trabajador (antes se mostraban datos de ejemplo).
 */
class FichasOcupacionalesTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->medico = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $this->medico->assignRole('medico_ocupacional');
        $this->headers = ['Authorization' => 'Bearer ' . $this->medico->createToken('t')->plainTextToken];
    }

    private function paciente(): User
    {
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole('paciente');
        DatosIdentificacion::create([
            'id_usuario' => $user->id, 'numero_cedula' => '0201864329', 'primer_nombre' => 'Luis',
            'segundo_nombre' => 'Alfredo', 'apellido_paterno' => 'Bayas', 'apellido_materno' => 'Torres', 'fecha_nacimiento' => '1980-05-10',
        ]);

        return $user;
    }

    public function test_la_ficha_se_guarda_cifrada_y_se_recupera_con_los_datos_del_paciente(): void
    {
        $paciente = $this->paciente();
        $contenido = ['motivoConsulta' => 'EVALUACIÓN PREOCUPACIONAL', 'diagnosticoCie' => 'Z10.0 - Examen ocupacional', 'aptitudLaboral' => 'apto'];

        $this->withHeaders($this->headers)->postJson('/api/v1/medicina-ocupacional/fichas', [
            'id_usuario_paciente' => $paciente->id, 'tipo' => 'ingreso', 'fecha' => '2026-10-25', 'contenido' => $contenido,
        ])->assertCreated();

        // En la base no queda el texto clínico legible
        $crudo = DB::table('fichas_medicoocupacional')->value('contenido');
        $this->assertStringNotContainsString('PREOCUPACIONAL', $crudo);

        $this->withHeaders($this->headers)->getJson('/api/v1/medicina-ocupacional/fichas?tipo=ingreso')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.contenido.motivoConsulta', 'EVALUACIÓN PREOCUPACIONAL')
            ->assertJsonPath('data.0.paciente.cedula', '0201864329')
            ->assertJsonPath('data.0.paciente.nombre_completo', 'Luis Alfredo Bayas Torres')
            ->assertJsonPath('data.0.fecha', '2026-10-25');
    }

    public function test_filtra_por_tipo_de_ficha(): void
    {
        $paciente = $this->paciente();
        foreach (['ingreso', 'retiro', 'reintegro'] as $tipo) {
            FichaMedicoocupacional::create(['id_usuario_doctor' => $this->medico->id, 'id_usuario_paciente' => $paciente->id, 'tipo' => $tipo, 'fecha' => '2026-10-25', 'contenido' => ['x' => 1]]);
        }

        $this->withHeaders($this->headers)->getJson('/api/v1/medicina-ocupacional/fichas?tipo=retiro')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipo', 'retiro');
    }

    public function test_valida_tipo_y_paciente(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/medicina-ocupacional/fichas', [
            'id_usuario_paciente' => 999, 'tipo' => 'inventado', 'fecha' => '2026-10-25', 'contenido' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['id_usuario_paciente', 'tipo', 'contenido']);
    }

    public function test_otros_profesionales_no_acceden_a_las_fichas(): void
    {
        $psicologo = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $psicologo->assignRole('psicologo');

        $this->withHeaders(['Authorization' => 'Bearer ' . $psicologo->createToken('t')->plainTextToken])
            ->getJson('/api/v1/medicina-ocupacional/fichas')
            ->assertForbidden();
    }
}

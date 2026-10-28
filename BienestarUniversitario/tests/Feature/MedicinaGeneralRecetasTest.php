<?php

namespace Tests\Feature;

use App\Models\RecetaMedicoocupacional;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La receta de la ficha de Medicina General se guarda con el parte diario (antes se perdía) y se
 * consulta por fecha para "Ver receta".
 */
class MedicinaGeneralRecetasTest extends TestCase
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

    private function usuario(string $rol): User
    {
        $user = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $user->assignRole($rol);

        return $user;
    }

    private function linea(string $medicamento): array
    {
        return ['detalle_medicamento' => $medicamento, 'detalle_dosis' => '1 tableta', 'detalle_via_administracion' => 'Oral', 'frecuencia_horas' => 8, 'duracion_tratamiento_dias' => 5];
    }

    public function test_la_receta_se_guarda_con_el_parte_y_se_consulta_por_fecha(): void
    {
        $medico = $this->usuario('medico_general');
        $paciente = $this->usuario('paciente');
        $headers = ['Authorization' => 'Bearer ' . $medico->createToken('t')->plainTextToken];
        $parte = ['id_usuario_paciente' => $paciente->id, 'fecha' => '2026-10-28', 'tipo_atencion' => 'primaria', 'tipo' => 'curativo'];

        $id = $this->withHeaders($headers)->postJson('/api/v1/medicina-general/parte-diario', $parte + [
            'prescripcion_lineas' => [$this->linea('Paracetamol 500mg')],
        ])->assertCreated()->json('data.id');

        // Guardar de nuevo la atención reemplaza los medicamentos, sin duplicar la receta
        $this->withHeaders($headers)->putJson("/api/v1/medicina-general/parte-diario/$id", $parte + [
            'prescripcion_lineas' => [$this->linea('Paracetamol 500mg'), $this->linea('Loratadina 10mg')],
        ])->assertOk();

        $this->assertSame(1, RecetaMedicoocupacional::where('id_usuario_paciente', $paciente->id)->count());

        $data = $this->withHeaders($headers)
            ->getJson("/api/v1/medicina-general/recetas?id_usuario_paciente={$paciente->id}&fecha=2026-10-28")
            ->assertOk()->json('data');
        $this->assertSame(['Paracetamol 500mg', 'Loratadina 10mg'], array_column($data[0]['lineas'], 'detalle_medicamento'));
    }

    public function test_el_parte_sin_medicamentos_no_crea_receta(): void
    {
        $medico = $this->usuario('medico_general');
        $paciente = $this->usuario('paciente');

        $this->withHeaders(['Authorization' => 'Bearer ' . $medico->createToken('t')->plainTextToken])
            ->postJson('/api/v1/medicina-general/parte-diario', [
                'id_usuario_paciente' => $paciente->id, 'fecha' => '2026-10-28', 'tipo_atencion' => 'primaria', 'tipo' => 'curativo',
            ])->assertCreated();

        $this->assertSame(0, RecetaMedicoocupacional::count());
    }
}

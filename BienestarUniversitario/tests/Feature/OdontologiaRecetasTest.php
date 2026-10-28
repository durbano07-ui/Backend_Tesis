<?php

namespace Tests\Feature;

use App\Models\LineaRecetaMedicoocupacional;
use App\Models\RecetaMedicoocupacional;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Ver receta" de la pestaña Evolución de Odontología: recetas del paciente emitidas en
 * Odontología, filtradas por la fecha de la atención.
 */
class OdontologiaRecetasTest extends TestCase
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

    private function receta(User $doctor, User $paciente, string $fecha, string $medicamento): RecetaMedicoocupacional
    {
        $receta = RecetaMedicoocupacional::create([
            'id_usuario_doctor' => $doctor->id,
            'id_usuario_paciente' => $paciente->id,
            'fecha' => $fecha,
            'estado_enfermedad' => 'agudo',
        ]);
        LineaRecetaMedicoocupacional::create([
            'id_receta' => $receta->id,
            'detalle_medicamento' => $medicamento,
            'detalle_dosis' => '1 tableta',
            'frecuencia_horas' => 8,
            'duracion_tratamiento_dias' => 3,
            'detalle_via_administracion' => 'Oral',
            'detalle_numero_y_letras' => 'Cada 8h por 3 días',
            'cantidad_cajas' => 0,
            'cantidad_unidades' => 0,
            'estado_despacho' => 'pendiente',
        ]);

        return $receta;
    }

    public function test_devuelve_la_receta_de_odontologia_de_la_fecha_con_sus_medicamentos(): void
    {
        $odontologo = $this->usuario('odontologo');
        $medico = $this->usuario('medico_general');
        $paciente = $this->usuario('paciente');

        $this->receta($odontologo, $paciente, '2026-10-27', 'Ibuprofeno 400mg');
        $this->receta($odontologo, $paciente, '2026-10-20', 'Amoxicilina 500mg');
        $this->receta($medico, $paciente, '2026-10-27', 'Paracetamol 500mg'); // otro departamento

        $token = $odontologo->createToken('t')->plainTextToken;
        $data = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson("/api/v1/odontologia/recetas?id_usuario_paciente={$paciente->id}&fecha=2026-10-27")
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $data);
        $this->assertSame('Ibuprofeno 400mg', $data[0]['lineas'][0]['detalle_medicamento']);
    }

    public function test_exige_el_paciente(): void
    {
        $token = $this->usuario('odontologo')->createToken('t')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/v1/odontologia/recetas')
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_usuario_paciente');
    }
}

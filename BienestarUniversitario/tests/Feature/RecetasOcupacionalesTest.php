<?php

namespace Tests\Feature;

use App\Models\DatosIdentificacion;
use App\Models\ProductoFarmacia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Las recetas de la atención ocupacional se guardan en el servidor (antes solo existían en el
 * navegador) y el Recetario las lee de ahí.
 */
class RecetasOcupacionalesTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;
    private User $paciente;
    private int $productoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $medico = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $medico->assignRole('medico_ocupacional');
        $this->headers = ['Authorization' => 'Bearer ' . $medico->createToken('t')->plainTextToken];

        $this->paciente = User::factory()->create(['activo' => true]);
        $this->paciente->assignRole('paciente');
        DatosIdentificacion::create(['id_usuario' => $this->paciente->id, 'numero_cedula' => '0201864329', 'primer_nombre' => 'Luis', 'apellido_paterno' => 'Bayas']);

        $presentacion = DB::table('presentaciones')->insertGetId(['nombre' => 'Tabletas']);
        $this->productoId = ProductoFarmacia::create(['codigo' => 'P1', 'nombre' => 'Paracetamol', 'id_presentacion' => $presentacion, 'stock_cajas' => 5])->id;
    }

    /** Cada petición resuelve su usuario desde cero, como en producción. */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function linea(array $extra = []): array
    {
        return $extra + ['detalle_medicamento' => 'Paracetamol 500 mg', 'detalle_dosis' => '1 tableta', 'frecuencia_horas' => 8, 'duracion_tratamiento_dias' => 3, 'detalle_via_administracion' => 'Oral'];
    }

    public function test_guarda_la_receta_con_sus_lineas_y_el_recetario_la_lista(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/medicina-ocupacional/recetas-ocupacionales', [
            'id_usuario_paciente' => $this->paciente->id,
            'fecha' => '2026-10-25',
            'cie' => 'M54.5 - Lumbalgia',
            'lineas' => [$this->linea(['id_producto_farmacia' => $this->productoId]), $this->linea(['detalle_medicamento' => 'Ibuprofeno 400 mg'])],
        ])->assertCreated();

        $this->withHeaders($this->headers)->getJson('/api/v1/medicina-ocupacional/recetas-ocupacionales')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.lineas')
            ->assertJsonPath('data.0.cie', 'M54.5 - Lumbalgia')
            ->assertJsonPath('data.0.paciente.cedula', '0201864329');
    }

    public function test_solo_los_medicamentos_de_farmacia_quedan_por_despachar(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/medicina-ocupacional/recetas-ocupacionales', [
            'id_usuario_paciente' => $this->paciente->id, 'fecha' => '2026-10-25',
            'lineas' => [$this->linea()], // compra externa: sin producto
        ])->assertCreated();

        $enfermero = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $enfermero->assignRole('enfermero');
        $pendientes = $this->withHeaders(['Authorization' => 'Bearer ' . $enfermero->createToken('t')->plainTextToken])
            ->getJson('/api/v1/receta-farmacia/pendientes-despacho');
        $pendientes->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_valida_las_lineas(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/medicina-ocupacional/recetas-ocupacionales', [
            'id_usuario_paciente' => $this->paciente->id, 'fecha' => '2026-10-25',
            'lineas' => [$this->linea(['detalle_via_administracion' => 'oral.', 'frecuencia_horas' => 0])],
        ])->assertStatus(422)->assertJsonValidationErrors(['lineas.0.detalle_via_administracion', 'lineas.0.frecuencia_horas']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ProductoFarmacia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * La vía de administración es una lista cerrada: la registra Farmacia en el producto y
 * el médico solo elige de la lista cuando el medicamento no está en el inventario.
 */
class ViaAdministracionTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;
    private int $presentacionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $enfermero = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $enfermero->assignRole('enfermero');
        $this->headers = ['Authorization' => 'Bearer ' . $enfermero->createToken('t')->plainTextToken];
        $this->presentacionId = DB::table('presentaciones')->insertGetId(['nombre' => 'Tabletas']);
    }

    private function producto(array $extra = []): array
    {
        return [
            'codigo' => 'PARA-500', 'nombre' => 'Paracetamol 500 mg', 'id_presentacion' => $this->presentacionId,
            'stock_inicial_cajas' => 2, 'stock_inicial_unidades' => 0,
        ] + $extra;
    }

    public function test_farmacia_registra_la_via_y_la_busqueda_la_devuelve(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/farmacia', $this->producto(['via_administracion' => 'Oral']))
            ->assertCreated()
            ->assertJsonPath('data.via_administracion', 'Oral');

        $this->withHeaders($this->headers)->getJson('/api/v1/farmacia-search?query=Parac')
            ->assertOk()
            ->assertJsonPath('data.0.via_administracion', 'Oral');
    }

    public function test_no_acepta_vias_escritas_a_mano(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/farmacia', $this->producto(['via_administracion' => 'oral.']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('via_administracion');
    }

    public function test_la_via_es_opcional_y_se_puede_editar(): void
    {
        $id = $this->withHeaders($this->headers)->postJson('/api/v1/farmacia', $this->producto())
            ->assertCreated()
            ->json('data.id');
        $this->assertNull(ProductoFarmacia::find($id)->via_administracion);

        $this->withHeaders($this->headers)->putJson("/api/v1/farmacia/{$id}", ['via_administracion' => 'Tópica'])
            ->assertOk()
            ->assertJsonPath('data.via_administracion', 'Tópica');
    }
}

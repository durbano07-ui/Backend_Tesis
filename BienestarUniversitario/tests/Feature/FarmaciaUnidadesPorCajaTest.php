<?php

namespace Tests\Feature;

use App\Models\ProductoFarmacia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Stock de Farmacia en unidades: cajas cerradas × unidades por caja + unidades sueltas. Al
 * despachar se usan primero las sueltas y se abren cajas cuando hace falta.
 */
class FarmaciaUnidadesPorCajaTest extends TestCase
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

    private function producto(array $datos = []): ProductoFarmacia
    {
        return ProductoFarmacia::create($datos + [
            'codigo' => 'PARA-' . uniqid(), 'nombre' => 'Paracetamol', 'id_presentacion' => $this->presentacionId,
            'stock_cajas' => 3, 'stock_unidades' => 0, 'unidades_por_caja' => 10,
        ]);
    }

    public function test_al_registrar_se_piden_las_unidades_por_caja_y_se_calcula_el_total(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/v1/farmacia', [
            'codigo' => 'AMOX-500', 'nombre' => 'Amoxicilina 500 mg', 'id_presentacion' => $this->presentacionId,
            'stock_inicial_cajas' => 5, 'stock_inicial_unidades' => 4, 'unidades_por_caja' => 12,
        ])->assertCreated()
            ->assertJsonPath('data.unidades_por_caja', 12)
            ->assertJsonPath('data.stock_total_unidades', 64);

        $this->withHeaders($this->headers)->postJson('/api/v1/farmacia', [
            'codigo' => 'IBU-400', 'nombre' => 'Ibuprofeno', 'id_presentacion' => $this->presentacionId,
            'stock_inicial_cajas' => 1, 'stock_inicial_unidades' => 0,
        ])->assertStatus(422)->assertJsonValidationErrors('unidades_por_caja');
    }

    public function test_despachar_unidades_abre_una_caja_cuando_no_hay_sueltas(): void
    {
        $producto = $this->producto(); // 3 cajas cerradas de 10, 0 sueltas

        $this->assertTrue($producto->tieneStock(0, 2));
        $producto->reducirStock(0, 2, User::first());

        $producto->refresh();
        $this->assertSame(2, $producto->stock_cajas);   // se abrió una caja
        $this->assertSame(8, $producto->stock_unidades); // quedan 8 sueltas
        $this->assertSame(28, $producto->stock_total_unidades);
    }

    public function test_no_se_despacha_mas_de_lo_que_hay(): void
    {
        $producto = $this->producto(['stock_cajas' => 1, 'stock_unidades' => 3]); // 13 unidades

        $this->assertTrue($producto->tieneStock(1, 3));
        $this->assertFalse($producto->tieneStock(1, 4));
    }

    public function test_agregar_stock_acepta_solo_unidades_o_solo_cajas(): void
    {
        $producto = $this->producto();

        $this->withHeaders($this->headers)->postJson("/api/v1/farmacia/{$producto->id}/stock/add", ['cantidad_cajas' => 0, 'cantidad_unidades' => 5])
            ->assertOk();
        $this->withHeaders($this->headers)->postJson("/api/v1/farmacia/{$producto->id}/stock/add", ['cantidad_cajas' => 0, 'cantidad_unidades' => 0])
            ->assertStatus(422);

        $this->assertSame(35, $producto->fresh()->stock_total_unidades);
    }

    public function test_un_producto_sin_unidades_por_caja_conserva_el_comportamiento_anterior(): void
    {
        $producto = $this->producto(['unidades_por_caja' => null, 'stock_cajas' => 3, 'stock_unidades' => 0]);

        $this->assertNull($producto->stock_total_unidades);
        $this->assertFalse($producto->tieneStock(0, 2)); // sin saber el tamaño de la caja no se abre
        $this->assertTrue($producto->tieneStock(1, 0));
    }

    public function test_stock_bajo_se_mide_en_unidades(): void
    {
        $this->producto(['codigo' => 'BAJO', 'stock_cajas' => 0, 'stock_unidades' => 5]);   // 5 unidades
        $this->producto(['codigo' => 'ALTO', 'stock_cajas' => 3, 'stock_unidades' => 0]);   // 30 unidades

        $codigos = ProductoFarmacia::stockBajo()->pluck('codigo')->all();
        $this->assertSame(['BAJO'], $codigos);
    }
}

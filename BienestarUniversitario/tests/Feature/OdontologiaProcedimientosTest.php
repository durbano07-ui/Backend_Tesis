<?php

namespace Tests\Feature;

use App\Models\ProcedimientoOdontologia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Catálogo de procedimientos de Odontología: incluye los predeterminados y todos se pueden editar.
 */
class OdontologiaProcedimientosTest extends TestCase
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

    private function odontologo(): User
    {
        $user = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $user->assignRole('odontologo');

        return $user;
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken];
    }

    public function test_el_catalogo_trae_los_predeterminados_y_se_pueden_renombrar(): void
    {
        $odontologo = $this->odontologo();

        $catalogo = $this->withHeaders($this->headers($odontologo))->getJson('/api/v1/odontologia/procedimientos')
            ->assertOk()->json('data');
        $this->assertSame(ProcedimientoOdontologia::PREDETERMINADOS, array_column($catalogo, 'nombre_procedimiento'));

        $profilaxis = collect($catalogo)->firstWhere('nombre_procedimiento', 'Profilaxis');
        $this->withHeaders($this->headers($odontologo))
            ->putJson("/api/v1/odontologia/procedimientos/{$profilaxis['id']}", ['nombre_procedimiento' => 'Profilaxis dental'])
            ->assertOk()
            ->assertJsonPath('data.nombre_procedimiento', 'Profilaxis dental');
    }

    public function test_no_se_edita_el_catalogo_de_otro_odontologo(): void
    {
        $propio = ProcedimientoOdontologia::create(['id_usuario_doctor' => $this->odontologo()->id, 'nombre_procedimiento' => 'Sellantes']);

        $this->withHeaders($this->headers($this->odontologo()))
            ->putJson("/api/v1/odontologia/procedimientos/{$propio->id}", ['nombre_procedimiento' => 'Otro'])
            ->assertNotFound();

        $this->assertSame('Sellantes', $propio->fresh()->nombre_procedimiento);
    }
}

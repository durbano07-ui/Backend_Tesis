<?php

namespace Tests\Feature;

use App\Models\DatosIdentificacion;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Editar un usuario desde administración no debe duplicar su segundo nombre ni su segundo apellido.
 */
class EdicionNombreUsuarioTest extends TestCase
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
        $user = User::factory()->create(['name' => 'Roberto Esteban Silva Paz', 'activo' => true, 'must_change_password' => false]);
        $user->assignRole('odontologo');
        DatosIdentificacion::create([
            'id_usuario' => $user->id,
            'primer_nombre' => 'Roberto', 'segundo_nombre' => 'Esteban',
            'apellido_paterno' => 'Silva', 'apellido_materno' => 'Paz',
        ]);

        return $user;
    }

    private function admin(): array
    {
        $admin = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $admin->assignRole('administrador');

        return ['Authorization' => 'Bearer ' . $admin->createToken('t')->plainTextToken];
    }

    private function nombre(User $user): string
    {
        $d = $user->datosIdentificacion()->first();

        return implode(' ', array_filter([$d->primer_nombre, $d->segundo_nombre, $d->apellido_paterno, $d->apellido_materno]));
    }

    public function test_editar_otros_datos_no_cambia_el_nombre(): void
    {
        $odontologo = $this->odontologo();

        // El formulario siempre envía el nombre, aunque solo se cambie el correo
        $this->withHeaders($this->admin())->putJson("/api/v1/users/{$odontologo->id}", [
            'name' => 'Roberto Esteban Silva Paz',
            'email' => 'nuevo.correo@ueb.edu.ec',
        ])->assertOk();

        $this->assertSame('Roberto Esteban Silva Paz', $this->nombre($odontologo));
    }

    public function test_cambiar_el_nombre_lo_reparte_sin_duplicar(): void
    {
        $odontologo = $this->odontologo();

        $this->withHeaders($this->admin())->putJson("/api/v1/users/{$odontologo->id}", [
            'name' => 'Roberto Andrés Silva Paz',
        ])->assertOk();

        $d = $odontologo->datosIdentificacion()->first();
        $this->assertSame(['Roberto', 'Andrés', 'Silva', 'Paz'], [$d->primer_nombre, $d->segundo_nombre, $d->apellido_paterno, $d->apellido_materno]);
    }

    public function test_la_migracion_repara_los_nombres_duplicados(): void
    {
        $odontologo = $this->odontologo();
        // Estado dañado que dejaba la versión anterior
        $odontologo->datosIdentificacion()->update(['apellido_paterno' => 'Esteban Silva Paz']);
        $this->assertSame('Roberto Esteban Esteban Silva Paz Paz', $this->nombre($odontologo));

        (require database_path('migrations/2026_11_02_000002_reparar_nombres_duplicados_datos_identificacion.php'))->up();

        $this->assertSame('Roberto Esteban Silva Paz', $this->nombre($odontologo));
    }
}

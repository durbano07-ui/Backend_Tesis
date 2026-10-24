<?php

namespace Tests\Feature;

use App\Models\DatosIdentificacion;
use App\Models\User;
use App\Support\CedulaEcuatoriana;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Validación de la cédula ecuatoriana (algoritmo del Registro Civil) y corrección obligatoria de
 * las cuentas que tienen registrada una cédula inválida.
 */
class CedulaEcuatorianaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Notification::fake();
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function userWithRole(string $role, ?string $cedula = null): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('Una-clave-larga-segura-2026'),
            'activo' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole($role);
        if ($cedula !== null) {
            DatosIdentificacion::create(['id_usuario' => $user->id, 'primer_nombre' => 'Ana', 'apellido_paterno' => 'Paz', 'numero_cedula' => $cedula]);
        }

        return $user;
    }

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken];
    }

    public function test_algoritmo_de_la_cedula(): void
    {
        $this->assertTrue(CedulaEcuatoriana::esValida('1710034065'));
        $this->assertTrue(CedulaEcuatoriana::esValida('0201864329'));
        $this->assertFalse(CedulaEcuatoriana::esValida('1710034066'));   // dígito verificador
        $this->assertFalse(CedulaEcuatoriana::esValida('2510034065'));   // provincia 25 no existe
        $this->assertFalse(CedulaEcuatoriana::esValida('1760034065'));   // tercer dígito >= 6
        $this->assertFalse(CedulaEcuatoriana::esValida('171003406'));    // 9 dígitos
        $this->assertFalse(CedulaEcuatoriana::esValida('17100340AB'));
    }

    public function test_el_personal_no_registra_pacientes_con_cedula_invalida(): void
    {
        $medico = $this->userWithRole('medico_general');

        $this->withHeaders($this->bearer($medico))
            ->postJson('/api/v1/users/register-patient', [
                'email' => 'nuevo.paciente@ueb.edu.ec',
                'cedula' => '1712345678',
                'nombre_completo' => 'Juan Pérez',
                'tipo_documento' => 'cedula',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cedula']);
    }

    public function test_un_extranjero_se_registra_con_pasaporte(): void
    {
        $medico = $this->userWithRole('medico_general');

        $this->withHeaders($this->bearer($medico))
            ->postJson('/api/v1/users/register-patient', [
                'email' => 'extranjero@ueb.edu.ec',
                'cedula' => 'AB123456',
                'nombre_completo' => 'John Smith',
                'tipo_documento' => 'pasaporte',
            ])
            ->assertJsonMissingValidationErrors(['cedula']);
    }

    public function test_una_cuenta_con_cedula_invalida_debe_corregirla(): void
    {
        $paciente = $this->userWithRole('paciente', '1712345678');

        $this->withHeaders($this->bearer($paciente))
            ->getJson('/api/v1/user-profile/cedula-estado')
            ->assertOk()
            ->assertJsonPath('data.requiere_correccion', true);

        $this->withHeaders($this->bearer($paciente))
            ->patchJson('/api/v1/user-profile/cedula', ['numero_cedula' => '1710034066'])
            ->assertStatus(422);

        $this->withHeaders($this->bearer($paciente))
            ->patchJson('/api/v1/user-profile/cedula', ['numero_cedula' => '1710034065'])
            ->assertOk();

        $this->assertSame('1710034065', $paciente->datosIdentificacion()->first()->numero_cedula);

        // Ya es válida: no se puede volver a cambiar por esta vía
        $this->withHeaders($this->bearer($paciente))
            ->patchJson('/api/v1/user-profile/cedula', ['numero_cedula' => '0201864329'])
            ->assertStatus(422);
    }

    public function test_cedula_valida_o_pasaporte_no_requieren_correccion(): void
    {
        foreach (['1710034065', 'AB123456'] as $documento) {
            $paciente = $this->userWithRole('paciente', $documento);

            $this->withHeaders($this->bearer($paciente))
                ->getJson('/api/v1/user-profile/cedula-estado')
                ->assertOk()
                ->assertJsonPath('data.requiere_correccion', false);
        }
    }
}

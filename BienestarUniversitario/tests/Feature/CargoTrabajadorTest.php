<?php

namespace Tests\Feature;

use App\Models\DatosIdentificacion;
use App\Models\User;
use App\Models\UsuarioEstudiaCarrera;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El cargo de las matrices ocupacionales se toma del sistema; si el paciente no lo ha definido,
 * el médico ocupacional lo elige de una lista (docente, administrativo, código de trabajo).
 */
class CargoTrabajadorTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        foreach ([1 => 'Médico', 2 => 'Estudiante', 3 => 'Docente', 4 => 'Administrativo', 5 => 'Código de Trabajo'] as $id => $nombre) {
            DB::table('tipo_usuario')->insert(['id' => $id, 'nombre' => $nombre]);
        }
        $medico = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $medico->assignRole('medico_ocupacional');
        $this->headers = ['Authorization' => 'Bearer ' . $medico->createToken('t')->plainTextToken];
    }

    /** Cada petición resuelve su usuario desde cero, como en producción. */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function paciente(?int $tipo = null, bool $confirmado = false): User
    {
        $user = User::factory()->create(['activo' => true, 'must_change_password' => false, 'tipo_usuario_confirmado_at' => $confirmado ? now() : null]);
        $user->assignRole('paciente');
        if ($tipo) {
            UsuarioEstudiaCarrera::create(['id_usuario' => $user->id, 'id_tipo_usuario' => $tipo]);
        }

        return $user;
    }

    public function test_el_medico_asigna_el_cargo_a_un_paciente_sin_definir(): void
    {
        $paciente = $this->paciente();

        $this->withHeaders($this->headers)->putJson("/api/v1/medicina-ocupacional/patient/{$paciente->id}/tipo-trabajador", ['id_tipo_usuario' => 3])
            ->assertOk()
            ->assertJsonPath('data.puestoTrabajo', 'Personal Docente');

        $this->assertSame(3, (int) $paciente->estudioCarrera()->first()->id_tipo_usuario);
        $this->assertNotNull($paciente->fresh()->tipo_usuario_confirmado_at);
    }

    public function test_no_cambia_el_cargo_que_el_paciente_ya_definio(): void
    {
        $paciente = $this->paciente(4, confirmado: true);

        $this->withHeaders($this->headers)->putJson("/api/v1/medicina-ocupacional/patient/{$paciente->id}/tipo-trabajador", ['id_tipo_usuario' => 3])
            ->assertStatus(409);
        $this->assertSame(4, (int) $paciente->estudioCarrera()->first()->id_tipo_usuario);
    }

    public function test_solo_acepta_cargos_de_la_lista(): void
    {
        $paciente = $this->paciente();

        foreach ([1, 2, 'CONSERJE'] as $valor) {
            $this->withHeaders($this->headers)->putJson("/api/v1/medicina-ocupacional/patient/{$paciente->id}/tipo-trabajador", ['id_tipo_usuario' => $valor])
                ->assertStatus(422);
        }
    }

    public function test_la_busqueda_del_medico_ocupacional_incluye_pacientes_sin_cargo(): void
    {
        $sinTipo = $this->paciente();
        $docente = $this->paciente(3, confirmado: true);
        $estudiante = $this->paciente(2, confirmado: true);
        foreach ([[$sinTipo, '0201111111'], [$docente, '0202222222'], [$estudiante, '0203333333']] as [$u, $cedula]) {
            DatosIdentificacion::create(['id_usuario' => $u->id, 'numero_cedula' => $cedula, 'primer_nombre' => 'Ana', 'apellido_paterno' => 'Paz']);
        }

        $data = $this->withHeaders($this->headers)->getJson('/api/v1/users/search-by-cedula?query=020&ambito=ocupacional')
            ->assertOk()
            ->json('data');

        $porId = collect($data)->keyBy('id');
        $this->assertFalse($porId[$sinTipo->id]['tipo_usuario_confirmado']);
        $this->assertSame('Sin confirmar', $porId[$sinTipo->id]['puestoTrabajo']);
        $this->assertTrue($porId[$docente->id]['tipo_usuario_confirmado']);
        $this->assertSame('Personal Docente', $porId[$docente->id]['puestoTrabajo']);
        $this->assertArrayNotHasKey($estudiante->id, $porId->all());
    }
}

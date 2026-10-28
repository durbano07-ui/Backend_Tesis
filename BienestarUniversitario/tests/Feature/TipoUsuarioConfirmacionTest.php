<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsuarioEstudiaCarrera;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Confirmación obligatoria del tipo de usuario del paciente: el registro asumía "estudiante"
 * por defecto, así que se pregunta a todos y se sobrescriben los datos.
 */
class TipoUsuarioConfirmacionTest extends TestCase
{
    use RefreshDatabase;

    private int $carreraId;
    private int $facultadId;
    private int $cicloId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        foreach ([1 => 'Médico', 2 => 'Estudiante', 3 => 'Docente', 4 => 'Administrativo', 5 => 'Código de Trabajo'] as $id => $nombre) {
            DB::table('tipo_usuario')->insert(['id' => $id, 'nombre' => $nombre]);
        }
        $this->facultadId = DB::table('facultad')->insertGetId(['nombre' => 'CIENCIAS DE LA SALUD']);
        $this->carreraId = DB::table('carrera')->insertGetId(['id_facultad' => $this->facultadId, 'nombre' => 'ENFERMERÍA']);
        $this->cicloId = DB::table('ciclos')->insertGetId(['numero' => '5']);
    }

    /** Cada petición resuelve su usuario desde cero, como en producción. */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function paciente(array $estudio = null): User
    {
        $user = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $user->assignRole('paciente');
        if ($estudio) {
            UsuarioEstudiaCarrera::create(['id_usuario' => $user->id] + $estudio);
        }

        return $user;
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken];
    }

    public function test_a_todo_paciente_sin_confirmar_se_le_pregunta(): void
    {
        // Registrado como estudiante "por defecto"
        $paciente = $this->paciente(['id_tipo_usuario' => 2, 'id_facultad' => $this->facultadId, 'id_carrera' => $this->carreraId, 'id_ciclo' => $this->cicloId]);

        $this->withHeaders($this->headers($paciente))->getJson('/api/v1/user-profile/tipo-usuario-estado')
            ->assertOk()
            ->assertJsonPath('data.requiere_confirmacion', true)
            ->assertJsonPath('data.id_tipo_usuario', 2);
    }

    public function test_al_personal_no_se_le_pregunta(): void
    {
        $medico = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $medico->assignRole('medico_general');

        $this->withHeaders($this->headers($medico))->getJson('/api/v1/user-profile/tipo-usuario-estado')
            ->assertOk()
            ->assertJsonPath('data.requiere_confirmacion', false);
    }

    public function test_docente_registrado_como_estudiante_se_corrige_y_pierde_la_carrera(): void
    {
        $paciente = $this->paciente(['id_tipo_usuario' => 2, 'id_facultad' => $this->facultadId, 'id_carrera' => $this->carreraId, 'id_ciclo' => $this->cicloId]);
        $headers = $this->headers($paciente);

        $this->withHeaders($headers)->putJson('/api/v1/user-profile/tipo-usuario', ['id_tipo_usuario' => 3])->assertOk();

        $estudio = $paciente->estudioCarrera()->first();
        $this->assertSame(3, (int) $estudio->id_tipo_usuario);
        $this->assertNull($estudio->id_carrera);
        $this->assertNull($estudio->id_ciclo);
        $this->assertNull($estudio->id_facultad);

        // Ya no se le vuelve a preguntar
        $this->withHeaders($headers)->getJson('/api/v1/user-profile/tipo-usuario-estado')
            ->assertJsonPath('data.requiere_confirmacion', false);
    }

    public function test_estudiante_debe_indicar_carrera_y_ciclo(): void
    {
        $headers = $this->headers($this->paciente());

        $this->withHeaders($headers)->putJson('/api/v1/user-profile/tipo-usuario', ['id_tipo_usuario' => 2])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_carrera', 'id_ciclo']);
    }

    public function test_estudiante_guarda_carrera_ciclo_y_la_facultad_se_deduce(): void
    {
        $paciente = $this->paciente();

        $this->withHeaders($this->headers($paciente))->putJson('/api/v1/user-profile/tipo-usuario', [
            'id_tipo_usuario' => 2, 'id_carrera' => $this->carreraId, 'id_ciclo' => $this->cicloId,
        ])->assertOk();

        $estudio = $paciente->estudioCarrera()->first();
        $this->assertSame($this->carreraId, (int) $estudio->id_carrera);
        $this->assertSame($this->cicloId, (int) $estudio->id_ciclo);
        $this->assertSame($this->facultadId, (int) $estudio->id_facultad);
        $this->assertNotNull($paciente->fresh()->tipo_usuario_confirmado_at);
    }

    public function test_un_paciente_no_puede_elegir_el_tipo_medico(): void
    {
        $this->withHeaders($this->headers($this->paciente()))
            ->putJson('/api/v1/user-profile/tipo-usuario', ['id_tipo_usuario' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_tipo_usuario');
    }

    public function test_el_personal_registra_pacientes_sin_tipo_y_sin_roles_extra(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $enfermero = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $enfermero->assignRole('enfermero');

        // Aunque se envíen tipo y un rol privilegiado, se ignoran
        $this->withHeaders($this->headers($enfermero))->postJson('/api/v1/users/register-patient', [
            'nombre_completo' => 'Luis Andrés Pérez Gómez',
            'cedula' => '1710034065',
            'email' => 'luis.perez@ueb.edu.ec',
            'id_tipo_usuario' => 2,
            'tipo' => 'Estudiante',
            'role' => 'administrador',
        ])->assertCreated();

        $paciente = User::where('email', 'luis.perez@ueb.edu.ec')->firstOrFail();
        $this->assertSame(['paciente'], $paciente->getRoleNames()->all());
        $this->assertNull($paciente->estudioCarrera()->first());

        // Al iniciar sesión se le pedirá su tipo de usuario
        $this->withHeaders($this->headers($paciente))->getJson('/api/v1/user-profile/tipo-usuario-estado')
            ->assertJsonPath('data.requiere_confirmacion', true)
            ->assertJsonPath('data.id_tipo_usuario', null);
    }

    public function test_los_ciclos_del_catalogo_salen_en_orden(): void
    {
        DB::table('ciclos')->delete();
        foreach (['Cuarto', 'Octavo', 'Primero', 'Quinto', 'Segundo', 'Séptimo', 'Sexto', 'Tercero'] as $numero) {
            DB::table('ciclos')->insert(['numero' => $numero]);
        }

        $ciclos = $this->withHeaders($this->headers($this->paciente()))
            ->getJson('/api/v1/user-profile/catalogos')
            ->assertOk()
            ->json('ciclos');

        $this->assertSame(
            ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo', 'Octavo'],
            array_column($ciclos, 'numero')
        );
    }

    public function test_el_formulario_de_perfil_tambien_borra_la_carrera_si_no_es_estudiante(): void
    {
        $paciente = $this->paciente(['id_tipo_usuario' => 2, 'id_facultad' => $this->facultadId, 'id_carrera' => $this->carreraId, 'id_ciclo' => $this->cicloId]);

        $this->withHeaders($this->headers($paciente))->postJson('/api/v1/user-profile/career-study', [
            'id_tipo_usuario' => 4, 'id_facultad' => $this->facultadId,
        ])->assertCreated();

        $estudio = $paciente->estudioCarrera()->first();
        $this->assertSame(4, (int) $estudio->id_tipo_usuario);
        $this->assertNull($estudio->id_carrera);
        $this->assertNull($estudio->id_ciclo);
        $this->assertSame($this->facultadId, (int) $estudio->id_facultad); // facultad de adscripción
        $this->assertNotNull($paciente->fresh()->tipo_usuario_confirmado_at);
    }

    private function estudianteConfirmado(): User
    {
        $paciente = $this->paciente(['id_tipo_usuario' => 2, 'id_facultad' => $this->facultadId, 'id_carrera' => $this->carreraId, 'id_ciclo' => $this->cicloId]);
        $paciente->forceFill(['tipo_usuario_confirmado_at' => now()])->save();

        return $paciente;
    }

    public function test_con_el_tipo_confirmado_aun_se_puede_cambiar_desde_el_perfil(): void
    {
        // El frontend pide confirmación antes de habilitar el cambio; el servidor lo permite
        $paciente = $this->estudianteConfirmado();

        $this->withHeaders($this->headers($paciente))->postJson('/api/v1/user-profile/career-study', [
            'id_tipo_usuario' => 3,
        ])->assertCreated();

        $estudio = $paciente->estudioCarrera()->first();
        $this->assertSame(3, (int) $estudio->id_tipo_usuario);
        $this->assertNull($estudio->id_carrera);
    }

    public function test_el_perfil_no_acepta_el_tipo_medico(): void
    {
        $this->withHeaders($this->headers($this->paciente()))
            ->postJson('/api/v1/user-profile/career-study', ['id_tipo_usuario' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_tipo_usuario');
    }
}

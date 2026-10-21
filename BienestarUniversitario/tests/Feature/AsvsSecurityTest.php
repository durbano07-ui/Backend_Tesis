<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verificación de requisitos OWASP ASVS 4.0.3 (Nivel 2) implementados en la API.
 */
class AsvsSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * En producción cada petición resuelve su usuario desde cero; en las pruebas la aplicación
     * se reutiliza y el guard de Sanctum recordaría al primer usuario. Se olvida antes de cada petición.
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function userWithRole(?string $role, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('Una-clave-larga-segura-2026'),
            'activo' => true,
            'must_change_password' => false,
        ], $attrs));

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken];
    }

    // ---------------------------------------------------------------- V4 Control de acceso

    public function test_v4_1_5_paciente_no_accede_a_funciones_del_personal(): void
    {
        $headers = $this->bearer($this->userWithRole('paciente'));

        foreach ([
            ['GET', '/api/v1/users'],
            ['GET', '/api/v1/audit-logs'],
            ['GET', '/api/v1/campus-management/campus'],
            ['POST', '/api/v1/campus-management/campus'],
            ['GET', '/api/v1/users/search-by-cedula?cedula=0200000001'],
            ['POST', '/api/v1/medicina-general/signos-vitales'],
            ['DELETE', '/api/v1/enfermeria/signos-vitales/1'],
            ['GET', '/api/v1/psicologia/pacientes/1/perfil'],
        ] as [$method, $uri]) {
            $this->withHeaders($headers)->json($method, $uri)
                ->assertStatus(403, "$method $uri debería estar prohibido para un paciente");
        }
    }

    public function test_v4_1_5_usuario_sin_rol_no_accede_a_nada_clinico(): void
    {
        $headers = $this->bearer($this->userWithRole(null));

        $this->withHeaders($headers)->getJson('/api/v1/medicina-general/diagnosticos')->assertStatus(403);
    }

    public function test_v4_2_1_paciente_solo_ve_su_propio_historial_aunque_pida_otro_id(): void
    {
        $medico = $this->userWithRole('medico_general');
        $paciente = $this->userWithRole('paciente');
        $otro = $this->userWithRole('paciente');

        foreach ([$paciente, $otro, $otro] as $p) {
            \Illuminate\Support\Facades\DB::table('signos_vitales')->insert([
                'id_usuario_doctor' => $medico->id,
                'id_usuario_paciente' => $p->id,
                'fecha' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $headers = $this->bearer($paciente);
        foreach (['?id_usuario_paciente=' . $otro->id, ''] as $query) {
            $data = $this->withHeaders($headers)
                ->getJson('/api/v1/medicina-general/signos-vitales' . $query)
                ->assertOk()
                ->json('data');

            // Pida el ID que pida (o ninguno), solo recibe su propio registro
            $this->assertCount(1, $data);
            $this->assertSame($paciente->id, (int) $data[0]['id_usuario_paciente']);
        }
    }

    public function test_v4_2_1_paciente_solo_consulta_su_propia_cuenta(): void
    {
        $paciente = $this->userWithRole('paciente');
        $otro = $this->userWithRole('paciente');
        $headers = $this->bearer($paciente);

        $this->withHeaders($headers)->getJson('/api/v1/users/' . $paciente->id)->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/users/' . $otro->id)->assertStatus(403);
    }

    public function test_v4_1_3_personal_clinico_no_gestiona_usuarios_ni_logs(): void
    {
        $headers = $this->bearer($this->userWithRole('medico_general'));

        $this->withHeaders($headers)->getJson('/api/v1/medicina-general/signos-vitales')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/users')->assertStatus(403);
        $this->withHeaders($headers)->getJson('/api/v1/audit-logs')->assertStatus(403);
    }

    public function test_v4_1_3_administrador_gestiona_usuarios(): void
    {
        $headers = $this->bearer($this->userWithRole('administrador'));

        $this->withHeaders($headers)->getJson('/api/v1/users')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/audit-logs')->assertOk();
    }

    public function test_v4_1_3_cada_profesional_accede_completo_solo_a_su_modulo(): void
    {
        $odontologo = $this->bearer($this->userWithRole('odontologo'));
        $psicologo = $this->bearer($this->userWithRole('psicologo'));

        // Expediente psicológico detallado: solo Psicología
        $this->withHeaders($psicologo)->getJson('/api/v1/psicologia/psicoanamnesis')->assertOk();
        $this->withHeaders($odontologo)->getJson('/api/v1/psicologia/psicoanamnesis')->assertStatus(403);
        $this->withHeaders($odontologo)->getJson('/api/v1/psicologia/historial-sexual')->assertStatus(403);

        // Escritura en módulos ajenos: prohibida
        $this->withHeaders($psicologo)->postJson('/api/v1/odontologia/historial-evolucion', [])->assertStatus(403);
        $this->withHeaders($this->bearer($this->userWithRole('enfermero')))
            ->postJson('/api/v1/medicina-general/diagnosticos', [])->assertStatus(403);
    }

    public function test_v4_1_3_resumen_clinico_compartido_sigue_disponible(): void
    {
        // Vista integrada del paciente: cualquier profesional lee evolución y parte diario
        $this->withHeaders($this->bearer($this->userWithRole('odontologo')))
            ->getJson('/api/v1/psicologia/historial-evolucion')->assertOk();
        $this->withHeaders($this->bearer($this->userWithRole('medico_general')))
            ->getJson('/api/v1/enfermeria/signos-vitales')->assertOk();

        // Excepción documentada: Ocupacional registra diagnósticos de medicina general
        $this->withHeaders($this->bearer($this->userWithRole('medico_ocupacional')))
            ->postJson('/api/v1/medicina-general/diagnosticos', [])->assertStatus(422); // pasa el control; falla la validación
    }

    public function test_v4_1_3_administracion_no_accede_a_historias_clinicas(): void
    {
        $admin = $this->bearer($this->userWithRole('administrador'));

        $this->withHeaders($admin)->getJson('/api/v1/medicina-general/diagnosticos')->assertStatus(403);
        $this->withHeaders($admin)->getJson('/api/v1/psicologia/historial-evolucion')->assertStatus(403);
        $this->withHeaders($admin)->getJson('/api/v1/reportes/catalogos')->assertOk();
    }

    public function test_v4_1_3_usuario_multirol_suma_los_permisos_de_sus_roles(): void
    {
        $user = $this->userWithRole('medico_general');
        $user->assignRole('medico_coordinador');
        $headers = $this->bearer($user);

        $this->withHeaders($headers)->getJson('/api/v1/medicina-general/diagnosticos')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/users')->assertOk();
    }

    // ---------------------------------------------------------------- V2 Autenticación

    public function test_v2_1_1_rechaza_contrasenas_de_menos_de_12_caracteres(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'email' => 'nuevo@ueb.edu.ec',
            'password' => 'Corta1!',
            'password_confirmation' => 'Corta1!',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_v2_1_9_no_exige_reglas_de_composicion(): void
    {
        // Frase larga solo con minúsculas y espacios: válida según ASVS
        $this->postJson('/api/v1/auth/register', [
            'email' => 'frase@ueb.edu.ec',
            'password' => 'una frase larga sin simbolos',
            'password_confirmation' => 'una frase larga sin simbolos',
        ])->assertJsonMissingValidationErrors('password');
    }

    public function test_v2_4_1_no_se_guarda_ninguna_contrasena_en_texto_plano(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'clave_temporal'));
    }

    public function test_v2_3_1_contrasena_inicial_se_envia_al_usuario_y_no_se_devuelve(): void
    {
        Notification::fake();
        \Illuminate\Support\Facades\DB::table('tipo_usuario')->insert(['id' => 1, 'nombre' => 'Personal']);
        $admin = $this->userWithRole('administrador');

        $response = $this->withHeaders($this->bearer($admin))->postJson('/api/v1/users', [
            'name' => 'Ana Torres',
            'email' => 'ana.torres@ueb.edu.ec',
            'roles' => ['medico_general'],
            'id_tipo_usuario' => 1, // el panel de administración lo envía como número
        ])->assertCreated();

        // Ni la respuesta ni el recurso del usuario incluyen la contraseña
        $response->assertJsonMissingPath('temporary_password')
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.clave_temporal');
        $nuevo = User::where('email', 'ana.torres@ueb.edu.ec')->firstOrFail();
        $this->assertTrue($nuevo->must_change_password);
        Notification::assertSentTo($nuevo, \App\Notifications\PatientRegisteredNotification::class);
    }

    public function test_v11_1_4_limita_las_solicitudes_de_recuperacion_de_contrasena(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', ['email' => 'alguien@ueb.edu.ec'])->assertOk();
        }

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'alguien@ueb.edu.ec'])->assertStatus(429);
    }

    // ---------------------------------------------------------------- V3 Sesiones

    public function test_v3_3_2_token_inactivo_30_minutos_se_revoca(): void
    {
        $user = $this->userWithRole('medico_general');
        $plain = $user->createToken('test');
        $plain->accessToken->forceFill(['last_activity_at' => now()->subMinutes(31)])->save();

        $this->withHeader('Authorization', 'Bearer ' . $plain->plainTextToken)
            ->getJson('/api/v1/medicina-general/signos-vitales')
            ->assertStatus(401);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $plain->accessToken->id]);
    }

    public function test_v3_3_3_cambiar_contrasena_cierra_las_demas_sesiones(): void
    {
        $user = $this->userWithRole('medico_general');
        $otraSesion = $user->createToken('otro-dispositivo')->accessToken;
        $actual = $user->createToken('actual')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $actual)->putJson('/api/v1/auth/password', [
            'current_password' => 'Una-clave-larga-segura-2026',
            'password' => 'Otra-clave-larga-segura-2027',
            'password_confirmation' => 'Otra-clave-larga-segura-2027',
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $otraSesion->id]);
    }

    // ---------------------------------------------------------------- V6 Criptografía

    public function test_v6_1_2_datos_de_salud_cifrados_en_la_base_y_legibles_por_el_medico(): void
    {
        $medico = $this->userWithRole('medico_general');
        $paciente = $this->userWithRole('paciente');

        $diag = \App\Models\DiagnosticoMedicina::create([
            'id_usuario_doctor' => $medico->id,
            'id_usuario_paciente' => $paciente->id,
            'detalle_diagnostico' => 'Trastorno de ansiedad generalizada',
        ]);

        // En la base de datos no aparece el texto
        $raw = \Illuminate\Support\Facades\DB::table('diagnosticos_medicina')->where('id', $diag->id)->value('detalle_diagnostico');
        $this->assertStringNotContainsString('ansiedad', $raw);
        $this->assertSame('Trastorno de ansiedad generalizada', \Illuminate\Support\Facades\Crypt::decryptString($raw));

        // El médico autorizado lo recibe legible
        $this->withHeaders($this->bearer($medico))
            ->getJson('/api/v1/medicina-general/diagnosticos?id_usuario_paciente=' . $paciente->id)
            ->assertOk()
            ->assertJsonPath('data.0.detalle_diagnostico', 'Trastorno de ansiedad generalizada');
    }

    public function test_v6_1_2_la_migracion_cifra_los_registros_existentes(): void
    {
        $medico = $this->userWithRole('medico_general');
        $paciente = $this->userWithRole('paciente');

        // Registro antiguo guardado en texto plano (antes del cifrado)
        $id = \Illuminate\Support\Facades\DB::table('diagnosticos_medicina')->insertGetId([
            'id_usuario_doctor' => $medico->id,
            'id_usuario_paciente' => $paciente->id,
            'detalle_diagnostico' => 'Hipertensión arterial',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_21_000001_encrypt_health_data_at_rest.php');
        $migration->up();
        $migration->up(); // idempotente: no cifra dos veces

        $raw = \Illuminate\Support\Facades\DB::table('diagnosticos_medicina')->where('id', $id)->value('detalle_diagnostico');
        $this->assertStringNotContainsString('Hipertensión', $raw);
        $this->assertSame('Hipertensión arterial', \App\Models\DiagnosticoMedicina::find($id)->detalle_diagnostico);
    }

    // ---------------------------------------------------------------- V5 Validación

    public function test_v5_1_3_nombres_solo_aceptan_caracteres_validos(): void
    {
        $headers = $this->bearer($this->userWithRole('paciente'));

        $this->withHeaders($headers)->postJson('/api/v1/user-profile/identification', [
            'primer_nombre' => '<img src=x onerror=alert(1)>',
            'apellido_paterno' => 'Pérez',
            'numero_cedula' => '0200000001',
        ])->assertStatus(422)->assertJsonValidationErrors('primer_nombre');
    }

    // ---------------------------------------------------------------- V14 Configuración

    public function test_v14_4_cabeceras_de_seguridad_en_la_api(): void
    {
        $response = $this->withHeaders($this->bearer($this->userWithRole('paciente')))
            ->getJson('/api/v1/users');

        $response->assertStatus(403)
            ->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'")
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}

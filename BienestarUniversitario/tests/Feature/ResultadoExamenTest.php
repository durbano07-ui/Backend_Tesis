<?php

namespace Tests\Feature;

use App\Models\OrdenDeExamenMedicoocupacional;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * El médico ocupacional adjunta el PDF con los resultados a la orden de examen del paciente.
 */
class ResultadoExamenTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private array $headers;
    private OrdenDeExamenMedicoocupacional $orden;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RoleSeeder::class);
        $this->medico = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $this->medico->assignRole('medico_ocupacional');
        $this->headers = ['Authorization' => 'Bearer ' . $this->medico->createToken('t')->plainTextToken];

        $paciente = User::factory()->create(['activo' => true]);
        $paciente->assignRole('paciente');
        $this->orden = OrdenDeExamenMedicoocupacional::create([
            'id_usuario_doctor' => $this->medico->id, 'id_usuario_paciente' => $paciente->id, 'fecha' => '2026-10-25', 'estado' => 'Pendiente',
        ]);
    }

    /** Cada petición resuelve su usuario desde cero, como en producción. */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function pdf(string $contenido = "%PDF-1.4\nresultado de biometria\n%%EOF"): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('biometria.pdf', $contenido);
    }

    private function subir(UploadedFile $archivo)
    {
        return $this->withHeaders($this->headers)->post("/api/v1/medicina-ocupacional/orden-examen/{$this->orden->id}/resultado", ['archivo' => $archivo], ['Accept' => 'application/json']);
    }

    public function test_adjunta_el_pdf_cifrado_y_la_orden_queda_realizada(): void
    {
        $this->subir($this->pdf())->assertCreated()->assertJsonPath('data.tiene_resultado', true);

        $orden = $this->orden->fresh();
        $this->assertSame('Realizado', $orden->estado);
        $this->assertSame('biometria.pdf', $orden->resultado_pdf_nombre);
        // En disco no queda el PDF legible
        $guardado = Storage::disk('local')->get($orden->resultado_pdf_path);
        $this->assertStringNotContainsString('biometria', $guardado);
        $this->assertStringStartsNotWith('%PDF', $guardado);

        $respuesta = $this->withHeaders($this->headers)->get("/api/v1/medicina-ocupacional/orden-examen/{$this->orden->id}/resultado");
        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-1.4', $respuesta->getContent());
    }

    public function test_solo_acepta_pdf_reales(): void
    {
        $this->subir(UploadedFile::fake()->createWithContent('virus.pdf', 'MZ ejecutable disfrazado'))->assertStatus(422);
        $this->subir(UploadedFile::fake()->create('foto.png', 10, 'image/png'))->assertStatus(422);
        $this->assertNull($this->orden->fresh()->resultado_pdf_path);
    }

    public function test_no_reemplaza_un_resultado_ya_adjunto(): void
    {
        $this->subir($this->pdf())->assertCreated();
        $this->subir($this->pdf("%PDF-1.4\notro"))->assertStatus(409);
    }

    public function test_lista_solo_las_ordenes_pendientes_de_resultado(): void
    {
        OrdenDeExamenMedicoocupacional::create([
            'id_usuario_doctor' => $this->medico->id, 'id_usuario_paciente' => $this->orden->id_usuario_paciente,
            'fecha' => '2026-10-20', 'estado' => 'Realizado', 'resultado_pdf_path' => 'resultados-examenes/x.pdf.enc',
        ]);

        $this->withHeaders($this->headers)
            ->getJson("/api/v1/medicina-ocupacional/orden-examen?id_usuario_paciente={$this->orden->id_usuario_paciente}&sin_resultado=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->orden->id)
            ->assertJsonMissingPath('data.0.resultado_pdf_path');
    }

    public function test_otros_profesionales_no_ven_los_resultados(): void
    {
        $this->subir($this->pdf())->assertCreated();
        $psicologo = User::factory()->create(['activo' => true, 'must_change_password' => false]);
        $psicologo->assignRole('psicologo');

        $this->withHeaders(['Authorization' => 'Bearer ' . $psicologo->createToken('t')->plainTextToken])
            ->get("/api/v1/medicina-ocupacional/orden-examen/{$this->orden->id}/resultado", ['Accept' => 'application/json'])
            ->assertForbidden();
    }
}

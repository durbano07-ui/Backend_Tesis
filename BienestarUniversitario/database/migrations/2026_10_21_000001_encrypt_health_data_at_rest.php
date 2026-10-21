<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OWASP ASVS V6.1.2: cifrado en reposo de los datos de salud.
 *
 * 1. Las columnas VARCHAR pasan a TEXT (el texto cifrado ocupa más que el original).
 * 2. Se cifran los valores existentes con la APP_KEY (mismo formato que el cast "encrypted").
 *
 * Es idempotente: los valores que ya están cifrados no se vuelven a cifrar.
 * Los modelos correspondientes usan el trait App\Models\Concerns\EncryptsHealthData.
 */
return new class extends Migration
{
    private const FIELDS = [
        'analisis_resultados_psicologia' => ['detalle_analisis_resultados'],
        'antecedentes_medicina' => ['detalle_antecedente'],
        'citas_medicas' => ['motivo', 'notas_doctor'],
        'conclusiones_psicologia' => ['detalle_conclusion'],
        'diagnostico_psicologia' => ['detalle_diagnostico'],
        'diagnosticos_medicina' => ['detalle_diagnostico'],
        'enfermedades_actuales_medicina' => ['detalle_enfermedad_actual'],
        'examen_fisico_medicina' => ['detalle_examen_fisico'],
        'examen_odontologia' => ['observaciones'],
        'historial_evolucion_medicina' => ['detalle_evolucion', 'prescripcion_medica'],
        'historial_evolucion_odontologia' => ['detalle_tratamiento', 'detalle_procedimiento'],
        'historial_evolucion_psicologia' => ['detalle_evolucion'],
        'historial_laboral_psicologia' => ['detalle_laboral'],
        'historial_sexual_psicologia' => ['detalle_sexual'],
        'historial_social_psicologia' => ['detalle_social'],
        'motivo_consulta_medicina' => ['detalle_motivo'],
        'motivo_consulta_odontologia' => ['detalle_motivo', 'detalle_tratamiento', 'detalle_medicamento'],
        'motivo_consulta_psicologia' => ['detalle_motivo'],
        'parte_diario_enfermeria' => ['detalle_procedimiento', 'detalle_medicacion'],
        'parte_diario_medicina' => ['detalle_diagnostico'],
        'parte_diario_odontologia' => ['detalle_diagnostico'],
        'parte_diario_psicologia' => ['detalle_diagnostico'],
        'patologias_psicologia' => ['detalle_patologia'],
        'planes_terapeuticos_medicina' => ['detalle_plan_terapeutico'],
        'pronostico_psicologia' => ['detalle_pronostico'],
        'pruebas_aplicadas_psicologia' => ['detalle_prueba_aplicada'],
        'psicoanamnesis_psicologia' => ['detalle_psicoanamnesis'],
        'recomendacion_psicologia' => ['detalle_recomendacion'],
        'revision_organos_medicina' => ['detalle_revision_organos'],
        'usuario_tiene_alergias' => ['detalle_alergia'],
        'usuario_tiene_discapacidad' => ['detalle_discapacidad'],
        'examen_estado_mental_psicologia' => ['apariencia', 'actitud', 'juicio', 'sueno', 'apetito', 'afectividad', 'orientacion', 'atencion', 'memoria', 'lenguaje', 'pensamiento', 'conducta_motora'],
        'accidentes_laborales_medico' => ['diagnostico', 'detalle_parte_lesionada', 'causas_directas', 'novedades'],
        'enfermedades_catastroficas_medico' => ['detalle_enfermedad', 'detalle_novedades'],
        'grupo_riesgo_psico_medico' => ['detalle_diagnostico'],
        'enfermedades_nuevas_medico' => ['detalle_enfermedad_nueva'],
        'grupo_vulnerable_medico' => ['detalle_otra_enfermedad'],
        'sospechosos_confirmados_influenza' => ['detalle_resultados', 'detalle_anticuerpos', 'detalle_altamedica'],
    ];

    public function up(): void
    {
        foreach (self::FIELDS as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $existing = collect(Schema::getColumns($table))->keyBy('name');
            $columns = array_values(array_filter($columns, fn ($c) => $existing->has($c)));

            // 1. VARCHAR -> TEXT conservando si admite nulos
            foreach ($columns as $column) {
                $info = $existing[$column];
                if (!in_array(strtolower($info['type_name']), ['text', 'mediumtext', 'longtext'], true)) {
                    Schema::table($table, function (Blueprint $t) use ($column, $info) {
                        $t->text($column)->nullable($info['nullable'])->change();
                    });
                }
            }

            // 2. Cifrar los datos existentes
            $this->transform($table, $columns, fn (string $value) => $this->isEncrypted($value) ? $value : Crypt::encryptString($value));
        }
    }

    public function down(): void
    {
        foreach (self::FIELDS as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $existing = collect(Schema::getColumns($table))->keyBy('name');
            $columns = array_values(array_filter($columns, fn ($c) => $existing->has($c)));

            $this->transform($table, $columns, fn (string $value) => $this->isEncrypted($value) ? Crypt::decryptString($value) : $value);
        }
    }

    private function transform(string $table, array $columns, callable $fn): void
    {
        if (!$columns) {
            return;
        }

        DB::table($table)->select(array_merge(['id'], $columns))->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $columns, $fn) {
                foreach ($rows as $row) {
                    $changes = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if ($value !== null && $value !== '') {
                            $new = $fn((string) $value);
                            if ($new !== $value) {
                                $changes[$column] = $new;
                            }
                        }
                    }
                    if ($changes) {
                        DB::table($table)->where('id', $row->id)->update($changes);
                    }
                }
            });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};

<?php

use App\Models\CitaMedica;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Origen de la cita: "agendada" (el paciente o el personal la agendó) o "atencion_directa"
 * (el profesional atendió sin cita previa y el sistema la creó para los reportes).
 * Las atenciones directas cuentan en los reportes pero no generan notificaciones al paciente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->string('origen', 20)->default('agendada')->after('estado');
        });

        // Citas automáticas ya existentes: se reconocen por la nota que les pone el sistema.
        // motivo y notas_doctor están cifrados, así que se leen a través del modelo.
        $ids = [];
        CitaMedica::query()->select(['id', 'motivo', 'notas_doctor'])->orderBy('id')
            ->chunkById(200, function ($citas) use (&$ids) {
                foreach ($citas as $cita) {
                    try {
                        if ($cita->notas_doctor === 'Atención registrada directamente por el profesional médico'
                            || str_starts_with((string) $cita->motivo, 'Atención médica directa (Sin agendamiento previo)')) {
                            $ids[] = $cita->id;
                        }
                    } catch (\Throwable) {
                        // valor ilegible: se deja como agendada
                    }
                }
            });

        foreach (array_chunk($ids, 500) as $lote) {
            DB::table('citas_medicas')->whereIn('id', $lote)->update(['origen' => 'atencion_directa']);
        }
    }

    public function down(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};

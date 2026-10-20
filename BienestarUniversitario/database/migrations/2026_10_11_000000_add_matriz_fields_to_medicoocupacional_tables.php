<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columnas que las matrices estadísticas de Medicina Ocupacional muestran
 * y que las tablas originales no tenían, más la tabla de exámenes periódicos por mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enfermedades_catastroficas_medico', function (Blueprint $table) {
            $table->string('clasificacion', 50)->nullable()->after('detalle_enfermedad');
            $table->string('fecha_diagnostico', 100)->nullable()->after('clasificacion');
            $table->string('cargo')->nullable()->after('fecha_diagnostico');
            $table->string('recibio_tratamiento', 5)->nullable()->after('cargo');
        });

        Schema::table('accidentes_laborales_medico', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('id_usuario_paciente');
            $table->string('diagnostico')->nullable()->after('tipo_accidente');
            $table->string('gravedad', 50)->nullable()->after('diagnostico');
            $table->string('novedades')->nullable()->after('dias_perdidos');
        });

        Schema::table('ausentismo_laboral_medicoocupacional', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('id_usuario_paciente');
            $table->date('fecha')->nullable()->after('cargo');
        });

        Schema::table('listado_embarazadas_medico', function (Blueprint $table) {
            $table->unsignedSmallInteger('edad')->nullable()->after('id_usuario_paciente');
            $table->string('telefono', 30)->nullable()->after('numero_controles');
        });

        Schema::table('grupo_riesgo_psico_medico', function (Blueprint $table) {
            $table->string('tipo_servidor')->nullable()->after('id_usuario_paciente');
        });

        Schema::table('funcionarios_discapacidad_medico', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('porcentaje_discapacidad');
            $table->string('dependencia')->nullable()->after('cargo');
            $table->string('condicion_laboral')->nullable()->after('dependencia');
        });

        Schema::table('grupo_vulnerable_medico', function (Blueprint $table) {
            $table->unsignedSmallInteger('edad')->nullable()->after('id_lista_vulnerabilidad');
        });

        Schema::table('personal_nuevo_medicoocupacional', function (Blueprint $table) {
            $table->string('cargo')->nullable()->after('fecha_ingreso');
        });

        Schema::table('sospechosos_confirmados_influenza', function (Blueprint $table) {
            $table->string('tipo_trabajador')->nullable()->after('tipo');
            $table->string('lugar_trabajo')->nullable()->after('tipo_trabajador');
            $table->date('fecha_resultados')->nullable()->after('lugar_trabajo');
            $table->boolean('pcr')->default(false)->after('fecha_resultados');
            $table->boolean('prueba_rapida')->default(false)->after('pcr');
            $table->boolean('alta_medica')->default(false)->after('prueba_rapida');
            $table->boolean('descartado')->default(false)->after('alta_medica');
        });

        Schema::create('examenes_periodicos_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->unsignedInteger('cantidad')->default(0);
            $table->timestamps();
            $table->unique(['anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examenes_periodicos_medicoocupacional');

        Schema::table('sospechosos_confirmados_influenza', function (Blueprint $table) {
            $table->dropColumn(['tipo_trabajador', 'lugar_trabajo', 'fecha_resultados', 'pcr', 'prueba_rapida', 'alta_medica', 'descartado']);
        });
        Schema::table('personal_nuevo_medicoocupacional', fn (Blueprint $table) => $table->dropColumn('cargo'));
        Schema::table('grupo_vulnerable_medico', fn (Blueprint $table) => $table->dropColumn('edad'));
        Schema::table('funcionarios_discapacidad_medico', fn (Blueprint $table) => $table->dropColumn(['cargo', 'dependencia', 'condicion_laboral']));
        Schema::table('grupo_riesgo_psico_medico', fn (Blueprint $table) => $table->dropColumn('tipo_servidor'));
        Schema::table('listado_embarazadas_medico', fn (Blueprint $table) => $table->dropColumn(['edad', 'telefono']));
        Schema::table('ausentismo_laboral_medicoocupacional', fn (Blueprint $table) => $table->dropColumn(['cargo', 'fecha']));
        Schema::table('accidentes_laborales_medico', fn (Blueprint $table) => $table->dropColumn(['cargo', 'diagnostico', 'gravedad', 'novedades']));
        Schema::table('enfermedades_catastroficas_medico', fn (Blueprint $table) => $table->dropColumn(['clasificacion', 'fecha_diagnostico', 'cargo', 'recibio_tratamiento']));
    }
};

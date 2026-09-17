<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_evolucion_medicina', function (Blueprint $table) {
            $table->enum('tipo_atencion', ['primaria', 'secundaria', 'certificadomedico', 'validacion'])->default('secundaria')->after('prescripcion_medica');
        });

        Schema::table('historial_evolucion_psicologia', function (Blueprint $table) {
            $table->enum('tipo_atencion', ['primaria', 'secundaria', 'certificadomedico'])->default('secundaria')->after('detalle_evolucion');
        });

        Schema::table('historial_evolucion_odontologia', function (Blueprint $table) {
            $table->enum('tipo_atencion', ['primaria', 'secundaria', 'certificadomedico', 'validacion'])->default('secundaria')->after('prescripción_farmaceutica');
        });
    }

    public function down(): void
    {
        Schema::table('historial_evolucion_medicina', function (Blueprint $table) {
            $table->dropColumn('tipo_atencion');
        });

        Schema::table('historial_evolucion_psicologia', function (Blueprint $table) {
            $table->dropColumn('tipo_atencion');
        });

        Schema::table('historial_evolucion_odontologia', function (Blueprint $table) {
            $table->dropColumn('tipo_atencion');
        });
    }
};

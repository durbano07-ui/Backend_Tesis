<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índice para CitaMedica::marcarInasistencias(), que busca citas pendientes por estado y fecha.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->index(['estado', 'fecha'], 'citas_medicas_estado_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->dropIndex('citas_medicas_estado_fecha_index');
        });
    }
};

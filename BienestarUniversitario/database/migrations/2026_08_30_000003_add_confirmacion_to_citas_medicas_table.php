<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->boolean('confirmada_por_paciente')->default(false)->after('notas_doctor');
            $table->timestamp('fecha_confirmacion')->nullable()->after('confirmada_por_paciente');
        });
    }

    public function down(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->dropColumn(['confirmada_por_paciente', 'fecha_confirmacion']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Estado "no_asistio": la cita pendiente cuyo margen de atención (CitaMedica::HORAS_MARGEN_ATENCION)
 * terminó sin que el profesional la completara.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return;
        }
        DB::statement("ALTER TABLE citas_medicas MODIFY estado ENUM('programada','confirmada','completada','cancelada','no_asistio') NOT NULL DEFAULT 'programada'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return;
        }
        DB::table('citas_medicas')->where('estado', 'no_asistio')->update(['estado' => 'cancelada']);
        DB::statement("ALTER TABLE citas_medicas MODIFY estado ENUM('programada','confirmada','completada','cancelada') NOT NULL DEFAULT 'programada'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE parte_diario_odontologia MODIFY procedimiento VARCHAR(255) NOT NULL');
            return;
        }

        // Otros motores (SQLite en las pruebas automáticas)
        Schema::table('parte_diario_odontologia', function (Blueprint $table) {
            $table->string('procedimiento', 255)->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE parte_diario_odontologia MODIFY procedimiento ENUM('Profilaxis', 'Fluorizacion', 'Destartraje', 'Rest. Provisional', 'Rest. con Resina', 'Desgaste', 'Exodoncias', 'Receta', 'Orden de RX') NOT NULL");
    }
};

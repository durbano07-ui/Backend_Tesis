<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE parte_diario_odontologia MODIFY procedimiento VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE parte_diario_odontologia MODIFY procedimiento ENUM('Profilaxis', 'Fluorizacion', 'Destartraje', 'Rest. Provisional', 'Rest. con Resina', 'Desgaste', 'Exodoncias', 'Receta', 'Orden de RX') NOT NULL");
    }
};

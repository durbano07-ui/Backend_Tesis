<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orden_de_examen_medicoocupacional', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('fecha');
            $table->string('estado', 50)->default('Pendiente')->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_de_examen_medicoocupacional', function (Blueprint $table) {
            $table->dropColumn(['observaciones', 'estado']);
        });
    }
};

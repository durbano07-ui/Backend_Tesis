<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario_estudia_carrera', function (Blueprint $table) {
            $table->unsignedBigInteger('id_facultad')->nullable()->change();
            $table->unsignedBigInteger('id_carrera')->nullable()->change();
            $table->unsignedBigInteger('id_ciclo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('usuario_estudia_carrera', function (Blueprint $table) {
            $table->unsignedBigInteger('id_facultad')->nullable(false)->change();
            $table->unsignedBigInteger('id_carrera')->nullable(false)->change();
            $table->unsignedBigInteger('id_ciclo')->nullable(false)->change();
        });
    }
};

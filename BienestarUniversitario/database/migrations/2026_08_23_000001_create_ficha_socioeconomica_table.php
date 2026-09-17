<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_socioeconomica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nivel_instruccion_jefe_hogar', 100)->nullable();
            $table->string('empleo_jefe_hogar', 100)->nullable();
            $table->string('ingresos_mensuales', 100)->nullable();
            $table->string('tipo_vivienda', 100)->nullable();
            $table->integer('numero_personas_hogar')->nullable();
            $table->integer('numero_aportantes')->nullable();
            $table->boolean('posee_internet')->default(false);
            $table->boolean('posee_computadora')->default(false);
            $table->boolean('recibe_beca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_socioeconomica');
    }
};

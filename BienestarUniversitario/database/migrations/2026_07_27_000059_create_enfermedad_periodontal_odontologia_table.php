<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedad_periodontal_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->string('placa_bacteriana', 255)->nullable();
            $table->string('calculos_dentales', 255)->nullable();
            $table->string('bolsa_periodontal', 255)->nullable();
            $table->string('movilidad_dental', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedad_periodontal_odontologia');
    }
};

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
        Schema::create('citas_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->string('rol_doctor'); // medico_general, psicologo, odontologo, medico_ocupacional
            $table->date('fecha');
            $table->string('hora_inicio');
            $table->string('hora_fin');
            $table->enum('estado', ['programada', 'confirmada', 'completada', 'cancelada'])->default('programada');
            $table->text('motivo')->nullable();
            $table->text('notas_doctor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas_medicas');
    }
};

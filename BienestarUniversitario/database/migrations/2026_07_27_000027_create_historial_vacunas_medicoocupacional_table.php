<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_vacunas_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_listado_vacuna')->constrained('listado_vacunas_medicoocupacional')->onDelete('cascade');
            $table->string('dosis');
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_vacunas_medicoocupacional');
    }
};

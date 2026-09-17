<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_resultados_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'arp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'arp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->text('detalle_analisis_resultados');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_resultados_psicologia');
    }
};

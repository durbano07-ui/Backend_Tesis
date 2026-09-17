<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accidentes_laborales_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'acclab_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'acclab_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('lugar_accidente');
            $table->date('fecha_accidente');
            $table->string('detalle_parte_lesionada');
            $table->string('tipo_incapacidad');
            $table->string('causas_directas');
            $table->string('agente_accidente');
            $table->string('fuente_accidente');
            $table->string('tipo_accidente');
            $table->integer('dias_perdidos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accidentes_laborales_medico');
    }
};

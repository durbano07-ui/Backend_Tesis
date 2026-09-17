<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_evolucion_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'hep_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'hep_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->date('fecha');
            $table->integer('sesion_numero');
            $table->text('detalle_evolucion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_evolucion_psicologia');
    }
};

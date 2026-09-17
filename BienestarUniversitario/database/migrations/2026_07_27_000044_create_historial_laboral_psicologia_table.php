<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_laboral_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'hlp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'hlp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->text('detalle_laboral');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_laboral_psicologia');
    }
};

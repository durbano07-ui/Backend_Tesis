<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcionarios_discapacidad_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'fd_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'fd_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_tipo_discapacidad');
            $table->foreign('id_tipo_discapacidad', 'fd_disc_fk')->references('id')->on('listado_discapacidades_medico')->onDelete('cascade');
            $table->decimal('porcentaje_discapacidad', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios_discapacidad_medico');
    }
};

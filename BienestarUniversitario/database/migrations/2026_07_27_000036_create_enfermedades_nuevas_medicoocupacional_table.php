<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedades_nuevas_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'enfnu_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'enfnu_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('detalle_enfermedad_nueva');
            $table->date('fecha_aparicion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedades_nuevas_medico');
    }
};

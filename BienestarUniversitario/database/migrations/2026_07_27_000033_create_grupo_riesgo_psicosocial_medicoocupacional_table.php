<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_riesgo_psico_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'grp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'grp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('detalle_diagnostico');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_riesgo_psico_medico');
    }
};

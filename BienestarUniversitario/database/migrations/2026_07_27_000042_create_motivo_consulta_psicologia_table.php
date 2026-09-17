<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivo_consulta_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'mcp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'mcp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->text('detalle_motivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivo_consulta_psicologia');
    }
};

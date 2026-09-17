<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_vulnerable_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'gvul_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'gvul_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_lista_vulnerabilidad');
            $table->foreign('id_lista_vulnerabilidad', 'gvul_vuln_fk')->references('id')->on('lista_vulnerabilidades_medico')->onDelete('cascade');
            $table->string('detalle_otra_enfermedad')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_vulnerable_medico');
    }
};

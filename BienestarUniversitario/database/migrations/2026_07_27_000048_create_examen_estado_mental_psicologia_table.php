<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_estado_mental_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'eemp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'eemp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('apariencia')->nullable();
            $table->string('actitud')->nullable();
            $table->string('juicio')->nullable();
            $table->string('sueno')->nullable();
            $table->string('apetito')->nullable();
            $table->string('afectividad')->nullable();
            $table->string('orientacion')->nullable();
            $table->string('atencion')->nullable();
            $table->string('memoria')->nullable();
            $table->string('lenguaje')->nullable();
            $table->string('pensamiento')->nullable();
            $table->string('conducta_motora')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_estado_mental_psicologia');
    }
};

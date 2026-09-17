<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parte_diario_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'pdp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'pdp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('tipo_atencion', ['primaria', 'secundaria', 'certificadomedico']);
            $table->enum('tipo_atencion2', ['curativo', 'preventivo']);
            $table->text('detalle_diagnostico');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parte_diario_psicologia');
    }
};

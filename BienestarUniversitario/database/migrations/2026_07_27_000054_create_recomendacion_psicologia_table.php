<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recomendacion_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'recp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'recp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->text('detalle_recomendacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recomendacion_psicologia');
    }
};

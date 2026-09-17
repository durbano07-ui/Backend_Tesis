<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conclusiones_psicologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'conp_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'conp_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->text('detalle_conclusion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusiones_psicologia');
    }
};

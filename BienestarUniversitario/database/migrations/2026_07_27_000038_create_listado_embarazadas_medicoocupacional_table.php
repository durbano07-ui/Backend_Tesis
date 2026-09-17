<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listado_embarazadas_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'lemb_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario_paciente');
            $table->foreign('id_usuario_paciente', 'lemb_paciente_fk')->references('id')->on('users')->onDelete('cascade');
            $table->integer('detalle_semanas_gestacion');
            $table->date('fecha_fum');
            $table->date('fecha_probable_parto');
            $table->integer('numero_controles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listado_embarazadas_medico');
    }
};

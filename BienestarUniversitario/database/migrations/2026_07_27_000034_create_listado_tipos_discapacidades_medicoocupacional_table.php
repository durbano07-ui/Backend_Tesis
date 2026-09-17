<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listado_discapacidades_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'ldisc_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('detalle_tipo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listado_discapacidades_medico');
    }
};

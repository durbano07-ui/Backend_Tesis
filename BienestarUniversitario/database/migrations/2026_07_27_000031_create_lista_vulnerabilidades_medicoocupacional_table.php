<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_vulnerabilidades_medico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_doctor');
            $table->foreign('id_usuario_doctor', 'lvul_doctor_fk')->references('id')->on('users')->onDelete('cascade');
            $table->string('detalle_vulnerabilidad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_vulnerabilidades_medico');
    }
};

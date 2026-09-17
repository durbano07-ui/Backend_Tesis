<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivo_consulta_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->text('detalle_motivo')->nullable();
            $table->date('ultima_visita_fecha')->nullable();
            $table->string('algun_tratamiento', 5)->default('no'); // sí/no
            $table->string('algun_medicamento', 5)->default('no'); // sí/no
            $table->text('detalle_tratamiento')->nullable();
            $table->text('detalle_medicamento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motivo_consulta_odontologia');
    }
};

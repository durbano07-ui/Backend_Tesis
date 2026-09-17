<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos_paciente_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_insumo')->constrained('catalogo_insumos_odontologia')->onDelete('cascade');
            $table->string('cantidad_gastada'); // Formato: "1x", "2x", "1/2x", "x", "1"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumos_paciente_odontologia');
    }
};

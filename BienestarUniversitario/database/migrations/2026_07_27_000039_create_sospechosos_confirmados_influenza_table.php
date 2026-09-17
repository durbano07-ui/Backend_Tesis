<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sospechosos_confirmados_influenza', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['sospechoso', 'confirmado']);
            $table->string('detalle_resultados')->nullable();
            $table->string('detalle_anticuerpos')->nullable();
            $table->string('detalle_altamedica')->nullable();
            $table->integer('dias_aislamiento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sospechosos_confirmados_influenza');
    }
};

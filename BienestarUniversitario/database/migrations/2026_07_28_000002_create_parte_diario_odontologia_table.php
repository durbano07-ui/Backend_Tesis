<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parte_diario_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('tipo_atencion', ['primaria', 'secundaria', 'certificadomedico', 'validacion']);
            $table->enum('tipo_atencion2', ['curativo', 'preventivo']);
            $table->text('detalle_diagnostico')->nullable();
            $table->string('procedimiento', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parte_diario_odontologia');
    }
};

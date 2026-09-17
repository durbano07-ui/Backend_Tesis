<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parte_diario_enfermeria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('tipo_atencion', ['primaria', 'secundaria']);
            $table->enum('tipo', ['curativo', 'preventivo']);
            $table->text('detalle_procedimiento')->nullable();
            $table->text('detalle_medicacion')->nullable();
            $table->foreignId('id_procedimiento_enfermeria')->nullable()->constrained('procedimientos_enfermeria')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parte_diario_enfermeria');
    }
};

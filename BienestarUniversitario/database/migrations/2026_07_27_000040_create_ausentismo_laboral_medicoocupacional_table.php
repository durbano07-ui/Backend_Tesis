<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ausentismo_laboral_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['enfermedad comun', 'enfermedad laboral', 'accidente laboral', 'otros']);
            $table->string('detalle_ausentismo');
            $table->integer('dias_perdidos');
            $table->decimal('horas_perdidas', 8, 2);
            $table->decimal('horas_trabajadas', 8, 2);
            $table->decimal('indice_ausentismo', 8, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ausentismo_laboral_medicoocupacional');
    }
};

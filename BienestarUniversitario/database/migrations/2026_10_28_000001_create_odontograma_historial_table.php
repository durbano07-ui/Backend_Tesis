<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado del odontograma de cada paciente al cierre de cada día de atención. El odontograma se
 * guarda reemplazando todas sus marcas, así que sin esta tabla solo se conocía el estado actual
 * y la historia clínica no podía mostrar cómo estaban los dientes en cada cita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontograma_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_paciente')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->json('asignaciones');
            $table->timestamps();

            $table->unique(['id_usuario_paciente', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograma_historial');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linea_receta_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_receta')->constrained('receta_medicoocupacional')->onDelete('cascade');
            $table->string('detalle_medicamento');
            $table->string('detalle_dosis');
            $table->integer('frecuencia_horas');
            $table->integer('duracion_tratamiento_dias');
            $table->string('detalle_via_administracion');
            $table->string('detalle_numero_y_letras');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linea_receta_medicoocupacional');
    }
};

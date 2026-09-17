<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_de_examen_tipo_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_orden_examen')->constrained('orden_de_examen_medicoocupacional')->onDelete('cascade');
            $table->foreignId('id_tipo_examen')->constrained('tipo_examen_medicoocupacional')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_de_examen_tipo_medicoocupacional');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signo_alarma_receta_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_receta')->constrained('receta_medicoocupacional')->onDelete('cascade');
            $table->string('detalle_alarma');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signo_alarma_receta_medicoocupacional');
    }
};

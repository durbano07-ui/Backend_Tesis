<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta_cie_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_receta')->constrained('receta_medicoocupacional')->onDelete('cascade');
            $table->string('detalle_cie10');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_cie_medicoocupacional');
    }
};

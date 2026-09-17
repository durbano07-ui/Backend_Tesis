<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listado_vacunas_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->string('detalle_vacuna');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listado_vacunas_medicoocupacional');
    }
};

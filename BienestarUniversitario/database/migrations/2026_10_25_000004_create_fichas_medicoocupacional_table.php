<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fichas médicas ocupacionales (ingreso, periódica, retiro, reintegro...) emitidas en cada
 * atención. Antes solo existían en el navegador y en Reportes se mostraban datos de ejemplo.
 * El contenido clínico se guarda cifrado (OWASP ASVS V6.1.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_usuario_paciente')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->date('fecha');
            $table->longText('contenido');
            $table->timestamps();
            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas_medicoocupacional');
    }
};

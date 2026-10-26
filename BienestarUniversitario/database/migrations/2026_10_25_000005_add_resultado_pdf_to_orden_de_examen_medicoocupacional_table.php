<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PDF con los resultados de la orden de examen, subido por el médico ocupacional.
 * El archivo se guarda cifrado en el disco privado; aquí solo queda su ubicación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_de_examen_medicoocupacional', function (Blueprint $table) {
            $table->string('resultado_pdf_path')->nullable()->after('estado');
            $table->string('resultado_pdf_nombre')->nullable()->after('resultado_pdf_path');
            $table->timestamp('resultado_subido_at')->nullable()->after('resultado_pdf_nombre');
            $table->foreignId('resultado_subido_por')->nullable()->after('resultado_subido_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orden_de_examen_medicoocupacional', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resultado_subido_por');
            $table->dropColumn(['resultado_pdf_path', 'resultado_pdf_nombre', 'resultado_subido_at']);
        });
    }
};

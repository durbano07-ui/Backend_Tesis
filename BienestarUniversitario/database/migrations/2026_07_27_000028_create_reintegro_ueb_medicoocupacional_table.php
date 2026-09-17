<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reintegro_ueb_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->string('detalle_motivo_salida');
            $table->date('fecha_salida');
            $table->date('fecha_reintegro');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reintegro_ueb_medicoocupacional');
    }
};

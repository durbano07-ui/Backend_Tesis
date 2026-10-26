<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca cuándo el paciente confirmó su tipo de usuario (estudiante, docente, administrativo,
 * código de trabajo). Hasta ahora el registro asumía "estudiante" por defecto, así que todos los
 * pacientes existentes quedan sin confirmar y se les pregunta al iniciar sesión.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('tipo_usuario_confirmado_at')->nullable()->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tipo_usuario_confirmado_at');
        });
    }
};

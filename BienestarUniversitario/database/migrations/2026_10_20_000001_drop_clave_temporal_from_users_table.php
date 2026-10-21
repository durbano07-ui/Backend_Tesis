<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OWASP ASVS V2.4.1: las contraseñas solo se guardan con hash.
 * La columna clave_temporal almacenaba la contraseña temporal en texto plano y no se leía
 * en ningún lugar del sistema (must_change_password ya indica que es temporal).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'clave_temporal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('clave_temporal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('clave_temporal')->nullable()->after('password');
        });
    }
};

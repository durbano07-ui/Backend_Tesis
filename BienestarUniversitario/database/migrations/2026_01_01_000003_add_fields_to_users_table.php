<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('clave_temporal')->nullable()->after('password');
            $table->boolean('must_change_password')->default(false)->after('clave_temporal');
            $table->boolean('activo')->default(true)->after('must_change_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['clave_temporal', 'must_change_password', 'activo']);
        });
    }
};

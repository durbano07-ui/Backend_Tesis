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
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->foreignId('id_usuario_medico_general')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('atendido')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signos_vitales', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_medico_general']);
            $table->dropColumn(['id_usuario_medico_general', 'atendido']);
        });
    }
};

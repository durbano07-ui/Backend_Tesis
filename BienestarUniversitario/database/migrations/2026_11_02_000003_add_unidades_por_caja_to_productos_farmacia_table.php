<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unidades que trae cada caja. Con este dato, stock_cajas son las cajas cerradas y stock_unidades
 * las unidades sueltas (de cajas abiertas); el total es cajas × unidades por caja + sueltas.
 * Los productos existentes quedan sin definir (NULL) y conservan el comportamiento anterior hasta
 * que Farmacia lo complete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos_farmacia', function (Blueprint $table) {
            $table->unsignedInteger('unidades_por_caja')->nullable()->after('stock_unidades');
        });
    }

    public function down(): void
    {
        Schema::table('productos_farmacia', function (Blueprint $table) {
            $table->dropColumn('unidades_por_caja');
        });
    }
};

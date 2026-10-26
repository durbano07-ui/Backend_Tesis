<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vía de administración registrada por Farmacia: al recetar un producto del inventario
 * el médico ya no la escribe, se toma de aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos_farmacia', function (Blueprint $table) {
            $table->string('via_administracion', 30)->nullable()->after('id_presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('productos_farmacia', function (Blueprint $table) {
            $table->dropColumn('via_administracion');
        });
    }
};

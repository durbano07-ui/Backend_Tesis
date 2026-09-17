<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // linea_receta_medicoocupacional: agregar columnas faltantes para integración con farmacia
        Schema::table('linea_receta_medicoocupacional', function (Blueprint $table) {
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'id_producto_farmacia')) {
                $table->unsignedBigInteger('id_producto_farmacia')->nullable()->after('id_receta');
                $table->foreign('id_producto_farmacia', 'lrm_pf_fk')
                    ->references('id')
                    ->on('productos_farmacia')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'cantidad_cajas')) {
                $table->integer('cantidad_cajas')->default(0)->after('id_producto_farmacia');
            }
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'cantidad_unidades')) {
                $table->integer('cantidad_unidades')->default(0)->after('cantidad_cajas');
            }
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'id_usuario_enfermera_despacho')) {
                $table->unsignedBigInteger('id_usuario_enfermera_despacho')->nullable()->after('detalle_numero_y_letras');
                $table->foreign('id_usuario_enfermera_despacho', 'lrm_enf_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'estado_despacho')) {
                $table->enum('estado_despacho', ['pendiente', 'despachado'])
                    ->default('pendiente')
                    ->after('id_usuario_enfermera_despacho');
            }
            if (!Schema::hasColumn('linea_receta_medicoocupacional', 'fecha_despacho')) {
                $table->timestamp('fecha_despacho')->nullable()->after('estado_despacho');
            }
        });
    }

    public function down(): void
    {
        Schema::table('linea_receta_medicoocupacional', function (Blueprint $table) {
            if (Schema::hasColumn('linea_receta_medicoocupacional', 'id_producto_farmacia')) {
                $table->dropForeign('lrm_pf_fk');
                $table->dropColumn('id_producto_farmacia');
            }
            if (Schema::hasColumn('linea_receta_medicoocupacional', 'id_usuario_enfermera_despacho')) {
                $table->dropForeign('lrm_enf_fk');
                $table->dropColumn('id_usuario_enfermera_despacho');
            }
            $table->dropColumn(array_filter([
                Schema::hasColumn('linea_receta_medicoocupacional', 'cantidad_cajas') ? 'cantidad_cajas' : null,
                Schema::hasColumn('linea_receta_medicoocupacional', 'cantidad_unidades') ? 'cantidad_unidades' : null,
                Schema::hasColumn('linea_receta_medicoocupacional', 'estado_despacho') ? 'estado_despacho' : null,
                Schema::hasColumn('linea_receta_medicoocupacional', 'fecha_despacho') ? 'fecha_despacho' : null,
            ]));
        });
    }
};
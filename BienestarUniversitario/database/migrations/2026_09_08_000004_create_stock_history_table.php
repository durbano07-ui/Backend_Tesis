<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto_farmacia')
                ->constrained('productos_farmacia')
                ->onDelete('cascade');
            $table->foreignId('id_usuario')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'despacho']);
            $table->integer('cantidad_cajas')->default(0);
            $table->integer('cantidad_unidades')->default(0);
            $table->integer('stock_anterior_cajas')->default(0);
            $table->integer('stock_anterior_unidades')->default(0);
            $table->integer('stock_nuevo_cajas')->default(0);
            $table->integer('stock_nuevo_unidades')->default(0);
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('id_linea_receta')->nullable();
            $table->foreign('id_linea_receta', 'sh_lr_fk')
                ->references('id')
                ->on('linea_receta_medicoocupacional')
                ->onDelete('set null');
            $table->timestamps();

            $table->index(['id_producto_farmacia', 'created_at']);
            $table->index(['tipo_movimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_history');
    }
};
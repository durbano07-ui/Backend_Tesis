<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_insumos_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_medico')->constrained('users')->onDelete('cascade');
            $table->string('nombre');
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_insumos_odontologia');
    }
};

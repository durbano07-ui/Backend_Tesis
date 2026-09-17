<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_sangre', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 10)->unique();
            $table->timestamps();
        });

        Schema::create('usuario_tipo_sangre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_tipo_sangre')->constrained('tipo_sangre')->onDelete('cascade');
            $table->unsignedBigInteger('asignado_por_usuario')->nullable();
            $table->string('asignado_por_rol', 50)->default('paciente');
            $table->timestamps();

            $table->foreign('asignado_por_usuario', 'uts_user_fk')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_tipo_sangre');
        Schema::dropIfExists('tipo_sangre');
    }
};
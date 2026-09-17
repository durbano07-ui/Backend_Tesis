<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->string('piel', 255)->nullable();
            $table->string('labios', 255)->nullable();
            $table->string('carrillos', 255)->nullable();
            $table->string('paladar', 255)->nullable();
            $table->string('piso_de_la_boca', 255)->nullable();
            $table->string('lengua', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('glándulas_salivales', 255)->nullable();
            $table->string('ganglios', 255)->nullable();
            $table->string('tejido_muscular', 255)->nullable();
            $table->string('atm', 255)->nullable(); // Articulación temporomandibular
            $table->string('maxilar_superior', 255)->nullable();
            $table->string('maxilar_inferior', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_odontologia');
    }
};

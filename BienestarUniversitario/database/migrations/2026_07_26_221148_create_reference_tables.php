<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identificacion_etnica', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('identificacion_genero', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('identificacion_estado_civil', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
        });

        Schema::create('provincia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('provincia_canton', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_provincia')->constrained('provincia')->onDelete('cascade');
            $table->string('nombre');
        });

        Schema::create('facultad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('carrera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_facultad')->constrained('facultad')->onDelete('cascade');
            $table->string('nombre');
        });

        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
        });

        Schema::create('tipo_direccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('tipo_usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('condicion_laboral_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('lugar_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('odontograma_odontologia', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pieza_dental'); // numbers 1-32 and letters A-T
        });

        Schema::create('odontograma_pieza_carilla_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_numero_pieza')->constrained('odontograma_odontologia')->onDelete('cascade');
            $table->string('numero_carilla'); // Mesial, Distal, Oclusal, Lingual/Palatal, Vestibular
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograma_pieza_carilla_odontologia');
        Schema::dropIfExists('odontograma_odontologia');
        Schema::dropIfExists('lugar_trabajo');
        Schema::dropIfExists('condicion_laboral_medicoocupacional');
        Schema::dropIfExists('tipo_usuario');
        Schema::dropIfExists('tipo_direccion');
        Schema::dropIfExists('ciclos');
        Schema::dropIfExists('carrera');
        Schema::dropIfExists('facultad');
        Schema::dropIfExists('provincia_canton');
        Schema::dropIfExists('provincia');
        Schema::dropIfExists('identificacion_estado_civil');
        Schema::dropIfExists('identificacion_genero');
        Schema::dropIfExists('identificacion_etnica');
    }
};

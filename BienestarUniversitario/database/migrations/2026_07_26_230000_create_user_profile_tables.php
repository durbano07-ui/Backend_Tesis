<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_identificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('primer_nombre', 100);
            $table->string('segundo_nombre', 100)->nullable();
            $table->string('numero_cedula', 20)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
        });

        Schema::create('usuario_tiene_hijos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->integer('numero');
        });

        Schema::create('usuario_tiene_alergias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->text('detalle_alergia');
        });

        Schema::create('usuario_tiene_discapacidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->text('detalle_discapacidad');
        });

        Schema::create('usuario_estudia_carrera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('id_facultad')->constrained('facultad');
            $table->foreignId('id_carrera')->constrained('carrera');
            $table->foreignId('id_ciclo')->constrained('ciclos');
            $table->foreignId('id_tipo_usuario')->constrained('tipo_usuario');
        });

        Schema::create('datos_autopercepcion_ciudadana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('id_identificacion_etnica')->nullable()->constrained('identificacion_etnica');
            $table->foreignId('id_genero')->nullable()->constrained('identificacion_genero');
            $table->foreignId('id_estado_civil')->nullable()->constrained('identificacion_estado_civil');
            $table->string('nacionalidad', 100)->default('Ecuador');
        });

        Schema::create('direcciones_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_provincia')->nullable()->constrained('provincia');
            $table->foreignId('id_canton')->nullable()->constrained('provincia_canton');
            $table->string('direccion_referencia', 255)->nullable();
            $table->string('telefono_convencional', 20)->nullable();
            $table->string('correo_electronico', 255)->nullable();
            $table->string('nacionalidad', 100)->default('Ecuador');
            $table->foreignId('id_tipo_direccion')->constrained('tipo_direccion');
            $table->boolean('es_extranjero')->default(false);
        });

        Schema::create('contactos_emergencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->string('nombre_completo', 255);
            $table->string('parentesco', 100);
            $table->string('telefono', 20);
            $table->string('celular', 20)->nullable();
        });

        Schema::create('foto_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->string('direccion_imagen', 500);
        });

        Schema::create('listado_cargo_personal_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users')->onDelete('cascade');
            $table->string('detalle_cargo', 255);
        });

        Schema::create('usuario_tiene_cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_cargo')->constrained('listado_cargo_personal_medicoocupacional')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('usuario_lugar_de_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_lugar_trabajo')->constrained('lugar_trabajo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_lugar_de_trabajo');
        Schema::dropIfExists('usuario_tiene_cargo');
        Schema::dropIfExists('listado_cargo_personal_medicoocupacional');
        Schema::dropIfExists('foto_usuario');
        Schema::dropIfExists('contactos_emergencias');
        Schema::dropIfExists('direcciones_usuario');
        Schema::dropIfExists('datos_autopercepcion_ciudadana');
        Schema::dropIfExists('usuario_estudia_carrera');
        Schema::dropIfExists('usuario_tiene_discapacidad');
        Schema::dropIfExists('usuario_tiene_alergias');
        Schema::dropIfExists('usuario_tiene_hijos');
        Schema::dropIfExists('datos_identificacion');
    }
};

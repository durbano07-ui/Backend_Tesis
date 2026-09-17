<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontograma_estado_odontologia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('color', 7);
            $table->text('svg_icon');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('odontograma_paciente_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_paciente')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique('id_usuario_paciente');
        });

        Schema::create('odontograma_asignacion_odontologia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_odontograma_paciente');
            $table->unsignedBigInteger('id_numero_pieza');
            $table->unsignedBigInteger('id_numero_carilla')->nullable();
            $table->unsignedBigInteger('id_estado');
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('id_odontograma_paciente', 'od_asig_op_fk')
                ->references('id')->on('odontograma_paciente_odontologia')
                ->onDelete('cascade');
            $table->foreign('id_numero_pieza', 'od_asig_pieza_fk')
                ->references('id')->on('odontograma_odontologia');
            $table->foreign('id_numero_carilla', 'od_asig_carilla_fk')
                ->references('id')->on('odontograma_pieza_carilla_odontologia');
            $table->foreign('id_estado', 'od_asig_estado_fk')
                ->references('id')->on('odontograma_estado_odontologia');

            $table->unique(['id_odontograma_paciente', 'id_numero_pieza', 'id_numero_carilla'], 'od_asig_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograma_asignacion_odontologia');
        Schema::dropIfExists('odontograma_paciente_odontologia');
        Schema::dropIfExists('odontograma_estado_odontologia');
    }
};
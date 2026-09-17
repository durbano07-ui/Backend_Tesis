<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimientos_odontologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario_doctor')->constrained('users')->onDelete('cascade');
            $table->string('nombre_procedimiento', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimientos_odontologia');
    }
};

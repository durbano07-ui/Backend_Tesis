<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receta_recomendaciones_nofarmaco_medicoocupacional', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_receta');
            $table->foreign('id_receta', 'rnf_id_receta_fk')->references('id')->on('receta_medicoocupacional')->onDelete('cascade');
            $table->string('detalle_recomendacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recomendaciones_nofarmacologicas_receta_medicoocupacional');
    }
};

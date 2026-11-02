<?php

use App\Models\DatosIdentificacion;
use Illuminate\Database\Migrations\Migration;

/**
 * Repara los nombres que quedaron duplicados al editar un usuario desde el panel de administración:
 * el primer apellido recibía "segundo nombre + apellidos" sin limpiar el segundo nombre ni el
 * segundo apellido (p. ej. "Roberto Esteban | Esteban Silva Paz | Paz"). Solo se corrige ese patrón.
 */
return new class extends Migration
{
    public function up(): void
    {
        DatosIdentificacion::whereNotNull('apellido_paterno')->each(function (DatosIdentificacion $d) {
            $paterno = trim((string) $d->apellido_paterno);
            $original = $paterno;
            $segundo = trim((string) $d->segundo_nombre);
            $materno = trim((string) $d->apellido_materno);

            if ($segundo !== '' && str_starts_with(mb_strtolower($paterno), mb_strtolower($segundo) . ' ')) {
                $paterno = trim(mb_substr($paterno, mb_strlen($segundo)));
            }
            if ($materno !== '' && str_ends_with(mb_strtolower($paterno), ' ' . mb_strtolower($materno))) {
                $paterno = trim(mb_substr($paterno, 0, mb_strlen($paterno) - mb_strlen($materno)));
            }

            if ($paterno !== $original && $paterno !== '') {
                $d->apellido_paterno = $paterno;
                $d->save();
            }
        });
    }

    public function down(): void
    {
        // Corrección de datos: no se revierte
    }
};

<?php

use App\Models\ProcedimientoOdontologia;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Los procedimientos predeterminados (Profilaxis, Fluorización, etc.) estaban escritos en el
 * formulario de atención y no aparecían en el catálogo, así que no se podían editar. Se agregan
 * al catálogo de cada odontólogo que no los tenga.
 */
return new class extends Migration
{
    public function up(): void
    {
        // En una base nueva los roles aún no existen; los odontólogos nuevos reciben el catálogo
        // predeterminado al abrir la pestaña de procedimientos.
        if (!\Spatie\Permission\Models\Role::where('name', 'odontologo')->where('guard_name', 'sanctum')->exists()) {
            return;
        }

        foreach (User::role('odontologo', 'sanctum')->pluck('id') as $doctorId) {
            foreach (ProcedimientoOdontologia::PREDETERMINADOS as $nombre) {
                ProcedimientoOdontologia::firstOrCreate([
                    'id_usuario_doctor' => $doctorId,
                    'nombre_procedimiento' => $nombre,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Datos de catálogo: no se eliminan al revertir para no perder los que se hayan editado
    }
};

<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Historial clínico por departamento (mínimo privilegio, OWASP ASVS V4.1.3).
 *
 * - Sin paciente: cada profesional ve solo sus propios registros (su parte diario).
 * - Con paciente: los registros de ese paciente hechos por profesionales de SU MISMO
 *   departamento (p. ej. un médico ocupacional ve lo de cualquier médico ocupacional, pero no
 *   lo de Enfermería u Odontología).
 * - Médico coordinador y administrador ven todos los registros del paciente.
 * - El paciente ve todo lo suyo (RestrictPatientAccess ya fija id_usuario_paciente a su id).
 */
trait ScopesHistoryByDepartment
{
    private static array $clinicalRoles = ['enfermero', 'medico_general', 'medico_ocupacional', 'psicologo', 'odontologo'];

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    protected function historyQuery(string $model, Request $request): Builder
    {
        $user = $request->user();

        if (!$request->filled('id_usuario_paciente')) {
            return $model::where('id_usuario_doctor', $user->id);
        }

        $query = $model::where('id_usuario_paciente', $request->id_usuario_paciente);

        if ($user->hasAnyRole(['medico_coordinador', 'administrador', 'paciente'])) {
            return $query;
        }

        $myRoles = array_values(array_intersect($user->getRoleNames()->all(), self::$clinicalRoles));
        if (!$myRoles) {
            return $query->where('id_usuario_doctor', $user->id);
        }

        return $query->whereIn('id_usuario_doctor', User::role($myRoles, 'sanctum')->select('id'));
    }
}

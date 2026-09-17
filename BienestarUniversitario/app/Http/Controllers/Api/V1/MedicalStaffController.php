<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserProfile\AsignarPacienteRequest;
use App\Models\ListadoCargoPersonalMedicoocupacional;
use App\Models\UsuarioTieneCargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalStaffController extends Controller
{
    public function getCargos(): JsonResponse
    {
        $cargos = ListadoCargoPersonalMedicoocupacional::all();

        return response()->json([
            'data' => $cargos,
            'message' => 'Cargos retrieved',
        ]);
    }

    public function storeCargo(Request $request): JsonResponse
    {
        $request->validate([
            'detalle_cargo' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $cargo = ListadoCargoPersonalMedicoocupacional::updateOrCreate(
            ['id_usuario' => $user->id],
            ['detalle_cargo' => $request->detalle_cargo]
        );

        return response()->json([
            'data' => $cargo,
            'message' => 'Cargo saved successfully',
        ], 201);
    }

    public function getMisPacientes(): JsonResponse
    {
        $user = Auth::user();
        $pacientes = $user->pacientesACargo()
            ->with(['datosIdentificacion', 'estudioCarrera.carrera'])
            ->get();

        return response()->json([
            'data' => $pacientes,
            'message' => 'Patients retrieved',
        ]);
    }

    public function asignarPaciente(AsignarPacienteRequest $request): JsonResponse
    {
        $user = Auth::user();

        $existe = UsuarioTieneCargo::where('id_usuario_doctor', $user->id)
            ->where('id_usuario_paciente', $request->id_usuario_paciente)
            ->where('id_cargo', $request->id_cargo)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Patient already assigned with this cargo',
            ], 422);
        }

        $asignacion = UsuarioTieneCargo::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_cargo' => $request->id_cargo,
        ]);

        return response()->json([
            'data' => $asignacion,
            'message' => 'Patient assigned successfully',
        ], 201);
    }

    public function desasignarPaciente($id): JsonResponse
    {
        $user = Auth::user();

        $asignacion = UsuarioTieneCargo::where('id_usuario_doctor', $user->id)
            ->where('id_usuario_paciente', $id)
            ->first();

        if (!$asignacion) {
            return response()->json([
                'message' => 'Assignment not found',
            ], 404);
        }

        $asignacion->delete();

        return response()->json(null, 204);
    }

    public function getDoctores(Request $request): JsonResponse
    {
        $rolesToSearch = ['medico_general', 'medico_ocupacional', 'medico_coordinador', 'doctor', 'medico'];
        $query = \App\Models\User::whereHas('roles', function ($q) use ($rolesToSearch) {
            $q->whereIn('name', $rolesToSearch);
        })->with(['lugaresDeTrabajo', 'cargoMedico', 'datosIdentificacion']);

        if ($request->has('campus_id')) {
            $campusId = (int) $request->query('campus_id');
            $query->whereHas('lugaresDeTrabajo', function ($q) use ($campusId) {
                $q->where('lugar_trabajo.id', $campusId);
            });
        }

        $doctoresList = $query->get();

        if ($doctoresList->isEmpty()) {
            $doctoresList = \App\Models\User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'paciente');
            })->with(['lugaresDeTrabajo', 'cargoMedico', 'datosIdentificacion'])->get();
        }

        $doctores = $doctoresList->map(function ($user) {
            $pIdent = $user->datosIdentificacion;
            $fullName = '';
            if ($pIdent) {
                $fullName = trim("{$pIdent->primer_nombre} {$pIdent->segundo_nombre} {$pIdent->apellido_paterno} {$pIdent->apellido_materno}");
                $fullName = preg_replace('/\s+/', ' ', $fullName);
            }
            if (empty($fullName)) {
                $fullName = $user->name ?: $user->email;
            }

            $cargoDetalle = $user->cargoMedico?->detalle_cargo;
            if (!$cargoDetalle) {
                if ($user->hasRole('medico_ocupacional')) {
                    $cargoDetalle = 'Médico Ocupacional';
                } elseif ($user->hasRole('medico_coordinador')) {
                    $cargoDetalle = 'Médico Coordinador';
                } else {
                    $cargoDetalle = 'Médico General';
                }
            }

            return [
                'id'              => $user->id,
                'name'            => $fullName,
                'nombre_completo' => $fullName,
                'email'           => $user->email,
                'cargo'           => $cargoDetalle,
                'campuses'        => $user->lugaresDeTrabajo->map(fn($l) => [
                    'id'     => $l->id,
                    'nombre' => $l->nombre,
                ])->values(),
            ];
        });

        return response()->json([
            'data'    => $doctores,
            'message' => 'Doctors retrieved',
        ]);
    }
}

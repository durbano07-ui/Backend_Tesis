<?php

namespace App\Http\Controllers\Api\V1\Enfermeria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enfermeria\StoreParteDiarioEnfermeriaRequest;
use App\Http\Requests\Enfermeria\StoreProcedimientoEnfermeriaRequest;
use App\Http\Requests\Enfermeria\StoreSignosVitalesRequest;
use App\Http\Requests\Enfermeria\UpdateParteDiarioEnfermeriaRequest;
use App\Http\Requests\Enfermeria\UpdateSignosVitalesRequest;
use App\Models\ParteDiarioEnfermeria;
use App\Models\ProcedimientoEnfermeria;
use App\Models\SignosVitales;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnfermeriaController extends Controller
{
    // ==========================================
    // PROCEDIMIENTOS DE ENFERMERIA
    // ==========================================

    public function indexProcedimientos(): JsonResponse
    {
        $user = Auth::user();
        $procedimientos = ProcedimientoEnfermeria::where('id_usuario_doctor', $user->id)->get();

        return response()->json([
            'data' => $procedimientos,
            'message' => 'Procedimientos retrieved',
        ]);
    }

    public function showProcedimiento(int $id): JsonResponse
    {
        $user = Auth::user();
        $procedimiento = ProcedimientoEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$procedimiento) {
            return response()->json(['message' => 'Procedimiento not found'], 404);
        }

        return response()->json([
            'data' => $procedimiento,
            'message' => 'Procedimiento retrieved',
        ]);
    }

    public function storeProcedimiento(StoreProcedimientoEnfermeriaRequest $request): JsonResponse
    {
        $user = Auth::user();

        $procedimiento = ProcedimientoEnfermeria::create([
            'id_usuario_doctor' => $user->id,
            'nombre_procedimiento' => $request->nombre_procedimiento,
        ]);

        return response()->json([
            'data' => $procedimiento,
            'message' => 'Procedimiento created successfully',
        ], 201);
    }

    public function updateProcedimiento(StoreProcedimientoEnfermeriaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $procedimiento = ProcedimientoEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$procedimiento) {
            return response()->json(['message' => 'Procedimiento not found'], 404);
        }

        $procedimiento->update([
            'nombre_procedimiento' => $request->nombre_procedimiento,
        ]);

        return response()->json([
            'data' => $procedimiento,
            'message' => 'Procedimiento updated successfully',
        ]);
    }

    public function destroyProcedimiento(int $id): JsonResponse
    {
        $user = Auth::user();
        $procedimiento = ProcedimientoEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$procedimiento) {
            return response()->json(['message' => 'Procedimiento not found'], 404);
        }

        $procedimiento->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // SIGNOS VITALES
    // ==========================================

    public function indexSignosVitales(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = SignosVitales::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        $signosVitales = $query->with(['paciente.datosIdentificacion', 'medicoGeneral'])->get();

        return response()->json([
            'data' => $signosVitales,
            'message' => 'Signos vitales retrieved',
        ]);
    }

    public function showSignosVitales(int $id): JsonResponse
    {
        $user = Auth::user();
        $signosVitales = SignosVitales::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion', 'medicoGeneral'])
            ->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        return response()->json([
            'data' => $signosVitales,
            'message' => 'Signos vitales retrieved',
        ]);
    }

    public function storeSignosVitales(StoreSignosVitalesRequest $request): JsonResponse
    {
        $user = Auth::user();

        $signosVitales = SignosVitales::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_usuario_medico_general' => $request->id_usuario_medico_general,
            'fecha' => $request->fecha ?? now(),
            'presion_arterial_diastolica' => $request->presion_arterial_diastolica,
            'presion_arterial_sistolica' => $request->presion_arterial_sistolica,
            'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
            'temperatura' => $request->temperatura,
            'talla' => $request->talla,
            'peso' => $request->peso,
        ]);

        return response()->json([
            'data' => $signosVitales->load(['paciente.datosIdentificacion', 'medicoGeneral']),
            'message' => 'Signos vitales created successfully',
        ], 201);
    }

    public function updateSignosVitales(UpdateSignosVitalesRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $signosVitales = SignosVitales::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        $signosVitales->update($request->validated());

        return response()->json([
            'data' => $signosVitales->load(['paciente.datosIdentificacion']),
            'message' => 'Signos vitales updated successfully',
        ]);
    }

    public function destroySignosVitales(int $id): JsonResponse
    {
        $user = Auth::user();
        $signosVitales = SignosVitales::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        $signosVitales->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // PARTE DIARIO DE ENFERMERIA
    // ==========================================

    public function indexParteDiario(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = ParteDiarioEnfermeria::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        $partes = $query->with(['paciente.datosIdentificacion', 'procedimiento'])->get();

        return response()->json([
            'data' => $partes,
            'message' => 'Partes diarios retrieved',
        ]);
    }

    public function showParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion', 'procedimiento'])
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        return response()->json([
            'data' => $parte,
            'message' => 'Parte diario retrieved',
        ]);
    }

    public function storeParteDiario(StoreParteDiarioEnfermeriaRequest $request): JsonResponse
    {
        $user = Auth::user();

        $parte = ParteDiarioEnfermeria::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'tipo_atencion' => $request->tipo_atencion,
            'tipo' => $request->tipo,
            'detalle_procedimiento' => $request->detalle_procedimiento,
            'detalle_medicacion' => $request->detalle_medicacion,
            'id_procedimiento_enfermeria' => $request->id_procedimiento_enfermeria,
        ]);

        if (!empty($request->id_usuario_paciente)) {
            \App\Http\Controllers\Api\V1\CitasMedicasController::syncAutoCita($user->id, (int)$request->id_usuario_paciente, 'enfermero', $request->detalle_procedimiento ?? 'Atención en Enfermería');
        }

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion', 'procedimiento']),
            'message' => 'Parte diario created successfully',
        ], 201);
    }

    public function updateParteDiario(UpdateParteDiarioEnfermeriaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        $parte->update($request->validated());

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion', 'procedimiento']),
            'message' => 'Parte diario updated successfully',
        ]);
    }

    public function destroyParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioEnfermeria::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        $parte->delete();

        return response()->json(null, 204);
    }
}
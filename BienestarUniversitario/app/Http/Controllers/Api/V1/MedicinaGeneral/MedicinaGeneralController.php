<?php

namespace App\Http\Controllers\Api\V1\MedicinaGeneral;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicinaGeneral\StoreAntecedenteRequest;
use App\Http\Requests\MedicinaGeneral\StoreDiagnosticoRequest;
use App\Http\Requests\MedicinaGeneral\StoreEnfermedadActualRequest;
use App\Http\Requests\MedicinaGeneral\StoreExamenFisicoRequest;
use App\Http\Requests\MedicinaGeneral\StoreHistorialEvolucionRequest;
use App\Http\Requests\MedicinaGeneral\StoreMotivoConsultaRequest;
use App\Http\Requests\MedicinaGeneral\StoreParteDiarioMedicinaRequest;
use App\Http\Requests\MedicinaGeneral\StorePlanTerapeuticoRequest;
use App\Http\Requests\MedicinaGeneral\StoreRevisionOrganosRequest;
use App\Http\Requests\MedicinaGeneral\UpdateParteDiarioMedicinaRequest;
use App\Models\AntecedenteMedicina;
use App\Models\DiagnosticoMedicina;
use App\Models\EnfermedadActualMedicina;
use App\Models\ExamenFisicoMedicina;
use App\Models\HistorialEvolucionMedicina;
use App\Models\MotivoConsultaMedicina;
use App\Models\ParteDiarioMedicina;
use App\Models\PlanTerapeuticoMedicina;
use App\Models\RevisionOrganosMedicina;
use App\Models\SignosVitales;
use App\Models\UsuarioTipoSangre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicinaGeneralController extends Controller
{
    // ==========================================
    // SIGNOS VITALES (el médico puede crear y modificar)
    // ==========================================

    public function indexSignosVitales(Request $request): JsonResponse
    {
        $query = SignosVitales::query();

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        $signosVitales = $query->with(['paciente.datosIdentificacion', 'doctor', 'medicoGeneral'])->orderBy('fecha', 'desc')->get();

        return response()->json([
            'data' => $signosVitales,
            'message' => 'Signos vitales retrieved',
        ]);
    }

    public function showSignosVitales(int $id): JsonResponse
    {
        $signosVitales = SignosVitales::where('id', $id)
            ->with(['paciente.datosIdentificacion', 'doctor', 'medicoGeneral'])
            ->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        return response()->json([
            'data' => $signosVitales,
            'message' => 'Signos vitales retrieved',
        ]);
    }

    public function storeSignosVitales(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'id_usuario_medico_general' => ['nullable', 'integer', 'exists:users,id'],
            'fecha' => ['nullable', 'date'],
            'presion_arterial_diastolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'presion_arterial_sistolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:300'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:100'],
            'temperatura' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'talla' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ]);

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

    public function updateSignosVitales(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'id_usuario_paciente' => ['sometimes', 'integer', 'exists:users,id'],
            'id_usuario_medico_general' => ['nullable', 'integer', 'exists:users,id'],
            'fecha' => ['sometimes', 'date'],
            'presion_arterial_diastolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'presion_arterial_sistolica' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:300'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:100'],
            'temperatura' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'talla' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'peso' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $user = Auth::user();
        $signosVitales = SignosVitales::where('id', $id)->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        $signosVitales->update($validated);

        return response()->json([
            'data' => $signosVitales->load(['paciente.datosIdentificacion', 'medicoGeneral']),
            'message' => 'Signos vitales updated successfully',
        ]);
    }

    public function destroySignosVitales(int $id): JsonResponse
    {
        $user = Auth::user();
        $signosVitales = SignosVitales::where('id', $id)->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found'], 404);
        }

        $signosVitales->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // PARTE DIARIO MEDICINA (compartido con médico ocupacional)
    // ==========================================

    public function indexParteDiario(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = ParteDiarioMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        $partes = $query->with(['paciente.datosIdentificacion'])->get();

        return response()->json([
            'data' => $partes,
            'message' => 'Partes diarios retrieved',
        ]);
    }

    public function showParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioMedicina::where('id', $id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        return response()->json([
            'data' => $parte,
            'message' => 'Parte diario retrieved',
        ]);
    }

    public function storeParteDiario(StoreParteDiarioMedicinaRequest $request): JsonResponse
    {
        $user = Auth::user();

        $parte = ParteDiarioMedicina::create([
            'id_usuario_doctor' => $user->id,
            ...$request->validated(),
        ]);

        if (!empty($parte->id_usuario_paciente)) {
            \App\Http\Controllers\Api\V1\CitasMedicasController::syncAutoCita($user->id, (int)$parte->id_usuario_paciente, 'medico_general', 'Atención en Medicina General');
        }

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion']),
            'message' => 'Parte diario created successfully',
        ], 201);
    }

    public function updateParteDiario(UpdateParteDiarioMedicinaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        $parte->update($request->validated());

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion']),
            'message' => 'Parte diario updated successfully',
        ]);
    }

    public function destroyParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$parte) {
            return response()->json(['message' => 'Parte diario not found'], 404);
        }

        $parte->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // MOTIVO CONSULTA
    // ==========================================

    public function indexMotivoConsulta(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = MotivoConsultaMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Motivos de consulta retrieved',
        ]);
    }

    public function showMotivoConsulta(int $id): JsonResponse
    {
        $user = Auth::user();
        $motivo = MotivoConsultaMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$motivo) {
            return response()->json(['message' => 'Motivo de consulta not found'], 404);
        }

        return response()->json([
            'data' => $motivo,
            'message' => 'Motivo de consulta retrieved',
        ]);
    }

    public function storeMotivoConsulta(StoreMotivoConsultaRequest $request): JsonResponse
    {
        $user = Auth::user();

        $motivo = MotivoConsultaMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo' => $request->detalle_motivo,
        ]);

        return response()->json([
            'data' => $motivo->load(['paciente.datosIdentificacion']),
            'message' => 'Motivo de consulta created successfully',
        ], 201);
    }

    public function updateMotivoConsulta(StoreMotivoConsultaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $motivo = MotivoConsultaMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$motivo) {
            return response()->json(['message' => 'Motivo de consulta not found'], 404);
        }

        $motivo->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo' => $request->detalle_motivo,
        ]);

        return response()->json([
            'data' => $motivo->load(['paciente.datosIdentificacion']),
            'message' => 'Motivo de consulta updated successfully',
        ]);
    }

    public function destroyMotivoConsulta(int $id): JsonResponse
    {
        $user = Auth::user();
        $motivo = MotivoConsultaMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$motivo) {
            return response()->json(['message' => 'Motivo de consulta not found'], 404);
        }

        $motivo->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // ANTECEDENTES (PERSONALES Y FAMILIARES)
    // ==========================================

    public function indexAntecedentes(Request $request): JsonResponse
    {
        $query = AntecedenteMedicina::query();

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        } else {
            $user = Auth::user();
            $query->where('id_usuario_doctor', $user->id);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json([
            'data' => $query->orderBy('id', 'desc')->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Antecedentes retrieved',
        ]);
    }

    public function showAntecedente(int $id): JsonResponse
    {
        $antecedente = AntecedenteMedicina::with(['paciente.datosIdentificacion'])->find($id);

        if (!$antecedente) {
            return response()->json(['message' => 'Antecedente not found'], 404);
        }

        return response()->json([
            'data' => $antecedente,
            'message' => 'Antecedente retrieved',
        ]);
    }

    public function storeAntecedente(StoreAntecedenteRequest $request): JsonResponse
    {
        $user = Auth::user();

        $antecedente = AntecedenteMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'tipo' => $request->tipo,
            'detalle_antecedente' => $request->detalle_antecedente,
        ]);

        return response()->json([
            'data' => $antecedente->load(['paciente.datosIdentificacion']),
            'message' => 'Antecedente created successfully',
        ], 201);
    }

    public function updateAntecedente(StoreAntecedenteRequest $request, int $id): JsonResponse
    {
        $antecedente = AntecedenteMedicina::find($id);

        if (!$antecedente) {
            return response()->json(['message' => 'Antecedente not found'], 404);
        }

        $antecedente->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'tipo' => $request->tipo,
            'detalle_antecedente' => $request->detalle_antecedente,
        ]);

        return response()->json([
            'data' => $antecedente->load(['paciente.datosIdentificacion']),
            'message' => 'Antecedente updated successfully',
        ]);
    }

    public function destroyAntecedente(int $id): JsonResponse
    {
        $antecedente = AntecedenteMedicina::find($id);

        if (!$antecedente) {
            return response()->json(['message' => 'Antecedente not found'], 404);
        }

        $antecedente->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // ENFERMEDADES ACTUALES
    // ==========================================

    public function indexEnfermedadesActuales(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = EnfermedadActualMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Enfermedades actuales retrieved',
        ]);
    }

    public function showEnfermedadActual(int $id): JsonResponse
    {
        $user = Auth::user();
        $enfermedad = EnfermedadActualMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$enfermedad) {
            return response()->json(['message' => 'Enfermedad actual not found'], 404);
        }

        return response()->json([
            'data' => $enfermedad,
            'message' => 'Enfermedad actual retrieved',
        ]);
    }

    public function storeEnfermedadActual(StoreEnfermedadActualRequest $request): JsonResponse
    {
        $user = Auth::user();

        $enfermedad = EnfermedadActualMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_enfermedad_actual' => $request->detalle_enfermedad_actual,
        ]);

        return response()->json([
            'data' => $enfermedad->load(['paciente.datosIdentificacion']),
            'message' => 'Enfermedad actual created successfully',
        ], 201);
    }

    public function updateEnfermedadActual(StoreEnfermedadActualRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $enfermedad = EnfermedadActualMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$enfermedad) {
            return response()->json(['message' => 'Enfermedad actual not found'], 404);
        }

        $enfermedad->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_enfermedad_actual' => $request->detalle_enfermedad_actual,
        ]);

        return response()->json([
            'data' => $enfermedad->load(['paciente.datosIdentificacion']),
            'message' => 'Enfermedad actual updated successfully',
        ]);
    }

    public function destroyEnfermedadActual(int $id): JsonResponse
    {
        $user = Auth::user();
        $enfermedad = EnfermedadActualMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$enfermedad) {
            return response()->json(['message' => 'Enfermedad actual not found'], 404);
        }

        $enfermedad->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // REVISIÓN DE ÓRGANOS
    // ==========================================

    public function indexRevisionOrganos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = RevisionOrganosMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Revisión de órganos retrieved',
        ]);
    }

    public function showRevisionOrganos(int $id): JsonResponse
    {
        $user = Auth::user();
        $revision = RevisionOrganosMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$revision) {
            return response()->json(['message' => 'Revisión de órganos not found'], 404);
        }

        return response()->json([
            'data' => $revision,
            'message' => 'Revisión de órganos retrieved',
        ]);
    }

    public function storeRevisionOrganos(StoreRevisionOrganosRequest $request): JsonResponse
    {
        $user = Auth::user();

        $revision = RevisionOrganosMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_revision_organos' => $request->detalle_revision_organos,
            'posicion_x' => $request->posicion_x,
            'posicion_y' => $request->posicion_y,
        ]);

        return response()->json([
            'data' => $revision->load(['paciente.datosIdentificacion']),
            'message' => 'Revisión de órganos created successfully',
        ], 201);
    }

    public function updateRevisionOrganos(StoreRevisionOrganosRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $revision = RevisionOrganosMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$revision) {
            return response()->json(['message' => 'Revisión de órganos not found'], 404);
        }

        $revision->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_revision_organos' => $request->detalle_revision_organos,
            'posicion_x' => $request->posicion_x,
            'posicion_y' => $request->posicion_y,
        ]);

        return response()->json([
            'data' => $revision->load(['paciente.datosIdentificacion']),
            'message' => 'Revisión de órganos updated successfully',
        ]);
    }

    public function destroyRevisionOrganos(int $id): JsonResponse
    {
        $user = Auth::user();
        $revision = RevisionOrganosMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$revision) {
            return response()->json(['message' => 'Revisión de órganos not found'], 404);
        }

        $revision->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // EXAMEN FÍSICO
    // ==========================================

    public function indexExamenFisico(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = ExamenFisicoMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Exámenes físicos retrieved',
        ]);
    }

    public function showExamenFisico(int $id): JsonResponse
    {
        $user = Auth::user();
        $examen = ExamenFisicoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$examen) {
            return response()->json(['message' => 'Examen físico not found'], 404);
        }

        return response()->json([
            'data' => $examen,
            'message' => 'Examen físico retrieved',
        ]);
    }

    public function storeExamenFisico(StoreExamenFisicoRequest $request): JsonResponse
    {
        $user = Auth::user();

        $examen = ExamenFisicoMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_examen_fisico' => $request->detalle_examen_fisico,
            'altura_x' => $request->altura_x,
            'altura_y' => $request->altura_y,
        ]);

        return response()->json([
            'data' => $examen->load(['paciente.datosIdentificacion']),
            'message' => 'Examen físico created successfully',
        ], 201);
    }

    public function updateExamenFisico(StoreExamenFisicoRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $examen = ExamenFisicoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$examen) {
            return response()->json(['message' => 'Examen físico not found'], 404);
        }

        $examen->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_examen_fisico' => $request->detalle_examen_fisico,
            'altura_x' => $request->altura_x,
            'altura_y' => $request->altura_y,
        ]);

        return response()->json([
            'data' => $examen->load(['paciente.datosIdentificacion']),
            'message' => 'Examen físico updated successfully',
        ]);
    }

    public function destroyExamenFisico(int $id): JsonResponse
    {
        $user = Auth::user();
        $examen = ExamenFisicoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$examen) {
            return response()->json(['message' => 'Examen físico not found'], 404);
        }

        $examen->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // DIAGNÓSTICOS
    // ==========================================

    public function indexDiagnosticos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = DiagnosticoMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Diagnósticos retrieved',
        ]);
    }

    public function showDiagnostico(int $id): JsonResponse
    {
        $user = Auth::user();
        $diagnostico = DiagnosticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$diagnostico) {
            return response()->json(['message' => 'Diagnóstico not found'], 404);
        }

        return response()->json([
            'data' => $diagnostico,
            'message' => 'Diagnóstico retrieved',
        ]);
    }

    public function storeDiagnostico(StoreDiagnosticoRequest $request): JsonResponse
    {
        $user = Auth::user();

        $diagnostico = DiagnosticoMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_diagnostico' => $request->detalle_diagnostico,
            'cie10' => $request->cie10,
            'presuntivo' => $request->presuntivo ?? false,
            'definitivo' => $request->definitivo ?? false,
        ]);

        return response()->json([
            'data' => $diagnostico->load(['paciente.datosIdentificacion']),
            'message' => 'Diagnóstico created successfully',
        ], 201);
    }

    public function updateDiagnostico(StoreDiagnosticoRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $diagnostico = DiagnosticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$diagnostico) {
            return response()->json(['message' => 'Diagnóstico not found'], 404);
        }

        $diagnostico->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_diagnostico' => $request->detalle_diagnostico,
            'cie10' => $request->cie10,
            'presuntivo' => $request->presuntivo ?? false,
            'definitivo' => $request->definitivo ?? false,
        ]);

        return response()->json([
            'data' => $diagnostico->load(['paciente.datosIdentificacion']),
            'message' => 'Diagnóstico updated successfully',
        ]);
    }

    public function destroyDiagnostico(int $id): JsonResponse
    {
        $user = Auth::user();
        $diagnostico = DiagnosticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$diagnostico) {
            return response()->json(['message' => 'Diagnóstico not found'], 404);
        }

        $diagnostico->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // PLANES TERAPÉUTICOS
    // ==========================================

    public function indexPlanesTerapeuticos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = PlanTerapeuticoMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->get(),
            'message' => 'Planes terapéuticos retrieved',
        ]);
    }

    public function showPlanTerapeutico(int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = PlanTerapeuticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$plan) {
            return response()->json(['message' => 'Plan terapéutico not found'], 404);
        }

        return response()->json([
            'data' => $plan,
            'message' => 'Plan terapéutico retrieved',
        ]);
    }

    public function storePlanTerapeutico(StorePlanTerapeuticoRequest $request): JsonResponse
    {
        $user = Auth::user();

        $plan = PlanTerapeuticoMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_plan_terapeutico' => $request->detalle_plan_terapeutico,
        ]);

        return response()->json([
            'data' => $plan->load(['paciente.datosIdentificacion']),
            'message' => 'Plan terapéutico created successfully',
        ], 201);
    }

    public function updatePlanTerapeutico(StorePlanTerapeuticoRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = PlanTerapeuticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$plan) {
            return response()->json(['message' => 'Plan terapéutico not found'], 404);
        }

        $plan->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_plan_terapeutico' => $request->detalle_plan_terapeutico,
        ]);

        return response()->json([
            'data' => $plan->load(['paciente.datosIdentificacion']),
            'message' => 'Plan terapéutico updated successfully',
        ]);
    }

    public function destroyPlanTerapeutico(int $id): JsonResponse
    {
        $user = Auth::user();
        $plan = PlanTerapeuticoMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$plan) {
            return response()->json(['message' => 'Plan terapéutico not found'], 404);
        }

        $plan->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // HISTORIAL DE EVOLUCIÓN
    // ==========================================

    public function indexHistorialEvolucion(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = HistorialEvolucionMedicina::where('id_usuario_doctor', $user->id);

        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        return response()->json([
            'data' => $query->with(['paciente.datosIdentificacion'])->orderBy('fecha', 'desc')->get(),
            'message' => 'Historial de evolución retrieved',
        ]);
    }

    public function showHistorialEvolucion(int $id): JsonResponse
    {
        $user = Auth::user();
        $historial = HistorialEvolucionMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])
            ->first();

        if (!$historial) {
            return response()->json(['message' => 'Historial de evolución not found'], 404);
        }

        return response()->json([
            'data' => $historial,
            'message' => 'Historial de evolución retrieved',
        ]);
    }

    public function storeHistorialEvolucion(StoreHistorialEvolucionRequest $request): JsonResponse
    {
        $user = Auth::user();

        $historial = HistorialEvolucionMedicina::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'detalle_evolucion' => $request->detalle_evolucion,
            'prescripcion_medica' => $request->prescripcion_medica,
        ]);

        return response()->json([
            'data' => $historial->load(['paciente.datosIdentificacion']),
            'message' => 'Historial de evolución created successfully',
        ], 201);
    }

    public function updateHistorialEvolucion(StoreHistorialEvolucionRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $historial = HistorialEvolucionMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$historial) {
            return response()->json(['message' => 'Historial de evolución not found'], 404);
        }

        $historial->update([
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'detalle_evolucion' => $request->detalle_evolucion,
            'prescripcion_medica' => $request->prescripcion_medica,
        ]);

        return response()->json([
            'data' => $historial->load(['paciente.datosIdentificacion']),
            'message' => 'Historial de evolución updated successfully',
        ]);
    }

    public function destroyHistorialEvolucion(int $id): JsonResponse
    {
        $user = Auth::user();
        $historial = HistorialEvolucionMedicina::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$historial) {
            return response()->json(['message' => 'Historial de evolución not found'], 404);
        }

        $historial->delete();

        return response()->json(null, 204);
    }

    // ==========================================
    // SIGNOS VITALES - MARCAR COMO ATENDIDO
    // ==========================================

    public function marcarAtendido(int $id): JsonResponse
    {
        $user = Auth::user();

        $signosVitales = SignosVitales::where('id', $id)
            ->where('id_usuario_medico_general', $user->id)
            ->first();

        if (!$signosVitales) {
            return response()->json(['message' => 'Signos vitales not found or not assigned to you'], 404);
        }

        $signosVitales->update(['atendido' => true]);

        return response()->json([
            'data' => $signosVitales->load(['paciente.datosIdentificacion', 'doctor', 'medicoGeneral']),
            'message' => 'Signos vitales marked as attended',
        ]);
    }

    public function pacientesPendientes(): JsonResponse
    {
        $user = Auth::user();

        $pendientes = SignosVitales::where('id_usuario_medico_general', $user->id)
            ->where('atendido', false)
            ->with(['paciente.datosIdentificacion', 'doctor'])
            ->orderBy('fecha', 'desc')
            ->get();

        $count = $pendientes->count();

        return response()->json([
            'data' => [
                'count' => $count,
                'pacientes' => $pendientes,
            ],
            'message' => 'Pending patients retrieved',
        ]);
    }

    // ==========================================
    // BLOOD TYPE (medico general asigna/edita)
    // ==========================================

    public function getPatientBloodType(int $patientId): JsonResponse
    {
        $user = User::find($patientId);
        if (!$user) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $actual = $user->tipoSangreActual()->with('tipoSangre')->first();

        return response()->json([
            'data' => $actual,
            'message' => $actual ? 'Blood type retrieved' : 'No blood type assigned',
        ]);
    }

    public function storePatientBloodType(Request $request, int $patientId): JsonResponse
    {
        $request->validate([
            'id_tipo_sangre' => 'required|integer|exists:tipo_sangre,id',
        ]);

        $patient = User::find($patientId);
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $user = Auth::user();

        $registro = UsuarioTipoSangre::create([
            'id_usuario' => $patientId,
            'id_tipo_sangre' => $request->id_tipo_sangre,
            'asignado_por_usuario' => $user->id,
            'asignado_por_rol' => 'medico_general',
        ]);

        return response()->json([
            'data' => $registro->load('tipoSangre'),
            'message' => 'Blood type saved successfully',
        ], 201);
    }
}
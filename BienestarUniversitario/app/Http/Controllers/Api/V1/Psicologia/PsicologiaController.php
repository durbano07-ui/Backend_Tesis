<?php

namespace App\Http\Controllers\Api\V1\Psicologia;

use App\Http\Controllers\Controller;
use App\Models\AnalisisResultadosPsicologia;
use App\Models\ConclusionesPsicologia;
use App\Models\DiagnosticoPsicologia;
use App\Models\ExamenEstadoMentalPsicologia;
use App\Models\HistorialEvolucionPsicologia;
use App\Models\HistorialLaboralPsicologia;
use App\Models\HistorialSexualPsicologia;
use App\Models\HistorialSocialPsicologia;
use App\Models\MotivoConsultaPsicologia;
use App\Models\ParteDiarioPsicologia;
use App\Models\PatologiasPsicologia;
use App\Models\PronosticoPsicologia;
use App\Models\PsicoanamnesisPsicologia;
use App\Models\PruebasAplicadasPsicologia;
use App\Models\RecomendacionPsicologia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PsicologiaController extends Controller
{
    // ==========================================
    // MOTIVO CONSULTA
    // ==========================================

    public function indexMotivoConsulta(Request $request): JsonResponse
    {
        $query = MotivoConsultaPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Motivo consulta retrieved']);
    }

    public function showMotivoConsulta(int $id): JsonResponse
    {
        $data = MotivoConsultaPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Motivo consulta retrieved']);
    }

    public function storeMotivoConsulta(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_motivo' => 'required|string',
        ]);
        $user = Auth::user();
        $data = MotivoConsultaPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo' => $request->detalle_motivo,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateMotivoConsulta(Request $request, int $id): JsonResponse
    {
        $data = MotivoConsultaPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_motivo' => 'sometimes|string']);
        $data->update($request->only(['detalle_motivo']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyMotivoConsulta(int $id): JsonResponse
    {
        $data = MotivoConsultaPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PSICOANAMNESIS
    // ==========================================

    public function indexPsicoanamnesis(Request $request): JsonResponse
    {
        $query = PsicoanamnesisPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Psicoanamnesis retrieved']);
    }

    public function showPsicoanamnesis(int $id): JsonResponse
    {
        $data = PsicoanamnesisPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Psicoanamnesis retrieved']);
    }

    public function storePsicoanamnesis(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_psicoanamnesis' => 'required|string',
            'tipo' => 'required|in:personal,familiar',
        ]);
        $user = Auth::user();
        $data = PsicoanamnesisPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_psicoanamnesis' => $request->detalle_psicoanamnesis,
            'tipo' => $request->tipo,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updatePsicoanamnesis(Request $request, int $id): JsonResponse
    {
        $data = PsicoanamnesisPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'detalle_psicoanamnesis' => 'sometimes|string',
            'tipo' => 'sometimes|in:personal,familiar',
        ]);
        $data->update($request->only(['detalle_psicoanamnesis', 'tipo']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyPsicoanamnesis(int $id): JsonResponse
    {
        $data = PsicoanamnesisPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL LABORAL
    // ==========================================

    public function indexHistorialLaboral(Request $request): JsonResponse
    {
        $query = HistorialLaboralPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial laboral retrieved']);
    }

    public function showHistorialLaboral(int $id): JsonResponse
    {
        $data = HistorialLaboralPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial laboral retrieved']);
    }

    public function storeHistorialLaboral(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_laboral' => 'required|string',
        ]);
        $user = Auth::user();
        $data = HistorialLaboralPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_laboral' => $request->detalle_laboral,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateHistorialLaboral(Request $request, int $id): JsonResponse
    {
        $data = HistorialLaboralPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_laboral' => 'sometimes|string']);
        $data->update($request->only(['detalle_laboral']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyHistorialLaboral(int $id): JsonResponse
    {
        $data = HistorialLaboralPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL SOCIAL
    // ==========================================

    public function indexHistorialSocial(Request $request): JsonResponse
    {
        $query = HistorialSocialPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial social retrieved']);
    }

    public function showHistorialSocial(int $id): JsonResponse
    {
        $data = HistorialSocialPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial social retrieved']);
    }

    public function storeHistorialSocial(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_social' => 'required|string',
        ]);
        $user = Auth::user();
        $data = HistorialSocialPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_social' => $request->detalle_social,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateHistorialSocial(Request $request, int $id): JsonResponse
    {
        $data = HistorialSocialPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_social' => 'sometimes|string']);
        $data->update($request->only(['detalle_social']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyHistorialSocial(int $id): JsonResponse
    {
        $data = HistorialSocialPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL SEXUAL
    // ==========================================

    public function indexHistorialSexual(Request $request): JsonResponse
    {
        $query = HistorialSexualPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial sexual retrieved']);
    }

    public function showHistorialSexual(int $id): JsonResponse
    {
        $data = HistorialSexualPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial sexual retrieved']);
    }

    public function storeHistorialSexual(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_sexual' => 'required|string',
        ]);
        $user = Auth::user();
        $data = HistorialSexualPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_sexual' => $request->detalle_sexual,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateHistorialSexual(Request $request, int $id): JsonResponse
    {
        $data = HistorialSexualPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_sexual' => 'sometimes|string']);
        $data->update($request->only(['detalle_sexual']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyHistorialSexual(int $id): JsonResponse
    {
        $data = HistorialSexualPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PATOLOGIAS
    // ==========================================

    public function indexPatologias(Request $request): JsonResponse
    {
        $query = PatologiasPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Patologias retrieved']);
    }

    public function showPatologias(int $id): JsonResponse
    {
        $data = PatologiasPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Patologia retrieved']);
    }

    public function storePatologias(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_patologia' => 'required|string',
        ]);
        $user = Auth::user();
        $data = PatologiasPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_patologia' => $request->detalle_patologia,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updatePatologias(Request $request, int $id): JsonResponse
    {
        $data = PatologiasPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_patologia' => 'sometimes|string']);
        $data->update($request->only(['detalle_patologia']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyPatologias(int $id): JsonResponse
    {
        $data = PatologiasPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // EXAMEN ESTADO MENTAL
    // ==========================================

    public function indexExamenEstadoMental(Request $request): JsonResponse
    {
        $query = ExamenEstadoMentalPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Examen estado mental retrieved']);
    }

    public function showExamenEstadoMental(int $id): JsonResponse
    {
        $data = ExamenEstadoMentalPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Examen estado mental retrieved']);
    }

    public function storeExamenEstadoMental(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);
        $user = Auth::user();
        $data = ExamenEstadoMentalPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'apariencia' => $request->apariencia,
            'actitud' => $request->actitud,
            'juicio' => $request->juicio,
            'sueno' => $request->sueno,
            'apetito' => $request->apetito,
            'afectividad' => $request->afectividad,
            'orientacion' => $request->orientacion,
            'atencion' => $request->atencion,
            'memoria' => $request->memoria,
            'lenguaje' => $request->lenguaje,
            'pensamiento' => $request->pensamiento,
            'conducta_motora' => $request->conducta_motora,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateExamenEstadoMental(Request $request, int $id): JsonResponse
    {
        $data = ExamenEstadoMentalPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $fields = ['apariencia', 'actitud', 'juicio', 'sueno', 'apetito', 'afectividad', 'orientacion', 'atencion', 'memoria', 'lenguaje', 'pensamiento', 'conducta_motora'];
        $data->update($request->only($fields));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyExamenEstadoMental(int $id): JsonResponse
    {
        $data = ExamenEstadoMentalPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PRUEBAS APLICADAS
    // ==========================================

    public function indexPruebasAplicadas(Request $request): JsonResponse
    {
        $query = PruebasAplicadasPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Pruebas aplicadas retrieved']);
    }

    public function showPruebasAplicadas(int $id): JsonResponse
    {
        $data = PruebasAplicadasPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Prueba aplicada retrieved']);
    }

    public function storePruebasAplicadas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_prueba_aplicada' => 'required|string',
        ]);
        $user = Auth::user();
        $data = PruebasAplicadasPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_prueba_aplicada' => $request->detalle_prueba_aplicada,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updatePruebasAplicadas(Request $request, int $id): JsonResponse
    {
        $data = PruebasAplicadasPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_prueba_aplicada' => 'sometimes|string']);
        $data->update($request->only(['detalle_prueba_aplicada']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyPruebasAplicadas(int $id): JsonResponse
    {
        $data = PruebasAplicadasPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ANALISIS RESULTADOS
    // ==========================================

    public function indexAnalisisResultados(Request $request): JsonResponse
    {
        $query = AnalisisResultadosPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Analisis resultados retrieved']);
    }

    public function showAnalisisResultados(int $id): JsonResponse
    {
        $data = AnalisisResultadosPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Analisis resultado retrieved']);
    }

    public function storeAnalisisResultados(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_analisis_resultados' => 'required|string',
        ]);
        $user = Auth::user();
        $data = AnalisisResultadosPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_analisis_resultados' => $request->detalle_analisis_resultados,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateAnalisisResultados(Request $request, int $id): JsonResponse
    {
        $data = AnalisisResultadosPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_analisis_resultados' => 'sometimes|string']);
        $data->update($request->only(['detalle_analisis_resultados']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyAnalisisResultados(int $id): JsonResponse
    {
        $data = AnalisisResultadosPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // CONCLUSIONES
    // ==========================================

    public function indexConclusiones(Request $request): JsonResponse
    {
        $query = ConclusionesPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Conclusiones retrieved']);
    }

    public function showConclusiones(int $id): JsonResponse
    {
        $data = ConclusionesPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Conclusion retrieved']);
    }

    public function storeConclusiones(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_conclusion' => 'required|string',
        ]);
        $user = Auth::user();
        $data = ConclusionesPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_conclusion' => $request->detalle_conclusion,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateConclusiones(Request $request, int $id): JsonResponse
    {
        $data = ConclusionesPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_conclusion' => 'sometimes|string']);
        $data->update($request->only(['detalle_conclusion']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyConclusiones(int $id): JsonResponse
    {
        $data = ConclusionesPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // DIAGNOSTICO
    // ==========================================

    public function indexDiagnostico(Request $request): JsonResponse
    {
        $query = DiagnosticoPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Diagnosticos retrieved']);
    }

    public function showDiagnostico(int $id): JsonResponse
    {
        $data = DiagnosticoPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Diagnostico retrieved']);
    }

    public function storeDiagnostico(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_diagnostico' => 'required|string',
        ]);
        $user = Auth::user();
        $data = DiagnosticoPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_diagnostico' => $request->detalle_diagnostico,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateDiagnostico(Request $request, int $id): JsonResponse
    {
        $data = DiagnosticoPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_diagnostico' => 'sometimes|string']);
        $data->update($request->only(['detalle_diagnostico']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyDiagnostico(int $id): JsonResponse
    {
        $data = DiagnosticoPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PRONOSTICO
    // ==========================================

    public function indexPronostico(Request $request): JsonResponse
    {
        $query = PronosticoPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Pronosticos retrieved']);
    }

    public function showPronostico(int $id): JsonResponse
    {
        $data = PronosticoPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Pronostico retrieved']);
    }

    public function storePronostico(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_pronostico' => 'required|string',
        ]);
        $user = Auth::user();
        $data = PronosticoPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_pronostico' => $request->detalle_pronostico,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updatePronostico(Request $request, int $id): JsonResponse
    {
        $data = PronosticoPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_pronostico' => 'sometimes|string']);
        $data->update($request->only(['detalle_pronostico']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyPronostico(int $id): JsonResponse
    {
        $data = PronosticoPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // RECOMENDACION
    // ==========================================

    public function indexRecomendacion(Request $request): JsonResponse
    {
        $query = RecomendacionPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Recomendaciones retrieved']);
    }

    public function showRecomendacion(int $id): JsonResponse
    {
        $data = RecomendacionPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Recomendacion retrieved']);
    }

    public function storeRecomendacion(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_recomendacion' => 'required|string',
        ]);
        $user = Auth::user();
        $data = RecomendacionPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_recomendacion' => $request->detalle_recomendacion,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateRecomendacion(Request $request, int $id): JsonResponse
    {
        $data = RecomendacionPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_recomendacion' => 'sometimes|string']);
        $data->update($request->only(['detalle_recomendacion']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyRecomendacion(int $id): JsonResponse
    {
        $data = RecomendacionPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL EVOLUCION (sesiones)
    // ==========================================

    public function indexHistorialEvolucion(Request $request): JsonResponse
    {
        $query = HistorialEvolucionPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial evolucion retrieved']);
    }

    public function showHistorialEvolucion(int $id): JsonResponse
    {
        $data = HistorialEvolucionPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial evolucion retrieved']);
    }

    public function storeHistorialEvolucion(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'sesion_numero' => 'required|integer|min:1',
            'detalle_evolucion' => 'required|string',
        ]);
        $user = Auth::user();
        $data = HistorialEvolucionPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha ?? now()->toDateString(),
            'sesion_numero' => $request->sesion_numero,
            'detalle_evolucion' => $request->detalle_evolucion,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateHistorialEvolucion(Request $request, int $id): JsonResponse
    {
        $data = HistorialEvolucionPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'fecha' => 'sometimes|date',
            'sesion_numero' => 'sometimes|integer|min:1',
            'detalle_evolucion' => 'sometimes|string',
        ]);
        $data->update($request->only(['fecha', 'sesion_numero', 'detalle_evolucion']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyHistorialEvolucion(int $id): JsonResponse
    {
        $data = HistorialEvolucionPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PARTE DIARIO
    // ==========================================

    public function indexParteDiario(Request $request): JsonResponse
    {
        $query = ParteDiarioPsicologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Parte diario retrieved']);
    }

    public function showParteDiario(int $id): JsonResponse
    {
        $data = ParteDiarioPsicologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Parte diario retrieved']);
    }

    public function storeParteDiario(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'fecha' => 'required|date',
            'tipo_atencion' => 'required|in:primaria,secundaria,certificadomedico',
            'tipo_atencion2' => 'required|in:curativo,preventivo',
            'detalle_diagnostico' => 'required|string',
        ]);
        $user = Auth::user();
        $data = ParteDiarioPsicologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'tipo_atencion' => $request->tipo_atencion,
            'tipo_atencion2' => $request->tipo_atencion2,
            'detalle_diagnostico' => $request->detalle_diagnostico,
        ]);

        if (!empty($request->id_usuario_paciente)) {
            \App\Http\Controllers\Api\V1\CitasMedicasController::syncAutoCita($user->id, (int)$request->id_usuario_paciente, 'psicologo', $request->detalle_diagnostico ?? 'Consulta en Psicología');
        }

        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateParteDiario(Request $request, int $id): JsonResponse
    {
        $data = ParteDiarioPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'fecha' => 'sometimes|date',
            'tipo_atencion' => 'sometimes|in:primaria,secundaria,certificadomedico',
            'tipo_atencion2' => 'sometimes|in:curativo,preventivo',
            'detalle_diagnostico' => 'sometimes|string',
        ]);
        $data->update($request->only(['fecha', 'tipo_atencion', 'tipo_atencion2', 'detalle_diagnostico']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyParteDiario(int $id): JsonResponse
    {
        $data = ParteDiarioPsicologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }
}

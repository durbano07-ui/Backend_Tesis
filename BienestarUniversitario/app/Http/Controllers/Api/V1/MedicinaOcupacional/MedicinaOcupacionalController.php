<?php

namespace App\Http\Controllers\Api\V1\MedicinaOcupacional;

use App\Http\Controllers\Controller;
use App\Models\AccidentesLaboralesMedicoocupacional;
use App\Models\AusentismoLaboralMedicoocupacional;
use App\Models\CeseDeFuncionesMedicoocupacional;
use App\Models\EnfermedadesCatastroficasMedicoocupacional;
use App\Models\EnfermedadesNuevasMedicoocupacional;
use App\Models\GrupoFuncionariosDiscapacidadMedicoocupacional;
use App\Models\GrupoRiesgoPsicosocialMedicoocupacional;
use App\Models\GrupoTipoExamenMedicoocupacional;
use App\Models\GrupoVulnerableMedicoocupacional;
use App\Models\HistorialVacunasMedicoocupacional;
use App\Models\LineaRecetaMedicoocupacional;
use App\Models\ListadoCargoPersonalMedicoocupacional;
use App\Models\ListadoEmbarazadasMedicoocupacional;
use App\Models\ListadoTiposDiscapacidadesMedicoocupacional;
use App\Models\ListadoVacunasMedicoocupacional;
use App\Models\ListaVulnerabilidadesMedicoocupacional;
use App\Models\OrdenDeExamenMedicoocupacional;
use App\Models\OrdenDeExamenOtrosMedicoocupacional;
use App\Models\OrdenDeExamenTipoMedicoocupacional;
use App\Models\PersonalNuevoMedicoocupacional;
use App\Models\RecetaCieMedicoocupacional;
use App\Models\RecetaMedicoocupacional;
use App\Models\RecomendacionNofarmacologicaRecetaMedicoocupacional;
use App\Models\ReintegroUebMedicoocupacional;
use App\Models\SignoAlarmaRecetaMedicoocupacional;
use App\Models\TipoExamenMedicoocupacional;
use App\Models\UsuarioLugarDeTrabajo;
use App\Models\UsuarioTieneCargo;
use App\Models\UsuarioTipoSangre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Dompdf\Dompdf;
use Carbon\Carbon;

class MedicinaOcupacionalController extends Controller
{
    // ==========================================
    // CARGOS PERSONAL
    // ==========================================

    public function indexCargos(Request $request): JsonResponse
    {
        $query = ListadoCargoPersonalMedicoocupacional::query();
        $query->with('doctor');
        $cargos = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $cargos, 'message' => 'Cargos retrieved']);
    }

    public function showCargo(int $id): JsonResponse
    {
        $cargo = ListadoCargoPersonalMedicoocupacional::with('doctor')->find($id);
        if (!$cargo) return response()->json(['message' => 'Cargo not found'], 404);
        return response()->json(['data' => $cargo, 'message' => 'Cargo retrieved']);
    }

    public function storeCargo(Request $request): JsonResponse
    {
        $request->validate(['detalle_cargo' => 'required|string|max:255']);
        $user = Auth::user();
        $cargo = ListadoCargoPersonalMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'detalle_cargo' => $request->detalle_cargo,
        ]);
        return response()->json(['data' => $cargo, 'message' => 'Cargo created'], 201);
    }

    public function updateCargo(Request $request, int $id): JsonResponse
    {
        $cargo = ListadoCargoPersonalMedicoocupacional::find($id);
        if (!$cargo) return response()->json(['message' => 'Cargo not found'], 404);
        $request->validate(['detalle_cargo' => 'required|string|max:255']);
        $cargo->update(['detalle_cargo' => $request->detalle_cargo]);
        return response()->json(['data' => $cargo, 'message' => 'Cargo updated']);
    }

    public function destroyCargo(int $id): JsonResponse
    {
        $cargo = ListadoCargoPersonalMedicoocupacional::find($id);
        if (!$cargo) return response()->json(['message' => 'Cargo not found'], 404);
        $cargo->delete();
        return response()->json(['message' => 'Cargo deleted']);
    }

    // ==========================================
    // USUARIO TIENE CARGO
    // ==========================================

    public function indexUsuarioTieneCargo(Request $request): JsonResponse
    {
        $query = UsuarioTieneCargo::query()->with(['doctor', 'paciente', 'cargo']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        if ($request->has('id_usuario_doctor')) {
            $query->where('id_usuario_doctor', $request->id_usuario_doctor);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Usuario tiene cargo retrieved']);
    }

    public function showUsuarioTieneCargo(int $id): JsonResponse
    {
        $data = UsuarioTieneCargo::with(['doctor', 'paciente', 'cargo'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Usuario tiene cargo retrieved']);
    }

    public function storeUsuarioTieneCargo(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'id_cargo' => 'required|integer|exists:listado_cargo_personal_medicoocupacional,id',
        ]);
        $user = Auth::user();
        $data = UsuarioTieneCargo::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_cargo' => $request->id_cargo,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'cargo']), 'message' => 'Created'], 201);
    }

    public function updateUsuarioTieneCargo(Request $request, int $id): JsonResponse
    {
        $data = UsuarioTieneCargo::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'id_cargo' => 'sometimes|integer|exists:listado_cargo_personal_medicoocupacional,id',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'id_cargo']));
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'cargo']), 'message' => 'Updated']);
    }

    public function destroyUsuarioTieneCargo(int $id): JsonResponse
    {
        $data = UsuarioTieneCargo::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // USUARIO LUGAR DE TRABAJO
    // ==========================================

    public function indexUsuarioLugarDeTrabajo(Request $request): JsonResponse
    {
        $query = UsuarioLugarDeTrabajo::query()->with(['usuario', 'lugarTrabajo']);
        
        $userId = $request->id_usuario;
        if (!$userId && $request->user()) {
            if (!$request->user()->hasRole('administrador')) {
                $userId = $request->user()->id;
            }
        }
        
        if ($userId) {
            $query->where('id_usuario', $userId);
        }

        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Usuario lugar de trabajo retrieved']);
    }

    public function showUsuarioLugarDeTrabajo(int $id): JsonResponse
    {
        $data = UsuarioLugarDeTrabajo::with(['usuario', 'lugarTrabajo'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Usuario lugar de trabajo retrieved']);
    }

    public function storeUsuarioLugarDeTrabajo(Request $request): JsonResponse
    {
        $userId = $request->id_usuario ?? $request->user()?->id;
        $request->merge(['id_usuario' => $userId]);

        $request->validate([
            'id_usuario' => 'required|integer|exists:users,id',
            'id_lugar_trabajo' => 'required',
        ]);

        $existing = UsuarioLugarDeTrabajo::where('id_usuario', $userId)
            ->where('id_lugar_trabajo', $request->id_lugar_trabajo)
            ->first();

        if ($existing) {
            return response()->json([
                'data' => $existing->load(['usuario', 'lugarTrabajo']),
                'message' => 'Campus ya asignado'
            ], 200);
        }

        $data = UsuarioLugarDeTrabajo::create([
            'id_usuario' => $userId,
            'id_lugar_trabajo' => $request->id_lugar_trabajo,
        ]);

        return response()->json(['data' => $data->load(['usuario', 'lugarTrabajo']), 'message' => 'Created'], 201);
    }

    public function updateUsuarioLugarDeTrabajo(Request $request, int $id): JsonResponse
    {
        $data = UsuarioLugarDeTrabajo::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario' => 'sometimes|integer|exists:users,id',
            'id_lugar_trabajo' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only(['id_usuario', 'id_lugar_trabajo']));
        return response()->json(['data' => $data->load('usuario'), 'message' => 'Updated']);
    }

    public function destroyUsuarioLugarDeTrabajo(int $id): JsonResponse
    {
        $data = UsuarioLugarDeTrabajo::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // GRUPO TIPO EXAMEN
    // ==========================================

    public function indexGrupoExamen(Request $request): JsonResponse
    {
        $query = GrupoTipoExamenMedicoocupacional::query()->with('doctor');
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Grupo tipo examen retrieved']);
    }

    public function showGrupoExamen(int $id): JsonResponse
    {
        $data = GrupoTipoExamenMedicoocupacional::with(['doctor', 'tiposExamen'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Grupo tipo examen retrieved']);
    }

    public function storeGrupoExamen(Request $request): JsonResponse
    {
        $request->validate(['detalle_grupo' => 'required|string|max:255']);
        $user = Auth::user();
        $data = GrupoTipoExamenMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'detalle_grupo' => $request->detalle_grupo,
            'activo' => $request->activo ?? true,
        ]);
        return response()->json(['data' => $data, 'message' => 'Created'], 201);
    }

    public function updateGrupoExamen(Request $request, int $id): JsonResponse
    {
        $data = GrupoTipoExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_grupo' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_grupo', 'activo']));
        return response()->json(['data' => $data, 'message' => 'Updated']);
    }

    public function destroyGrupoExamen(int $id): JsonResponse
    {
        $data = GrupoTipoExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // TIPO EXAMEN
    // ==========================================

    public function indexTipoExamen(Request $request): JsonResponse
    {
        $query = TipoExamenMedicoocupacional::query()->with(['doctor', 'grupo']);
        if ($request->has('id_grupo_tipo_examen')) {
            $query->where('id_grupo_tipo_examen', $request->id_grupo_tipo_examen);
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Tipo examen retrieved']);
    }

    public function showTipoExamen(int $id): JsonResponse
    {
        $data = TipoExamenMedicoocupacional::with(['doctor', 'grupo'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Tipo examen retrieved']);
    }

    public function storeTipoExamen(Request $request): JsonResponse
    {
        $request->validate([
            'id_grupo_tipo_examen' => 'required|integer|exists:grupo_tipo_examen_medicoocupacional,id',
            'detalle_tipo' => 'required|string|max:255',
        ]);
        $user = Auth::user();
        $data = TipoExamenMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_grupo_tipo_examen' => $request->id_grupo_tipo_examen,
            'detalle_tipo' => $request->detalle_tipo,
            'activo' => $request->activo ?? true,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'grupo']), 'message' => 'Created'], 201);
    }

    public function updateTipoExamen(Request $request, int $id): JsonResponse
    {
        $data = TipoExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_grupo_tipo_examen' => 'sometimes|integer|exists:grupo_tipo_examen_medicoocupacional,id',
            'detalle_tipo' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only(['id_grupo_tipo_examen', 'detalle_tipo', 'activo']));
        return response()->json(['data' => $data->load(['doctor', 'grupo']), 'message' => 'Updated']);
    }

    public function destroyTipoExamen(int $id): JsonResponse
    {
        $data = TipoExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ORDEN DE EXAMEN
    // ==========================================

    public function getCatalogoExamenes(): JsonResponse
    {
        $grupos = GrupoTipoExamenMedicoocupacional::with(['tiposExamen' => function ($q) {
            $q->where('activo', true)->orderBy('id', 'asc');
        }])->where('activo', true)->orderBy('id', 'asc')->get();

        $col1Nombres = ['HEMATOLOGÍA', 'PERFIL DE ANEMIA', 'PERFIL LIPÍDICO', 'ORINA'];
        $col2Nombres = ['BIOQUÍMICOS', 'ENZIMAS', 'ELECTROLITOS', 'HECES'];
        $col3Nombres = ['HORMONAS', 'EXUDADO VAGINAL/URETRAL'];

        $formatGrupo = function ($g) {
            return [
                'id' => $g->id,
                'nombre' => $g->detalle_grupo,
                'items' => $g->tiposExamen->map(fn($t) => [
                    'id' => $t->id,
                    'nombre' => $t->detalle_tipo,
                ])->values(),
            ];
        };

        $columna1 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col1Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col1Nombres))
            ->map($formatGrupo)->values();

        $columna2 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col2Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col2Nombres))
            ->map($formatGrupo)->values();

        $columna3 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col3Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col3Nombres))
            ->map($formatGrupo)->values();

        return response()->json([
            'columna1' => $columna1,
            'columna2' => $columna2,
            'columna3' => $columna3,
            'todos_grupos' => $grupos->map($formatGrupo)->values(),
        ]);
    }

    public function indexOrdenExamen(Request $request): JsonResponse
    {
        $query = OrdenDeExamenMedicoocupacional::query()
            ->with([
                'doctor.datosIdentificacion',
                'paciente.datosIdentificacion',
                'tiposExamen.tipoExamen.grupo',
                'otros'
            ]);

        if ($request->filled('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }

        if ($request->filled('estado') && $request->estado !== 'all') {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('paciente', function ($pq) use ($term) {
                    $pq->where('name', 'like', "%{$term}%")
                       ->orWhere('email', 'like', "%{$term}%");
                })->orWhereHas('paciente.datosIdentificacion', function ($dq) use ($term) {
                    $dq->where('primer_nombre', 'like', "%{$term}%")
                       ->orWhere('apellido_paterno', 'like', "%{$term}%")
                       ->orWhere('numero_cedula', 'like', "%{$term}%");
                });
            });
        }

        $data = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Orden de examen retrieved']);
    }

    public function showOrdenExamen(int $id): JsonResponse
    {
        $data = OrdenDeExamenMedicoocupacional::with([
            'doctor.datosIdentificacion',
            'paciente.datosIdentificacion',
            'tiposExamen.tipoExamen.grupo',
            'otros'
        ])->find($id);

        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Orden de examen retrieved']);
    }

    public function storeOrdenExamen(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string',
            'tipos_examen' => 'nullable|array',
            'tipos_examen.*' => 'integer|exists:tipo_examen_medicoocupacional,id',
            'otros_examenes' => 'nullable|array',
            'otros_examenes.*' => 'string|max:255',
        ]);

        $user = Auth::user();

        $orden = DB::transaction(function () use ($request, $user) {
            $orden = OrdenDeExamenMedicoocupacional::create([
                'id_usuario_doctor' => $user->id,
                'id_usuario_paciente' => $request->id_usuario_paciente,
                'fecha' => $request->fecha,
                'observaciones' => $request->observaciones,
                'estado' => 'Pendiente',
            ]);

            if ($request->has('tipos_examen') && is_array($request->tipos_examen)) {
                $inserts = [];
                $now = now();
                foreach (array_unique($request->tipos_examen) as $tipoId) {
                    $inserts[] = [
                        'id_orden_examen' => $orden->id,
                        'id_tipo_examen' => $tipoId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($inserts)) {
                    OrdenDeExamenTipoMedicoocupacional::insert($inserts);
                }
            }

            if ($request->has('otros_examenes') && is_array($request->otros_examenes)) {
                $otrosInserts = [];
                $now = now();
                foreach ($request->otros_examenes as $otro) {
                    $otroTrim = trim($otro);
                    if (!empty($otroTrim)) {
                        $otrosInserts[] = [
                            'id_orden_examen' => $orden->id,
                            'detalle_otro_examen' => $otroTrim,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                if (!empty($otrosInserts)) {
                    OrdenDeExamenOtrosMedicoocupacional::insert($otrosInserts);
                }
            }

            return $orden;
        });

        $orden->load([
            'doctor.datosIdentificacion',
            'paciente.datosIdentificacion',
            'tiposExamen.tipoExamen.grupo',
            'otros'
        ]);

        return response()->json([
            'data' => $orden,
            'message' => 'Orden de examen emitida con éxito'
        ], 201);
    }

    public function updateEstadoOrdenExamen(int $id, Request $request): JsonResponse
    {
        $orden = OrdenDeExamenMedicoocupacional::find($id);
        if (!$orden) return response()->json(['message' => 'Not found'], 404);

        $request->validate([
            'estado' => 'required|string|in:Pendiente,Realizado',
        ]);

        $orden->update(['estado' => $request->estado]);
        return response()->json(['data' => $orden, 'message' => 'Estado actualizado']);
    }

    public function updateOrdenExamen(Request $request, int $id): JsonResponse
    {
        $data = OrdenDeExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'fecha' => 'sometimes|date',
            'observaciones' => 'sometimes|nullable|string',
            'estado' => 'sometimes|string',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'fecha', 'observaciones', 'estado']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyOrdenExamen(int $id): JsonResponse
    {
        $data = OrdenDeExamenMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function descargarPdfOrdenExamen(int $id)
    {
        $orden = OrdenDeExamenMedicoocupacional::with([
            'doctor.datosIdentificacion',
            'paciente.datosIdentificacion',
            'tiposExamen.tipoExamen',
            'otros'
        ])->find($id);

        if (!$orden) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        // Logo base64
        $logoPath = public_path('images/ueb.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // Paciente info
        $paciente = $orden->paciente;
        $pIdent = $paciente?->datosIdentificacion;
        $pacienteNombre = $pIdent
            ? trim("{$pIdent->primer_nombre} {$pIdent->segundo_nombre} {$pIdent->apellido_paterno} {$pIdent->apellido_materno}")
            : ($paciente?->name ?? '—');
        $pacienteCedula = $pIdent?->numero_cedula ?? '—';
        $pacienteEdad = $pIdent?->fecha_nacimiento
            ? Carbon::parse($pIdent->fecha_nacimiento)->age
            : null;

        // Doctor info
        $doctor = $orden->doctor;
        $dIdent = $doctor?->datosIdentificacion;
        $doctorNombre = $dIdent
            ? trim("{$dIdent->primer_nombre} {$dIdent->segundo_nombre} {$dIdent->apellido_paterno} {$dIdent->apellido_materno}")
            : ($doctor?->name ?? 'Dr. Médico Ocupacional');

        // Selected exam IDs
        $selectedIds = $orden->tiposExamen->pluck('id_tipo_examen')->toArray();

        // Catalog categories for 3 columns
        $grupos = GrupoTipoExamenMedicoocupacional::with(['tiposExamen' => function ($q) {
            $q->where('activo', true)->orderBy('id', 'asc');
        }])->where('activo', true)->orderBy('id', 'asc')->get();

        $col1Nombres = ['HEMATOLOGÍA', 'PERFIL DE ANEMIA', 'PERFIL LIPÍDICO', 'ORINA'];
        $col2Nombres = ['BIOQUÍMICOS', 'ENZIMAS', 'ELECTROLITOS', 'HECES'];
        $col3Nombres = ['HORMONAS', 'EXUDADO VAGINAL/URETRAL'];

        $formatGrupo = function ($g) {
            return [
                'id' => $g->id,
                'nombre' => $g->detalle_grupo,
                'items' => $g->tiposExamen->map(fn($t) => [
                    'id' => $t->id,
                    'nombre' => $t->detalle_tipo,
                ])->toArray(),
            ];
        };

        $columna1 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col1Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col1Nombres))
            ->map($formatGrupo)->values()->toArray();

        $columna2 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col2Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col2Nombres))
            ->map($formatGrupo)->values()->toArray();

        $columna3 = $grupos->filter(fn($g) => in_array($g->detalle_grupo, $col3Nombres))
            ->sortBy(fn($g) => array_search($g->detalle_grupo, $col3Nombres))
            ->map($formatGrupo)->values()->toArray();

        $otrosExamenes = $orden->otros->pluck('detalle_otro_examen')->toArray();

        $html = View::make('pdf.orden_examen_ocupacional', [
            'orden' => $orden,
            'logoBase64' => $logoBase64,
            'pacienteNombre' => $pacienteNombre,
            'pacienteCedula' => $pacienteCedula,
            'pacienteEdad' => $pacienteEdad,
            'doctorNombre' => $doctorNombre,
            'fecha' => $orden->fecha,
            'selectedIds' => $selectedIds,
            'columna1' => $columna1,
            'columna2' => $columna2,
            'columna3' => $columna3,
            'otrosExamenes' => $otrosExamenes,
            'observaciones' => $orden->observaciones,
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "Orden_Examenes_UEB_{$orden->id}_{$pacienteCedula}.pdf";

        return response()->streamDownload(
            function () use ($dompdf) {
                echo $dompdf->output();
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    // ==========================================
    // ORDEN EXAMEN TIPO
    // ==========================================

    public function indexOrdenExamenTipo(Request $request): JsonResponse
    {
        $query = OrdenDeExamenTipoMedicoocupacional::query()->with(['ordenExamen', 'tipoExamen']);
        if ($request->has('id_orden_examen')) {
            $query->where('id_orden_examen', $request->id_orden_examen);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Orden examen tipo retrieved']);
    }

    public function storeOrdenExamenTipo(Request $request): JsonResponse
    {
        $request->validate([
            'id_orden_examen' => 'required|integer|exists:orden_de_examen_medicoocupacional,id',
            'id_tipo_examen' => 'required|integer|exists:tipo_examen_medicoocupacional,id',
        ]);
        $data = OrdenDeExamenTipoMedicoocupacional::create($request->only(['id_orden_examen', 'id_tipo_examen']));
        return response()->json(['data' => $data->load(['ordenExamen', 'tipoExamen']), 'message' => 'Created'], 201);
    }

    public function destroyOrdenExamenTipo(int $id): JsonResponse
    {
        $data = OrdenDeExamenTipoMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ORDEN EXAMEN OTROS
    // ==========================================

    public function indexOrdenExamenOtros(Request $request): JsonResponse
    {
        $query = OrdenDeExamenOtrosMedicoocupacional::query()->with('ordenExamen');
        if ($request->has('id_orden_examen')) {
            $query->where('id_orden_examen', $request->id_orden_examen);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Orden examen otros retrieved']);
    }

    public function storeOrdenExamenOtros(Request $request): JsonResponse
    {
        $request->validate([
            'id_orden_examen' => 'required|integer|exists:orden_de_examen_medicoocupacional,id',
            'detalle_otro_examen' => 'required|string|max:255',
        ]);
        $data = OrdenDeExamenOtrosMedicoocupacional::create($request->only(['id_orden_examen', 'detalle_otro_examen']));
        return response()->json(['data' => $data->load('ordenExamen'), 'message' => 'Created'], 201);
    }

    public function updateOrdenExamenOtros(Request $request, int $id): JsonResponse
    {
        $data = OrdenDeExamenOtrosMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_otro_examen' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_otro_examen']));
        return response()->json(['data' => $data->load('ordenExamen'), 'message' => 'Updated']);
    }

    public function destroyOrdenExamenOtros(int $id): JsonResponse
    {
        $data = OrdenDeExamenOtrosMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // RECETA MEDICOOCUPACIONAL
    // ==========================================

    public function indexReceta(Request $request): JsonResponse
    {
        $query = RecetaMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Recetas retrieved']);
    }

    public function showReceta(int $id): JsonResponse
    {
        $data = RecetaMedicoocupacional::with(['doctor', 'paciente', 'cie10s', 'lineas', 'signosAlarma', 'recomendaciones'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Receta retrieved']);
    }

    public function storeReceta(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'fecha' => 'required|date',
            'estado_enfermedad' => 'required|in:agudo,cronico',
        ]);
        $user = Auth::user();
        $data = RecetaMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'estado_enfermedad' => $request->estado_enfermedad,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateReceta(Request $request, int $id): JsonResponse
    {
        $data = RecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'fecha' => 'sometimes|date',
            'estado_enfermedad' => 'sometimes|in:agudo,cronico',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'fecha', 'estado_enfermedad']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyReceta(int $id): JsonResponse
    {
        $data = RecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // RECETA CIE10
    // ==========================================

    public function indexRecetaCie(Request $request): JsonResponse
    {
        $query = RecetaCieMedicoocupacional::query()->with('receta');
        if ($request->has('id_receta')) {
            $query->where('id_receta', $request->id_receta);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Receta cie10 retrieved']);
    }

    public function storeRecetaCie(Request $request): JsonResponse
    {
        $request->validate([
            'id_receta' => 'required|integer|exists:receta_medicoocupacional,id',
            'detalle_cie10' => 'required|string|max:255',
        ]);
        $data = RecetaCieMedicoocupacional::create($request->only(['id_receta', 'detalle_cie10']));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Created'], 201);
    }

    public function updateRecetaCie(Request $request, int $id): JsonResponse
    {
        $data = RecetaCieMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_cie10' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_cie10']));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Updated']);
    }

    public function destroyRecetaCie(int $id): JsonResponse
    {
        $data = RecetaCieMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // LINEA RECETA
    // ==========================================

    public function indexLineaReceta(Request $request): JsonResponse
    {
        $query = LineaRecetaMedicoocupacional::query()->with('receta');
        if ($request->has('id_receta')) {
            $query->where('id_receta', $request->id_receta);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Linea receta retrieved']);
    }

    public function storeLineaReceta(Request $request): JsonResponse
    {
        $request->validate([
            'id_receta' => 'required|integer|exists:receta_medicoocupacional,id',
            'detalle_medicamento' => 'required|string|max:255',
            'detalle_dosis' => 'required|string|max:255',
            'frecuencia_horas' => 'required|integer|min:1',
            'duracion_tratamiento_dias' => 'required|integer|min:1',
            'detalle_via_administracion' => 'required|string|max:255',
            'detalle_numero_y_letras' => 'required|string|max:255',
        ]);
        $data = LineaRecetaMedicoocupacional::create($request->only([
            'id_receta', 'detalle_medicamento', 'detalle_dosis', 'frecuencia_horas',
            'duracion_tratamiento_dias', 'detalle_via_administracion', 'detalle_numero_y_letras'
        ]));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Created'], 201);
    }

    public function updateLineaReceta(Request $request, int $id): JsonResponse
    {
        $data = LineaRecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'detalle_medicamento' => 'sometimes|string|max:255',
            'detalle_dosis' => 'sometimes|string|max:255',
            'frecuencia_horas' => 'sometimes|integer|min:1',
            'duracion_tratamiento_dias' => 'sometimes|integer|min:1',
            'detalle_via_administracion' => 'sometimes|string|max:255',
            'detalle_numero_y_letras' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only([
            'detalle_medicamento', 'detalle_dosis', 'frecuencia_horas',
            'duracion_tratamiento_dias', 'detalle_via_administracion', 'detalle_numero_y_letras'
        ]));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Updated']);
    }

    public function destroyLineaReceta(int $id): JsonResponse
    {
        $data = LineaRecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // SIGNO ALARMA RECETA
    // ==========================================

    public function indexSignoAlarmaReceta(Request $request): JsonResponse
    {
        $query = SignoAlarmaRecetaMedicoocupacional::query()->with('receta');
        if ($request->has('id_receta')) {
            $query->where('id_receta', $request->id_receta);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Signo alarma retrieved']);
    }

    public function storeSignoAlarmaReceta(Request $request): JsonResponse
    {
        $request->validate([
            'id_receta' => 'required|integer|exists:receta_medicoocupacional,id',
            'detalle_alarma' => 'required|string|max:255',
        ]);
        $data = SignoAlarmaRecetaMedicoocupacional::create($request->only(['id_receta', 'detalle_alarma']));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Created'], 201);
    }

    public function destroySignoAlarmaReceta(int $id): JsonResponse
    {
        $data = SignoAlarmaRecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // RECOMENDACIONES NOFARMACOLOGICAS
    // ==========================================

    public function indexRecomendacionReceta(Request $request): JsonResponse
    {
        $query = RecomendacionNofarmacologicaRecetaMedicoocupacional::query()->with('receta');
        if ($request->has('id_receta')) {
            $query->where('id_receta', $request->id_receta);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Recomendaciones retrieved']);
    }

    public function storeRecomendacionReceta(Request $request): JsonResponse
    {
        $request->validate([
            'id_receta' => 'required|integer|exists:receta_medicoocupacional,id',
            'detalle_recomendacion' => 'required|string|max:255',
        ]);
        $data = RecomendacionNofarmacologicaRecetaMedicoocupacional::create($request->only(['id_receta', 'detalle_recomendacion']));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Created'], 201);
    }

    public function updateRecomendacionReceta(Request $request, int $id): JsonResponse
    {
        $data = RecomendacionNofarmacologicaRecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_recomendacion' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_recomendacion']));
        return response()->json(['data' => $data->load('receta'), 'message' => 'Updated']);
    }

    public function destroyRecomendacionReceta(int $id): JsonResponse
    {
        $data = RecomendacionNofarmacologicaRecetaMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // LISTADO VACUNAS
    // ==========================================

    public function indexListadoVacunas(Request $request): JsonResponse
    {
        $query = ListadoVacunasMedicoocupacional::query()->with('doctor');
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Listado vacunas retrieved']);
    }

    public function showListadoVacunas(int $id): JsonResponse
    {
        $data = ListadoVacunasMedicoocupacional::with('doctor')->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Listado vacuna retrieved']);
    }

    public function storeListadoVacunas(Request $request): JsonResponse
    {
        $request->validate(['detalle_vacuna' => 'required|string|max:255']);
        $user = Auth::user();
        $data = ListadoVacunasMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'detalle_vacuna' => $request->detalle_vacuna,
        ]);
        return response()->json(['data' => $data, 'message' => 'Created'], 201);
    }

    public function updateListadoVacunas(Request $request, int $id): JsonResponse
    {
        $data = ListadoVacunasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_vacuna' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_vacuna']));
        return response()->json(['data' => $data, 'message' => 'Updated']);
    }

    public function destroyListadoVacunas(int $id): JsonResponse
    {
        $data = ListadoVacunasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL VACUNAS
    // ==========================================

    public function indexHistorialVacunas(Request $request): JsonResponse
    {
        $query = HistorialVacunasMedicoocupacional::query()->with(['doctor', 'paciente', 'vacuna']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial vacunas retrieved']);
    }

    public function showHistorialVacunas(int $id): JsonResponse
    {
        $data = HistorialVacunasMedicoocupacional::with(['doctor', 'paciente', 'vacuna'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial vacuna retrieved']);
    }

    public function storeHistorialVacunas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'id_listado_vacuna' => 'required|integer|exists:listado_vacunas_medicoocupacional,id',
            'dosis' => 'required|string|max:255',
            'fecha' => 'required|date',
        ]);
        $user = Auth::user();
        $data = HistorialVacunasMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_listado_vacuna' => $request->id_listado_vacuna,
            'dosis' => $request->dosis,
            'fecha' => $request->fecha,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'vacuna']), 'message' => 'Created'], 201);
    }

    public function updateHistorialVacunas(Request $request, int $id): JsonResponse
    {
        $data = HistorialVacunasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'id_listado_vacuna' => 'sometimes|integer|exists:listado_vacunas_medicoocupacional,id',
            'dosis' => 'sometimes|string|max:255',
            'fecha' => 'sometimes|date',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'id_listado_vacuna', 'dosis', 'fecha']));
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'vacuna']), 'message' => 'Updated']);
    }

    public function destroyHistorialVacunas(int $id): JsonResponse
    {
        $data = HistorialVacunasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // REINTEGRO UEB
    // ==========================================

    public function indexReintegroUeb(Request $request): JsonResponse
    {
        $query = ReintegroUebMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_salida', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Reintegros retrieved']);
    }

    public function showReintegroUeb(int $id): JsonResponse
    {
        $data = ReintegroUebMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Reintegro retrieved']);
    }

    public function storeReintegroUeb(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_motivo_salida' => 'required|string|max:255',
            'fecha_salida' => 'required|date',
            'fecha_reintegro' => 'required|date|after_or_equal:fecha_salida',
        ]);
        $user = Auth::user();
        $data = ReintegroUebMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo_salida' => $request->detalle_motivo_salida,
            'fecha_salida' => $request->fecha_salida,
            'fecha_reintegro' => $request->fecha_reintegro,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateReintegroUeb(Request $request, int $id): JsonResponse
    {
        $data = ReintegroUebMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_motivo_salida' => 'sometimes|string|max:255',
            'fecha_salida' => 'sometimes|date',
            'fecha_reintegro' => 'sometimes|date',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_motivo_salida', 'fecha_salida', 'fecha_reintegro']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyReintegroUeb(int $id): JsonResponse
    {
        $data = ReintegroUebMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // PERSONAL NUEVO
    // ==========================================

    public function indexPersonalNuevo(Request $request): JsonResponse
    {
        $query = PersonalNuevoMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_ingreso', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Personal nuevo retrieved']);
    }

    public function showPersonalNuevo(int $id): JsonResponse
    {
        $data = PersonalNuevoMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Personal nuevo retrieved']);
    }

    public function storePersonalNuevo(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'fecha_ingreso' => 'required|date',
        ]);
        $user = Auth::user();
        $data = PersonalNuevoMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha_ingreso' => $request->fecha_ingreso,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updatePersonalNuevo(Request $request, int $id): JsonResponse
    {
        $data = PersonalNuevoMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'fecha_ingreso' => 'sometimes|date',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'fecha_ingreso']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyPersonalNuevo(int $id): JsonResponse
    {
        $data = PersonalNuevoMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // CESE DE FUNCIONES
    // ==========================================

    public function indexCeseFunciones(Request $request): JsonResponse
    {
        $query = CeseDeFuncionesMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_salida', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Cese de funciones retrieved']);
    }

    public function showCeseFunciones(int $id): JsonResponse
    {
        $data = CeseDeFuncionesMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Cese de funciones retrieved']);
    }

    public function storeCeseFunciones(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_motivo_salida' => 'required|string|max:255',
            'fecha_salida' => 'required|date',
        ]);
        $user = Auth::user();
        $data = CeseDeFuncionesMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo_salida' => $request->detalle_motivo_salida,
            'fecha_salida' => $request->fecha_salida,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateCeseFunciones(Request $request, int $id): JsonResponse
    {
        $data = CeseDeFuncionesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_motivo_salida' => 'sometimes|string|max:255',
            'fecha_salida' => 'sometimes|date',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_motivo_salida', 'fecha_salida']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyCeseFunciones(int $id): JsonResponse
    {
        $data = CeseDeFuncionesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // LISTA VULNERABILIDADES
    // ==========================================

    public function indexListaVulnerabilidades(Request $request): JsonResponse
    {
        $query = ListaVulnerabilidadesMedicoocupacional::query()->with('doctor');
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Lista vulnerabilidades retrieved']);
    }

    public function showListaVulnerabilidades(int $id): JsonResponse
    {
        $data = ListaVulnerabilidadesMedicoocupacional::with('doctor')->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Lista vulnerabilidad retrieved']);
    }

    public function storeListaVulnerabilidades(Request $request): JsonResponse
    {
        $request->validate(['detalle_vulnerabilidad' => 'required|string|max:255']);
        $user = Auth::user();
        $data = ListaVulnerabilidadesMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'detalle_vulnerabilidad' => $request->detalle_vulnerabilidad,
        ]);
        return response()->json(['data' => $data, 'message' => 'Created'], 201);
    }

    public function updateListaVulnerabilidades(Request $request, int $id): JsonResponse
    {
        $data = ListaVulnerabilidadesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_vulnerabilidad' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_vulnerabilidad']));
        return response()->json(['data' => $data, 'message' => 'Updated']);
    }

    public function destroyListaVulnerabilidades(int $id): JsonResponse
    {
        $data = ListaVulnerabilidadesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // GRUPO VULNERABLE
    // ==========================================

    public function indexGrupoVulnerable(Request $request): JsonResponse
    {
        $query = GrupoVulnerableMedicoocupacional::query()->with(['doctor', 'paciente', 'vulnerabilidad']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Grupo vulnerable retrieved']);
    }

    public function showGrupoVulnerable(int $id): JsonResponse
    {
        $data = GrupoVulnerableMedicoocupacional::with(['doctor', 'paciente', 'vulnerabilidad'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Grupo vulnerable retrieved']);
    }

    public function storeGrupoVulnerable(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'id_lista_vulnerabilidad' => 'required|integer|exists:lista_vulnerabilidades_medicoocupacional,id',
            'detalle_otra_enfermedad' => 'nullable|string|max:255',
        ]);
        $user = Auth::user();
        $data = GrupoVulnerableMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_lista_vulnerabilidad' => $request->id_lista_vulnerabilidad,
            'detalle_otra_enfermedad' => $request->detalle_otra_enfermedad,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'vulnerabilidad']), 'message' => 'Created'], 201);
    }

    public function updateGrupoVulnerable(Request $request, int $id): JsonResponse
    {
        $data = GrupoVulnerableMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'id_lista_vulnerabilidad' => 'sometimes|integer|exists:lista_vulnerabilidades_medicoocupacional,id',
            'detalle_otra_enfermedad' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'id_lista_vulnerabilidad', 'detalle_otra_enfermedad']));
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'vulnerabilidad']), 'message' => 'Updated']);
    }

    public function destroyGrupoVulnerable(int $id): JsonResponse
    {
        $data = GrupoVulnerableMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // GRUPO RIESGO PSICOSOCIAL
    // ==========================================

    public function indexGrupoRiesgoPsicosocial(Request $request): JsonResponse
    {
        $query = GrupoRiesgoPsicosocialMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Grupo riesgo psicosocial retrieved']);
    }

    public function showGrupoRiesgoPsicosocial(int $id): JsonResponse
    {
        $data = GrupoRiesgoPsicosocialMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Grupo riesgo psicosocial retrieved']);
    }

    public function storeGrupoRiesgoPsicosocial(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_diagnostico' => 'required|string|max:255',
        ]);
        $user = Auth::user();
        $data = GrupoRiesgoPsicosocialMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_diagnostico' => $request->detalle_diagnostico,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateGrupoRiesgoPsicosocial(Request $request, int $id): JsonResponse
    {
        $data = GrupoRiesgoPsicosocialMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_diagnostico' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_diagnostico']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyGrupoRiesgoPsicosocial(int $id): JsonResponse
    {
        $data = GrupoRiesgoPsicosocialMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // LISTADO TIPOS DISCAPACIDADES
    // ==========================================

    public function indexListadoDiscapacidades(Request $request): JsonResponse
    {
        $query = ListadoTiposDiscapacidadesMedicoocupacional::query()->with('doctor');
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Listado discapacidades retrieved']);
    }

    public function showListadoDiscapacidades(int $id): JsonResponse
    {
        $data = ListadoTiposDiscapacidadesMedicoocupacional::with('doctor')->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Listado discapacidad retrieved']);
    }

    public function storeListadoDiscapacidades(Request $request): JsonResponse
    {
        $request->validate(['detalle_tipo' => 'required|string|max:255']);
        $user = Auth::user();
        $data = ListadoTiposDiscapacidadesMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'detalle_tipo' => $request->detalle_tipo,
        ]);
        return response()->json(['data' => $data, 'message' => 'Created'], 201);
    }

    public function updateListadoDiscapacidades(Request $request, int $id): JsonResponse
    {
        $data = ListadoTiposDiscapacidadesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['detalle_tipo' => 'sometimes|string|max:255']);
        $data->update($request->only(['detalle_tipo']));
        return response()->json(['data' => $data, 'message' => 'Updated']);
    }

    public function destroyListadoDiscapacidades(int $id): JsonResponse
    {
        $data = ListadoTiposDiscapacidadesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // GRUPO FUNCIONARIOS DISCAPACIDAD
    // ==========================================

    public function indexFuncionariosDiscapacidad(Request $request): JsonResponse
    {
        $query = GrupoFuncionariosDiscapacidadMedicoocupacional::query()->with(['doctor', 'paciente', 'tipoDiscapacidad']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Funcionarios discapacidad retrieved']);
    }

    public function showFuncionariosDiscapacidad(int $id): JsonResponse
    {
        $data = GrupoFuncionariosDiscapacidadMedicoocupacional::with(['doctor', 'paciente', 'tipoDiscapacidad'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Funcionario discapacidad retrieved']);
    }

    public function storeFuncionariosDiscapacidad(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'id_tipo_discapacidad' => 'required|integer|exists:listado_tipos_discapacidades_medicoocupacional,id',
            'porcentaje_discapacidad' => 'required|numeric|min:0|max:100',
        ]);
        $user = Auth::user();
        $data = GrupoFuncionariosDiscapacidadMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'id_tipo_discapacidad' => $request->id_tipo_discapacidad,
            'porcentaje_discapacidad' => $request->porcentaje_discapacidad,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'tipoDiscapacidad']), 'message' => 'Created'], 201);
    }

    public function updateFuncionariosDiscapacidad(Request $request, int $id): JsonResponse
    {
        $data = GrupoFuncionariosDiscapacidadMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'id_tipo_discapacidad' => 'sometimes|integer|exists:listado_tipos_discapacidades_medicoocupacional,id',
            'porcentaje_discapacidad' => 'sometimes|numeric|min:0|max:100',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'id_tipo_discapacidad', 'porcentaje_discapacidad']));
        return response()->json(['data' => $data->load(['doctor', 'paciente', 'tipoDiscapacidad']), 'message' => 'Updated']);
    }

    public function destroyFuncionariosDiscapacidad(int $id): JsonResponse
    {
        $data = GrupoFuncionariosDiscapacidadMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ENFERMEDADES NUEVAS
    // ==========================================

    public function indexEnfermedadesNuevas(Request $request): JsonResponse
    {
        $query = EnfermedadesNuevasMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_aparicion', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Enfermedades nuevas retrieved']);
    }

    public function showEnfermedadesNuevas(int $id): JsonResponse
    {
        $data = EnfermedadesNuevasMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Enfermedad nueva retrieved']);
    }

    public function storeEnfermedadesNuevas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_enfermedad_nueva' => 'required|string|max:255',
            'fecha_aparicion' => 'required|date',
        ]);
        $user = Auth::user();
        $data = EnfermedadesNuevasMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_enfermedad_nueva' => $request->detalle_enfermedad_nueva,
            'fecha_aparicion' => $request->fecha_aparicion,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateEnfermedadesNuevas(Request $request, int $id): JsonResponse
    {
        $data = EnfermedadesNuevasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_enfermedad_nueva' => 'sometimes|string|max:255',
            'fecha_aparicion' => 'sometimes|date',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_enfermedad_nueva', 'fecha_aparicion']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyEnfermedadesNuevas(int $id): JsonResponse
    {
        $data = EnfermedadesNuevasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ENFERMEDADES CATASTROFICAS
    // ==========================================

    public function indexEnfermedadesCatastroficas(Request $request): JsonResponse
    {
        $query = EnfermedadesCatastroficasMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Enfermedades catastroficas retrieved']);
    }

    public function showEnfermedadesCatastroficas(int $id): JsonResponse
    {
        $data = EnfermedadesCatastroficasMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Enfermedad catastrofica retrieved']);
    }

    public function storeEnfermedadesCatastroficas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_enfermedad' => 'required|string|max:255',
            'detalle_novedades' => 'nullable|string|max:255',
        ]);
        $user = Auth::user();
        $data = EnfermedadesCatastroficasMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_enfermedad' => $request->detalle_enfermedad,
            'detalle_novedades' => $request->detalle_novedades,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateEnfermedadesCatastroficas(Request $request, int $id): JsonResponse
    {
        $data = EnfermedadesCatastroficasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_enfermedad' => 'sometimes|string|max:255',
            'detalle_novedades' => 'sometimes|string|max:255',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_enfermedad', 'detalle_novedades']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyEnfermedadesCatastroficas(int $id): JsonResponse
    {
        $data = EnfermedadesCatastroficasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // LISTADO EMBARAZADAS
    // ==========================================

    public function indexListadoEmbarazadas(Request $request): JsonResponse
    {
        $query = ListadoEmbarazadasMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_fum', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Listado embarazadas retrieved']);
    }

    public function showListadoEmbarazadas(int $id): JsonResponse
    {
        $data = ListadoEmbarazadasMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Listado embarazada retrieved']);
    }

    public function storeListadoEmbarazadas(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'detalle_semanas_gestacion' => 'required|integer|min:1|max:50',
            'fecha_fum' => 'required|date',
            'fecha_probable_parto' => 'required|date|after:fecha_fum',
            'numero_controles' => 'required|integer|min:0',
        ]);
        $user = Auth::user();
        $data = ListadoEmbarazadasMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_semanas_gestacion' => $request->detalle_semanas_gestacion,
            'fecha_fum' => $request->fecha_fum,
            'fecha_probable_parto' => $request->fecha_probable_parto,
            'numero_controles' => $request->numero_controles,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateListadoEmbarazadas(Request $request, int $id): JsonResponse
    {
        $data = ListadoEmbarazadasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'detalle_semanas_gestacion' => 'sometimes|integer|min:1|max:50',
            'fecha_fum' => 'sometimes|date',
            'fecha_probable_parto' => 'sometimes|date',
            'numero_controles' => 'sometimes|integer|min:0',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'detalle_semanas_gestacion', 'fecha_fum', 'fecha_probable_parto', 'numero_controles']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyListadoEmbarazadas(int $id): JsonResponse
    {
        $data = ListadoEmbarazadasMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // SOSPECHOSOS CONFIRMADOS INFLUENZA
    // ==========================================

    public function indexInfluenza(Request $request): JsonResponse
    {
        $query = SospechososConfirmadosInfluenza::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Influenza retrieved']);
    }

    public function showInfluenza(int $id): JsonResponse
    {
        $data = SospechososConfirmadosInfluenza::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Influenza retrieved']);
    }

    public function storeInfluenza(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'tipo' => 'required|in:sospechoso,confirmado',
            'detalle_resultados' => 'nullable|string|max:255',
            'detalle_anticuerpos' => 'nullable|string|max:255',
            'detalle_altamedica' => 'nullable|string|max:255',
            'dias_aislamiento' => 'required|integer|min:1',
        ]);
        $user = Auth::user();
        $data = SospechososConfirmadosInfluenza::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'tipo' => $request->tipo,
            'detalle_resultados' => $request->detalle_resultados,
            'detalle_anticuerpos' => $request->detalle_anticuerpos,
            'detalle_altamedica' => $request->detalle_altamedica,
            'dias_aislamiento' => $request->dias_aislamiento,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateInfluenza(Request $request, int $id): JsonResponse
    {
        $data = SospechososConfirmadosInfluenza::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'tipo' => 'sometimes|in:sospechoso,confirmado',
            'detalle_resultados' => 'sometimes|string|max:255',
            'detalle_anticuerpos' => 'sometimes|string|max:255',
            'detalle_altamedica' => 'sometimes|string|max:255',
            'dias_aislamiento' => 'sometimes|integer|min:1',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'tipo', 'detalle_resultados', 'detalle_anticuerpos', 'detalle_altamedica', 'dias_aislamiento']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyInfluenza(int $id): JsonResponse
    {
        $data = SospechososConfirmadosInfluenza::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // AUSENTISMO LABORAL
    // ==========================================

    public function indexAusentismoLaboral(Request $request): JsonResponse
    {
        $query = AusentismoLaboralMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Ausentismo laboral retrieved']);
    }

    public function showAusentismoLaboral(int $id): JsonResponse
    {
        $data = AusentismoLaboralMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Ausentismo laboral retrieved']);
    }

    public function storeAusentismoLaboral(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'tipo' => 'required|in:enfermedad comun,enfermedad laboral,accidente laboral,otros',
            'detalle_ausentismo' => 'required|string',
            'dias_perdidos' => 'required|integer|min:0',
            'horas_perdidas' => 'required|numeric|min:0',
            'horas_trabajadas' => 'required|numeric|min:0',
            'indice_ausentismo' => 'required|numeric|min:0',
        ]);
        $user = Auth::user();
        $data = AusentismoLaboralMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'tipo' => $request->tipo,
            'detalle_ausentismo' => $request->detalle_ausentismo,
            'dias_perdidos' => $request->dias_perdidos,
            'horas_perdidas' => $request->horas_perdidas,
            'horas_trabajadas' => $request->horas_trabajadas,
            'indice_ausentismo' => $request->indice_ausentismo,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateAusentismoLaboral(Request $request, int $id): JsonResponse
    {
        $data = AusentismoLaboralMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'tipo' => 'sometimes|in:enfermedad comun,enfermedad laboral,accidente laboral,otros',
            'detalle_ausentismo' => 'sometimes|string',
            'dias_perdidos' => 'sometimes|integer|min:0',
            'horas_perdidas' => 'sometimes|numeric|min:0',
            'horas_trabajadas' => 'sometimes|numeric|min:0',
            'indice_ausentismo' => 'sometimes|numeric|min:0',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'tipo', 'detalle_ausentismo', 'dias_perdidos', 'horas_perdidas', 'horas_trabajadas', 'indice_ausentismo']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyAusentismoLaboral(int $id): JsonResponse
    {
        $data = AusentismoLaboralMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ACCIDENTES LABORALES
    // ==========================================

    public function indexAccidentesLaborales(Request $request): JsonResponse
    {
        $query = AccidentesLaboralesMedicoocupacional::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha_accidente', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Accidentes laborales retrieved']);
    }

    public function showAccidentesLaborales(int $id): JsonResponse
    {
        $data = AccidentesLaboralesMedicoocupacional::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Accidente laboral retrieved']);
    }

    public function storeAccidentesLaborales(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'lugar_accidente' => 'required|string|max:255',
            'fecha_accidente' => 'required|date',
            'detalle_parte_lesionada' => 'required|string|max:255',
            'tipo_incapacidad' => 'required|string|max:255',
            'causas_directas' => 'required|string|max:255',
            'agente_accidente' => 'required|string|max:255',
            'fuente_accidente' => 'required|string|max:255',
            'tipo_accidente' => 'required|string|max:255',
            'dias_perdidos' => 'required|integer|min:0',
        ]);
        $user = Auth::user();
        $data = AccidentesLaboralesMedicoocupacional::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'lugar_accidente' => $request->lugar_accidente,
            'fecha_accidente' => $request->fecha_accidente,
            'detalle_parte_lesionada' => $request->detalle_parte_lesionada,
            'tipo_incapacidad' => $request->tipo_incapacidad,
            'causas_directas' => $request->causas_directas,
            'agente_accidente' => $request->agente_accidente,
            'fuente_accidente' => $request->fuente_accidente,
            'tipo_accidente' => $request->tipo_accidente,
            'dias_perdidos' => $request->dias_perdidos,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateAccidentesLaborales(Request $request, int $id): JsonResponse
    {
        $data = AccidentesLaboralesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $request->validate([
            'id_usuario_paciente' => 'sometimes|integer|exists:users,id',
            'lugar_accidente' => 'sometimes|string|max:255',
            'fecha_accidente' => 'sometimes|date',
            'detalle_parte_lesionada' => 'sometimes|string|max:255',
            'tipo_incapacidad' => 'sometimes|string|max:255',
            'causas_directas' => 'sometimes|string|max:255',
            'agente_accidente' => 'sometimes|string|max:255',
            'fuente_accidente' => 'sometimes|string|max:255',
            'tipo_accidente' => 'sometimes|string|max:255',
            'dias_perdidos' => 'sometimes|integer|min:0',
        ]);
        $data->update($request->only(['id_usuario_paciente', 'lugar_accidente', 'fecha_accidente', 'detalle_parte_lesionada', 'tipo_incapacidad', 'causas_directas', 'agente_accidente', 'fuente_accidente', 'tipo_accidente', 'dias_perdidos']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyAccidentesLaborales(int $id): JsonResponse
    {
        $data = AccidentesLaboralesMedicoocupacional::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // BLOOD TYPE (medico ocupacional asigna/edita)
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
            'asignado_por_rol' => 'medico_ocupacional',
        ]);

        return response()->json([
            'data' => $registro->load('tipoSangre'),
            'message' => 'Blood type saved successfully',
        ], 201);
    }

    // ==========================================
    // INTEGRACION RECETA - FARMACIA
    // ==========================================

    public function agregarProductoReceta(Request $request, int $lineaRecetaId): JsonResponse
    {
        $linea = LineaRecetaMedicoocupacional::find($lineaRecetaId);
        if (!$linea) {
            return response()->json(['message' => 'Línea de receta no encontrada'], 404);
        }

        $request->validate([
            'id_producto_farmacia' => 'required|integer|exists:productos_farmacia,id',
        ]);

        $linea->update([
            'id_producto_farmacia' => $request->id_producto_farmacia,
        ]);

        return response()->json([
            'data' => $linea->load('producto'),
            'message' => 'Producto de farmacia asociado a la receta'
        ]);
    }

    public function recetasPorPaciente(int $pacienteId): JsonResponse
    {
        $recetas = RecetaMedicoocupacional::with(['doctor', 'lineas.producto', 'cie10s'])
            ->where('id_usuario_paciente', $pacienteId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $recetas,
            'message' => 'Recetas del paciente'
        ]);
    }

    public function recetasPendientesDespacho(): JsonResponse
    {
        $recetas = RecetaMedicoocupacional::with([
            'paciente.datosIdentificacion',
            'doctor',
            'lineas' => function ($q) {
                $q->with('producto');
            }
        ])
        ->whereHas('lineas', function ($q) {
            $q->whereNull('estado_despacho')
              ->orWhere('estado_despacho', '!=', 'despachado');
        })
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'data' => $recetas,
            'message' => 'Recetas pendientes de despacho'
        ]);
    }

    public function despacharProducto(Request $request, int $lineaRecetaId): JsonResponse
    {
        $linea = LineaRecetaMedicoocupacional::find($lineaRecetaId);
        if (!$linea) {
            return response()->json(['message' => 'Línea de receta no encontrada'], 404);
        }

        $request->validate([
            'cantidad_cajas' => 'nullable|integer|min:0',
            'cantidad_unidades' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $cajas = (int) ($request->cantidad_cajas ?? 0);
        $unidades = (int) ($request->cantidad_unidades ?? 1);

        $linea->update([
            'estado_despacho' => 'despachado',
            'despachado_por' => $user->id,
            'fecha_despacho' => now(),
            'cantidad_cajas_despachadas' => $cajas,
            'cantidad_unidades_despachadas' => $unidades,
            'observaciones_despacho' => $request->observaciones,
        ]);

        // Si la línea tiene un producto de farmacia vinculado, reducir stock
        if ($linea->id_producto_farmacia) {
            $producto = \App\Models\ProductoFarmacia::find($linea->id_producto_farmacia);
            if ($producto) {
                $producto->reducirStock($cajas, $unidades, $user, $request->observaciones ?? 'Despacho de receta', $linea->id);
            }
        }

        return response()->json([
            'data' => $linea->load('producto'),
            'message' => 'Medicamento despachado exitosamente'
        ]);
    }
}
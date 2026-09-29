<?php

namespace App\Http\Controllers\Api\V1\Odontologia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Odontologia\StoreCatalogoInsumosOdontologiaRequest;
use App\Http\Requests\Odontologia\StoreInsumosPacienteOdontologiaRequest;
use App\Http\Requests\Odontologia\StoreParteDiarioOdontologiaRequest;
use App\Http\Requests\Odontologia\UpdateCatalogoInsumosOdontologiaRequest;
use App\Http\Requests\Odontologia\UpdateInsumosPacienteOdontologiaRequest;
use App\Http\Requests\Odontologia\UpdateParteDiarioOdontologiaRequest;
use App\Models\CatalogoInsumosOdontologia;
use App\Models\InsumosPacienteOdontologia;
use App\Models\Odontologia\MotivoConsultaOdontologia;
use App\Models\Odontologia\ExamenOdontologia;
use App\Models\Odontologia\EnfermedadPeriodontalOdontologia;
use App\Models\Odontologia\HistorialEvolucionOdontologia;
use App\Models\ParteDiarioOdontologia;
use App\Models\OdontogramaEstado;
use App\Models\OdontogramaPaciente;
use App\Models\OdontogramaAsignacion;
use App\Models\OdontogramaOdontologia;
use App\Models\OdontogramaPiezaCarilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdontologiaController extends Controller
{
    // ==========================================
    // MOTIVO CONSULTA ODONTOLOGIA
    // ==========================================

    public function indexMotivoConsulta(Request $request): JsonResponse
    {
        $query = MotivoConsultaOdontologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Motivo consulta odontologia retrieved']);
    }

    public function showMotivoConsulta(int $id): JsonResponse
    {
        $data = MotivoConsultaOdontologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Motivo consulta odontologia retrieved']);
    }

    public function storeMotivoConsulta(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);
        $user = Auth::user();
        $data = MotivoConsultaOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'detalle_motivo' => $request->detalle_motivo,
            'ultima_visita_fecha' => $request->ultima_visita_fecha,
            'algun_tratamiento' => $request->algun_tratamiento ?? 'no',
            'algun_medicamento' => $request->algun_medicamento ?? 'no',
            'detalle_tratamiento' => $request->detalle_tratamiento,
            'detalle_medicamento' => $request->detalle_medicamento,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateMotivoConsulta(Request $request, int $id): JsonResponse
    {
        $data = MotivoConsultaOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $fields = [
            'detalle_motivo', 'ultima_visita_fecha', 'algun_tratamiento',
            'algun_medicamento', 'detalle_tratamiento', 'detalle_medicamento'
        ];
        $data->update($request->only($fields));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyMotivoConsulta(int $id): JsonResponse
    {
        $data = MotivoConsultaOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // EXAMEN ODONTOLOGIA
    // ==========================================

    public function indexExamen(Request $request): JsonResponse
    {
        $query = ExamenOdontologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Examen odontologia retrieved']);
    }

    public function showExamen(int $id): JsonResponse
    {
        $data = ExamenOdontologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Examen odontologia retrieved']);
    }

    public function storeExamen(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);
        $user = Auth::user();
        $data = ExamenOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'piel' => $request->piel,
            'labios' => $request->labios,
            'carrillos' => $request->carrillos,
            'paladar' => $request->paladar,
            'piso_de_la_boca' => $request->piso_de_la_boca,
            'lengua' => $request->lengua,
            'observaciones' => $request->observaciones,
            'glándulas_salivales' => $request->glándulas_salivales,
            'ganglios' => $request->ganglios,
            'tejido_muscular' => $request->tejido_muscular,
            'atm' => $request->atm,
            'maxilar_superior' => $request->maxilar_superior,
            'maxilar_inferior' => $request->maxilar_inferior,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateExamen(Request $request, int $id): JsonResponse
    {
        $data = ExamenOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $fields = [
            'piel', 'labios', 'carrillos', 'paladar', 'piso_de_la_boca', 'lengua',
            'observaciones', 'glándulas_salivales', 'ganglios', 'tejido_muscular',
            'atm', 'maxilar_superior', 'maxilar_inferior'
        ];
        $data->update($request->only($fields));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyExamen(int $id): JsonResponse
    {
        $data = ExamenOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // ENFERMEDAD PERIODONTAL ODONTOLOGIA
    // ==========================================

    public function indexEnfermedadPeriodontal(Request $request): JsonResponse
    {
        $query = EnfermedadPeriodontalOdontologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Enfermedad periodontal retrieved']);
    }

    public function showEnfermedadPeriodontal(int $id): JsonResponse
    {
        $data = EnfermedadPeriodontalOdontologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Enfermedad periodontal retrieved']);
    }

    public function storeEnfermedadPeriodontal(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);
        $user = Auth::user();
        $data = EnfermedadPeriodontalOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'placa_bacteriana' => $request->placa_bacteriana,
            'calculos_dentales' => $request->calculos_dentales,
            'bolsa_periodontal' => $request->bolsa_periodontal,
            'movilidad_dental' => $request->movilidad_dental,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateEnfermedadPeriodontal(Request $request, int $id): JsonResponse
    {
        $data = EnfermedadPeriodontalOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->update($request->only(['placa_bacteriana', 'calculos_dentales', 'bolsa_periodontal', 'movilidad_dental']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyEnfermedadPeriodontal(int $id): JsonResponse
    {
        $data = EnfermedadPeriodontalOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // HISTORIAL EVOLUCION ODONTOLOGIA
    // ==========================================

    public function indexHistorialEvolucion(Request $request): JsonResponse
    {
        $query = HistorialEvolucionOdontologia::query()->with(['doctor', 'paciente']);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $data = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $data, 'message' => 'Historial evolucion odontologia retrieved']);
    }

    public function showHistorialEvolucion(int $id): JsonResponse
    {
        $data = HistorialEvolucionOdontologia::with(['doctor', 'paciente'])->find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        return response()->json(['data' => $data, 'message' => 'Historial evolucion odontologia retrieved']);
    }

    public function storeHistorialEvolucion(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);
        $user = Auth::user();
        $data = HistorialEvolucionOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha ?? now()->toDateString(),
            'detalle_tratamiento' => $request->detalle_tratamiento,
            'detalle_procedimiento' => $request->detalle_procedimiento,
            'prescripción_farmaceutica' => $request->prescripción_farmaceutica,
        ]);
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Created'], 201);
    }

    public function updateHistorialEvolucion(Request $request, int $id): JsonResponse
    {
        $data = HistorialEvolucionOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->update($request->only(['fecha', 'detalle_tratamiento', 'detalle_procedimiento', 'prescripción_farmaceutica']));
        return response()->json(['data' => $data->load(['doctor', 'paciente']), 'message' => 'Updated']);
    }

    public function destroyHistorialEvolucion(int $id): JsonResponse
    {
        $data = HistorialEvolucionOdontologia::find($id);
        if (!$data) return response()->json(['message' => 'Not found'], 404);
        $data->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ==========================================
    // CATALOGO INSUMOS ODONTOLOGIA
    // ==========================================

    public function indexCatalogoInsumos(): JsonResponse
    {
        $user = Auth::user();
        $insumos = CatalogoInsumosOdontologia::where('id_usuario_medico', $user->id)->get();
        return response()->json(['data' => $insumos, 'message' => 'Catalogo insumos retrieved']);
    }

    public function showCatalogoInsumos(int $id): JsonResponse
    {
        $user = Auth::user();
        $insumo = CatalogoInsumosOdontologia::where('id', $id)
            ->where('id_usuario_medico', $user->id)->first();
        if (!$insumo) return response()->json(['message' => 'Insumo not found'], 404);
        return response()->json(['data' => $insumo, 'message' => 'Insumo retrieved']);
    }

    public function storeCatalogoInsumos(StoreCatalogoInsumosOdontologiaRequest $request): JsonResponse
    {
        $user = Auth::user();
        $insumo = CatalogoInsumosOdontologia::create([
            'id_usuario_medico' => $user->id,
            'nombre' => $request->nombre,
            'stock' => $request->stock,
        ]);
        return response()->json(['data' => $insumo, 'message' => 'Insumo created successfully'], 201);
    }

    public function updateCatalogoInsumos(UpdateCatalogoInsumosOdontologiaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $insumo = CatalogoInsumosOdontologia::where('id', $id)
            ->where('id_usuario_medico', $user->id)->first();
        if (!$insumo) return response()->json(['message' => 'Insumo not found'], 404);

        if ($request->filled('nombre')) {
            $insumo->nombre = $request->nombre;
        }

        if ($request->filled('cantidad_llegada') && (int)$request->cantidad_llegada > 0) {
            $insumo->stock += (int)$request->cantidad_llegada;
        } elseif ($request->has('stock') && $request->stock !== null) {
            $insumo->stock = (int)$request->stock;
        }

        $insumo->save();

        return response()->json(['data' => $insumo, 'message' => 'Stock updated successfully']);
    }

    public function destroyCatalogoInsumos(int $id): JsonResponse
    {
        $user = Auth::user();
        $insumo = CatalogoInsumosOdontologia::where('id', $id)
            ->where('id_usuario_medico', $user->id)->first();
        if (!$insumo) return response()->json(['message' => 'Insumo not found'], 404);
        $insumo->delete();
        return response()->json(null, 204);
    }

    // ==========================================
    // PARTE DIARIO ODONTOLOGIA
    // ==========================================

    public function indexParteDiario(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = ParteDiarioOdontologia::where('id_usuario_doctor', $user->id);
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        }
        $partes = $query->with(['paciente.datosIdentificacion'])->get();
        if ($partes->isEmpty() && !$request->has('id_usuario_paciente')) {
            $partes = ParteDiarioOdontologia::with(['paciente.datosIdentificacion'])->get();
        }
        return response()->json(['data' => $partes, 'message' => 'Partes diarios retrieved']);
    }

    public function showParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion'])->first();
        if (!$parte) return response()->json(['message' => 'Parte diario not found'], 404);
        return response()->json(['data' => $parte, 'message' => 'Parte diario retrieved']);
    }

    public function storeParteDiario(StoreParteDiarioOdontologiaRequest $request): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'id_usuario_paciente' => $request->id_usuario_paciente,
            'fecha' => $request->fecha,
            'tipo_atencion' => $request->tipo_atencion,
            'tipo_atencion2' => $request->tipo_atencion2,
            'detalle_diagnostico' => $request->detalle_diagnostico,
            'procedimiento' => $request->procedimiento ?? 'Profilaxis',
        ]);

        if (!empty($request->id_usuario_paciente)) {
            \App\Http\Controllers\Api\V1\CitasMedicasController::syncAutoCita($user->id, (int)$request->id_usuario_paciente, 'odontologo', $request->procedimiento ?? 'Atención en Odontología');
        }

        if ($request->has('prescripcion_lineas') && is_array($request->prescripcion_lineas) && count($request->prescripcion_lineas) > 0) {
            $receta = \App\Models\RecetaMedicoocupacional::create([
                'id_usuario_doctor' => $user->id,
                'id_usuario_paciente' => $request->id_usuario_paciente,
                'fecha' => $request->fecha ?? now()->toDateString(),
                'estado_enfermedad' => 'agudo',
            ]);

            foreach ($request->prescripcion_lineas as $linea) {
                if (empty($linea['detalle_medicamento'])) continue;

                $freq = isset($linea['frecuencia_horas']) ? (int)$linea['frecuencia_horas'] : 8;
                $dur = isset($linea['duracion_tratamiento_dias']) ? (int)$linea['duracion_tratamiento_dias'] : 3;
                $dosis = $linea['detalle_dosis'] ?? '1';
                $via = $linea['detalle_via_administracion'] ?? 'Oral';
                $cantCajas = isset($linea['cantidad_cajas']) ? (int)$linea['cantidad_cajas'] : 1;
                $cantUnidades = isset($linea['cantidad_unidades']) ? (int)$linea['cantidad_unidades'] : 0;
                $prodId = $linea['id_producto_farmacia'] ?? null;

                \App\Models\LineaRecetaMedicoocupacional::create([
                    'id_receta' => $receta->id,
                    'id_producto_farmacia' => $prodId,
                    'detalle_medicamento' => $linea['detalle_medicamento'],
                    'detalle_dosis' => $dosis,
                    'frecuencia_horas' => $freq,
                    'duracion_tratamiento_dias' => $dur,
                    'detalle_via_administracion' => $via,
                    'detalle_numero_y_letras' => "Cada {$freq}h por {$dur} días",
                    'cantidad_cajas' => $cantCajas,
                    'cantidad_unidades' => $cantUnidades,
                    'estado_despacho' => 'pendiente',
                ]);
            }
        }

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion']),
            'message' => 'Parte diario created successfully',
        ], 201);
    }

    public function updateParteDiario(UpdateParteDiarioOdontologiaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)->first();
        if (!$parte) return response()->json(['message' => 'Parte diario not found'], 404);

        $parte->update($request->validated());

        if ($request->has('prescripcion_lineas') && is_array($request->prescripcion_lineas) && count($request->prescripcion_lineas) > 0) {
            $receta = \App\Models\RecetaMedicoocupacional::create([
                'id_usuario_doctor' => $user->id,
                'id_usuario_paciente' => $parte->id_usuario_paciente,
                'fecha' => $parte->fecha ?? now()->toDateString(),
                'estado_enfermedad' => 'agudo',
            ]);

            foreach ($request->prescripcion_lineas as $linea) {
                if (empty($linea['detalle_medicamento'])) continue;

                $freq = isset($linea['frecuencia_horas']) ? (int)$linea['frecuencia_horas'] : 8;
                $dur = isset($linea['duracion_tratamiento_dias']) ? (int)$linea['duracion_tratamiento_dias'] : 3;
                $dosis = $linea['detalle_dosis'] ?? '1';
                $via = $linea['detalle_via_administracion'] ?? 'Oral';
                $cantCajas = isset($linea['cantidad_cajas']) ? (int)$linea['cantidad_cajas'] : 1;
                $cantUnidades = isset($linea['cantidad_unidades']) ? (int)$linea['cantidad_unidades'] : 0;
                $prodId = $linea['id_producto_farmacia'] ?? null;

                \App\Models\LineaRecetaMedicoocupacional::create([
                    'id_receta' => $receta->id,
                    'id_producto_farmacia' => $prodId,
                    'detalle_medicamento' => $linea['detalle_medicamento'],
                    'detalle_dosis' => $dosis,
                    'frecuencia_horas' => $freq,
                    'duracion_tratamiento_dias' => $dur,
                    'detalle_via_administracion' => $via,
                    'detalle_numero_y_letras' => "Cada {$freq}h por {$dur} días",
                    'cantidad_cajas' => $cantCajas,
                    'cantidad_unidades' => $cantUnidades,
                    'estado_despacho' => 'pendiente',
                ]);
            }
        }

        return response()->json([
            'data' => $parte->load(['paciente.datosIdentificacion']),
            'message' => 'Parte diario updated successfully',
        ]);
    }

    public function destroyParteDiario(int $id): JsonResponse
    {
        $user = Auth::user();
        $parte = ParteDiarioOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)->first();
        if (!$parte) return response()->json(['message' => 'Parte diario not found'], 404);
        $parte->delete();
        return response()->json(null, 204);
    }

    // ==========================================
    // INSUMOS PACIENTE ODONTOLOGIA
    // Logica: si cantidad_gastada comienza con "1", disminuye stock
    // ==========================================

    public function indexInsumosPaciente(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = InsumosPacienteOdontologia::query();
        if ($request->has('id_usuario_paciente')) {
            $query->where('id_usuario_paciente', $request->id_usuario_paciente);
        } else {
            $query->where('id_usuario_doctor', $user->id);
        }
        $insumos = $query->with(['paciente.datosIdentificacion', 'insumo'])->get();

        // Fallback para reportes si el doctor aún no ha registrado insumos con su ID de doctor actual
        if ($insumos->isEmpty() && !$request->has('id_usuario_paciente')) {
            $insumos = InsumosPacienteOdontologia::with(['paciente.datosIdentificacion', 'insumo'])->get();
        }
        return response()->json(['data' => $insumos, 'message' => 'Insumos paciente retrieved']);
    }

    public function showInsumosPaciente(int $id): JsonResponse
    {
        $user = Auth::user();
        $insumo = InsumosPacienteOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion', 'insumo'])->first();
        if (!$insumo) return response()->json(['message' => 'Insumo paciente not found'], 404);
        return response()->json(['data' => $insumo, 'message' => 'Insumo paciente retrieved']);
    }

    public function storeInsumosPaciente(StoreInsumosPacienteOdontologiaRequest $request): JsonResponse
    {
        $user = Auth::user();
        $insumo = CatalogoInsumosOdontologia::where('id', $request->id_insumo)
            ->where('id_usuario_medico', $user->id)->first();
        if (!$insumo) return response()->json(['message' => 'Insumo not found or not owned by you'], 404);

        if ($insumo->stock <= 0) {
            return response()->json(['message' => 'Stock insuficiente'], 422);
        }

        $insumoPaciente = new InsumosPacienteOdontologia();
        $insumoPaciente->id_usuario_doctor = $user->id;
        $insumoPaciente->id_usuario_paciente = $request->id_usuario_paciente;
        $insumoPaciente->id_insumo = $request->id_insumo;
        $insumoPaciente->cantidad_gastada = $request->cantidad_gastada;

        if ($insumoPaciente->debeDisminuirStock()) {
            DB::transaction(function () use ($insumoPaciente, $insumo) {
                $insumo = CatalogoInsumosOdontologia::lockForUpdate()->find($insumo->id);
                $cantidad = (int) trim($insumoPaciente->cantidad_gastada);
                if ($insumo->stock < $cantidad) throw new \Exception('Stock insuficiente');
                $insumo->decrement('stock', $cantidad);
                $insumoPaciente->save();
            });
        } else {
            $insumoPaciente->save();
        }

        return response()->json([
            'data' => $insumoPaciente->load(['paciente.datosIdentificacion', 'insumo']),
            'message' => 'Insumo paciente created successfully',
        ], 201);
    }

    public function updateInsumosPaciente(UpdateInsumosPacienteOdontologiaRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $insumoPaciente = InsumosPacienteOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)->first();
        if (!$insumoPaciente) return response()->json(['message' => 'Insumo paciente not found'], 404);
        $insumoPaciente->update($request->only(['id_usuario_paciente', 'id_insumo', 'cantidad_gastada']));
        return response()->json([
            'data' => $insumoPaciente->load(['paciente.datosIdentificacion', 'insumo']),
            'message' => 'Insumo paciente updated successfully',
        ]);
    }

    public function destroyInsumosPaciente(int $id): JsonResponse
    {
        $user = Auth::user();
        $insumoPaciente = InsumosPacienteOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)->first();
        if (!$insumoPaciente) return response()->json(['message' => 'Insumo paciente not found'], 404);
        $insumoPaciente->delete();
        return response()->json(null, 204);
    }

    // ==========================================
    // ODONTOGRAMA ESTADOS
    // ==========================================

    public function indexCatalogoOdontograma(): JsonResponse
    {
        $catalogo = OdontogramaOdontologia::with('carillas')->get();
        return response()->json(['data' => $catalogo, 'message' => 'Catálogo de odontograma retrieved']);
    }

    public function indexEstadoOdontograma(): JsonResponse
    {
        $estados = OdontogramaEstado::all();
        return response()->json(['data' => $estados, 'message' => 'Estados odontograma retrieved']);
    }

    public function showEstadoOdontograma(int $id): JsonResponse
    {
        $estado = OdontogramaEstado::find($id);
        if (!$estado) return response()->json(['message' => 'Estado not found'], 404);
        return response()->json(['data' => $estado, 'message' => 'Estado retrieved']);
    }

    public function storeEstadoOdontograma(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:odontograma_estado_odontologia,nombre',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'svg_icon' => 'required|string|max:2048',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $estado = OdontogramaEstado::create($request->only(['nombre', 'color', 'svg_icon', 'descripcion']));
        return response()->json(['data' => $estado, 'message' => 'Estado created'], 201);
    }

    public function updateEstadoOdontograma(Request $request, int $id): JsonResponse
    {
        $estado = OdontogramaEstado::find($id);
        if (!$estado) return response()->json(['message' => 'Estado not found'], 404);

        $request->validate([
            'nombre' => 'sometimes|string|max:100|unique:odontograma_estado_odontologia,nombre,' . $id,
            'color' => 'sometimes|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'svg_icon' => 'sometimes|string|max:2048',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $estado->update($request->only(['nombre', 'color', 'svg_icon', 'descripcion']));
        return response()->json(['data' => $estado, 'message' => 'Estado updated']);
    }

    public function destroyEstadoOdontograma(int $id): JsonResponse
    {
        $estado = OdontogramaEstado::find($id);
        if (!$estado) return response()->json(['message' => 'Estado not found'], 404);

        $asignacionesCount = $estado->asignaciones()->count();
        if ($asignacionesCount > 0) {
            return response()->json(['message' => "Estado referenced by {$asignacionesCount} assignments"], 409);
        }

        $estado->delete();
        return response()->json(['message' => 'Estado deleted']);
    }

    // ==========================================
    // ODONTOGRAMA PACIENTE
    // ==========================================

    public function showOdontogramaPaciente(int $pacienteId): JsonResponse
    {
        $odontograma = OdontogramaPaciente::where('id_usuario_paciente', $pacienteId)
            ->with(['asignaciones.pieza', 'asignaciones.carilla', 'asignaciones.estado'])
            ->first();

        if (!$odontograma) {
            return response()->json(['message' => 'Odontograma not found for this patient'], 404);
        }

        return response()->json(['data' => $odontograma, 'message' => 'Odontograma retrieved']);
    }

    public function storeOdontogramaPaciente(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
        ]);

        $existing = OdontogramaPaciente::where('id_usuario_paciente', $request->id_usuario_paciente)->first();
        if ($existing) {
            return response()->json(['data' => $existing->load(['asignaciones.pieza', 'asignaciones.carilla', 'asignaciones.estado']), 'message' => 'Odontograma already exists']);
        }

        $odontograma = OdontogramaPaciente::create([
            'id_usuario_paciente' => $request->id_usuario_paciente,
        ]);

        return response()->json(['data' => $odontograma, 'message' => 'Odontograma created'], 201);
    }

    public function syncOdontogramaPaciente(Request $request, int $pacienteId): JsonResponse
    {
        $request->validate([
            'asignaciones' => 'present|array',
            'asignaciones.*.id_numero_pieza' => 'required|integer|exists:odontograma_odontologia,id',
            'asignaciones.*.id_numero_carilla' => 'nullable|integer|exists:odontograma_pieza_carilla_odontologia,id',
            'asignaciones.*.id_estado' => 'required|integer|exists:odontograma_estado_odontologia,id',
            'asignaciones.*.fecha' => 'nullable|date',
        ]);

        $odontograma = OdontogramaPaciente::firstOrCreate([
            'id_usuario_paciente' => $pacienteId,
        ]);

        $fechaDefault = today()->toDateString();

        DB::transaction(function () use ($odontograma, $request, $fechaDefault) {
            OdontogramaAsignacion::where('id_odontograma_paciente', $odontograma->id)->delete();

            foreach ($request->asignaciones as $item) {
                OdontogramaAsignacion::create([
                    'id_odontograma_paciente' => $odontograma->id,
                    'id_numero_pieza' => $item['id_numero_pieza'],
                    'id_numero_carilla' => $item['id_numero_carilla'] ?? null,
                    'id_estado' => $item['id_estado'],
                    'fecha' => !empty($item['fecha']) ? $item['fecha'] : $fechaDefault,
                ]);
            }
        });

        $odontograma->load(['asignaciones.pieza', 'asignaciones.carilla', 'asignaciones.estado']);

        return response()->json([
            'data' => $odontograma,
            'message' => 'Odontograma sincronizado exitosamente'
        ]);
    }


    // ==========================================
    // ODONTOGRAMA ASIGNACIONES
    // ==========================================

    public function indexAsignacionOdontograma(Request $request): JsonResponse
    {
        $query = OdontogramaAsignacion::query()
            ->with(['pieza', 'carilla', 'estado']);

        if ($request->has('id_odontograma_paciente')) {
            $query->where('id_odontograma_paciente', $request->id_odontograma_paciente);
        }

        $asignaciones = $query->get();
        return response()->json(['data' => $asignaciones, 'message' => 'Asignaciones retrieved']);
    }

    public function storeAsignacionOdontograma(Request $request): JsonResponse
    {
        $request->validate([
            'id_odontograma_paciente' => 'required|integer|exists:odontograma_paciente_odontologia,id',
            'id_numero_pieza' => 'required|integer|exists:odontograma_odontologia,id',
            'id_numero_carilla' => 'nullable|integer|exists:odontograma_pieza_carilla_odontologia,id',
            'id_estado' => 'required|integer|exists:odontograma_estado_odontologia,id',
            'fecha' => 'required|date|before_or_equal:today',
        ]);

        // Check for mixed granularity: if tooth has full-tooth assignment, reject carilla-level
        if ($request->id_numero_carilla !== null) {
            $fullToothAssignment = OdontogramaAsignacion::where('id_odontograma_paciente', $request->id_odontograma_paciente)
                ->where('id_numero_pieza', $request->id_numero_pieza)
                ->whereNull('id_numero_carilla')
                ->first();

            if ($fullToothAssignment) {
                return response()->json(['message' => 'Tooth already has full-tooth assignment; remove it before carilla-level assignment'], 422);
            }
        } else {
            // If assigning full tooth, reject if carilla-level assignments exist
            $carillaAssignments = OdontogramaAsignacion::where('id_odontograma_paciente', $request->id_odontograma_paciente)
                ->where('id_numero_pieza', $request->id_numero_pieza)
                ->whereNotNull('id_numero_carilla')
                ->count();

            if ($carillaAssignments > 0) {
                return response()->json(['message' => 'Tooth already has carilla-level assignments; remove them before full-tooth assignment'], 422);
            }
        }

        $asignacion = OdontogramaAsignacion::create([
            'id_odontograma_paciente' => $request->id_odontograma_paciente,
            'id_numero_pieza' => $request->id_numero_pieza,
            'id_numero_carilla' => $request->id_numero_carilla,
            'id_estado' => $request->id_estado,
            'fecha' => $request->fecha,
        ]);

        return response()->json(['data' => $asignacion->load(['pieza', 'carilla', 'estado']), 'message' => 'Asignacion created'], 201);
    }

    public function updateAsignacionOdontograma(Request $request, int $id): JsonResponse
    {
        $asignacion = OdontogramaAsignacion::find($id);
        if (!$asignacion) return response()->json(['message' => 'Asignacion not found'], 404);

        $request->validate([
            'id_estado' => 'sometimes|integer|exists:odontograma_estado_odontologia,id',
            'fecha' => 'sometimes|date|before_or_equal:today',
        ]);

        $asignacion->update($request->only(['id_estado', 'fecha']));
        return response()->json(['data' => $asignacion->load(['pieza', 'carilla', 'estado']), 'message' => 'Asignacion updated']);
    }

    public function destroyAsignacionOdontograma(int $id): JsonResponse
    {
        $asignacion = OdontogramaAsignacion::find($id);
        if (!$asignacion) return response()->json(['message' => 'Asignacion not found'], 404);

        $asignacion->delete();
        return response()->json(['message' => 'Asignacion deleted']);
    }

    // ==========================================
    // PROCEDIMIENTOS ODONTOLOGIA
    // ==========================================

    public function indexProcedimientos(): JsonResponse
    {
        $user = Auth::user();
        $procedimientos = \App\Models\ProcedimientoOdontologia::where('id_usuario_doctor', $user->id)->get();

        // Si el catálogo está vacío para este doctor, se cargan procedimientos predeterminados
        if ($procedimientos->isEmpty()) {
            $defaultNames = [
                'Profilaxis',
                'Fluorización',
                'Destartraje',
                'Rest. Provisional',
                'Rest. con Resina',
                'Desgaste',
                'Exodoncias',
                'Receta',
                'Orden de RX'
            ];
            foreach ($defaultNames as $name) {
                \App\Models\ProcedimientoOdontologia::create([
                    'id_usuario_doctor' => $user->id,
                    'nombre_procedimiento' => $name,
                ]);
            }
            $procedimientos = \App\Models\ProcedimientoOdontologia::where('id_usuario_doctor', $user->id)->get();
        }

        return response()->json(['data' => $procedimientos, 'message' => 'Procedimientos odontologia retrieved']);
    }

    public function storeProcedimiento(Request $request): JsonResponse
    {
        $request->validate([
            'nombre_procedimiento' => 'required|string|max:255',
        ]);
        $user = Auth::user();
        $procedimiento = \App\Models\ProcedimientoOdontologia::create([
            'id_usuario_doctor' => $user->id,
            'nombre_procedimiento' => $request->nombre_procedimiento,
        ]);
        return response()->json(['data' => $procedimiento, 'message' => 'Procedimiento created successfully'], 201);
    }

    public function destroyProcedimiento(int $id): JsonResponse
    {
        $user = Auth::user();
        $procedimiento = \App\Models\ProcedimientoOdontologia::where('id', $id)
            ->where('id_usuario_doctor', $user->id)->first();
        if (!$procedimiento) {
            return response()->json(['message' => 'Procedimiento not found'], 404);
        }
        $procedimiento->delete();
        return response()->json(null, 204);
    }
}
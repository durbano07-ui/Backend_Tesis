<?php

namespace App\Http\Controllers\Concerns;

/**
 * Receta emitida junto con la atención del día (Odontología y Medicina General). Antes los
 * medicamentos de la ficha no se enviaban ni se guardaban. Requiere ScopesHistoryByDepartment.
 */
trait GuardaRecetaDelDia
{
    /**
     * Receta del día de la atención: una por profesional, paciente y fecha. Guardar la atención otra
     * vez reemplaza sus medicamentos en lugar de crear otra receta, salvo que Farmacia ya haya
     * despachado alguno (entonces la receta no se toca).
     */
    protected function guardarRecetaDelDia(int $doctorId, int $pacienteId, $fecha, array $lineas): void
    {
        $fecha = \Illuminate\Support\Carbon::parse($fecha)->toDateString();
        $lineas = array_values(array_filter($lineas, fn ($l) => !empty($l['detalle_medicamento'])));

        $receta = \App\Models\RecetaMedicoocupacional::where('id_usuario_doctor', $doctorId)
            ->where('id_usuario_paciente', $pacienteId)
            ->whereDate('fecha', $fecha)
            ->first();

        if ($receta) {
            if ($receta->lineas()->where('estado_despacho', '!=', 'pendiente')->exists()) {
                return;
            }
            $receta->lineas()->delete();
            if (!$lineas) {
                $receta->delete();
                return;
            }
        } elseif (!$lineas) {
            return;
        } else {
            $receta = \App\Models\RecetaMedicoocupacional::create([
                'id_usuario_doctor' => $doctorId,
                'id_usuario_paciente' => $pacienteId,
                'fecha' => $fecha,
                'estado_enfermedad' => 'agudo',
            ]);
        }

        foreach ($lineas as $linea) {
            $freq = isset($linea['frecuencia_horas']) ? (int) $linea['frecuencia_horas'] : 8;
            $dur = isset($linea['duracion_tratamiento_dias']) ? (int) $linea['duracion_tratamiento_dias'] : 3;

            \App\Models\LineaRecetaMedicoocupacional::create([
                'id_receta' => $receta->id,
                'id_producto_farmacia' => $linea['id_producto_farmacia'] ?? null,
                'detalle_medicamento' => $linea['detalle_medicamento'],
                'detalle_dosis' => $linea['detalle_dosis'] ?? '1',
                'frecuencia_horas' => $freq,
                'duracion_tratamiento_dias' => $dur,
                'detalle_via_administracion' => $linea['detalle_via_administracion'] ?? 'Oral',
                'detalle_numero_y_letras' => "Cada {$freq}h por {$dur} días",
                // Las cantidades las define Farmacia al despachar
                'cantidad_cajas' => isset($linea['cantidad_cajas']) ? (int) $linea['cantidad_cajas'] : 0,
                'cantidad_unidades' => isset($linea['cantidad_unidades']) ? (int) $linea['cantidad_unidades'] : 0,
                'estado_despacho' => 'pendiente',
            ]);
        }
    }

    /**
     * Recetas del paciente emitidas en el departamento del profesional (historyQuery), con sus
     * medicamentos. La receta se guarda junto
     * al parte diario sin enlazarlo, así que la atención se identifica por la fecha (?fecha=Y-m-d).
     */
    public function indexRecetas(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => 'required|integer|exists:users,id',
            'fecha' => 'nullable|date_format:Y-m-d',
        ]);

        $query = $this->historyQuery(\App\Models\RecetaMedicoocupacional::class, $request)
            ->with(['lineas', 'doctor.datosIdentificacion']);
        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        return response()->json([
            'data' => $query->orderByDesc('fecha')->orderByDesc('id')->get(),
            'message' => 'Recetas retrieved',
        ]);
    }
}

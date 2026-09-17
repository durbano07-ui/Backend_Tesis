<?php

namespace App\Http\Controllers\Api\V1\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\Presentacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    /**
     * Listar todas las presentaciones
     */
    public function index(): JsonResponse
    {
        $presentaciones = Presentacion::withCount('productos')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'data' => $presentaciones,
            'message' => 'Presentaciones retrieved'
        ]);
    }

    /**
     * Mostrar una presentación
     */
    public function show(int $id): JsonResponse
    {
        $presentacion = Presentacion::withCount('productos')->find($id);

        if (!$presentacion) {
            return response()->json(['message' => 'Presentación no encontrada'], 404);
        }

        return response()->json([
            'data' => $presentacion,
            'message' => 'Presentación retrieved'
        ]);
    }

    /**
     * Crear una presentación
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:presentaciones,nombre',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $presentacion = Presentacion::create($request->only(['nombre', 'descripcion']));

        return response()->json([
            'data' => $presentacion,
            'message' => 'Presentación creada'
        ], 201);
    }

    /**
     * Actualizar una presentación
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $presentacion = Presentacion::find($id);

        if (!$presentacion) {
            return response()->json(['message' => 'Presentación no encontrada'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255|unique:presentaciones,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        $presentacion->update($request->only(['nombre', 'descripcion']));

        return response()->json([
            'data' => $presentacion,
            'message' => 'Presentación actualizada'
        ]);
    }

    /**
     * Eliminar una presentación
     */
    public function destroy(int $id): JsonResponse
    {
        $presentacion = Presentacion::find($id);

        if (!$presentacion) {
            return response()->json(['message' => 'Presentación no encontrada'], 404);
        }

        // Verificar si tiene productos asociados
        if ($presentacion->productos()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: tiene productos asociados'
            ], 400);
        }

        $presentacion->delete();

        return response()->json(['message' => 'Presentación eliminada']);
    }
}
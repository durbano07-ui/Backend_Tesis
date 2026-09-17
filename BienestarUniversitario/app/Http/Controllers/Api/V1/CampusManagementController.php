<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\LugarTrabajo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampusManagementController extends Controller
{
    // ==========================================
    // CRUD: CAMPUS (LugarTrabajo)
    // ==========================================

    /**
     * Get all Campus (Lugar de Trabajo).
     */
    public function indexCampus(Request $request): JsonResponse
    {
        $campus = LugarTrabajo::all();
        return response()->json([
            'data' => $campus,
            'message' => 'Catálogo de campus obtenido con éxito'
        ]);
    }

    /**
     * Store new Campus.
     */
    public function storeCampus(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:lugar_trabajo,nombre'],
        ]);

        $campus = LugarTrabajo::create([
            'nombre' => trim($request->nombre),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'campus_created',
            'model_type' => LugarTrabajo::class,
            'model_id' => $campus->id,
            'new_values' => $campus->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $campus,
            'message' => 'Campus registrado exitosamente'
        ], 201);
    }

    /**
     * Update existing Campus.
     */
    public function updateCampus(Request $request, int $id): JsonResponse
    {
        $campus = LugarTrabajo::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:lugar_trabajo,nombre,' . $id],
        ]);

        $oldValues = $campus->toArray();
        $campus->nombre = trim($request->nombre);
        $campus->save();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'campus_updated',
            'model_type' => LugarTrabajo::class,
            'model_id' => $campus->id,
            'old_values' => $oldValues,
            'new_values' => $campus->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $campus,
            'message' => 'Campus actualizado exitosamente'
        ]);
    }

    /**
     * Delete Campus.
     */
    public function destroyCampus(Request $request, int $id): JsonResponse
    {
        $campus = LugarTrabajo::findOrFail($id);
        $oldValues = $campus->toArray();
        $campus->delete();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'campus_deleted',
            'model_type' => LugarTrabajo::class,
            'model_id' => $id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Campus eliminado correctamente'
        ]);
    }

    // ==========================================
    // CRUD: FACULTADES
    // ==========================================

    /**
     * Get all Facultades.
     */
    public function indexFacultades(Request $request): JsonResponse
    {
        $facultades = Facultad::withCount('carreras')->get();
        return response()->json([
            'data' => $facultades,
            'message' => 'Facultades obtenidas con éxito'
        ]);
    }

    /**
     * Store new Facultad.
     */
    public function storeFacultad(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:facultad,nombre'],
        ]);

        $facultad = Facultad::create([
            'nombre' => trim($request->nombre),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'facultad_created',
            'model_type' => Facultad::class,
            'model_id' => $facultad->id,
            'new_values' => $facultad->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $facultad,
            'message' => 'Facultad registrada exitosamente'
        ], 201);
    }

    /**
     * Update existing Facultad.
     */
    public function updateFacultad(Request $request, int $id): JsonResponse
    {
        $facultad = Facultad::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:facultad,nombre,' . $id],
        ]);

        $oldValues = $facultad->toArray();
        $facultad->nombre = trim($request->nombre);
        $facultad->save();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'facultad_updated',
            'model_type' => Facultad::class,
            'model_id' => $facultad->id,
            'old_values' => $oldValues,
            'new_values' => $facultad->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $facultad,
            'message' => 'Facultad actualizada exitosamente'
        ]);
    }

    /**
     * Delete Facultad.
     */
    public function destroyFacultad(Request $request, int $id): JsonResponse
    {
        $facultad = Facultad::findOrFail($id);
        $oldValues = $facultad->toArray();
        $facultad->delete();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'facultad_deleted',
            'model_type' => Facultad::class,
            'model_id' => $id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Facultad eliminada correctamente'
        ]);
    }

    // ==========================================
    // CRUD: CARRERAS
    // ==========================================

    /**
     * Get all Carreras.
     */
    public function indexCarreras(Request $request): JsonResponse
    {
        $query = Carrera::with('facultad');

        if ($request->has('id_facultad') && !empty($request->id_facultad)) {
            $query->where('id_facultad', $request->id_facultad);
        }

        $carreras = $query->get();

        return response()->json([
            'data' => $carreras,
            'message' => 'Carreras obtenidas con éxito'
        ]);
    }

    /**
     * Store new Carrera.
     */
    public function storeCarrera(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'id_facultad' => ['required', 'integer', 'exists:facultad,id'],
        ]);

        $carrera = Carrera::create([
            'id_facultad' => $request->id_facultad,
            'nombre' => trim($request->nombre),
        ]);

        $carrera->load('facultad');

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'carrera_created',
            'model_type' => Carrera::class,
            'model_id' => $carrera->id,
            'new_values' => $carrera->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $carrera,
            'message' => 'Carrera registrada exitosamente'
        ], 201);
    }

    /**
     * Update existing Carrera.
     */
    public function updateCarrera(Request $request, int $id): JsonResponse
    {
        $carrera = Carrera::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'id_facultad' => ['required', 'integer', 'exists:facultad,id'],
        ]);

        $oldValues = $carrera->toArray();
        $carrera->nombre = trim($request->nombre);
        $carrera->id_facultad = $request->id_facultad;
        $carrera->save();
        $carrera->load('facultad');

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'carrera_updated',
            'model_type' => Carrera::class,
            'model_id' => $carrera->id,
            'old_values' => $oldValues,
            'new_values' => $carrera->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'data' => $carrera,
            'message' => 'Carrera actualizada exitosamente'
        ]);
    }

    /**
     * Delete Carrera.
     */
    public function destroyCarrera(Request $request, int $id): JsonResponse
    {
        $carrera = Carrera::findOrFail($id);
        $oldValues = $carrera->toArray();
        $carrera->delete();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'carrera_deleted',
            'model_type' => Carrera::class,
            'model_id' => $id,
            'old_values' => $oldValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Carrera eliminada correctamente'
        ]);
    }
}

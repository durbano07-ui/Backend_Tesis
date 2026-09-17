<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard statistics.
     * GET /api/v1/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('medico_coordinador');

        if (!$isAdmin && !$isCoordinator) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        // Date filters
        $fechaDesde = $request->input('fecha_desde', now()->subDays(30)->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', now()->format('Y-m-d'));

        $response = [];

        // User stats (admin only)
        if ($isAdmin) {
            $response['user_stats'] = $this->dashboardService->getUserStats();
        }

        // Doctor stats (coordinator and admin)
        $response['doctores_stats'] = $this->dashboardService->getDoctoresStats($fechaDesde, $fechaHasta);

        // Frequent students (coordinator and admin)
        $limite = $request->input('limite_estudiantes', 20);
        $response['estudiantes_frecuentes'] = $this->dashboardService->getEstudiantesFrecuentes($fechaDesde, $fechaHasta, $limite);

        // Attentions by time (coordinator and admin)
        $agrupar = $request->input('agrupar', 'dia');
        $response['atenciones_por_tiempo'] = $this->dashboardService->getAtencionesPorTiempo($fechaDesde, $fechaHasta, $agrupar);

        // Resumen atenciones (coordinator and admin)
        $response['resumen_atenciones'] = $this->dashboardService->getResumenAtenciones($fechaDesde, $fechaHasta);

        // Audit logs (admin only)
        if ($isAdmin) {
            $response['audits_recientes'] = $this->dashboardService->getAuditsRecientes(50);
            $response['security_events'] = $this->dashboardService->getSecurityEventsRecientes(50);
        }

        return response()->json($response);
    }

    /**
     * Get detailed doctor statistics.
     * GET /api/v1/dashboard/doctores
     */
    public function doctores(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('medico_coordinador');

        if (!$isAdmin && !$isCoordinator) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $fechaDesde = $request->input('fecha_desde', now()->subDays(30)->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', now()->format('Y-m-d'));

        return response()->json([
            'doctores_stats' => $this->dashboardService->getDoctoresStats($fechaDesde, $fechaHasta),
        ]);
    }

    /**
     * Get frequent students.
     * GET /api/v1/dashboard/estudiantes-frecuentes
     */
    public function estudiantesFrecuentes(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('medico_coordinador');

        if (!$isAdmin && !$isCoordinator) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $fechaDesde = $request->input('fecha_desde', now()->subDays(30)->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', now()->format('Y-m-d'));
        $limite = $request->input('limite', 20);

        return response()->json([
            'estudiantes_frecuentes' => $this->dashboardService->getEstudiantesFrecuentes($fechaDesde, $fechaHasta, $limite),
        ]);
    }

    /**
     * Get audit and security logs.
     * GET /api/v1/dashboard/auditoria (admin only)
     */
    public function auditoria(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('administrador')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        return response()->json([
            'audits_recientes' => $this->dashboardService->getAuditsRecientes(50),
            'security_events' => $this->dashboardService->getSecurityEventsRecientes(50),
        ]);
    }

    /**
     * Get attentions grouped by time.
     * GET /api/v1/dashboard/atenciones-tiempo
     */
    public function atencionesPorTiempo(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('medico_coordinador');

        if (!$isAdmin && !$isCoordinator) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $fechaDesde = $request->input('fecha_desde', now()->subDays(30)->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', now()->format('Y-m-d'));
        $agrupar = $request->input('agrupar', 'dia');

        return response()->json([
            'atenciones_por_tiempo' => $this->dashboardService->getAtencionesPorTiempo($fechaDesde, $fechaHasta, $agrupar),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportesService;
use Dompdf\Dompdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ReportesController extends Controller
{
    public function __construct(
        private ReportesService $reportesService
    ) {}

    /**
     * Generate a statistical report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reporte(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'tipo' => ['nullable', 'in:medicina_general,enfermeria,psicologia,odontologia'],
            'id_facultad' => ['nullable', 'integer', 'exists:facultad,id'],
            'id_carrera' => ['nullable', 'integer', 'exists:carrera,id'],
            'id_genero' => ['nullable', 'integer', 'exists:identificacion_genero,id'],
            'id_tipo_usuario' => ['nullable', 'integer', 'exists:tipo_usuario,id'],
            'agrupar_por' => ['nullable', 'in:facultad,carrera,genero,tipo_usuario,mensual'],
        ]);

        $reporte = $this->reportesService->generarReporte($validated);

        return response()->json([
            'success' => true,
            'data' => $reporte,
            'message' => 'Reporte generado correctamente',
        ]);
    }

    /**
     * Generate and download a PDF report.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function reportePdf(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'tipo' => ['nullable', 'in:medicina_general,enfermeria,psicologia,odontologia'],
            'id_facultad' => ['nullable', 'integer', 'exists:facultad,id'],
            'id_carrera' => ['nullable', 'integer', 'exists:carrera,id'],
            'id_genero' => ['nullable', 'integer', 'exists:identificacion_genero,id'],
            'id_tipo_usuario' => ['nullable', 'integer', 'exists:tipo_usuario,id'],
            'agrupar_por' => ['nullable', 'in:facultad,carrera,genero,tipo_usuario,mensual'],
        ]);

        $reporte = $this->reportesService->generarReporte($validated);
        $tipo = $validated['tipo'] ?? 'medicina_general';
        $fechaDesde = $validated['fecha_desde'] ?? now()->startOfYear()->format('Y-m-d');
        $fechaHasta = $validated['fecha_hasta'] ?? now()->endOfYear()->format('Y-m-d');

        $doctor = Auth::user();
        $doctorName = $doctor->name;
        $tipoLabel = ReportesService::getTipoLabel($tipo);

        // Generate HTML for PDF
        $html = View::make('pdf.reporte', [
            'reporte' => $reporte,
            'doctorName' => $doctorName,
            'tipoLabel' => $tipoLabel,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'filtros' => $validated,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->render();

        // Instantiate Dompdf
        $dompdf = new Dompdf();

        // Load HTML content
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Generate filename
        $filename = "reporte_{$tipo}_{$fechaDesde}_{$fechaHasta}.pdf";

        // Return PDF response
        return response()->streamDownload(
            function () use ($dompdf) {
                echo $dompdf->output();
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Get catalog data for filter dropdowns.
     *
     * @return JsonResponse
     */
    public function catalogos(): JsonResponse
    {
        $catalogos = $this->reportesService->getCatalogos();

        return response()->json([
            'success' => true,
            'data' => $catalogos,
            'message' => 'Catálogos recuperados correctamente',
        ]);
    }
}

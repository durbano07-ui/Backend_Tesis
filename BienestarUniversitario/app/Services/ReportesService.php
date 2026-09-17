<?php

namespace App\Services;

use App\Models\ParteDiarioEnfermeria;
use App\Models\ParteDiarioMedicina;
use App\Models\ParteDiarioOdontologia;
use App\Models\ParteDiarioPsicologia;

class ReportesService
{
    /**
     * Table mapping for parte diario types.
     */
    private const TIPO_TABLE_MAP = [
        'medicina_general' => ['table' => 'parte_diario_medicina', 'model' => ParteDiarioMedicina::class],
        'enfermeria'       => ['table' => 'parte_diario_enfermeria', 'model' => ParteDiarioEnfermeria::class],
        'psicologia'       => ['table' => 'parte_diario_psicologia', 'model' => ParteDiarioPsicologia::class],
        'odontologia'      => ['table' => 'parte_diario_odontologia', 'model' => ParteDiarioOdontologia::class],
    ];

    /**
     * Generate a statistical report based on filters.
     *
     * @param array $filtros
     * @return array
     */
    public function generarReporte(array $filtros): array
    {
        $tipo = $filtros['tipo'] ?? 'medicina_general';
        $fechaDesde = $filtros['fecha_desde'] ?? now()->startOfYear()->format('Y-m-d');
        $fechaHasta = $filtros['fecha_hasta'] ?? now()->endOfYear()->format('Y-m-d');

        $tipoConfig = self::TIPO_TABLE_MAP[$tipo] ?? self::TIPO_TABLE_MAP['medicina_general'];
        $tableName = $tipoConfig['table'];
        $tableAlias = 'pd';

        // Get all records for this doctor's parte diario within date range
        $records = $this->queryRecords($tipoConfig['model'], $tableAlias, $tableName, $filtros, $fechaDesde, $fechaHasta);

        // Calculate totals
        $totales = [
            'total_atenciones' => $records->count(),
            'pacientes_unicos' => $records->pluck('id_usuario_paciente')->unique()->count(),
        ];

        // Calculate groupings - all dimensions
        $result = [
            'totales' => $totales,
            'por_facultad' => $this->groupByFacultad($records),
            'por_carrera' => $this->groupByCarrera($records),
            'por_genero' => $this->groupByGenero($records),
            'por_tipo_usuario' => $this->groupByTipoUsuario($records),
            'por_mes' => $this->groupByMes($records, $fechaDesde, $fechaHasta),
        ];

        return $result;
    }

    /**
     * Query records with joins for filtering.
     */
    private function queryRecords(string $modelClass, string $tableAlias, string $tableName, array $filtros, string $fechaDesde, string $fechaHasta)
    {
        // Use raw query to handle the joins properly
        $query = \DB::table($tableName, $tableAlias)
            ->select([
                "{$tableAlias}.*",
                'f.nombre as facultad_nombre',
                'c.nombre as carrera_nombre',
                'tu.nombre as tipo_usuario_nombre',
                'ig.nombre as genero_nombre',
                'uec.id_facultad',
                'uec.id_carrera',
                'uec.id_tipo_usuario',
                'dac.id_genero',
            ])
            ->leftJoin('usuario_estudia_carrera as uec', function ($join) use ($tableAlias) {
                $join->on('uec.id_usuario', '=', "{$tableAlias}.id_usuario_paciente");
            })
            ->leftJoin('facultad as f', 'f.id', '=', 'uec.id_facultad')
            ->leftJoin('carrera as c', 'c.id', '=', 'uec.id_carrera')
            ->leftJoin('tipo_usuario as tu', 'tu.id', '=', 'uec.id_tipo_usuario')
            ->leftJoin('datos_autopercepcion_ciudadana as dac', 'dac.id_usuario', '=', "{$tableAlias}.id_usuario_paciente")
            ->leftJoin('identificacion_genero as ig', 'ig.id', '=', 'dac.id_genero')
            ->whereBetween("{$tableAlias}.fecha", [$fechaDesde, $fechaHasta]);

        // Apply optional filters
        if (!empty($filtros['id_facultad'])) {
            $query->where('uec.id_facultad', $filtros['id_facultad']);
        }
        if (!empty($filtros['id_carrera'])) {
            $query->where('uec.id_carrera', $filtros['id_carrera']);
        }
        if (!empty($filtros['id_genero'])) {
            $query->where('dac.id_genero', $filtros['id_genero']);
        }
        if (!empty($filtros['id_tipo_usuario'])) {
            $query->where('uec.id_tipo_usuario', $filtros['id_tipo_usuario']);
        }

        return $query->get();
    }

    /**
     * Group records by faculty.
     */
    private function groupByFacultad($records): array
    {
        $grouped = $records->groupBy('id_facultad')->map(function ($group) {
            return [
                'facultad' => $group->first()->facultad_nombre ?? 'Sin asignar',
                'cantidad' => $group->count(),
            ];
        })->values()->toArray();

        usort($grouped, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return $grouped;
    }

    /**
     * Group records by career.
     */
    private function groupByCarrera($records): array
    {
        $grouped = $records->groupBy('id_carrera')->map(function ($group) {
            return [
                'carrera' => $group->first()->carrera_nombre ?? 'Sin asignar',
                'facultad' => $group->first()->facultad_nombre ?? 'Sin asignar',
                'cantidad' => $group->count(),
            ];
        })->values()->toArray();

        usort($grouped, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return $grouped;
    }

    /**
     * Group records by gender.
     */
    private function groupByGenero($records): array
    {
        $grouped = $records->groupBy('id_genero')->map(function ($group) {
            return [
                'genero' => $group->first()->genero_nombre ?? 'No especificado',
                'cantidad' => $group->count(),
            ];
        })->values()->toArray();

        usort($grouped, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return $grouped;
    }

    /**
     * Group records by user type.
     */
    private function groupByTipoUsuario($records): array
    {
        $grouped = $records->groupBy('id_tipo_usuario')->map(function ($group) {
            return [
                'tipo_usuario' => $group->first()->tipo_usuario_nombre ?? 'No especificado',
                'cantidad' => $group->count(),
            ];
        })->values()->toArray();

        usort($grouped, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return $grouped;
    }

    /**
     * Group records by month.
     */
    private function groupByMes($records, string $fechaDesde, string $fechaHasta): array
    {
        // Generate all months in range
        $months = [];
        $current = new \DateTime($fechaDesde);
        $end = new \DateTime($fechaHasta);

        // Ensure we start from the first day of the month
        $current->modify('first day of this month');

        while ($current <= $end) {
            $monthKey = $current->format('Y-m');
            $months[$monthKey] = 0;
            $current->modify('+1 month');
        }

        // Count records per month
        foreach ($records as $record) {
            $date = new \DateTime($record->fecha);
            $monthKey = $date->format('Y-m');
            if (isset($months[$monthKey])) {
                $months[$monthKey]++;
            }
        }

        // Convert to array format sorted by month
        $result = [];
        ksort($months);
        foreach ($months as $month => $cantidad) {
            $result[] = [
                'mes' => $month,
                'cantidad' => $cantidad,
            ];
        }

        return $result;
    }

    /**
     * Get catalog data for filter dropdowns.
     */
    public function getCatalogos(): array
    {
        return [
            'facultades' => \App\Models\Facultad::orderBy('nombre')->get(['id', 'nombre']),
            'carreras' => \App\Models\Carrera::with('facultad')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'id_facultad']),
            'generos' => \App\Models\IdentificacionGenero::orderBy('nombre')->get(['id', 'nombre']),
            'tipos_usuario' => \App\Models\TipoUsuario::orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    /**
     * Get report type label for display.
     */
    public static function getTipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'medicina_general' => 'Medicina General',
            'enfermeria'       => 'Enfermería',
            'psicologia'       => 'Psicología',
            'odontologia'      => 'Odontología',
            default            => 'Desconocido',
        };
    }
}

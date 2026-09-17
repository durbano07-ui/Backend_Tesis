<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ParteDiarioEnfermeria;
use App\Models\ParteDiarioMedicina;
use App\Models\ParteDiarioPsicologia;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get user statistics for admin.
     */
    public function getUserStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('activo', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        // Count by role
        $usersByRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total' => $totalUsers,
            'total_users' => $totalUsers,
            'activos' => $activeUsers,
            'active_users' => $activeUsers,
            'inactivos' => $inactiveUsers,
            'inactive_users' => $inactiveUsers,
            'por_rol' => $usersByRole,
        ];
    }

    /**
     * Get doctor statistics for coordinator and admin.
     */
    public function getDoctoresStats(string $fechaDesde, string $fechaHasta): array
    {
        $dateFilter = " BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'";

        // Get counts from all parts diarios
        $medicina = DB::table('parte_diario_medicina')
            ->select('id_usuario_doctor', DB::raw('COUNT(*) as total_atenciones'), DB::raw('COUNT(DISTINCT id_usuario_paciente) as unique_patients'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_doctor');

        $enfermeria = DB::table('parte_diario_enfermeria')
            ->select('id_usuario_doctor', DB::raw('COUNT(*) as total_atenciones'), DB::raw('COUNT(DISTINCT id_usuario_paciente) as unique_patients'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_doctor');

        $psicologia = DB::table('parte_diario_psicologia')
            ->select('id_usuario_doctor', DB::raw('COUNT(*) as total_atenciones'), DB::raw('COUNT(DISTINCT id_usuario_paciente) as unique_patients'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_doctor');

        $odontologia = DB::table('parte_diario_odontologia')
            ->select('id_usuario_doctor', DB::raw('COUNT(*) as total_atenciones'), DB::raw('COUNT(DISTINCT id_usuario_paciente) as unique_patients'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_doctor');

        // Union all
        $combined = DB::table(DB::raw("({$medicina->toSql()} UNION ALL {$enfermeria->toSql()} UNION ALL {$psicologia->toSql()} UNION ALL {$odontologia->toSql()}) as combined"))
            ->mergeBindings($medicina)
            ->mergeBindings($enfermeria)
            ->mergeBindings($psicologia)
            ->mergeBindings($odontologia)
            ->select(
                'id_usuario_doctor',
                DB::raw('SUM(total_atenciones) as total_atenciones'),
                DB::raw('SUM(unique_patients) as unique_patients')
            )
            ->groupBy('id_usuario_doctor')
            ->orderByDesc('total_atenciones')
            ->get();

        // Get doctor details
        $doctorIds = $combined->pluck('id_usuario_doctor')->filter();
        $doctors = User::whereIn('id', $doctorIds)
            ->with(['datosIdentificacion', 'roles'])
            ->get()
            ->keyBy('id');

        return $combined->map(function ($stat) use ($doctors) {
            $doctor = $doctors->get($stat->id_usuario_doctor);
            $identificacion = $doctor?->datosIdentificacion;

            $nombre = $doctor?->name ?? 'Unknown';
            if ($identificacion) {
                $nombre = trim(
                    ($identificacion->primer_nombre ?? '') . ' ' .
                    ($identificacion->segundo_nombre ?? '') . ' ' .
                    ($identificacion->apellido_paterno ?? '') . ' ' .
                    ($identificacion->apellido_materno ?? '')
                );
            }

            $roles = $doctor?->roles->pluck('name')->toArray() ?? [];
            $rol = !empty($roles) ? $roles[0] : 'personal_salud';

            return [
                'id_usuario' => $stat->id_usuario_doctor,
                'doctor_nombre' => $nombre ?: $doctor?->name ?? 'Unknown',
                'doctor_rol' => $rol,
                'email' => $doctor?->email,
                'total_atenciones' => (int) $stat->total_atenciones,
                'cantidad_atenciones' => (int) $stat->total_atenciones,
                'unique_patients' => (int) $stat->unique_patients,
            ];
        })->values()->toArray();
    }

    /**
     * Get most frequent students (patients) for coordinator and admin.
     */
    public function getEstudiantesFrecuentes(string $fechaDesde, string $fechaHasta, int $limite = 20): array
    {
        $dateFilter = " BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'";

        // Get all patient visits from all parts diarios
        $medicina = DB::table('parte_diario_medicina')
            ->select('id_usuario_paciente', DB::raw('COUNT(*) as visit_count'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_paciente');

        $enfermeria = DB::table('parte_diario_enfermeria')
            ->select('id_usuario_paciente', DB::raw('COUNT(*) as visit_count'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_paciente');

        $psicologia = DB::table('parte_diario_psicologia')
            ->select('id_usuario_paciente', DB::raw('COUNT(*) as visit_count'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_paciente');

        $odontologia = DB::table('parte_diario_odontologia')
            ->select('id_usuario_paciente', DB::raw('COUNT(*) as visit_count'))
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('id_usuario_paciente');

        // Combine all visits
        $allVisits = DB::table(DB::raw("({$medicina->toSql()} UNION ALL {$enfermeria->toSql()} UNION ALL {$psicologia->toSql()} UNION ALL {$odontologia->toSql()}) as combined"))
            ->mergeBindings($medicina)
            ->mergeBindings($enfermeria)
            ->mergeBindings($psicologia)
            ->mergeBindings($odontologia)
            ->select('id_usuario_paciente', DB::raw('SUM(visit_count) as total_visits'))
            ->groupBy('id_usuario_paciente')
            ->orderByDesc('total_visits')
            ->limit($limite)
            ->get();
        // Get patient details
        $patientIds = $allVisits->pluck('id_usuario_paciente')->filter();
        $patients = User::whereIn('id', $patientIds)
            ->with('datosIdentificacion')
            ->get()
            ->keyBy('id');

        return $allVisits->map(function ($stat) use ($patients) {
            $patient = $patients->get($stat->id_usuario_paciente);
            $identificacion = $patient?->datosIdentificacion;

            $nombre = 'Unknown';
            $cedula = null;
            if ($identificacion) {
                $nombre = trim(
                    ($identificacion->primer_nombre ?? '') . ' ' .
                    ($identificacion->segundo_nombre ?? '') . ' ' .
                    ($identificacion->apellido_paterno ?? '') . ' ' .
                    ($identificacion->apellido_materno ?? '')
                );
                $cedula = $identificacion->numero_cedula;
            }

            return [
                'id_usuario' => $stat->id_usuario_paciente,
                'nombre' => $nombre ?: $patient?->name ?? 'Unknown',
                'paciente_nombre' => $nombre ?: $patient?->name ?? 'Unknown',
                'cedula' => $cedula,
                'paciente_cedula' => $cedula,
                'email' => $patient?->email,
                'total_visitas' => (int) $stat->total_visits,
                'cantidad_atenciones' => (int) $stat->total_visits,
            ];
        })->values()->toArray();
    }

    /**
     * Get attentions grouped by time period for coordinator and admin.
     */
    public function getAtencionesPorTiempo(string $fechaDesde, string $fechaHasta, string $agrupar = 'dia'): array
    {
        $dateFilter = " BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'";
        $driver = DB::connection()->getDriverName();

        // Determine date format based on grouping and driver
        $dateFormat = match ($agrupar) {
            'semana' => ($driver === 'sqlite' ? '%Y-%W' : '%Y-%u'),
            'mes'    => '%Y-%m',
            default  => '%Y-%m-%d',
        };

        $dateSelectExpr = $driver === 'sqlite'
            ? "strftime('{$dateFormat}', fecha)"
            : "DATE_FORMAT(fecha, '{$dateFormat}')";

        $results = [];

        // Medicina
        $medicinaData = DB::table('parte_diario_medicina')
            ->select(
                DB::raw("{$dateSelectExpr} as period"),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();

        // Enfermeria
        $enfermeriaData = DB::table('parte_diario_enfermeria')
            ->select(
                DB::raw("{$dateSelectExpr} as period"),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();

        // Psicologia
        $psicologiaData = DB::table('parte_diario_psicologia')
            ->select(
                DB::raw("{$dateSelectExpr} as period"),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();

        // Odontologia
        $odontologiaData = DB::table('parte_diario_odontologia')
            ->select(
                DB::raw("{$dateSelectExpr} as period"),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw("fecha {$dateFilter}")
            ->groupBy('period')
            ->get()
            ->pluck('count', 'period')
            ->toArray();

        // Merge all periods
        $allPeriods = array_unique(array_merge(
            array_keys($medicinaData),
            array_keys($enfermeriaData),
            array_keys($psicologiaData),
            array_keys($odontologiaData)
        ));
        sort($allPeriods);

        foreach ($allPeriods as $period) {
            $results[] = [
                'periodo' => $period,
                'medicina' => $medicinaData[$period] ?? 0,
                'enfermeria' => $enfermeriaData[$period] ?? 0,
                'psicologia' => $psicologiaData[$period] ?? 0,
                'odontologia' => $odontologiaData[$period] ?? 0,
                'total' => ($medicinaData[$period] ?? 0) + ($enfermeriaData[$period] ?? 0) + ($psicologiaData[$period] ?? 0) + ($odontologiaData[$period] ?? 0),
            ];
        }

        return $results;
    }

    /**
     * Get recent audit logs for admin.
     */
    public function getAuditsRecientes(int $limite = 50): array
    {
        return AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_email' => $log->user?->email,
                    'action' => $log->action,
                    'model_type' => $log->model_type,
                    'model_id' => $log->model_id,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent security events for admin.
     */
    public function getSecurityEventsRecientes(int $limite = 50): array
    {
        return SecurityLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'ip_address' => $log->ip_address,
                    'event_type' => $log->event_type,
                    'description' => $log->description,
                    'user_id' => $log->user_id,
                    'user_email' => $log->user?->email,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get summary of attentions by type for coordinator and admin.
     */
    public function getResumenAtenciones(string $fechaDesde, string $fechaHasta): array
    {
        $dateFilter = " BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'";

        $medicinaCount = DB::table('parte_diario_medicina')
            ->whereRaw("fecha {$dateFilter}")
            ->count();

        $enfermeriaCount = DB::table('parte_diario_enfermeria')
            ->whereRaw("fecha {$dateFilter}")
            ->count();

        $psicologiaCount = DB::table('parte_diario_psicologia')
            ->whereRaw("fecha {$dateFilter}")
            ->count();

        $odontologiaCount = DB::table('parte_diario_odontologia')
            ->whereRaw("fecha {$dateFilter}")
            ->count();

        $total = $medicinaCount + $enfermeriaCount + $psicologiaCount + $odontologiaCount;

        // Count unique patients across all tables
        $medicinaPatients = DB::table('parte_diario_medicina')
            ->whereRaw("fecha {$dateFilter}")
            ->pluck('id_usuario_paciente');

        $enfermeriaPatients = DB::table('parte_diario_enfermeria')
            ->whereRaw("fecha {$dateFilter}")
            ->pluck('id_usuario_paciente');

        $psicologiaPatients = DB::table('parte_diario_psicologia')
            ->whereRaw("fecha {$dateFilter}")
            ->pluck('id_usuario_paciente');

        $odontologiaPatients = DB::table('parte_diario_odontologia')
            ->whereRaw("fecha {$dateFilter}")
            ->pluck('id_usuario_paciente');

        $uniquePatientsCount = $medicinaPatients
            ->merge($enfermeriaPatients)
            ->merge($psicologiaPatients)
            ->merge($odontologiaPatients)
            ->unique()
            ->count();

        return [
            'medicina' => $medicinaCount,
            'enfermeria' => $enfermeriaCount,
            'psicologia' => $psicologiaCount,
            'odontologia' => $odontologiaCount,
            'total' => $total,
            'total_atenciones' => $total,
            'pacientes_unicos' => $uniquePatientsCount,
            'porcentajes' => [
                'medicina' => $total > 0 ? round(($medicinaCount / $total) * 100, 2) : 0,
                'enfermeria' => $total > 0 ? round(($enfermeriaCount / $total) * 100, 2) : 0,
                'psicologia' => $total > 0 ? round(($psicologiaCount / $total) * 100, 2) : 0,
                'odontologia' => $total > 0 ? round(($odontologiaCount / $total) * 100, 2) : 0,
            ],
        ];
    }}

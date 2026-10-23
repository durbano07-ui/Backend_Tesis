<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CitasMedicas\StoreCitaMedicaRequest;
use App\Http\Requests\CitasMedicas\UpdateCitaMedicaRequest;
use App\Models\CitaMedica;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Notifications\CitaMedicaNotification;
use Illuminate\Support\Facades\Log;

class CitasMedicasController extends Controller
{
    /**
     * Horarios disponibles: 8:00-12:00 y 14:00-17:00, intervalos de 30 min
     */
    private const AVAILABLE_SLOTS = [
        '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
    ];

    /**
     * Roles de doctor que todos pueden agendar
     */
    private const ROLES_ABIERTOS = ['medico_general', 'psicologo', 'odontologo'];

    /**
     * Tipos de usuario que pueden agendar con medico ocupacional (Personal UEB)
     */
    private const TIPOS_USUARIO_PUEDE_AGENDAR_OCUPACIONAL = ['Docente', 'Administrativo', 'Código de Trabajo'];

    /**
     * Tipos de usuario que pueden agendar en clinica general (Estudiantes)
     */
    private const TIPOS_USUARIO_PUEDE_AGENDAR_CLINICA_GENERAL = ['Estudiante'];

    /**
     * Verificar si un usuario puede agendar con medico ocupacional
     */
    private function puedeAgendarOcupacional($user): bool
    {
        if ($user->estudioCarrera && $user->estudioCarrera->tipoUsuario) {
            return in_array($user->estudioCarrera->tipoUsuario->nombre, self::TIPOS_USUARIO_PUEDE_AGENDAR_OCUPACIONAL);
        }
        return false;
    }

    /**
     * Verificar si un usuario puede agendar con médicos de clínica general (estudiantes)
     */
    private function puedeAgendarClinicaGeneral($user): bool
    {
        if ($user->estudioCarrera && $user->estudioCarrera->tipoUsuario) {
            return in_array($user->estudioCarrera->tipoUsuario->nombre, self::TIPOS_USUARIO_PUEDE_AGENDAR_CLINICA_GENERAL);
        }
        return true;
    }

    /**
     * Listar mis citas como paciente
     */
    public function misCitas(Request $request): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $query = CitaMedica::where('id_usuario_paciente', $user->id)
            ->with(['doctor.datosIdentificacion']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        return response()->json([
            'data' => $citas,
            'message' => 'Mis citas retrieved',
        ]);
    }

    /**
     * Listar citas donde soy doctor
     */
    public function citasDoctor(Request $request): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $query = CitaMedica::where('id_usuario_doctor', $user->id)
            ->with(['paciente.datosIdentificacion']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        $citas = $query->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'data' => $citas,
            'message' => 'Citas del doctor retrieved',
        ]);
    }

    /**
     * Ver slots disponibles para un doctor en una fecha específica
     */
    public function disponibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_doctor' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date'],
        ]);

        $doctor = User::findOrFail($request->id_usuario_doctor);
        $fecha = Carbon::parse($request->fecha);

        // Solo lunes a viernes
        if ($fecha->isWeekend()) {
            return response()->json([
                'data' => [
                    'fecha' => $request->fecha,
                    'dia_semana' => $fecha->dayName,
                    'slots_disponibles' => [],
                    'slots_tomados' => [],
                ],
                'message' => 'No hay atención los fines de semana',
            ]);
        }

        // Obtener fecha y hora actual
        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $nowTimeStr = $now->format('H:i');
        $isToday = ($fecha->toDateString() === $todayStr);

        // Obtener citas existentes para ese doctor en esa fecha
        $citasExistentes = CitaMedica::where('id_usuario_doctor', $doctor->id)
            ->where('fecha', $fecha->toDateString())
            ->whereNotIn('estado', ['cancelada'])
            ->pluck('hora_inicio')
            ->toArray();

        // Filtrar slots disponibles (no tomados y no transcurridos si es hoy)
        $slotsDisponibles = array_filter(self::AVAILABLE_SLOTS, function ($slot) use ($citasExistentes, $isToday, $nowTimeStr) {
            if (in_array($slot, $citasExistentes)) {
                return false;
            }
            if ($isToday && $slot <= $nowTimeStr) {
                return false;
            }
            return true;
        });

        $slotsTomados = array_filter(self::AVAILABLE_SLOTS, function ($slot) use ($citasExistentes) {
            return in_array($slot, $citasExistentes);
        });

        return response()->json([
            'data' => [
                'fecha' => $request->fecha,
                'dia_semana' => $fecha->dayName,
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                ],
                'slots_disponibles' => array_values($slotsDisponibles),
                'slots_tomados' => array_values($slotsTomados),
                'todos_los_slots' => self::AVAILABLE_SLOTS,
            ],
            'message' => 'Disponibilidad retrieved',
        ]);
    }

    /**
     * Verificar si un doctor tiene un rol específico y el paciente puede agendar
     */
    public function verificarAcceso(Request $request): JsonResponse
    {
        $request->validate([
            'rol_doctor' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $rolDoctor = $request->rol_doctor;

        // Si es medico ocupacional, verificar que el usuario sea personal universitario
        if ($rolDoctor === 'medico_ocupacional') {
            if (!$this->puedeAgendarOcupacional($user)) {
                return response()->json([
                    'data' => [
                        'acceder' => false,
                        'rol_doctor' => $rolDoctor,
                        'mensaje' => 'La atención con médico ocupacional está destinada exclusivamente a personal docente, administrativo o de servicios.',
                    ],
                    'message' => 'Access denied',
                ], 403);
            }
        }

        // Si es clinica general (médico general, psicólogo, odontólogo), verificar que sea estudiante
        if (in_array($rolDoctor, self::ROLES_ABIERTOS)) {
            if (!$this->puedeAgendarClinicaGeneral($user)) {
                return response()->json([
                    'data' => [
                        'acceder' => false,
                        'rol_doctor' => $rolDoctor,
                        'mensaje' => 'La atención con médicos generales, psicólogos y odontólogos está dirigida exclusivamente a estudiantes. El personal institucional debe solicitar atención con Medicina Ocupacional.',
                    ],
                    'message' => 'Access denied',
                ], 403);
            }
        }

        return response()->json([
            'data' => [
                'acceder' => true,
                'rol_doctor' => $rolDoctor,
            ],
            'message' => 'Access granted',
        ]);
    }

    /**
     * Listar doctores disponibles por rol
     */
    public function doctoresPorRol(Request $request): JsonResponse
    {
        $request->validate([
            'rol' => ['required', 'string', 'in:medico_general,psicologo,odontologo,medico_ocupacional'],
        ]);

        $rol = $request->rol;
        $user = Auth::user();

        // Verificar acceso a medico ocupacional
        if ($rol === 'medico_ocupacional') {
            if (!$this->puedeAgendarOcupacional($user)) {
                return response()->json([
                    'data' => [],
                    'message' => 'El servicio de medicina ocupacional está destinado exclusivamente al personal docente, administrativo y de servicios.',
                ]);
            }
        }

        // Verificar acceso a clinica general para estudiantes
        if (in_array($rol, self::ROLES_ABIERTOS)) {
            if (!$this->puedeAgendarClinicaGeneral($user)) {
                return response()->json([
                    'data' => [],
                    'message' => 'La atención con este especialista está reservada exclusivamente para estudiantes.',
                ]);
            }
        }

        $doctores = User::whereHas('roles', function ($q) use ($rol) {
            $q->where('name', $rol);
        })
        ->where('activo', true)
        ->with(['datosIdentificacion', 'cargoMedico'])
        ->get();

        return response()->json([
            'data' => $doctores,
            'message' => 'Doctores retrieved',
        ]);
    }

    /**
     * Crear una nueva cita médica
     */
    public function store(StoreCitaMedicaRequest $request): JsonResponse
    {
        $user = Auth::user();
        $doctor = User::findOrFail($request->id_usuario_doctor);
        $rolDoctor = $request->rol_doctor;

        // Verificar acceso a medico ocupacional
        if ($rolDoctor === 'medico_ocupacional') {
            if (!$this->puedeAgendarOcupacional($user)) {
                return response()->json([
                    'message' => 'Solo el personal docente, administrativo o de código de trabajo puede agendar citas con medicina ocupacional.',
                ], 403);
            }
        }

        // Verificar acceso a clinica general
        if (in_array($rolDoctor, self::ROLES_ABIERTOS)) {
            if (!$this->puedeAgendarClinicaGeneral($user)) {
                return response()->json([
                    'message' => 'La atención con médicos generales, odontólogos y psicólogos está reservada para estudiantes. El personal universitario debe agendar con Medicina Ocupacional.',
                ], 403);
            }
        }

        // Verificar que el doctor tenga el rol que dice
        if (!$doctor->hasRole($rolDoctor)) {
            return response()->json([
                'message' => 'El usuario seleccionado no tiene el rol de ' . $rolDoctor,
            ], 422);
        }

        $fecha = Carbon::parse($request->fecha);

        // Verificar que no sea fin de semana
        if ($fecha->isWeekend()) {
            return response()->json([
                'message' => 'No se pueden agendar citas los fines de semana',
            ], 422);
        }

        // Verificar que la fecha no haya transcurrido
        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $nowTimeStr = $now->format('H:i');

        if ($fecha->toDateString() < $todayStr) {
            return response()->json([
                'message' => 'No se pueden agendar citas en fechas pasadas.',
            ], 422);
        }

        if ($fecha->toDateString() === $todayStr && $request->hora_inicio <= $nowTimeStr) {
            return response()->json([
                'message' => 'No se puede agendar una cita médica en un horario que ya transcurrió el día de hoy.',
            ], 422);
        }

        // Verificar que la hora sea válida
        $horaInicio = $request->hora_inicio;
        if (!in_array($horaInicio, self::AVAILABLE_SLOTS)) {
            return response()->json([
                'message' => 'Hora no válida. Horarios disponibles: 8:00-12:00 y 14:00-17:00 en intervalos de 30 minutos',
            ], 422);
        }

        // Calcular hora fin (30 minutos después)
        $horaFin = Carbon::parse($horaInicio)->addMinutes(30)->format('H:i');

        // Verificar que el doctor no tenga otra cita en ese horario
        $existeCita = CitaMedica::where('id_usuario_doctor', $doctor->id)
            ->where('fecha', $fecha->toDateString())
            ->where('hora_inicio', $horaInicio)
            ->whereNotIn('estado', ['cancelada'])
            ->exists();

        if ($existeCita) {
            return response()->json([
                'message' => 'Este horario ya está ocupado. Por favor seleccione otro',
            ], 422);
        }

        // Verificar que el paciente no tenga otra cita pendiente en ese mismo horario (en cualquier área)
        $pacienteOcupado = CitaMedica::where('id_usuario_paciente', $user->id)
            ->where('fecha', $fecha->toDateString())
            ->where('hora_inicio', $horaInicio)
            ->pendientesVigentes()
            ->exists();

        if ($pacienteOcupado) {
            return response()->json([
                'message' => 'Ya tienes una cita agendada en este horario en otra área. Elige otro horario.',
            ], 422);
        }

        // Una sola cita pendiente por área: para agendar otra, la anterior debe completarse,
        // cancelarse o haber pasado su margen de atención ("No asistió")
        $pendienteMismaArea = CitaMedica::where('id_usuario_paciente', $user->id)
            ->where('rol_doctor', $rolDoctor)
            ->pendientesVigentes()
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->first();

        if ($pendienteMismaArea) {
            $areas = [
                'medico_general' => 'Medicina General',
                'psicologo' => 'Psicología',
                'odontologo' => 'Odontología',
                'medico_ocupacional' => 'Medicina Ocupacional',
            ];
            return response()->json([
                'message' => 'Ya tienes una cita pendiente en ' . ($areas[$rolDoctor] ?? 'esta área')
                    . ' para el ' . $pendienteMismaArea->fecha->format('d/m/Y') . ' a las ' . substr($pendienteMismaArea->hora_inicio, 0, 5)
                    . '. Podrás agendar otra cuando esa cita se complete o si la cancelas.',
            ], 422);
        }

        $cita = CitaMedica::create([
            'id_usuario_paciente' => $user->id,
            'id_usuario_doctor' => $doctor->id,
            'rol_doctor' => $rolDoctor,
            'fecha' => $fecha->toDateString(),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => 'programada',
            'motivo' => $request->motivo,
        ]);

        try {
            $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']);
            $pIdent = $cita->paciente?->datosIdentificacion;
            $pacienteNombre = $pIdent ? trim("{$pIdent->primer_nombre} {$pIdent->apellido_paterno}") : ($cita->paciente?->name ?? "Paciente");
            $dIdent = $cita->doctor?->datosIdentificacion;
            $doctorNombre = $dIdent ? trim("{$dIdent->primer_nombre} {$dIdent->apellido_paterno}") : ($cita->doctor?->name ?? "Especialista");

            // Notificar al Doctor
            $doctor->notify(new CitaMedicaNotification(
                'nueva_cita',
                'Nueva Cita Agendada',
                "El paciente {$pacienteNombre} agendó cita para el {$cita->fecha->format('d/m/Y')} a las {$cita->hora_inicio}.",
                $cita
            ));

            // Notificar al Paciente
            $user->notify(new CitaMedicaNotification(
                'cita_agendada',
                'Cita Reservada con Éxito',
                "Tu cita con {$doctorNombre} ha sido reservada para el {$cita->fecha->format('d/m/Y')} a las {$cita->hora_inicio}.",
                $cita
            ));
        } catch (\Throwable $e) {
            Log::error("Error al notificar cita médica agendada: " . $e->getMessage());
        }

        return response()->json([
            'data' => $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']),
            'message' => 'Cita agendada exitosamente',
        ], 201);
    }

    /**
     * Cancelar una cita
     */
    public function cancelar(int $id): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $cita = CitaMedica::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('id_usuario_paciente', $user->id)
                  ->orWhere('id_usuario_doctor', $user->id);
            })
            ->first();

        if (!$cita) {
            return response()->json(['message' => 'Cita not found'], 404);
        }

        if (!in_array($cita->estado, CitaMedica::ESTADOS_PENDIENTES, true)) {
            return response()->json([
                'message' => 'Solo se pueden cancelar citas programadas o confirmadas',
            ], 422);
        }

        $cita->update(['estado' => 'cancelada']);

        try {
            $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']);
            $pIdent = $cita->paciente?->datosIdentificacion;
            $pacienteNombre = $pIdent ? trim("{$pIdent->primer_nombre} {$pIdent->apellido_paterno}") : ($cita->paciente?->name ?? "Paciente");
            $dIdent = $cita->doctor?->datosIdentificacion;
            $doctorNombre = $dIdent ? trim("{$dIdent->primer_nombre} {$dIdent->apellido_paterno}") : ($cita->doctor?->name ?? "Especialista");

            if ($user->id === $cita->id_usuario_paciente) {
                // Canceló el paciente -> notificar al doctor
                $cita->doctor?->notify(new CitaMedicaNotification(
                    'cita_cancelada',
                    'Cita Cancelada por Paciente',
                    "El paciente {$pacienteNombre} canceló su cita del {$cita->fecha->format('d/m/Y')} a las {$cita->hora_inicio}.",
                    $cita
                ));
            } else {
                // Canceló el doctor -> notificar al paciente
                $cita->paciente?->notify(new CitaMedicaNotification(
                    'cita_cancelada',
                    'Cita Cancelada',
                    "Tu cita con {$doctorNombre} del {$cita->fecha->format('d/m/Y')} a las {$cita->hora_inicio} ha sido cancelada.",
                    $cita
                ));
            }
        } catch (\Throwable $e) {
            Log::error("Error al notificar cancelación de cita: " . $e->getMessage());
        }

        return response()->json([
            'data' => $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']),
            'message' => 'Cita cancelada exitosamente',
        ]);
    }

    /**
     * Confirmar una cita (solo el doctor)
     */
    public function confirmar(int $id): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $cita = CitaMedica::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$cita) {
            return response()->json(['message' => 'Cita not found'], 404);
        }

        if ($cita->estado !== 'programada') {
            return response()->json([
                'message' => 'Solo se pueden confirmar citas en estado programada',
            ], 422);
        }

        $cita->update(['estado' => 'confirmada']);

        return response()->json([
            'data' => $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']),
            'message' => 'Cita confirmada exitosamente',
        ]);
    }

    /**
     * Completar una cita (solo el doctor)
     */
    public function completar(Request $request, int $id): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $cita = CitaMedica::where('id', $id)
            ->where('id_usuario_doctor', $user->id)
            ->first();

        if (!$cita) {
            return response()->json(['message' => 'Cita not found'], 404);
        }

        if (!in_array($cita->estado, CitaMedica::ESTADOS_PENDIENTES, true)) {
            $mensajes = [
                'completada' => 'Esta cita ya fue completada',
                'cancelada' => 'No se puede completar una cita cancelada',
                'no_asistio' => 'El margen de ' . CitaMedica::HORAS_MARGEN_ATENCION . ' horas para atender esta cita terminó; quedó registrada como "No asistió"',
            ];
            return response()->json([
                'message' => $mensajes[$cita->estado] ?? 'Esta cita no se puede completar',
            ], 422);
        }

        // Solo el día de la cita (el paciente puede llegar antes) y hasta el fin del margen
        if (Carbon::now()->greaterThanOrEqualTo($cita->finMargenAtencion())) {
            $cita->update(['estado' => 'no_asistio']);
            return response()->json([
                'message' => 'El margen de ' . CitaMedica::HORAS_MARGEN_ATENCION . ' horas para atender esta cita terminó; quedó registrada como "No asistió"',
            ], 422);
        }
        if ($cita->fecha->toDateString() > Carbon::today()->toDateString()) {
            return response()->json([
                'message' => 'La cita es para el ' . $cita->fecha->format('d/m/Y') . '; solo se puede completar ese día',
            ], 422);
        }

        $cita->update([
            'estado' => 'completada',
            'notas_doctor' => $request->input('notas_doctor') ?: $cita->notas_doctor,
        ]);

        try {
            $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']);
            $dIdent = $cita->doctor?->datosIdentificacion;
            $doctorNombre = $dIdent ? trim("{$dIdent->primer_nombre} {$dIdent->apellido_paterno}") : ($cita->doctor?->name ?? "Especialista");

            // Notificar al Paciente
            $cita->paciente?->notify(new CitaMedicaNotification(
                'atencion_completada',
                'Atención Médica Completada',
                "Tu atención médica con {$doctorNombre} ha sido completada satisfactoriamente.",
                $cita
            ));
        } catch (\Throwable $e) {
            Log::error("Error al notificar atención completada: " . $e->getMessage());
        }

        return response()->json([
            'data' => $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']),
            'message' => 'Cita completada exitosamente',
        ]);
    }

    /**
     * Sincronización automática de citas médicas al atender un paciente.
     * Si existe una cita previa programada/confirmada para el paciente y doctor, la marca como 'completada'.
     * Si NO existe (atención directa sin agendamiento), crea automáticamente una cita completada para la fecha de hoy.
     */
    public static function syncAutoCita(int $doctorId, int $patientId, ?string $doctorRole = null, ?string $motivo = null): ?CitaMedica
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        CitaMedica::marcarInasistencias();

        // 1. Buscar la cita pendiente de HOY del paciente con este profesional cuyo margen de
        //    atención sigue abierto. No se toman citas de otros días: una cita futura no debe
        //    darse por atendida, y una pasada ya quedó como "no_asistio".
        $citaExistente = CitaMedica::where('id_usuario_paciente', $patientId)
            ->where('id_usuario_doctor', $doctorId)
            ->whereIn('estado', CitaMedica::ESTADOS_PENDIENTES)
            ->whereDate('fecha', $today)
            // margen abierto: inicio + margen > ahora
            ->where('hora_inicio', '>', $now->copy()->subHours(CitaMedica::HORAS_MARGEN_ATENCION)->format('H:i'))
            ->orderBy('hora_inicio', 'asc')
            ->first();

        if ($citaExistente) {
            $citaExistente->update([
                'estado' => 'completada',
                'notas_doctor' => $motivo ?? $citaExistente->notas_doctor ?? 'Atención médica completada',
            ]);
            return $citaExistente;
        }

        // 2. Si el paciente no agendó previamente (atención directa), crear y completar cita automáticamente
        if (!$doctorRole) {
            $doctor = User::find($doctorId);
            $doctorRole = $doctor ? ($doctor->getRoleNames()->first() ?? 'medico_general') : 'medico_general';
        }

        return CitaMedica::create([
            'id_usuario_paciente' => $patientId,
            'id_usuario_doctor' => $doctorId,
            'rol_doctor' => $doctorRole,
            'fecha' => $today,
            'hora_inicio' => $now->format('H:i'),
            'hora_fin' => $now->copy()->addMinutes(30)->format('H:i'),
            'estado' => 'completada',
            'motivo' => $motivo ?? 'Atención médica directa (Sin agendamiento previo)',
            'notas_doctor' => 'Atención registrada directamente por el profesional médico',
            'confirmada_por_paciente' => true,
            'fecha_confirmacion' => $now,
        ]);
    }

    /**
     * Endpoint API para registrar atención y auto-completar/crear cita
     */
    public function registrarAtencionAutoCita(Request $request): JsonResponse
    {
        $request->validate([
            'id_usuario_paciente' => ['required', 'integer', 'exists:users,id'],
            'rol_doctor' => ['nullable', 'string'],
            'motivo' => ['nullable', 'string'],
        ]);

        $doctor = Auth::user();
        $patientId = (int)$request->id_usuario_paciente;

        $cita = self::syncAutoCita($doctor->id, $patientId, $request->rol_doctor, $request->motivo);

        return response()->json([
            'data' => $cita ? $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']) : null,
            'message' => 'Sincronización de cita completada exitosamente',
        ]);
    }

    /**
     * Confirmar asistencia del paciente a su cita
     */
    public function confirmarAsistencia(int $id): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();
        $cita = CitaMedica::where('id', $id)
            ->where('id_usuario_paciente', $user->id)
            ->first();

        if (!$cita) {
            return response()->json(['message' => 'Cita not found'], 404);
        }

        if (!in_array($cita->estado, CitaMedica::ESTADOS_PENDIENTES, true)) {
            return response()->json([
                'message' => 'Solo se puede confirmar asistencia a citas programadas o confirmadas',
            ], 422);
        }

        $cita->update([
            'confirmada_por_paciente' => true,
            'fecha_confirmacion' => now(),
        ]);

        try {
            $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']);
            $pIdent = $cita->paciente?->datosIdentificacion;
            $pacienteNombre = $pIdent ? trim("{$pIdent->primer_nombre} {$pIdent->apellido_paterno}") : ($cita->paciente?->name ?? "Paciente");

            // Notificar al Doctor
            $cita->doctor?->notify(new CitaMedicaNotification(
                'asistencia_confirmada',
                'Asistencia Confirmada',
                "El paciente {$pacienteNombre} confirmó su asistencia para la cita del {$cita->fecha->format('d/m/Y')} a las {$cita->hora_inicio}.",
                $cita
            ));
        } catch (\Throwable $e) {
            Log::error("Error al notificar confirmación de asistencia: " . $e->getMessage());
        }

        return response()->json([
            'data' => $cita->load(['doctor.datosIdentificacion', 'paciente.datosIdentificacion']),
            'message' => 'Asistencia confirmada exitosamente',
        ]);
    }

    /**
     * Ver detalle de una cita
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $cita = CitaMedica::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('id_usuario_paciente', $user->id)
                  ->orWhere('id_usuario_doctor', $user->id);
            })
            ->with(['doctor.datosIdentificacion', 'paciente.datosIdentificacion'])
            ->first();

        if (!$cita) {
            return response()->json(['message' => 'Cita not found'], 404);
        }

        return response()->json([
            'data' => $cita,
            'message' => 'Cita retrieved',
        ]);
    }

    /**
     * Calendario del doctor - vista completa
     */
    public function calendarioDoctor(Request $request): JsonResponse
    {
        CitaMedica::marcarInasistencias();
        $user = Auth::user();

        $request->validate([
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'anio' => ['nullable', 'integer', 'min:2024', 'max:2030'],
        ]);

        $mes = $request->mes ?? Carbon::now()->month;
        $anio = $request->anio ?? Carbon::now()->year;

        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();

        $citas = CitaMedica::where('id_usuario_doctor', $user->id)
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->whereNotIn('estado', ['cancelada'])
            ->with(['paciente.datosIdentificacion'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        // Agrupar por fecha
        $calendario = $citas->groupBy(function ($cita) {
            return $cita->fecha->format('Y-m-d');
        });

        return response()->json([
            'data' => [
                'mes' => $mes,
                'anio' => $anio,
                'total_citas' => $citas->count(),
                'citas_por_fecha' => $calendario,
            ],
            'message' => 'Calendario del doctor retrieved',
        ]);
    }

    /**
     * Listar doctores disponibles (todos los roles)
     */
    public function doctoresDisponibles(): JsonResponse
    {
        $user = Auth::user();
        $isStaff = $this->puedeAgendarOcupacional($user);

        $doctores = User::whereHas('roles', function ($q) use ($isStaff) {
            if ($isStaff) {
                $q->where('name', 'medico_ocupacional');
            } else {
                $q->whereIn('name', ['medico_general', 'psicologo', 'odontologo']);
            }
        })
        ->where('activo', true)
        ->with(['roles', 'datosIdentificacion', 'cargoMedico'])
        ->get();

        return response()->json([
            'data' => $doctores,
            'message' => 'Doctores disponibles retrieved',
        ]);
    }
}
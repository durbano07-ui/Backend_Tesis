<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso para usuarios que no son personal de salud (pacientes).
 *
 * OWASP ASVS 4.0.3:
 *  - V4.1.5 Los controles de acceso fallan de forma segura: se deniega todo lo que no
 *           esté explícitamente permitido para un paciente.
 *  - V4.2.1 Protección contra IDOR: en el historial clínico se fuerza el ID del propio
 *           paciente, sin importar el valor que envíe el cliente.
 *  - V4.1.3 Mínimo privilegio: el paciente solo accede a sus datos y a la gestión de sus citas.
 */
class RestrictPatientAccess
{
    public const STAFF_ROLES = [
        'administrador',
        'medico_coordinador',
        'medico_general',
        'medico_ocupacional',
        'psicologo',
        'odontologo',
        'enfermero',
    ];

    /**
     * Rutas permitidas a un paciente: [método(s), patrón sobre la ruta sin "api/v1/"].
     */
    private const PATIENT_ALLOWED = [
        ['*', '#^auth/(logout|password)$#'],
        ['*', '#^user-profile(/.*)?$#'],          // el controlador siempre usa el usuario autenticado
        ['*', '#^notificaciones(/.*)?$#'],        // ídem
        ['GET', '#^citas-medicas/(mis-citas|disponibilidad|doctores-disponibles|verificar-acceso)$#'],
        ['GET', '#^citas-medicas/\d+$#'],         // el controlador comprueba que la cita sea suya
        ['POST', '#^citas-medicas$#'],
        ['PATCH', '#^citas-medicas/\d+/(cancelar|confirmar-asistencia)$#'],
    ];

    /**
     * Historial clínico de solo lectura que ve el propio paciente en su portal.
     * El filtro id_usuario_paciente se fuerza al usuario autenticado.
     */
    private const PATIENT_OWN_HISTORY = '#^(medicina-general/(historial-evolucion|parte-diario|signos-vitales|examen-fisico|antecedentes)'
        . '|psicologia/(historial-evolucion|parte-diario)'
        . '|odontologia/(historial-evolucion|parte-diario-odontologia)'
        . '|enfermeria/(signos-vitales|parte-diario))$#';

    public function handle(Request $request, Closure $next): Response
    {
        // Corre en el grupo "api", antes que auth:sanctum: se pide el guard de Sanctum.
        // Sin usuario se deja pasar: las rutas protegidas responderán 401 en auth:sanctum.
        $user = $request->user('sanctum');

        if (!$user || $user->hasAnyRole(self::STAFF_ROLES)) {
            return $next($request);
        }

        $path = preg_replace('#^api/v1/?#', '', $request->path());
        $method = $request->method();

        foreach (self::PATIENT_ALLOWED as [$methods, $pattern]) {
            if (($methods === '*' || $methods === $method) && preg_match($pattern, $path)) {
                return $next($request);
            }
        }

        if ($method === 'GET' && preg_match(self::PATIENT_OWN_HISTORY, $path)) {
            $request->query->set('id_usuario_paciente', $user->id);
            $request->merge(['id_usuario_paciente' => $user->id]);
            return $next($request);
        }

        // Su propia cuenta (el portal la consulta para mostrar sus datos)
        if ($method === 'GET' && $path === 'users/' . $user->id) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mínimo privilegio entre el personal de salud (OWASP ASVS 4.0.3 — V4.1.3, V4.2.1).
 *
 * Cada módulo clínico pertenece a su profesional: solo él crea, modifica y consulta el
 * expediente completo. El resto del personal clínico solo puede LEER el resumen que necesita
 * la vista integrada del paciente (evolución, parte diario, signos vitales y perfil).
 * Así, por ejemplo, las fichas psicológicas detalladas solo las ve Psicología.
 *
 * Las reglas se derivaron de las llamadas reales del frontend de cada rol.
 * Los pacientes ya están restringidos antes por RestrictPatientAccess.
 */
class RestrictStaffModuleAccess
{
    private const CLINICAL = ['enfermero', 'medico_general', 'medico_ocupacional', 'psicologo', 'odontologo'];
    private const MANAGEMENT = ['administrador', 'medico_coordinador'];

    /** Dueño de cada módulo clínico: acceso completo. */
    private const MODULE_OWNERS = [
        'enfermeria' => ['enfermero'],
        'medicina-general' => ['medico_general'],
        'medicina-ocupacional' => ['medico_ocupacional'],
        'odontologia' => ['odontologo'],
        'psicologia' => ['psicologo'],
    ];

    /** Resumen clínico que cualquier profesional puede leer (GET) de otro módulo. */
    private const SHARED_READ = '#^(enfermeria|medicina-general|medicina-ocupacional|odontologia|psicologia)/'
        . '(historial-evolucion|parte-diario|parte-diario-odontologia|signos-vitales)(/.*)?$'
        . '|^(medicina-general|psicologia|odontologia)/(pacientes/\d+/perfil|patient/\d+/blood-type)$#';

    /**
     * Excepciones puntuales: [métodos, patrón, roles].
     */
    private const EXTRA = [
        // Configuración del campus de trabajo (modal que ve todo el personal clínico)
        [['GET', 'POST'], '#^medicina-ocupacional/(campus|usuario-lugar-de-trabajo)$#', self::CLINICAL],
        // Medicina Ocupacional registra la atención de medicina general de sus trabajadores
        [['POST'], '#^medicina-general/(signos-vitales|diagnosticos|parte-diario)$#', ['medico_ocupacional']],
        // Farmacia
        [['*'], '#^farmacia(/.*)?$#', ['enfermero', 'medico_general']],
        [['GET'], '#^farmacia-search(/.*)?$#', self::CLINICAL],
        [['GET'], '#^presentaciones(/.*)?$#', ['enfermero', 'medico_general']],
        [['*'], '#^presentaciones(/.*)?$#', ['enfermero']],
        [['*'], '#^receta-farmacia(/.*)?$#', ['enfermero']],
        // Reportes institucionales
        [['GET'], '#^reportes(/.*)?$#', self::MANAGEMENT],
    ];

    /** Prefijos sujetos a esta política (los demás tienen su propio control). */
    private const GOVERNED = '#^(enfermeria|medicina-general|medicina-ocupacional|odontologia|psicologia|farmacia|farmacia-search|presentaciones|receta-farmacia|reportes)(/|$)#';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return $next($request);
        }

        $roles = $user->getRoleNames()->all();
        // Pacientes: ya filtrados por RestrictPatientAccess
        if (!array_intersect($roles, array_merge(self::CLINICAL, self::MANAGEMENT))) {
            return $next($request);
        }

        $path = preg_replace('#^api/v1/?#', '', $request->path());
        if (!preg_match(self::GOVERNED, $path)) {
            return $next($request);
        }

        return $this->allowed($roles, $request->method(), $path)
            ? $next($request)
            : response()->json(['message' => 'Forbidden'], 403);
    }

    private function allowed(array $roles, string $method, string $path): bool
    {
        $module = explode('/', $path)[0];

        if (isset(self::MODULE_OWNERS[$module]) && array_intersect($roles, self::MODULE_OWNERS[$module])) {
            return true;
        }

        if ($method === 'GET' && array_intersect($roles, self::CLINICAL) && preg_match(self::SHARED_READ, $path)) {
            return true;
        }

        foreach (self::EXTRA as [$methods, $pattern, $allowedRoles]) {
            if ((in_array('*', $methods, true) || in_array($method, $methods, true))
                && preg_match($pattern, $path)
                && array_intersect($roles, $allowedRoles)) {
                return true;
            }
        }

        return false;
    }
}

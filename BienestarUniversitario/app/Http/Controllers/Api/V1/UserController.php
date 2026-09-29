<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\DatosIdentificacion;
use App\Models\Role;
use App\Models\User;
use App\Models\UsuarioEstudiaCarrera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = User::with(['roles', 'datosIdentificacion', 'estudioCarrera']);

        // Coordinator can only see non-admin users
        if ($user->hasRole('medico_coordinador') && !$user->hasRole('administrador')) {
            $coordinatorRoles = ['enfermero', 'medico_general', 'psicologo', 'odontologo', 'medico_ocupacional'];
            $query->whereHas('roles', function ($q) use ($coordinatorRoles) {
                $q->whereIn('name', $coordinatorRoles);
            });
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->role($request->role);
        }

        // Filter by active status if provided
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20);
        $users = $query->paginate($perPage);

        $this->recordAuditLog(
            $request->user(),
            'users_listed',
            $users->first() ?? new User(),
            null,
            ['filters' => $request->all()],
            $request
        );

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Only administrador and medico_coordinador can create users
        if (!$request->user()->hasAnyRole(['administrador', 'medico_coordinador'])) {
            return response()->json([
                'message' => 'No tienes permisos para crear usuarios',
            ], 403);
        }

        // administrador can assign any role
        // medico_coordinador can only assign medical staff roles (NOT administrador or medico_coordinador)
        if ($request->user()->hasRole('medico_coordinador')) {
            $allowedRoles = ['enfermero', 'medico_general', 'psicologo', 'odontologo', 'medico_ocupacional', 'paciente'];
            $forbiddenRoles = array_diff($request->roles, $allowedRoles);
            if (count($forbiddenRoles) > 0) {
                return response()->json([
                    'message' => 'El coordinador médico no puede asignar roles de administrador o coordinador',
                ], 422);
            }
        }

        // administrador cannot assign administrador role to others (self-protection)
        if (in_array('administrador', $request->roles) && !$request->user()->hasRole('administrador')) {
            return response()->json([
                'message' => 'Solo un administrador puede crear otros administradores',
            ], 422);
        }

        // Generate secure temporary password
        $tempPassword = $this->generateSecurePassword();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $tempPassword, // Will be hashed by model
            'clave_temporal' => $tempPassword,
            'activo' => true,
            'must_change_password' => true,
        ]);

        if ($request->has('id_tipo_usuario')) {
            UsuarioEstudiaCarrera::updateOrCreate(
                ['id_usuario' => $user->id],
                ['id_tipo_usuario' => $request->id_tipo_usuario]
            );
        }

        // Assign roles
        $roles = Role::whereIn('name', $request->roles)->get();
        $user->assignRole($roles);

        $this->recordAuditLog(
            $request->user(),
            'staff_account_created',
            $user,
            null,
            [
                'created_by_user_id' => $request->user()->id,
                'new_user_id' => $user->id,
                'roles' => $request->roles,
            ],
            $request
        );

        // In production, send email with credentials here

        return response()->json([
            'user' => new UserResource($user->load('roles')),
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): UserResource
    {
        $user->load('roles');
        return new UserResource($user);
    }

    /**
     * Search patient by cedula (identification number) or name with role-based scoping.
     */
    public function searchByCedula(Request $request): JsonResponse
    {
        $request->validate([
            'cedula' => ['nullable', 'string', 'max:50'],
            'query' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:50'],
            'term' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $request->cedula ?? $request->query('query') ?? $request->query('search') ?? $request->query('term') ?? '';
        $doctor = Auth::user() ?? $request->user();

        $identificacionesQuery = DatosIdentificacion::with([
            'user.estudioCarrera.tipoUsuario',
            'user.estudioCarrera.facultad',
            'user.estudioCarrera.carrera',
            'user.cargoMedico',
            'user.lugaresDeTrabajo',
            'user.roles'
        ]);

        if (!empty($query)) {
            $identificacionesQuery->where(function ($q) use ($query) {
                $q->where('numero_cedula', 'like', "%{$query}%")
                    ->orWhere('primer_nombre', 'like', "%{$query}%")
                    ->orWhere('segundo_nombre', 'like', "%{$query}%")
                    ->orWhere('apellido_paterno', 'like', "%{$query}%")
                    ->orWhere('apellido_materno', 'like', "%{$query}%");
            });
        }

        // Scope filter based on doctor role and request parameters
        $ambito = $request->get('tipo_paciente') ?? $request->get('ambito');

        if ($doctor && $doctor->hasRole('medico_ocupacional')) {
            // Medico Ocupacional: Solo personal de la institucion (Docentes, Administrativos, Codigo de Trabajo: id_tipo_usuario IN [3, 4, 5])
            $identificacionesQuery->whereHas('user.estudioCarrera', function ($q) {
                $q->whereIn('id_tipo_usuario', [3, 4, 5]);
            });
        } elseif ($doctor && $doctor->hasAnyRole(['medico_general', 'odontologo', 'psicologo', 'enfermero'])) {
            // Medicos Clinicos Generales / Estudiantiles: Solo estudiantes (id_tipo_usuario = 2 o pacientes que no son personal institucional)
            $identificacionesQuery->whereHas('user', function ($q) {
                $q->whereDoesntHave('roles', function ($rq) {
                    $rq->whereIn('name', ['enfermero', 'medico_general', 'psicologo', 'odontologo', 'medico_ocupacional', 'medico_coordinador', 'administrador']);
                })
                ->where(function ($sub) {
                    $sub->whereHas('estudioCarrera', function ($eq) {
                        $eq->where('id_tipo_usuario', 2);
                    })
                    ->orWhereDoesntHave('estudioCarrera');
                });
            });
        } elseif ($ambito === 'ocupacional') {
            $identificacionesQuery->whereHas('user.estudioCarrera', function ($q) {
                $q->whereIn('id_tipo_usuario', [3, 4, 5]);
            });
        } elseif ($ambito === 'estudiante') {
            $identificacionesQuery->whereHas('user', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('estudioCarrera', function ($eq) {
                        $eq->where('id_tipo_usuario', 2);
                    })
                    ->orWhereDoesntHave('estudioCarrera');
                });
            });
        }

        $identificaciones = $identificacionesQuery->take(30)->get();

        $results = [];
        foreach ($identificaciones as $identificacion) {
            $user = $identificacion->user;
            if (!$user) {
                continue; // Evitamos crash si es un registro huerfano sin usuario asociado
            }

            $tipoUsuario = $user->estudioCarrera?->tipoUsuario?->nombre ?? 'Estudiante';
            $idTipoUsuario = $user->estudioCarrera?->id_tipo_usuario ?? 2;
            $nombres = trim(($identificacion->primer_nombre ?? '') . ' ' . ($identificacion->segundo_nombre ?? ''));
            $apellidos = trim(($identificacion->apellido_paterno ?? '') . ' ' . ($identificacion->apellido_materno ?? ''));
            $nombreCompleto = trim("{$nombres} {$apellidos}") ?: ($user->name ?? 'Sin nombre');

            $edad = null;
            if ($identificacion->fecha_nacimiento) {
                try {
                    $edad = Carbon::parse($identificacion->fecha_nacimiento)->age;
                } catch (\Throwable $e) {}
            }

            $puestoTrabajo = $user->cargoMedico?->detalle_cargo ?? ($tipoUsuario !== 'Estudiante' ? "Personal {$tipoUsuario}" : 'Estudiante');
            $lugarTrabajo = $user->lugaresDeTrabajo->first()?->nombre;
            $areaTrabajo = $lugarTrabajo ?? ($user->estudioCarrera?->facultad?->nombre ?? ($user->estudioCarrera?->carrera?->nombre ?? 'Universidad Estatal de Bolívar'));

            $results[] = [
                'id_usuario' => $user->id,
                'id' => $user->id,
                'cedula' => $identificacion->numero_cedula,
                'numero_cedula' => $identificacion->numero_cedula,
                'nombre_completo' => $nombreCompleto,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'primer_nombre' => $identificacion->primer_nombre,
                'segundo_nombre' => $identificacion->segundo_nombre,
                'apellido_paterno' => $identificacion->apellido_paterno,
                'apellido_materno' => $identificacion->apellido_materno,
                'email' => $user->email,
                'tipo_usuario' => $tipoUsuario,
                'id_tipo_usuario' => $idTipoUsuario,
                'puestoTrabajo' => $puestoTrabajo,
                'areaTrabajo' => $areaTrabajo,
                'edad' => $edad,
            ];
        }

        return response()->json([
            'data' => $results,
            'message' => empty($results) ? 'No se encontraron pacientes para este criterio o ámbito médico.' : 'Pacientes encontrados',
        ], 200);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        // Only administrador and medico_coordinador can update users
        if (!$request->user()->hasAnyRole(['administrador', 'medico_coordinador'])) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar usuarios',
            ], 403);
        }

        $oldRoles = $user->getRoleNames()->toArray();

        if ($request->has('name')) {
            $user->name = $request->name;

            $nameParts = preg_split('/\s+/', trim($request->name));
            $pNombre = $nameParts[0] ?? '';
            $pApellido = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

            $identificacion = $user->datosIdentificacion;
            if ($identificacion) {
                $identificacion->primer_nombre = $pNombre;
                $identificacion->apellido_paterno = $pApellido;
                $identificacion->save();
            } else {
                DatosIdentificacion::create([
                    'id_usuario' => $user->id,
                    'primer_nombre' => $pNombre,
                    'apellido_paterno' => $pApellido,
                    'numero_cedula' => '020' . str_pad($user->id, 7, '0', STR_PAD_LEFT),
                ]);
            }
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('id_tipo_usuario')) {
            UsuarioEstudiaCarrera::updateOrCreate(
                ['id_usuario' => $user->id],
                ['id_tipo_usuario' => $request->id_tipo_usuario]
            );
        }
        if ($request->filled('password')) {
            $user->password = $request->password;
            $user->clave_temporal = $request->password;
            $user->must_change_password = true;
        }
        $user->save();

        if ($request->has('roles')) {
            // Validate - administrador role can only be assigned by administrador
            if (in_array('administrador', $request->roles) && !$request->user()->hasRole('administrador')) {
                return response()->json([
                    'message' => 'Solo un administrador puede asignar roles de administrador',
                ], 422);
            }

            // medico_coordinador can only assign medical staff roles
            if ($request->user()->hasRole('medico_coordinador')) {
                $allowedRoles = ['enfermero', 'medico_general', 'psicologo', 'odontologo', 'medico_ocupacional', 'paciente'];
                $forbiddenRoles = array_diff($request->roles, $allowedRoles);
                if (count($forbiddenRoles) > 0) {
                    return response()->json([
                        'message' => 'El coordinador médico no puede asignar roles de administrador o coordinador',
                    ], 422);
                }
            }

            // Sync new roles
            $user->syncRoles($request->roles);

            $this->recordAuditLog(
                $request->user(),
                'user_roles_updated',
                $user,
                ['roles' => $oldRoles],
                ['roles' => $request->roles],
                $request
            );
        }

        $user->load('roles');
        return new UserResource($user);
    }

    /**
     * Disable the specified user.
     */
    public function disable(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasRole(['medico_coordinador', 'administrador'])) {
            return response()->json([
                'message' => 'No tienes permisos para deshabilitar usuarios',
            ], 403);
        }

        // Coordinators cannot disable admins
        if ($request->user()->hasRole('medico_coordinador') && $user->hasRole('administrador')) {
            return response()->json([
                'message' => 'No puedes deshabilitar a un administrador',
            ], 403);
        }

        $user->activo = false;
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();

        $this->recordAuditLog(
            $request->user(),
            'user_disabled',
            $user,
            ['activo' => true],
            ['activo' => false, 'disabled_by' => $request->user()->id],
            $request
        );

        return response()->json([
            'message' => 'Usuario deshabilitado exitosamente',
        ]);
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, User $user, string $role): JsonResponse
    {
        if (!$request->user()->hasRole(['medico_coordinador', 'administrador'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot remove own coordinator role
        if ($request->user()->id === $user->id && $role === 'medico_coordinador') {
            return response()->json([
                'message' => 'Cannot remove your own coordinator role',
            ], 422);
        }

        // Check if user has the role
        if (!$user->hasRole($role)) {
            return response()->json([
                'message' => 'User does not have this role',
            ], 422);
        }

        $user->removeRole($role);

        $this->recordAuditLog(
            $request->user(),
            'role_removed',
            $user,
            null,
            [
                'target_user_id' => $user->id,
                'role' => $role,
                'removed_by' => $request->user()->id,
            ],
            $request
        );

        return response()->json([
            'message' => 'Role removed successfully',
        ]);
    }

    /**
     * Enable a previously disabled user.
     * PUT /api/v1/users/{user}/enable
     */
    public function enable(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasRole('administrador')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        if ($user->activo) {
            return response()->json([
                'message' => 'User is already active',
            ], 422);
        }

        $user->activo = true;
        $user->save();

        $this->recordAuditLog(
            $request->user(),
            'user_enabled',
            $user,
            ['activo' => false],
            ['activo' => true, 'enabled_by' => $request->user()->id],
            $request
        );

        return response()->json([
            'message' => 'User enabled successfully',
        ]);
    }

    /**
     * Reset user password to temporary.
     * POST /api/v1/users/{user}/reset-password
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->hasAnyRole(['medico_coordinador', 'administrador'])) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        // Generate secure temporary password
        $tempPassword = $this->generateSecurePassword();

        $user->password = $tempPassword;
        $user->clave_temporal = $tempPassword;
        $user->must_change_password = true;
        $user->save();

        // Revoke all existing tokens
        $user->tokens()->delete();

        $this->recordAuditLog(
            $currentUser,
            'password_reset_by_admin',
            $user,
            null,
            [
                'target_user_id' => $user->id,
                'reset_by' => $currentUser->id,
            ],
            $request
        );

        return response()->json([
            'message' => 'Password reset successfully',
            'temporary_password' => $tempPassword,
        ]);
    }

    /**
     * Patient registration by medical personnel.
     * Generates temporary password, sets must_change_password=true, sends notification email.
     */
    public function registerPatient(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        // Check if user is medical staff or admin
        $allowedStaffRoles = [
            'enfermero', 'medico_general', 'psicologo', 'odontologo',
            'medico_ocupacional', 'medico_coordinador', 'administrador'
        ];

        if (!$currentUser->hasAnyRole($allowedStaffRoles)) {
            return response()->json([
                'message' => 'No tienes permisos para registrar pacientes',
            ], 403);
        }

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'cedula' => ['required', 'string', 'max:20', 'unique:datos_identificacion,numero_cedula'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'id_tipo_usuario' => ['nullable'],
            'tipo' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
        ]);

        // Separate full name intelligently
        $fullName = trim($request->nombre_completo);
        $nameParts = preg_split('/\s+/', $fullName);
        $primerNombre = '';
        $segundoNombre = '';
        $apellidoPaterno = '';
        $apellidoMaterno = '';

        if (count($nameParts) === 1) {
            $primerNombre = $nameParts[0];
            $apellidoPaterno = 'S/A';
        } elseif (count($nameParts) === 2) {
            $primerNombre = $nameParts[0];
            $apellidoPaterno = $nameParts[1];
        } elseif (count($nameParts) === 3) {
            $primerNombre = $nameParts[0];
            $segundoNombre = $nameParts[1];
            $apellidoPaterno = $nameParts[2];
        } elseif (count($nameParts) >= 4) {
            $primerNombre = $nameParts[0];
            $segundoNombre = $nameParts[1];
            $apellidoPaterno = $nameParts[2];
            $apellidoMaterno = implode(' ', array_slice($nameParts, 3));
        }

        // Determine id_tipo_usuario dynamically (2: Estudiante, 3: Docente, 4: Administrativo, 5: Código de Trabajo)
        $idTipoUsuario = 2;
        if ($request->filled('id_tipo_usuario') && is_numeric($request->id_tipo_usuario)) {
            $idTipoUsuario = (int) $request->id_tipo_usuario;
        } elseif ($request->filled('tipo')) {
            $tipoStr = strtolower(trim($request->tipo));
            if (str_contains($tipoStr, 'estudiante')) {
                $idTipoUsuario = 2;
            } elseif (str_contains($tipoStr, 'docente') || str_contains($tipoStr, 'profesor')) {
                $idTipoUsuario = 3;
            } elseif (str_contains($tipoStr, 'admin')) {
                $idTipoUsuario = 4;
            } elseif (str_contains($tipoStr, 'codigo') || str_contains($tipoStr, 'trabajo') || str_contains($tipoStr, 'servidor')) {
                $idTipoUsuario = 5;
            } elseif (str_contains($tipoStr, 'medico') || str_contains($tipoStr, 'médico')) {
                $idTipoUsuario = 1;
            }
        }

        // Generate temporary password
        $tempPassword = $this->generateSecurePassword();

        // Create user with must_change_password = true
        $user = User::create([
            'name' => $fullName,
            'email' => strtolower(trim($request->email)),
            'password' => $tempPassword, // Hashed by model cast
            'clave_temporal' => $tempPassword,
            'activo' => true,
            'must_change_password' => true,
        ]);

        // Assign 'paciente' role
        $pacienteRole = Role::where('name', 'paciente')->first();
        if ($pacienteRole) {
            $user->assignRole($pacienteRole);
        }

        // Map id_tipo_usuario to Spatie role if defined, or use specified role parameter
        $typeRoleMap = [
            2 => 'estudiante',
            3 => 'docente',
            4 => 'administrativo',
            5 => 'codigo_trabajo',
        ];

        $targetRoleName = $request->input('role') ?? ($typeRoleMap[$idTipoUsuario] ?? null);
        if ($targetRoleName) {
            $addRole = Role::where('name', $targetRoleName)->first();
            if ($addRole) {
                $user->assignRole($addRole);
            }
        }

        // Create identification record
        DatosIdentificacion::create([
            'id_usuario' => $user->id,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'numero_cedula' => trim($request->cedula),
            'fecha_nacimiento' => null,
        ]);

        // Create or update study career / user-type record
        UsuarioEstudiaCarrera::updateOrCreate(
            ['id_usuario' => $user->id],
            ['id_tipo_usuario' => $idTipoUsuario]
        );

        // Send email notification with temporary password
        try {
            $user->notify(new \App\Notifications\PatientRegisteredNotification($tempPassword, $fullName));
        } catch (\Throwable $e) {
            \Log::error('Error al enviar correo de bienvenida al paciente: ' . $e->getMessage());
        }

        $this->recordAuditLog(
            $currentUser,
            'patient_registered_by_staff',
            $user,
            null,
            [
                'created_by_user_id' => $currentUser->id,
                'patient_user_id' => $user->id,
                'email' => $user->email,
            ],
            $request
        );

        return response()->json([
            'data' => [
                'id_usuario' => $user->id,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numero_cedula' => $request->cedula,
                'nombre_completo' => $fullName,
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
            ],
            'message' => 'Paciente registrado exitosamente. Se ha enviado una contraseña temporal a su correo electrónico.',
        ], 201);
    }

    /**
     * Record an audit log entry.
     */
    private function recordAuditLog($user, string $action, $model, ?array $oldValues, ?array $newValues, Request $request): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Generate a secure password that always meets requirements:
     * - At least 1 uppercase letter
     * - At least 1 lowercase letter
     * - At least 1 number
     * - At least 1 special character (@$!%*#?&)
     * - Minimum 8 characters
     */
    private function generateSecurePassword(): string
    {
        $upper = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $lower = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $specials = ['@', '$', '!', '%', '*', '#', '?', '&'];

        // Guarantee at least one of each required type
        $password = '';
        $password .= $upper[array_rand($upper)];
        $password .= $lower[array_rand($lower)];
        $password .= $numbers[array_rand($numbers)];
        $password .= $specials[array_rand($specials)];

        // Fill the rest with random characters from all pools
        $all = array_merge($upper, $lower, $numbers, $specials);
        for ($i = 0; $i < 4; $i++) {
            $password .= $all[array_rand($all)];
        }

        // Shuffle to avoid predictable positions
        return str_shuffle($password);
    }
}
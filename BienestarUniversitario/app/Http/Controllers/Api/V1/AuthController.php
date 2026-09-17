<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Models\Role;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WelcomeNotification;

class AuthController extends Controller
{
    /**
     * Attempt to authenticate the user and return a token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $ip = $request->ip();
        $email = $request->email;

        // Check if IP or email is locked
        if (LoginAttempt::isLocked($email, $ip)) {
            $this->recordLoginAttempt($email, $ip, $request, false);
            throw ValidationException::withMessages([
                'email' => ['Account temporarily locked due to too many failed attempts. Try again later.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->handleFailedLogin($email, $ip, $request, $user);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Check if user is active
        if (!$user->activo) {
            $this->handleFailedLogin($email, $ip, $request, $user, 'Account is disabled');
            throw ValidationException::withMessages([
                'email' => ['Account is disabled'],
            ]);
        }

        // Clear any previous lockouts on successful login
        LoginAttempt::where('email', $email)
            ->orWhere('ip_address', $ip)
            ->update(['locked_until' => null]);

        // Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        $this->recordLoginAttempt($email, $ip, $request, true, $user->id);

        $this->recordAuditLog(
            $user,
            'login_success',
            $user,
            null,
            null,
            $request
        );

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
            'must_change_password' => $user->must_change_password,
        ]);
    }

    /**
     * Handle a failed login attempt.
     */
    private function handleFailedLogin(string $email, string $ip, Request $request, ?User $user, string $reason = 'Invalid credentials'): void
    {
        $this->recordLoginAttempt($email, $ip, $request, false);

        $failedAttempts = LoginAttempt::getFailedAttemptsCount($email, $ip);

        if ($failedAttempts >= 5) {
            // Lock for 15 minutes
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'success' => false,
                'locked_until' => now()->addMinutes(15),
            ]);

            SecurityLog::logLoginLocked(
                $ip,
                $user?->id,
                "Account locked after {$failedAttempts} failed login attempts. Reason: {$reason}"
            );
        }

        $this->recordAuditLog(
            $user,
            'login_failed',
            $user,
            null,
            ['reason' => $reason],
            $request
        );
    }

    /**
     * Record a login attempt.
     */
    private function recordLoginAttempt(string $email, string $ip, Request $request, bool $success, ?int $userId = null): void
    {
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'success' => $success,
        ]);
    }

    /**
     * Log the user out (invalidate the current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->recordAuditLog(
            $user,
            'logout',
            $user,
            null,
            null,
            $request
        );

        // Revoke the current token
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    /**
     * Register a new patient account.
     * Only email and password required. Name is collected later in profile setup.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => null, // Will be collected in profile setup (punto 2)
            'email' => $request->email,
            'password' => $request->password,
            'activo' => true,
            'must_change_password' => false,
        ]);

        // Assign paciente role
        $pacienteRole = Role::where('name', 'paciente')->first();
        if ($pacienteRole) {
            $user->assignRole($pacienteRole);
        }

        // Send welcome email with credentials
        $user->notify(new WelcomeNotification($request->password));

        $this->recordAuditLog(
            $user,
            'user_registered',
            $user,
            null,
            $user->getAttributes(),
            $request
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name, // Will be null initially
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
            'must_change_password' => $user->must_change_password,
        ], 201);
    }

    /**
     * Send a password reset link to the user.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a password reset token
            $token = app('auth.password.broker')->createToken($user);
            
            // Send the notification via email
            $user->notify(new ResetPasswordNotification($token));
        }

        // Always return success to prevent email enumeration
        $this->recordAuditLog(
            $user,
            'password_reset_requested',
            $user,
            null,
            null,
            $request
        );

        return response()->json([
            'message' => 'Password reset link sent',
        ]);
    }

    /**
     * Reset the user's password with a valid token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ]);

        // Validate the token and get the broker
        $broker = app('auth.password.broker');
        
        // Check if the token is valid
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !$broker->tokenExists($user, $request->token)) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 422);
        }

        // Reset the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the used token
        $broker->deleteToken($user);

        // Invalidate all Sanctum tokens
        $user->tokens()->delete();

        $this->recordAuditLog(
            $user,
            'password_reset_completed',
            $user,
            null,
            null,
            $request
        );

        return response()->json([
            'message' => 'Password reset successful',
        ]);
    }

    /**
     * Change the user's password (forced or voluntary).
     */
    public function changePassword(PasswordChangeRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->clave_temporal = null;
        $user->save();

        return response()->json([
            'message' => 'Password updated',
        ]);
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
}
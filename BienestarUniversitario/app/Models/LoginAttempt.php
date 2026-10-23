<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LoginAttempt extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_attempts';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'locked_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'locked_until' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include failed attempts.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('success', false);
    }

    /**
     * Scope a query to only include successful attempts.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('success', true);
    }

    /**
     * Scope a query to only include currently locked records.
     */
    public function scopeLocked(Builder $query): Builder
    {
        return $query->whereNotNull('locked_until')
            ->where('locked_until', '>', now());
    }

    /**
     * Indica si la cuenta (correo) está bloqueada temporalmente.
     *
     * El bloqueo es por cuenta y no por IP: en la universidad muchos usuarios comparten la
     * misma IP pública (NAT), así que los fallos de unos no deben bloquear a los demás.
     * Los ataques desde una misma IP contra varias cuentas los frena el throttle de la ruta.
     */
    public static function isAccountLocked(string $email): bool
    {
        return static::locked()->where('email', $email)->exists();
    }

    /**
     * Intentos fallidos de la cuenta en los últimos $minutes minutos. El contador vuelve a cero
     * al iniciar sesión correctamente y al terminar un bloqueo (los intentos hechos mientras
     * estaba bloqueada no cuentan).
     */
    public static function failedAttemptsForAccount(string $email, int $minutes = 15): int
    {
        $since = now()->subMinutes($minutes);

        $lastLockEnd = static::where('email', $email)->whereNotNull('locked_until')->max('locked_until');
        if ($lastLockEnd && Carbon::parse($lastLockEnd)->greaterThan($since)) {
            $since = Carbon::parse($lastLockEnd);
        }

        // Por id y no por fecha: los registros del mismo segundo quedan en el orden correcto
        $lastSuccessId = static::successful()->where('email', $email)->max('id') ?? 0;

        return static::failed()
            ->where('email', $email)
            ->whereNull('locked_until')
            ->where('created_at', '>=', $since)
            ->where('id', '>', $lastSuccessId)
            ->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
     * Check if the IP or email is currently locked.
     */
    public static function isLocked(string $email, string $ip): bool
    {
        return static::locked()
            ->where(function ($query) use ($email, $ip) {
                $query->where('email', $email)
                    ->orWhere('ip_address', $ip);
            })
            ->exists();
    }

    /**
     * Get the number of failed attempts for an email/IP since a given time.
     */
    public static function getFailedAttemptsCount(string $email, string $ip, int $minutes = 15): int
    {
        return static::failed()
            ->where(function ($query) use ($email, $ip) {
                $query->where('email', $email)
                    ->orWhere('ip_address', $ip);
            })
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'security_logs';

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
        'ip_address',
        'event_type',
        'description',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Event types constants.
     */
    public const EVENT_RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    public const EVENT_LOGIN_LOCKED = 'login_locked';
    public const EVENT_SUSPICIOUS_ACTIVITY = 'suspicious_activity';

    /**
     * Get the user that owns the security log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a rate limit exceeded event.
     */
    public static function logRateLimitExceeded(string $ip, string $description): self
    {
        return static::create([
            'ip_address' => $ip,
            'event_type' => self::EVENT_RATE_LIMIT_EXCEEDED,
            'description' => $description,
        ]);
    }

    /**
     * Log a login locked event.
     */
    public static function logLoginLocked(string $ip, ?int $userId, string $description): self
    {
        return static::create([
            'ip_address' => $ip,
            'event_type' => self::EVENT_LOGIN_LOCKED,
            'description' => $description,
            'user_id' => $userId,
        ]);
    }

    /**
     * Log suspicious activity.
     */
    public static function logSuspiciousActivity(string $ip, ?int $userId, string $description): self
    {
        return static::create([
            'ip_address' => $ip,
            'event_type' => self::EVENT_SUSPICIOUS_ACTIVITY,
            'description' => $description,
            'user_id' => $userId,
        ]);
    }
}

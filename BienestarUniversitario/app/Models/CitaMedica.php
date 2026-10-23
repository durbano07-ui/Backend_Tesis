<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CitaMedica extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['motivo', 'notas_doctor'];

    use HasFactory;

    protected $table = 'citas_medicas';

    protected $fillable = [
        'id_usuario_paciente',
        'id_usuario_doctor',
        'rol_doctor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo',
        'notas_doctor',
        'confirmada_por_paciente',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'confirmada_por_paciente' => 'boolean',
        'fecha_confirmacion' => 'datetime',
    ];

    /**
     * Horas que la cita sigue abierta después de su hora de inicio. Dentro de ese margen el
     * profesional puede atender al paciente aunque llegue tarde; pasado el margen, una cita
     * que nadie completó pasa a "no_asistio" y ya no se puede completar.
     */
    public const HORAS_MARGEN_ATENCION = 5;

    public const ESTADOS_PENDIENTES = ['programada', 'confirmada'];

    /** Se envía al frontend para mostrar hasta qué hora puede atenderse la cita. */
    protected $appends = ['fin_margen_atencion'];

    public function getFinMargenAtencionAttribute(): ?string
    {
        if (!$this->fecha || !$this->hora_inicio) {
            return null;
        }

        return $this->finMargenAtencion()->format('Y-m-d\TH:i:s');
    }

    /** Momento en que termina el margen de atención de la cita. */
    public function finMargenAtencion(): Carbon
    {
        return Carbon::parse($this->fecha->toDateString() . ' ' . $this->hora_inicio)
            ->addHours(self::HORAS_MARGEN_ATENCION);
    }

    /**
     * Marca como "no_asistio" las citas pendientes cuyo margen de atención ya terminó.
     * Se ejecuta antes de listar o modificar citas, así no depende de un cron en el servidor.
     */
    public static function marcarInasistencias(): int
    {
        // fin del margen <= ahora  <=>  inicio de la cita <= ahora - margen
        $limite = Carbon::now()->subHours(self::HORAS_MARGEN_ATENCION);

        return static::query()
            ->whereIn('estado', self::ESTADOS_PENDIENTES)
            ->where(function ($q) use ($limite) {
                $q->whereDate('fecha', '<', $limite->toDateString())
                    ->orWhere(function ($q2) use ($limite) {
                        $q2->whereDate('fecha', $limite->toDateString())
                            ->where('hora_inicio', '<=', $limite->format('H:i'));
                    });
            })
            ->update(['estado' => 'no_asistio']);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_paciente');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_doctor');
    }
}

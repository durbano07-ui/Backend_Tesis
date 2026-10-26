<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CitaMedica extends Model
{
    use \App\Models\Concerns\EncryptsHealthData;

    /** OWASP ASVS V6.1.2: datos de salud cifrados en reposo */
    protected array $encryptedHealthFields = ['motivo', 'notas_doctor'];

    use HasFactory;

    protected $table = 'citas_medicas';

    /** La cita la agendó el paciente o el personal */
    public const ORIGEN_AGENDADA = 'agendada';
    /** El profesional atendió sin cita previa: cuenta en reportes, sin notificaciones */
    public const ORIGEN_ATENCION_DIRECTA = 'atencion_directa';

    protected $fillable = [
        'id_usuario_paciente',
        'id_usuario_doctor',
        'rol_doctor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'origen',
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

    /**
     * Citas programadas o confirmadas cuyo margen de atención sigue abierto (aún pueden atenderse).
     * No depende de que marcarInasistencias() ya haya corrido.
     */
    public function scopePendientesVigentes($query)
    {
        $limite = Carbon::now()->subHours(self::HORAS_MARGEN_ATENCION);

        return $query->whereIn('estado', self::ESTADOS_PENDIENTES)
            ->where(function ($q) use ($limite) {
                $q->whereDate('fecha', '>', $limite->toDateString())
                    ->orWhere(function ($q2) use ($limite) {
                        $q2->whereDate('fecha', $limite->toDateString())
                            ->where('hora_inicio', '>', $limite->format('H:i'));
                    });
            });
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
     * Como las pantallas se actualizan solas cada pocos segundos, corre como máximo una vez por
     * minuto; completar() y syncAutoCita() revisan además el margen de cada cita por su cuenta.
     */
    public static function marcarInasistencias(): int
    {
        if (!Cache::add('citas:marcar_inasistencias', true, 60)) {
            return 0;
        }

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

<?php

namespace App\Notifications;

use App\Models\CitaMedica;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CitaMedicaNotification extends Notification
{
    use Queueable;

    public string $tipo;
    public string $titulo;
    public string $mensaje;
    public ?CitaMedica $cita;
    public array $extraData;

    public function __construct(string $tipo, string $titulo, string $mensaje, ?CitaMedica $cita = null, array $extraData = [])
    {
        $this->tipo = $tipo;
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
        $this->cita = $cita;
        $this->extraData = $extraData;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pIdent = $this->cita?->paciente?->datosIdentificacion;
        $dIdent = $this->cita?->doctor?->datosIdentificacion;

        $pacienteNombre = $pIdent 
            ? trim("{$pIdent->primer_nombre} {$pIdent->apellido_paterno}")
            : ($this->cita?->paciente?->name ?? 'Paciente');

        $doctorNombre = $dIdent
            ? trim("{$dIdent->primer_nombre} {$dIdent->apellido_paterno}")
            : ($this->cita?->doctor?->name ?? 'Especialista');

        return array_merge([
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'cita_id' => $this->cita?->id,
            'fecha_cita' => $this->cita?->fecha,
            'hora_cita' => $this->cita?->hora_inicio ? "{$this->cita->hora_inicio} - " . ($this->cita->hora_fin ?? '') : '',
            'paciente_nombre' => $pacienteNombre,
            'doctor_nombre' => $doctorNombre,
            'rol_doctor' => $this->cita?->rol_doctor ?? '',
            'motivo' => $this->cita?->motivo ?? '',
        ], $this->extraData);
    }
}

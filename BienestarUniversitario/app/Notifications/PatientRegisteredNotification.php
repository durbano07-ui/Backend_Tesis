<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se envía en el momento (sin cola): así no depende de un queue:work en el servidor y la
 * contraseña o el token no quedan guardados en texto plano en la tabla jobs.
 */
class PatientRegisteredNotification extends Notification
{
    use Queueable;

    public string $password;
    public string $patientName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, string $patientName = 'Paciente')
    {
        $this->password = $password;
        $this->patientName = $patientName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('app.frontend_url') . '/login';

        return (new MailMessage)
            ->subject('Acceso a Bienestar Universitario UEB - Clave Temporal')
            ->view('emails.patient_registered', [
                'email' => $notifiable->email,
                'password' => $this->password,
                'patientName' => $this->patientName,
                'loginUrl' => $loginUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'email' => $notifiable->email,
        ];
    }
}

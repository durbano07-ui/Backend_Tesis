<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

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
        $loginUrl = config('app.url') . '/login';

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

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('PatientRegisteredNotification failed for user: ' . ($this->notifiable?->email ?? 'unknown'), [
            'error' => $exception->getMessage(),
        ]);
    }
}

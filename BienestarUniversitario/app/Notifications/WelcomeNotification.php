<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Retry configuration.
     */
    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    /**
     * The user's plain text password (only for welcome email).
     */
    public string $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
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
            ->subject('¡Bienvenido a Bienestar Universitario UEB!')
            ->view('emails.welcome', [
                'email' => $notifiable->email,
                'password' => $this->password,
                'loginUrl' => $loginUrl,
                'headerTitle' => 'Bienestar Universitario UEB',
                'headerSubtitle' => 'Universidad Estatal de Bolívar',
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
        \Log::error('WelcomeNotification failed for user: ' . $this->notifiable?->email ?? 'unknown', [
            'error' => $exception->getMessage(),
        ]);
    }
}
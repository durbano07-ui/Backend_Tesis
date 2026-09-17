<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Retry configuration.
     */
    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * The callback URL.
     */
    public ?string $callbackUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, ?string $callbackUrl = null)
    {
        $this->token = $token;
        $this->callbackUrl = $callbackUrl;
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
        // Build the reset URL with token and email
        $resetUrl = config('app.url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
        
        return (new MailMessage)
            ->subject('Restablecer contraseña - Bienestar Universitario')
            ->view('emails.password-reset', [
                'name' => $notifiable->name ?? $notifiable->email,
                'resetUrl' => $resetUrl,
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
            'token' => $this->token,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('ResetPasswordNotification failed for token: ' . $this->token, [
            'error' => $exception->getMessage(),
        ]);
    }
}
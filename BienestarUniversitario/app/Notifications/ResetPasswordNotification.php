<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se envía en el momento (sin cola): así no depende de un queue:work en el servidor y la
 * contraseña o el token no quedan guardados en texto plano en la tabla jobs.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;


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
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
        
        return (new MailMessage)
            ->subject('Restablecer contraseña - Bienestar Universitario')
            ->view('emails.reset-password', [
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
}
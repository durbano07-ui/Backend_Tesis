<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se envía en el momento (sin cola): así no depende de un queue:work en el servidor y la
 * contraseña o el token no quedan guardados en texto plano en la tabla jobs.
 */
class WelcomeNotification extends Notification
{
    use Queueable;


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
        $loginUrl = config('app.frontend_url') . '/login';

        return (new MailMessage)
            ->subject('¡Bienvenido a Bienestar Universitario UEB!')
            ->view('emails.welcome', [
                'name' => $notifiable->name ?? $notifiable->email,
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
            'name' => $notifiable->name ?? $notifiable->email,
                'email' => $notifiable->email,
        ];
    }
}
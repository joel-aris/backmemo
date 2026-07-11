<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EmailVerificationCode extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Code de verification de votre adresse email')
            ->greeting('Bonjour,')
            ->line('Merci de vous etre inscrit sur VALIDIKA.')
            ->line('Votre code de verification par email est:')
            ->line("**{$this->code}**")
            ->line('Utilisez ce code dans l application pour activer votre compte.')
            ->line('Si vous n avez pas demande ce code, ignorez ce message.');
    }
}

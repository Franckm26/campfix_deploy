<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExistingUserWelcomeNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to CampFix')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Welcome to CampFix! Your account is available for you to use.')
            ->line('Your login email is: '.$notifiable->email)
            ->line('Your existing password has not been changed. If you do not know it, use Forgot Password on the login page to reset it.')
            ->action('Login to CampFix', rtrim(config('app.url'), '/').'/login')
            ->line('If you need help accessing your account, please contact your system administrator.');
    }
}

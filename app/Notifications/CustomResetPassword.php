<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Klik tombol di bawah untuk atur ulang password Anda:')
            ->action('Reset Password', $this->url)
            ->line('Abaikan jika Anda tidak meminta reset.');
    }
}

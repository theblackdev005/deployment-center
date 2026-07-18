<?php

namespace App\Notifications;

use App\Models\HostingerAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HostingerProblemDetected extends Notification
{
    use Queueable;

    public function __construct(private readonly HostingerAlert $alert) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Deploy Center] '.$this->alert->title)
            ->greeting('Bonjour,')
            ->line($this->alert->message)
            ->line('Compte Hostinger : '.$this->alert->account->name)
            ->action('Consulter les domaines', route('hostinger.index'))
            ->line('Cette alerte ne sera pas renvoyée tant que la situation ne change pas.');
    }
}

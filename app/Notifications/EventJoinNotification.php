<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventJoinNotification extends Notification
{
    use Queueable;

    private $fromUser;
    private $message;
    private $title;

    public function __construct($fromUser, $message, $title)
    {
        $this->fromUser = $fromUser;
        $this->message = $message;
        $this->title = $title;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => "{$this->fromUser} join " . "'" . $this->title . "'" . " event.",
            'body' => $this->message,
        ];
    }
}

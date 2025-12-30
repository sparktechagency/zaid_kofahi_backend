<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SelectedWinnerNotification extends Notification
{
    use Queueable;

    private $fromUser;
    private $message;
    private $title;
    private $place;

    public function __construct($fromUser, $message, $title, $place)
    {
        $this->fromUser = $fromUser;
        $this->message = $message;
        $this->title = $title;
        $this->place = $place;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => "You have been selected as the '{$this->place} place' from the '{$this->title}' event.",
            'is_body_use' => true,
            'body' => $this->message,
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionStatusNotification extends Notification
{
    use Queueable;
    protected $md_session;
    protected $doctor_name;
    protected $message;
    /**
     * Create a new notification instance.
     */
    public function __construct($md_session, $message)
    {
        $this->md_session = $md_session;
        $this->message = $message;
        $this->doctor_name = $md_session->doctor->user->first_name . $md_session->doctor->user->last_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => "جلستك لدى الطبيب $this->doctor_name $this->message",
            'md_session_id' => $this->md_session->id,
            'status' => $this->md_session->status,

        ];
    }
}

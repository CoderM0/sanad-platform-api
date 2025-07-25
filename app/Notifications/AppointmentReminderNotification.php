<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    use Queueable;
    protected $md_session;
    /**
     * Create a new notification instance.
     */
    public function __construct($md_session)
    {
        $this->md_session = $md_session;
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


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'لديك جلسة مجدولة غدًا في الساعة ' . $this->md_session->scheduled_at->format('H:i'),
            'session_id' => $this->md_session->id,
        ];
    }
}

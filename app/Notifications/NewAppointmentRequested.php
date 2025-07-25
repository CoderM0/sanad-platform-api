<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentRequested extends Notification
{
    protected $md_session;

    public function __construct($md_session)
    {
        $this->md_session = $md_session;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'مريض جديد طلب جلسة.',
            'appointment_id' => $this->md_session->id,
            'patient_name' => $this->md_session->patient->user->first_name ?? '',
            'scheduled_at' => $this->md_session->scheduled_at,
        ];
    }
}

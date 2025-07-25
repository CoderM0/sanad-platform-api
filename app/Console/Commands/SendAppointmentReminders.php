<?php

namespace App\Console\Commands;

use App\Models\MdSession;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->startOfDay();
        $endOfTomorrow = now()->addDay()->endOfDay();

        $md_sessions = MdSession::whereBetween('scheduled_at', [$tomorrow, $endOfTomorrow])
            ->with(['patient.user', 'doctor.user'])
            ->get();

        foreach ($md_sessions as $md_session) {

            if ($md_session->patient && $md_session->patient->user) {
                $md_session->patient->user->notify(new AppointmentReminderNotification($md_session));
            }


            if ($md_session->doctor && $md_session->doctor->user) {
                $md_session->doctor->user->notify(new AppointmentReminderNotification($md_session));
            }
        }

        $this->info("Reminders sent successfully.");
    }
}

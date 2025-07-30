<?php

namespace App\Http\Controllers;

use App\Http\Resources\MdSessionResource;
use App\Models\Doctor;
use App\Models\MdSession;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\NewAppointmentRequested;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MdSessionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',

            'scheduled_at' => 'required|date|after:now',
        ]);

        $scheduledAt = Carbon::parse($validated['scheduled_at'])->seconds(0);


        if (!in_array($scheduledAt->minute, [0, 30])) {
            return response()->json([
                'message' => 'يجب أن يكون وقت الحجز على فواصل نصف ساعة فقط (مثلاً 09:00 أو 09:30).'
            ], 422);
        }


        if (!in_array($scheduledAt->dayOfWeek, [0, 1, 2, 3, 4])) {
            return response()->json([
                'message' => 'المواعيد متاحة فقط من الأحد إلى الخميس.'
            ], 422);
        }


        $hour = $scheduledAt->hour;
        if ($hour < 9 || $hour >= 15) {
            return response()->json([
                'message' => 'أوقات العمل من الساعة 09:00 صباحاً حتى 03:00 مساءً فقط.'
            ], 422);
        }


        $exists = MdSession::where('doctor_id', $validated['doctor_id'])
            ->where('scheduled_at', $scheduledAt)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'الطبيب مشغول في هذا الموعد، الرجاء اختيار وقت آخر.'
            ], 422);
        }


        $md_session = MdSession::create([
            'patient_id' => Auth::user()->patient->id,
            'doctor_id' => $validated['doctor_id'],
            'scheduled_at' => $scheduledAt,
            'status' => "pending"
        ]);
        $doctor = Doctor::find($validated['doctor_id']);
        $doctor->user->notify(new NewAppointmentRequested($md_session));
        return response()->json([
            'message' => 'تم إرسال الطلب بنجاح، بانتظار موافقة الطبيب.',
            'appointment' => $md_session,
        ]);
    }

    public function free_times(Request $request, $doctorId)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($validated['date']);


        if (!in_array($date->dayOfWeek, [0, 1, 2, 3, 4])) {
            return response()->json([
                'message' => 'هذا اليوم خارج أوقات العمل (الأحد إلى الخميس).'
            ], 422);
        }

        $startTime = $date->copy()->setTime(9, 0);
        $endTime = $date->copy()->setTime(15, 0);

        $allSlots = [];
        while ($startTime < $endTime) {
            $allSlots[] = $startTime->format('H:i');
            $startTime->addMinutes(30);
        }


        $busySlots = MdSession::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date)
            ->pluck('scheduled_at')
            ->map(function ($slot) {
                return Carbon::parse($slot)->format('H:i');
            })
            ->toArray();


        $freeSlots = array_values(array_diff($allSlots, $busySlots));

        return response()->json([
            'date' => $date->toDateString(),
            'available_slots' => $freeSlots,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\MdSessionResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\RatingResource;
use App\Models\MdSession;
use App\Models\Patient;
use App\Notifications\SessionStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function my_sessions()
    {
        $doctor = Auth::user()->doctor;
        $md_session = $doctor->md_sessions;

        return MdSessionResource::collection($md_session);
    }
    public function accept_session($session_id)
    {
        $doctor = Auth::user()->doctor;
        $md_session = MdSession::find($session_id);
        if ($doctor->id != $md_session->doctor_id) {
            abort(403, 'not authorized to accept this session ');
        } else {
            $md_session->update(['status' => 'accepted']);
            $patient = Patient::find($md_session->patient_id);
            $patient->user->notify(new SessionStatusNotification($md_session, "تم قبولها"));
            return response()->json(['message' => 'تم قبول الموعد بنجاح ']);
        }
    }
    public function decline_session($session_id)
    {
        $doctor = Auth::user()->doctor;
        $md_session = MdSession::find($session_id);
        if ($doctor->id != $md_session->doctor_id) {
            abort(403, 'not authorized to accept this session ');
        } else {
            $md_session->update(['status' => 'declined']);
            $patient = Patient::find($md_session->patient_id);
            $patient->user->notify(new SessionStatusNotification($md_session, "تم رفضها"));
            return response()->json(['message' => 'تم رفض الموعد والغاؤه']);
        }
    }
    public function change_session_time(Request $request, $session_id)
    {
        $doctor = Auth::user()->doctor;
        $md_session = MdSession::find($session_id);
        if ($doctor->id != $md_session->doctor_id) {
            abort(403, 'not authorized to accept this session ');
        } else {
            $request->validate([
                'new_date' => 'datetime'
            ]);

            $md_session->update(['status' => 'accepted', 'scheduled_at' => $request->new_date]);
            $patient = Patient::find($md_session->patient_id);
            $patient->user->notify(new SessionStatusNotification($md_session, "تم قبولها وتعديل الموعد الى {$request->new_date}"));
            return response()->json(['message' => 'تم رفض الموعد والغاؤه']);
        }
    }
    public function my_patients()
    {
        $doctor = Auth::user()->doctor;
        $patients = $doctor->patients;
        return PatientResource::collection($patients)->additional(['tests' => 'this is tests']);
    }
}

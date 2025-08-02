<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\MdSessionResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\RatingResource;
use App\Models\MdSession;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\SessionStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
            $patient->user->notify(new SessionStatusNotification($md_session, " قبولها"));
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
            $patient->user->notify(new SessionStatusNotification($md_session, " رفضها"));
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
                'new_date' => 'date|after:now'
            ]);

            $md_session->update(['status' => 'accepted', 'scheduled_at' => $request->new_date]);
            $patient = Patient::find($md_session->patient_id);
            $patient->user->notify(new SessionStatusNotification($md_session, " قبولها وتعديل الموعد الى {$request->new_date}"));
            return response()->json(['message' => ' تم تعديل الموعد بنجاح']);
        }
    }
    public function my_patients()
    {
        $doctor = Auth::user()->doctor;
        $patients = $doctor->patients;
        return PatientResource::collection($patients)->additional(['tests' => 'this is tests']);
    }
    public function update_info(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $doctor = $user->doctor;
        $validatedUser = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'password' => ['nullable', Password::defaults()],
            'email' => [
                'nullable',
                'string',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'email.unique' => 'هذا البريد مستخدم مسبقاً.',
            'avatar.image' => 'الصورة يجب أن تكون من نوع صورة.',
        ]);
        if ($request->file('avatar')) {
            Storage::disk('public')->delete($user->avatar);
            $new_avatar_path = Storage::disk('public')->put('users/', $request->file('avatar'));
            $validatedUser['avatar'] = $new_avatar_path;
        }
        if ($request->has("password")) {
            $validatedUser['password'] = Hash::make($request->string('password'));
        }
        $user->update($validatedUser);
        return response()->json([
            'message' => 'تم تحديث المعلومات بنجاح.',
            'doctor' => new DoctorResource($doctor)
        ]);
    }
}

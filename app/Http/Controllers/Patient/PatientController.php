<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\MdSessionResource;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Models\MdSession;
use App\Models\Patient;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PatientController extends Controller
{
    public function home()
    {
        $patient = Auth::user()->patient;
        return  new PatientResource($patient);
    }
    public function update(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $patient = $user->patient;

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

        $validatedPatient = $request->validate([
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'nullable|in:ذكر,انثى',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('avatar')) {
            Storage::disk('public')->delete($user->avatar);
            $path = Storage::disk('public')->put('/users', $request->file('avatar'));
            $validatedUser['avatar'] = $path;
        }
        if ($request->has("password")) {
            $validatedUser['password'] = Hash::make($request->string('password'));
        }
        $user->update($validatedUser);
        $patient->update($validatedPatient);

        return response()->json([
            'message' => 'تم تحديث المعلومات بنجاح.',
            'patient' => new PatientResource($patient)
        ]);
    }
    public function my_sessions()
    {  // return response()->json($md_session);
        $patient = Auth::user()->patient;
        $md_session = $patient->md_sessions;

        return MdSessionResource::collection($md_session);
    }
    public function doctors()
    {
        $doctors = Doctor::all();
        return DoctorResource::collection($doctors);
    }
    public function add_rate(Request $request)
    {

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'rate' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ], [
            'doctor_id.required' => 'يجب تحديد الطبيب.',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود.',

            'rate.required' => 'يرجى إدخال التقييم.',
            'rate.integer' => 'يجب أن يكون التقييم رقمًا صحيحًا.',
            'rate.min' => 'أدنى تقييم مسموح به هو 1.',
            'rate.max' => 'أقصى تقييم مسموح به هو 5.',

            'comment.required' => 'يرجى كتابة تعليق.',
            'comment.string' => 'يجب أن يكون التعليق نصًا.',
        ]);

        $patientId = Auth::user()->id;
        $doctorId = $request->input('doctor_id');

        $hasSession = MdSession::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->exists();

        if (!$hasSession) {
            return response()->json(['message' => 'لا يمكنك تقييم هذا الطبيب قبل حضور جلسة معه.'], 403);
        }

        $alreadyRated = Rating::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->exists();

        if ($alreadyRated) {
            return response()->json(['message' => 'لقد قمت بتقييم هذا الطبيب مسبقًا.'], 403);
        }


        $rating = Rating::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'rate' => $request->input('rate'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json(['message' => 'تم إضافة التقييم بنجاح.', 'data' => $rating], 201);
    }
}

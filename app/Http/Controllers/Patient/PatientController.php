<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
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
}

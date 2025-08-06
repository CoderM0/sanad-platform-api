<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocPatientsResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Models\ContactInfo;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function get_all_doctors()
    {
        $doctors = Doctor::all();
        return DoctorResource::collection($doctors);
    }
    public function add_doctor(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'avatar' => 'required|image|mimes:png,jpg,jpeg',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'achievements' => "string|required",
            'specialization' => 'required|string',
            'phone_number' => 'required'
        ], [
            'first_name.required'   => 'يرجى إدخال الاسم الأول.',
            'first_name.string'     => 'يجب أن يكون الاسم الأول نصًا.',
            'first_name.max'        => 'يجب ألا يتجاوز الاسم الأول 255 حرفًا.',

            'last_name.required'    => 'يرجى إدخال الاسم الأخير.',
            'last_name.string'      => 'يجب أن يكون الاسم الأخير نصًا.',
            'last_name.max'         => 'يجب ألا يتجاوز الاسم الأخير 255 حرفًا.',

            'avatar.required'       => 'يرجى رفع صورة شخصية.',
            'avatar.image'          => 'يجب أن يكون الملف صورة.',
            'avatar.mimes'          => 'يجب أن تكون الصورة بصيغة png أو jpg أو jpeg.',

            'email.required'        => 'يرجى إدخال البريد الإلكتروني.',
            'email.string'          => 'يجب أن يكون البريد الإلكتروني نصًا.',
            'email.lowercase'       => 'يجب أن يكون البريد الإلكتروني بأحرف صغيرة.',
            'email.email'           => 'البريد الإلكتروني غير صالح.',
            'email.max'             => 'يجب ألا يتجاوز البريد الإلكتروني 255 حرفًا.',
            'email.unique'          => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.required'     => 'يرجى إدخال كلمة المرور.',
            'password.confirmed'    => 'تأكيد كلمة المرور غير مطابق.',

            'achievements.required'          => 'يرجى إدخال الانجازات.',
            'achievements.string'           => 'يجب ان تكون الانجازات نصا.',

            'specialization.required'       => 'يرجى تحديد الاختصاص.',
            'specialization.string'             => 'الاختصاص يجب ان يكون نصا',

            'phone_number.required' => 'يرجى إدخال رقم الهاتف.',
        ]);
        try {

            DB::beginTransaction();

            $avatarPath = Storage::disk('public')->put('/users', $request->file('avatar'));

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => User::ROLE_DOCTOR,
                'avatar' => $avatarPath,
                'password' => Hash::make($request->string('password')),
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'achievements' => $request->achievements,
                'specialization' => $request->specialization,
                'phone_number' => $request->phone_number,
            ]);

            DB::commit();

            return response()->json([
                'user' => new DoctorResource($doctor)
            ]);
        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'خطأ في التحقق من المدخلات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Registration error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء التسجيل',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function update_doctor(Request $request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        $user = $doctor->user;
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

        $validatedDoctor = $request->validate([
            'achievements' => "nullable|string",
            'specialization' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
        ], [
            'achievements.string' => 'يجب ان تكون الانجازات نصا.',
            'specialization.string' => 'الاختصاص يجب ان يكون نصا',
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
        $doctor->update($validatedDoctor);

        return response()->json([
            'message' => 'تم تحديث المعلومات بنجاح.',
            'doctor' => new DoctorResource($doctor)
        ]);
    }
    public function delete_doctor($doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        $user = $doctor->user;
        Storage::disk("public")->delete($user->avatar);
        $user->delete();
        return response()->json(['message' => "تم الحذف بنجاح"]);
    }
    public function doc_patients($doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        $patients = $doctor->patients;
        return PatientResource::collection($patients);
    }
    public function docs_with_patients()
    {
        $doctors = Doctor::with('patients')->get();
        return DocPatientsResource::collection($doctors);
    }
    public function add_contact(Request $request)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string',
            'contact_value' => 'required|string',
        ]);
        ContactInfo::create($validated);
        return response()->json(['message' => 'تم الاضافة بنجاح']);
    }
    public function edit_contact(Request $request, ContactInfo $contact)
    {
        $validated = $request->validate([
            'contact_name' => 'nullable|string',
            'contact_value' => 'nullable|string',
        ]);
        $contact->update($validated);
        return response()->json(['message' => 'تم التعديل بنجاح']);
    }
    public function delete_contact(ContactInfo $contact)
    {
        $contact->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}

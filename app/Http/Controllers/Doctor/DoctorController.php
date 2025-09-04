<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\MdSessionResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\RatingResource;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\FinancialRecord;
use App\Models\MdSession;
use App\Models\Patient;
use App\Models\Section;
use App\Models\TestResult;
use App\Models\User;
use App\Notifications\SessionStatusNotification;
use Carbon\Carbon;
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
        MdSession::where('scheduled_at', '<', Carbon::now())->delete();
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
            $f_record = FinancialRecord::where('md_session_id', $md_session->id)->first();
            $f_record->update(['status' => 'paid']);
            return response()->json(['message' => 'تم قبول الموعد بنجاح ']);
        }
    }
    public function decline_session($session_id)
    {
        $doctor = Auth::user()->doctor;
        $md_session = MdSession::find($session_id);

        if ($doctor->id != $md_session->doctor_id) {
            abort(403, 'لا يمكنك رفض جلسة ليست من جلساتك ');
        } else {
            $f_record = FinancialRecord::where('md_session_id', $md_session->id)->first();
            $f_record->delete();
            $tmp_md_session = $md_session;
            $md_session->delete();
            $patient = Patient::find($md_session->patient_id);
            $patient->user->notify(new SessionStatusNotification($tmp_md_session, " رفضها"));
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
            $f_record = FinancialRecord::where('md_session_id', $md_session->id)->first();
            $f_record->update(['reservation_date' => $request->new_date]);
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
    public function getTestResults($patient_id, $test_id)
    {
        $patientId = $patient_id;


        $doctor = Auth::user()->doctor;
        $exists = $doctor->patients()->where('patients.id', $patient_id)->exists();
        if (!$exists) {
            return response()->json(['message' => 'لا تملك صلاحية عرض اختبارات احد المرضى ما لم يكن احد مرضاك']);
        }
        $results = TestResult::where('patient_id', $patientId)
            ->where('test_id', $test_id)
            ->select('question', 'test_name', 'answer', 'result', 'result_description')
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'لا توجد نتائج'], 404);
        }

        $answers = [];
        $resultValue = null;

        foreach ($results as $row) {
            $answers[] = [$row->question => $row->answer];
            $resultValue = $row->result;
            $result_description = $row->result_description;
        }

        return response()->json([
            'patient_id' => $patientId,
            'test_name' => $results[0]->test_name,
            'result' => $resultValue,
            'result_description' => $result_description,
            'answers' => $answers
        ]);
    }
    public function add_blog(Request $request)
    {
        $doctor_id = Auth::user()->doctor->id;
        $request->validate([
            'blog_title' => 'required|string',
            'blog_img' => 'required|image',
            'first_section_title' => 'required|string',
            'first_section_text' => 'required|string',
        ]);
        $blog_img_path = Storage::disk("public")->put("/blogs", $request->file("blog_img"));
        $blog = Blog::create([
            'doctor_id' => $doctor_id,
            'blog_img' => $blog_img_path,
            "title" => $request->blog_title
        ]);
        Section::create([
            'blog_id' => $blog->id,
            'section_title' => $request->first_section_title,
            'section_text' => $request->first_section_text
        ]);
        return response()->json(['message' => 'تم انشاء المدونة بنجاح', 'blog' => $blog]);
    }
    public function add_section(Request $request, $blog_id)
    {
        $request->validate([
            'section_title' => 'required|string',
            'section_text' => 'required|string',
        ]);
        Section::create([
            'blog_id' => $blog_id,
            'section_title' => $request->section_title,
            'section_text' => $request->section_text,
        ]);
        return response()->json(['message' => 'تم الاضافة بنجاح']);
    }
    public function get_blog_info(Blog $blog)
    {
        return new BlogResource($blog);
    }
    public function get_doc_blogs(Doctor $doctor)
    {

        return BlogResource::collection($doctor->blogs);
    }
}

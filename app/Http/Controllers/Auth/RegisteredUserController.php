<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */


    public function store(SignupRequest $request)
    {
        try {

            DB::beginTransaction();

            $avatarPath = Storage::disk('public')->put('/users', $request->file('avatar'));

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => User::ROLE_PATIENT,
                'avatar' => $avatarPath,
                'password' => Hash::make($request->string('password')),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;


            $patient = Patient::create([
                'user_id' => $user->id,
                'age' => $request->age,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
            ]);

            DB::commit();

            event(new Registered($user));


            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new PatientResource($patient)
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
}

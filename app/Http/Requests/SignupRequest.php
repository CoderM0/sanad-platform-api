<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'avatar' => 'required|image|mimes:png,jpg,jpeg',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'age' => "integer|required",
            'gender' => 'required|in:ذكر,انثى',
            'phone_number' => 'required'
        ];
    }
    public function messages()
    {
        return [
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

            'age.required'          => 'يرجى إدخال العمر.',
            'age.integer'           => 'يجب أن يكون العمر رقمًا صحيحًا.',

            'gender.required'       => 'يرجى تحديد الجنس.',
            'gender.in'             => 'الجنس يجب أن يكون "ذكر" أو "أنثى".',

            'phone_number.required' => 'يرجى إدخال رقم الهاتف.',
        ];
    }
}

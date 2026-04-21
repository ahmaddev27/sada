<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// AUTH-04: reset password — same policy as AUTH-03
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'     => 'رمز إعادة التعيين مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required'  => 'كلمة المرور الجديدة مطلوبة.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
            'password.min'       => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.',
            'password.mixed'     => 'كلمة المرور يجب أن تحتوي على أحرف كبيرة وصغيرة.',
            'password.numbers'   => 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل.',
        ];
    }
}

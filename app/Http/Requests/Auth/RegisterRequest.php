<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// AUTH-01: email/password registration | AUTH-03: password policy
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'الاسم الكامل مطلوب.',
            'name.max'           => 'الاسم يجب ألا يتجاوز 255 حرفاً.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'       => 'هذا البريد الإلكتروني مسجّل مسبقاً.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
            'password.min'       => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.',
            'password.mixed'     => 'كلمة المرور يجب أن تحتوي على أحرف كبيرة وصغيرة.',
            'password.numbers'   => 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل.',
        ];
    }
}

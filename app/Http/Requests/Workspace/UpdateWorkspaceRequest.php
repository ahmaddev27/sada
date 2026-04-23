<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

// WS-03
class UpdateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'min:2', 'max:80'],
            'business_type'   => ['nullable', 'string', 'max:60'],
            'countries'       => ['nullable', 'array'],
            'countries.*'     => ['string', 'in:sa,ae,kw,qa,bh,om'],
            'default_dialect' => ['nullable', 'string', 'in:fos,sa,ae,kw,qa,bh,om'],
            'logo'            => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'اسم مساحة العمل مطلوب.',
            'name.min'       => 'اسم مساحة العمل يجب أن يكون حرفين على الأقل.',
            'logo.image'     => 'الشعار يجب أن يكون صورة.',
            'logo.mimes'     => 'الشعار يجب أن يكون PNG أو JPG أو WEBP أو SVG.',
            'logo.max'       => 'حجم الشعار لا يتجاوز 2MB.',
        ];
    }
}

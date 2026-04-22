<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

// WS-01
class CreateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'min:2', 'max:80'],
            'business_type'    => ['nullable', 'string', 'max:60'],
            'countries'        => ['nullable', 'array'],
            'countries.*'      => ['string', 'in:sa,ae,kw,qa,bh,om'],
            'default_dialect'  => ['nullable', 'string', 'in:fos,sa,ae,kw,qa,bh,om'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم مساحة العمل مطلوب.',
            'name.min'      => 'اسم مساحة العمل يجب أن يكون حرفين على الأقل.',
            'name.max'      => 'اسم مساحة العمل لا يتجاوز ٨٠ حرفاً.',
            'countries.*.in'=> 'الدولة المحددة غير مدعومة.',
            'default_dialect.in' => 'اللهجة المحددة غير مدعومة.',
        ];
    }
}

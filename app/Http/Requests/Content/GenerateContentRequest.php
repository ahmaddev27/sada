<?php

// CG-01→CG-04

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content_type'   => ['required', 'in:post,reel,story,ad,thread,snap_caption'],
            'platform'       => ['required', 'in:instagram,facebook,tiktok,snapchat,x'],
            'dialect'        => ['required', 'in:fos,sa,ae,kw,qa,bh,om'],
            'prompt'         => ['required', 'string', 'min:5', 'max:500'],   // CG-04
            'use_brand'      => ['boolean'],
            'include_emojis' => ['boolean'],
            'length'         => ['in:short,med,long'],
            'cta'            => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'prompt.required' => 'يرجى إدخال وصف الفكرة.',
            'prompt.min'      => 'الفكرة قصيرة جداً — أضف تفاصيل أكثر.',
            'prompt.max'      => 'الحد الأقصى للفكرة 500 حرف.',
        ];
    }
}

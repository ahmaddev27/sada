<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

// BI-01 → BI-04
class UpdateBrandIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'    => ['nullable', 'string', 'max:1000'],
            'tone'           => ['nullable', 'string', 'max:60'],
            'target_audience'=> ['nullable', 'string', 'max:500'],
            'banned_words'   => ['nullable', 'array', 'max:10'],
            'banned_words.*' => ['string', 'max:50'],
            'example_posts'  => ['nullable', 'array', 'max:5'],
            'example_posts.*'=> ['string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.max'    => 'وصف العلامة لا يتجاوز ١٠٠٠ حرف.',
            'banned_words.max'   => 'لا يمكن إضافة أكثر من ١٠ كلمات محظورة. (BI-03)',
            'example_posts.max'  => 'لا يمكن إضافة أكثر من ٥ منشورات نموذجية. (BI-04)',
        ];
    }
}

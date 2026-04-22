<?php

// CG-08

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class SavePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content'          => ['required', 'string', 'min:1', 'max:63206'],
            'hashtags'         => ['array'],
            'hashtags.*'       => ['string', 'starts_with:#', 'max:100'],
            'platform'         => ['required', 'in:instagram,facebook,tiktok,snapchat,x'],
            'content_type'     => ['required', 'in:post,reel,story,ad,thread,snap_caption'],
            'dialect'          => ['required', 'in:fos,sa,ae,kw,qa,bh,om'],
            'action'           => ['required', 'in:draft,schedule,publish'],
            'scheduled_for'    => ['required_if:action,schedule', 'nullable', 'date', 'after:now'],
            'social_account_id'=> ['nullable', 'integer', 'exists:social_accounts,id'],
        ];
    }
}

<?php

// ADS-01

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'               => ['nullable', 'string', 'min:2', 'max:120'],
            'objective'          => ['nullable', 'in:awareness,traffic,engagement,conversions,app_installs,video_views'],
            'platform'           => ['nullable', 'in:instagram,facebook'],
            'social_account_id'  => ['nullable', 'integer'],
            'post_id'            => ['nullable', 'integer'],
            'target_countries'   => ['nullable', 'array', 'min:1'],
            'target_countries.*' => ['in:sa,ae,kw,qa,bh,om'],
            'target_age_min'     => ['nullable', 'integer', 'min:13', 'max:65'],
            'target_age_max'     => ['nullable', 'integer', 'min:13', 'max:65', 'gte:target_age_min'],
            'target_gender'      => ['nullable', 'in:all,male,female'],
            'target_interests'   => ['nullable', 'array'],
            'budget_type'        => ['nullable', 'in:daily,lifetime'],
            'budget_amount'      => ['nullable', 'numeric', 'min:5'],
            'budget_currency'    => ['nullable', 'in:SAR,AED,USD,QAR,KWD,BHD,OMR'],
            'starts_at'          => ['nullable', 'date', 'after_or_equal:today'],
            'ends_at'            => ['nullable', 'date', 'after:starts_at'],
            'status'             => ['nullable', 'in:draft,pending,active,paused,completed,rejected'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.min'                    => 'اسم الحملة يجب أن يكون حرفين على الأقل.',
            'name.max'                    => 'اسم الحملة لا يتجاوز 120 حرفاً.',
            'objective.in'                => 'هدف الحملة غير صحيح.',
            'platform.in'                 => 'المنصة المحددة غير مدعومة. الخيارات: انستجرام أو فيسبوك.',
            'social_account_id.integer'   => 'معرّف الحساب غير صحيح.',
            'post_id.integer'             => 'معرّف المنشور الإبداعي غير صحيح.',
            'target_countries.min'        => 'يرجى تحديد دولة استهداف واحدة على الأقل.',
            'target_countries.*.in'       => 'دولة الاستهداف غير مدعومة. الدول المتاحة: SA, AE, KW, QA, BH, OM.',
            'target_age_min.min'          => 'الحد الأدنى للعمر 13 سنة.',
            'target_age_min.max'          => 'الحد الأقصى للعمر 65 سنة.',
            'target_age_max.min'          => 'الحد الأدنى للعمر 13 سنة.',
            'target_age_max.max'          => 'الحد الأقصى للعمر 65 سنة.',
            'target_age_max.gte'          => 'الحد الأقصى للعمر يجب أن يكون مساوياً أو أكبر من الحد الأدنى.',
            'target_gender.in'            => 'قيمة الجنس غير صحيحة. الخيارات: الكل، ذكر، أنثى.',
            'budget_type.in'              => 'نوع الميزانية غير صحيح. الخيارات: يومية أو إجمالية.',
            'budget_amount.numeric'       => 'مبلغ الميزانية يجب أن يكون رقماً.',
            'budget_amount.min'           => 'الحد الأدنى للميزانية 5 وحدات.',
            'budget_currency.in'          => 'العملة غير مدعومة. الخيارات: SAR, AED, USD, QAR, KWD, BHD, OMR.',
            'starts_at.date'              => 'تاريخ البدء غير صحيح.',
            'starts_at.after_or_equal'    => 'تاريخ البدء يجب أن يكون اليوم أو بعده.',
            'ends_at.date'                => 'تاريخ الانتهاء غير صحيح.',
            'ends_at.after'               => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.',
            'status.in'                   => 'الحالة المحددة غير صحيحة.',
        ];
    }
}

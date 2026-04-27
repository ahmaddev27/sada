<?php

// ADS-01

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'min:2', 'max:120'],
            'objective'          => ['required', 'in:awareness,traffic,engagement,conversions,app_installs,video_views'],
            'platform'           => ['required', 'in:instagram,facebook'],
            'social_account_id'  => ['nullable', 'integer'],
            'post_id'            => ['nullable', 'integer'],
            'ad_copy'            => ['nullable', 'string', 'max:5000'],
            'ad_headline'        => ['nullable', 'string', 'max:255'],
            'ad_description'     => ['nullable', 'string', 'max:500'],
            'target_countries'   => ['required', 'array', 'min:1'],
            'target_countries.*' => ['in:sa,ae,kw,qa,bh,om'],
            'target_age_min'     => ['required', 'integer', 'min:13', 'max:65'],
            'target_age_max'     => ['required', 'integer', 'min:13', 'max:65', 'gte:target_age_min'],
            'target_gender'      => ['required', 'in:all,male,female'],
            'target_interests'   => ['nullable', 'array'],
            'budget_type'        => ['required', 'in:daily,lifetime'],
            'budget_amount'      => ['required', 'numeric', 'min:5'],
            'budget_currency'    => ['required', 'in:SAR,AED,USD,QAR,KWD,BHD,OMR'],
            'starts_at'          => ['required', 'date'],
            'ends_at'            => ['required', 'date', 'after:starts_at'],
            'status'             => ['nullable', 'in:draft,pending'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'               => 'اسم الحملة مطلوب.',
            'name.min'                    => 'اسم الحملة يجب أن يكون حرفين على الأقل.',
            'name.max'                    => 'اسم الحملة لا يتجاوز 120 حرفاً.',
            'objective.required'          => 'يرجى تحديد هدف الحملة.',
            'objective.in'                => 'هدف الحملة غير صحيح.',
            'platform.required'           => 'يرجى تحديد المنصة.',
            'platform.in'                 => 'المنصة المحددة غير مدعومة. الخيارات: انستجرام أو فيسبوك.',
            'social_account_id.integer'   => 'معرّف الحساب غير صحيح.',
            'post_id.integer'             => 'معرّف المنشور الإبداعي غير صحيح.',
            'target_countries.required'   => 'يرجى تحديد دولة استهداف واحدة على الأقل.',
            'target_countries.min'        => 'يرجى تحديد دولة استهداف واحدة على الأقل.',
            'target_countries.*.in'       => 'دولة الاستهداف غير مدعومة. الدول المتاحة: SA, AE, KW, QA, BH, OM.',
            'target_age_min.required'     => 'يرجى تحديد الحد الأدنى للعمر.',
            'target_age_min.min'          => 'الحد الأدنى للعمر 13 سنة.',
            'target_age_min.max'          => 'الحد الأقصى للعمر 65 سنة.',
            'target_age_max.required'     => 'يرجى تحديد الحد الأقصى للعمر.',
            'target_age_max.min'          => 'الحد الأدنى للعمر 13 سنة.',
            'target_age_max.max'          => 'الحد الأقصى للعمر 65 سنة.',
            'target_age_max.gte'          => 'الحد الأقصى للعمر يجب أن يكون مساوياً أو أكبر من الحد الأدنى.',
            'target_gender.required'      => 'يرجى تحديد الجنس المستهدف.',
            'target_gender.in'            => 'قيمة الجنس غير صحيحة. الخيارات: الكل، ذكر، أنثى.',
            'budget_type.required'        => 'يرجى تحديد نوع الميزانية.',
            'budget_type.in'              => 'نوع الميزانية غير صحيح. الخيارات: يومية أو إجمالية.',
            'budget_amount.required'      => 'يرجى إدخال مبلغ الميزانية.',
            'budget_amount.numeric'       => 'مبلغ الميزانية يجب أن يكون رقماً.',
            'budget_amount.min'           => 'الحد الأدنى للميزانية 5 وحدات.',
            'budget_currency.required'    => 'يرجى تحديد عملة الميزانية.',
            'budget_currency.in'          => 'العملة غير مدعومة. الخيارات: SAR, AED, USD, QAR, KWD, BHD, OMR.',
            'starts_at.required'          => 'يرجى تحديد تاريخ بدء الحملة.',
            'starts_at.date'              => 'تاريخ البدء غير صحيح.',
            'starts_at.after_or_equal'    => 'تاريخ البدء يجب أن يكون اليوم أو بعده.',
            'ends_at.required'            => 'يرجى تحديد تاريخ انتهاء الحملة.',
            'ends_at.date'                => 'تاريخ الانتهاء غير صحيح.',
            'ends_at.after'               => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.',
            'status.in'                   => 'الحالة غير صحيحة عند الإنشاء. الخيارات: مسودة أو قيد المراجعة.',
        ];
    }
}

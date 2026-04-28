<?php

// MKT-01: generate a complete Arabic marketing plan via AI

namespace App\Actions\MarketingPlan;

use App\Models\MarketingPlan;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Ai\Drivers\AnthropicDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\GroqDriver;
use App\Services\Ai\Drivers\OpenAiDriver;
use Illuminate\Support\Facades\Log;

class GenerateMarketingPlanAction
{
    /**
     * @param array<string, mixed> $inputs
     */
    public function execute(array $inputs, Workspace $workspace, User $user): MarketingPlan
    {
        $plan = MarketingPlan::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $user->id,
            'title'        => "خطة تسويقية — {$inputs['business_name']}",
            'inputs'       => $inputs,
            'status'       => 'generating',
        ]);

        try {
            $system = $this->buildSystemPrompt();
            $user_msg = $this->buildUserMessage($inputs);

            $driver = $this->resolveDriver();
            $result = $driver->complete($system, $user_msg);

            $planData = $this->extractJson($result['content']);

            $cost = $this->calculateCost(
                $driver->model(),
                $result['input_tokens'],
                $result['output_tokens']
            );

            $plan->update([
                'plan'          => $planData,
                'status'        => 'completed',
                'ai_provider'   => $driver->name(),
                'ai_model'      => $driver->model(),
                'cost_usd'      => $cost,
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'title'         => $planData['title'] ?? $plan->title,
            ]);
        } catch (\Throwable $e) {
            Log::error('marketing_plan_generation_failed', [
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);
            $plan->update(['status' => 'failed']);
        }

        return $plan->fresh();
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
أنت مستشار تسويق رقمي خبير متخصص في السوق الخليجي (السعودية، الإمارات، الكويت، قطر، البحرين، عُمان).
مهمتك: إنشاء خطة تسويقية رقمية شاملة وقابلة للتنفيذ باللغة العربية الفصحى الحديثة.

معايير الخطة:
- محددة وواقعية وقابلة للتنفيذ على أرض الواقع
- مراعية للثقافة الخليجية والمناسبات والمواسم
- تحتوي على أفكار محتوى حقيقية وليست عامة
- ذات توزيع ميزانية منطقي ومبرر

أعد الخطة كـ JSON صالح فقط بدون أي نص قبله أو بعده، بدون markdown code blocks، JSON خالص فقط.
PROMPT;
    }

    /**
     * @param array<string, mixed> $inputs
     */
    private function buildUserMessage(array $inputs): string
    {
        $countries  = implode('، ', array_map(fn ($c) => $this->countryLabel($c), (array) ($inputs['countries'] ?? [])));
        $platforms  = implode('، ', array_map(fn ($p) => $this->platformLabel($p), (array) ($inputs['platforms'] ?? [])));
        $occasions  = implode('، ', (array) ($inputs['occasions'] ?? []));
        $interests  = implode('، ', (array) ($inputs['interests'] ?? []));
        $gender     = $this->genderLabel($inputs['gender'] ?? 'all');
        $goal       = $this->goalLabel($inputs['goal'] ?? 'awareness');
        $duration   = $this->durationLabel($inputs['duration'] ?? '3_months');
        $budget     = number_format((float) ($inputs['budget'] ?? 0));
        $currency   = $inputs['currency'] ?? 'SAR';
        $ageMin     = $inputs['age_min'] ?? 18;
        $ageMax     = $inputs['age_max'] ?? 45;
        $months     = $this->durationMonths($inputs['duration'] ?? '3_months');

        return <<<MSG
أنشئ خطة تسويقية رقمية شاملة لـ {$months} أشهر بناءً على المعلومات التالية:

**معلومات العمل:**
- اسم العمل: {$inputs['business_name']}
- نوع العمل: {$inputs['business_type']}
- وصف المنتج/الخدمة: {$inputs['description']}
- الميزة التنافسية: {$inputs['usp']}

**الأهداف:**
- الهدف الرئيسي: {$goal}
- مدة الخطة: {$duration}
- الميزانية الإجمالية: {$budget} {$currency}

**الجمهور المستهدف:**
- الدول: {$countries}
- الفئة العمرية: من {$ageMin} إلى {$ageMax} سنة
- الجنس: {$gender}
- الاهتمامات: {$interests}

**المنصات المستهدفة:** {$platforms}
**المناسبات الموسمية المراد استغلالها:** {$occasions}

أنشئ الخطة بهذا التنسيق JSON بالضبط:
{
  "title": "عنوان الخطة التسويقية",
  "executive_summary": "ملخص تنفيذي 3-4 جمل يوضح الاستراتيجية والأهداف",
  "audience_analysis": {
    "primary_segment": "وصف دقيق للشريحة الرئيسية المستهدفة",
    "pain_points": ["نقطة ألم أو تحدٍّ يواجهه العميل 1", "2", "3"],
    "buying_triggers": ["ما يدفع العميل لاتخاذ قرار الشراء 1", "2", "3"]
  },
  "goals": [
    { "title": "اسم الهدف", "kpi": "المؤشر القابل للقياس", "target": "الرقم أو النسبة المستهدفة", "timeframe": "الإطار الزمني" }
  ],
  "content_pillars": [
    { "name": "اسم الركيزة", "description": "شرح موجز", "percentage": 30, "examples": ["فكرة محتوى حقيقية 1", "فكرة 2", "فكرة 3"] }
  ],
  "platform_strategy": [
    { "platform": "اسم المنصة", "role": "دور هذه المنصة في الخطة", "content_types": ["نوع المحتوى 1", "نوع 2"], "frequency": "التردد اليومي أو الأسبوعي", "budget_pct": 40, "tips": ["نصيحة عملية 1", "نصيحة 2"] }
  ],
  "monthly_plan": [
    {
      "month": 1,
      "theme": "موضوع الشهر",
      "key_occasions": ["مناسبة خليجية أو إسلامية أو تجارية في هذا الشهر"],
      "focus": "التركيز الاستراتيجي للشهر",
      "weekly_breakdown": [
        { "week": 1, "theme": "موضوع الأسبوع", "content_ideas": ["فكرة منشور 1", "فكرة 2", "فكرة 3"] },
        { "week": 2, "theme": "موضوع الأسبوع", "content_ideas": ["فكرة 1", "فكرة 2", "فكرة 3"] },
        { "week": 3, "theme": "موضوع الأسبوع", "content_ideas": ["فكرة 1", "فكرة 2", "فكرة 3"] },
        { "week": 4, "theme": "موضوع الأسبوع", "content_ideas": ["فكرة 1", "فكرة 2", "فكرة 3"] }
      ]
    }
  ],
  "budget_breakdown": [
    { "category": "الفئة", "percentage": 40, "notes": "توضيح وتبرير هذا التخصيص" }
  ],
  "content_samples": [
    { "platform": "اسم المنصة", "type": "نوع المنشور", "caption": "نص منشور حقيقي قابل للاستخدام مباشرة", "hashtags": ["#هاشتاق1", "#هاشتاق2"] }
  ],
  "recommendations": ["توصية استراتيجية عملية 1", "2", "3", "4", "5"]
}

مهم: أنشئ {$months} شهر في monthly_plan. اجعل أفكار المحتوى حقيقية ومحددة لهذا العمل بالذات.
MSG;
    }

    private function extractJson(string $content): array
    {
        $content = trim($content);

        // Strip markdown code fences if present
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/i', '', $content) ?? $content;
        $content = trim($content);

        // Find the first { and last }
        $start = strpos($content, '{');
        $end   = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            throw new \RuntimeException('No valid JSON object found in AI response');
        }

        $json = substr($content, $start, $end - $start + 1);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new \RuntimeException('AI response JSON is not an array');
        }

        return $decoded;
    }

    private function resolveDriver(): \App\Services\Ai\Drivers\AiDriverInterface
    {
        $all = [
            'anthropic' => fn () => new AnthropicDriver(
                apiKey: (string) config('services.anthropic.api_key', ''),
                model:  (string) config('services.anthropic.model', 'claude-3-5-haiku-20241022'),
            ),
            'openai' => fn () => new OpenAiDriver(
                apiKey: (string) config('services.openai.api_key', ''),
                model:  (string) config('services.openai.model', 'gpt-4o-mini'),
            ),
            'gemini' => fn () => new GeminiDriver(
                apiKey: (string) config('services.gemini.api_key', ''),
                model:  (string) config('services.gemini.model', 'gemini-2.0-flash'),
            ),
            'groq' => fn () => new GroqDriver(
                apiKey: (string) config('services.groq.api_key', ''),
                model:  (string) config('services.groq.model', 'llama-3.3-70b-versatile'),
            ),
        ];

        $primary = (string) config('services.ai.provider', 'anthropic');

        return isset($all[$primary]) ? ($all[$primary])() : ($all['anthropic'])();
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $rates = config('ai_pricing.models', [])[$model] ?? null;

        if ($rates === null) {
            return 0.0;
        }

        return round(
            ($inputTokens / 1_000_000 * $rates['input']) + ($outputTokens / 1_000_000 * $rates['output']),
            8
        );
    }

    private function countryLabel(string $code): string
    {
        return match ($code) {
            'sa' => 'السعودية', 'ae' => 'الإمارات',
            'kw' => 'الكويت',   'qa' => 'قطر',
            'bh' => 'البحرين',  'om' => 'عُمان',
            default => $code,
        };
    }

    private function platformLabel(string $code): string
    {
        return match ($code) {
            'instagram' => 'انستجرام', 'facebook' => 'فيسبوك',
            'tiktok'    => 'تيك توك',  'snapchat' => 'سناب شات',
            'x'         => 'X (تويتر)', default => $code,
        };
    }

    private function genderLabel(string $gender): string
    {
        return match ($gender) {
            'male'   => 'ذكور',
            'female' => 'إناث',
            default  => 'الجنسين',
        };
    }

    private function goalLabel(string $goal): string
    {
        return match ($goal) {
            'awareness'   => 'زيادة الوعي بالعلامة التجارية',
            'sales'       => 'زيادة المبيعات والإيرادات',
            'engagement'  => 'تعزيز التفاعل والمجتمع',
            'leads'       => 'توليد عملاء محتملين',
            'retention'   => 'الاحتفاظ بالعملاء وتعزيز الولاء',
            default       => $goal,
        };
    }

    private function durationLabel(string $duration): string
    {
        return match ($duration) {
            '1_month'   => 'شهر واحد',
            '3_months'  => '3 أشهر',
            '6_months'  => '6 أشهر',
            '12_months' => 'سنة كاملة',
            default     => $duration,
        };
    }

    private function durationMonths(string $duration): int
    {
        return match ($duration) {
            '1_month'   => 1,
            '3_months'  => 3,
            '6_months'  => 6,
            '12_months' => 12,
            default     => 3,
        };
    }
}

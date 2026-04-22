<?php

// CG-01→CG-11

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class ContentGenerationService
{
    // CG-11: platform character limits
    private const PLATFORM_LIMITS = [
        'instagram' => 2200,
        'facebook'  => 63206,
        'tiktok'    => 2200,
        'snapchat'  => 250,
        'x'         => 280,
    ];

    // CG-03: dialect system instructions
    private const DIALECT_INSTRUCTIONS = [
        'fos' => 'استخدم العربية الفصحى الحديثة — رسمية واضحة بدون لهجة عامية.',
        'sa'  => 'استخدم اللهجة السعودية العامية الحديثة — طبيعية وعفوية كما يتحدث الشباب السعودي.',
        'ae'  => 'استخدم اللهجة الإماراتية — خليجية دافئة بمفردات إماراتية.',
        'kw'  => 'استخدم اللهجة الكويتية — خليجية مميزة بمفردات كويتية خاصة.',
        'qa'  => 'استخدم اللهجة القطرية — خليجية بمفردات قطرية.',
        'bh'  => 'استخدم اللهجة البحرينية — خليجية بمفردات بحرينية.',
        'om'  => 'استخدم اللهجة العُمانية — مميزة بنبرة عُمانية أصيلة.',
    ];

    private const CONTENT_TYPE_INSTRUCTIONS = [
        'post'         => 'منشور نصي عادي مناسب للفيد.',
        'reel'         => 'نص سكريبت فيديو قصير — ابدأ بـ hook جذاب في أول 3 ثوانٍ.',
        'story'        => 'نص قصة قصيرة مباشرة وسريعة — لا تزيد عن 3 أسطر.',
        'ad'           => 'نص إعلاني مبيعاتي — headline قوي + body مقنع + CTA واضح.',
        'thread'       => 'خيط تغريدات — كل تغريدة لا تتجاوز 280 حرفاً، رقّمها.',
        'snap_caption' => 'كابشن سناب — لا يتجاوز 250 حرفاً، مباشر ومرح.',
    ];

    /**
     * CG-05: generate 3 variations using Anthropic Claude.
     *
     * @param array<string, mixed> $params
     * @return array<int, array{title: string, body: string, tags: string[], char_count: int}>
     */
    public function generate(array $params): array
    {
        $systemPrompt = $this->buildSystemPrompt($params);
        $userPrompt   = $this->buildUserPrompt($params);

        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-3-5-haiku-20241022',
            'max_tokens' => 2048,
            'system'     => $systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $userPrompt]],
        ])->throw()->json();

        $raw = $response['content'][0]['text'] ?? '';

        return $this->parseVariations($raw, $params['platform']);
    }

    /** @param array<string, mixed> $params */
    private function buildSystemPrompt(array $params): string
    {
        $dialect    = self::DIALECT_INSTRUCTIONS[$params['dialect']] ?? self::DIALECT_INSTRUCTIONS['fos'];
        $type       = self::CONTENT_TYPE_INSTRUCTIONS[$params['content_type']] ?? self::CONTENT_TYPE_INSTRUCTIONS['post'];
        $limit      = self::PLATFORM_LIMITS[$params['platform']] ?? 2200;
        $platform   = $params['platform'];
        $emojiLine  = $params['include_emojis'] ? 'أضف إيموجيات مناسبة للسياق الخليجي بشكل طبيعي.' : 'لا تستخدم إيموجيات.';

        $brandSection = '';
        if ($params['use_brand'] && ! empty($params['brand_identity'])) {
            $brand = $params['brand_identity'];
            $brandSection = "\n\nهوية العلامة التجارية:\n" .
                "- الوصف: {$brand['description']}\n" .
                "- النبرة: {$brand['tone']}\n" .
                (! empty($brand['banned_words'])
                    ? '- كلمات محظورة: ' . implode('، ', (array) $brand['banned_words']) . "\n"
                    : '');
        }

        return <<<SYSTEM
أنت كاتب محتوى خليجي محترف متخصص في التسويق الرقمي.

اللهجة: {$dialect}
نوع المحتوى: {$type}
المنصة: {$platform} (الحد الأقصى للحروف: {$limit})
{$emojiLine}{$brandSection}

قواعد صارمة:
- لا تتجاوز الحد الأقصى للحروف للمنصة
- اكتب دائماً من اليمين إلى اليسار
- الهاشتاقات دائماً بالعربية ما لم تكن المنصة تستوجب الإنجليزية
- CG-09: أضف 5-7 هاشتاقات ذات صلة لكل خيار
SYSTEM;
    }

    /** @param array<string, mixed> $params */
    private function buildUserPrompt(array $params): string
    {
        $lengthMap = ['short' => '50-100', 'med' => '150-250', 'long' => '300+'];
        $length    = $lengthMap[$params['length'] ?? 'med'] ?? '150-250';
        $cta       = ! empty($params['cta']) ? "\nCTA المطلوب: {$params['cta']}" : '';

        return <<<PROMPT
الفكرة: {$params['prompt']}
الطول المطلوب: {$length} حرف تقريباً{$cta}

اكتب 3 خيارات مختلفة بأسلوب وزاوية مختلفة لكل خيار.

استخدم هذا التنسيق بالضبط لكل خيار:

===OPTION_1===
TITLE: الخيار ١ · [وصف الأسلوب]
BODY:
[النص الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===

===OPTION_2===
TITLE: الخيار ٢ · [وصف الأسلوب]
BODY:
[النص الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===

===OPTION_3===
TITLE: الخيار ٣ · [وصف الأسلوب]
BODY:
[النص الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===
PROMPT;
    }

    /**
     * @return array<int, array{title: string, body: string, tags: string[], char_count: int}>
     */
    private function parseVariations(string $raw, string $platform): array
    {
        $results = [];
        $limit   = self::PLATFORM_LIMITS[$platform] ?? 2200;

        preg_match_all('/===OPTION_\d+===(.*?)===END===/s', $raw, $matches);

        foreach ($matches[1] as $block) {
            $block = trim($block);

            preg_match('/TITLE:\s*(.+)/u', $block, $titleM);
            preg_match('/BODY:\s*(.*?)(?=TAGS:)/su', $block, $bodyM);
            preg_match('/TAGS:\s*(.+)/u', $block, $tagsM);

            $title = trim($titleM[1] ?? 'خيار');
            $body  = trim($bodyM[1] ?? $block);
            $body  = mb_substr($body, 0, $limit); // CG-11: enforce limit

            $tagsRaw = trim($tagsM[1] ?? '');
            $tags    = array_values(array_filter(
                preg_split('/\s+/', $tagsRaw) ?: [],
                fn (string $t) => str_starts_with($t, '#')
            ));

            $results[] = [
                'title'      => $title,
                'body'       => $body,
                'tags'       => array_slice($tags, 0, 10), // CG-09: max 10
                'char_count' => mb_strlen($body),
            ];
        }

        // Fallback: return empty structure if parsing failed
        if (empty($results)) {
            $results[] = ['title' => 'الخيار ١', 'body' => $raw, 'tags' => [], 'char_count' => mb_strlen($raw)];
        }

        return $results;
    }
}

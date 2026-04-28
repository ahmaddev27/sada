<?php

// CG-01→CG-11

namespace App\Services\Ai;

use App\Services\Ai\Drivers\AiDriverInterface;
use App\Services\Ai\Drivers\AnthropicDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\GroqDriver;
use App\Services\Ai\Drivers\OpenAiDriver;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ContentGenerationService
{
    // CG-11: platform character limits
    private const PLATFORM_LIMITS = [
        'instagram' => 2200,
        'facebook'  => 63206,
        'tiktok'    => 2200,
        'snapchat'  => 250,
        'x'         => 280,
        'linkedin'  => 3000,
    ];

    // Platform-specific formatting constraints
    private const PLATFORM_FORMAT = [
        'instagram' => 'اكتب النص كفقرات متدفقة طبيعية. لا تستخدم عناوين أو قوائم bullets. النص يبدأ مباشرة بالفكرة الرئيسية.',
        'facebook'  => 'اكتب بفقرات واضحة. يمكن أن يكون النص أطول. أسلوب ودّي ومحادثاتي.',
        'tiktok'    => 'ابدأ بـ hook صادم في أول سطر يجذب الانتباه فوراً. النص خفيف وعامي وسريع الإيقاع. مناسب لسكريبت فيديو قصير.',
        'snapchat'  => 'نص مباشر جداً لا يتجاوز 3 أسطر. أسلوب مرح وعفوي. لا تفاصيل زائدة.',
        'x'         => 'نص مضغوط لا يتجاوز 280 حرفاً. جملة قوية ومباشرة. يمكن hashtag واحد أو اثنان فقط.',
        'linkedin'  => 'نص احترافي بفقرات منظمة. أسلوب رسمي يناسب المهنيين. يمكن البدء بسؤال أو insight يستقطب الاهتمام. يجوز استخدام فقرات قصيرة.',
    ];

    // Workspace business type context
    private const WORKSPACE_TYPE_CONTEXT = [
        'product'  => 'الـ workspace يمثل علامة تجارية تبيع منتجات ملموسة. ركّز على المنتج ومميزاته وقيمته للمشتري.',
        'service'  => 'الـ workspace يقدم خدمات. ركّز على القيمة المقدمة والنتيجة التي يحصل عليها العميل.',
        'personal' => 'الـ workspace يمثل شخصية شخصية (personal brand). أسلوب المحتوى شخصي وأصيل يعكس هوية صاحب الحساب.',
        'persona'  => 'الـ workspace يمثل شخصية شخصية (personal brand). أسلوب المحتوى شخصي وأصيل يعكس هوية صاحب الحساب.',
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
     * Build the ordered driver chain from config.
     * Primary driver first, then the rest as fallbacks.
     *
     * @return AiDriverInterface[]
     */
    private function buildDriverChain(): array
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

        $primary  = (string) config('services.ai.provider', 'anthropic');
        $fallbacks = (string) config('services.ai.fallbacks', 'openai,gemini');

        $order = array_filter(
            [$primary, ...explode(',', $fallbacks)],
            fn (string $k) => isset($all[$k])
        );

        // Deduplicate while preserving order
        $seen  = [];
        $chain = [];
        foreach ($order as $key) {
            $key = trim($key);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $chain[]    = ($all[$key])();
            }
        }

        return $chain;
    }

    /**
     * CG-05: generate 3 variations — tries each driver in order until one succeeds.
     *
     * @param array<string, mixed> $params
     * @return array{
     *   variations: array<int, array{title: string, body: string, headline?: string, description?: string, tags: string[], char_count: int}>,
     *   provider: string,
     *   model: string,
     *   input_tokens: int,
     *   output_tokens: int,
     * }
     */
    public function generate(array $params): array
    {
        $system  = $this->buildSystemPrompt($params);
        $user    = $this->buildUserPrompt($params);
        $drivers = $this->buildDriverChain();
        $last    = null;

        foreach ($drivers as $driver) {
            try {
                $result = $driver->complete($system, $user);
                return [
                    'variations'    => $this->parseVariations($result['content'], (string) ($params['platform'] ?? 'instagram'), (string) ($params['content_type'] ?? 'post')),
                    'provider'      => $driver->name(),
                    'model'         => $driver->model(),
                    'input_tokens'  => $result['input_tokens'],
                    'output_tokens' => $result['output_tokens'],
                ];
            } catch (Throwable $e) {
                Log::warning("AI driver [{$driver->name()}] failed: {$e->getMessage()}");
                $last = $e;
            }
        }

        throw new RuntimeException(
            'فشل توليد المحتوى من جميع مزودي الذكاء الاصطناعي. حاول مرة أخرى لاحقاً.',
            0,
            $last
        );
    }

    /** @param array<string, mixed> $params */
    private function buildSystemPrompt(array $params): string
    {
        $dialect         = self::DIALECT_INSTRUCTIONS[$params['dialect']] ?? self::DIALECT_INSTRUCTIONS['fos'];
        $type            = self::CONTENT_TYPE_INSTRUCTIONS[$params['content_type']] ?? self::CONTENT_TYPE_INSTRUCTIONS['post'];
        $limit           = self::PLATFORM_LIMITS[$params['platform']] ?? 2200;
        $platform        = $params['platform'];
        $platformFormat  = self::PLATFORM_FORMAT[$platform] ?? '';
        $emojiLine       = ($params['include_emojis'] ?? true)
            ? 'أضف 3-5 إيموجيات Unicode حقيقية في كل نص (مثال: 🌟 ✨ 🎯 💎 🛍️). ادمجها بشكل طبيعي داخل النص — لا تضعها كلها في نهاية النص.'
            : 'لا تستخدم إيموجيات نهائياً.';
        $includeHashtags = $params['include_hashtags'] ?? true;
        $hashtagRule     = $includeHashtags
            ? '- أضف 5-7 هاشتاقات ذات صلة في حقل TAGS لكل خيار (عربية أو إنجليزية حسب المنصة)'
            : '- اترك حقل TAGS فارغاً — المستخدم أوقف الهاشتاقات';

        $brandSection = '';
        if (($params['use_brand'] ?? true) && ! empty($params['brand_identity'])) {
            $brand        = $params['brand_identity'];
            $brandSection = "\n\nهوية العلامة التجارية:\n" .
                "- الوصف: {$brand['description']}\n" .
                "- النبرة: {$brand['tone']}\n" .
                (! empty($brand['banned_words'])
                    ? '- كلمات محظورة: ' . implode('، ', (array) $brand['banned_words']) . "\n"
                    : '');
        }

        $wsTypeSection = '';
        if (($params['entity_type'] ?? 'business') === 'persona') {
            $niche         = ! empty($params['workspace_type']) ? " المجال: {$params['workspace_type']}." : '';
            $wsTypeSection = "\n\nنوع الحساب: بيرسونة شخصية / مؤثر.{$niche}" .
                "\n- اكتب بضمير المتكلم ('أنا'، 'شاركتكم'، 'اليوم جربت'، 'رأيي الشخصي')" .
                "\n- الأسلوب شخصي وحقيقي يعكس تجارب وشخصية صاحب الحساب" .
                "\n- لا تستخدم ضمير الجمع مثل 'متجرنا' أو 'خدماتنا' أو 'منتجاتنا'";
        } elseif (! empty($params['workspace_type'])) {
            $wsCtx = self::WORKSPACE_TYPE_CONTEXT[$params['workspace_type']] ?? '';
            if ($wsCtx) {
                $wsTypeSection = "\n\nسياق مساحة العمل: {$wsCtx}";
            }
        }

        return <<<SYSTEM
أنت كاتب محتوى خليجي محترف متخصص في التسويق الرقمي.

اللهجة: {$dialect}
نوع المحتوى: {$type}
المنصة: {$platform} (الحد الأقصى للحروف: {$limit})
متطلبات تنسيق المنصة: {$platformFormat}
{$emojiLine}{$brandSection}{$wsTypeSection}

قواعد صارمة:
- لا تتجاوز الحد الأقصى للحروف للمنصة
- اكتب دائماً من اليمين إلى اليسار
- اكتب نصاً عادياً فقط — ممنوع استخدام Markdown مثل ** أو __ أو * أو # أو ```
{$hashtagRule}
SYSTEM;
    }

    /** @param array<string, mixed> $params */
    private function buildUserPrompt(array $params): string
    {
        $lengthMap   = ['short' => '50-100', 'med' => '150-250', 'long' => '300-600'];
        $length      = $lengthMap[$params['length'] ?? 'med'] ?? '150-250';
        $cta         = ! empty($params['cta']) ? "\nCTA المطلوب: {$params['cta']}" : '';
        $isAd        = ($params['content_type'] ?? '') === 'ad';

        if ($isAd) {
            return <<<PROMPT
الفكرة: {$params['prompt']}
الطول المطلوب للـ body: {$length} حرف تقريباً{$cta}

اكتب 3 نصوص إعلانية مختلفة. كل خيار يحتوي على:
- HEADLINE: جملة قصيرة جذابة (لا تتجاوز 40 حرفاً) — عنوان الإعلان كما يظهر تحت الصورة في فيسبوك/انستجرام
- DESCRIPTION: جملة واحدة مختصرة جداً (لا تتجاوز 30 حرفاً) — نص مكمّل يظهر تحت الهيدلاين
- BODY: النص الإعلاني الكامل المقنع مع CTA

استخدم هذا التنسيق بالضبط:

===OPTION_1===
TITLE: الخيار ١ · [وصف الأسلوب]
HEADLINE: [عنوان الإعلان الجذاب]
DESCRIPTION: [الوصف المكمّل]
BODY:
[النص الإعلاني الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===

===OPTION_2===
TITLE: الخيار ٢ · [وصف الأسلوب]
HEADLINE: [عنوان الإعلان الجذاب]
DESCRIPTION: [الوصف المكمّل]
BODY:
[النص الإعلاني الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===

===OPTION_3===
TITLE: الخيار ٣ · [وصف الأسلوب]
HEADLINE: [عنوان الإعلان الجذاب]
DESCRIPTION: [الوصف المكمّل]
BODY:
[النص الإعلاني الكامل]
TAGS: [هاشتاق١] [هاشتاق٢] [هاشتاق٣] [هاشتاق٤] [هاشتاق٥]
===END===
PROMPT;
        }

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
     * @return array<int, array{title: string, body: string, headline?: string, description?: string, tags: string[], char_count: int}>
     */
    private function parseVariations(string $raw, string $platform, string $contentType = 'post'): array
    {
        $results = [];
        $isAd    = $contentType === 'ad';
        $limit   = self::PLATFORM_LIMITS[$platform] ?? 2200;

        preg_match_all('/===OPTION_\d+===(.*?)===END===/s', $raw, $matches);

        foreach ($matches[1] as $block) {
            $block = trim($block);

            preg_match('/TITLE:\s*(.+)/u', $block, $titleM);
            preg_match('/BODY:\s*(.*?)(?=TAGS:|$)/su', $block, $bodyM);
            preg_match('/TAGS:\s*(.+)/u', $block, $tagsM);

            $title = trim($titleM[1] ?? 'خيار');
            $body  = $this->stripMarkdown(trim($bodyM[1] ?? $block));
            $body  = mb_substr($body, 0, $limit); // CG-11: enforce limit

            $tagsRaw = trim($tagsM[1] ?? '');
            $tags    = array_values(array_filter(
                preg_split('/\s+/', $tagsRaw) ?: [],
                fn (string $t) => str_starts_with($t, '#')
            ));

            $variation = [
                'title'      => $title,
                'body'       => $body,
                'tags'       => array_slice($tags, 0, 10),
                'char_count' => mb_strlen($body),
            ];

            if ($isAd) {
                preg_match('/HEADLINE:\s*(.+)/u', $block, $headlineM);
                preg_match('/DESCRIPTION:\s*(.+)/u', $block, $descM);
                $variation['headline']    = trim($headlineM[1] ?? '');
                $variation['description'] = trim($descM[1] ?? '');
            }

            $results[] = $variation;
        }

        if (empty($results)) {
            $results[] = ['title' => 'الخيار ١', 'body' => $this->stripMarkdown($raw), 'tags' => [], 'char_count' => mb_strlen($raw)];
        }

        return $results;
    }

    private function stripMarkdown(string $text): string
    {
        // **bold** و __bold__
        $text = preg_replace('/\*\*(.+?)\*\*/su', '$1', $text) ?? $text;
        $text = preg_replace('/__(.+?)__/su', '$1', $text) ?? $text;
        // *italic* و _italic_ (single — not part of Arabic punctuation)
        $text = preg_replace('/(?<!\w)\*([^*\n]+)\*(?!\w)/su', '$1', $text) ?? $text;
        $text = preg_replace('/(?<!\w)_([^_\n]+)_(?!\w)/su', '$1', $text) ?? $text;
        // ## headers at start of line — double+ # stripped even without space (##word)
        // Single # with space only (preserve #hashtag)
        $text = preg_replace('/^#{2,6}\s*/mu', '', $text) ?? $text;
        $text = preg_replace('/^#\s+/mu', '', $text) ?? $text;
        // `code`
        $text = preg_replace('/`([^`]+)`/su', '$1', $text) ?? $text;
        // Orphaned ** / __ left after pair-stripping above
        $text = preg_replace('/\*{2,}/', '', $text) ?? $text;
        $text = preg_replace('/_{2,}/', '', $text) ?? $text;
        // Unicode directional/zero-width control chars that render as broken symbols
        $text = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $text) ?? $text;

        return trim($text);
    }
}

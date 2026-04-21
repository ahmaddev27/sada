---
title: دمج Laravel AI SDK في مشروع صدى (Sada)

---

# دمج Laravel AI SDK في مشروع صدى (Sada)
## دليل تطبيق عملي — من التثبيت حتى Production

> **ملاحظة استراتيجية أولاً:** Laravel أطلقت AI SDK رسمي (first-party) في فبراير 2026 كجزء من Laravel 12/13. هذا هو الخيار الصحيح لصدى — مش `prism-php` ولا `ghdj/laravel-ai-integration` ولا باكجات طرف ثالث. السبب: دعم طويل المدى من فريق Laravel، تكامل أعمق مع Horizon/Queue/Testing، و built-in fakes للاختبار.

---

## 1. التثبيت والإعداد الأولي

```bash
composer require laravel/ai

php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"

php artisan migrate
```

هذا ينشئ:
- `config/ai.php` — إعدادات الـ providers والموديلات الافتراضية
- Migration لجداول `agent_conversations` و `agent_conversation_messages` (لتخزين سياق المحادثات)

### `.env` لمشروع صدى

```env
# AI Providers
OPENAI_API_KEY=sk-proj-xxxxx
ANTHROPIC_API_KEY=sk-ant-xxxxx
GEMINI_API_KEY=AIzaxxxxx

# Default models (نحدد الموديلات حسب المهمة لاحقاً)
AI_DEFAULT_TEXT_PROVIDER=anthropic
AI_DEFAULT_TEXT_MODEL=claude-sonnet-4-5
AI_FALLBACK_PROVIDER=openai
AI_FALLBACK_MODEL=gpt-4o
```

### `config/ai.php` — الإعداد الموصى به لصدى

```php
<?php

return [
    'default' => env('AI_DEFAULT_TEXT_PROVIDER', 'anthropic'),

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'url' => env('OPENAI_BASE_URL'),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
            'url' => env('ANTHROPIC_BASE_URL'),
        ],
        'gemini' => [
            'driver' => 'gemini',
            'key' => env('GEMINI_API_KEY'),
        ],
    ],

    'defaults' => [
        'text' => [
            'provider' => env('AI_DEFAULT_TEXT_PROVIDER', 'anthropic'),
            'model' => env('AI_DEFAULT_TEXT_MODEL', 'claude-sonnet-4-5'),
        ],
    ],

    // إعدادات خاصة بصدى — task-based routing
    'task_routing' => [
        'arabic_content' => ['provider' => 'anthropic', 'model' => 'claude-sonnet-4-5'],
        'dialect_content' => ['provider' => 'anthropic', 'model' => 'claude-sonnet-4-5'],
        'hashtags' => ['provider' => 'gemini', 'model' => 'gemini-2.0-flash'],
        'ad_copy' => ['provider' => 'openai', 'model' => 'gpt-4o'],
        'insights_analytics' => ['provider' => 'gemini', 'model' => 'gemini-2.0-flash'],
    ],
];
```

**لماذا Claude الافتراضي للمحتوى العربي؟** تجريبياً، Claude Sonnet يعطي جودة أعلى في النصوص العربية الطويلة واللهجات الخليجية مقارنة بـ GPT-4o و Gemini. Gemini Flash للمهام الخفيفة (hashtags, insights) لأنه أرخص 10x وسريع.

---

## 2. البنية المعمارية في صدى

### هيكل المجلدات الموصى به

```
app/
├── Ai/
│   ├── Agents/
│   │   ├── ContentGeneratorAgent.php      ← توليد منشورات
│   │   ├── SeasonalCampaignAgent.php      ← حملات موسمية
│   │   ├── AdCopyAgent.php                ← نصوص إعلانات
│   │   ├── HashtagAgent.php               ← hashtags
│   │   ├── InsightsAgent.php              ← AI Insights للتحليلات
│   │   └── StrategyAgent.php              ← content calendar استراتيجي
│   ├── Tools/
│   │   ├── GetBrandIdentity.php           ← جلب Brand Identity للـ workspace
│   │   ├── GetSeasonalContext.php         ← جلب معلومات المناسبة
│   │   └── GetHistoricalPerformance.php   ← تحليل أداء سابق
│   ├── Schemas/
│   │   ├── GeneratedPostSchema.php        ← structured output للمنشور
│   │   └── CampaignPlanSchema.php         ← خطة حملة كاملة
│   └── Middleware/
│       ├── TrackTokenUsage.php            ← حساب التوكنز per workspace
│       └── EnforceWorkspaceQuota.php      ← فرض حد الاستهلاك
```

**لماذا Agents منفصلة لكل مهمة؟** كل agent له system prompt، tools، و structured output خاص. هذا يعطيك test-ability و separation of concerns، بدل `if/else` كبير في Service واحد.

---

## 3. Agent أول: توليد المحتوى (Core Feature)

### إنشاء الـ Agent

```bash
php artisan make:agent ContentGeneratorAgent --structured
```

### `app/Ai/Agents/ContentGeneratorAgent.php`

```php
<?php

namespace App\Ai\Agents;

use App\Ai\Schemas\GeneratedPostSchema;
use App\Ai\Tools\GetBrandIdentity;
use App\Models\Workspace;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;

class ContentGeneratorAgent implements Agent, HasStructuredOutput, HasTools
{
    public function __construct(
        public readonly Workspace $workspace,
        public readonly string $contentType, // 'post' | 'reel' | 'story' | 'ad'
        public readonly string $platform,    // 'instagram' | 'facebook'
        public readonly string $dialect,     // 'formal' | 'saudi' | 'emirati' | ...
        public readonly bool $useBrandIdentity = true,
    ) {}

    public function instructions(): string
    {
        $dialectGuide = $this->getDialectGuide();
        $platformRules = $this->getPlatformRules();

        return <<<PROMPT
        أنت خبير تسويق رقمي متخصص في السوق الخليجي (السعودية، الإمارات، الكويت، قطر، البحرين، عمان).
        مهمتك توليد محتوى تسويقي عالي الجودة للسوشيال ميديا.

        # نوع المحتوى المطلوب: {$this->contentType}
        # المنصة: {$this->platform}
        # اللهجة: {$this->dialect}

        ## إرشادات اللهجة:
        {$dialectGuide}

        ## قواعد المنصة:
        {$platformRules}

        ## قواعد الجودة (إلزامية):
        - اكتب بعربية أصيلة، ليست ترجمة من الإنجليزية
        - استخدم تعابير خليجية محلية إذا اللهجة تتطلب ذلك
        - تجنب الكليشيهات ("في عالمنا اليوم..."، "هل تعلم أن...")
        - المحتوى يجب أن يثير فعلاً محدداً (قراءة، تفاعل، شراء)
        - لا تستخدم emojis عشوائية — فقط عند الضرورة التسويقية
        - Hook قوي في أول جملة — السوشيال ميديا لا تعطي فرصة ثانية

        ## المخرجات:
        يجب أن ترجع 3 خيارات مختلفة في الأسلوب، كلها تخدم نفس الهدف.
        PROMPT;
    }

    public function tools(): array
    {
        return [
            GetBrandIdentity::class,
        ];
    }

    public function schema(): JsonSchema
    {
        return new GeneratedPostSchema;
    }

    private function getDialectGuide(): string
    {
        return match ($this->dialect) {
            'formal' => 'استخدم العربية الفصحى الحديثة — مفهومة لكل الخليج، تناسب العلامات الفاخرة والمؤسسية.',
            'saudi' => 'استخدم اللهجة السعودية (نجدية أو حجازية مبسطة). كلمات مثل: "يا حبّ"، "عسا"، "كذا"، "أبي أقولك". تجنب التعقيد.',
            'emirati' => 'استخدم اللهجة الإماراتية. كلمات مثل: "شو"، "يبا"، "عادي"، "حق". نبرة دافئة وواثقة.',
            'kuwaiti' => 'استخدم اللهجة الكويتية. كلمات مثل: "شلون"، "يبيله"، "حركات". نبرة حيوية.',
            'qatari' => 'استخدم اللهجة القطرية. قريبة من الإماراتية والبحرينية مع خصوصية قطرية.',
            'bahraini' => 'استخدم اللهجة البحرينية. كلمات مثل: "أشلونك"، "خوش". نبرة ودودة.',
            'omani' => 'استخدم اللهجة العمانية. أقرب للفصحى قليلاً مع خصوصيات محلية.',
            default => 'استخدم العربية الفصحى الحديثة.',
        };
    }

    private function getPlatformRules(): string
    {
        return match ($this->platform) {
            'instagram' => "- الطول المثالي: 125-150 حرف في أول سطر (قبل 'المزيد')\n- استخدم سطور قصيرة مع مسافات\n- 5-10 hashtags في نهاية المنشور",
            'facebook' => "- الطول المثالي: 40-80 حرف للـ engagement الأعلى\n- hashtags أقل (2-3)\n- أسلوب أكثر محادثة",
            default => '',
        };
    }
}
```

### `app/Ai/Schemas/GeneratedPostSchema.php` — Structured Output

```php
<?php

namespace App\Ai\Schemas;

use Illuminate\JsonSchema\JsonSchema;

class GeneratedPostSchema
{
    public function __invoke(JsonSchema $schema): JsonSchema
    {
        return $schema->object([
            'variations' => $schema->array()
                ->items($schema->object([
                    'caption' => $schema->string()->description('نص المنشور الرئيسي'),
                    'hashtags' => $schema->array()->items($schema->string()),
                    'cta' => $schema->string()->description('Call-to-action واضح'),
                    'tone_note' => $schema->string()->description('وصف مختصر لأسلوب هذا الخيار'),
                ]))
                ->minItems(3)
                ->maxItems(3),
        ])->required(['variations']);
    }
}
```

### `app/Ai/Tools/GetBrandIdentity.php` — Tool للـ Brand

```php
<?php

namespace App\Ai\Tools;

use App\Models\Workspace;
use Laravel\Ai\Contracts\Tool;

class GetBrandIdentity implements Tool
{
    public function __construct(
        public readonly Workspace $workspace,
    ) {}

    public function name(): string
    {
        return 'get_brand_identity';
    }

    public function description(): string
    {
        return 'جلب هوية العلامة التجارية للـ workspace الحالي (الاسم، النبرة، الكلمات المحظورة، الأمثلة السابقة). استخدم هذه الأداة دائماً قبل توليد المحتوى لضمان التوافق مع هوية العلامة.';
    }

    public function handle(): array
    {
        $brand = $this->workspace->brandIdentity;

        if (!$brand) {
            return ['message' => 'لا توجد هوية علامة محددة، استخدم الإعدادات الافتراضية.'];
        }

        return [
            'brand_name' => $brand->name,
            'description' => $brand->description,
            'tone' => $brand->tone,
            'banned_words' => $brand->banned_words ?? [],
            'example_posts' => $brand->example_posts ?? [],
            'target_audience' => $brand->target_audience,
        ];
    }
}
```

---

## 4. استدعاء الـ Agent من Controller / Action

```php
<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ContentGeneratorAgent;
use App\Http\Requests\GenerateContentRequest;
use App\Jobs\TrackTokenUsage;
use Laravel\Ai\Facades\Ai;

class ContentController extends Controller
{
    public function generate(GenerateContentRequest $request)
    {
        $workspace = $request->user()->currentWorkspace;

        $agent = new ContentGeneratorAgent(
            workspace: $workspace,
            contentType: $request->content_type,
            platform: $request->platform,
            dialect: $request->dialect,
            useBrandIdentity: $request->use_brand_identity,
        );

        $response = Ai::agent($agent)
            ->prompt($request->user_prompt)
            ->generate();

        // حساب التوكنز (للـ billing)
        TrackTokenUsage::dispatch(
            workspaceId: $workspace->id,
            userId: $request->user()->id,
            inputTokens: $response->usage->inputTokens,
            outputTokens: $response->usage->outputTokens,
            task: 'content_generation',
        );

        return response()->json([
            'variations' => $response->data['variations'],
            'usage' => [
                'tokens_consumed' => $response->usage->totalTokens,
                'balance_remaining' => $request->user()->tokenBalance(),
            ],
        ]);
    }
}
```

---

## 5. Streaming للـ UX الأسرع (مهم لصدى)

المستخدم ما يستنى 8 ثواني شاشة فاضية. استخدم streaming + Laravel Reverb (WebSockets):

```php
public function generateStream(GenerateContentRequest $request)
{
    $agent = new ContentGeneratorAgent(...);

    return response()->stream(function () use ($agent, $request) {
        Ai::agent($agent)
            ->prompt($request->user_prompt)
            ->stream(function ($chunk) {
                echo "data: " . json_encode(['text' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            });
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no', // لـ Nginx
    ]);
}
```

في Vue/Inertia frontend:

```javascript
const eventSource = new EventSource('/api/content/generate-stream?...');
eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    this.streamedText += data.text;
};
```

---

## 6. Queueing المهام الثقيلة (حملات موسمية = 14 منشور)

توليد حملة كاملة (14 منشور لليوم الوطني مثلاً) ما يتم sync — ياخذ 40-60 ثانية. ارمي المهمة على Queue:

```php
<?php

namespace App\Jobs;

use App\Ai\Agents\SeasonalCampaignAgent;
use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Ai\Facades\Ai;

class GenerateSeasonalCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 دقائق
    public $tries = 2;

    public function __construct(
        public Workspace $workspace,
        public string $season,     // 'saudi_national_day' | 'ramadan' | ...
        public int $postCount,     // 7 | 10 | 14
        public array $preferences, // tone, industry, goals
    ) {}

    public function handle()
    {
        $agent = new SeasonalCampaignAgent(
            workspace: $this->workspace,
            season: $this->season,
            postCount: $this->postCount,
            preferences: $this->preferences,
        );

        $response = Ai::agent($agent)
            ->prompt("ولّد حملة كاملة لـ {$this->season}")
            ->generate();

        // حفظ المنشورات كـ drafts
        foreach ($response->data['posts'] as $post) {
            $this->workspace->posts()->create([
                'status' => 'draft',
                'content' => $post['caption'],
                'hashtags' => $post['hashtags'],
                'scheduled_for' => $post['suggested_datetime'],
                'campaign_id' => $response->data['campaign_id'],
            ]);
        }

        // إشعار المستخدم عبر Reverb
        broadcast(new CampaignReadyEvent($this->workspace, $response->data['campaign_id']));
    }
}
```

**Horizon config** (`config/horizon.php`) — queue منفصل للـ AI jobs:

```php
'defaults' => [
    'ai' => [
        'connection' => 'redis',
        'queue' => ['ai-heavy'],
        'balance' => 'auto',
        'maxProcesses' => 5,   // حد concurrent AI calls
        'memory' => 512,
        'tries' => 2,
        'timeout' => 300,
    ],
],
```

---

## 7. Failover Strategy (حرج لـ Production)

الـ AI SDK فيه failover مدمج — لما Anthropic يطيح، يتحول تلقائياً لـ OpenAI:

```php
use Laravel\Ai\Facades\Ai;
use Laravel\Ai\Enums\Lab;

$response = Ai::agent($agent)
    ->failover([Lab::Anthropic, Lab::OpenAI, Lab::Gemini])
    ->prompt($userPrompt)
    ->generate();
```

**لصدى:** هذا ميزة حرجة لأن الإطلاق يوم اليوم الوطني = load عالي = احتمال rate limits.

---

## 8. Token Tracking & Billing (الأهم للـ Pay-as-you-go)

### Middleware يحسب التوكنز تلقائياً

```php
<?php

namespace App\Ai\Middleware;

use App\Models\TokenTransaction;
use Closure;
use Laravel\Ai\Contracts\AgentMiddleware;

class TrackTokenUsage implements AgentMiddleware
{
    public function __construct(
        public readonly int $workspaceId,
        public readonly string $task,
    ) {}

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // تحويل توكنز الـ AI إلى توكنز صدى (نسبة markup)
        $sadaTokens = $this->calculateSadaTokens(
            provider: $response->provider,
            inputTokens: $response->usage->inputTokens,
            outputTokens: $response->usage->outputTokens,
        );

        TokenTransaction::create([
            'workspace_id' => $this->workspaceId,
            'task' => $this->task,
            'provider' => $response->provider,
            'model' => $response->model,
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'sada_tokens_charged' => $sadaTokens,
        ]);

        return $response;
    }

    private function calculateSadaTokens($provider, $inputTokens, $outputTokens): int
    {
        // معادلة مبنية على تكلفة الـ API + margin 70%
        $costUsd = match ($provider) {
            'anthropic' => ($inputTokens * 0.000003) + ($outputTokens * 0.000015),
            'openai'    => ($inputTokens * 0.0000025) + ($outputTokens * 0.00001),
            'gemini'    => ($inputTokens * 0.00000015) + ($outputTokens * 0.0000006),
        };

        // 1 Sada token = $0.003 (بعد الـ margin)
        return (int) ceil(($costUsd / 0.003) * 1); // 70% margin مدمج في السعر
    }
}
```

### استخدام الـ Middleware

```php
$response = Ai::agent($agent)
    ->middleware(new TrackTokenUsage($workspace->id, 'content_generation'))
    ->prompt($userPrompt)
    ->generate();
```

---

## 9. Caching للتوفير (توليدات متشابهة)

```php
use Illuminate\Support\Facades\Cache;

public function generate(GenerateContentRequest $request)
{
    // Cache key من hash الـ inputs
    $cacheKey = 'ai_content:' . md5(json_encode([
        $request->content_type,
        $request->platform,
        $request->dialect,
        $request->user_prompt,
        $request->use_brand_identity ? $workspace->brandIdentity->updated_at : null,
    ]));

    // Cache لـ 24 ساعة — نفس الـ prompt ما يولد مرتين
    return Cache::remember($cacheKey, 86400, function () use ($agent, $request) {
        return Ai::agent($agent)
            ->prompt($request->user_prompt)
            ->generate()
            ->data;
    });
}
```

**توفير محتمل:** 15-30% من تكاليف AI في الأشهر الأولى، أعلى مع نمو الـ user base.

---

## 10. Testing (هذي ميزة SDK الرسمي الكبيرة)

### Unit test للـ Agent

```php
<?php

namespace Tests\Feature\Ai;

use App\Ai\Agents\ContentGeneratorAgent;
use App\Models\Workspace;
use Laravel\Ai\Facades\Ai;
use Tests\TestCase;

class ContentGeneratorAgentTest extends TestCase
{
    public function test_generates_three_variations_in_saudi_dialect()
    {
        // Fake الـ AI response
        Ai::fake([
            'variations' => [
                ['caption' => 'عرض حصري للسعوديين...', 'hashtags' => ['#اليوم_الوطني'], 'cta' => 'اطلب الآن', 'tone_note' => 'حماسي'],
                ['caption' => 'احتفالاً بالوطن...', 'hashtags' => ['#نحتفل_بالوطن'], 'cta' => 'تسوّق', 'tone_note' => 'راقٍ'],
                ['caption' => 'هدية خاصة لكم...', 'hashtags' => ['#هدية_الوطن'], 'cta' => 'احصل عليها', 'tone_note' => 'شخصي'],
            ],
        ]);

        $workspace = Workspace::factory()->create();

        $agent = new ContentGeneratorAgent(
            workspace: $workspace,
            contentType: 'post',
            platform: 'instagram',
            dialect: 'saudi',
        );

        $response = Ai::agent($agent)
            ->prompt('خصم اليوم الوطني')
            ->generate();

        $this->assertCount(3, $response->data['variations']);
        $this->assertStringContainsString('السعوديين', $response->data['variations'][0]['caption']);
    }

    public function test_assertions_on_ai_calls()
    {
        Ai::fake();

        // ... trigger action that calls AI

        Ai::assertGenerated(ContentGeneratorAgent::class);
        Ai::assertGeneratedCount(1);
    }
}
```

---

## 11. خارطة تطبيق في صدى — أسبوع بأسبوع

| الأسبوع | Agent/Feature | الأولوية |
|---------|---------------|----------|
| 1 | Install + Config + `ContentGeneratorAgent` + Brand Identity Tool | Must |
| 2 | Streaming + Frontend integration (Vue + SSE) | Must |
| 3 | Token tracking middleware + Billing integration | Must |
| 4 | `HashtagAgent` + `AdCopyAgent` | Must |
| 5 | `SeasonalCampaignAgent` + Queue workers + Horizon | Must |
| 6 | `InsightsAgent` (للـ analytics dashboard) | Should |
| 7 | Caching layer + Failover testing | Must |
| 8 | Test coverage + Production hardening | Must |

---

## 12. Gotchas ومخاطر عملية

### 🔴 حرجة

1. **Rate Limits:** Anthropic 50 req/min افتراضياً. استخدم Laravel's `RateLimiter` + queue throttling. خلال IYD (يوم وطني) ممكن تضرب السقف.
2. **Token cost explosion:** Agent بدون `maxTokens` cap ممكن يولد 4000 token منشور واحد. ضع `->maxTokens(800)` على كل agent.
3. **RTL في structured output:** JSON output من LLM أحياناً يقلب ترتيب الـ keys الحاوية عربي. استخدم `ensureAscii=false` عند الـ encoding.
4. **Streaming + Inertia:** Inertia ما يدعم SSE مباشرة. استخدم fetch API عادي من Vue، أو ارفع النتيجة لـ Pinia store.
5. **Concurrent generations per workspace:** وكالة بـ 30 عميل ممكن تولد 30 منشور مرة وحدة. طبق `rateLimiter()->perWorkspace(5)`.

### ⚠️ متوسطة

6. **Prompt injection:** مستخدم خبيث ممكن يكتب prompt يكسر system prompt. استخدم `->sanitize()` middleware على user input.
7. **LLM يولد كلمات محظورة:** أضف post-processing filter يراجع الـ banned_words من Brand Identity ويرفض التوليد إذا ظهرت.
8. **Fallback quality mismatch:** Claude يكتب عربي أجود من GPT-4o. إذا طاح Anthropic و تحولت لـ OpenAI، المستخدم ممكن يلاحظ فرق جودة. وثّق هذا في الـ UI ("تم استخدام موديل بديل").

---

## 13. الخلاصة والتوصية النهائية

**استخدم Laravel AI SDK الرسمي** — هو الخيار الصحيح لصدى لأن:

1. First-party من Laravel = دعم طويل المدى، ما رح يموت الباكج
2. Built-in testing (fakes, assertions) = CI/CD مريح
3. Failover + streaming + queueing متكاملة مع Horizon
4. Multi-provider موحد = تقدر تغير Claude إلى Gemini بسطر واحد في الـ config
5. Agents abstraction ممتازة = الكود قابل للتوسعة بدون refactor

**البديل الثاني لو ما تبغى الرسمي:** [`prism-php/prism`](https://prismphp.com) — ناضج، mature، community كبير، لكنه طرف ثالث.

**لا تستخدم:** `ghdj/laravel-ai-integration` (1 install، community صفر، لا يناسب production) أو `openai-php/client` مباشرة (تضطر تكتب كل الـ abstractions بنفسك).

---

## الخطوة التالية

ابدأ بـ `ContentGeneratorAgent` فقط في الأسبوع الأول. اختبره على 10 prompts عربية حقيقية بلهجات مختلفة قبل ما تبني بقية الـ agents. الجودة هنا تحدد نجاح المنصة كلها.

تبغى أبني لك الـ `ContentGeneratorAgent` الكامل بـ prompt engineering متقدم + unit tests جاهزة كـ starting point؟
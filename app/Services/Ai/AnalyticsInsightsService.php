<?php

// ANL-03: AI-generated analytics insights in Arabic

namespace App\Services\Ai;

use App\Services\Ai\Drivers\AnthropicDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\GroqDriver;
use App\Services\Ai\Drivers\OpenAiDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyticsInsightsService
{
    /**
     * Return 3 Arabic insights derived from the supplied analytics data.
     * Results are cached per workspace+filters for 6 hours to avoid redundant AI calls.
     *
     * @param  int                       $workspaceId
     * @param  array<string, mixed>      $kpis              Output of buildKpis()
     * @param  array<int, array<string, mixed>> $platformBreakdown Output of buildPlatformBreakdown()
     * @param  array<int, array<string, mixed>> $topPosts          Output of buildTopPosts()
     * @param  array<string, mixed>      $filters
     * @return string[]                  Array of 3 insight strings
     */
    public function insights(
        int   $workspaceId,
        array $kpis,
        array $platformBreakdown,
        array $topPosts,
        array $filters,
    ): array {
        $cacheKey = 'analytics_insights:' . md5(
            $workspaceId . json_encode($filters) . json_encode($kpis)
        );

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($kpis, $platformBreakdown, $topPosts) {
            return $this->generate($kpis, $platformBreakdown, $topPosts);
        });
    }

    /**
     * @param  array<string, mixed>      $kpis
     * @param  array<int, array<string, mixed>> $platformBreakdown
     * @param  array<int, array<string, mixed>> $topPosts
     * @return string[]
     */
    private function generate(array $kpis, array $platformBreakdown, array $topPosts): array
    {
        $system = $this->buildSystemPrompt();
        $user   = $this->buildUserPrompt($kpis, $platformBreakdown, $topPosts);

        foreach ($this->buildDriverChain() as $driver) {
            try {
                $raw = $driver->complete($system, $user);
                $parsed = $this->parseInsights($raw);
                if (count($parsed) >= 1) {
                    return $parsed;
                }
            } catch (Throwable $e) {
                Log::warning("AnalyticsInsightsService [{$driver->name()}] failed: {$e->getMessage()}");
            }
        }

        return $this->fallbackInsights($kpis, $platformBreakdown);
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
أنت محلل بيانات تسويقية متخصص في أسواق الخليج العربي. مهمتك تحليل بيانات أداء المحتوى الرقمي وتقديم 3 insights قابلة للتنفيذ.

قواعد صارمة:
- اكتب بالعربية الفصحى الحديثة
- كل insight جملة واحدة أو جملتان مختصرتان فقط — لا تطويل
- اجعل كل insight محددة وقابلة للتنفيذ (actionable) بناءً على الأرقام الفعلية
- لا تستخدم نقاط bullets أو أرقام أو مقدمات — ابدأ مباشرة بالـ insight
- افصل بين كل insight بسطر فارغ واحد
- إذا كانت البيانات صفرية، اذكر ذلك وأعطِ توصية للبدء
PROMPT;
    }

    /**
     * @param array<string, mixed>           $kpis
     * @param array<int, array<string, mixed>> $platformBreakdown
     * @param array<int, array<string, mixed>> $topPosts
     */
    private function buildUserPrompt(array $kpis, array $platformBreakdown, array $topPosts): string
    {
        $platformLines = empty($platformBreakdown)
            ? 'لا توجد بيانات منصات بعد.'
            : implode("\n", array_map(
                fn ($p) => "- {$p['platform']}: وصول {$p['total_reach']} · تفاعل {$p['total_engagement']} · ظهور {$p['total_impressions']}",
                $platformBreakdown
            ));

        $topPostLines = empty($topPosts)
            ? 'لا توجد بيانات منشورات بعد.'
            : implode("\n", array_map(
                fn ($p, $i) => ($i + 1) . ". [{$p['platform']}] وصول {$p['total_reach']} · تفاعل {$p['total_engagement']}" .
                    ($p['content_preview'] ? " · «{$p['content_preview']}»" : ''),
                $topPosts,
                array_keys($topPosts)
            ));

        $engagementRate = $kpis['engagement_rate'] ?? 0;
        $ctr            = $kpis['ctr'] ?? 0;
        $followerGrowth = $kpis['follower_growth'] ?? 0;

        return <<<PROMPT
بيانات الأداء:

إجمالي الوصول: {$kpis['total_reach']}
إجمالي الظهور: {$kpis['total_impressions']}
إجمالي التفاعل: {$kpis['total_engagement']}
معدل التفاعل: {$engagementRate}%
معدل النقر: {$ctr}%
نمو المتابعين: {$followerGrowth}

أداء المنصات:
{$platformLines}

أفضل 5 منشورات:
{$topPostLines}

بناءً على هذه البيانات، اكتب 3 insights مفيدة وقابلة للتنفيذ مفصولة بسطر فارغ.
PROMPT;
    }

    /**
     * Parse the raw AI response into an array of up to 3 insights.
     *
     * @return string[]
     */
    private function parseInsights(string $raw): array
    {
        // Split on blank lines, clean up, keep non-empty
        $lines = array_filter(
            array_map('trim', preg_split('/\n{2,}/', trim($raw)) ?: []),
            fn (string $l) => $l !== ''
        );

        return array_slice($lines, 0, 3);
    }

    /**
     * Deterministic fallback when all AI drivers fail.
     *
     * @param  array<string, mixed>           $kpis
     * @param  array<int, array<string, mixed>> $platformBreakdown
     * @return string[]
     */
    private function fallbackInsights(array $kpis, array $platformBreakdown): array
    {
        $insights = [];

        $topPlatform = collect($platformBreakdown)->sortByDesc('total_engagement')->first();
        if ($topPlatform) {
            $insights[] = "منصة {$topPlatform['platform']} تحقق أعلى تفاعل — يُنصح بزيادة تكرار النشر عليها.";
        }

        $rate = $kpis['engagement_rate'] ?? 0;
        if ($rate > 0) {
            $insights[] = $rate >= 3
                ? "معدل التفاعل {$rate}% يفوق المتوسط الصناعي — حافظ على هذا المستوى بالاستمرار في نفس أسلوب المحتوى."
                : "معدل التفاعل {$rate}% أقل من المتوسط — جرّب محتوى تفاعلياً كالاستطلاعات والأسئلة لرفعه.";
        }

        if (($kpis['total_reach'] ?? 0) === 0) {
            $insights[] = 'لا توجد بيانات كافية بعد — ابدأ بنشر محتوى منتظم لتتمكن من قراءة الأداء.';
        } elseif (($kpis['follower_growth'] ?? 0) > 0) {
            $insights[] = "نمو المتابعين بمقدار {$kpis['follower_growth']} خلال هذه الفترة يعكس محتوى جاذباً — استثمر هذا الزخم بحملة موسمية.";
        }

        return array_slice($insights ?: ['لا توجد بيانات كافية لتوليد insights في الوقت الحالي.'], 0, 3);
    }

    /**
     * Build the same driver priority chain used by ContentGenerationService.
     *
     * @return \App\Services\Ai\Drivers\AiDriverInterface[]
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

        $primary   = (string) config('services.ai.provider', 'anthropic');
        $fallbacks = (string) config('services.ai.fallbacks', 'openai,gemini');
        $order     = array_filter([$primary, ...explode(',', $fallbacks)], fn ($k) => isset($all[$k]));

        $seen = $chain = [];
        foreach ($order as $key) {
            $key = trim($key);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $chain[]    = ($all[$key])();
            }
        }

        return $chain;
    }
}

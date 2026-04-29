<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPricingService
{
    // OpenRouter public API — no auth needed, updated continuously
    private const OPENROUTER_API = 'https://openrouter.ai/api/v1/models';
    private const CACHE_KEY      = 'ai:pricing:openrouter';
    private const CACHE_TTL      = 86400; // refresh every 24 hours

    /**
     * Map our internal model IDs → OpenRouter model IDs.
     * OpenRouter aggregates pricing from all providers and keeps it current.
     *
     * @var array<string, string>
     */
    private const MODEL_MAP = [
        // Anthropic
        'claude-3-5-haiku-20241022'  => 'anthropic/claude-3-5-haiku-20241022',
        'claude-3-5-sonnet-20241022' => 'anthropic/claude-3-5-sonnet-20241022',
        'claude-sonnet-4-6'          => 'anthropic/claude-sonnet-4-5',
        'claude-opus-4-7'            => 'anthropic/claude-opus-4',
        'claude-haiku-4-5-20251001'  => 'anthropic/claude-haiku-4-5',

        // OpenAI
        'gpt-4o-mini'   => 'openai/gpt-4o-mini',
        'gpt-4o'        => 'openai/gpt-4o',
        'gpt-4-turbo'   => 'openai/gpt-4-turbo',
        'gpt-3.5-turbo' => 'openai/gpt-3.5-turbo',

        // Google Gemini
        'gemini-2.0-flash' => 'google/gemini-2.0-flash-001',
        'gemini-1.5-flash' => 'google/gemini-flash-1.5',
        'gemini-1.5-pro'   => 'google/gemini-pro-1.5',
        'gemini-2.5-pro'   => 'google/gemini-2.5-pro-preview',

        // Groq (hosted open-source)
        'llama-3.3-70b-versatile' => 'meta-llama/llama-3.3-70b-instruct',
        'llama-3.1-8b-instant'    => 'meta-llama/llama-3.1-8b-instruct',
        'mixtral-8x7b-32768'      => 'mistralai/mixtral-8x7b-instruct',
    ];

    /**
     * Calculate cost for a generation using live OpenRouter prices.
     * Falls back to config/ai_pricing.php if OpenRouter is unreachable.
     */
    public function costFor(string $model, int $inputTokens, int $outputTokens): float
    {
        if ($inputTokens === 0 && $outputTokens === 0) {
            return 0.0;
        }

        $rates = $this->ratesFor($model);

        if ($rates === null) {
            return 0.0;
        }

        return round(
            ($inputTokens  / 1_000_000 * $rates['input']) +
            ($outputTokens / 1_000_000 * $rates['output']),
            8
        );
    }

    /**
     * Get input/output rates (per 1M tokens, USD) for a model.
     * Returns null if the model is unknown in both live pricing and config.
     *
     * @return array{input: float, output: float}|null
     */
    public function ratesFor(string $model): ?array
    {
        // 1. Try live OpenRouter pricing (cached 24h)
        $live = $this->liveRates();
        if (isset($live[$model])) {
            return $live[$model];
        }

        // 2. Fall back to static config as safety net
        $config = config("ai_pricing.models.{$model}");

        return is_array($config) ? $config : null;
    }

    /**
     * Force-refresh the cached prices from OpenRouter.
     * Called by a daily scheduled command.
     */
    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->liveRates();
    }

    /** @return array<string, array{input: float, output: float}> */
    private function liveRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $fetched = $this->fetchFromOpenRouter();
                if (! empty($fetched)) {
                    return $fetched;
                }
            } catch (\Throwable $e) {
                Log::warning('ai_pricing_fetch_failed', ['error' => $e->getMessage()]);
            }

            // Return empty so the caller falls back to config
            return [];
        });
    }

    /** @return array<string, array{input: float, output: float}> */
    private function fetchFromOpenRouter(): array
    {
        $response = Http::timeout(8)
            ->withHeaders(['HTTP-Referer' => config('app.url', 'https://sada.sa')])
            ->get(self::OPENROUTER_API);

        if (! $response->successful()) {
            return [];
        }

        $reverse = array_flip(self::MODEL_MAP);
        $pricing = [];

        foreach ($response->json('data', []) as $item) {
            $orId = $item['id'] ?? '';

            if (! isset($reverse[$orId])) {
                continue;
            }

            $p      = $item['pricing'] ?? [];
            $input  = isset($p['prompt'])     ? (float) $p['prompt']     : null;
            $output = isset($p['completion'])  ? (float) $p['completion'] : null;

            // Skip free/unknown models (price = 0 means free tier on OpenRouter)
            if ($input === null || $output === null) {
                continue;
            }

            // OpenRouter pricing is per single token → convert to per 1M tokens
            $pricing[$reverse[$orId]] = [
                'input'  => round($input  * 1_000_000, 6),
                'output' => round($output * 1_000_000, 6),
            ];
        }

        return $pricing;
    }
}

<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiDriver implements AiDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4o-mini',
        private readonly int    $maxTokens = 2048,
    ) {}

    public function complete(string $system, string $user): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages'   => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ])->throw()->json();

        return $response['choices'][0]['message']['content'] ?? '';
    }

    public function name(): string { return 'openai'; }
}

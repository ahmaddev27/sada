<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqDriver implements AiDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'llama-3.3-70b-versatile',
        private readonly int    $maxTokens = 8192,
    ) {}

    public function complete(string $system, string $user): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Groq API key is not configured.');
        }

        // Groq is OpenAI-compatible
        $response = Http::withToken($this->apiKey)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'      => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages'   => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ])->throw()->json();

        return [
            'content'       => $response['choices'][0]['message']['content']  ?? '',
            'input_tokens'  => $response['usage']['prompt_tokens']     ?? 0,
            'output_tokens' => $response['usage']['completion_tokens'] ?? 0,
        ];
    }

    public function name(): string  { return 'groq'; }
    public function model(): string { return $this->model; }
}

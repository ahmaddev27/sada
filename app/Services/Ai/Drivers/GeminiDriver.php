<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiDriver implements AiDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.0-flash',
        private readonly int    $maxTokens = 2048,
    ) {}

    public function complete(string $system, string $user): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'system_instruction' => [
                    'parts' => [['text' => $system]],
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $user]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $this->maxTokens,
                ],
            ]
        )->throw()->json();

        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function name(): string { return 'gemini'; }
}

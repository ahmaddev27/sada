<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiDriver implements AiDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.0-flash',
        private readonly int    $maxTokens = 8192,
    ) {}

    public function complete(string $system, string $user): array
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

        $usageMeta = $response['usageMetadata'] ?? [];

        return [
            'content'       => $response['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'input_tokens'  => $usageMeta['promptTokenCount']     ?? 0,
            'output_tokens' => $usageMeta['candidatesTokenCount'] ?? 0,
        ];
    }

    public function name(): string  { return 'gemini'; }
    public function model(): string { return $this->model; }
}

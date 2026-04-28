<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicDriver implements AiDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-3-5-haiku-20241022',
        private readonly int    $maxTokens = 8192,
    ) {}

    public function complete(string $system, string $user): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => $this->maxTokens,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $user]],
        ])->throw()->json();

        return [
            'content'       => $response['content'][0]['text'] ?? '',
            'input_tokens'  => $response['usage']['input_tokens']  ?? 0,
            'output_tokens' => $response['usage']['output_tokens'] ?? 0,
        ];
    }

    public function name(): string  { return 'anthropic'; }
    public function model(): string { return $this->model; }
}

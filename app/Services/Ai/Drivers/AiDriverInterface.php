<?php

namespace App\Services\Ai\Drivers;

interface AiDriverInterface
{
    /**
     * Send a completion request.
     *
     * @return array{content: string, input_tokens: int, output_tokens: int}
     */
    public function complete(string $system, string $user): array;

    public function name(): string;

    public function model(): string;
}

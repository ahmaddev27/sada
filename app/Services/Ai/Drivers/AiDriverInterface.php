<?php

namespace App\Services\Ai\Drivers;

interface AiDriverInterface
{
    /**
     * Send a completion request and return the raw text response.
     */
    public function complete(string $system, string $user): string;

    public function name(): string;
}

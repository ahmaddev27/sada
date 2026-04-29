<?php

namespace App\Console\Commands;

use App\Services\Ai\AiPricingService;
use Illuminate\Console\Command;

class RefreshAiPricing extends Command
{
    protected $signature   = 'ai:refresh-pricing';
    protected $description = 'Refresh AI model pricing from OpenRouter and update the cache';

    public function handle(AiPricingService $pricing): int
    {
        $pricing->refresh();
        $this->info('AI pricing cache refreshed from OpenRouter.');

        return self::SUCCESS;
    }
}

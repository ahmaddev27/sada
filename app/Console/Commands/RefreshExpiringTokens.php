<?php

// CON-07: auto-refresh social tokens expiring within the next hour

namespace App\Console\Commands;

use App\Actions\Social\RefreshSocialTokenAction;
use App\Models\SocialAccount;
use Illuminate\Console\Command;

class RefreshExpiringTokens extends Command
{
    protected $signature   = 'social:refresh-tokens';
    protected $description = 'Refresh social account tokens expiring within the next hour';

    public function __construct(private readonly RefreshSocialTokenAction $action)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $accounts = SocialAccount::withoutGlobalScopes()
            ->where('status', 'healthy')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addMinutes(60))
            ->get();

        $refreshed = 0;
        $failed    = 0;

        foreach ($accounts as $account) {
            try {
                $this->action->execute($account);
                $this->line("✓ {$account->provider} #{$account->id} — {$account->account_name}");
                $refreshed++;
            } catch (\Throwable $e) {
                $account->markExpired();
                $this->warn("✗ {$account->provider} #{$account->id} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done — {$refreshed} refreshed, {$failed} expired.");

        return Command::SUCCESS;
    }
}

<?php

// CON-07: social:refresh-tokens command

use App\Actions\Social\RefreshSocialTokenAction;
use App\Models\SocialAccount;
use App\Models\Workspace;
use Mockery\MockInterface;

function makeExpiringAccount(int $expiresInMinutes = 30, string $status = 'healthy'): SocialAccount
{
    $workspace = Workspace::factory()->create([
        'user_id' => \App\Models\User::factory()->create()->id,
    ]);

    return SocialAccount::factory()->for($workspace)->create([
        'status'           => $status,
        'token_expires_at' => now()->addMinutes($expiresInMinutes),
        'refresh_token'    => 'test-refresh-token',
    ]);
}

// ── Success path ──────────────────────────────────────────────────────────────

it('يجدد الحسابات المنتهية خلال ساعة', function () {
    $account = makeExpiringAccount(30);

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) use ($account) {
        $mock->shouldReceive('execute')->once()->with(
            Mockery::on(fn ($a) => $a->id === $account->id)
        );
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('1 refreshed');
});

it('لا يجدد الحسابات التي تنتهي بعد أكثر من ساعة', function () {
    makeExpiringAccount(120); // ينتهي بعد ساعتين — خارج النافذة

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->never();
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('0 refreshed');
});

it('لا يجدد الحسابات غير النشطة', function () {
    makeExpiringAccount(30, 'expired');
    makeExpiringAccount(30, 'revoked');

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->never();
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('0 refreshed');
});

it('لا يجدد الحسابات التي لا تملك تاريخ انتهاء', function () {
    $workspace = Workspace::factory()->create([
        'user_id' => \App\Models\User::factory()->create()->id,
    ]);

    SocialAccount::factory()->for($workspace)->create([
        'status'           => 'healthy',
        'token_expires_at' => null,
    ]);

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->never();
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('0 refreshed');
});

// ── Failure path ──────────────────────────────────────────────────────────────

it('يُعلّم الحساب كمنتهٍ ويكمل عند فشل التجديد', function () {
    $account = makeExpiringAccount(30);

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->once()
            ->andThrow(new RuntimeException('انتهت صلاحية رمز التحديث'));
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('0 refreshed, 1 expired');

    expect($account->fresh()->status)->toBe('expired');
});

it('يكمل التجديد لبقية الحسابات عند فشل أحدها', function () {
    $failingId = makeExpiringAccount(30)->id;
    makeExpiringAccount(45);

    $this->mock(RefreshSocialTokenAction::class, function (MockInterface $mock) use ($failingId) {
        $mock->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn (SocialAccount $a) => $a->id === $failingId))
            ->andThrow(new RuntimeException('فشل'));

        $mock->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn (SocialAccount $a) => $a->id !== $failingId));
    });

    $this->artisan('social:refresh-tokens')
        ->assertSuccessful()
        ->expectsOutputToContain('1 refreshed, 1 expired');

    expect(SocialAccount::withoutGlobalScopes()->find($failingId)->status)->toBe('expired');
});

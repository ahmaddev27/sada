<?php

// CON-06: tokens stored encrypted
// CON-08: disconnect revokes locally (and best-effort on provider)
// CON-09: needsRefresh() triggers within buffer window

use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Meta\MetaOAuthService;
use Illuminate\Support\Facades\Crypt;
use Mockery\MockInterface;

// ── CON-06: Token encryption ────────────────────────────────────────────────

it('يخزّن رمز الوصول مشفراً في قاعدة البيانات', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $account = SocialAccount::factory()
        ->for($workspace)
        ->create(['access_token' => 'رمز-سري-123']);

    $raw = \DB::table('social_accounts')->where('id', $account->id)->value('access_token');

    expect($raw)->not->toBe('رمز-سري-123');
    expect(Crypt::decryptString($raw))->toBe('رمز-سري-123');
});

it('يُرجع رمز الوصول مفككاً تلقائياً عبر الـ cast', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $account = SocialAccount::factory()
        ->for($workspace)
        ->create(['access_token' => 'رمز-واضح-456']);

    expect($account->fresh()->access_token)->toBe('رمز-واضح-456');
});

// ── CON-08: Disconnect ──────────────────────────────────────────────────────

it('يفصل الحساب ويحذفه من قاعدة البيانات', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();

    $this->mock(MetaOAuthService::class, function (MockInterface $mock) {
        $mock->shouldReceive('revokeToken')->once();
    });

    $this->actingAs($user)
        ->delete(route('social.disconnect', $account))
        ->assertRedirect(route('social.index'));

    $this->assertModelMissing($account);
});

it('يمنع المستخدم من فصل حساب لا يملكه', function () {
    $owner     = User::factory()->create();
    $attacker  = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();

    $this->actingAs($attacker)
        ->delete(route('social.disconnect', $account))
        ->assertForbidden();

    $this->assertModelExists($account);
});

it('يكمل الفصل المحلي حتى لو فشل إلغاء الرمز على Meta', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();

    $this->mock(MetaOAuthService::class, function (MockInterface $mock) {
        $mock->shouldReceive('revokeToken')->andThrow(new \RuntimeException('Meta API error'));
    });

    $this->actingAs($user)
        ->delete(route('social.disconnect', $account))
        ->assertRedirect(route('social.index'));

    $this->assertModelMissing($account);
});

// ── CON-09: Token health ────────────────────────────────────────────────────

it('يكتشف أن الرمز يحتاج تجديداً عند اقتراب انتهاء الصلاحية', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $account = SocialAccount::factory()
        ->for($workspace)
        ->create(['token_expires_at' => now()->addMinutes(30)]);

    expect($account->needsRefresh(60))->toBeTrue();
    expect($account->needsRefresh(20))->toBeFalse();
});

it('لا يحتاج تجديداً إذا لم يكن للرمز تاريخ انتهاء', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $account = SocialAccount::factory()
        ->for($workspace)
        ->create(['token_expires_at' => null]);

    expect($account->needsRefresh())->toBeFalse();
});

it('يُعيد محاولة تجديد الرمز ويحدّث الحالة إلى healthy', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->expired()->instagram()->for($workspace)->create();

    $this->mock(MetaOAuthService::class, function (MockInterface $mock) {
        $mock->shouldReceive('refreshToken')->once()->andReturn([
            'access_token' => 'رمز-جديد-789',
            'expires_in'   => 5184000,
        ]);
    });

    $this->actingAs($user)
        ->post(route('social.refresh', $account))
        ->assertRedirect(route('social.index'));

    expect($account->fresh()->status)->toBe('healthy');
    expect($account->fresh()->access_token)->toBe('رمز-جديد-789');
});

it('يُعلّم الحساب منتهياً إذا فشل التجديد', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->expired()->instagram()->for($workspace)->create();

    $this->mock(MetaOAuthService::class, function (MockInterface $mock) {
        $mock->shouldReceive('refreshToken')->andThrow(new \RuntimeException('فشل API'));
    });

    $this->actingAs($user)
        ->post(route('social.refresh', $account))
        ->assertRedirect(route('social.index'));

    expect($account->fresh()->status)->toBe('expired');
});

// ── CON-10: Index page ──────────────────────────────────────────────────────

it('يعرض صفحة الحسابات المرتبطة مع الحسابات الصحيحة', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    session(['current_workspace_id' => $workspace->id]);

    SocialAccount::factory()->instagram()->for($workspace)->create(['account_name' => 'حساب الاختبار']);
    SocialAccount::factory()->facebook()->for($workspace)->create(['account_name' => 'صفحة الاختبار']);

    $this->actingAs($user)
        ->get(route('social.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Social/Connections')
            ->has('accounts', 2)
        );
});

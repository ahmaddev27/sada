<?php

// M8: إدارة المستخدمين — حظر، رفع حظر، منح توكنات، impersonation

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user  = User::factory()->create(['is_admin' => false, 'token_balance' => 100]);
});

// ── Users list ─────────────────────────────────────────────────────────────

it('يعرض قائمة المستخدمين للمشرف', function () {
    $this->actingAs($this->admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Users/Index')
            ->has('users')
            ->has('stats')
        );
});

it('يعرض تفاصيل مستخدم', function () {
    $this->actingAs($this->admin)
        ->get("/admin/users/{$this->user->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Users/Show'));
});

// ── Ban / Unban ────────────────────────────────────────────────────────────

it('يحظر مستخدماً عادياً', function () {
    $this->actingAs($this->admin)
        ->post("/admin/users/{$this->user->id}/ban")
        ->assertRedirect();

    expect($this->user->fresh()->banned_at)->not->toBeNull();
});

it('لا يحظر مشرفاً آخر', function () {
    $otherAdmin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($this->admin)
        ->post("/admin/users/{$otherAdmin->id}/ban")
        ->assertForbidden();
});

it('يرفع الحظر عن مستخدم', function () {
    $this->user->update(['banned_at' => now()]);

    $this->actingAs($this->admin)
        ->post("/admin/users/{$this->user->id}/unban")
        ->assertRedirect();

    expect($this->user->fresh()->banned_at)->toBeNull();
});

// ── Grant tokens ───────────────────────────────────────────────────────────

it('يمنح توكنات لمستخدم', function () {
    $this->actingAs($this->admin)
        ->post("/admin/users/{$this->user->id}/grant-tokens", ['amount' => 500])
        ->assertRedirect();

    expect($this->user->fresh()->token_balance)->toBe(600);
});

it('يرفض منح توكنات بكمية غير صالحة', function () {
    $this->actingAs($this->admin)
        ->post("/admin/users/{$this->user->id}/grant-tokens", ['amount' => 0])
        ->assertSessionHasErrors('amount');
});

// ── Impersonation ──────────────────────────────────────────────────────────

it('يتيح للمشرف الانتقال لحساب مستخدم عادي', function () {
    $this->actingAs($this->admin)
        ->post("/admin/users/{$this->user->id}/impersonate")
        ->assertRedirect('/dashboard');

    expect(auth()->id())->toBe($this->user->id);
    expect(session('impersonating_admin_id'))->toBe($this->admin->id);
});

it('لا يتيح الانتقال لحساب مشرف', function () {
    $otherAdmin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($this->admin)
        ->post("/admin/users/{$otherAdmin->id}/impersonate")
        ->assertForbidden();
});

it('يوقف وضع الانتقال ويعيد للمشرف', function () {
    // During impersonation the regular user is the authenticated user
    $this->actingAs($this->user)
        ->withSession(['impersonating_admin_id' => $this->admin->id])
        ->post('/admin/impersonate/stop')
        ->assertRedirect('/admin/users');

    expect(auth()->id())->toBe($this->admin->id);
});

<?php

// WS-01: first-run onboarding flow — step 1 workspace, step 2 social connect, step 3 completion

use App\Models\User;
use App\Models\Workspace;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

// ── Step 1: Show ────────────────────────────────────────────────────────────

it('يعرض خطوة ١ لمستخدم جديد بدون مساحة عمل', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/onboarding')
        ->assertInertia(fn ($page) => $page
            ->component('Onboarding/Index')
            ->where('step', 1)
        );
});

it('يعيد التوجيه للداشبورد إذا كان المستخدم أنهى التهيئة', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['active_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get('/onboarding')
        ->assertRedirect(route('dashboard'));
});

it('يعرض خطوة ٢ إذا كانت الجلسة تحمل onboarding_step=2', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['active_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->withSession(['onboarding_step' => 2])
        ->get('/onboarding')
        ->assertInertia(fn ($page) => $page
            ->component('Onboarding/Index')
            ->where('step', 2)
        );
});

it('يعرض خطوة ٣ إذا كانت الجلسة تحمل onboarding_step=3', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['active_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->withSession(['onboarding_step' => 3])
        ->get('/onboarding')
        ->assertInertia(fn ($page) => $page
            ->component('Onboarding/Index')
            ->where('step', 3)
        );
});

// ── Step 1: Store workspace ─────────────────────────────────────────────────

it('يُنشئ مساحة العمل ويضع الجلسة على خطوة ٢', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/onboarding/workspace', [
            'name'            => 'متجر البيت السعيد',
            'business_type'   => 'متجر إلكتروني',
            'countries'       => ['sa'],
            'default_dialect' => 'sa',
        ])
        ->assertRedirect(route('onboarding'));

    $this->assertDatabaseHas('workspaces', ['name' => 'متجر البيت السعيد', 'user_id' => $user->id]);
    Notification::assertSentTo($user, WelcomeNotification::class);
});

it('يرفض إنشاء مساحة عمل بدون اسم', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/onboarding/workspace', ['name' => ''])
        ->assertSessionHasErrors('name');
});

// ── Step 2: Skip → Step 3 ───────────────────────────────────────────────────

it('يضع الجلسة على خطوة ٣ عند تخطّي ربط الحسابات', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['active_workspace_id' => $workspace->id]);

    $response = $this->actingAs($user)
        ->withSession(['onboarding_step' => 2])
        ->post('/onboarding/skip');

    $response->assertRedirect(route('onboarding'));
    $this->assertEquals(3, session('onboarding_step'));
});

// ── Step 3: Complete ────────────────────────────────────────────────────────

it('ينهي التهيئة ويوجه للداشبورد مع رسالة ترحيب', function () {
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['active_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->withSession(['onboarding_step' => 3])
        ->post('/onboarding/complete')
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('flash.success');

    $this->assertNull(session('onboarding_step'));
});

it('يتطلب مصادقة للوصول لمسارات التهيئة', function () {
    $this->get('/onboarding')->assertRedirect(route('login'));
    $this->post('/onboarding/workspace')->assertRedirect(route('login'));
    $this->post('/onboarding/skip')->assertRedirect(route('login'));
    $this->post('/onboarding/complete')->assertRedirect(route('login'));
});

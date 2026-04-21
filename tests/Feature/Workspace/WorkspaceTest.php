<?php

// WS-01 → WS-05

use App\Models\User;
use App\Models\Workspace;

// ── Create (WS-01) ──────────────────────────────────────────────────────────

it('يُنشئ مساحة عمل جديدة للمستخدم المصادق', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/workspaces', [
            'name'            => 'متجر أنيق',
            'business_type'   => 'تجارة إلكترونية',
            'countries'       => ['sa', 'ae'],
            'default_dialect' => 'sa',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('workspaces', [
        'user_id' => $user->id,
        'name'    => 'متجر أنيق',
    ]);
});

it('ينشئ هوية علامة تجارية تلقائياً مع المساحة', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/workspaces', ['name' => 'قهوة الرواق']);

    $workspace = Workspace::where('user_id', $user->id)->first();

    $this->assertDatabaseHas('brand_identities', ['workspace_id' => $workspace->id]);
});

it('يرفض إنشاء مساحة بدون اسم', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/workspaces', ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('يمنع المستخدم من تجاوز ١٠ مساحات عمل نشطة — WS-01', function () {
    $user = User::factory()->create();

    Workspace::factory()->count(10)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post('/workspaces', ['name' => 'المساحة الحادية عشرة'])
        ->assertSessionHasErrors('name');
});

it('يرفض المستخدم غير المصادق من إنشاء مساحة', function () {
    $this->post('/workspaces', ['name' => 'محاولة'])->assertRedirect('/login');
});

// ── Switch (WS-02) ──────────────────────────────────────────────────────────

it('يُبدّل المساحة النشطة للمستخدم — WS-02', function () {
    $user = User::factory()->create();
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/switch")
        ->assertRedirect();

    $this->assertEquals($ws->id, session('current_workspace_id'));
});

it('لا يُبدّل لمساحة تعود لمستخدم آخر', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $ws    = Workspace::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/switch")
        ->assertStatus(404);
});

// ── Update (WS-03) ──────────────────────────────────────────────────────────

it('يُحدّث بيانات مساحة العمل — WS-03', function () {
    $user = User::factory()->create();
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}", [
            'name'            => 'اسم جديد',
            'default_dialect' => 'ae',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('workspaces', [
        'id'              => $ws->id,
        'name'            => 'اسم جديد',
        'default_dialect' => 'ae',
    ]);
});

it('يمنع تحديث مساحة مستخدم آخر', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $ws    = Workspace::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}", ['name' => 'محاولة اختراق'])
        ->assertForbidden();
});

// ── Archive (WS-05) ─────────────────────────────────────────────────────────

it('يُؤرشف مساحة العمل — WS-05', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/archive")
        ->assertRedirect(route('workspace.index'));

    expect($ws->fresh()->archived_at)->not->toBeNull();
});

it('لا تظهر المساحات المؤرشفة في القائمة', function () {
    $user = User::factory()->create();
    Workspace::factory()->create(['user_id' => $user->id, 'archived_at' => now()]);

    $active = Workspace::factory()->create(['user_id' => $user->id]);

    expect($user->activeWorkspaces()->count())->toBe(1);
    expect($user->activeWorkspaces()->first()->id)->toBe($active->id);
});

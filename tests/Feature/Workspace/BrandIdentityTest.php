<?php

// BI-01 → BI-04

use App\Models\BrandIdentity;
use App\Models\User;
use App\Models\Workspace;

// ── Update brand identity (BI-01) ───────────────────────────────────────────

it('يُحدّث هوية العلامة التجارية — BI-01', function () {
    $user = User::factory()->create();
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);
    BrandIdentity::factory()->create(['workspace_id' => $ws->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/brand", [
            'description'    => 'متجر متخصص في الأزياء العصرية',
            'tone'           => 'عصرية',
            'target_audience'=> 'شباب خليجيون',
            'banned_words'   => ['رخيص', 'غير موثوق'],
            'example_posts'  => ['أحدث صيحات الموضة الخليجية 🌟'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('brand_identities', [
        'workspace_id' => $ws->id,
        'description'  => 'متجر متخصص في الأزياء العصرية',
        'tone'         => 'عصرية',
    ]);
});

it('يرفض أكثر من ١٠ كلمات محظورة — BI-03', function () {
    $user = User::factory()->create();
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/brand", [
            'banned_words' => array_fill(0, 11, 'كلمة'),
        ])
        ->assertSessionHasErrors('banned_words');
});

it('يرفض أكثر من ٥ منشورات نموذجية — BI-04', function () {
    $user = User::factory()->create();
    $ws   = Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/brand", [
            'example_posts' => array_fill(0, 6, 'منشور'),
        ])
        ->assertSessionHasErrors('example_posts');
});

// ── containsBannedWord (BI-07) ──────────────────────────────────────────────

it('يكتشف الكلمات المحظورة في المحتوى — BI-07', function () {
    $brand = new BrandIdentity();
    $brand->banned_words = ['رخيص', 'غير موثوق'];

    expect($brand->containsBannedWord('أسعار رخيصة جداً'))->toBeTrue();
    expect($brand->containsBannedWord('منتجاتنا ذات جودة عالية'))->toBeFalse();
});

it('يمنع تحديث هوية علامة مستخدم آخر', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $ws    = Workspace::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->post("/workspaces/{$ws->id}/brand", ['description' => 'محاولة'])
        ->assertForbidden();
});

<?php

// M8: System health + cache management

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('يعرض صفحة صحة النظام', function () {
    $this->actingAs($this->admin)
        ->get('/admin/system')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/System/Index')
            ->has('dbStatus')
            ->has('info')
        );
});

it('يمسح الكاش عبر الـ API', function () {
    $this->actingAs($this->admin)
        ->post('/admin/system/clear-cache', ['what' => 'app'])
        ->assertRedirect();
});

it('يرفض مسح كاش بنوع غير مدعوم', function () {
    $this->actingAs($this->admin)
        ->post('/admin/system/clear-cache', ['what' => 'invalid'])
        ->assertSessionHasErrors('what');
});

it('يشغّل optimize للنظام', function () {
    Artisan::shouldReceive('call')->andReturn(0);

    $this->actingAs($this->admin)
        ->post('/admin/system/optimize')
        ->assertRedirect();
});

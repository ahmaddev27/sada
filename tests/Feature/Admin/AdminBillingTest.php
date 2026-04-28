<?php

// M8: لوحة الفواتير والتصدير

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('يعرض صفحة الفواتير للمشرف', function () {
    $this->actingAs($this->admin)
        ->get('/admin/billing')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Billing/Index')
            ->has('invoices')
            ->has('stats')
        );
});

it('يصدّر ملف CSV للفواتير', function () {
    $this->actingAs($this->admin)
        ->get('/admin/billing/export')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('يعرض صفحة سجل الرصيد', function () {
    $this->actingAs($this->admin)
        ->get('/admin/tokens')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Tokens/Index')
            ->has('transactions')
            ->has('stats')
        );
});

it('يصدّر ملف CSV لسجل الرصيد', function () {
    $this->actingAs($this->admin)
        ->get('/admin/tokens/export')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('يمنع تصدير الفواتير لغير المشرف', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin/billing/export')->assertForbidden();
});

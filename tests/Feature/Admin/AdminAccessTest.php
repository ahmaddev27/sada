<?php

// M8: AdminMiddleware — لا يمكن الوصول لأي admin route بدون is_admin = true

use App\Models\User;

it('يمنع الوصول للوحة الإدارة بدون تسجيل دخول', function () {
    $this->get('/admin')->assertRedirect('/login');
});

it('يمنع الوصول للوحة الإدارة لمستخدم عادي', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('يسمح بالوصول للوحة الإدارة للمشرف', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
});

it('يمنع الوصول لقائمة المستخدمين الإدارية بدون صلاحية', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

it('يمنع الوصول لإعدادات النظام بدون صلاحية', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin/settings')->assertForbidden();
});

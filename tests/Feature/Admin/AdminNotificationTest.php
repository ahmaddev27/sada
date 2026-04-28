<?php

// M8: Broadcast notifications

use App\Models\User;
use App\Models\AdminLog;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    User::factory()->count(5)->create(['email_verified_at' => now()]);
    User::factory()->count(3)->create(['email_verified_at' => null]);
});

it('يعرض صفحة الإشعارات', function () {
    $this->actingAs($this->admin)
        ->get('/admin/notifications')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Notifications/Index')
            ->has('stats')
            ->has('recentBroadcasts')
        );
});

it('يرسل إشعاراً لكل المستخدمين ويسجل في admin_logs', function () {
    $this->actingAs($this->admin)
        ->post('/admin/notifications/broadcast', [
            'title'    => 'تحديث هام',
            'body'     => 'تم إطلاق ميزات جديدة في المنصة',
            'audience' => 'all',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('admin_logs', [
        'admin_id' => $this->admin->id,
        'action'   => 'broadcast_notification',
    ]);
});

it('يرسل إشعاراً للمستخدمين الموثّقين فقط', function () {
    $this->actingAs($this->admin)
        ->post('/admin/notifications/broadcast', [
            'title'    => 'إشعار موثّقين',
            'body'     => 'هذا الإشعار للمستخدمين الموثّقين فقط',
            'audience' => 'verified',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('admin_logs', ['action' => 'broadcast_notification']);
});

it('يرفض البث بدون عنوان', function () {
    $this->actingAs($this->admin)
        ->post('/admin/notifications/broadcast', [
            'title'    => '',
            'body'     => 'محتوى الإشعار',
            'audience' => 'all',
        ])
        ->assertSessionHasErrors('title');
});

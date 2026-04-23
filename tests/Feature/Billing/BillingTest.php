<?php

// BIL-01: billing page renders with packages and balance
// BIL-02: checkout creates a payment session via CreatePaymentAction
// BIL-03: webhook action is triggered and always returns 200
// BIL-07: invoice download authorised vs forbidden
// BIL-08: callback redirect based on status

use App\Actions\Billing\CreatePaymentAction;
use App\Actions\Billing\HandlePaymentWebhookAction;
use App\Models\Invoice;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\Workspace;
use Mockery\MockInterface;

function makeBillingUser(): User
{
    $user = User::factory()->create(['token_balance' => 100]);
    Workspace::factory()->create(['user_id' => $user->id]);
    return $user;
}

function makePackage(array $overrides = []): TokenPackage
{
    return TokenPackage::create(array_merge([
        'name'       => 'باقة اختبار',
        'name_en'    => 'Test Package',
        'tokens'     => 1000,
        'price'      => 49.00,
        'currency'   => 'SAR',
        'is_popular' => false,
        'is_active'  => true,
        'sort_order' => 1,
    ], $overrides));
}

// ── BIL-01: Index ────────────────────────────────────────────────────────────

it('تعرض صفحة الفوترة للمستخدم المُسجَّل', function () {
    $user = makeBillingUser();

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Billing/Index')
            ->has('packages')
            ->has('balance')
            ->has('transactions')
            ->has('invoices')
        );
});

it('يعرض رصيد التوكنز الصحيح للمستخدم', function () {
    $user = User::factory()->create(['token_balance' => 350]);
    Workspace::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('balance', 350));
});

it('يعرض الباقات النشطة فقط', function () {
    makePackage(['is_active' => true,  'name' => 'باقة نشطة']);
    makePackage(['is_active' => false, 'name' => 'باقة معطّلة']);

    $user = makeBillingUser();

    $this->actingAs($user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->has('packages', 1));
});

// ── BIL-01: Checkout ─────────────────────────────────────────────────────────

it('يبدأ جلسة الدفع ويُرجع استجابة ناجحة', function () {
    $package = makePackage();
    $user    = makeBillingUser();

    $this->mock(CreatePaymentAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->once()->andReturn([
            'payment_url' => 'https://api.moyasar.com/v1/payments/test-123',
            'payment_id'  => 'test-123',
        ]);
    });

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), [
            'package_id'   => $package->id,
            'callback_url' => 'https://example.com/billing/callback',
        ])
        ->assertOk()
        ->assertJsonFragment(['payment_id' => 'test-123']);
});

it('يرفض طلب الدفع بدون package_id', function () {
    $user = makeBillingUser();

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), [
            'callback_url' => 'https://example.com/billing/callback',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('package_id');
});

it('يرفض package_id غير موجود', function () {
    $user = makeBillingUser();

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), [
            'package_id'   => 99999,
            'callback_url' => 'https://example.com/billing/callback',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('package_id');
});

it('يُرجع 422 إذا رمى CreatePaymentAction استثناءً', function () {
    $package = makePackage();
    $user    = makeBillingUser();

    $this->mock(CreatePaymentAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->andThrow(new RuntimeException('خطأ في بوابة الدفع'));
    });

    $this->actingAs($user)
        ->postJson(route('billing.checkout'), [
            'package_id'   => $package->id,
            'callback_url' => 'https://example.com/billing/callback',
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'خطأ في بوابة الدفع']);
});

// ── BIL-08: Callback ─────────────────────────────────────────────────────────

it('يُعيد redirect بنجاح بعد الدفع الناجح', function () {
    $user = makeBillingUser();

    $this->actingAs($user)
        ->get(route('billing.callback', ['status' => 'paid']))
        ->assertRedirect(route('billing.index'));
});

it('يُعيد redirect مع رسالة خطأ عند فشل الدفع', function () {
    $user = makeBillingUser();

    $this->actingAs($user)
        ->get(route('billing.callback', ['status' => 'failed']))
        ->assertRedirect(route('billing.index'));
});

// ── BIL-03: Webhook ──────────────────────────────────────────────────────────

it('يستقبل webhook ويُرجع 204 دائماً', function () {
    $this->mock(HandlePaymentWebhookAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->once();
    });

    $this->postJson(route('billing.webhook'), [
        'id'     => 'pay_test_123',
        'status' => 'paid',
        'amount' => 4900,
        'currency' => 'SAR',
    ])->assertNoContent();
});

it('يُرجع 204 حتى لو فشل معالج الـ webhook', function () {
    $this->mock(HandlePaymentWebhookAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->andThrow(new RuntimeException('خطأ داخلي'));
    });

    $this->postJson(route('billing.webhook'), ['id' => 'pay_broken'])
        ->assertNoContent();
});

// ── BIL-07: Invoice download ─────────────────────────────────────────────────

it('يمنع المستخدم من تحميل فاتورة مستخدم آخر', function () {
    $owner   = makeBillingUser();
    $other   = makeBillingUser();

    $invoice = Invoice::create([
        'user_id'        => $owner->id,
        'invoice_number' => 'INV-2026-001',
        'amount'         => 49.00,
        'vat_rate'       => 15,
        'vat_amount'     => 7.35,
        'total_amount'   => 56.35,
        'currency'       => 'SAR',
        'status'         => 'paid',
        'paid_at'        => now(),
        'tokens_purchased' => 1000,
    ]);

    $this->actingAs($other)
        ->get(route('billing.invoice.download', $invoice))
        ->assertForbidden();
});

<?php

// BIL-01: handle Moyasar/Tap webhook — credit tokens on successful payment

namespace App\Actions\Billing;

use App\Models\Invoice;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandlePaymentWebhookAction
{
    public function __construct(private readonly TokenService $tokenService) {}

    /**
     * BIL-01 / BIL-02: process a payment webhook payload.
     * Idempotent — safe to call multiple times for the same payment.
     *
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): void
    {
        $invoiceId = $payload['metadata']['invoice_id'] ?? null;

        if (! $invoiceId) {
            Log::warning('Billing webhook received without invoice_id in metadata', $payload);

            return;
        }

        $invoice = Invoice::with('user')->find($invoiceId);

        if (! $invoice) {
            Log::warning('Billing webhook: invoice not found', ['invoice_id' => $invoiceId]);

            return;
        }

        // Idempotency guard — do not double-credit
        if ($invoice->isPaid()) {
            return;
        }

        if (($payload['status'] ?? '') !== 'paid') {
            Log::info('Billing webhook: payment not paid', [
                'invoice_id' => $invoiceId,
                'status'     => $payload['status'] ?? 'unknown',
            ]);

            return;
        }

        DB::transaction(function () use ($invoice, $payload): void {
            $invoice->update([
                'status'             => 'paid',
                'paid_at'            => now(),
                'gateway_payment_id' => $payload['id'] ?? null,
                'metadata'           => array_merge($invoice->metadata ?? [], ['gateway_payload' => $payload]),
            ]);

            $this->tokenService->credit(
                user: $invoice->user,
                amount: $invoice->tokens_purchased,
                description: 'شراء ' . $invoice->tokens_purchased . ' توكن — ' . $invoice->invoice_number,
                refType: 'payment',
                refId: $invoice->id,
                type: 'purchase',
            );
        });
    }
}

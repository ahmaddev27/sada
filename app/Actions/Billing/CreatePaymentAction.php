<?php

// BIL-01: initiate a Moyasar payment session and create a pending invoice

namespace App\Actions\Billing;

use App\Models\Invoice;
use App\Models\TokenPackage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreatePaymentAction
{
    private const VAT_RATES = [
        'SA' => 15.00,
        'AE' => 5.00,
    ];

    /**
     * BIL-01: initiate Moyasar payment and persist a pending invoice.
     *
     * @param  array{callback_url: string, country?: string}  $data
     * @return array{payment_url: string, invoice_id: int}
     */
    public function execute(User $user, TokenPackage $package, array $data): array
    {
        $country = strtoupper($data['country'] ?? '');
        $vatRate = self::VAT_RATES[$country] ?? 0.00;

        $amount    = (float) $package->price;
        $vatAmount = round($amount * $vatRate / 100, 2);
        $total     = round($amount + $vatAmount, 2);

        $invoice = Invoice::create([
            'user_id'          => $user->id,
            'invoice_number'   => Invoice::generateNumber(),
            'amount'           => $amount,
            'vat_rate'         => $vatRate,
            'vat_amount'       => $vatAmount,
            'total_amount'     => $total,
            'currency'         => $package->currency,
            'status'           => 'pending',
            'payment_gateway'  => 'moyasar',
            'tokens_purchased' => $package->tokens,
            'country'          => $country ?: null,
        ]);

        // Moyasar uses smallest unit (halalah = 1/100 SAR)
        $halalah = (int) round($total * 100);

        $response = Http::withBasicAuth(config('services.moyasar.publishable_key'), '')
            ->post('https://api.moyasar.com/v1/payments', [
                'amount'       => $halalah,
                'currency'     => $package->currency,
                'description'  => 'شراء ' . $package->tokens . ' توكن — صدى',
                'callback_url' => $data['callback_url'],
                'source'       => ['type' => 'creditcard'],
                'metadata'     => [
                    'invoice_id' => $invoice->id,
                    'user_id'    => $user->id,
                    'package_id' => $package->id,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Moyasar payment initiation failed', [
                'invoice_id' => $invoice->id,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            $invoice->update(['status' => 'cancelled']);

            throw new \RuntimeException('فشل الاتصال ببوابة الدفع. يرجى المحاولة لاحقاً.');
        }

        $paymentUrl = $response->json('source.transaction_url');

        return [
            'payment_url' => $paymentUrl,
            'invoice_id'  => $invoice->id,
        ];
    }
}

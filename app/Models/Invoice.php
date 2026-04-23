<?php

// BIL-06: VAT-compliant invoices | BIL-07: downloadable PDF

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_number',
        'amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'currency',
        'status',
        'payment_gateway',
        'gateway_payment_id',
        'tokens_purchased',
        'country',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'paid_at'  => 'datetime',
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * BIL-06: SA = 15% VAT, UAE = 5% VAT.
     */
    public function isVatable(): bool
    {
        return in_array($this->country, ['SA', 'AE'], true);
    }

    /**
     * BIL-06: generate next sequential invoice number.
     * Format: INV-{YEAR}-{padded 6 digits}
     */
    public static function generateNumber(): string
    {
        $year = now()->format('Y');

        $last = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($last) {
            $sequence = (int) substr($last, -6) + 1;
        } else {
            $sequence = 1;
        }

        return 'INV-' . $year . '-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}

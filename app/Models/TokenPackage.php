<?php

// BIL-01: token packages available for purchase

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TokenPackage extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'tokens',
        'price',
        'currency',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_popular' => 'boolean',
            'is_active'  => 'boolean',
            'price'      => 'decimal:2',
        ];
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /** @param Builder<TokenPackage> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * BIL-01: human-readable price in Arabic format.
     * Example: "٢٠٠ ر.س"
     */
    public function formattedPrice(): string
    {
        $arabic = str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            number_format((float) $this->price, 0)
        );

        return $arabic . ' ر.س';
    }
}

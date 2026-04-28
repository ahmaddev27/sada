<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonalTemplate extends Model
{
    protected $fillable = [
        'seasonal_occasion_id', 'platform', 'content_template',
        'hashtags', 'tone', 'active', 'sort_order',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'active'   => 'boolean',
    ];

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(SeasonalOccasion::class, 'seasonal_occasion_id');
    }
}

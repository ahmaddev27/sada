<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeasonalOccasion extends Model
{
    protected $fillable = [
        'key', 'name', 'subtitle', 'date', 'end_date',
        'icon', 'color', 'countries', 'featured', 'hashtags',
        'is_recurring', 'type', 'active', 'sort_order',
    ];

    protected $casts = [
        'date'         => 'date',
        'end_date'     => 'date',
        'countries'    => 'array',
        'hashtags'     => 'array',
        'featured'     => 'boolean',
        'is_recurring' => 'boolean',
        'active'       => 'boolean',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(SeasonalTemplate::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// WS-01 → WS-05
#[Fillable(['user_id', 'name', 'business_type', 'countries', 'default_dialect', 'logo_path'])]
class Workspace extends Model
{
    use HasFactory;
    protected $casts = [
        'countries'   => 'array',
        'archived_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brandIdentity(): HasOne
    {
        return $this->hasOne(BrandIdentity::class);
    }

    // WS-05: archived workspaces are excluded from normal queries
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    public function archive(): void
    {
        $this->forceFill(['archived_at' => now()])->save();
    }

    public function restore(): void
    {
        $this->forceFill(['archived_at' => null])->save();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}

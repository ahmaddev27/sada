<?php

// ANL-01→ANL-07

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSnapshot extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'post_id',
        'campaign_id',
        'platform',
        'snapshot_date',
        'reach',
        'impressions',
        'likes',
        'comments',
        'shares',
        'saves',
        'clicks',
        'spend',
        'follower_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'spend'         => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    // ── Computed ───────────────────────────────────────────────────────────────

    /**
     * Engagement rate = (likes + comments + shares + saves) / max(impressions, 1) * 100
     */
    public function engagementRate(): float
    {
        $engagement = $this->likes + $this->comments + $this->shares + $this->saves;

        return round($engagement / max((int) $this->impressions, 1) * 100, 2);
    }
}

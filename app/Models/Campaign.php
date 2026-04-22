<?php

// ADS-01→ADS-11

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'social_account_id',
        'post_id',
        'ad_copy',
        'name',
        'objective',
        'platform',
        'status',
        'target_countries',
        'target_age_min',
        'target_age_max',
        'target_gender',
        'target_interests',
        'budget_type',
        'budget_amount',
        'budget_currency',
        'starts_at',
        'ends_at',
        'meta_campaign_id',
        'meta_adset_id',
        'meta_ad_id',
        'insights',
        'insights_synced_at',
    ];

    protected $casts = [
        'target_countries'   => 'array',
        'target_interests'   => 'array',
        'insights'           => 'array',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
        'insights_synced_at' => 'datetime',
        'budget_amount'      => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    /** @return BelongsTo<SocialAccount, $this> */
    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // ── State helpers ──────────────────────────────────────────────────────────

    public function isActive(): bool  { return $this->status === 'active'; }
    public function isDraft(): bool   { return $this->status === 'draft'; }
    public function isPaused(): bool  { return $this->status === 'paused'; }

    // ── Arabic labels ──────────────────────────────────────────────────────────

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'مسودة',
            'pending'   => 'قيد المراجعة',
            'active'    => 'نشطة',
            'paused'    => 'موقوفة',
            'completed' => 'مكتملة',
            'rejected'  => 'مرفوضة',
            default     => 'غير معروف',
        };
    }

    public function objectiveLabel(): string
    {
        return match ($this->objective) {
            'awareness'    => 'الوعي بالعلامة التجارية',
            'traffic'      => 'الزيارات',
            'engagement'   => 'التفاعل',
            'conversions'  => 'التحويلات',
            'app_installs' => 'تثبيت التطبيق',
            'video_views'  => 'مشاهدات الفيديو',
            default        => 'غير محدد',
        };
    }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'instagram' => 'انستجرام',
            'facebook'  => 'فيسبوك',
            default     => 'غير محدد',
        };
    }
}

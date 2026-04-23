<?php

// CG-08, SCH-01

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'content',
        'hashtags',
        'platform',
        'content_type',
        'dialect',
        'status',
        'scheduled_for',
        'published_at',
        'social_account_id',
        'metadata',
    ];

    protected $casts = [
        'hashtags'     => 'array',
        'metadata'     => 'array',
        'scheduled_for'=> 'datetime',
        'published_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<SocialAccount, $this> */
    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isScheduled(): bool { return $this->status === 'scheduled'; }
    public function isPublished(): bool { return $this->status === 'published'; }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'instagram' => 'انستجرام',
            'facebook'  => 'فيسبوك',
            'tiktok'    => 'تيك توك',
            'snapchat'  => 'سناب شات',
            default     => 'X (تويتر)',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'مسودة',
            'scheduled' => 'مجدول',
            'published' => 'منشور',
            default     => 'فشل',
        };
    }
}

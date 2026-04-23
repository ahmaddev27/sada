<?php

// CON-01→CON-11

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Carbon\Carbon;
use Database\Factories\SocialAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    /** @use HasFactory<SocialAccountFactory> */
    use HasFactory;
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'provider',
        'provider_account_id',
        'account_name',
        'account_picture_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'scopes',
        'metadata',
    ];

    protected $casts = [
        // CON-06: AES-256 encryption at rest
        'access_token'     => 'encrypted',
        'refresh_token'    => 'encrypted',
        'token_expires_at' => 'datetime',
        'scopes'           => 'array',
        'metadata'         => 'array',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    // CON-09: token health helpers

    public function isTokenExpired(): bool
    {
        if ($this->token_expires_at === null) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function markHealthy(): void
    {
        $this->update(['status' => 'healthy']);
    }

    public function markRevoked(): void
    {
        $this->update(['status' => 'revoked']);
    }

    // CON-07: refresh is needed when token expires within the buffer window
    public function needsRefresh(int $bufferMinutes = 60): bool
    {
        if ($this->token_expires_at === null) {
            return false;
        }

        return $this->token_expires_at->lessThan(Carbon::now()->addMinutes($bufferMinutes));
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
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
            'healthy' => 'متصل',
            'expired' => 'منتهي الصلاحية',
            'revoked' => 'تم الإلغاء',
            default   => 'خطأ',
        };
    }
}

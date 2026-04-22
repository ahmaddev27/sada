<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Database\Factories\BrandIdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// BI-01 → BI-04
#[Fillable(['workspace_id', 'description', 'tone', 'target_audience', 'banned_words', 'example_posts'])]
class BrandIdentity extends Model
{
    /** @use HasFactory<BrandIdentityFactory> */
    use HasFactory, BelongsToWorkspace;

    protected $casts = [
        'banned_words'  => 'array',
        'example_posts' => 'array',
    ];

    // BI-03: max 10 banned words
    public function addBannedWord(string $word): void
    {
        $words = $this->banned_words ?? [];
        if (count($words) >= 10) {
            return;
        }
        $this->update(['banned_words' => array_unique([...$words, $word])]);
    }

    // BI-07: check if content contains any banned word
    public function containsBannedWord(string $content): bool
    {
        foreach ($this->banned_words ?? [] as $word) {
            if (mb_stripos($content, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}

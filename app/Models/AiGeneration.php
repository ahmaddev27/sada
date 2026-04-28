<?php

// CG-10

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    /** @use HasFactory<\Database\Factories\AiGenerationFactory> */
    use HasFactory;
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'agent_type',
        'provider',
        'ai_model',
        'dialect',
        'platform',
        'content_type',
        'prompt',
        'input_tokens',
        'output_tokens',
        'sada_tokens_charged',
        'cost_usd',
        'cached',
    ];

    protected $casts = [
        'cached'   => 'boolean',
        'cost_usd' => 'decimal:8',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

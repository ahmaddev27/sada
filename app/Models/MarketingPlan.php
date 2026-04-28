<?php

// MKT-01

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingPlan extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'user_id', 'title', 'inputs', 'plan',
        'status', 'ai_provider', 'ai_model', 'cost_usd',
        'input_tokens', 'output_tokens',
    ];

    protected $casts = [
        'inputs'   => 'array',
        'plan'     => 'array',
        'cost_usd' => 'decimal:8',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}

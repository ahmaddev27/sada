<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiGenerationsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AiGeneration::withoutWorkspaceScope()
            ->with(['workspace:id,name', 'user:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('platform')) {
            $query->where('platform', $request->string('platform')->toString());
        }

        if ($request->filled('agent_type')) {
            $query->where('agent_type', $request->string('agent_type')->toString());
        }

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('workspace', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $generations = $query->paginate(30)->withQueryString();

        $usdToSar = (float) config('ai_pricing.usd_to_sar', 3.75);

        $stats = [
            'total'           => AiGeneration::withoutWorkspaceScope()->count(),
            'today'           => AiGeneration::withoutWorkspaceScope()->whereDate('created_at', today())->count(),
            'tokens_charged'  => (int) AiGeneration::withoutWorkspaceScope()->sum('sada_tokens_charged'),
            'input_tokens'    => (int) AiGeneration::withoutWorkspaceScope()->sum('input_tokens'),
            'output_tokens'   => (int) AiGeneration::withoutWorkspaceScope()->sum('output_tokens'),
            'total_cost_usd'  => round((float) AiGeneration::withoutWorkspaceScope()->sum('cost_usd'), 4),
            'total_cost_sar'  => round((float) AiGeneration::withoutWorkspaceScope()->sum('cost_usd') * $usdToSar, 4),
            'cached_count'    => AiGeneration::withoutWorkspaceScope()->where('cached', true)->count(),
        ];

        return Inertia::render('Admin/AiGenerations/Index', [
            'generations' => $generations,
            'stats'       => $stats,
            'filters'     => $request->only('search', 'platform', 'agent_type'),
        ]);
    }
}

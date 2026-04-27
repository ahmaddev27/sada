<?php

// ANL + CG-10: AI cost reporting for admins

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiCostController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AiGeneration::withoutWorkspaceScope()
            ->with(['workspace:id,name', 'user:id,name'])
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->whereHas('workspace', fn ($w) => $w->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('agent_type')) {
            $query->where('agent_type', $request->string('agent_type')->toString());
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->string('platform')->toString());
        }

        $generations = $query->paginate(30)->withQueryString();

        // Breakdown by agent type
        $byAgentType = AiGeneration::withoutWorkspaceScope()
            ->select('agent_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(sada_tokens_charged) as tokens'), DB::raw('SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cached_count'))
            ->groupBy('agent_type')
            ->orderByDesc('count')
            ->get();

        // Daily usage — last 14 days
        $dailyChart = AiGeneration::withoutWorkspaceScope()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(sada_tokens_charged) as tokens'),
                DB::raw('SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cached'),
            )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $stats = [
            'total_generations'   => AiGeneration::withoutWorkspaceScope()->count(),
            'today_generations'   => AiGeneration::withoutWorkspaceScope()->whereDate('created_at', today())->count(),
            'total_tokens_billed' => (int) AiGeneration::withoutWorkspaceScope()->sum('sada_tokens_charged'),
            'total_input_tokens'  => (int) AiGeneration::withoutWorkspaceScope()->sum('input_tokens'),
            'total_output_tokens' => (int) AiGeneration::withoutWorkspaceScope()->sum('output_tokens'),
            'cached_count'        => (int) AiGeneration::withoutWorkspaceScope()->where('cached', true)->count(),
            'cached_savings'      => (int) AiGeneration::withoutWorkspaceScope()->where('cached', true)->sum('sada_tokens_charged'),
        ];

        return Inertia::render('Admin/AiCosts/Index', [
            'generations' => $generations,
            'filters'     => $request->only('search', 'agent_type', 'platform'),
            'stats'       => $stats,
            'byAgentType' => $byAgentType,
            'dailyChart'  => $dailyChart,
        ]);
    }
}

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
        $query = AiGeneration::with(['workspace:id,name', 'user:id,name'])
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

        $stats = [
            'total'           => AiGeneration::count(),
            'today'           => AiGeneration::whereDate('created_at', today())->count(),
            'tokens_charged'  => (int) AiGeneration::sum('sada_tokens_charged'),
            'input_tokens'    => (int) AiGeneration::sum('input_tokens'),
            'output_tokens'   => (int) AiGeneration::sum('output_tokens'),
            'cached_count'    => AiGeneration::where('cached', true)->count(),
        ];

        return Inertia::render('Admin/AiGenerations/Index', [
            'generations' => $generations,
            'stats'       => $stats,
            'filters'     => $request->only('search', 'platform', 'agent_type'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiModelsController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', '30');

        $from = match ($period) {
            '1'   => now()->subDay(),
            '7'   => now()->subDays(7),
            '30'  => now()->subDays(30),
            '90'  => now()->subDays(90),
            default => null,
        };

        $query = AiGeneration::query()
            ->select([
                'provider',
                'ai_model',
                DB::raw('COUNT(*)                            AS requests'),
                DB::raw('SUM(input_tokens)                  AS total_input_tokens'),
                DB::raw('SUM(output_tokens)                 AS total_output_tokens'),
                DB::raw('SUM(input_tokens + output_tokens)  AS total_tokens'),
                DB::raw('SUM(cost_usd)                      AS total_cost_usd'),
            ])
            ->groupBy('provider', 'ai_model')
            ->orderByDesc('requests');

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        $rows = $query->get();

        $usdToSar = (float) config('ai_pricing.usd_to_sar', 3.75);

        $models = $rows->map(fn ($r) => [
            'provider'           => $r->provider ?? 'unknown',
            'ai_model'           => $r->ai_model  ?? 'unknown',
            'requests'           => (int) $r->requests,
            'total_input_tokens' => (int) $r->total_input_tokens,
            'total_output_tokens'=> (int) $r->total_output_tokens,
            'total_tokens'       => (int) $r->total_tokens,
            'total_cost_usd'     => round((float) $r->total_cost_usd, 4),
            'total_cost_sar'     => round((float) $r->total_cost_usd * $usdToSar, 4),
        ]);

        // Summary totals
        $totals = [
            'requests'       => $models->sum('requests'),
            'total_tokens'   => $models->sum('total_tokens'),
            'total_cost_usd' => round($models->sum('total_cost_usd'), 4),
            'total_cost_sar' => round($models->sum('total_cost_sar'), 4),
        ];

        // Daily trend for chart (last N days grouped by date + provider)
        $trendQuery = AiGeneration::query()
            ->select([
                DB::raw('DATE(created_at) AS date'),
                'provider',
                DB::raw('COUNT(*) AS requests'),
                DB::raw('SUM(cost_usd) AS cost_usd'),
            ])
            ->whereNotNull('provider')
            ->groupBy(DB::raw('DATE(created_at)'), 'provider')
            ->orderBy('date');

        if ($from) {
            $trendQuery->where('created_at', '>=', $from);
        }

        $trend = $trendQuery->get()->map(fn ($r) => [
            'date'     => $r->date,
            'provider' => $r->provider,
            'requests' => (int) $r->requests,
            'cost_usd' => round((float) $r->cost_usd, 4),
        ]);

        return Inertia::render('Admin/AiModels/Index', [
            'models'  => $models,
            'totals'  => $totals,
            'trend'   => $trend,
            'period'  => $period,
            'usdToSar'=> $usdToSar,
        ]);
    }
}

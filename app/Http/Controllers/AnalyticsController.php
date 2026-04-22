<?php

// ANL-01→ANL-07

namespace App\Http\Controllers;

use App\Models\AnalyticsSnapshot;
use App\Models\Workspace;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsController extends Controller
{
    // ANL-01, ANL-02, ANL-03, ANL-04
    public function index(Request $request): Response
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        $filters = $this->resolveFilters($request);

        $kpis              = $this->buildKpis($workspace, $filters);
        $engagementOverTime = $this->buildEngagementOverTime($workspace, $filters);
        $topPosts          = $this->buildTopPosts($workspace, $filters);
        $platformBreakdown = $this->buildPlatformBreakdown($workspace, $filters);

        // ANL-03: AI insights in Arabic — TODO: replace with real laravel/ai call
        $aiInsights = [
            'أفضل أداء للمنشورات في أيام الثلاثاء والأربعاء بين الساعة 7 و9 مساءً بتوقيت الرياض.',
            'انستجرام يحقق أعلى معدل تفاعل بنسبة تفوق باقي المنصات بمقدار الضعف — يُنصح بتركيز الميزانية عليه.',
            'المنشورات التي تحتوي على صور المنتج الفعلي تحصل على وصول أعلى بنسبة 34% مقارنةً بالمنشورات النصية.',
        ];

        $hasData = AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->exists();

        return Inertia::render('Analytics/Index', [
            'kpis'               => $kpis,
            'engagementOverTime' => $engagementOverTime,
            'topPosts'           => $topPosts,
            'platformBreakdown'  => $platformBreakdown,
            'aiInsights'         => $aiInsights,
            'filters'            => $filters,
            'hasData'            => $hasData,
        ]);
    }

    // ANL-05: PDF export (bilingual via $lang param)
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        $lang    = in_array($request->query('lang'), ['ar', 'en', 'both']) ? $request->query('lang') : 'ar';
        $filters = $this->resolveFilters($request);
        $kpis    = $this->buildKpis($workspace, $filters);
        $topPosts = $this->buildTopPosts($workspace, $filters);

        $pdf = Pdf::loadView('exports.analytics', compact('kpis', 'workspace', 'lang', 'filters', 'topPosts'))
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'analytics-%s-%s.pdf',
            str($workspace->name)->slug(),
            now()->format('Y-m-d')
        );

        return $pdf->download($filename);
    }

    // ANL-06: CSV export (streamed, no temp file)
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        $filters = $this->resolveFilters($request);

        $filename = sprintf(
            'analytics-%s-%s.csv',
            str($workspace->name)->slug(),
            now()->format('Y-m-d')
        );

        $snapshots = AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->orderBy('snapshot_date')
            ->get(['snapshot_date', 'platform', 'reach', 'impressions', 'likes', 'comments', 'shares', 'clicks', 'spend']);

        return response()->streamDownload(function () use ($snapshots) {
            $handle = fopen('php://output', 'w');

            // ANL-07: bilingual headers (Arabic + English)
            fputcsv($handle, [
                'التاريخ / Date',
                'المنصة / Platform',
                'الوصول / Reach',
                'الانطباعات / Impressions',
                'الإعجابات / Likes',
                'التعليقات / Comments',
                'المشاركات / Shares',
                'النقرات / Clicks',
                'الإنفاق / Spend',
            ]);

            foreach ($snapshots as $row) {
                fputcsv($handle, [
                    $row->snapshot_date->format('Y-m-d'),
                    $row->platform,
                    $row->reach,
                    $row->impressions,
                    $row->likes,
                    $row->comments,
                    $row->shares,
                    $row->clicks,
                    $row->spend,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Resolve and validate filters from request query string (ANL-04).
     *
     * @return array{date_from: Carbon, date_to: Carbon, platform: string|null, post_type: string|null}
     */
    private function resolveFilters(Request $request): array
    {
        $dateFrom = $request->query('date_from')
            ? Carbon::parse($request->query('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dateTo = $request->query('date_to')
            ? Carbon::parse($request->query('date_to'))->endOfDay()
            : now()->endOfDay();

        // Guard: date_from must not be after date_to
        if ($dateFrom->gt($dateTo)) {
            $dateFrom = $dateTo->copy()->subDays(30)->startOfDay();
        }

        $allowedPlatforms = ['instagram', 'facebook', 'tiktok', 'snapchat', 'x'];
        $platform = in_array($request->query('platform'), $allowedPlatforms)
            ? $request->query('platform')
            : null;

        $postType = $request->query('post_type') ?: null;

        return compact('date_from', 'date_to', 'platform', 'post_type');
    }

    /**
     * Build KPI aggregations for the given workspace and filters (ANL-01).
     *
     * @param array<string, mixed> $filters
     * @return array<string, float|int>
     */
    private function buildKpis(Workspace $workspace, array $filters): array
    {
        $base = AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->when($filters['post_type'], fn ($q, $pt) => $q->whereHas(
                'post',
                fn ($pq) => $pq->where('content_type', $pt)
            ));

        $aggregates = $base->selectRaw(
            'SUM(reach) as total_reach,
             SUM(impressions) as total_impressions,
             SUM(likes + comments + shares + saves) as total_engagement,
             SUM(clicks) as total_clicks,
             SUM(spend) as total_spend'
        )->first();

        $totalReach       = (int) ($aggregates->total_reach ?? 0);
        $totalImpressions = (int) ($aggregates->total_impressions ?? 0);
        $totalEngagement  = (int) ($aggregates->total_engagement ?? 0);
        $totalClicks      = (int) ($aggregates->total_clicks ?? 0);
        $totalSpend       = (float) ($aggregates->total_spend ?? 0.0);

        // Follower growth: latest snapshot follower_count - earliest in range
        $followerGrowth = $this->computeFollowerGrowth($workspace, $filters);

        return [
            'total_reach'       => $totalReach,
            'total_impressions'  => $totalImpressions,
            'total_engagement'  => $totalEngagement,
            'engagement_rate'   => round($totalEngagement / max($totalImpressions, 1) * 100, 2),
            'total_clicks'      => $totalClicks,
            'ctr'               => round($totalClicks / max($totalImpressions, 1) * 100, 2),
            'total_spend'       => $totalSpend,
            'roas'              => 0, // TODO: compute when revenue tracking is added
            'follower_growth'   => $followerGrowth,
        ];
    }

    /**
     * Follower growth = latest follower_count - earliest follower_count in range.
     *
     * @param array<string, mixed> $filters
     */
    private function computeFollowerGrowth(Workspace $workspace, array $filters): int
    {
        $query = AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->whereNotNull('follower_count');

        $earliest = $query->clone()->orderBy('snapshot_date')->value('follower_count');
        $latest   = $query->clone()->orderByDesc('snapshot_date')->value('follower_count');

        if ($earliest === null || $latest === null) {
            return 0;
        }

        return $latest - $earliest;
    }

    /**
     * Engagement over time grouped by day for charts (ANL-02).
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{date: string, engagement: int}>
     */
    private function buildEngagementOverTime(Workspace $workspace, array $filters): array
    {
        return AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->selectRaw('snapshot_date, SUM(likes + comments + shares + saves) as engagement')
            ->groupBy('snapshot_date')
            ->orderBy('snapshot_date')
            ->get()
            ->map(fn ($row) => [
                'date'       => $row->snapshot_date->format('Y-m-d'),
                'engagement' => (int) $row->engagement,
            ])
            ->values()
            ->all();
    }

    /**
     * Top 5 posts by total reach in date range (ANL-02).
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    private function buildTopPosts(Workspace $workspace, array $filters): array
    {
        return AnalyticsSnapshot::withoutWorkspaceScope()
            ->with('post:id,content,platform')
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->whereNotNull('post_id')
            ->selectRaw('post_id, platform, SUM(reach) as total_reach, SUM(likes + comments + shares + saves) as total_engagement')
            ->groupBy('post_id', 'platform')
            ->orderByDesc('total_reach')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'post_id'          => $row->post_id,
                'platform'         => $row->platform,
                'total_reach'      => (int) $row->total_reach,
                'total_engagement' => (int) $row->total_engagement,
                'content_preview'  => $row->post
                    ? mb_substr($row->post->content ?? '', 0, 80)
                    : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Platform breakdown: reach, impressions, engagement per platform (ANL-02).
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    private function buildPlatformBreakdown(Workspace $workspace, array $filters): array
    {
        return AnalyticsSnapshot::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('snapshot_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['platform'], fn ($q, $p) => $q->where('platform', $p))
            ->selectRaw(
                'platform,
                 SUM(reach) as total_reach,
                 SUM(impressions) as total_impressions,
                 SUM(likes + comments + shares + saves) as total_engagement'
            )
            ->groupBy('platform')
            ->orderByDesc('total_reach')
            ->get()
            ->map(fn ($row) => [
                'platform'         => $row->platform,
                'total_reach'      => (int) $row->total_reach,
                'total_impressions' => (int) $row->total_impressions,
                'total_engagement' => (int) $row->total_engagement,
            ])
            ->values()
            ->all();
    }
}

<!DOCTYPE html>
<html lang="{{ in_array($lang, ['ar', 'both']) ? 'ar' : 'en' }}"
      dir="{{ in_array($lang, ['ar', 'both']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ in_array($lang, ['ar', 'both']) ? 'تقرير التحليلات' : 'Analytics Report' }}</title>
    <style>
        /* ── Base ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0E1512;
            background: #ffffff;
            direction: {{ in_array($lang, ['ar', 'both']) ? 'rtl' : 'ltr' }};
        }

        /* ── Header ── */
        .header {
            background: #0F6F5C;
            color: #ffffff;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .header .brand {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header .sub {
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.85;
        }
        .header .workspace {
            font-size: 13px;
            margin-top: 8px;
            background: rgba(255,255,255,0.15);
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0F6F5C;
            border-bottom: 2px solid #0F6F5C;
            padding-bottom: 4px;
            margin-bottom: 12px;
            margin-top: 20px;
        }

        /* ── KPI grid ── */
        .kpi-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .kpi-grid td {
            width: 25%;
            vertical-align: top;
            padding: 4px;
        }
        .kpi-card {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 12px;
            background: #F7F8F7;
        }
        .kpi-card .label {
            font-size: 10px;
            color: #5E6A64;
            margin-bottom: 4px;
        }
        .kpi-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #0F6F5C;
        }
        .kpi-card .sub-value {
            font-size: 10px;
            color: #5E6A64;
            margin-top: 2px;
        }

        /* ── Tables ── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.data-table th {
            background: #0F6F5C;
            color: #ffffff;
            padding: 7px 10px;
            text-align: {{ in_array($lang, ['ar', 'both']) ? 'right' : 'left' }};
        }
        table.data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.data-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 32px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        /* ── Date range badge ── */
        .date-range {
            font-size: 10px;
            color: #5E6A64;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<div class="header">
    <div class="brand">صدى · Sada</div>
    <div class="sub">
        @if(in_array($lang, ['ar', 'both']))تقرير التحليلات —
        @endif
        @if(in_array($lang, ['en', 'both']))Analytics Report
        @endif
    </div>
    <div class="workspace">{{ $workspace->name }}</div>
</div>

<div class="date-range">
    @if(in_array($lang, ['ar', 'both']))الفترة: @endif
    @if(in_array($lang, ['en', 'both']))Period: @endif
    {{ $filters['date_from']->format('Y-m-d') }} → {{ $filters['date_to']->format('Y-m-d') }}
    &nbsp;&nbsp;
    @if(in_array($lang, ['ar', 'both']))تاريخ التقرير: @endif
    @if(in_array($lang, ['en', 'both']))Generated: @endif
    {{ now()->format('Y-m-d H:i') }}
</div>

{{-- ── KPI Summary (ANL-01) ───────────────────────────────────────────────── --}}
<div class="section-title">
    @if(in_array($lang, ['ar', 'both']))مؤشرات الأداء الرئيسية @endif
    @if(in_array($lang, ['en', 'both']))Key Performance Indicators @endif
</div>

<table class="kpi-grid">
    <tr>
        {{-- Reach --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))الوصول @endif
                    @if(in_array($lang, ['en', 'both']))Reach @endif
                </div>
                <div class="value">{{ number_format($kpis['total_reach']) }}</div>
            </div>
        </td>
        {{-- Impressions --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))الانطباعات @endif
                    @if(in_array($lang, ['en', 'both']))Impressions @endif
                </div>
                <div class="value">{{ number_format($kpis['total_impressions']) }}</div>
            </div>
        </td>
        {{-- Engagement Rate --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))معدل التفاعل @endif
                    @if(in_array($lang, ['en', 'both']))Engagement Rate @endif
                </div>
                <div class="value">{{ $kpis['engagement_rate'] }}%</div>
                <div class="sub-value">
                    @if(in_array($lang, ['ar', 'both']))إجمالي: @endif
                    @if(in_array($lang, ['en', 'both']))Total: @endif
                    {{ number_format($kpis['total_engagement']) }}
                </div>
            </div>
        </td>
        {{-- CTR --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))نسبة النقر @endif
                    @if(in_array($lang, ['en', 'both']))CTR @endif
                </div>
                <div class="value">{{ $kpis['ctr'] }}%</div>
                <div class="sub-value">
                    @if(in_array($lang, ['ar', 'both']))نقرات: @endif
                    @if(in_array($lang, ['en', 'both']))Clicks: @endif
                    {{ number_format($kpis['total_clicks']) }}
                </div>
            </div>
        </td>
    </tr>
    <tr>
        {{-- Follower Growth --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))نمو المتابعين @endif
                    @if(in_array($lang, ['en', 'both']))Follower Growth @endif
                </div>
                <div class="value">{{ ($kpis['follower_growth'] >= 0 ? '+' : '') . number_format($kpis['follower_growth']) }}</div>
            </div>
        </td>
        {{-- Spend --}}
        <td>
            <div class="kpi-card">
                <div class="label">
                    @if(in_array($lang, ['ar', 'both']))الإنفاق الإعلاني @endif
                    @if(in_array($lang, ['en', 'both']))Ad Spend @endif
                </div>
                <div class="value">{{ number_format($kpis['total_spend'], 2) }}</div>
                <div class="sub-value">SAR</div>
            </div>
        </td>
        {{-- ROAS placeholder --}}
        <td>
            <div class="kpi-card">
                <div class="label">ROAS</div>
                <div class="value">—</div>
                <div class="sub-value">
                    @if(in_array($lang, ['ar', 'both']))قريباً @endif
                    @if(in_array($lang, ['en', 'both']))Coming soon @endif
                </div>
            </div>
        </td>
        <td></td>
    </tr>
</table>

{{-- ── Top Posts (ANL-02) ─────────────────────────────────────────────────── --}}
@if(count($topPosts) > 0)
<div class="section-title">
    @if(in_array($lang, ['ar', 'both']))أفضل المنشورات @endif
    @if(in_array($lang, ['en', 'both']))Top Posts @endif
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>
                @if(in_array($lang, ['ar', 'both']))المحتوى @endif
                @if(in_array($lang, ['en', 'both']))Content @endif
            </th>
            <th>
                @if(in_array($lang, ['ar', 'both']))المنصة @endif
                @if(in_array($lang, ['en', 'both']))Platform @endif
            </th>
            <th>
                @if(in_array($lang, ['ar', 'both']))الوصول @endif
                @if(in_array($lang, ['en', 'both']))Reach @endif
            </th>
            <th>
                @if(in_array($lang, ['ar', 'both']))التفاعل @endif
                @if(in_array($lang, ['en', 'both']))Engagement @endif
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($topPosts as $post)
        <tr>
            <td>{{ $post['content_preview'] ?? '—' }}</td>
            <td>{{ $post['platform'] }}</td>
            <td>{{ number_format($post['total_reach']) }}</td>
            <td>{{ number_format($post['total_engagement']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── Footer ──────────────────────────────────────────────────────────────── --}}
<div class="footer">
    صدى · Sada &mdash;
    @if(in_array($lang, ['ar', 'both']))تقرير تلقائي &mdash; جميع الحقوق محفوظة
    @endif
    @if(in_array($lang, ['en', 'both']))Automated report &mdash; All rights reserved
    @endif
    &mdash; {{ now()->year }}
</div>

</body>
</html>

<script setup lang="ts">
// ANL-01 → ANL-04
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
)

// ── Props (ANL-01 → ANL-04) ────────────────────────────────────────────────
const props = defineProps<{
    kpis: {
        total_reach: number
        total_impressions: number
        total_engagement: number
        engagement_rate: number
        total_clicks: number
        ctr: number
        total_spend: number
        roas: number
        follower_growth: number
    }
    engagementOverTime: Array<{ date: string; engagement: number }>
    topPosts: Array<{ id: number; content: string; platform: string; reach: number; impressions: number; engagement: number }>
    platformBreakdown: Array<{ platform: string; reach: number; impressions: number; engagement: number }>
    aiInsights: string[]
    filters: { date_from: string; date_to: string; platform: string | null }
    hasData: boolean
}>()

// ── Filters state (ANL-04) ─────────────────────────────────────────────────
const dateFrom   = ref<string>(props.filters.date_from ?? '')
const dateTo     = ref<string>(props.filters.date_to ?? '')
const activePlatform = ref<string>(props.filters.platform ?? 'all')

const PLATFORM_CHIPS = [
    { key: 'all',       label: 'الكل' },
    { key: 'instagram', label: 'انستجرام' },
    { key: 'facebook',  label: 'فيسبوك' },
]

function applyFilters() {
    router.get(
        '/analytics',
        {
            date_from: dateFrom.value || undefined,
            date_to:   dateTo.value   || undefined,
            platform:  activePlatform.value === 'all' ? undefined : activePlatform.value,
        },
        { preserveState: true, replace: true },
    )
}

// ── KPI cards config (ANL-01) ──────────────────────────────────────────────
interface KpiCard {
    key: string
    label: string
    value: string
    icon: string
    highlight?: boolean
    highlightColor?: 'green' | 'accent'
}

const kpiCards = computed<KpiCard[]>(() => [
    {
        key:   'total_reach',
        label: 'الوصول الإجمالي',
        value: formatNumber(props.kpis.total_reach),
        icon:  'eye',
    },
    {
        key:   'total_impressions',
        label: 'الانطباعات',
        value: formatNumber(props.kpis.total_impressions),
        icon:  'chart',
    },
    {
        key:            'engagement_rate',
        label:          'معدل التفاعل',
        value:          props.kpis.engagement_rate.toFixed(2) + '%',
        icon:           'sparkle',
        highlight:      props.kpis.engagement_rate > 3,
        highlightColor: 'accent',
    },
    {
        key:   'total_clicks',
        label: 'النقرات',
        value: formatNumber(props.kpis.total_clicks),
        icon:  'arrowLeft',
    },
    {
        key:   'total_spend',
        label: 'الإنفاق الإعلاني',
        value: 'ر.س ' + formatNumber(props.kpis.total_spend),
        icon:  'credit',
    },
    {
        key:            'follower_growth',
        label:          'نمو المتابعين',
        value:          (props.kpis.follower_growth >= 0 ? '+' : '') + formatNumber(props.kpis.follower_growth),
        icon:           'bell',
        highlight:      props.kpis.follower_growth > 0,
        highlightColor: 'green',
    },
])

// ── Chart.js: Engagement Over Time (ANL-02) ────────────────────────────────
const lineChartData = computed(() => ({
    labels: props.engagementOverTime.map(d => d.date),
    datasets: [
        {
            label: 'التفاعل',
            data:  props.engagementOverTime.map(d => d.engagement),
            borderColor:     '#0F6F5C',
            backgroundColor: 'rgba(15, 111, 92, 0.12)',
            borderWidth:     2,
            pointRadius:     3,
            pointBackgroundColor: '#0F6F5C',
            fill:   true,
            tension: 0.4,
        },
    ],
}))

const lineChartOptions = {
    responsive:          true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            rtl:       true,
            bodyFont:  { family: 'Tajawal, sans-serif', size: 13 },
            titleFont: { family: 'Tajawal, sans-serif', size: 12 },
        },
    },
    scales: {
        x: {
            ticks: {
                font:  { family: 'Tajawal, sans-serif', size: 11 },
                color: '#5E6A64',
                maxRotation: 0,
            },
            grid: { color: 'rgba(166, 180, 175, 0.15)' },
        },
        y: {
            ticks: {
                font:  { family: 'Tajawal, sans-serif', size: 11 },
                color: '#5E6A64',
            },
            grid: { color: 'rgba(166, 180, 175, 0.15)' },
        },
    },
}

// ── Chart.js: Platform Breakdown Doughnut (ANL-02) ─────────────────────────
const PLATFORM_COLORS: Record<string, string> = {
    instagram: '#E1306C',
    facebook:  '#1877F2',
}

const PLATFORM_LABELS_AR: Record<string, string> = {
    instagram: 'انستجرام',
    facebook:  'فيسبوك',
    tiktok:    'تيك توك',
    snapchat:  'سناب شات',
    x:         'إكس',
}

const doughnutChartData = computed(() => ({
    labels: props.platformBreakdown.map(p => PLATFORM_LABELS_AR[p.platform] ?? p.platform),
    datasets: [
        {
            data:            props.platformBreakdown.map(p => p.reach),
            backgroundColor: props.platformBreakdown.map(p => PLATFORM_COLORS[p.platform] ?? '#0F6F5C'),
            borderWidth:     2,
            borderColor:     '#FFFFFF',
            hoverOffset:     6,
        },
    ],
}))

const doughnutChartOptions = {
    responsive:          true,
    maintainAspectRatio: false,
    cutout:              '62%',
    plugins: {
        legend: {
            display:  true,
            position: 'bottom' as const,
            rtl:      true,
            labels: {
                font:      { family: 'Tajawal, sans-serif', size: 12 },
                color:     '#5E6A64',
                padding:   16,
                usePointStyle: true,
                pointStyleWidth: 8,
            },
        },
        tooltip: {
            rtl:       true,
            bodyFont:  { family: 'Tajawal, sans-serif', size: 13 },
            titleFont: { family: 'Tajawal, sans-serif', size: 12 },
        },
    },
}

// ── Arabic ordinals (top posts rank) ──────────────────────────────────────
const ARABIC_ORDINALS = ['١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '١٠']

function ordinal(n: number): string {
    return ARABIC_ORDINALS[n] ?? String(n + 1)
}

function platformColor(platform: string): string {
    return PLATFORM_COLORS[platform] ?? '#0F6F5C'
}

function platformLabel(platform: string): string {
    return PLATFORM_LABELS_AR[platform] ?? platform
}

function truncate(text: string, max = 80): string {
    return text.length > max ? text.slice(0, max) + '…' : text
}

function formatNumber(n: number): string {
    if (n === undefined || n === null) return '—'
    return n.toLocaleString('ar-SA')
}
</script>

<template>
    <AppLayout title="التحليلات" :crumbs="['الرئيسية', 'التحليلات']">
        <div class="anl-page">

            <!-- ── Filters bar (ANL-04) ─────────────────────────────────── -->
            <div class="anl-header">
                <div>
                    <h1 class="anl-title">التحليلات</h1>
                    <p class="anl-sub">تتبع أداء منشوراتك وحملاتك بشكل مفصل</p>
                </div>
                <div class="anl-export-btns">
                    <a href="/analytics/export/pdf" class="btn btn-secondary btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        PDF تصدير
                    </a>
                    <a href="/analytics/export/csv" class="btn btn-secondary btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        CSV تصدير
                    </a>
                </div>
            </div>

            <!-- Filter controls -->
            <div class="card anl-filters">
                <!-- Date range -->
                <div class="anl-filter-dates">
                    <div class="input-group" style="flex:1; min-width:140px;">
                        <label class="input-label">من</label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="input"
                        />
                    </div>
                    <div class="input-group" style="flex:1; min-width:140px;">
                        <label class="input-label">إلى</label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="input"
                        />
                    </div>
                </div>

                <!-- Platform chips -->
                <div class="anl-filter-chips">
                    <span class="input-label" style="margin-bottom:0; flex-shrink:0;">المنصة</span>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button
                            v-for="chip in PLATFORM_CHIPS"
                            :key="chip.key"
                            class="chip"
                            :data-selected="activePlatform === chip.key"
                            @click="activePlatform = chip.key"
                        >{{ chip.label }}</button>
                    </div>
                </div>

                <button class="btn btn-primary btn-sm anl-filter-apply" @click="applyFilters">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    تطبيق الفلتر
                </button>
            </div>

            <!-- ── Empty state ─────────────────────────────────────────── -->
            <div v-if="!hasData" class="anl-empty">
                <div class="anl-empty-icon">
                    <!-- Chart SVG 64px -->
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6"  y1="20" x2="6"  y2="14"/>
                        <line x1="2"  y1="20" x2="22" y2="20"/>
                    </svg>
                </div>
                <h3>لا توجد بيانات تحليلية بعد</h3>
                <p>انشر منشوراتك وستظهر التحليلات هنا تلقائياً</p>
                <Link href="/generate" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    ابدأ بتوليد محتوى
                </Link>
            </div>

            <!-- ── Data sections (only when hasData) ──────────────────── -->
            <template v-else>

                <!-- ── KPI cards row (ANL-01) ──────────────────────────── -->
                <div class="anl-kpi-grid">
                    <div
                        v-for="card in kpiCards"
                        :key="card.key"
                        class="card anl-kpi-card"
                        :class="{
                            'anl-kpi-card--accent': card.highlight && card.highlightColor === 'accent',
                            'anl-kpi-card--green':  card.highlight && card.highlightColor === 'green',
                        }"
                    >
                        <div class="anl-kpi-icon-wrap" :class="`anl-kpi-icon--${card.highlightColor ?? 'default'}`">
                            <Icon :name="card.icon" :size="18" />
                        </div>
                        <div class="anl-kpi-value">{{ card.value }}</div>
                        <div class="anl-kpi-label">{{ card.label }}</div>
                    </div>
                </div>

                <!-- ── Charts section (ANL-02) ─────────────────────────── -->
                <div class="anl-charts-grid">

                    <!-- Line chart: Engagement over time -->
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>التفاعل عبر الوقت</h3>
                                <p class="sub">إجمالي التفاعلات اليومية</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="anl-line-chart-wrap">
                                <Line
                                    v-if="engagementOverTime.length"
                                    :data="lineChartData"
                                    :options="lineChartOptions"
                                />
                                <div v-else class="anl-chart-empty">
                                    <span>لا توجد بيانات كافية</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doughnut chart: Platform breakdown -->
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>توزيع المنصات</h3>
                                <p class="sub">الوصول حسب المنصة</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="anl-doughnut-wrap">
                                <Doughnut
                                    v-if="platformBreakdown.length"
                                    :data="doughnutChartData"
                                    :options="doughnutChartOptions"
                                />
                                <div v-else class="anl-chart-empty">
                                    <span>لا توجد بيانات كافية</span>
                                </div>
                            </div>

                            <!-- Platform stats list -->
                            <div v-if="platformBreakdown.length" class="anl-platform-list">
                                <div
                                    v-for="p in platformBreakdown"
                                    :key="p.platform"
                                    class="anl-platform-row"
                                >
                                    <div class="anl-platform-dot" :style="`background:${platformColor(p.platform)}`" />
                                    <span class="anl-platform-name">{{ platformLabel(p.platform) }}</span>
                                    <span class="anl-platform-reach">{{ formatNumber(p.reach) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── Top Posts (ANL-02) ──────────────────────────────── -->
                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>أفضل المنشورات</h3>
                            <p class="sub">المنشورات الأعلى أداءً في الفترة المحددة</p>
                        </div>
                        <span class="badge badge-neutral">أعلى ٥</span>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div v-if="topPosts.length" class="anl-posts-list">
                            <div
                                v-for="(post, idx) in topPosts.slice(0, 5)"
                                :key="post.id"
                                class="anl-post-row"
                            >
                                <!-- Rank -->
                                <div class="anl-post-rank">{{ ordinal(idx) }}</div>

                                <!-- Platform dot + content -->
                                <div class="anl-post-content">
                                    <div class="anl-post-meta">
                                        <div
                                            class="anl-post-dot"
                                            :style="`background:${platformColor(post.platform)}`"
                                        />
                                        <span class="badge badge-neutral" style="font-size:11px;">
                                            {{ platformLabel(post.platform) }}
                                        </span>
                                    </div>
                                    <p class="anl-post-text">{{ truncate(post.content) }}</p>
                                </div>

                                <!-- Stats -->
                                <div class="anl-post-stats">
                                    <div class="anl-post-stat">
                                        <span class="anl-stat-label">الوصول</span>
                                        <span class="anl-stat-value">{{ formatNumber(post.reach) }}</span>
                                    </div>
                                    <div class="anl-post-stat">
                                        <span class="anl-stat-label">التفاعل</span>
                                        <span class="anl-stat-value">{{ formatNumber(post.engagement) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="anl-posts-empty">
                            <span>لا توجد منشورات بعد</span>
                        </div>
                    </div>
                </div>

                <!-- ── AI Insights (ANL-03) ───────────────────────────── -->
                <div class="card anl-insights-card">
                    <div class="card-head">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="anl-insights-icon">
                                <!-- Sparkle SVG -->
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
                                </svg>
                            </div>
                            <div>
                                <h3>رؤى الذكاء الاصطناعي</h3>
                                <p class="sub">تحليل مدعوم بالذكاء الاصطناعي لبيانات أدائك</p>
                            </div>
                        </div>
                        <span class="badge badge-brand" style="gap:5px;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                            مدعوم بـ AI
                        </span>
                    </div>
                    <div class="card-body anl-insights-body">
                        <!-- Has insights -->
                        <template v-if="aiInsights.length">
                            <div
                                v-for="(insight, idx) in aiInsights"
                                :key="idx"
                                class="anl-insight-row"
                            >
                                <div class="anl-insight-bullet">
                                    <!-- Lightbulb icon -->
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="9" y1="18" x2="15" y2="18"/>
                                        <line x1="10" y1="22" x2="14" y2="22"/>
                                        <path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>
                                    </svg>
                                </div>
                                <p class="anl-insight-text">{{ insight }}</p>
                            </div>
                        </template>
                        <!-- No insights yet -->
                        <div v-else class="anl-insights-empty">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>ستظهر الرؤى بعد تجميع البيانات الكافية</span>
                        </div>
                    </div>
                </div>

            </template>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Page wrapper ── */
.anl-page {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Page header ── */
.anl-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.anl-title { font-size: 22px; font-weight: 700; color: var(--text-primary); }
.anl-sub   { font-size: 13px; color: var(--text-muted); margin-top: 3px; }

.anl-export-btns {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* ── Filters card ── */
.anl-filters {
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
    padding: 16px 20px;
}

.anl-filter-dates {
    display: flex;
    gap: 12px;
    flex: 1;
    min-width: 280px;
    flex-wrap: wrap;
}

.anl-filter-chips {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.anl-filter-apply { flex-shrink: 0; }

/* ── Empty state ── */
.anl-empty {
    text-align: center;
    padding: 96px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    background: var(--bg-surface);
    border: 1px dashed var(--border-default);
    border-radius: var(--radius-lg);
}
.anl-empty-icon {
    width: 96px; height: 96px;
    border-radius: 50%;
    background: var(--accent-soft);
    display: grid;
    place-items: center;
    color: var(--accent);
    margin-bottom: 4px;
}
.anl-empty h3 { font-size: 17px; font-weight: 700; color: var(--text-primary); }
.anl-empty p  { font-size: 13px; color: var(--text-muted); max-width: 340px; }

/* ── KPI grid (ANL-01) ── */
.anl-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
@media (max-width: 860px) { .anl-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 520px) { .anl-kpi-grid { grid-template-columns: 1fr; } }

.anl-kpi-card {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: box-shadow var(--dur-fast), border-color var(--dur-fast);
    position: relative;
    overflow: hidden;
}
.anl-kpi-card:hover { box-shadow: var(--shadow-md); }

.anl-kpi-card--accent { border-color: var(--sada-200); background: var(--bg-surface-2); }
.anl-kpi-card--green  { border-color: #b8e8d0; }

.anl-kpi-icon-wrap {
    width: 38px; height: 38px;
    border-radius: var(--radius-md);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.anl-kpi-icon--default { background: var(--bg-muted);   color: var(--text-muted); }
.anl-kpi-icon--accent  { background: var(--accent-soft); color: var(--accent); }
.anl-kpi-icon--green   { background: #e3f4eb;            color: #0d8b5a; }

.anl-kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.1;
    letter-spacing: -0.5px;
}
.anl-kpi-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
}

/* ── Charts grid (ANL-02) ── */
.anl-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: start;
}
@media (max-width: 820px) { .anl-charts-grid { grid-template-columns: 1fr; } }

.anl-line-chart-wrap {
    height: 200px;
    width: 100%;
}

.anl-doughnut-wrap {
    height: 180px;
    width: 180px;
    margin: 0 auto 16px;
}

.anl-chart-empty {
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: var(--text-faint);
    background: var(--bg-muted);
    border-radius: var(--radius-md);
}

/* Platform list below doughnut */
.anl-platform-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 4px;
}
.anl-platform-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}
.anl-platform-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.anl-platform-name  { flex: 1; color: var(--text-secondary); font-weight: 500; }
.anl-platform-reach { color: var(--text-primary); font-weight: 700; font-size: 13px; }

/* ── Top Posts list (ANL-02) ── */
.anl-posts-list {
    display: flex;
    flex-direction: column;
}
.anl-post-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-subtle);
    transition: background var(--dur-fast);
}
.anl-post-row:last-child { border-bottom: none; }
.anl-post-row:hover { background: var(--bg-muted); }

.anl-post-rank {
    width: 28px; height: 28px;
    border-radius: var(--radius-sm);
    background: var(--bg-muted);
    display: grid;
    place-items: center;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
    flex-shrink: 0;
}

.anl-post-content { flex: 1; min-width: 0; }
.anl-post-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.anl-post-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.anl-post-text {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.anl-post-stats {
    display: flex;
    gap: 16px;
    flex-shrink: 0;
}
.anl-post-stat {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    min-width: 64px;
}
.anl-stat-label {
    font-size: 11px;
    color: var(--text-faint);
    font-weight: 600;
}
.anl-stat-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
}

.anl-posts-empty {
    padding: 32px;
    text-align: center;
    font-size: 13px;
    color: var(--text-faint);
}

/* ── AI Insights (ANL-03) ── */
.anl-insights-card { border-color: var(--border-brand); }

.anl-insights-icon {
    width: 34px; height: 34px;
    border-radius: var(--radius-md);
    background: var(--accent-soft);
    display: grid;
    place-items: center;
    color: var(--accent);
    flex-shrink: 0;
}

.anl-insights-body {
    background: var(--accent-soft);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.anl-insight-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    background: var(--bg-surface);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-brand);
}
.anl-insight-bullet {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: var(--accent-soft);
    display: grid;
    place-items: center;
    color: var(--accent);
    flex-shrink: 0;
    margin-top: 1px;
}
.anl-insight-text {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.7;
    flex: 1;
}

.anl-insights-empty {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-muted);
    padding: 8px 0;
}
</style>

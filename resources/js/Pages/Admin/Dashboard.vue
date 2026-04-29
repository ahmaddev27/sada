<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

const props = defineProps<{
    totalUsers:    number
    newUsersToday: number
    bannedUsers:   number
    totalWorkspaces:     number
    suspendedWorkspaces: number
    archivedWorkspaces:  number
    totalPosts:     number
    scheduledPosts: number
    publishedPosts: number
    failedPosts:    number
    draftPosts:     number
    totalGenerations:   number
    generationsToday:   number
    totalTokensCharged: number
    totalInputTokens:   number
    totalOutputTokens:  number
    totalSocialAccounts:   number
    healthySocialAccounts: number
    expiredSocialAccounts: number
    totalRevenue: number
    newUsersInPeriod:    number
    generationsInPeriod: number
    tokensInPeriod:      number
    revenueInPeriod:     number
    period: string
    userGrowth:       { date: string; count: number }[]
    revenueChart:     { month: string; total: number }[]
    generationsChart: { date: string; count: number; tokens: number }[]
    recentUsers:       { id: number; name: string; email: string; created_at: string; banned_at: string | null; is_admin: boolean; token_balance: number }[]
    recentGenerations: { id: number; workspace: { name: string } | null; user: { name: string } | null; agent_type: string; platform: string; sada_tokens_charged: number; cached: boolean; created_at: string }[]
    recentFailedPosts: { id: number; workspace: { name: string } | null; platform: string; scheduled_for: string | null; created_at: string }[]
}>()

const PERIODS = [
    { value: '1',   label: '٢٤ ساعة' },
    { value: '7',   label: '٧ أيام'  },
    { value: '30',  label: '٣٠ يوماً' },
    { value: '90',  label: '٩٠ يوماً' },
    { value: 'all', label: 'الكل'    },
]

function setPeriod(p: string) {
    router.get('/admin', { period: p }, { preserveState: true, replace: true })
}

const periodLabel = computed(() => PERIODS.find(p => p.value === props.period)?.label ?? '٣٠ يوماً')
const chartPeriodLabel = computed(() => props.period === 'all' ? 'كل الوقت' : `آخر ${periodLabel.value}`)

const today = new Date().toLocaleDateString('ar-SA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}
function dt(iso: string) {
    return new Date(iso).toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' })
}
function barH(val: number, max: number) {
    return `${Math.max(6, Math.round((val / Math.max(max, 1)) * 100))}%`
}

const AGENT_LABELS: Record<string, string> = {
    content_generator:  'كاتب المحتوى',
    content_generation: 'توليد المحتوى',
    seasonal:           'موسمي',
    campaign:           'حملة',
    analytics:          'تحليلات',
}
const PLATFORM_LABELS: Record<string, string> = {
    instagram: 'انستجرام',
    facebook:  'فيسبوك',
    tiktok:    'تيك توك',
    snapchat:  'سناب شات',
    x:         'X',
    twitter:   'X',
    linkedin:  'لينكدإن',
}

const userGrowthMax   = computed(() => Math.max(...props.userGrowth.map(x => x.count), 1))
const genChartMax     = computed(() => Math.max(...props.generationsChart.map(x => x.count), 1))
const revenueChartMax = computed(() => Math.max(...props.revenueChart.map(x => x.total), 1))

const postHealthPct = computed(() => {
    if (!props.totalPosts) return 0
    return Math.round((props.publishedPosts / props.totalPosts) * 100)
})
const socialHealthPct = computed(() => {
    if (!props.totalSocialAccounts) return 0
    return Math.round((props.healthySocialAccounts / props.totalSocialAccounts) * 100)
})
</script>

<template>
    <AdminLayout>
        <div class="adm-page">

            <!-- ── Page header ─────────────────────────────────────── -->
            <div class="adm-header">
                <div>
                    <h1 class="adm-title">لوحة التحكم</h1>
                    <p class="adm-date">{{ today }}</p>
                </div>
                <div class="adm-header-actions">
                    <Link href="/admin/users"          class="hdr-btn">المستخدمون</Link>
                    <Link href="/admin/workspaces"     class="hdr-btn">Workspaces</Link>
                    <Link href="/admin/posts"          class="hdr-btn">المنشورات</Link>
                    <Link href="/admin/ai-generations" class="hdr-btn hdr-btn--accent">توليدات AI</Link>
                </div>
            </div>

            <!-- ── Period selector ────────────────────────────────── -->
            <div class="period-bar">
                <span class="period-bar-label">الفترة:</span>
                <div class="period-btns">
                    <button
                        v-for="p in PERIODS"
                        :key="p.value"
                        :class="['period-btn', { 'period-btn--active': period === p.value }]"
                        @click="setPeriod(p.value)"
                    >{{ p.label }}</button>
                </div>
            </div>

            <!-- ── Alert: failed posts ─────────────────────────────── -->
            <div v-if="failedPosts > 0" class="alert-bar">
                <span class="alert-dot" />
                <span>{{ fmt(failedPosts) }} منشور فاشل يحتاج مراجعة</span>
                <Link href="/admin/posts?status=failed" class="alert-link">عرض ←</Link>
            </div>

            <!-- ── Hero KPIs ───────────────────────────────────────── -->
            <div class="hero-grid">
                <div class="hero-card hero-card--primary">
                    <div class="hero-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="hero-num">{{ fmt(totalUsers) }}</div>
                    <div class="hero-label">إجمالي المستخدمين</div>
                    <div class="hero-sub">
                        <span class="tag tag--light">+{{ newUsersToday }} اليوم</span>
                        <span class="tag tag--light">+{{ fmt(newUsersInPeriod) }} في {{ periodLabel }}</span>
                    </div>
                </div>

                <div class="hero-card">
                    <div class="hero-card-icon hero-card-icon--purple">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.09 6.26L20 10l-5.91 1.74L12 18l-2.09-6.26L4 10l5.91-1.74z"/></svg>
                    </div>
                    <div class="hero-num">{{ fmt(totalGenerations) }}</div>
                    <div class="hero-label">توليدات AI</div>
                    <div class="hero-sub">
                        <span class="tag tag--purple">+{{ generationsToday }} اليوم</span>
                        <span class="tag tag--purple">{{ fmt(generationsInPeriod) }} في {{ periodLabel }}</span>
                    </div>
                </div>

                <div class="hero-card">
                    <div class="hero-card-icon hero-card-icon--blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <div class="hero-num">{{ fmt(totalWorkspaces) }}</div>
                    <div class="hero-label">Workspaces</div>
                    <div class="hero-sub">
                        <span v-if="suspendedWorkspaces" class="tag tag--red">{{ suspendedWorkspaces }} معلّق</span>
                        <span class="tag tag--muted">{{ archivedWorkspaces }} مؤرشف</span>
                    </div>
                </div>

                <div class="hero-card">
                    <div class="hero-card-icon hero-card-icon--gold">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="hero-num">{{ fmt(totalTokensCharged) }}</div>
                    <div class="hero-label">توكنات AI (صدى)</div>
                    <div class="hero-sub">
                        <span class="tag tag--muted">{{ fmt(totalInputTokens) }} دخل</span>
                        <span class="tag tag--muted">{{ fmt(totalOutputTokens) }} خرج</span>
                    </div>
                </div>
            </div>

            <!-- ── Mid section: Posts + Social + Users ────────────── -->
            <div class="mid-grid">
                <!-- Posts breakdown -->
                <div class="stat-card">
                    <div class="stat-card-head">
                        <span class="stat-card-title">المنشورات</span>
                        <Link href="/admin/posts" class="stat-link">عرض الكل</Link>
                    </div>
                    <div class="stat-num-big">{{ fmt(totalPosts) }}</div>
                    <div class="post-bars">
                        <div class="post-bar-row">
                            <span class="post-bar-label">منشور</span>
                            <div class="post-bar-track">
                                <div class="post-bar-fill post-bar-fill--green" :style="{ width: `${totalPosts ? (publishedPosts/totalPosts)*100 : 0}%` }" />
                            </div>
                            <span class="post-bar-val green">{{ fmt(publishedPosts) }}</span>
                        </div>
                        <div class="post-bar-row">
                            <span class="post-bar-label">مجدول</span>
                            <div class="post-bar-track">
                                <div class="post-bar-fill post-bar-fill--blue" :style="{ width: `${totalPosts ? (scheduledPosts/totalPosts)*100 : 0}%` }" />
                            </div>
                            <span class="post-bar-val blue">{{ fmt(scheduledPosts) }}</span>
                        </div>
                        <div class="post-bar-row">
                            <span class="post-bar-label">مسودة</span>
                            <div class="post-bar-track">
                                <div class="post-bar-fill post-bar-fill--muted" :style="{ width: `${totalPosts ? (draftPosts/totalPosts)*100 : 0}%` }" />
                            </div>
                            <span class="post-bar-val muted">{{ fmt(draftPosts) }}</span>
                        </div>
                        <div class="post-bar-row">
                            <span class="post-bar-label">فشل</span>
                            <div class="post-bar-track">
                                <div class="post-bar-fill post-bar-fill--red" :style="{ width: `${totalPosts ? (failedPosts/totalPosts)*100 : 0}%` }" />
                            </div>
                            <span class="post-bar-val red">{{ fmt(failedPosts) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Social accounts health -->
                <div class="stat-card">
                    <div class="stat-card-head">
                        <span class="stat-card-title">الحسابات المرتبطة</span>
                        <Link href="/admin/social-accounts" class="stat-link">عرض الكل</Link>
                    </div>
                    <div class="stat-num-big">{{ fmt(totalSocialAccounts) }}</div>
                    <div class="donut-wrap">
                        <svg viewBox="0 0 36 36" class="donut-svg">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--border-default)" stroke-width="3.5"/>
                            <circle
                                cx="18" cy="18" r="15.9" fill="none"
                                stroke="var(--sada-500)" stroke-width="3.5"
                                stroke-dasharray="100" stroke-dashoffset="0"
                                :stroke-dasharray="`${socialHealthPct} ${100 - socialHealthPct}`"
                                stroke-linecap="round"
                                transform="rotate(-90 18 18)"
                            />
                            <text x="18" y="20.5" text-anchor="middle" class="donut-text">{{ socialHealthPct }}%</text>
                        </svg>
                        <div class="donut-legend">
                            <div class="legend-row">
                                <span class="legend-dot legend-dot--green" />
                                <span class="legend-lbl">متصل</span>
                                <span class="legend-val green">{{ fmt(healthySocialAccounts) }}</span>
                            </div>
                            <div class="legend-row">
                                <span class="legend-dot legend-dot--red" />
                                <span class="legend-lbl">منتهي/ملغي</span>
                                <span class="legend-val red">{{ fmt(expiredSocialAccounts) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users quick stats -->
                <div class="stat-card">
                    <div class="stat-card-head">
                        <span class="stat-card-title">المستخدمون</span>
                        <Link href="/admin/users" class="stat-link">عرض الكل</Link>
                    </div>
                    <div class="user-stats">
                        <div class="usr-row">
                            <div class="usr-icon usr-icon--blue">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <span class="usr-lbl">جدد اليوم</span>
                            <span class="usr-val blue">+{{ fmt(newUsersToday) }}</span>
                        </div>
                        <div class="usr-row">
                            <div class="usr-icon usr-icon--green">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span class="usr-lbl">جدد في {{ periodLabel }}</span>
                            <span class="usr-val green">+{{ fmt(newUsersInPeriod) }}</span>
                        </div>
                        <div class="usr-row">
                            <div class="usr-icon usr-icon--red">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            </div>
                            <span class="usr-lbl">محظورون</span>
                            <span class="usr-val red">{{ fmt(bannedUsers) }}</span>
                        </div>
                        <div class="usr-row">
                            <div class="usr-icon usr-icon--gold">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </div>
                            <span class="usr-lbl">إجمالي التوكنات (AI)</span>
                            <span class="usr-val gold">{{ fmt(totalTokensCharged) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Charts row ──────────────────────────────────────── -->
            <div class="charts-row">
                <!-- User growth -->
                <div class="chart-card">
                    <div class="chart-head">
                        <span class="chart-title">نمو المستخدمين</span>
                        <span class="chart-sub">{{ chartPeriodLabel }}</span>
                    </div>
                    <div class="spark-chart">
                        <template v-if="userGrowth.length">
                            <div
                                v-for="d in userGrowth"
                                :key="d.date"
                                class="spark-col"
                                :title="`${d.date}: ${d.count}`"
                            >
                                <div class="spark-bar spark-bar--primary" :style="{ height: barH(d.count, userGrowthMax) }" />
                            </div>
                        </template>
                        <div v-else class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>

                <!-- AI generations -->
                <div class="chart-card">
                    <div class="chart-head">
                        <span class="chart-title">توليدات AI</span>
                        <span class="chart-sub">{{ chartPeriodLabel }}</span>
                    </div>
                    <div class="spark-chart">
                        <template v-if="generationsChart.length">
                            <div
                                v-for="d in generationsChart"
                                :key="d.date"
                                class="spark-col"
                                :title="`${d.date}: ${d.count} توليد`"
                            >
                                <div class="spark-bar spark-bar--purple" :style="{ height: barH(d.count, genChartMax) }" />
                            </div>
                        </template>
                        <div v-else class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>

                <!-- Revenue (tokens sold) -->
                <div class="chart-card chart-card--wide">
                    <div class="chart-head">
                        <span class="chart-title">الإيرادات (توكنات مُباعة)</span>
                        <span class="chart-sub">{{ chartPeriodLabel }} · {{ fmt(revenueInPeriod) }} توكن</span>
                    </div>
                    <div class="spark-chart spark-chart--labeled">
                        <template v-if="revenueChart.length">
                            <div
                                v-for="d in revenueChart"
                                :key="d.month"
                                class="spark-col spark-col--labeled"
                                :title="`${d.month}: ${fmt(d.total)}`"
                            >
                                <div class="spark-val">{{ d.total ? fmt(d.total) : '' }}</div>
                                <div class="spark-bar spark-bar--gold" :style="{ height: barH(d.total, revenueChartMax) }" />
                                <div class="spark-month">{{ d.month.slice(5) }}</div>
                            </div>
                        </template>
                        <div v-else class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>
            </div>

            <!-- ── Recent tables ───────────────────────────────────── -->
            <div class="tbl-grid">
                <!-- Recent users -->
                <div class="tbl-card">
                    <div class="tbl-head">
                        <span class="tbl-title">آخر المستخدمين</span>
                        <Link href="/admin/users" class="tbl-link-all">عرض الكل</Link>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>التوكنات</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in recentUsers" :key="u.id">
                                <td>
                                    <div class="cell-user">
                                        <div class="cell-avatar">{{ u.name.charAt(0) }}</div>
                                        <div>
                                            <Link :href="`/admin/users/${u.id}`" class="cell-name">{{ u.name }}</Link>
                                            <div class="cell-email">{{ u.email }}</div>
                                        </div>
                                        <span v-if="u.is_admin" class="pill pill--gold">إداري</span>
                                    </div>
                                </td>
                                <td class="cell-num">{{ fmt(u.token_balance) }}</td>
                                <td>
                                    <span :class="['pill', u.banned_at ? 'pill--red' : 'pill--green']">
                                        {{ u.banned_at ? 'محظور' : 'فعّال' }}
                                    </span>
                                </td>
                                <td class="cell-date">{{ dt(u.created_at) }}</td>
                            </tr>
                            <tr v-if="!recentUsers.length">
                                <td colspan="4" class="cell-empty">لا يوجد مستخدمون</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recent AI generations -->
                <div class="tbl-card">
                    <div class="tbl-head">
                        <span class="tbl-title">آخر التوليدات</span>
                        <Link href="/admin/ai-generations" class="tbl-link-all">عرض الكل</Link>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Workspace</th>
                                <th>النوع</th>
                                <th>التوكنات</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in recentGenerations" :key="g.id">
                                <td class="cell-muted">{{ g.workspace?.name ?? '—' }}</td>
                                <td><span class="pill pill--ghost">{{ AGENT_LABELS[g.agent_type] ?? g.agent_type }}</span></td>
                                <td>
                                    <span :class="g.cached ? 'green' : ''">{{ fmt(g.sada_tokens_charged) }}</span>
                                    <span v-if="g.cached" class="pill pill--muted" style="margin-right:4px">مخزن</span>
                                </td>
                                <td class="cell-date">{{ dt(g.created_at) }}</td>
                            </tr>
                            <tr v-if="!recentGenerations.length">
                                <td colspan="4" class="cell-empty">لا يوجد توليدات</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Failed posts table (conditional) ───────────────── -->
            <div v-if="recentFailedPosts.length" class="tbl-card tbl-card--danger">
                <div class="tbl-head">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span class="tbl-title" style="color:#ef4444">منشورات فاشلة — تحتاج مراجعة</span>
                    </div>
                    <Link href="/admin/posts?status=failed" class="tbl-link-all" style="color:#ef4444">عرض الكل</Link>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Workspace</th>
                            <th>المنصة</th>
                            <th>المجدول لـ</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in recentFailedPosts" :key="p.id">
                            <td class="cell-muted">{{ p.workspace?.name ?? '—' }}</td>
                            <td><span class="pill pill--ghost">{{ PLATFORM_LABELS[p.platform] ?? p.platform }}</span></td>
                            <td class="cell-muted">{{ p.scheduled_for ? dt(p.scheduled_for) : '—' }}</td>
                            <td class="cell-date">{{ dt(p.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* ── Page shell ─────────────────────────────────── */
.adm-page { padding: 28px 32px; }

/* ── Header ─────────────────────────────────────── */
.adm-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 16px; margin-bottom: 22px;
}
.adm-title { font-size: 22px; font-weight: 800; color: var(--text-primary); margin: 0 0 4px; }
.adm-date  { font-size: 12px; color: var(--text-muted); margin: 0; }

.adm-header-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.hdr-btn {
    font-size: 12px; font-weight: 600; padding: 7px 14px;
    border-radius: var(--radius-md); text-decoration: none;
    color: var(--text-secondary);
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    transition: background .15s, border-color .15s;
}
.hdr-btn:hover { background: var(--bg-muted); border-color: var(--border-hover); }
.hdr-btn--accent {
    color: var(--sada-600);
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    border-color: color-mix(in oklab, var(--sada-500) 25%, transparent);
}
.hdr-btn--accent:hover { background: color-mix(in oklab, var(--sada-500) 18%, transparent); }

/* ── Period selector ────────────────────────────── */
.period-bar {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.period-bar-label { font-size: 12px; color: var(--text-muted); font-weight: 600; }
.period-btns { display: flex; gap: 4px; flex-wrap: wrap; }
.period-btn {
    font-size: 12px; font-weight: 600; padding: 5px 14px;
    border-radius: var(--radius-md);
    background: var(--bg-surface); border: 1px solid var(--border-default);
    color: var(--text-secondary); cursor: pointer;
    font-family: var(--font-arabic);
    transition: all .15s;
}
.period-btn:hover { background: var(--bg-muted); border-color: var(--border-hover); }
.period-btn--active {
    background: var(--sada-500); border-color: var(--sada-500);
    color: #fff;
}

/* ── Alert bar ──────────────────────────────────── */
.alert-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; margin-bottom: 18px;
    background: color-mix(in oklab, #ef4444 7%, transparent);
    border: 1px solid color-mix(in oklab, #ef4444 22%, transparent);
    border-radius: var(--radius-md);
    font-size: 13px; font-weight: 600; color: #ef4444;
}
.alert-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #ef4444; flex-shrink: 0;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{ opacity:1 } 50%{ opacity:.4 } }
.alert-link { margin-right: auto; font-size: 12px; color: #ef4444; text-decoration: none; font-weight: 700; }
.alert-link:hover { text-decoration: underline; }

/* ── Hero KPIs ──────────────────────────────────── */
.hero-grid { display: grid; grid-template-columns: 1.3fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px; }

.hero-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 20px 22px;
    position: relative; overflow: hidden;
    transition: box-shadow .2s;
}
.hero-card:hover { box-shadow: 0 4px 20px color-mix(in oklab, var(--text-primary) 6%, transparent); }
.hero-card--primary {
    background: linear-gradient(135deg, var(--sada-500) 0%, color-mix(in oklab, var(--sada-500) 70%, #0a4a3b) 100%);
    border-color: transparent;
    color: #fff;
}
.hero-card--primary .hero-label { color: rgba(255,255,255,.75); }
.hero-card--primary .hero-num   { color: #fff; }

.hero-card-icon {
    width: 42px; height: 42px; border-radius: var(--radius-md);
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
    display: grid; place-items: center; margin-bottom: 14px;
}
.hero-card--primary .hero-card-icon { background: rgba(255,255,255,.18); color: #fff; }
.hero-card-icon--purple { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #8b5cf6; }
.hero-card-icon--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #3b82f6; }
.hero-card-icon--gold   { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; }

.hero-num   { font-size: 32px; font-weight: 900; color: var(--text-primary); line-height: 1; margin-bottom: 4px; }
.hero-label { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-bottom: 12px; }
.hero-sub   { display: flex; gap: 6px; flex-wrap: wrap; }

/* Tags */
.tag { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
.tag--light  { background: rgba(255,255,255,.25); color: #fff; }
.tag--purple { background: color-mix(in oklab, #8b5cf6 14%, transparent); color: #8b5cf6; }
.tag--red    { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }
.tag--muted  { background: var(--bg-muted); color: var(--text-muted); }

/* ── Mid grid ────────────────────────────────────── */
.mid-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 14px; margin-bottom: 16px; }

.stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
}
.stat-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.stat-card-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.stat-link { font-size: 11px; color: var(--sada-500); text-decoration: none; font-weight: 600; }
.stat-link:hover { text-decoration: underline; }
.stat-num-big { font-size: 28px; font-weight: 900; color: var(--text-primary); margin-bottom: 16px; }

/* Horizontal progress bars */
.post-bars { display: flex; flex-direction: column; gap: 10px; }
.post-bar-row { display: flex; align-items: center; gap: 10px; }
.post-bar-label { font-size: 11px; color: var(--text-muted); width: 38px; text-align: right; flex-shrink: 0; }
.post-bar-track { flex: 1; height: 6px; background: var(--bg-muted); border-radius: 99px; overflow: hidden; }
.post-bar-fill { height: 100%; border-radius: 99px; transition: width .4s ease; min-width: 2px; }
.post-bar-fill--green { background: #10b981; }
.post-bar-fill--blue  { background: #3b82f6; }
.post-bar-fill--muted { background: var(--border-default); }
.post-bar-fill--red   { background: #ef4444; }
.post-bar-val { font-size: 12px; font-weight: 700; width: 34px; text-align: left; }

/* Donut chart */
.donut-wrap { display: flex; align-items: center; gap: 20px; }
.donut-svg { width: 90px; height: 90px; flex-shrink: 0; }
.donut-text { font-size: 9px; font-weight: 800; fill: var(--text-primary); font-family: var(--font-arabic); }

.donut-legend { display: flex; flex-direction: column; gap: 10px; }
.legend-row { display: flex; align-items: center; gap: 8px; font-size: 12px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-dot--green { background: var(--sada-500); }
.legend-dot--red   { background: #ef4444; }
.legend-lbl { color: var(--text-muted); flex: 1; }
.legend-val { font-weight: 700; }

/* User stats list */
.user-stats { display: flex; flex-direction: column; gap: 0; }
.usr-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-subtle);
}
.usr-row:last-child { border-bottom: none; }
.usr-icon {
    width: 28px; height: 28px; border-radius: var(--radius-sm);
    display: grid; place-items: center; flex-shrink: 0;
}
.usr-icon--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #3b82f6; }
.usr-icon--green  { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; }
.usr-icon--red    { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; }
.usr-icon--gold   { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; }
.usr-lbl { font-size: 12px; color: var(--text-muted); flex: 1; }
.usr-val { font-size: 14px; font-weight: 800; }

/* ── Charts row ──────────────────────────────────── */
.charts-row { display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 14px; margin-bottom: 16px; }
.chart-card--wide { /* already placed by grid */ }

.chart-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
}
.chart-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; gap: 8px; }
.chart-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.chart-sub   { font-size: 11px; color: var(--text-muted); }

.spark-chart { display: flex; align-items: flex-end; gap: 3px; height: 72px; }
.spark-chart--labeled { height: 100px; }
.spark-col { flex: 1; display: flex; align-items: flex-end; height: 100%; }
.spark-col--labeled { flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 0; }

.spark-bar { width: 100%; border-radius: 3px 3px 0 0; min-height: 6px; transition: height .3s; }
.spark-bar--primary { background: linear-gradient(180deg, var(--sada-500) 0%, color-mix(in oklab, var(--sada-500) 60%, transparent) 100%); }
.spark-bar--purple  { background: linear-gradient(180deg, #8b5cf6 0%, color-mix(in oklab, #8b5cf6 60%, transparent) 100%); }
.spark-bar--gold    { background: linear-gradient(180deg, #f59e0b 0%, color-mix(in oklab, #f59e0b 60%, transparent) 100%); }

.spark-val   { font-size: 8px; color: var(--text-muted); white-space: nowrap; margin-bottom: 3px; }
.spark-month { font-size: 9px; color: var(--text-muted); margin-top: 5px; }

.chart-empty { font-size: 12px; color: var(--text-muted); margin: auto; }

/* ── Tables ──────────────────────────────────────── */
.tbl-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }

.tbl-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 14px;
}
.tbl-card--danger {
    border-color: color-mix(in oklab, #ef4444 25%, transparent);
    background: color-mix(in oklab, #ef4444 4%, var(--bg-surface));
}

.tbl-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-bottom: 1px solid var(--border-subtle);
}
.tbl-title    { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.tbl-link-all { font-size: 11px; color: var(--sada-500); text-decoration: none; font-weight: 600; }
.tbl-link-all:hover { text-decoration: underline; }

.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table th {
    padding: 9px 16px; text-align: right;
    color: var(--text-muted); font-weight: 600; font-size: 11px;
    background: var(--bg-muted);
    border-bottom: 1px solid var(--border-subtle);
    white-space: nowrap;
}
.data-table td { padding: 10px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: color-mix(in oklab, var(--bg-muted) 50%, transparent); }

.cell-user  { display: flex; align-items: center; gap: 10px; }
.cell-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: color-mix(in oklab, var(--sada-500) 15%, transparent);
    color: var(--sada-600); font-size: 12px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}
.cell-name  { color: var(--sada-600); text-decoration: none; font-weight: 600; font-size: 12px; display: block; }
.cell-name:hover { text-decoration: underline; }
.cell-email { font-size: 10px; color: var(--text-muted); margin-top: 1px; }
.cell-num   { font-weight: 700; color: var(--text-primary); font-variant-numeric: tabular-nums; }
.cell-date  { color: var(--text-muted); white-space: nowrap; }
.cell-muted { color: var(--text-muted); }
.cell-empty { text-align: center; padding: 24px; color: var(--text-muted); }

/* Pills */
.pill { display: inline-flex; align-items: center; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; white-space: nowrap; }
.pill--green  { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.pill--red    { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }
.pill--gold   { background: color-mix(in oklab, #f59e0b 14%, transparent); color: #f59e0b; }
.pill--ghost  { background: var(--bg-muted); color: var(--text-muted); }
.pill--muted  { background: var(--bg-muted); color: var(--text-muted); font-size: 9px; }

/* Color utilities */
.green { color: #10b981 !important; }
.red   { color: #ef4444 !important; }
.blue  { color: #3b82f6 !important; }
.gold  { color: #f59e0b !important; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 1100px) {
    .hero-grid   { grid-template-columns: repeat(2, 1fr); }
    .mid-grid    { grid-template-columns: 1fr 1fr; }
    .charts-row  { grid-template-columns: 1fr 1fr; }
    .chart-card--wide { grid-column: span 2; }
}
@media (max-width: 720px) {
    .adm-page    { padding: 16px; }
    .hero-grid   { grid-template-columns: 1fr; }
    .mid-grid    { grid-template-columns: 1fr; }
    .charts-row  { grid-template-columns: 1fr; }
    .chart-card--wide { grid-column: span 1; }
    .tbl-grid    { grid-template-columns: 1fr; }
    .adm-header  { flex-direction: column; }
}
</style>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

defineProps<{
    // Users
    totalUsers:    number
    newUsersToday: number
    newUsersWeek:  number
    bannedUsers:   number
    // Workspaces
    totalWorkspaces:     number
    suspendedWorkspaces: number
    archivedWorkspaces:  number
    // Posts
    totalPosts:     number
    scheduledPosts: number
    publishedPosts: number
    failedPosts:    number
    draftPosts:     number
    // AI
    totalGenerations:   number
    generationsToday:   number
    totalTokensCharged: number
    totalInputTokens:   number
    totalOutputTokens:  number
    // Social
    totalSocialAccounts:   number
    healthySocialAccounts: number
    expiredSocialAccounts: number
    // Revenue
    totalRevenue: number
    // Charts
    userGrowth:       { date: string; count: number }[]
    revenueChart:     { month: string; total: number }[]
    generationsChart: { date: string; count: number; tokens: number }[]
    // Recent
    recentUsers:       { id: number; name: string; email: string; created_at: string; banned_at: string | null; is_admin: boolean; token_balance: number }[]
    recentGenerations: { id: number; workspace: { name: string } | null; user: { name: string } | null; agent_type: string; platform: string; sada_tokens_charged: number; cached: boolean; created_at: string }[]
    recentFailedPosts: { id: number; workspace: { name: string } | null; platform: string; scheduled_for: string | null; created_at: string }[]
}>()

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}

function dt(iso: string) {
    return new Date(iso).toLocaleDateString('ar-SA')
}

function barH(val: number, max: number) {
    return `${Math.max(4, Math.round((val / Math.max(max, 1)) * 100))}%`
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <h1 class="page-title">لوحة التحكم الرئيسية</h1>
                <span class="page-date">{{ new Date().toLocaleDateString('ar-SA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
            </div>

            <!-- ── Row 1: Users ──────────────────── -->
            <div class="section-label">المستخدمون</div>
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalUsers) }}</div>
                        <div class="kpi-label">إجمالي المستخدمين</div>
                        <div class="kpi-sub green">+{{ newUsersToday }} اليوم · +{{ newUsersWeek }} الأسبوع</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(bannedUsers) }}</div>
                        <div class="kpi-label">محظورون</div>
                        <div class="kpi-sub muted">{{ bannedUsers === 0 ? 'لا يوجد محظورون' : 'مستخدم محظور' }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalWorkspaces) }}</div>
                        <div class="kpi-label">Workspaces</div>
                        <div class="kpi-sub muted">{{ suspendedWorkspaces }} معلّق · {{ archivedWorkspaces }} مؤرشف</div>
                    </div>
                </div>
                <div class="kpi-card kpi-card--accent">
                    <div class="kpi-icon kpi-icon--gold">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalRevenue) }}</div>
                        <div class="kpi-label">إجمالي توكنات مُشتراة</div>
                        <div class="kpi-sub muted">من عمليات الشراء</div>
                    </div>
                </div>
            </div>

            <!-- ── Row 2: Posts ──────────────────── -->
            <div class="section-label">المنشورات</div>
            <div class="kpi-row kpi-row--5">
                <div class="kpi-card kpi-card--sm">
                    <div class="kpi-value">{{ fmt(totalPosts) }}</div>
                    <div class="kpi-label">الكل</div>
                </div>
                <div class="kpi-card kpi-card--sm">
                    <div class="kpi-value blue">{{ fmt(scheduledPosts) }}</div>
                    <div class="kpi-label">مجدول</div>
                </div>
                <div class="kpi-card kpi-card--sm">
                    <div class="kpi-value green">{{ fmt(publishedPosts) }}</div>
                    <div class="kpi-label">منشور</div>
                </div>
                <div class="kpi-card kpi-card--sm">
                    <div class="kpi-value red">{{ fmt(failedPosts) }}</div>
                    <div class="kpi-label">فشل</div>
                </div>
                <div class="kpi-card kpi-card--sm">
                    <div class="kpi-value muted">{{ fmt(draftPosts) }}</div>
                    <div class="kpi-label">مسودة</div>
                </div>
            </div>

            <!-- ── Row 3: AI + Social ────────────── -->
            <div class="section-label">الذكاء الاصطناعي والحسابات المرتبطة</div>
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalGenerations) }}</div>
                        <div class="kpi-label">توليدات AI</div>
                        <div class="kpi-sub green">+{{ generationsToday }} اليوم</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalTokensCharged) }}</div>
                        <div class="kpi-label">توكنات محملة (صدى)</div>
                        <div class="kpi-sub muted">{{ fmt(totalInputTokens) }} دخل · {{ fmt(totalOutputTokens) }} خرج</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value">{{ fmt(totalSocialAccounts) }}</div>
                        <div class="kpi-label">حسابات مرتبطة</div>
                        <div class="kpi-sub">
                            <span class="green">{{ healthySocialAccounts }} متصل</span>
                            · <span class="red">{{ expiredSocialAccounts }} منتهي</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <div class="kpi-value red">{{ fmt(failedPosts) }}</div>
                        <div class="kpi-label">منشورات فاشلة</div>
                        <div class="kpi-sub muted">تحتاج مراجعة</div>
                    </div>
                </div>
            </div>

            <!-- ── Charts ────────────────────────── -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">نمو المستخدمين — 30 يوماً</h3>
                    <div class="bar-chart">
                        <div
                            v-for="d in userGrowth"
                            :key="d.date"
                            class="bar-col"
                            :title="`${d.date}: ${d.count} مستخدم`"
                        >
                            <div class="bar bar--blue" :style="{ height: barH(d.count, Math.max(...userGrowth.map(x => x.count), 1)) }" />
                        </div>
                        <div v-if="!userGrowth.length" class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">توليدات AI — 14 يوماً</h3>
                    <div class="bar-chart">
                        <div
                            v-for="d in generationsChart"
                            :key="d.date"
                            class="bar-col"
                            :title="`${d.date}: ${d.count} توليد`"
                        >
                            <div class="bar bar--purple" :style="{ height: barH(d.count, Math.max(...generationsChart.map(x => x.count), 1)) }" />
                        </div>
                        <div v-if="!generationsChart.length" class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>

                <div class="chart-card chart-card--full">
                    <h3 class="chart-title">الإيرادات (توكنات مُشتراة) — 6 أشهر</h3>
                    <div class="bar-chart bar-chart--labeled">
                        <div
                            v-for="d in revenueChart"
                            :key="d.month"
                            class="bar-col bar-col--labeled"
                            :title="`${d.month}: ${fmt(d.total)}`"
                        >
                            <div class="bar-label-top">{{ fmt(d.total) }}</div>
                            <div class="bar bar--green" :style="{ height: barH(d.total, Math.max(...revenueChart.map(x => x.total), 1)) }" />
                            <div class="bar-label-bot">{{ d.month.slice(5) }}</div>
                        </div>
                        <div v-if="!revenueChart.length" class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>
            </div>

            <!-- ── Recent tables ────────────────── -->
            <div class="tables-grid">
                <!-- Recent users -->
                <div class="table-card">
                    <div class="table-card-header">
                        <h3 class="table-card-title">آخر المستخدمين</h3>
                        <Link href="/admin/users" class="table-card-link">عرض الكل ←</Link>
                    </div>
                    <table class="mini-table">
                        <thead><tr><th>الاسم</th><th>التوكنات</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            <tr v-for="u in recentUsers" :key="u.id">
                                <td>
                                    <Link :href="`/admin/users/${u.id}`" class="tbl-link">{{ u.name }}</Link>
                                    <span v-if="u.is_admin" class="badge badge--admin">Admin</span>
                                </td>
                                <td class="muted">{{ fmt(u.token_balance) }}</td>
                                <td>
                                    <span :class="['dot', u.banned_at ? 'dot--red' : 'dot--green']">{{ u.banned_at ? 'محظور' : 'فعّال' }}</span>
                                </td>
                                <td class="muted">{{ dt(u.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recent AI generations -->
                <div class="table-card">
                    <div class="table-card-header">
                        <h3 class="table-card-title">آخر التوليدات</h3>
                        <Link href="/admin/ai-generations" class="table-card-link">عرض الكل ←</Link>
                    </div>
                    <table class="mini-table">
                        <thead><tr><th>Workspace</th><th>النوع</th><th>التوكنات</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            <tr v-for="g in recentGenerations" :key="g.id">
                                <td class="muted">{{ g.workspace?.name ?? '—' }}</td>
                                <td><span class="type-badge">{{ g.agent_type }}</span></td>
                                <td :class="g.cached ? 'green' : ''">{{ fmt(g.sada_tokens_charged) }} {{ g.cached ? '(مخزن)' : '' }}</td>
                                <td class="muted">{{ dt(g.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Failed posts alert -->
            <div v-if="recentFailedPosts.length" class="failed-alert">
                <div class="failed-alert-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>منشورات فاشلة تحتاج مراجعة</span>
                    <Link href="/admin/posts?status=failed" class="failed-link">عرض الكل ←</Link>
                </div>
                <table class="mini-table">
                    <thead><tr><th>Workspace</th><th>المنصة</th><th>المجدول لـ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                        <tr v-for="p in recentFailedPosts" :key="p.id">
                            <td class="muted">{{ p.workspace?.name ?? '—' }}</td>
                            <td>{{ p.platform }}</td>
                            <td class="muted">{{ p.scheduled_for ? dt(p.scheduled_for) : '—' }}</td>
                            <td class="muted">{{ dt(p.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Quick nav -->
            <div class="quick-nav">
                <Link href="/admin/users"          class="quick-link">المستخدمون</Link>
                <Link href="/admin/workspaces"     class="quick-link">Workspaces</Link>
                <Link href="/admin/posts"          class="quick-link">المنشورات</Link>
                <Link href="/admin/social-accounts" class="quick-link">الحسابات المرتبطة</Link>
                <Link href="/admin/ai-generations" class="quick-link">توليدات AI</Link>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 24px 28px; max-width: 1200px; }

.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 8px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0; }
.page-date   { font-size: 12px; color: var(--text-muted); }

.section-label {
    font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    color: var(--text-muted); margin: 20px 0 10px;
}
.section-label:first-of-type { margin-top: 0; }

/* ── KPI cards ── */
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.kpi-row--5 { grid-template-columns: repeat(5, 1fr); }

.kpi-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    display: flex; align-items: flex-start; gap: 14px;
}
.kpi-card--sm { display: block; padding: 14px 18px; }
.kpi-card--accent { border-color: color-mix(in oklab, var(--sada-500) 30%, transparent); }

.kpi-icon {
    width: 38px; height: 38px; border-radius: var(--radius-md);
    display: grid; place-items: center; flex-shrink: 0;
}
.kpi-icon--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #3b82f6; }
.kpi-icon--red    { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; }
.kpi-icon--green  { background: color-mix(in oklab, var(--sada-500) 12%, transparent); color: var(--sada-500); }
.kpi-icon--purple { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #8b5cf6; }
.kpi-icon--gold   { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; }

.kpi-value { font-size: 26px; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 4px; }
.kpi-label { font-size: 11px; color: var(--text-muted); font-weight: 600; }
.kpi-sub   { font-size: 11px; margin-top: 5px; }
.kpi-card--sm .kpi-value { font-size: 22px; }

/* ── Charts ── */
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
.chart-card--full { grid-column: span 2; }

.chart-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
}
.chart-title { font-size: 12px; font-weight: 600; color: var(--text-muted); margin: 0 0 14px; }

.bar-chart { display: flex; align-items: flex-end; gap: 3px; height: 80px; }
.bar-chart--labeled { height: 110px; align-items: flex-end; }

.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
.bar-col--labeled { gap: 4px; height: 100%; justify-content: flex-end; }

.bar { width: 100%; border-radius: 3px 3px 0 0; min-height: 4px; transition: height .3s; }
.bar--blue   { background: #3b82f6; }
.bar--green  { background: var(--sada-500); }
.bar--purple { background: #8b5cf6; }

.bar-label-top { font-size: 8px; color: var(--text-muted); white-space: nowrap; }
.bar-label-bot { font-size: 9px; color: var(--text-muted); margin-top: 3px; }

.chart-empty { font-size: 12px; color: var(--text-muted); margin: auto; }

/* ── Tables ── */
.tables-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }

.table-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.table-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px 10px;
    border-bottom: 1px solid var(--border-subtle);
}
.table-card-title { font-size: 13px; font-weight: 700; color: var(--text-primary); margin: 0; }
.table-card-link  { font-size: 11px; color: var(--sada-500); text-decoration: none; font-weight: 600; }
.table-card-link:hover { text-decoration: underline; }

.mini-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.mini-table th { padding: 8px 14px; color: var(--text-muted); font-weight: 600; text-align: right; background: var(--bg-muted); border-bottom: 1px solid var(--border-subtle); white-space: nowrap; }
.mini-table td { padding: 9px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.mini-table tr:last-child td { border-bottom: none; }
.mini-table tr:hover td { background: var(--bg-muted); }

.tbl-link { color: var(--sada-500); text-decoration: none; font-weight: 600; }
.tbl-link:hover { text-decoration: underline; }

/* ── Badges / dots ── */
.badge { font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 99px; margin-right: 5px; }
.badge--admin { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }

.dot { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 99px; }
.dot--green { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.dot--red   { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }

.type-badge { font-size: 10px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 7px; border-radius: 5px; }

/* ── Colors ── */
.green { color: #10b981 !important; }
.red   { color: #ef4444 !important; }
.blue  { color: #3b82f6 !important; }
.muted { color: var(--text-muted) !important; }

/* ── Failed posts alert ── */
.failed-alert {
    background: color-mix(in oklab, #ef4444 6%, transparent);
    border: 1px solid color-mix(in oklab, #ef4444 25%, transparent);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 16px;
}
.failed-alert-header {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    color: #ef4444;
    font-size: 13px;
    font-weight: 600;
    border-bottom: 1px solid color-mix(in oklab, #ef4444 15%, transparent);
}
.failed-link { margin-right: auto; font-size: 11px; color: #ef4444; text-decoration: none; font-weight: 600; }
.failed-link:hover { text-decoration: underline; }

/* ── Quick nav ── */
.quick-nav { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.quick-link {
    font-size: 12px; font-weight: 600;
    color: var(--sada-500);
    text-decoration: none;
    padding: 8px 14px;
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
    border: 1px solid color-mix(in oklab, var(--sada-500) 20%, transparent);
    border-radius: var(--radius-md);
    transition: background .15s;
}
.quick-link:hover { background: color-mix(in oklab, var(--sada-500) 15%, transparent); }

/* ── Responsive ── */
@media (max-width: 1000px) {
    .kpi-row     { grid-template-columns: repeat(2, 1fr); }
    .kpi-row--5  { grid-template-columns: repeat(3, 1fr); }
    .charts-grid { grid-template-columns: 1fr; }
    .chart-card--full { grid-column: span 1; }
    .tables-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .admin-page { padding: 16px; }
    .kpi-row    { grid-template-columns: 1fr; }
    .kpi-row--5 { grid-template-columns: repeat(2, 1fr); }
}
</style>

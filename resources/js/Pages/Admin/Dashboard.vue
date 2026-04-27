<script setup lang="ts">
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

defineProps<{
    totalUsers:      number
    totalWorkspaces: number
    totalPosts:      number
    totalRevenue:    number
    newUsersToday:   number
    newUsersWeek:    number
    userGrowth:      { date: string; count: number }[]
    revenueChart:    { month: string; total: number }[]
}>()

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <h1 class="admin-page-title">لوحة التحكم</h1>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">إجمالي المستخدمين</div>
                    <div class="kpi-value">{{ fmt(totalUsers) }}</div>
                    <div class="kpi-sub">+{{ newUsersToday }} اليوم · +{{ newUsersWeek }} هذا الأسبوع</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Workspaces</div>
                    <div class="kpi-value">{{ fmt(totalWorkspaces) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">إجمالي المنشورات</div>
                    <div class="kpi-value">{{ fmt(totalPosts) }}</div>
                </div>
                <div class="kpi-card kpi-card--accent">
                    <div class="kpi-label">إجمالي الإيرادات</div>
                    <div class="kpi-value">{{ fmt(totalRevenue) }} <span style="font-size:14px;font-weight:500">توكن</span></div>
                </div>
            </div>

            <!-- Charts row -->
            <div class="charts-row">
                <!-- User growth -->
                <div class="chart-card">
                    <h3 class="chart-title">نمو المستخدمين — آخر 30 يوماً</h3>
                    <div class="bar-chart">
                        <div
                            v-for="d in userGrowth"
                            :key="d.date"
                            class="bar-wrap"
                            :title="`${d.date}: ${d.count}`"
                        >
                            <div
                                class="bar"
                                :style="{ height: `${Math.max(4, (d.count / Math.max(...userGrowth.map(x => x.count), 1)) * 100)}%` }"
                            />
                        </div>
                        <div v-if="!userGrowth.length" class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>

                <!-- Revenue chart -->
                <div class="chart-card">
                    <h3 class="chart-title">الإيرادات — آخر 6 أشهر</h3>
                    <div class="bar-chart bar-chart--revenue">
                        <div
                            v-for="d in revenueChart"
                            :key="d.month"
                            class="bar-wrap"
                            :title="`${d.month}: ${fmt(d.total)}`"
                        >
                            <div class="bar-label">{{ d.month.slice(5) }}</div>
                            <div
                                class="bar bar--green"
                                :style="{ height: `${Math.max(4, (d.total / Math.max(...revenueChart.map(x => x.total), 1)) * 100)}%` }"
                            />
                        </div>
                        <div v-if="!revenueChart.length" class="chart-empty">لا توجد بيانات</div>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="quick-links">
                <a href="/admin/users" class="quick-link">إدارة المستخدمين ←</a>
                <a href="/admin/workspaces" class="quick-link">إدارة Workspaces ←</a>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; max-width: 1100px; }
.admin-page-title { font-size: 22px; font-weight: 700; color: #f1f5f9; margin: 0 0 24px; }

/* KPI */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
.kpi-card {
    background: #161b27; border: 1px solid #1e2535; border-radius: 12px;
    padding: 18px 20px;
}
.kpi-card--accent { border-color: color-mix(in oklab, #0F6F5C 40%, transparent); }
.kpi-label { font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; }
.kpi-value { font-size: 28px; font-weight: 800; color: #f1f5f9; line-height: 1; }
.kpi-sub   { font-size: 11px; color: #4ade80; margin-top: 6px; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px; }
.chart-card {
    background: #161b27; border: 1px solid #1e2535; border-radius: 12px;
    padding: 18px 20px;
}
.chart-title { font-size: 13px; font-weight: 600; color: #94a3b8; margin: 0 0 16px; }

.bar-chart {
    display: flex; align-items: flex-end; gap: 3px;
    height: 100px; position: relative;
}
.bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 4px; height: 100%; }
.bar { width: 100%; border-radius: 3px 3px 0 0; background: #3b82f6; min-height: 4px; transition: height .3s; }
.bar--green { background: #0F6F5C; }
.bar-label  { font-size: 9px; color: #475569; }
.chart-empty { font-size: 12px; color: #475569; margin: auto; }

/* Quick links */
.quick-links { display: flex; gap: 12px; }
.quick-link {
    font-size: 13px; font-weight: 600; color: #4ade80;
    text-decoration: none; padding: 10px 16px;
    background: color-mix(in oklab, #0F6F5C 15%, transparent);
    border: 1px solid color-mix(in oklab, #0F6F5C 30%, transparent);
    border-radius: 8px; transition: all .15s;
}
.quick-link:hover { background: color-mix(in oklab, #0F6F5C 25%, transparent); }

@media (max-width: 900px) {
    .kpi-grid    { grid-template-columns: repeat(2, 1fr); }
    .charts-row  { grid-template-columns: 1fr; }
}
</style>

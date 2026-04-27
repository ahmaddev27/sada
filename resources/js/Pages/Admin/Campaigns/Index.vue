<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface Campaign {
    id: number
    name: string
    objective: string
    platform: string
    status: string
    budget_type: string
    budget_amount: string
    budget_currency: string
    starts_at: string | null
    ends_at: string | null
    created_at: string
    workspace: { id: number; name: string } | null
    social_account: { id: number; provider: string; account_name: string } | null
}

interface Paginator {
    data: Campaign[]
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Stats {
    total: number
    active: number
    pending: number
    draft: number
    paused: number
    today: number
    budget_total: number
}

const props = defineProps<{
    campaigns: Paginator
    filters:   { search?: string; status?: string; platform?: string }
    stats:     Stats
}>()

const search   = ref(props.filters.search   ?? '')
const status   = ref(props.filters.status   ?? '')
const platform = ref(props.filters.platform ?? '')

const forceStatusTarget = ref<Campaign | null>(null)
const forceStatusVal    = ref('')

function applyFilter() {
    router.get('/admin/campaigns', { search: search.value, status: status.value, platform: platform.value }, { preserveState: true, replace: true })
}

function openForceStatus(c: Campaign) {
    forceStatusTarget.value = c
    forceStatusVal.value    = c.status
}

function submitForceStatus() {
    if (! forceStatusTarget.value) return
    router.post(`/admin/campaigns/${forceStatusTarget.value.id}/force-status`, { status: forceStatusVal.value }, {
        onSuccess: () => { forceStatusTarget.value = null },
    })
}

const STATUS_META: Record<string, { label: string; cls: string }> = {
    draft:     { label: 'مسودة',         cls: 'st--gray'  },
    pending:   { label: 'قيد المراجعة',   cls: 'st--blue'  },
    active:    { label: 'نشطة',           cls: 'st--green' },
    paused:    { label: 'موقوفة',         cls: 'st--amber' },
    completed: { label: 'مكتملة',         cls: 'st--teal'  },
    rejected:  { label: 'مرفوضة',         cls: 'st--red'   },
}

const OBJECTIVE_LABELS: Record<string, string> = {
    awareness:    'وعي',
    traffic:      'زيارات',
    engagement:   'تفاعل',
    conversions:  'تحويلات',
    app_installs: 'تثبيت تطبيق',
    video_views:  'مشاهدات فيديو',
}

function statusMeta(s: string) { return STATUS_META[s] ?? { label: s, cls: 'st--gray' } }

function fmt(d: string | null) {
    return d ? new Date(d).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الحملات الإعلانية</h1>
                    <p class="page-sub">{{ stats.total }} حملة · {{ stats.today }} اليوم</p>
                </div>
            </div>

            <!-- KPI row -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num">{{ stats.total }}</span>
                        <span class="kpi-icon kpi-icon--blue">📣</span>
                    </div>
                    <div class="kpi-label">إجمالي</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--green">{{ stats.active }}</span>
                        <span class="kpi-icon kpi-icon--green">▶</span>
                    </div>
                    <div class="kpi-label">نشطة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--blue">{{ stats.pending }}</span>
                        <span class="kpi-icon kpi-icon--blue-light">⏳</span>
                    </div>
                    <div class="kpi-label">قيد المراجعة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--amber">{{ stats.paused }}</span>
                        <span class="kpi-icon kpi-icon--amber">⏸</span>
                    </div>
                    <div class="kpi-label">موقوفة</div>
                </div>
                <div class="kpi-card kpi-card--wide">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--sada">{{ stats.budget_total.toLocaleString('ar', { maximumFractionDigits: 0 }) }}</span>
                        <span class="kpi-icon kpi-icon--sada">💰</span>
                    </div>
                    <div class="kpi-label">إجمالي الميزانيات (SAR)</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-row">
                <input v-model="search" class="admin-input" placeholder="بحث باسم الحملة..." @keyup.enter="applyFilter" />
                <select v-model="status" class="admin-select" @change="applyFilter">
                    <option value="">كل الحالات</option>
                    <option value="draft">مسودة</option>
                    <option value="pending">قيد المراجعة</option>
                    <option value="active">نشطة</option>
                    <option value="paused">موقوفة</option>
                    <option value="completed">مكتملة</option>
                    <option value="rejected">مرفوضة</option>
                </select>
                <select v-model="platform" class="admin-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الحملة</th>
                            <th>الـ Workspace</th>
                            <th>المنصة</th>
                            <th>الهدف</th>
                            <th>الميزانية</th>
                            <th>الفترة</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in campaigns.data" :key="c.id">
                            <td>
                                <div class="camp-name">{{ c.name }}</div>
                                <div class="camp-id muted">#{{ c.id }}</div>
                            </td>
                            <td>
                                <div v-if="c.workspace" class="ws-cell">{{ c.workspace.name }}</div>
                                <span v-else class="muted">—</span>
                            </td>
                            <td>
                                <span :class="['plat-pill', c.platform === 'instagram' ? 'plat--pink' : 'plat--blue']">
                                    {{ c.platform === 'instagram' ? 'انستجرام' : 'فيسبوك' }}
                                </span>
                            </td>
                            <td>
                                <span class="obj-badge">{{ OBJECTIVE_LABELS[c.objective] ?? c.objective }}</span>
                            </td>
                            <td>
                                <div class="budget-val">{{ parseFloat(c.budget_amount).toLocaleString('ar', { maximumFractionDigits: 0 }) }}</div>
                                <div class="muted" style="font-size:10px">{{ c.budget_currency }} · {{ c.budget_type === 'daily' ? 'يومية' : 'إجمالية' }}</div>
                            </td>
                            <td class="muted" style="font-size:12px; white-space:nowrap">
                                {{ fmt(c.starts_at) }}<br>{{ fmt(c.ends_at) }}
                            </td>
                            <td>
                                <span :class="['status-pill', statusMeta(c.status).cls]">
                                    ● {{ statusMeta(c.status).label }}
                                </span>
                            </td>
                            <td>
                                <button class="action-btn" @click="openForceStatus(c)">تغيير الحالة</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="campaigns.last_page > 1" class="pagination">
                <template v-for="link in campaigns.links" :key="link.label">
                    <a v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>

        </div>

        <!-- Force status modal -->
        <Teleport to="body">
            <div v-if="forceStatusTarget" class="modal-overlay" @click.self="forceStatusTarget = null">
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">تغيير حالة الحملة</h3>
                        <button class="modal-close" @click="forceStatusTarget = null">✕</button>
                    </div>
                    <div class="modal-body">
                        <p class="modal-camp-name">{{ forceStatusTarget.name }}</p>
                        <select v-model="forceStatusVal" class="admin-select full-w">
                            <option value="draft">مسودة</option>
                            <option value="pending">قيد المراجعة</option>
                            <option value="active">نشطة</option>
                            <option value="paused">موقوفة</option>
                            <option value="completed">مكتملة</option>
                            <option value="rejected">مرفوضة</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="admin-btn-sm admin-btn-sm--ghost" @click="forceStatusTarget = null">إلغاء</button>
                        <button class="admin-btn-sm" @click="submitForceStatus">حفظ</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.admin-page  { padding: 24px 28px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0 0 2px; }
.page-sub    { font-size: 12px; color: var(--text-muted); margin: 0; }

/* KPI */
.kpi-row  { display: grid; grid-template-columns: repeat(4, 1fr) 1.4fr; gap: 12px; margin-bottom: 20px; }
.kpi-card {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); padding: 16px 18px;
    display: flex; flex-direction: column; gap: 6px;
}
.kpi-top   { display: flex; align-items: center; justify-content: space-between; }
.kpi-num   { font-size: 26px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kpi-num--green { color: #10b981; }
.kpi-num--blue  { color: #3b82f6; }
.kpi-num--amber { color: #f59e0b; }
.kpi-num--sada  { color: var(--sada-500); }
.kpi-icon { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; }
.kpi-icon--blue       { background: color-mix(in oklab, #3b82f6 14%, transparent); }
.kpi-icon--blue-light { background: color-mix(in oklab, #60a5fa 14%, transparent); }
.kpi-icon--green      { background: color-mix(in oklab, #10b981 14%, transparent); }
.kpi-icon--amber      { background: color-mix(in oklab, #f59e0b 14%, transparent); }
.kpi-icon--sada       { background: color-mix(in oklab, var(--sada-500) 14%, transparent); }
.kpi-label { font-size: 12px; color: var(--text-muted); }

/* Filters */
.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 200px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

/* Table */
.table-wrap  { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }

.camp-name { font-weight: 600; font-size: 13px; }
.camp-id   { font-size: 11px; }
.ws-cell   { font-size: 13px; color: var(--text-muted); }
.budget-val { font-weight: 700; font-size: 14px; color: var(--sada-500); }
.muted { color: var(--text-muted) !important; }

.plat-pill   { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; white-space: nowrap; }
.plat--pink  { background: color-mix(in oklab, #e1306c 12%, transparent); color: #e1306c; }
.plat--blue  { background: color-mix(in oklab, #1877f2 12%, transparent); color: #1877f2; }

.obj-badge { font-size: 11px; font-weight: 600; padding: 2px 9px; border-radius: 99px; background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #8b5cf6; }

.status-pill { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; white-space: nowrap; }
.st--gray  { background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border-default); }
.st--blue  { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #3b82f6; border: 1px solid color-mix(in oklab, #3b82f6 28%, transparent); }
.st--green { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border: 1px solid color-mix(in oklab, #10b981 28%, transparent); }
.st--amber { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; border: 1px solid color-mix(in oklab, #f59e0b 28%, transparent); }
.st--teal  { background: color-mix(in oklab, #06b6d4 12%, transparent); color: #06b6d4; border: 1px solid color-mix(in oklab, #06b6d4 28%, transparent); }
.st--red   { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; border: 1px solid color-mix(in oklab, #ef4444 28%, transparent); }

.action-btn { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; border: 1px solid var(--border-default); background: var(--bg-surface); color: var(--text-muted); transition: all .15s; font-family: var(--font-arabic); }
.action-btn:hover { background: var(--bg-muted); color: var(--text-primary); }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 999; }
.modal { background: var(--bg-surface); border-radius: var(--radius-lg); width: 360px; max-width: calc(100vw - 32px); box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); }
.modal-title  { font-size: 15px; font-weight: 700; color: var(--text-primary); margin: 0; }
.modal-close  { background: none; border: none; font-size: 16px; color: var(--text-muted); cursor: pointer; line-height: 1; }
.modal-body   { padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.modal-camp-name { font-size: 13px; font-weight: 600; color: var(--text-primary); margin: 0; }
.modal-footer { display: flex; gap: 8px; justify-content: flex-end; padding: 14px 20px; border-top: 1px solid var(--border-subtle); }
.full-w { width: 100%; }

.admin-btn-sm { height: 32px; padding: 0 16px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 12px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); }
.admin-btn-sm:hover { background: var(--sada-600); }
.admin-btn-sm--ghost { background: var(--bg-muted); color: var(--text-primary); }
.admin-btn-sm--ghost:hover { background: var(--border-default); }

@media (max-width: 900px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } }
</style>

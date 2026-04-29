<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import { useConfirmStore } from '@/Stores/confirm'

const confirmStore = useConfirmStore()

interface Workspace {
    id: number
    name: string
    business_type: string | null
    posts_count: number
    suspended_at: string | null
    archived_at: string | null
    created_at: string
    user: { id: number; name: string; email: string }
}

interface Paginator {
    data: Workspace[]
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Stats {
    total: number
    active: number
    suspended: number
    archived: number
    today: number
}

const props = defineProps<{
    workspaces: Paginator
    filters:    { search?: string; status?: string }
    stats:      Stats
}>()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

function applyFilter() {
    router.get('/admin/workspaces', { search: search.value, status: status.value }, { preserveState: true, replace: true })
}

async function suspend(ws: Workspace) {
    const ok = await confirmStore.ask({ title: `تعليق "${ws.name}"؟`, confirmText: 'تعليق', dangerous: true })
    if (!ok) return
    router.post(`/admin/workspaces/${ws.id}/suspend`, {})
}

function restore(ws: Workspace) {
    router.post(`/admin/workspaces/${ws.id}/restore`, {})
}

const BIZ_LABELS: Record<string, string> = {
    product:  'منتج',
    service:  'خدمة',
    personal: 'شخصي',
    persona:  'شخصية',
}

function initials(name: string) {
    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase()
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Workspaces</h1>
                    <p class="page-sub">{{ stats.total }} workspace مسجّل · {{ stats.today }} اليوم</p>
                </div>
            </div>

            <!-- KPI row -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num">{{ stats.total.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--blue">🏢</span>
                    </div>
                    <div class="kpi-label">إجمالي</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--green">{{ stats.active.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--green">✓</span>
                    </div>
                    <div class="kpi-label">فعّالة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--amber">{{ stats.suspended.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--amber">⏸</span>
                    </div>
                    <div class="kpi-label">معلّقة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--muted">{{ stats.archived.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--muted">📦</span>
                    </div>
                    <div class="kpi-label">مؤرشفة</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-row">
                <input v-model="search" class="admin-input" placeholder="بحث بالاسم..." @keyup.enter="applyFilter" />
                <select v-model="status" class="admin-select" @change="applyFilter">
                    <option value="">الكل</option>
                    <option value="active">فعّال</option>
                    <option value="suspended">معلّق</option>
                    <option value="archived">مؤرشف</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الـ Workspace</th>
                            <th>المالك</th>
                            <th>نوع النشاط</th>
                            <th>المنشورات</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ws in workspaces.data" :key="ws.id">
                            <td>
                                <div class="ws-cell">
                                    <div class="ws-avatar">{{ initials(ws.name) }}</div>
                                    <div class="ws-name">{{ ws.name }}</div>
                                </div>
                            </td>
                            <td>
                                <Link :href="`/admin/users/${ws.user.id}`" class="user-link">{{ ws.user.name }}</Link>
                                <div class="sub-email" dir="ltr">{{ ws.user.email }}</div>
                            </td>
                            <td>
                                <span v-if="ws.business_type" class="biz-badge">{{ BIZ_LABELS[ws.business_type] ?? ws.business_type }}</span>
                                <span v-else class="muted">—</span>
                            </td>
                            <td class="center">
                                <span class="count-badge">{{ ws.posts_count }}</span>
                            </td>
                            <td>
                                <span v-if="ws.suspended_at" class="status-pill status--suspended">● معلّق</span>
                                <span v-else-if="ws.archived_at" class="status-pill status--archived">● مؤرشف</span>
                                <span v-else class="status-pill status--active">● فعّال</span>
                            </td>
                            <td class="muted">{{ new Date(ws.created_at).toLocaleDateString('ar-SA') }}</td>
                            <td>
                                <div class="row-actions">
                                    <button v-if="!ws.suspended_at && !ws.archived_at" class="action-btn action-btn--warn" @click="suspend(ws)">تعليق</button>
                                    <button v-if="ws.suspended_at" class="action-btn action-btn--green" @click="restore(ws)">استعادة</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="workspaces.last_page > 1" class="pagination">
                <template v-for="link in workspaces.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page  { padding: 24px 28px; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0 0 2px; }
.page-sub    { font-size: 12px; color: var(--text-muted); margin: 0; }

/* KPI */
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
.kpi-card {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); padding: 16px 18px;
    display: flex; flex-direction: column; gap: 6px;
}
.kpi-top   { display: flex; align-items: center; justify-content: space-between; }
.kpi-num   { font-size: 26px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kpi-num--green { color: #10b981; }
.kpi-num--amber { color: #f59e0b; }
.kpi-num--muted { color: var(--text-muted); }
.kpi-icon  { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; }
.kpi-icon--blue  { background: color-mix(in oklab, #3b82f6 14%, transparent); }
.kpi-icon--green { background: color-mix(in oklab, #10b981 14%, transparent); }
.kpi-icon--amber { background: color-mix(in oklab, #f59e0b 14%, transparent); }
.kpi-icon--muted { background: var(--bg-muted); }
.kpi-label { font-size: 12px; color: var(--text-muted); }

/* Filters */
.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 220px; }
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

.ws-cell   { display: flex; align-items: center; gap: 10px; }
.ws-avatar {
    width: 32px; height: 32px; border-radius: 8px;
    background: color-mix(in oklab, var(--sada-500) 15%, transparent);
    color: var(--sada-500); font-size: 12px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}
.ws-name   { font-weight: 600; font-size: 13px; color: var(--text-primary); }

.user-link  { color: var(--sada-500); text-decoration: none; font-size: 13px; font-weight: 600; }
.user-link:hover { text-decoration: underline; }
.sub-email  { font-size: 11px; color: var(--text-muted); }

.biz-badge  { font-size: 11px; font-weight: 600; padding: 2px 9px; border-radius: 99px; background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #8b5cf6; }
.count-badge { font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 99px; background: var(--bg-muted); color: var(--text-muted); }

.status-pill { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; white-space: nowrap; }
.status--active    { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border: 1px solid color-mix(in oklab, #10b981 28%, transparent); }
.status--suspended { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; border: 1px solid color-mix(in oklab, #f59e0b 28%, transparent); }
.status--archived  { background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border-default); }

.muted  { color: var(--text-muted); font-size: 12px; }
.center { text-align: center; }

.row-actions { display: flex; gap: 4px; }
.action-btn  { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; border: 1px solid var(--border-default); background: var(--bg-surface); color: var(--text-muted); transition: all .15s; font-family: var(--font-arabic); }
.action-btn--warn:hover  { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; border-color: #ef4444; }
.action-btn--green:hover { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border-color: #10b981; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }

@media (max-width: 900px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } }
</style>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface User {
    id: number
    name: string
    email: string
    is_admin: boolean
    token_balance: number
    workspaces_count: number
    banned_at: string | null
    created_at: string
}

interface Paginator {
    data: User[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Stats {
    total: number
    active: number
    banned: number
    admins: number
    today: number
}

const props = defineProps<{
    users:   Paginator
    filters: { search?: string; status?: string }
    stats:   Stats
}>()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

function applyFilter() {
    router.get('/admin/users', { search: search.value, status: status.value }, { preserveState: true, replace: true })
}

function ban(user: User) {
    if (! confirm(`حظر ${user.name}؟`)) return
    router.post(`/admin/users/${user.id}/ban`, {})
}

function unban(user: User) {
    router.post(`/admin/users/${user.id}/unban`, {})
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
                    <h1 class="page-title">المستخدمون</h1>
                    <p class="page-sub">{{ stats.total }} مستخدم مسجّل · {{ stats.today }} اليوم</p>
                </div>
            </div>

            <!-- KPI row -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num">{{ stats.total.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--blue">👥</span>
                    </div>
                    <div class="kpi-label">إجمالي المستخدمين</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--green">{{ stats.active.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--green">✓</span>
                    </div>
                    <div class="kpi-label">فعّالون</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--red">{{ stats.banned.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--red">🚫</span>
                    </div>
                    <div class="kpi-label">محظورون</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <span class="kpi-num kpi-num--amber">{{ stats.admins.toLocaleString('ar') }}</span>
                        <span class="kpi-icon kpi-icon--amber">⭐</span>
                    </div>
                    <div class="kpi-label">مدراء</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-row">
                <input
                    v-model="search"
                    class="admin-input"
                    placeholder="بحث بالاسم أو الإيميل..."
                    @keyup.enter="applyFilter"
                />
                <select v-model="status" class="admin-select" @change="applyFilter">
                    <option value="">الكل</option>
                    <option value="active">فعّال</option>
                    <option value="banned">محظور</option>
                    <option value="admin">مدير</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>Workspaces</th>
                            <th>التوكنات</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id" class="clickable-row" @click="router.visit(`/admin/users/${u.id}`)">
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">{{ initials(u.name) }}</div>
                                    <div>
                                        <div class="user-name">
                                            {{ u.name }}
                                            <span v-if="u.is_admin" class="badge badge--admin">Admin</span>
                                        </div>
                                        <div class="user-email" dir="ltr">{{ u.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <span class="count-badge">{{ u.workspaces_count }}</span>
                            </td>
                            <td class="center">
                                <span class="token-val">{{ u.token_balance.toLocaleString('ar') }}</span>
                            </td>
                            <td>
                                <span v-if="u.banned_at"   class="status-pill status--banned">● محظور</span>
                                <span v-else-if="u.is_admin" class="status-pill status--admin">● مدير</span>
                                <span v-else               class="status-pill status--active">● فعّال</span>
                            </td>
                            <td class="muted">{{ new Date(u.created_at).toLocaleDateString('ar-SA') }}</td>
                            <td @click.stop>
                                <div class="row-actions">
                                    <Link :href="`/admin/users/${u.id}`" class="action-btn">عرض</Link>
                                    <button v-if="!u.banned_at && !u.is_admin" class="action-btn action-btn--warn" @click="ban(u)">حظر</button>
                                    <button v-if="u.banned_at" class="action-btn action-btn--green" @click="unban(u)">رفع الحظر</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="pagination">
                <template v-for="link in users.links" :key="link.label">
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
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    display: flex; flex-direction: column; gap: 6px;
}
.kpi-top { display: flex; align-items: center; justify-content: space-between; }
.kpi-num  { font-size: 26px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kpi-num--green { color: #10b981; }
.kpi-num--red   { color: #ef4444; }
.kpi-num--amber { color: #f59e0b; }
.kpi-icon { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; }
.kpi-icon--blue  { background: color-mix(in oklab, #3b82f6 14%, transparent); }
.kpi-icon--green { background: color-mix(in oklab, #10b981 14%, transparent); }
.kpi-icon--red   { background: color-mix(in oklab, #ef4444 14%, transparent); }
.kpi-icon--amber { background: color-mix(in oklab, #f59e0b 14%, transparent); }
.kpi-label { font-size: 12px; color: var(--text-muted); }

/* Filters */
.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 240px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

/* Table */
.table-wrap { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.clickable-row { cursor: pointer; transition: background .1s; }
.clickable-row:hover td { background: color-mix(in oklab, var(--sada-500) 4%, var(--bg-muted)); }

.user-cell  { display: flex; align-items: center; gap: 10px; }
.avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff; font-size: 12px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}
.user-name  { font-weight: 600; color: var(--text-primary); font-size: 13px; }
.user-email { font-size: 11px; color: var(--text-muted); direction: ltr; }

.badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; margin-right: 6px; vertical-align: middle; }
.badge--admin { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }

.count-badge { font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 99px; background: var(--bg-muted); color: var(--text-muted); }
.token-val   { font-size: 13px; font-weight: 700; color: var(--sada-500); font-variant-numeric: tabular-nums; }

.status-pill { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; white-space: nowrap; }
.status--active { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border: 1px solid color-mix(in oklab, #10b981 28%, transparent); }
.status--banned { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; border: 1px solid color-mix(in oklab, #ef4444 28%, transparent); }
.status--admin  { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; border: 1px solid color-mix(in oklab, #f59e0b 28%, transparent); }

.muted  { color: var(--text-muted); font-size: 12px; }
.center { text-align: center; }

.row-actions { display: flex; gap: 4px; flex-wrap: wrap; }
.action-btn  {
    font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px;
    cursor: pointer; border: 1px solid var(--border-default);
    background: var(--bg-surface); color: var(--text-muted);
    text-decoration: none; display: inline-block; transition: all .15s;
    font-family: var(--font-arabic);
}
.action-btn:hover        { background: var(--bg-muted); color: var(--text-primary); }
.action-btn--warn:hover  { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; border-color: #ef4444; }
.action-btn--green:hover { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border-color: #10b981; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn {
    min-width: 34px; height: 34px; display: grid; place-items: center;
    padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600;
    background: var(--bg-surface); border: 1px solid var(--border-default);
    color: var(--text-muted); text-decoration: none; cursor: pointer;
}
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }

@media (max-width: 900px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } }
</style>

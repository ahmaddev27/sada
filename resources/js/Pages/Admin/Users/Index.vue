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

const props = defineProps<{
    users:   Paginator
    filters: { search?: string; status?: string }
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
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <h1 class="page-title">المستخدمون</h1>
                <span class="total-badge">{{ users.total }} مستخدم</span>
            </div>

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

            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الإيميل</th>
                            <th>Workspaces</th>
                            <th>التوكنات</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id">
                            <td class="muted">{{ u.id }}</td>
                            <td>
                                <Link :href="`/admin/users/${u.id}`" class="user-link">{{ u.name }}</Link>
                                <span v-if="u.is_admin" class="badge badge--admin">Admin</span>
                            </td>
                            <td class="muted" dir="ltr">{{ u.email }}</td>
                            <td class="center">{{ u.workspaces_count }}</td>
                            <td class="center">{{ u.token_balance.toLocaleString('ar') }}</td>
                            <td>
                                <span :class="['status-dot', u.banned_at ? 'status-dot--banned' : 'status-dot--active']">
                                    {{ u.banned_at ? 'محظور' : 'فعّال' }}
                                </span>
                            </td>
                            <td class="muted">{{ new Date(u.created_at).toLocaleDateString('ar-SA') }}</td>
                            <td>
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
.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0; }
.total-badge { font-size: 12px; background: var(--bg-muted); color: var(--text-muted); padding: 3px 10px; border-radius: 99px; }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 240px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

.table-wrap { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }
.muted  { color: var(--text-muted) !important; }
.center { text-align: center; }

.user-link { color: var(--sada-500); text-decoration: none; font-weight: 600; }
.user-link:hover { text-decoration: underline; }

.badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; margin-right: 6px; }
.badge--admin { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }

.status-dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.status-dot--active { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.status-dot--banned { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }

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
</style>

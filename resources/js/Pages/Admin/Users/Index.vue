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

function impersonate(user: User) {
    if (! confirm(`تصفح كـ ${user.name}؟`)) return
    router.post(`/admin/users/${user.id}/impersonate`, {})
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="admin-page-header">
                <h1 class="admin-page-title">المستخدمون</h1>
                <span class="total-badge">{{ users.total }} مستخدم</span>
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
            <div class="admin-table-wrap">
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
                                <span v-if="u.is_admin" class="badge badge-admin">Admin</span>
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
                                    <button v-if="!u.is_admin" class="action-btn action-btn--ghost" @click="impersonate(u)">دخول كـ</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="pagination">
                <template v-for="link in users.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="['page-btn', { 'page-btn--active': link.active }]"
                        v-html="link.label"
                    />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; }
.admin-page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.admin-page-title  { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0; }
.total-badge { font-size: 12px; background: #1e2535; color: #64748b; padding: 3px 10px; border-radius: 99px; }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: 8px; background: #1e2535; border: 1px solid #2d3748; color: #e2e8f0; font-size: 13px; min-width: 240px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: 8px; background: #1e2535; border: 1px solid #2d3748; color: #e2e8f0; font-size: 13px; }
.admin-btn    { height: 36px; padding: 0 16px; border-radius: 8px; background: #0F6F5C; color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }
.admin-btn:hover { background: #0A5A4B; }

.admin-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid #1e2535; }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: #161b27; color: #64748b; font-weight: 600; text-align: right; border-bottom: 1px solid #1e2535; white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid #1e2535; color: #cbd5e1; vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #161b27; }
.muted  { color: #64748b !important; }
.center { text-align: center; }

.user-link { color: #93c5fd; text-decoration: none; font-weight: 600; }
.user-link:hover { text-decoration: underline; }

.badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; margin-right: 6px; }
.badge-admin { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }

.status-dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.status-dot--active { background: color-mix(in oklab, #10b981 15%, transparent); color: #10b981; }
.status-dot--banned { background: color-mix(in oklab, #ef4444 15%, transparent); color: #ef4444; }

.row-actions { display: flex; gap: 4px; flex-wrap: wrap; }
.action-btn  { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; border: 1px solid #2d3748; background: #1e2535; color: #94a3b8; text-decoration: none; display: inline-block; transition: all .15s; }
.action-btn:hover        { background: #2d3748; color: #e2e8f0; }
.action-btn--warn:hover  { background: color-mix(in oklab, #ef4444 15%, transparent); color: #ef4444; border-color: #ef4444; }
.action-btn--green:hover { background: color-mix(in oklab, #10b981 15%, transparent); color: #10b981; border-color: #10b981; }
.action-btn--ghost:hover { background: color-mix(in oklab, #3b82f6 15%, transparent); color: #3b82f6; border-color: #3b82f6; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: 7px; font-size: 12px; font-weight: 600; background: #1e2535; border: 1px solid #2d3748; color: #94a3b8; text-decoration: none; cursor: pointer; }
.page-btn--active   { background: #0F6F5C; color: #fff; border-color: #0F6F5C; }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }
</style>

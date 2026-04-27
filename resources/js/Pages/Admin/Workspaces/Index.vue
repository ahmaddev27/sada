<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

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

const props = defineProps<{
    workspaces: Paginator
    filters: { search?: string; status?: string }
}>()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

function applyFilter() {
    router.get('/admin/workspaces', { search: search.value, status: status.value }, { preserveState: true, replace: true })
}

function suspend(ws: Workspace) {
    if (! confirm(`تعليق workspace "${ws.name}"؟`)) return
    router.post(`/admin/workspaces/${ws.id}/suspend`, {})
}

function restore(ws: Workspace) {
    router.post(`/admin/workspaces/${ws.id}/restore`, {})
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="admin-page-header">
                <h1 class="admin-page-title">Workspaces</h1>
                <span class="total-badge">{{ workspaces.total }} workspace</span>
            </div>

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

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
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
                            <td class="muted">{{ ws.id }}</td>
                            <td class="bold">{{ ws.name }}</td>
                            <td>
                                <Link :href="`/admin/users/${ws.user.id}`" class="user-link">{{ ws.user.name }}</Link>
                                <div class="sub-email" dir="ltr">{{ ws.user.email }}</div>
                            </td>
                            <td class="muted">{{ ws.business_type ?? '—' }}</td>
                            <td class="center">{{ ws.posts_count }}</td>
                            <td>
                                <span v-if="ws.suspended_at" class="status-dot status-dot--suspended">معلّق</span>
                                <span v-else-if="ws.archived_at" class="status-dot status-dot--archived">مؤرشف</span>
                                <span v-else class="status-dot status-dot--active">فعّال</span>
                            </td>
                            <td class="muted">{{ new Date(ws.created_at).toLocaleDateString('ar-SA') }}</td>
                            <td>
                                <div class="row-actions">
                                    <button v-if="!ws.suspended_at" class="action-btn action-btn--warn" @click="suspend(ws)">تعليق</button>
                                    <button v-else class="action-btn action-btn--green" @click="restore(ws)">استعادة</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
.admin-page { padding: 28px 32px; }
.admin-page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.admin-page-title  { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0; }
.total-badge { font-size: 12px; background: #1e2535; color: #64748b; padding: 3px 10px; border-radius: 99px; }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: 8px; background: #1e2535; border: 1px solid #2d3748; color: #e2e8f0; font-size: 13px; min-width: 220px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: 8px; background: #1e2535; border: 1px solid #2d3748; color: #e2e8f0; font-size: 13px; }
.admin-btn    { height: 36px; padding: 0 16px; border-radius: 8px; background: #0F6F5C; color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }

.admin-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid #1e2535; }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: #161b27; color: #64748b; font-weight: 600; text-align: right; border-bottom: 1px solid #1e2535; white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid #1e2535; color: #cbd5e1; vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #161b27; }
.muted  { color: #64748b !important; }
.bold   { font-weight: 600; }
.center { text-align: center; }

.user-link   { color: #93c5fd; text-decoration: none; font-size: 13px; font-weight: 600; }
.user-link:hover { text-decoration: underline; }
.sub-email   { font-size: 11px; color: #475569; direction: ltr; }

.status-dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.status-dot--active    { background: color-mix(in oklab, #10b981 15%, transparent); color: #10b981; }
.status-dot--suspended { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }
.status-dot--archived  { background: color-mix(in oklab, #64748b 15%, transparent); color: #64748b; }

.row-actions { display: flex; gap: 4px; }
.action-btn  { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; border: 1px solid #2d3748; background: #1e2535; color: #94a3b8; transition: all .15s; }
.action-btn--warn:hover  { background: color-mix(in oklab, #ef4444 15%, transparent); color: #ef4444; border-color: #ef4444; }
.action-btn--green:hover { background: color-mix(in oklab, #10b981 15%, transparent); color: #10b981; border-color: #10b981; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: 7px; font-size: 12px; font-weight: 600; background: #1e2535; border: 1px solid #2d3748; color: #94a3b8; text-decoration: none; cursor: pointer; }
.page-btn--active   { background: #0F6F5C; color: #fff; border-color: #0F6F5C; }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }
</style>

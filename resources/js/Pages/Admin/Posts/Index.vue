<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface Post {
    id: number
    content: string
    platform: string
    status: string
    scheduled_for: string | null
    published_at: string | null
    created_at: string
    workspace: { id: number; name: string } | null
    user: { id: number; name: string } | null
}

interface Paginator {
    data: Post[]
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    posts:   Paginator
    counts:  { all: number; scheduled: number; published: number; failed: number; draft: number }
    filters: { search?: string; status?: string; platform?: string }
}>()

const search   = ref(props.filters.search ?? '')
const status   = ref(props.filters.status ?? '')
const platform = ref(props.filters.platform ?? '')

function applyFilter() {
    router.get('/admin/posts', { search: search.value, status: status.value, platform: platform.value }, { preserveState: true, replace: true })
}

function statusLabel(s: string) {
    return { draft: 'مسودة', scheduled: 'مجدول', published: 'منشور', failed: 'فشل' }[s] ?? s
}

function statusClass(s: string) {
    return { draft: 'dot--gray', scheduled: 'dot--blue', published: 'dot--green', failed: 'dot--red' }[s] ?? ''
}

function platformLabel(p: string) {
    return { instagram: 'انستجرام', facebook: 'فيسبوك', tiktok: 'تيك توك', snapchat: 'سناب شات' }[p] ?? p
}

function dt(iso: string | null) {
    return iso ? new Date(iso).toLocaleDateString('ar-SA') : '—'
}

function truncate(text: string, len = 60) {
    return text.length > len ? text.slice(0, len) + '…' : text
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <h1 class="page-title">المنشورات</h1>
                <span class="total-badge">{{ posts.total }} منشور</span>
            </div>

            <!-- Status tabs -->
            <div class="status-tabs">
                <button
                    v-for="tab in [
                        { key: '', label: 'الكل', count: counts.all },
                        { key: 'scheduled', label: 'مجدول', count: counts.scheduled },
                        { key: 'published', label: 'منشور', count: counts.published },
                        { key: 'failed',    label: 'فشل',    count: counts.failed },
                        { key: 'draft',     label: 'مسودة',  count: counts.draft },
                    ]"
                    :key="tab.key"
                    :class="['tab-btn', { 'tab-btn--active': status === tab.key }]"
                    @click="status = tab.key; applyFilter()"
                >
                    {{ tab.label }}
                    <span class="tab-count">{{ tab.count }}</span>
                </button>
            </div>

            <div class="filters-row">
                <input v-model="search" class="admin-input" placeholder="بحث في المحتوى..." @keyup.enter="applyFilter" />
                <select v-model="platform" class="admin-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X (تويتر)</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المحتوى</th>
                            <th>Workspace</th>
                            <th>المستخدم</th>
                            <th>المنصة</th>
                            <th>الحالة</th>
                            <th>المجدول لـ</th>
                            <th>تاريخ الإنشاء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in posts.data" :key="p.id">
                            <td class="muted">{{ p.id }}</td>
                            <td class="content-cell">{{ truncate(p.content) }}</td>
                            <td class="muted">{{ p.workspace?.name ?? '—' }}</td>
                            <td class="muted">{{ p.user?.name ?? '—' }}</td>
                            <td><span class="platform-badge">{{ platformLabel(p.platform) }}</span></td>
                            <td><span :class="['dot', statusClass(p.status)]">{{ statusLabel(p.status) }}</span></td>
                            <td class="muted">{{ dt(p.scheduled_for) }}</td>
                            <td class="muted">{{ dt(p.created_at) }}</td>
                        </tr>
                        <tr v-if="!posts.data.length">
                            <td colspan="8" class="empty-row">لا توجد منشورات</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="posts.last_page > 1" class="pagination">
                <template v-for="link in posts.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page  { padding: 24px 28px; }
.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0; }
.total-badge { font-size: 12px; background: var(--bg-muted); color: var(--text-muted); padding: 3px 10px; border-radius: 99px; }

.status-tabs { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
.tab-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: var(--radius-md);
    font-size: 12px; font-weight: 600;
    border: 1px solid var(--border-default);
    background: var(--bg-surface); color: var(--text-muted);
    cursor: pointer; font-family: var(--font-arabic); transition: all .15s;
}
.tab-btn:hover        { background: var(--bg-muted); color: var(--text-primary); }
.tab-btn--active      { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.tab-count { font-size: 10px; background: rgba(255,255,255,.2); padding: 1px 5px; border-radius: 99px; }
.tab-btn:not(.tab-btn--active) .tab-count { background: var(--bg-muted); color: var(--text-muted); }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 220px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

.table-wrap  { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }
.muted        { color: var(--text-muted) !important; }
.content-cell { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.empty-row    { text-align: center; color: var(--text-muted); padding: 24px !important; }

.dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.dot--green { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.dot--blue  { background: color-mix(in oklab, #3b82f6 14%, transparent); color: #3b82f6; }
.dot--red   { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }
.dot--gray  { background: var(--bg-muted); color: var(--text-muted); }

.platform-badge { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 8px; border-radius: 5px; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }
</style>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface Generation {
    id: number
    agent_type: string
    platform: string
    sada_tokens_charged: number
    cached: boolean
    created_at: string
    workspace: { id: number; name: string } | null
    user: { id: number; name: string } | null
}

interface Paginator {
    data: Generation[]
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    generations: Paginator
    stats: {
        total: number
        today: number
        tokens_charged: number
        input_tokens: number
        output_tokens: number
        cached_count: number
    }
    filters: { search?: string; platform?: string; agent_type?: string }
}>()

const search    = ref(props.filters.search ?? '')
const platform  = ref(props.filters.platform ?? '')
const agentType = ref(props.filters.agent_type ?? '')

function applyFilter() {
    router.get('/admin/ai-generations', { search: search.value, platform: platform.value, agent_type: agentType.value }, { preserveState: true, replace: true })
}

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}

function dt(iso: string) {
    return new Date(iso).toLocaleDateString('ar-SA')
}

function platformLabel(p: string) {
    return { instagram: 'انستجرام', facebook: 'فيسبوك', tiktok: 'تيك توك', snapchat: 'سناب شات' }[p] ?? (p || '—')
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <h1 class="page-title">توليدات الذكاء الاصطناعي</h1>
                <span class="total-badge">{{ fmt(generations.total) }} توليد</span>
            </div>

            <!-- Stats bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-val">{{ fmt(stats.total) }}</div>
                    <div class="stat-label">إجمالي التوليدات</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val green">+{{ fmt(stats.today) }}</div>
                    <div class="stat-label">اليوم</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val purple">{{ fmt(stats.tokens_charged) }}</div>
                    <div class="stat-label">توكنات صدى محملة</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val muted">{{ fmt(stats.input_tokens) }}</div>
                    <div class="stat-label">توكنات دخل (Anthropic)</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val muted">{{ fmt(stats.output_tokens) }}</div>
                    <div class="stat-label">توكنات خرج (Anthropic)</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val blue">{{ fmt(stats.cached_count) }}</div>
                    <div class="stat-label">مخزّن مؤقتاً</div>
                </div>
            </div>

            <div class="filters-row">
                <input v-model="search" class="admin-input" placeholder="بحث بالـ workspace..." @keyup.enter="applyFilter" />
                <select v-model="platform" class="admin-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X (تويتر)</option>
                </select>
                <select v-model="agentType" class="admin-select" @change="applyFilter">
                    <option value="">كل الأنواع</option>
                    <option value="content_generation">توليد محتوى</option>
                    <option value="seasonal">موسمي</option>
                    <option value="campaign">حملة</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Workspace</th>
                            <th>المستخدم</th>
                            <th>نوع الوكيل</th>
                            <th>المنصة</th>
                            <th>توكنات صدى</th>
                            <th>مخزّن</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="g in generations.data" :key="g.id">
                            <td class="muted">{{ g.id }}</td>
                            <td class="bold">{{ g.workspace?.name ?? '—' }}</td>
                            <td class="muted">{{ g.user?.name ?? '—' }}</td>
                            <td><span class="type-badge">{{ g.agent_type }}</span></td>
                            <td><span class="platform-badge">{{ platformLabel(g.platform) }}</span></td>
                            <td class="purple bold">{{ fmt(g.sada_tokens_charged) }}</td>
                            <td>
                                <span v-if="g.cached" class="dot dot--blue">مخزّن</span>
                                <span v-else class="muted">—</span>
                            </td>
                            <td class="muted">{{ dt(g.created_at) }}</td>
                        </tr>
                        <tr v-if="!generations.data.length">
                            <td colspan="8" class="empty-row">لا توجد بيانات</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="generations.last_page > 1" class="pagination">
                <template v-for="link in generations.links" :key="link.label">
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

.stats-bar {
    display: flex; align-items: center; gap: 0;
    background: var(--bg-surface); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); padding: 16px 20px;
    margin-bottom: 16px; flex-wrap: wrap; gap: 12px;
}
.stat-item { text-align: center; flex: 1; min-width: 80px; }
.stat-val   { font-size: 18px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.stat-label { font-size: 10px; color: var(--text-muted); margin-top: 4px; font-weight: 600; }
.stat-divider { width: 1px; height: 36px; background: var(--border-subtle); flex-shrink: 0; }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 200px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

.table-wrap  { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }
.muted  { color: var(--text-muted) !important; }
.bold   { font-weight: 600; }
.green  { color: #10b981 !important; }
.blue   { color: #3b82f6 !important; }
.purple { color: #8b5cf6 !important; }
.empty-row { text-align: center; color: var(--text-muted); padding: 24px !important; }

.type-badge     { font-size: 11px; background: color-mix(in oklab, #8b5cf6 10%, transparent); color: #8b5cf6; padding: 2px 8px; border-radius: 5px; }
.platform-badge { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 8px; border-radius: 5px; }
.dot     { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.dot--blue { background: color-mix(in oklab, #3b82f6 14%, transparent); color: #3b82f6; }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }
</style>

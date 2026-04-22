<script setup lang="ts">
// SCH-11: posts history
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface SocialAccount {
    id: number
    provider: string
    account_name: string
}

interface Post {
    id: number
    content: string
    platform: string
    content_type: string
    dialect: string
    status: string
    scheduled_for: string | null
    published_at: string | null
    created_at: string
    social_account: SocialAccount | null
    metadata: Record<string, string> | null
}

interface Paginator {
    data: Post[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    posts: Paginator
    filters: { status?: string; platform?: string; content_type?: string; search?: string }
}>()

// ── Filter state ─────────────────────────────────────────────
const search      = ref(props.filters.search ?? '')
const status      = ref(props.filters.status ?? 'all')
const platform    = ref(props.filters.platform ?? 'all')
const contentType = ref(props.filters.content_type ?? 'all')
const view        = ref<'table' | 'grid'>('table')
const selected    = ref<Set<number>>(new Set())

let filterTimer: ReturnType<typeof setTimeout>
function applyFilters() {
    clearTimeout(filterTimer)
    filterTimer = setTimeout(() => {
        router.get('/posts', {
            status:       status.value   !== 'all' ? status.value   : undefined,
            platform:     platform.value !== 'all' ? platform.value : undefined,
            content_type: contentType.value !== 'all' ? contentType.value : undefined,
            search:       search.value || undefined,
        }, { preserveState: true, replace: true })
    }, 350)
}

function resetFilters() {
    search.value = ''
    status.value = 'all'
    platform.value = 'all'
    contentType.value = 'all'
    applyFilters()
}

const activeFilterCount = computed(() =>
    [status.value, platform.value, contentType.value].filter(v => v !== 'all').length +
    (search.value ? 1 : 0)
)

// ── Selection ────────────────────────────────────────────────
function toggleOne(id: number) {
    const s = new Set(selected.value)
    s.has(id) ? s.delete(id) : s.add(id)
    selected.value = s
}
function toggleAll() {
    if (selected.value.size === props.posts.data.length) {
        selected.value = new Set()
    } else {
        selected.value = new Set(props.posts.data.map(p => p.id))
    }
}
const allSelected    = computed(() => props.posts.data.length > 0 && selected.value.size === props.posts.data.length)
const someSelected   = computed(() => selected.value.size > 0 && selected.value.size < props.posts.data.length)

// ── Actions ──────────────────────────────────────────────────
function deletePost(id: number) {
    if (!confirm('حذف هذا المنشور؟')) return
    router.delete(`/posts/${id}`, { preserveState: false })
}

function deleteSelected() {
    if (!confirm(`حذف ${selected.value.size} منشور؟`)) return
    // Delete one at a time — simple approach
    const ids = Array.from(selected.value)
    const next = () => {
        const id = ids.pop()
        if (!id) return
        router.delete(`/posts/${id}`, {
            preserveState: false,
            onSuccess: next,
        })
    }
    next()
}

// ── Formatting helpers ───────────────────────────────────────
const STATUS_META: Record<string, { label: string; badge: string }> = {
    published: { label: 'منشور',  badge: 'badge-success' },
    scheduled: { label: 'مجدول',  badge: 'badge-info' },
    draft:     { label: 'مسودة',  badge: 'badge-neutral' },
    failed:    { label: 'فشل',    badge: 'badge-error' },
}

const PLATFORM_LABELS: Record<string, string> = {
    instagram: 'انستجرام', facebook: 'فيسبوك',
    tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X',
}

const CONTENT_TYPE_LABELS: Record<string, string> = {
    post: 'منشور', reel: 'ريلز', story: 'قصة',
    ad: 'إعلان', thread: 'خيط', snap_caption: 'كابشن',
}

const DIALECT_LABELS: Record<string, string> = {
    fos: 'فصحى', sa: 'سعودي', ae: 'إماراتي',
    kw: 'كويتي', qa: 'قطري', bh: 'بحريني', om: 'عُماني',
}

function formatDate(dt: string | null): string {
    if (!dt) return '—'
    return new Date(dt).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' })
}
function formatTime(dt: string | null): string {
    if (!dt) return ''
    return new Date(dt).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}

// ── Stats (computed from current page data) ──────────────────
const stats = computed(() => {
    const d = props.posts.data
    return {
        total:     props.posts.total,
        published: d.filter(p => p.status === 'published').length,
        scheduled: d.filter(p => p.status === 'scheduled').length,
        drafts:    d.filter(p => p.status === 'draft').length,
    }
})
</script>

<template>
    <AppLayout title="سجل المحتوى">
        <div class="stack-lg">
            <!-- Header -->
            <div class="history-hero">
                <div>
                    <h2 style="margin:0;font-size:22px;font-weight:800;letter-spacing:-0.01em">سجل المحتوى</h2>
                    <p style="margin:6px 0 0;color:var(--text-muted);font-size:14px">
                        كل ما أُنشئ، جُدوِل، أو نُشر — مع الفلاتر والبحث.
                    </p>
                </div>
                <div class="row-sm">
                    <a href="/generate" class="btn btn-primary btn-sm">
                        <Icon name="sparkle" :size="14" /> توليد محتوى
                    </a>
                </div>
            </div>

            <!-- KPI strip -->
            <div class="history-kpis">
                <div class="history-kpi">
                    <div class="history-kpi-label">إجمالي</div>
                    <div class="history-kpi-value">{{ posts.total.toLocaleString('ar-SA') }}</div>
                </div>
                <div class="history-kpi">
                    <div class="history-kpi-label"><span class="dot dot-success" /> منشور</div>
                    <div class="history-kpi-value">{{ stats.published }}</div>
                </div>
                <div class="history-kpi">
                    <div class="history-kpi-label"><span class="dot dot-info" /> مجدول</div>
                    <div class="history-kpi-value">{{ stats.scheduled }}</div>
                </div>
                <div class="history-kpi">
                    <div class="history-kpi-label">مسودات</div>
                    <div class="history-kpi-value">{{ stats.drafts }}</div>
                </div>
            </div>

            <!-- Filters + Table -->
            <div class="card">
                <div class="history-filters">
                    <!-- Search -->
                    <div class="history-search">
                        <Icon name="search" :size="15" style="color:var(--text-muted)" />
                        <input
                            v-model="search"
                            placeholder="ابحث في المحتوى..."
                            @input="applyFilters"
                        />
                        <button v-if="search" @click="search = ''; applyFilters()">
                            <Icon name="x" :size="13" />
                        </button>
                    </div>

                    <!-- Status -->
                    <select class="select" v-model="status" @change="applyFilters">
                        <option value="all">كل الحالات</option>
                        <option value="published">منشور</option>
                        <option value="scheduled">مجدول</option>
                        <option value="draft">مسودة</option>
                        <option value="failed">فشل</option>
                    </select>

                    <!-- Platform -->
                    <select class="select" v-model="platform" @change="applyFilters">
                        <option value="all">كل المنصات</option>
                        <option value="instagram">انستجرام</option>
                        <option value="facebook">فيسبوك</option>
                        <option value="tiktok">تيك توك</option>
                        <option value="snapchat">سناب شات</option>
                        <option value="x">X (تويتر)</option>
                    </select>

                    <!-- Content type -->
                    <select class="select" v-model="contentType" @change="applyFilters">
                        <option value="all">كل الأنواع</option>
                        <option value="post">منشور</option>
                        <option value="reel">ريلز</option>
                        <option value="story">قصة</option>
                        <option value="ad">إعلان</option>
                        <option value="thread">خيط</option>
                        <option value="snap_caption">كابشن</option>
                    </select>

                    <!-- Reset -->
                    <button
                        v-if="activeFilterCount > 0"
                        class="btn btn-sm btn-ghost"
                        @click="resetFilters"
                    >
                        <Icon name="x" :size="13" /> مسح ({{ activeFilterCount }})
                    </button>

                    <div style="flex:1" />

                    <!-- View toggle -->
                    <div class="segmented" style="height:38px">
                        <button :data-active="view === 'table'" @click="view = 'table'">
                            <Icon name="menu" :size="14" />
                        </button>
                        <button :data-active="view === 'grid'" @click="view = 'grid'">
                            <Icon name="image" :size="14" />
                        </button>
                    </div>
                </div>

                <!-- Bulk bar -->
                <div v-if="selected.size > 0" class="history-bulk-bar">
                    <span style="font-size:13px;font-weight:600">
                        محدد {{ selected.size }} من {{ posts.data.length }}
                    </span>
                    <div class="row-sm" style="margin-right:auto">
                        <button class="btn btn-sm btn-danger" @click="deleteSelected">
                            <Icon name="trash" :size="13" /> حذف
                        </button>
                        <button class="btn btn-icon btn-icon-sm btn-ghost" @click="selected = new Set()">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="posts.data.length === 0"
                    style="padding:60px;text-align:center;color:var(--text-muted)"
                >
                    <Icon name="sparkle" :size="32" style="opacity:0.3;margin-bottom:12px" />
                    <p style="margin:0;font-size:14px">لا توجد منشورات — جرّب تعديل الفلاتر أو توليد محتوى جديد.</p>
                </div>

                <!-- Table view -->
                <template v-else-if="view === 'table'">
                    <div class="table-scroll">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th style="width:36px">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            :indeterminate="someSelected"
                                            @change="toggleAll"
                                            style="accent-color:var(--accent)"
                                        />
                                    </th>
                                    <th>المحتوى</th>
                                    <th>النوع</th>
                                    <th>المنصة</th>
                                    <th>اللهجة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="post in posts.data"
                                    :key="post.id"
                                    :data-selected="selected.has(post.id)"
                                >
                                    <td>
                                        <input
                                            type="checkbox"
                                            :checked="selected.has(post.id)"
                                            @change="toggleOne(post.id)"
                                            style="accent-color:var(--accent)"
                                        />
                                    </td>
                                    <td>
                                        <div class="history-title-btn" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:600">
                                            {{ post.content.slice(0, 60) }}…
                                        </div>
                                        <div v-if="post.metadata?.error" style="font-size:11px;color:var(--error);margin-top:2px">
                                            {{ post.metadata.error.slice(0, 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral" style="font-size:11px">
                                            {{ CONTENT_TYPE_LABELS[post.content_type] ?? post.content_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-sm" style="align-items:center">
                                            <Icon :name="post.platform" :size="14" />
                                            <span style="font-size:12px">{{ PLATFORM_LABELS[post.platform] ?? post.platform }}</span>
                                        </div>
                                    </td>
                                    <td style="font-size:12px;color:var(--text-secondary)">
                                        {{ DIALECT_LABELS[post.dialect] ?? post.dialect }}
                                    </td>
                                    <td>
                                        <div class="mono" style="font-size:12px;color:var(--text-secondary)">
                                            {{ formatDate(post.scheduled_for ?? post.published_at ?? post.created_at) }}
                                        </div>
                                        <div class="mono" style="font-size:11px;color:var(--text-muted)">
                                            {{ formatTime(post.scheduled_for ?? post.published_at) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span :class="['badge', STATUS_META[post.status]?.badge ?? 'badge-neutral']">
                                            {{ STATUS_META[post.status]?.label ?? post.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-sm">
                                            <a
                                                href="/generate"
                                                class="btn btn-icon btn-icon-sm btn-ghost"
                                                title="إعادة استخدام"
                                            >
                                                <Icon name="copy" :size="14" />
                                            </a>
                                            <button
                                                class="btn btn-icon btn-icon-sm btn-ghost"
                                                style="color:var(--error)"
                                                title="حذف"
                                                @click="deletePost(post.id)"
                                            >
                                                <Icon name="trash" :size="14" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <!-- Grid view -->
                <template v-else>
                    <div class="history-grid">
                        <div
                            v-for="post in posts.data"
                            :key="post.id"
                            class="history-card"
                        >
                            <div style="padding:14px">
                                <div class="row-sm" style="justify-content:space-between;margin-bottom:8px">
                                    <span :class="['badge', STATUS_META[post.status]?.badge ?? 'badge-neutral']">
                                        {{ STATUS_META[post.status]?.label ?? post.status }}
                                    </span>
                                    <Icon :name="post.platform" :size="14" />
                                </div>
                                <div style="font-weight:700;font-size:13px;margin-bottom:4px;line-height:1.4">
                                    {{ post.content.slice(0, 80) }}…
                                </div>
                                <div class="mono" style="font-size:11px;color:var(--text-muted)">
                                    {{ formatDate(post.scheduled_for ?? post.created_at) }}
                                </div>
                            </div>
                            <div style="padding:0 14px 14px;display:flex;gap:8px">
                                <button
                                    class="btn btn-sm btn-ghost"
                                    style="flex:1;color:var(--error)"
                                    @click="deletePost(post.id)"
                                >
                                    <Icon name="trash" :size="13" /> حذف
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Pagination -->
                <div v-if="posts.last_page > 1" class="history-pagination">
                    <template v-for="link in posts.links" :key="link.label">
                        <a
                            v-if="link.url"
                            :href="link.url"
                            class="btn btn-sm"
                            :class="link.active ? 'btn-primary' : 'btn-ghost'"
                            v-html="link.label"
                            @click.prevent="router.get(link.url)"
                        />
                        <span v-else class="btn btn-sm btn-ghost" style="opacity:0.4;cursor:default" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.history-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}
.history-kpis {
    display: flex;
    gap: 0;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    overflow: hidden;
}
.history-kpi {
    flex: 1;
    padding: 16px 20px;
    border-left: 1px solid var(--border-subtle);
    min-width: 0;
}
.history-kpi:last-child { border-left: none; }
.history-kpi-label { font-size: 12px; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 5px; margin-bottom: 6px; }
.history-kpi-value { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; }

.history-filters {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    flex-wrap: wrap;
}
.history-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--bg-muted);
    border-radius: var(--radius-sm);
    min-width: 220px;
}
.history-search input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
    color: var(--text-primary);
    flex: 1;
    font-family: var(--font-arabic);
    direction: rtl;
}
.history-bulk-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    background: color-mix(in oklab, var(--accent) 8%, transparent);
    border-bottom: 1px solid var(--border-subtle);
}
.table-scroll { overflow-x: auto; }
.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.history-table th {
    padding: 12px 16px;
    text-align: right;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-subtle);
    white-space: nowrap;
}
.history-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    vertical-align: middle;
}
.history-table tr:last-child td { border-bottom: none; }
.history-table tr[data-selected="true"] td { background: color-mix(in oklab, var(--accent) 5%, transparent); }
.history-table tr:hover td { background: var(--bg-muted); }

.history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    padding: 20px;
}
.history-card {
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--border-subtle);
}
.history-pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    border-top: 1px solid var(--border-subtle);
}
.select {
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-subtle);
    background: var(--bg-surface);
    color: var(--text-primary);
    font-size: 13px;
    font-family: var(--font-arabic);
    outline: none;
    cursor: pointer;
}
</style>

<script setup lang="ts">
// SCH-11: posts history
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface SocialAccount { id: number; provider: string; account_name: string }
interface Post {
    id: number; content: string; platform: string; content_type: string
    dialect: string; status: string; scheduled_for: string | null
    published_at: string | null; created_at: string
    social_account: SocialAccount | null; metadata: Record<string, string> | null
}
interface Paginator {
    data: Post[]; current_page: number; last_page: number
    per_page: number; total: number; from: number | null; to: number | null
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    posts: Paginator
    filters: { status?: string; platform?: string; content_type?: string; search?: string }
    stats: { total: number; published: number; scheduled: number; drafts: number; failed: number; this_month: number }
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
    search.value = ''; status.value = 'all'
    platform.value = 'all'; contentType.value = 'all'
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
    selected.value = selected.value.size === props.posts.data.length
        ? new Set()
        : new Set(props.posts.data.map(p => p.id))
}
const allSelected  = computed(() => props.posts.data.length > 0 && selected.value.size === props.posts.data.length)
const someSelected = computed(() => selected.value.size > 0 && selected.value.size < props.posts.data.length)

// ── Single delete modal ──────────────────────────────────────
const deleteModal  = ref(false)
const postToDelete = ref<Post | null>(null)
const deleting     = ref(false)

function openDelete(post: Post) { postToDelete.value = post; deleteModal.value = true }
function cancelDelete()         { deleteModal.value = false; postToDelete.value = null }
function confirmDelete() {
    if (!postToDelete.value) return
    deleting.value = true
    router.delete(`/posts/${postToDelete.value.id}`, {
        preserveState: false,
        onFinish: () => { deleting.value = false; cancelDelete() },
    })
}

// ── Bulk delete modal ────────────────────────────────────────
const bulkDeleteModal = ref(false)
const bulkDeleting    = ref(false)

function openBulkDelete()   { bulkDeleteModal.value = true }
function cancelBulkDelete() { bulkDeleteModal.value = false }
function confirmBulkDelete() {
    bulkDeleting.value = true
    const ids = Array.from(selected.value)
    const next = () => {
        const id = ids.pop()
        if (!id) {
            bulkDeleting.value = false
            bulkDeleteModal.value = false
            selected.value = new Set()
            return
        }
        router.delete(`/posts/${id}`, { preserveState: false, onSuccess: next })
    }
    next()
}

// ── Lookups ──────────────────────────────────────────────────
const PLATFORM_ICONS: Record<string, string> = {
    instagram: 'instagram', facebook: 'facebook',
    tiktok: 'tiktok', snapchat: 'snapchat', x: 'x-brand',
}
const PLATFORM_LABELS: Record<string, string> = {
    instagram: 'انستجرام', facebook: 'فيسبوك',
    tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X',
}
const PLATFORM_COLORS: Record<string, string> = {
    instagram: '#E1306C', facebook: '#1877F2',
    tiktok: '#010101', snapchat: '#FFFC00', x: '#000000',
}
const CONTENT_TYPE_LABELS: Record<string, string> = {
    post: 'منشور', reel: 'ريلز', story: 'قصة',
    ad: 'إعلان', thread: 'خيط', snap_caption: 'كابشن',
}
const DIALECT_LABELS: Record<string, string> = {
    fos: 'فصحى', sa: 'سعودي', ae: 'إماراتي',
    kw: 'كويتي', qa: 'قطري', bh: 'بحريني', om: 'عُماني',
}
const STATUS_META: Record<string, { label: string; badge: string; dot: string }> = {
    published: { label: 'منشور',  badge: 'badge-success', dot: 'var(--success)' },
    scheduled: { label: 'مجدول',  badge: 'badge-info',    dot: 'var(--info)' },
    draft:     { label: 'مسودة',  badge: 'badge-neutral', dot: 'var(--text-muted)' },
    failed:    { label: 'فشل',    badge: 'badge-error',   dot: 'var(--error)' },
}

function platformIcon(p: string)  { return PLATFORM_ICONS[p]  ?? 'instagram' }
function platformLabel(p: string) { return PLATFORM_LABELS[p] ?? p }
function platformColor(p: string) { return PLATFORM_COLORS[p] ?? '#888' }

function formatDate(dt: string | null): string {
    if (!dt) return '—'
    return new Date(dt).toLocaleDateString('ar-SA', { month: 'short', day: 'numeric', year: 'numeric' })
}
function formatTime(dt: string | null): string {
    if (!dt) return ''
    return new Date(dt).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}

const publishedPct = computed(() =>
    props.stats.total > 0
        ? Math.round((props.stats.published / props.stats.total) * 100)
        : 0
)
</script>

<template>
    <AppLayout title="سجل المحتوى">

        <!-- ── Single Delete Modal ── -->
        <Teleport to="body">
            <div v-if="deleteModal" class="del-backdrop" @click.self="cancelDelete">
                <div class="del-modal">
                    <div class="del-head">
                        <h3>تأكيد الحذف</h3>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="cancelDelete">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>
                    <div class="del-body" v-if="postToDelete">
                        <p style="margin:0 0 12px;font-size:13px;color:var(--text-muted)">
                            هل أنت متأكد من حذف هذا المنشور؟ لا يمكن التراجع عن هذا الإجراء.
                        </p>
                        <div class="del-preview">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                                <Icon :name="platformIcon(postToDelete.platform)" :size="13" />
                                <span style="font-size:12px;color:var(--text-muted)">{{ platformLabel(postToDelete.platform) }}</span>
                                <span :class="['badge', STATUS_META[postToDelete.status]?.badge ?? 'badge-neutral']" style="font-size:10px">
                                    {{ STATUS_META[postToDelete.status]?.label ?? postToDelete.status }}
                                </span>
                            </div>
                            <p style="margin:0;font-size:13px;color:var(--text-primary);line-height:1.6">
                                {{ postToDelete.content.slice(0, 100) }}{{ postToDelete.content.length > 100 ? '…' : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="del-foot">
                        <button class="btn btn-ghost btn-sm" @click="cancelDelete">إلغاء</button>
                        <button class="btn btn-danger btn-sm" :disabled="deleting" @click="confirmDelete">
                            <Icon name="trash" :size="13" />
                            {{ deleting ? 'جارٍ الحذف…' : 'حذف المنشور' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Bulk Delete Modal ── -->
        <Teleport to="body">
            <div v-if="bulkDeleteModal" class="del-backdrop" @click.self="cancelBulkDelete">
                <div class="del-modal">
                    <div class="del-head">
                        <h3>حذف المنشورات المحددة</h3>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="cancelBulkDelete">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>
                    <div class="del-body">
                        <div class="bulk-del-icon">
                            <Icon name="trash" :size="28" style="color:var(--error)" />
                        </div>
                        <p style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);text-align:center">
                            حذف {{ selected.size }} منشور{{ selected.size > 2 ? 'ات' : selected.size === 2 ? 'ين' : '' }}
                        </p>
                        <p style="margin:8px 0 0;font-size:13px;color:var(--text-muted);text-align:center;line-height:1.6">
                            هذا الإجراء لا يمكن التراجع عنه. سيتم حذف جميع المنشورات المحددة نهائياً.
                        </p>
                    </div>
                    <div class="del-foot">
                        <button class="btn btn-ghost btn-sm" @click="cancelBulkDelete" :disabled="bulkDeleting">إلغاء</button>
                        <button class="btn btn-danger btn-sm" :disabled="bulkDeleting" @click="confirmBulkDelete">
                            <Icon name="trash" :size="13" />
                            {{ bulkDeleting ? 'جارٍ الحذف…' : `حذف ${selected.size} منشور` }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <div class="stack-lg">

            <!-- ── Header ── -->
            <div class="history-hero">
                <div>
                    <h2 style="margin:0;font-size:22px;font-weight:800;letter-spacing:-0.02em">سجل المحتوى</h2>
                    <p style="margin:5px 0 0;color:var(--text-muted);font-size:14px">
                        كل ما أُنشئ، جُدوِل، أو نُشر
                    </p>
                </div>
                <a href="/generate" class="btn btn-primary btn-sm">
                    <Icon name="sparkle" :size="14" /> توليد محتوى
                </a>
            </div>

            <!-- ── KPI Strip ── -->
            <div class="kpi-strip">

                <div class="kpi-card">
                    <div class="kpi-label">إجمالي المحتوى</div>
                    <div class="kpi-value">{{ stats.total.toLocaleString('ar-SA') }}</div>
                    <div v-if="stats.this_month > 0" class="kpi-delta delta-up">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                        {{ stats.this_month }} هذا الشهر
                    </div>
                    <div v-else class="kpi-delta" style="color:var(--text-muted)">إجمالي المنشورات</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">
                        <span class="kpi-dot" style="background:var(--success)" />
                        منشور
                    </div>
                    <div class="kpi-value" style="color:var(--success)">{{ stats.published.toLocaleString('ar-SA') }}</div>
                    <div class="kpi-delta" style="color:var(--text-muted)">{{ publishedPct }}% من الإجمالي</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">
                        <span class="kpi-dot" style="background:var(--info)" />
                        مجدول
                    </div>
                    <div class="kpi-value" style="color:var(--info)">{{ stats.scheduled.toLocaleString('ar-SA') }}</div>
                    <div class="kpi-delta" style="color:var(--text-muted)">قيد الانتظار</div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-label">
                        <span class="kpi-dot" style="background:var(--warning)" />
                        مسودات
                    </div>
                    <div class="kpi-value" style="color:var(--warning)">{{ stats.drafts.toLocaleString('ar-SA') }}</div>
                    <div class="kpi-delta" style="color:var(--text-muted)">غير منشورة</div>
                </div>

                <div class="kpi-card" :class="{ 'kpi-card--danger': stats.failed > 0 }">
                    <div class="kpi-label">
                        <span class="kpi-dot" style="background:var(--error)" />
                        فشل النشر
                    </div>
                    <div class="kpi-value" :style="stats.failed > 0 ? 'color:var(--error)' : ''">
                        {{ stats.failed.toLocaleString('ar-SA') }}
                    </div>
                    <div class="kpi-delta" style="color:var(--text-muted)">{{ stats.failed > 0 ? 'تحتاج مراجعة' : 'لا أخطاء' }}</div>
                </div>

            </div>

            <!-- ── Filter Bar + Table Card ── -->
            <div class="card" style="padding:0;overflow:hidden">

                <!-- ── Filter bar (single compact row) ── -->
                <div class="filter-bar">

                    <!-- Search -->
                    <div class="fsearch">
                        <Icon name="search" :size="14" style="color:var(--text-muted);flex-shrink:0" />
                        <input v-model="search" placeholder="ابحث بالعنوان أو المحتوى..." @input="applyFilters" />
                        <button v-if="search" class="fsearch-clear" @click="search = ''; applyFilters()">
                            <Icon name="x" :size="11" />
                        </button>
                    </div>

                    <!-- Status -->
                    <select class="fselect" v-model="status" @change="applyFilters">
                        <option value="all">كل الحالات</option>
                        <option value="published">منشور</option>
                        <option value="scheduled">مجدول</option>
                        <option value="draft">مسودة</option>
                        <option value="failed">فشل</option>
                    </select>

                    <!-- Platform -->
                    <select class="fselect" v-model="platform" @change="applyFilters">
                        <option value="all">المنصة</option>
                        <option value="instagram">انستجرام</option>
                        <option value="facebook">فيسبوك</option>
                        <option value="tiktok">تيك توك</option>
                        <option value="snapchat">سناب شات</option>
                        <option value="x">X</option>
                    </select>

                    <!-- Content type -->
                    <select class="fselect" v-model="contentType" @change="applyFilters">
                        <option value="all">النوع</option>
                        <option value="post">منشور</option>
                        <option value="reel">ريلز</option>
                        <option value="story">قصة</option>
                        <option value="ad">إعلان</option>
                        <option value="thread">خيط</option>
                        <option value="snap_caption">كابشن</option>
                    </select>

                    <!-- Reset -->
                    <button v-if="activeFilterCount > 0" class="freset" @click="resetFilters">
                        <Icon name="x" :size="11" /> مسح
                    </button>

                    <div style="flex:1" />

                    <!-- Bulk actions (when selected) -->
                    <template v-if="selected.size > 0">
                        <span class="bulk-count">
                            <Icon name="check" :size="12" style="color:var(--accent)" />
                            تم تحديد {{ selected.size }}
                        </span>
                        <button class="btn btn-danger btn-sm" @click="openBulkDelete">
                            <Icon name="trash" :size="12" /> حذف المحدد
                        </button>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="selected = new Set()" title="إلغاء التحديد">
                            <Icon name="x" :size="13" />
                        </button>
                        <div class="fbar-sep" />
                    </template>

                    <!-- View toggle -->
                    <div class="view-toggle">
                        <button :data-active="view === 'table'" @click="view = 'table'" title="جدول">
                            <Icon name="menu" :size="14" />
                        </button>
                        <button :data-active="view === 'grid'" @click="view = 'grid'" title="بطاقات">
                            <Icon name="image" :size="14" />
                        </button>
                    </div>
                </div>

                <!-- ── Empty ── -->
                <div v-if="posts.data.length === 0" class="empty-state">
                    <div class="empty-icon">
                        <Icon name="sparkle" :size="28" />
                    </div>
                    <p style="margin:0;font-size:14px;font-weight:600;color:var(--text-primary)">لا توجد نتائج</p>
                    <p style="margin:4px 0 0;font-size:13px;color:var(--text-muted)">
                        جرّب تعديل الفلاتر أو
                        <a href="/generate" style="color:var(--accent);font-weight:600">توليد محتوى جديد</a>
                    </p>
                </div>

                <!-- ── Table view ── -->
                <template v-else-if="view === 'table'">
                    <div class="table-scroll">
                        <table class="posts-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;padding-right:20px">
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
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="post in posts.data"
                                    :key="post.id"
                                    :data-selected="selected.has(post.id)"
                                >
                                    <td style="padding-right:20px">
                                        <input
                                            type="checkbox"
                                            :checked="selected.has(post.id)"
                                            @change="toggleOne(post.id)"
                                            style="accent-color:var(--accent)"
                                        />
                                    </td>

                                    <td>
                                        <div class="post-content-cell">
                                            {{ post.content.slice(0, 65) }}{{ post.content.length > 65 ? '…' : '' }}
                                        </div>
                                        <div v-if="post.metadata?.error" class="post-error-hint">
                                            {{ post.metadata.error.slice(0, 55) }}…
                                        </div>
                                    </td>

                                    <td>
                                        <span class="type-badge">
                                            {{ CONTENT_TYPE_LABELS[post.content_type] ?? post.content_type }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="platform-cell">
                                            <span class="platform-dot" :style="{ background: platformColor(post.platform) }" />
                                            <Icon :name="platformIcon(post.platform)" :size="14" />
                                            <span>{{ platformLabel(post.platform) }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="dialect-tag">
                                            {{ DIALECT_LABELS[post.dialect] ?? post.dialect }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="date-primary">
                                            {{ formatDate(post.scheduled_for ?? post.published_at ?? post.created_at) }}
                                        </div>
                                        <div class="date-secondary">
                                            {{ formatTime(post.scheduled_for ?? post.published_at) }}
                                        </div>
                                    </td>

                                    <td>
                                        <span :class="['badge', STATUS_META[post.status]?.badge ?? 'badge-neutral']">
                                            {{ STATUS_META[post.status]?.label ?? post.status }}
                                        </span>
                                    </td>

                                    <td>
                                        <div style="display:flex;gap:4px;justify-content:flex-end">
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
                                                @click="openDelete(post)"
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

                <!-- ── Grid view ── -->
                <template v-else>
                    <div class="posts-grid">
                        <div v-for="post in posts.data" :key="post.id" class="post-card">
                            <div class="post-card-head">
                                <div class="post-card-platform">
                                    <span class="platform-icon-wrap" :style="{ background: platformColor(post.platform) + '18' }">
                                        <Icon :name="platformIcon(post.platform)" :size="14" :style="{ color: platformColor(post.platform) }" />
                                    </span>
                                    <span style="font-size:12px;color:var(--text-muted)">{{ platformLabel(post.platform) }}</span>
                                </div>
                                <span :class="['badge', STATUS_META[post.status]?.badge ?? 'badge-neutral']" style="font-size:10px">
                                    {{ STATUS_META[post.status]?.label ?? post.status }}
                                </span>
                            </div>
                            <div class="post-card-body">
                                <p class="post-card-text">{{ post.content.slice(0, 90) }}{{ post.content.length > 90 ? '…' : '' }}</p>
                            </div>
                            <div class="post-card-foot">
                                <div>
                                    <span class="type-badge">{{ CONTENT_TYPE_LABELS[post.content_type] ?? post.content_type }}</span>
                                    <span class="dialect-tag" style="margin-right:4px">{{ DIALECT_LABELS[post.dialect] ?? post.dialect }}</span>
                                </div>
                                <div style="display:flex;gap:4px">
                                    <a href="/generate" class="btn btn-icon btn-icon-sm btn-ghost" title="إعادة استخدام">
                                        <Icon name="copy" :size="13" />
                                    </a>
                                    <button
                                        class="btn btn-icon btn-icon-sm btn-ghost"
                                        style="color:var(--error)"
                                        @click="openDelete(post)"
                                    >
                                        <Icon name="trash" :size="13" />
                                    </button>
                                </div>
                            </div>
                            <div class="post-card-date">
                                <Icon name="calendar" :size="11" />
                                {{ formatDate(post.scheduled_for ?? post.published_at ?? post.created_at) }}
                                <span v-if="formatTime(post.scheduled_for ?? post.published_at)">
                                    · {{ formatTime(post.scheduled_for ?? post.published_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ── Pagination ── -->
                <div v-if="posts.last_page > 1" class="pagination-bar">
                    <template v-for="link in posts.links" :key="link.label">
                        <a
                            v-if="link.url"
                            :href="link.url"
                            class="btn btn-sm"
                            :class="link.active ? 'btn-primary' : 'btn-ghost'"
                            v-html="link.label"
                            @click.prevent="router.get(link.url)"
                        />
                        <span v-else class="btn btn-sm btn-ghost" style="opacity:.35;cursor:default" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Layout ── */
.history-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }

/* ── KPI Strip ── */
.kpi-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
}
@media (max-width: 1100px) { .kpi-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px)  { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }

.kpi-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    display: flex; flex-direction: column; gap: 4px;
    transition: border-color .15s;
}
.kpi-card:hover { border-color: var(--border-default); }
.kpi-card--danger { border-color: color-mix(in oklab, var(--error) 25%, transparent); }

.kpi-label {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .04em;
}
.kpi-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.kpi-value { font-size: 26px; font-weight: 800; letter-spacing: -0.03em; line-height: 1.1; color: var(--text-primary); }
.kpi-delta {
    display: flex; align-items: center; gap: 3px;
    font-size: 11px; font-weight: 500;
}
.delta-up { color: var(--success); }

/* ── Filter bar ── */
.filter-bar {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    flex-wrap: wrap;
    background: var(--bg-surface);
}
.fsearch {
    display: flex; align-items: center; gap: 7px;
    padding: 0 11px; height: 36px;
    background: var(--bg-muted);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    flex: 1; min-width: 200px; max-width: 320px;
    transition: border-color .15s, background .15s;
}
.fsearch:focus-within { border-color: var(--accent); background: var(--bg-surface); }
.fsearch input {
    border: none; background: transparent; outline: none;
    font-size: 13px; color: var(--text-primary); flex: 1;
    font-family: var(--font-arabic); direction: rtl;
}
.fsearch-clear {
    width: 17px; height: 17px; border-radius: 50%;
    border: none; background: var(--text-muted); color: #fff;
    cursor: pointer; display: grid; place-items: center;
    opacity: .65; transition: opacity .12s; flex-shrink: 0;
}
.fsearch-clear:hover { opacity: 1; }

.fselect {
    height: 36px; padding: 0 10px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    background: var(--bg-surface);
    color: var(--text-secondary);
    font-size: 13px; font-family: var(--font-arabic);
    outline: none; cursor: pointer;
    transition: border-color .15s, color .15s;
}
.fselect:hover { border-color: var(--border-default); color: var(--text-primary); }
.fselect:focus { border-color: var(--accent); }

.freset {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 0 10px; height: 36px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    background: transparent;
    font-size: 12px; font-weight: 600;
    color: var(--text-muted); cursor: pointer;
    font-family: var(--font-arabic);
    transition: all .12s;
}
.freset:hover { background: var(--bg-muted); color: var(--error); border-color: var(--error); }

.bulk-count {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 13px; font-weight: 600; color: var(--text-secondary);
    padding: 0 8px;
}
.fbar-sep { width: 1px; height: 24px; background: var(--border-subtle); flex-shrink: 0; }

.view-toggle {
    display: inline-flex; padding: 2px;
    background: var(--bg-muted); border-radius: var(--radius-sm); gap: 1px;
}
.view-toggle button {
    width: 32px; height: 32px;
    border-radius: calc(var(--radius-sm) - 2px);
    display: grid; place-items: center;
    color: var(--text-muted); cursor: pointer;
    border: none; background: transparent;
    transition: all .12s;
}
.view-toggle button[data-active="true"] {
    background: var(--bg-surface); color: var(--text-primary);
    box-shadow: 0 1px 3px rgba(0,0,0,.1);
}
.view-toggle button:hover:not([data-active="true"]) { color: var(--text-primary); }

/* ── Empty ── */
.empty-state {
    padding: 64px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.empty-icon {
    width: 60px; height: 60px; border-radius: 50%;
    background: var(--bg-muted); display: grid; place-items: center;
    color: var(--text-muted); margin-bottom: 4px;
}

/* ── Table ── */
.table-scroll { overflow-x: auto; }
.posts-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.posts-table th {
    padding: 11px 14px; text-align: right;
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 2px solid var(--border-subtle);
    white-space: nowrap; background: var(--bg-muted);
}
.posts-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border-subtle);
    vertical-align: middle;
}
.posts-table tr:last-child td { border-bottom: none; }
.posts-table tr[data-selected="true"] td { background: color-mix(in oklab, var(--accent) 5%, transparent); }
.posts-table tr:hover td { background: var(--bg-muted); }

.post-content-cell {
    font-size: 13px; font-weight: 600; color: var(--text-primary);
    max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.post-error-hint { font-size: 11px; color: var(--error); margin-top: 3px; }
.platform-cell { display: flex; align-items: center; gap: 6px; }
.platform-dot  { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.type-badge {
    display: inline-block; padding: 2px 8px; border-radius: 12px;
    font-size: 11px; font-weight: 600;
    background: var(--bg-muted); color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
}
.dialect-tag {
    display: inline-block; padding: 2px 7px; border-radius: 10px;
    font-size: 11px; font-weight: 500;
    color: var(--text-muted); background: transparent;
    border: 1px dashed var(--border-subtle);
}
.date-primary   { font-size: 12px; color: var(--text-secondary); font-weight: 500; }
.date-secondary { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ── Grid ── */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px; padding: 20px;
}
.post-card {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md); overflow: hidden;
    display: flex; flex-direction: column;
    transition: box-shadow .15s, border-color .15s;
}
.post-card:hover { box-shadow: var(--shadow-md); border-color: var(--border-default); }
.post-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 14px 8px;
}
.post-card-platform { display: flex; align-items: center; gap: 7px; }
.platform-icon-wrap { width: 26px; height: 26px; border-radius: 7px; display: grid; place-items: center; flex-shrink: 0; }
.post-card-body { padding: 4px 14px 12px; flex: 1; }
.post-card-text { margin: 0; font-size: 13px; line-height: 1.6; color: var(--text-primary); font-weight: 500; }
.post-card-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; border-top: 1px solid var(--border-subtle); background: var(--bg-muted);
}
.post-card-date {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 14px; font-size: 11px; color: var(--text-muted);
    border-top: 1px solid var(--border-subtle);
}

/* ── Pagination ── */
.pagination-bar {
    display: flex; justify-content: center; gap: 4px;
    padding: 16px; border-top: 1px solid var(--border-subtle);
}

/* ── Modals ── */
.del-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 500; display: grid; place-items: center; padding: 20px;
}
.del-modal {
    background: var(--bg-surface); border-radius: var(--radius-lg);
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    width: 420px; max-width: 100%; overflow: hidden;
}
.del-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);
}
.del-head h3 { margin: 0; font-size: 15px; font-weight: 700; }
.del-body { padding: 20px; }
.del-preview {
    background: var(--bg-muted); border-radius: var(--radius-md);
    padding: 12px 14px; border: 1px solid var(--border-subtle);
}
.del-foot {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 14px 20px; border-top: 1px solid var(--border-subtle);
    background: var(--bg-muted);
}
.bulk-del-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: color-mix(in oklab, var(--error) 10%, transparent);
    display: grid; place-items: center;
    margin: 0 auto 16px;
}
</style>

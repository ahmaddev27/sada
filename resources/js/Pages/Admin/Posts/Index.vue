<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface Post {
    id: number
    content: string
    platform: string
    content_type: string | null
    dialect: string | null
    hashtags: string[] | null
    status: string
    scheduled_for: string | null
    published_at: string | null
    created_at: string
    workspace: { id: number; name: string } | null
    user: { id: number; name: string } | null
    social_account: { id: number; provider: string } | null
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

// Detail modal
const selected = ref<Post | null>(null)

function applyFilter() {
    router.get('/admin/posts', { search: search.value, status: status.value, platform: platform.value }, { preserveState: true, replace: true })
}

const STATUS_META: Record<string, { label: string; cls: string }> = {
    draft:     { label: 'مسودة',  cls: 'status--gray'  },
    scheduled: { label: 'مجدول', cls: 'status--blue'  },
    published: { label: 'منشور', cls: 'status--green' },
    failed:    { label: 'فشل',   cls: 'status--red'   },
}

const PLATFORM_META: Record<string, { label: string; cls: string }> = {
    instagram: { label: 'انستجرام', cls: 'plat--pink'   },
    facebook:  { label: 'فيسبوك',   cls: 'plat--blue'   },
    tiktok:    { label: 'تيك توك',  cls: 'plat--dark'   },
    snapchat:  { label: 'سناب شات', cls: 'plat--yellow' },
    twitter:   { label: 'X',         cls: 'plat--gray'   },
    x:         { label: 'X',         cls: 'plat--gray'   },
    linkedin:  { label: 'لينكدإن',   cls: 'plat--linkedin' },
}

const DIALECT_LABELS: Record<string, string> = {
    saudi:    'سعودي',
    gulf:     'خليجي',
    egyptian: 'مصري',
    levantine:'شامي',
    moroccan: 'مغربي',
    msa:      'فصحى',
}

const CONTENT_TYPE_LABELS: Record<string, string> = {
    promotional: 'ترويجي',
    educational: 'تعليمي',
    entertaining:'ترفيهي',
    seasonal:    'موسمي',
    ad:          'إعلان',
    organic:     'عضوي',
}

function statusMeta(s: string) { return STATUS_META[s] ?? { label: s, cls: 'status--gray' } }
function platformMeta(p: string) { return PLATFORM_META[p] ?? { label: p, cls: 'plat--gray' } }
function dialectLabel(d: string | null) { return d ? (DIALECT_LABELS[d] ?? d) : null }
function contentTypeLabel(t: string | null) { return t ? (CONTENT_TYPE_LABELS[t] ?? t) : null }

function dt(iso: string | null) {
    return iso ? new Date(iso).toLocaleString('ar-SA', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
}
function dtShort(iso: string | null) {
    return iso ? new Date(iso).toLocaleDateString('ar-SA', { month: 'short', day: 'numeric', year: '2-digit' }) : '—'
}
function truncate(text: string, len = 80) {
    return text.length > len ? text.slice(0, len) + '…' : text
}
</script>

<template>
    <AdminLayout>
        <div class="posts-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">المنشورات</h1>
                    <p class="page-sub">جميع منشورات المنصة عبر كل Workspaces</p>
                </div>
                <span class="total-chip">{{ posts.total }} منشور</span>
            </div>

            <!-- Status tabs -->
            <div class="tabs-bar">
                <button
                    v-for="tab in [
                        { key: '', label: 'الكل', count: counts.all, cls: '' },
                        { key: 'scheduled', label: 'مجدول', count: counts.scheduled, cls: 'tc--blue' },
                        { key: 'published', label: 'منشور', count: counts.published, cls: 'tc--green' },
                        { key: 'failed',    label: 'فشل',   count: counts.failed,    cls: 'tc--red' },
                        { key: 'draft',     label: 'مسودة', count: counts.draft,     cls: 'tc--gray' },
                    ]"
                    :key="tab.key"
                    :class="['tab', { 'tab--active': status === tab.key }]"
                    @click="status = tab.key; applyFilter()"
                >
                    {{ tab.label }}
                    <span :class="['tab-count', status === tab.key ? '' : tab.cls]">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input
                    v-model="search"
                    class="f-input"
                    placeholder="بحث في المحتوى..."
                    @keyup.enter="applyFilter"
                />
                <select v-model="platform" class="f-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X (تويتر)</option>
                </select>
                <button class="f-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-shell">
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>المحتوى والتفاصيل</th>
                            <th>Workspace / المستخدم</th>
                            <th>المنصة</th>
                            <th>الحالة</th>
                            <th>التوقيت</th>
                            <th>الإنشاء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="p in posts.data"
                            :key="p.id"
                            class="clickable-row"
                            @click="selected = p"
                        >
                            <!-- ID -->
                            <td class="td-id">{{ p.id }}</td>

                            <!-- Content + meta badges -->
                            <td class="td-content">
                                <div class="content-text">{{ truncate(p.content) }}</div>
                                <div class="content-meta">
                                    <span v-if="contentTypeLabel(p.content_type)" class="meta-badge meta-badge--type">
                                        {{ contentTypeLabel(p.content_type) }}
                                    </span>
                                    <span v-if="dialectLabel(p.dialect)" class="meta-badge meta-badge--dialect">
                                        {{ dialectLabel(p.dialect) }}
                                    </span>
                                    <span v-if="p.hashtags?.length" class="meta-badge meta-badge--hash">
                                        # {{ p.hashtags.length }} وسم
                                    </span>
                                    <span v-if="p.social_account" class="meta-badge meta-badge--account">
                                        {{ p.social_account.provider }}
                                    </span>
                                </div>
                            </td>

                            <!-- Workspace + user -->
                            <td class="td-ws">
                                <div class="ws-name">{{ p.workspace?.name ?? '—' }}</div>
                                <div class="ws-user">{{ p.user?.name ?? '—' }}</div>
                            </td>

                            <!-- Platform -->
                            <td>
                                <span :class="['platform-pill', platformMeta(p.platform).cls]">
                                    {{ platformMeta(p.platform).label }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span :class="['status-pill', statusMeta(p.status).cls]">
                                    <span class="status-dot" />
                                    {{ statusMeta(p.status).label }}
                                </span>
                            </td>

                            <!-- Timing: scheduled_for + published_at -->
                            <td class="td-time">
                                <template v-if="p.status === 'published' && p.published_at">
                                    <div class="time-label time-label--green">نُشر</div>
                                    <div class="time-val">{{ dt(p.published_at) }}</div>
                                </template>
                                <template v-else-if="p.scheduled_for">
                                    <div class="time-label">مجدول لـ</div>
                                    <div class="time-val">{{ dt(p.scheduled_for) }}</div>
                                </template>
                                <span v-else class="td-muted">—</span>
                            </td>

                            <!-- Created at -->
                            <td class="td-muted td-date">{{ dtShort(p.created_at) }}</td>
                        </tr>
                        <tr v-if="!posts.data.length">
                            <td colspan="7" class="empty-row">لا توجد منشورات</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="posts.last_page > 1" class="pagination">
                <template v-for="link in posts.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>

        </div>

        <!-- ── Post detail modal ──────────────────────────── -->
        <Teleport to="body">
            <div v-if="selected" class="modal-backdrop" @click.self="selected = null">
                <div class="modal">
                    <div class="modal-head">
                        <span class="modal-title">تفاصيل المنشور #{{ selected.id }}</span>
                        <button class="modal-close" @click="selected = null">✕</button>
                    </div>
                    <div class="modal-body">

                        <!-- Meta grid -->
                        <div class="modal-meta">
                            <div class="meta-item">
                                <span class="meta-lbl">Workspace</span>
                                <span class="meta-val">{{ selected.workspace?.name ?? '—' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-lbl">المستخدم</span>
                                <span class="meta-val">{{ selected.user?.name ?? '—' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-lbl">المنصة</span>
                                <span :class="['platform-pill', platformMeta(selected.platform).cls]">{{ platformMeta(selected.platform).label }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-lbl">الحالة</span>
                                <span :class="['status-pill', statusMeta(selected.status).cls]">
                                    <span class="status-dot" />
                                    {{ statusMeta(selected.status).label }}
                                </span>
                            </div>
                            <div v-if="contentTypeLabel(selected.content_type)" class="meta-item">
                                <span class="meta-lbl">نوع المحتوى</span>
                                <span class="meta-badge meta-badge--type">{{ contentTypeLabel(selected.content_type) }}</span>
                            </div>
                            <div v-if="dialectLabel(selected.dialect)" class="meta-item">
                                <span class="meta-lbl">اللهجة</span>
                                <span class="meta-badge meta-badge--dialect">{{ dialectLabel(selected.dialect) }}</span>
                            </div>
                            <div v-if="selected.social_account" class="meta-item">
                                <span class="meta-lbl">الحساب المرتبط</span>
                                <span class="meta-badge meta-badge--account">{{ selected.social_account.provider }}</span>
                            </div>
                        </div>

                        <!-- Timing -->
                        <div class="modal-timing">
                            <div class="timing-item">
                                <span class="timing-lbl">مجدول لـ</span>
                                <span class="timing-val">{{ selected.scheduled_for ? dt(selected.scheduled_for) : '—' }}</span>
                            </div>
                            <div v-if="selected.published_at" class="timing-item">
                                <span class="timing-lbl" style="color:#059669">نُشر في</span>
                                <span class="timing-val" style="color:#059669">{{ dt(selected.published_at) }}</span>
                            </div>
                            <div class="timing-item">
                                <span class="timing-lbl">تاريخ الإنشاء</span>
                                <span class="timing-val">{{ dtShort(selected.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Full content -->
                        <div class="modal-section">
                            <div class="modal-section-label">محتوى المنشور</div>
                            <div class="modal-content-box">{{ selected.content }}</div>
                        </div>

                        <!-- Hashtags -->
                        <div v-if="selected.hashtags?.length" class="modal-section">
                            <div class="modal-section-label">الوسوم ({{ selected.hashtags.length }})</div>
                            <div class="hashtags-wrap">
                                <span v-for="tag in selected.hashtags" :key="tag" class="hashtag-chip">
                                    #{{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
/* ── Shell ────────────────────────────────── */
.posts-page { padding: 26px 30px; }

/* ── Header ───────────────────────────────── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 12px; }
.page-title  { font-size: 20px; font-weight: 800; color: var(--text-primary); margin: 0 0 4px; }
.page-sub    { font-size: 12px; color: var(--text-muted); margin: 0; }
.total-chip  { font-size: 12px; background: var(--bg-muted); color: var(--text-muted); padding: 5px 14px; border-radius: 99px; font-weight: 600; white-space: nowrap; }

/* ── Status tabs ──────────────────────────── */
.tabs-bar { display: flex; gap: 6px; margin-bottom: 14px; flex-wrap: wrap; }
.tab {
    display: flex; align-items: center; gap: 7px;
    padding: 7px 14px; border-radius: var(--radius-md);
    font-size: 12px; font-weight: 600;
    border: 1px solid var(--border-default);
    background: var(--bg-surface); color: var(--text-muted);
    cursor: pointer; font-family: var(--font-arabic); transition: all .15s;
}
.tab:hover         { background: var(--bg-muted); color: var(--text-primary); }
.tab--active       { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.tab-count         { font-size: 10px; padding: 1px 7px; border-radius: 99px; font-weight: 700; background: var(--bg-muted); color: var(--text-muted); }
.tab--active .tab-count { background: rgba(255,255,255,.22); color: #fff; }
.tc--blue   { background: color-mix(in oklab, #3b82f6 14%, transparent); color: #3b82f6; }
.tc--green  { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.tc--red    { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }
.tc--gray   { background: var(--bg-muted); color: var(--text-muted); }

/* ── Filters ──────────────────────────────── */
.filters-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.f-input  {
    height: 36px; padding: 0 12px; border-radius: var(--radius-md);
    background: var(--bg-surface); border: 1px solid var(--border-default);
    color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic);
    min-width: 220px; outline: none;
}
.f-input:focus { border-color: var(--sada-500); }
.f-select {
    height: 36px; padding: 0 10px; border-radius: var(--radius-md);
    background: var(--bg-surface); border: 1px solid var(--border-default);
    color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); outline: none;
}
.f-btn {
    height: 36px; padding: 0 20px; border-radius: var(--radius-md);
    background: var(--sada-500); color: #fff;
    font-size: 13px; font-weight: 600; border: none; cursor: pointer;
    font-family: var(--font-arabic); transition: background .15s;
}
.f-btn:hover { background: var(--sada-600); }

/* ── Table ────────────────────────────────── */
.table-shell { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.posts-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.posts-table th {
    padding: 10px 16px; background: var(--bg-muted);
    color: var(--text-muted); font-weight: 600; text-align: right;
    border-bottom: 1px solid var(--border-default); white-space: nowrap;
}
.posts-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.posts-table tr:last-child td { border-bottom: none; }
.posts-table tr:hover td { background: color-mix(in oklab, var(--bg-muted) 60%, transparent); }

.td-id   { color: var(--text-muted); font-size: 11px; font-variant-numeric: tabular-nums; }
.td-muted { color: var(--text-muted) !important; }
.td-date  { white-space: nowrap; font-size: 12px; }
.empty-row { text-align: center; color: var(--text-muted); padding: 32px !important; }

/* Content cell */
.td-content { max-width: 300px; }
.content-text {
    font-size: 13px; color: var(--text-primary);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 6px; direction: rtl;
}
.content-meta { display: flex; gap: 5px; flex-wrap: wrap; }
.meta-badge {
    font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 5px;
    white-space: nowrap;
}
.meta-badge--type    { background: color-mix(in oklab, var(--sada-500) 12%, transparent); color: var(--sada-600); }
.meta-badge--dialect { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #7c3aed; }
.meta-badge--hash    { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.meta-badge--account { background: var(--bg-muted); color: var(--text-muted); text-transform: capitalize; }

/* Workspace cell */
.td-ws { min-width: 130px; }
.ws-name { font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 3px; }
.ws-user { font-size: 11px; color: var(--text-muted); }

/* Platform pill */
.platform-pill {
    display: inline-flex; align-items: center;
    font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 99px;
    white-space: nowrap;
}
.plat--pink   { background: color-mix(in oklab, #e1306c 12%, transparent); color: #e1306c; }
.plat--blue   { background: color-mix(in oklab, #1877f2 12%, transparent); color: #1877f2; }
.plat--dark   { background: color-mix(in oklab, #69c9d0 10%, transparent); color: #2d9fa6; }
.plat--yellow { background: color-mix(in oklab, #fffc00 20%, transparent); color: #a38900; }
.plat--gray     { background: var(--bg-muted); color: var(--text-muted); }
.plat--linkedin { background: color-mix(in oklab, #0a66c2 12%, transparent); color: #0a66c2; }

/* Status pill */
.status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 99px;
    white-space: nowrap;
}
.status-dot {
    width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
}

.status--green { background: color-mix(in oklab, #10b981 14%, transparent); color: #059669; border: 1px solid color-mix(in oklab, #10b981 28%, transparent); }
.status--green .status-dot { background: #10b981; }

.status--blue  { background: color-mix(in oklab, #3b82f6 14%, transparent); color: #2563eb; border: 1px solid color-mix(in oklab, #3b82f6 28%, transparent); }
.status--blue  .status-dot { background: #3b82f6; }

.status--red   { background: color-mix(in oklab, #ef4444 14%, transparent); color: #dc2626; border: 1px solid color-mix(in oklab, #ef4444 28%, transparent); }
.status--red   .status-dot { background: #ef4444; box-shadow: 0 0 0 2px color-mix(in oklab, #ef4444 25%, transparent); animation: blink 1.4s ease infinite; }

.status--gray  { background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border-subtle); }
.status--gray  .status-dot { background: var(--text-muted); }

@keyframes blink { 0%,100%{ opacity:1 } 50%{ opacity:.4 } }

/* Timing cell */
.td-time { min-width: 110px; }
.time-label { font-size: 10px; font-weight: 700; color: var(--text-muted); margin-bottom: 2px; }
.time-label--green { color: #059669; }
.time-val { font-size: 12px; color: var(--text-primary); white-space: nowrap; }

/* ── Pagination ───────────────────────────── */
.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn {
    min-width: 34px; height: 34px; display: grid; place-items: center;
    padding: 0 10px; border-radius: var(--radius-md);
    font-size: 12px; font-weight: 600;
    background: var(--bg-surface); border: 1px solid var(--border-default);
    color: var(--text-muted); text-decoration: none; cursor: pointer;
    transition: all .15s;
}
.page-btn:hover          { background: var(--bg-muted); color: var(--text-primary); }
.page-btn--active        { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled      { opacity: .35; cursor: not-allowed; }

/* ── Clickable rows ───────────────────────── */
.clickable-row { cursor: pointer; }
.clickable-row:hover td { background: color-mix(in oklab, var(--sada-500) 4%, var(--bg-muted)); }

/* ── Modal ────────────────────────────────── */
.modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.55); backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center; padding: 24px;
}
.modal {
    background: var(--bg-surface); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); width: 100%; max-width: 600px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,.3);
    animation: modal-in .2s ease;
}
@keyframes modal-in { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:none } }

.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);
    position: sticky; top: 0; background: var(--bg-surface); z-index: 1;
}
.modal-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.modal-close {
    background: none; border: none; color: var(--text-muted);
    font-size: 16px; cursor: pointer; padding: 4px 8px;
    border-radius: var(--radius-sm); transition: background .15s;
}
.modal-close:hover { background: var(--bg-muted); color: var(--text-primary); }

.modal-body { padding: 20px; }

.modal-meta { display: flex; flex-wrap: wrap; gap: 14px 22px; margin-bottom: 18px; }
.meta-item  { display: flex; flex-direction: column; gap: 5px; }
.meta-lbl   { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }
.meta-val   { font-size: 13px; font-weight: 600; color: var(--text-primary); }

.modal-timing {
    display: flex; gap: 20px; flex-wrap: wrap;
    background: var(--bg-muted); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md); padding: 12px 16px;
    margin-bottom: 18px;
}
.timing-item { display: flex; flex-direction: column; gap: 3px; }
.timing-lbl  { font-size: 10px; font-weight: 700; color: var(--text-muted); }
.timing-val  { font-size: 13px; font-weight: 600; color: var(--text-primary); }

.modal-section { margin-bottom: 16px; }
.modal-section-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }

.modal-content-box {
    background: var(--bg-muted); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md); padding: 16px;
    font-size: 14px; line-height: 1.8; color: var(--text-primary);
    white-space: pre-wrap; direction: rtl; text-align: right;
}

.hashtags-wrap { display: flex; gap: 6px; flex-wrap: wrap; }
.hashtag-chip {
    font-size: 12px; font-weight: 600;
    background: color-mix(in oklab, #f59e0b 12%, transparent);
    color: #d97706; padding: 3px 10px; border-radius: 99px;
}
</style>

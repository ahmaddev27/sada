<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface Generation {
    id: number
    agent_type: string
    dialect: string | null
    platform: string | null
    content_type: string | null
    prompt: string | null
    input_tokens: number
    output_tokens: number
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

// Modal
const selected = ref<Generation | null>(null)

function applyFilter() {
    router.get('/admin/ai-generations', {
        search: search.value,
        platform: platform.value,
        agent_type: agentType.value,
    }, { preserveState: true, replace: true })
}

function fmt(n: number) { return new Intl.NumberFormat('ar-SA').format(n) }
function dt(iso: string) {
    return new Date(iso).toLocaleString('ar-SA', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function truncate(t: string | null, len = 70) {
    if (!t) return '—'
    return t.length > len ? t.slice(0, len) + '…' : t
}

const PLATFORM_META: Record<string, { label: string; cls: string }> = {
    instagram: { label: 'انستجرام', cls: 'plat--pink'   },
    facebook:  { label: 'فيسبوك',   cls: 'plat--blue'   },
    tiktok:    { label: 'تيك توك',  cls: 'plat--teal'   },
    snapchat:  { label: 'سناب شات', cls: 'plat--yellow' },
    twitter:   { label: 'X',         cls: 'plat--gray'   },
    x:         { label: 'X',         cls: 'plat--gray'   },
    linkedin:  { label: 'لينكدإن',   cls: 'plat--linkedin' },
}
const AGENT_META: Record<string, { label: string; cls: string }> = {
    content_generator:  { label: 'كاتب المحتوى', cls: 'agent--green'  },
    content_generation: { label: 'توليد محتوى',  cls: 'agent--green'  },
    seasonal:           { label: 'موسمي',          cls: 'agent--amber'  },
    campaign:           { label: 'حملة',            cls: 'agent--purple' },
    analytics:          { label: 'تحليلات',        cls: 'agent--purple' },
}
const DIALECT_LABELS: Record<string, string> = {
    fos: 'الفصحى',
    sa:  'السعودية',
    ae:  'الإماراتية',
    kw:  'الكويتية',
    qa:  'القطرية',
    bh:  'البحرينية',
    om:  'العُمانية',
}
const CONTENT_TYPE_LABELS: Record<string, string> = {
    post:  'منشور',
    reel:  'ريل',
    story: 'قصة',
    ad:    'إعلان',
}

function platformMeta(p: string | null) { return p ? (PLATFORM_META[p] ?? { label: p, cls: 'plat--gray' }) : null }
function agentMeta(a: string) { return AGENT_META[a] ?? { label: a, cls: 'agent--green' } }
function dialectLabel(d: string | null) { return d ? (DIALECT_LABELS[d] ?? d) : null }
function contentTypeLabel(t: string | null) { return t ? (CONTENT_TYPE_LABELS[t] ?? t) : null }

// Cache rate %
const cacheRate = props.stats.total ? Math.round((props.stats.cached_count / props.stats.total) * 100) : 0
</script>

<template>
    <AdminLayout>
        <div class="gen-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">توليدات الذكاء الاصطناعي</h1>
                    <p class="page-sub">سجل كامل لطلبات AI عبر كل Workspaces</p>
                </div>
                <span class="total-chip">{{ fmt(generations.total) }} توليد</span>
            </div>

            <!-- KPI cards -->
            <div class="kpi-strip">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.09 6.26L20 10l-5.91 1.74L12 18l-2.09-6.26L4 10l5.91-1.74z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num">{{ fmt(stats.total) }}</div>
                        <div class="kpi-label">إجمالي التوليدات</div>
                        <div class="kpi-sub"><span class="badge-green">+{{ fmt(stats.today) }} اليوم</span></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--amber">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num">{{ fmt(stats.tokens_charged) }}</div>
                        <div class="kpi-label">توكنات صدى محملة</div>
                        <div class="kpi-sub muted">دخل {{ fmt(stats.input_tokens) }} · خرج {{ fmt(stats.output_tokens) }}</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="10"/><path d="M12 8v4l3 3"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num">{{ fmt(stats.cached_count) }}</div>
                        <div class="kpi-label">توليد مخزّن مؤقتاً</div>
                        <div class="kpi-sub muted">{{ cacheRate }}% من الإجمالي</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num">
                            {{ stats.total ? Math.round(stats.tokens_charged / stats.total) : 0 }}
                        </div>
                        <div class="kpi-label">متوسط توكنات / طلب</div>
                        <div class="kpi-sub muted">توكنات صدى</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" class="f-input" placeholder="بحث باسم الـ workspace..." @keyup.enter="applyFilter" />
                <select v-model="platform" class="f-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X</option>
                </select>
                <select v-model="agentType" class="f-select" @change="applyFilter">
                    <option value="">كل الأنواع</option>
                    <option value="content_generation">توليد محتوى</option>
                    <option value="seasonal">موسمي</option>
                    <option value="campaign">حملة</option>
                </select>
                <button class="f-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-shell">
                <table class="gen-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Workspace / المستخدم</th>
                            <th>نوع الطلب</th>
                            <th>الطلب (Prompt)</th>
                            <th>المنصة</th>
                            <th>الرموز (Tokens)</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="g in generations.data"
                            :key="g.id"
                            class="clickable-row"
                            @click="selected = g"
                        >
                            <td class="td-id">{{ g.id }}</td>

                            <!-- Workspace + user -->
                            <td class="td-ws">
                                <div class="ws-name">{{ g.workspace?.name ?? '—' }}</div>
                                <div class="ws-user">{{ g.user?.name ?? '—' }}</div>
                            </td>

                            <!-- Agent type + content_type + dialect -->
                            <td class="td-type">
                                <span :class="['agent-pill', agentMeta(g.agent_type).cls]">
                                    {{ agentMeta(g.agent_type).label }}
                                </span>
                                <div class="type-meta">
                                    <span v-if="contentTypeLabel(g.content_type)" class="meta-tag meta-tag--type">{{ contentTypeLabel(g.content_type) }}</span>
                                    <span v-if="dialectLabel(g.dialect)" class="meta-tag meta-tag--dialect">{{ dialectLabel(g.dialect) }}</span>
                                </div>
                            </td>

                            <!-- Prompt preview -->
                            <td class="td-prompt">{{ truncate(g.prompt) }}</td>

                            <!-- Platform -->
                            <td>
                                <span v-if="platformMeta(g.platform)" :class="['plat-pill', platformMeta(g.platform)!.cls]">
                                    {{ platformMeta(g.platform)!.label }}
                                </span>
                                <span v-else class="td-muted">—</span>
                            </td>

                            <!-- Tokens breakdown -->
                            <td class="td-tokens">
                                <div class="token-main">
                                    <span class="token-num">{{ fmt(g.sada_tokens_charged) }}</span>
                                    <span class="token-unit">صدى</span>
                                    <span v-if="g.cached" class="cached-pill">مخزّن</span>
                                </div>
                                <div class="token-sub">
                                    ↑{{ fmt(g.input_tokens) }} · ↓{{ fmt(g.output_tokens) }}
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="td-muted td-date">{{ dt(g.created_at) }}</td>
                        </tr>
                        <tr v-if="!generations.data.length">
                            <td colspan="7" class="empty-row">لا توجد توليدات</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="generations.last_page > 1" class="pagination">
                <template v-for="link in generations.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>
        </div>

        <!-- ── Prompt detail modal ──────────────────────────── -->
        <Teleport to="body">
            <div v-if="selected" class="modal-backdrop" @click.self="selected = null">
                <div class="modal">
                    <div class="modal-head">
                        <span class="modal-title">تفاصيل التوليد #{{ selected.id }}</span>
                        <button class="modal-close" @click="selected = null">✕</button>
                    </div>
                    <div class="modal-body">
                        <!-- Meta row -->
                        <div class="modal-meta">
                            <div class="meta-item">
                                <span class="meta-label">Workspace</span>
                                <span class="meta-val">{{ selected.workspace?.name ?? '—' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">المستخدم</span>
                                <span class="meta-val">{{ selected.user?.name ?? '—' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">النوع</span>
                                <span :class="['agent-pill', agentMeta(selected.agent_type).cls]">{{ agentMeta(selected.agent_type).label }}</span>
                            </div>
                            <div v-if="platformMeta(selected.platform)" class="meta-item">
                                <span class="meta-label">المنصة</span>
                                <span :class="['plat-pill', platformMeta(selected.platform)!.cls]">{{ platformMeta(selected.platform)!.label }}</span>
                            </div>
                            <div v-if="contentTypeLabel(selected.content_type)" class="meta-item">
                                <span class="meta-label">نوع المحتوى</span>
                                <span class="meta-tag meta-tag--type">{{ contentTypeLabel(selected.content_type) }}</span>
                            </div>
                            <div v-if="dialectLabel(selected.dialect)" class="meta-item">
                                <span class="meta-label">اللهجة</span>
                                <span class="meta-tag meta-tag--dialect">{{ dialectLabel(selected.dialect) }}</span>
                            </div>
                        </div>

                        <!-- Tokens -->
                        <div class="modal-tokens">
                            <div class="tok-box tok-box--accent">
                                <div class="tok-num">{{ fmt(selected.sada_tokens_charged) }}</div>
                                <div class="tok-lbl">توكنات صدى</div>
                                <span v-if="selected.cached" class="cached-pill">مخزّن</span>
                            </div>
                            <div class="tok-box">
                                <div class="tok-num muted">{{ fmt(selected.input_tokens) }}</div>
                                <div class="tok-lbl">توكنات دخل</div>
                            </div>
                            <div class="tok-box">
                                <div class="tok-num muted">{{ fmt(selected.output_tokens) }}</div>
                                <div class="tok-lbl">توكنات خرج</div>
                            </div>
                        </div>

                        <!-- Prompt -->
                        <div v-if="selected.prompt" class="modal-section">
                            <div class="modal-section-label">الطلب (Prompt)</div>
                            <div class="modal-prompt">{{ selected.prompt }}</div>
                        </div>

                        <div class="modal-footer-date">{{ dt(selected.created_at) }}</div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* ── Page ──────────────────────────────────── */
.gen-page { padding: 26px 30px; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 12px; }
.page-title  { font-size: 20px; font-weight: 800; color: var(--text-primary); margin: 0 0 4px; }
.page-sub    { font-size: 12px; color: var(--text-muted); margin: 0; }
.total-chip  { font-size: 12px; background: var(--bg-muted); color: var(--text-muted); padding: 5px 14px; border-radius: 99px; font-weight: 600; white-space: nowrap; }

/* ── KPI strip ────────────────────────────── */
.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
.kpi-card  {
    background: var(--bg-surface); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); padding: 16px 18px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: box-shadow .2s;
}
.kpi-card:hover { box-shadow: 0 4px 16px color-mix(in oklab, var(--text-primary) 6%, transparent); }
.kpi-icon { width: 38px; height: 38px; border-radius: var(--radius-md); display: grid; place-items: center; flex-shrink: 0; }
.kpi-icon--purple { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #8b5cf6; }
.kpi-icon--amber  { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; }
.kpi-icon--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #3b82f6; }
.kpi-icon--green  { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; }

.kpi-num   { font-size: 24px; font-weight: 900; color: var(--text-primary); line-height: 1; margin-bottom: 3px; }
.kpi-label { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; }
.kpi-sub   { font-size: 11px; color: var(--text-muted); }
.badge-green { background: color-mix(in oklab, #10b981 14%, transparent); color: #059669; padding: 1px 7px; border-radius: 99px; font-size: 10px; font-weight: 700; }

/* ── Filters ──────────────────────────────── */
.filters-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.f-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 200px; outline: none; }
.f-input:focus { border-color: var(--sada-500); }
.f-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); outline: none; }
.f-btn    { height: 36px; padding: 0 20px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.f-btn:hover { background: var(--sada-600); }

/* ── Table ────────────────────────────────── */
.table-shell { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); margin-bottom: 16px; }
.gen-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.gen-table th { padding: 10px 16px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.gen-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.gen-table tr:last-child td { border-bottom: none; }
.clickable-row { cursor: pointer; }
.clickable-row:hover td { background: color-mix(in oklab, var(--sada-500) 4%, var(--bg-muted)); }

.td-id    { color: var(--text-muted); font-size: 11px; }
.td-muted { color: var(--text-muted) !important; }
.td-date  { white-space: nowrap; font-size: 12px; }
.td-prompt { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-muted); font-size: 12px; }
.empty-row { text-align: center; color: var(--text-muted); padding: 32px !important; }

.td-ws { min-width: 130px; }
.ws-name { font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 3px; }
.ws-user { font-size: 11px; color: var(--text-muted); }

/* Agent type cell */
.td-type { min-width: 120px; }
.agent-pill { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 99px; white-space: nowrap; margin-bottom: 5px; }
.agent--green  { background: color-mix(in oklab, #10b981 14%, transparent); color: #059669; border: 1px solid color-mix(in oklab, #10b981 25%, transparent); }
.agent--amber  { background: color-mix(in oklab, #f59e0b 14%, transparent); color: #d97706; border: 1px solid color-mix(in oklab, #f59e0b 25%, transparent); }
.agent--purple { background: color-mix(in oklab, #8b5cf6 14%, transparent); color: #7c3aed; border: 1px solid color-mix(in oklab, #8b5cf6 25%, transparent); }

.type-meta { display: flex; gap: 4px; flex-wrap: wrap; }
.meta-tag { font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 5px; }
.meta-tag--type    { background: color-mix(in oklab, var(--sada-500) 12%, transparent); color: var(--sada-600); }
.meta-tag--dialect { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #7c3aed; }

/* Platform pill */
.plat-pill { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 99px; white-space: nowrap; }
.plat--pink   { background: color-mix(in oklab, #e1306c 12%, transparent); color: #e1306c; }
.plat--blue   { background: color-mix(in oklab, #1877f2 12%, transparent); color: #1877f2; }
.plat--teal   { background: color-mix(in oklab, #69c9d0 14%, transparent); color: #2d9fa6; }
.plat--yellow { background: color-mix(in oklab, #fffc00 20%, transparent); color: #a38900; }
.plat--gray     { background: var(--bg-muted); color: var(--text-muted); }
.plat--linkedin { background: color-mix(in oklab, #0a66c2 12%, transparent); color: #0a66c2; }

/* Tokens cell */
.td-tokens { min-width: 130px; }
.token-main { display: flex; align-items: center; gap: 5px; margin-bottom: 3px; }
.token-num  { font-size: 15px; font-weight: 800; color: #7c3aed; }
.token-unit { font-size: 10px; color: var(--text-muted); font-weight: 600; }
.cached-pill { font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 99px; background: color-mix(in oklab, #3b82f6 14%, transparent); color: #2563eb; }
.token-sub  { font-size: 10px; color: var(--text-muted); }

/* ── Pagination ───────────────────────────── */
.pagination { display: flex; gap: 4px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; transition: all .15s; }
.page-btn:hover { background: var(--bg-muted); }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .35; cursor: not-allowed; }

/* ── Modal ────────────────────────────────── */
.modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.55); backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center; padding: 24px;
}
.modal {
    background: var(--bg-surface); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); width: 100%; max-width: 580px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0,0,0,.3);
    animation: modal-in .2s ease;
}
@keyframes modal-in { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:none } }

.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);
}
.modal-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.modal-close { background: none; border: none; color: var(--text-muted); font-size: 16px; cursor: pointer; padding: 4px 8px; border-radius: var(--radius-sm); transition: background .15s; }
.modal-close:hover { background: var(--bg-muted); color: var(--text-primary); }

.modal-body { padding: 20px; }

.modal-meta { display: flex; flex-wrap: wrap; gap: 12px 20px; margin-bottom: 18px; }
.meta-item  { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }
.meta-val   { font-size: 13px; font-weight: 600; color: var(--text-primary); }

.modal-tokens { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
.tok-box { background: var(--bg-muted); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 12px 14px; text-align: center; }
.tok-box--accent { border-color: color-mix(in oklab, #8b5cf6 30%, transparent); background: color-mix(in oklab, #8b5cf6 6%, transparent); }
.tok-num { font-size: 20px; font-weight: 900; color: var(--text-primary); }
.tok-num.muted { color: var(--text-muted); font-size: 16px; }
.tok-lbl { font-size: 10px; color: var(--text-muted); font-weight: 600; margin-top: 3px; }

.modal-section { margin-bottom: 16px; }
.modal-section-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
.modal-prompt {
    background: var(--bg-muted); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md); padding: 14px;
    font-size: 13px; color: var(--text-primary); line-height: 1.7;
    white-space: pre-wrap; direction: rtl;
}
.modal-footer-date { font-size: 11px; color: var(--text-muted); margin-top: 8px; }
.muted { color: var(--text-muted) !important; }

/* Responsive */
@media (max-width: 900px) { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .gen-page { padding: 16px; } .kpi-strip { grid-template-columns: 1fr; } }
</style>

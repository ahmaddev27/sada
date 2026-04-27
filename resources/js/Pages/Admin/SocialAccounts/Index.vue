<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface SocialAccount {
    id: number
    provider: string
    provider_account_id: string | null
    account_name: string | null
    account_picture_url: string | null
    status: string
    scopes: string[] | null
    token_expires_at: string | null
    created_at: string
    workspace: { id: number; name: string } | null
}

interface Paginator {
    data: SocialAccount[]
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
    accounts: Paginator
    stats: { total: number; healthy: number; expired: number; revoked: number }
    filters: { search?: string; provider?: string; status?: string }
}>()

const search   = ref(props.filters.search ?? '')
const provider = ref(props.filters.provider ?? '')
const status   = ref(props.filters.status ?? '')

function applyFilter() {
    router.get('/admin/social-accounts', {
        search: search.value,
        provider: provider.value,
        status: status.value,
    }, { preserveState: true, replace: true })
}

const PROVIDER_META: Record<string, { label: string; cls: string; icon: string }> = {
    instagram: { label: 'انستجرام', cls: 'plat--pink',   icon: 'IG' },
    facebook:  { label: 'فيسبوك',   cls: 'plat--blue',   icon: 'FB' },
    tiktok:    { label: 'تيك توك',  cls: 'plat--teal',   icon: 'TT' },
    snapchat:  { label: 'سناب شات', cls: 'plat--yellow', icon: 'SC' },
    twitter:   { label: 'X',         cls: 'plat--gray',   icon: 'X'  },
}

const STATUS_META: Record<string, { label: string; cls: string }> = {
    healthy: { label: 'متصل',             cls: 'st--green'  },
    expired: { label: 'منتهي الصلاحية',   cls: 'st--amber'  },
    revoked: { label: 'تم الإلغاء',       cls: 'st--red'    },
    error:   { label: 'خطأ',              cls: 'st--red'    },
}

function providerMeta(p: string) { return PROVIDER_META[p] ?? { label: p, cls: 'plat--gray', icon: '?' } }
function statusMeta(s: string) { return STATUS_META[s] ?? { label: s, cls: 'st--gray' } }

function dt(iso: string | null) {
    return iso ? new Date(iso).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'
}
function dtShort(iso: string | null) {
    return iso ? new Date(iso).toLocaleDateString('ar-SA', { month: 'short', day: 'numeric', year: '2-digit' }) : '—'
}

function isExpired(iso: string | null) {
    return iso ? new Date(iso) < new Date() : false
}
function expiresInDays(iso: string | null) {
    if (!iso) return null
    const diff = Math.ceil((new Date(iso).getTime() - Date.now()) / 86400000)
    return diff
}

const healthRate = props.stats.total
    ? Math.round((props.stats.healthy / props.stats.total) * 100)
    : 0
</script>

<template>
    <AdminLayout>
        <div class="soc-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الحسابات المرتبطة</h1>
                    <p class="page-sub">جميع حسابات التواصل الاجتماعي المربوطة بالـ Workspaces</p>
                </div>
                <span class="total-chip">{{ accounts.total }} حساب</span>
            </div>

            <!-- KPI cards -->
            <div class="kpi-strip">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num">{{ stats.total }}</div>
                        <div class="kpi-label">إجمالي الحسابات</div>
                        <div class="kpi-sub muted">{{ healthRate }}% بصحة جيدة</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num" style="color:#059669">{{ stats.healthy }}</div>
                        <div class="kpi-label">متصل ونشط</div>
                        <div class="kpi-sub">
                            <span class="badge-green">نسبة {{ healthRate }}%</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--amber">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num" style="color:#d97706">{{ stats.expired }}</div>
                        <div class="kpi-label">منتهي الصلاحية</div>
                        <div class="kpi-sub muted">يحتاج إعادة ربط</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon--red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num" style="color:#dc2626">{{ stats.revoked }}</div>
                        <div class="kpi-label">ملغى / خطأ</div>
                        <div class="kpi-sub muted">صلاحية مسحوبة</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" class="f-input" placeholder="بحث باسم الحساب أو الـ workspace..." @keyup.enter="applyFilter" />
                <select v-model="provider" class="f-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X (تويتر)</option>
                </select>
                <select v-model="status" class="f-select" @change="applyFilter">
                    <option value="">كل الحالات</option>
                    <option value="healthy">متصل</option>
                    <option value="expired">منتهي</option>
                    <option value="revoked">ملغى</option>
                </select>
                <button class="f-btn" @click="applyFilter">بحث</button>
            </div>

            <!-- Table -->
            <div class="table-shell">
                <table class="soc-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>الحساب</th>
                            <th>Workspace</th>
                            <th>المنصة</th>
                            <th>الحالة</th>
                            <th>انتهاء التوكن</th>
                            <th>الصلاحيات</th>
                            <th>تاريخ الربط</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in accounts.data" :key="a.id">

                            <td class="td-id">{{ a.id }}</td>

                            <!-- Account -->
                            <td class="td-account">
                                <div class="account-row">
                                    <div v-if="a.account_picture_url" class="acc-avatar">
                                        <img :src="a.account_picture_url" :alt="a.account_name ?? ''" class="acc-img" />
                                    </div>
                                    <div v-else class="acc-avatar acc-avatar--letter">
                                        {{ (a.account_name ?? a.provider).charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="acc-name">{{ a.account_name || '—' }}</div>
                                        <div v-if="a.provider_account_id" class="acc-id">ID: {{ a.provider_account_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Workspace -->
                            <td class="td-muted td-ws">{{ a.workspace?.name ?? '—' }}</td>

                            <!-- Platform -->
                            <td>
                                <span :class="['plat-pill', providerMeta(a.provider).cls]">
                                    {{ providerMeta(a.provider).label }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span :class="['status-pill', statusMeta(a.status).cls]">
                                    <span class="status-dot" />
                                    {{ statusMeta(a.status).label }}
                                </span>
                            </td>

                            <!-- Token expiry -->
                            <td class="td-expiry">
                                <template v-if="a.token_expires_at">
                                    <div :class="['expiry-date', isExpired(a.token_expires_at) ? 'expiry--expired' : expiresInDays(a.token_expires_at)! < 7 ? 'expiry--soon' : '']">
                                        {{ dt(a.token_expires_at) }}
                                    </div>
                                    <div class="expiry-sub">
                                        <template v-if="isExpired(a.token_expires_at)">
                                            <span class="expiry-tag expiry-tag--red">منتهي</span>
                                        </template>
                                        <template v-else-if="expiresInDays(a.token_expires_at)! < 7">
                                            <span class="expiry-tag expiry-tag--amber">{{ expiresInDays(a.token_expires_at) }} أيام</span>
                                        </template>
                                        <template v-else>
                                            <span class="expiry-tag expiry-tag--green">{{ expiresInDays(a.token_expires_at) }} يوم</span>
                                        </template>
                                    </div>
                                </template>
                                <span v-else class="td-muted">دائم</span>
                            </td>

                            <!-- Scopes -->
                            <td class="td-scopes">
                                <span v-if="a.scopes?.length" class="scopes-count">
                                    {{ a.scopes.length }} صلاحية
                                </span>
                                <span v-else class="td-muted">—</span>
                            </td>

                            <!-- Date -->
                            <td class="td-muted td-date">{{ dtShort(a.created_at) }}</td>
                        </tr>
                        <tr v-if="!accounts.data.length">
                            <td colspan="8" class="empty-row">لا توجد حسابات مرتبطة</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="accounts.last_page > 1" class="pagination">
                <template v-for="link in accounts.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="['page-btn', { 'page-btn--active': link.active }]" v-html="link.label" />
                    <span v-else class="page-btn page-btn--disabled" v-html="link.label" />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* ── Page ──────────────────────────────────── */
.soc-page { padding: 26px 30px; }

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
.kpi-icon--primary { background: color-mix(in oklab, var(--sada-500) 12%, transparent); color: var(--sada-500); }
.kpi-icon--green   { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; }
.kpi-icon--amber   { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #f59e0b; }
.kpi-icon--red     { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; }

.kpi-num   { font-size: 26px; font-weight: 900; color: var(--text-primary); line-height: 1; margin-bottom: 3px; }
.kpi-label { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; }
.kpi-sub   { font-size: 11px; color: var(--text-muted); }
.badge-green { background: color-mix(in oklab, #10b981 14%, transparent); color: #059669; padding: 1px 7px; border-radius: 99px; font-size: 10px; font-weight: 700; }
.muted { color: var(--text-muted) !important; }

/* ── Filters ──────────────────────────────── */
.filters-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.f-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 240px; outline: none; }
.f-input:focus { border-color: var(--sada-500); }
.f-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); outline: none; }
.f-btn    { height: 36px; padding: 0 20px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.f-btn:hover { background: var(--sada-600); }

/* ── Table ────────────────────────────────── */
.table-shell { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); margin-bottom: 16px; }
.soc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.soc-table th { padding: 10px 16px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.soc-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.soc-table tr:last-child td { border-bottom: none; }
.soc-table tr:hover td { background: color-mix(in oklab, var(--bg-muted) 60%, transparent); }

.td-id    { color: var(--text-muted); font-size: 11px; }
.td-muted { color: var(--text-muted) !important; }
.td-date  { white-space: nowrap; font-size: 12px; }
.td-ws    { font-size: 12px; font-weight: 600; }
.empty-row { text-align: center; color: var(--text-muted); padding: 32px !important; }

/* Account cell */
.td-account { min-width: 160px; }
.account-row { display: flex; align-items: center; gap: 10px; }
.acc-avatar  { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; overflow: hidden; }
.acc-img     { width: 100%; height: 100%; object-fit: cover; }
.acc-avatar--letter {
    background: color-mix(in oklab, var(--sada-500) 14%, transparent);
    color: var(--sada-600); font-size: 13px; font-weight: 700;
    display: grid; place-items: center;
}
.acc-name { font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
.acc-id   { font-size: 10px; color: var(--text-muted); font-family: monospace; }

/* Platform pill */
.plat-pill { display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 99px; white-space: nowrap; }
.plat--pink   { background: color-mix(in oklab, #e1306c 12%, transparent); color: #e1306c; }
.plat--blue   { background: color-mix(in oklab, #1877f2 12%, transparent); color: #1877f2; }
.plat--teal   { background: color-mix(in oklab, #69c9d0 14%, transparent); color: #2d9fa6; }
.plat--yellow { background: color-mix(in oklab, #fffc00 20%, transparent); color: #a38900; }
.plat--gray   { background: var(--bg-muted); color: var(--text-muted); }

/* Status pill */
.status-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 99px; white-space: nowrap; }
.status-dot  { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

.st--green { background: color-mix(in oklab, #10b981 14%, transparent); color: #059669; border: 1px solid color-mix(in oklab, #10b981 28%, transparent); }
.st--green .status-dot { background: #10b981; }

.st--amber { background: color-mix(in oklab, #f59e0b 14%, transparent); color: #d97706; border: 1px solid color-mix(in oklab, #f59e0b 28%, transparent); }
.st--amber .status-dot { background: #f59e0b; animation: pulse .15s ease infinite; }

.st--red   { background: color-mix(in oklab, #ef4444 14%, transparent); color: #dc2626; border: 1px solid color-mix(in oklab, #ef4444 28%, transparent); }
.st--red   .status-dot { background: #ef4444; }

.st--gray  { background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border-subtle); }
.st--gray  .status-dot { background: var(--text-muted); }

@keyframes pulse { 0%,100%{ opacity:1 } 50%{ opacity:.4 } }

/* Expiry cell */
.td-expiry { min-width: 120px; }
.expiry-date { font-size: 12px; color: var(--text-primary); margin-bottom: 3px; }
.expiry--expired { color: #dc2626; }
.expiry--soon    { color: #d97706; }
.expiry-sub { display: flex; }
.expiry-tag { font-size: 9px; font-weight: 700; padding: 1px 7px; border-radius: 99px; }
.expiry-tag--green { background: color-mix(in oklab, #10b981 12%, transparent); color: #059669; }
.expiry-tag--amber { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.expiry-tag--red   { background: color-mix(in oklab, #ef4444 12%, transparent); color: #dc2626; }

/* Scopes */
.td-scopes { min-width: 80px; }
.scopes-count { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 8px; border-radius: 5px; font-weight: 600; }

/* ── Pagination ───────────────────────────── */
.pagination { display: flex; gap: 4px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; transition: all .15s; }
.page-btn:hover { background: var(--bg-muted); }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .35; cursor: not-allowed; }

/* Responsive */
@media (max-width: 900px) { .kpi-strip { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .soc-page { padding: 16px; } .kpi-strip { grid-template-columns: 1fr; } }
</style>

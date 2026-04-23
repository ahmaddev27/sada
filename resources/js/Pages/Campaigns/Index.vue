<script setup lang="ts">
// ADS-01 → ADS-11
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Modal from '@/Components/Base/Modal.vue'
import Icon from '@/Components/Base/Icon.vue'
import type { Campaign, CampaignStatus } from '@/Types'

interface SocialAccount {
    id: number
    provider: string
    account_name: string
}

const props = defineProps<{
    campaigns: { data: Campaign[]; links: any[]; meta: any }
    filters: { status: string }
    socialAccounts: SocialAccount[]
}>()

// ── Status filter ──────────────────────────────────────────────────────────
const activeStatus = ref<string>(props.filters.status ?? 'all')

const STATUS_TABS = [
    { key: 'all',       label: 'الكل' },
    { key: 'draft',     label: 'مسودة' },
    { key: 'active',    label: 'نشطة' },
    { key: 'paused',    label: 'موقوفة' },
    { key: 'completed', label: 'مكتملة' },
]

function filterByStatus(key: string) {
    activeStatus.value = key
    router.get('/campaigns', { status: key === 'all' ? '' : key }, { preserveState: true, replace: true })
}

// ── Delete confirm ─────────────────────────────────────────────────────────
const showDeleteModal  = ref(false)
const deleteTarget     = ref<Campaign | null>(null)
const deleteProcessing = ref(false)

function openDelete(campaign: Campaign) {
    deleteTarget.value = campaign
    showDeleteModal.value = true
}
function closeDelete() {
    showDeleteModal.value = false
    deleteTarget.value = null
}
function confirmDelete() {
    if (!deleteTarget.value) return
    deleteProcessing.value = true
    router.delete(`/campaigns/${deleteTarget.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false
            closeDelete()
        },
    })
}

// ── Campaign actions ───────────────────────────────────────────────────────
function pauseCampaign(id: number) {
    router.post(`/campaigns/${id}/pause`)
}
function resumeCampaign(id: number) {
    router.post(`/campaigns/${id}/resume`)
}
function duplicateCampaign(id: number) {
    router.post(`/campaigns/${id}/duplicate`)
}

// ── Helpers ────────────────────────────────────────────────────────────────
const OBJECTIVE_LABELS: Record<string, string> = {
    awareness:   'الوعي بالعلامة',
    traffic:     'الزيارات',
    engagement:  'التفاعل',
    conversions: 'التحويلات',
    app_installs:'تثبيت التطبيق',
    video_views: 'مشاهدات الفيديو',
}

const STATUS_LABELS: Record<string, string> = {
    draft:     'مسودة',
    pending:   'قيد المراجعة',
    active:    'نشطة',
    paused:    'موقوفة',
    completed: 'مكتملة',
    rejected:  'مرفوضة',
}

const BUDGET_TYPE_LABELS: Record<string, string> = {
    daily:    'يومي',
    lifetime: 'إجمالي',
}

function statusBadgeClass(status: CampaignStatus): string {
    switch (status) {
        case 'active':    return 'badge badge-brand'
        case 'rejected':  return 'badge badge-error'
        default:          return 'badge badge-neutral'
    }
}

function statusInlineStyle(status: CampaignStatus): string {
    if (status === 'pending') return 'background:var(--sand-500);color:#fff;'
    return ''
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatNumber(n: number | undefined): string {
    if (n === undefined || n === null) return '—'
    return n.toLocaleString('ar-SA')
}

function formatCurrency(n: number | undefined, currency: string): string {
    if (n === undefined || n === null) return '—'
    return `${n.toLocaleString('ar-SA')} ${currency}`
}

const hasCampaigns = computed(() => props.campaigns.data.length > 0)

// Pagination helpers
const prevLink = computed(() => props.campaigns.links.find((l: any) => l.label === '&laquo; Previous' || l.label === '‹') ?? null)
const nextLink = computed(() => props.campaigns.links.find((l: any) => l.label === 'Next &raquo;' || l.label === '›') ?? null)
</script>

<template>
    <AppLayout title="الحملات الإعلانية" :crumbs="['الرئيسية', 'الحملات']">
        <div class="camp-page">

            <!-- Page header -->
            <div class="camp-header">
                <div>
                    <h1 class="camp-title">الحملات الإعلانية</h1>
                    <p class="camp-sub">أدر حملاتك المدفوعة على انستجرام وفيسبوك</p>
                </div>
                <Link href="/campaigns/create" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M12 5v14M5 12h14"/></svg>
                    إنشاء حملة
                </Link>
            </div>

            <!-- Status filter tabs -->
            <div class="cmp-tabs" style="margin-bottom:24px;">
                <button
                    v-for="tab in STATUS_TABS"
                    :key="tab.key"
                    :data-active="activeStatus === tab.key"
                    @click="filterByStatus(tab.key)"
                >{{ tab.label }}</button>
            </div>

            <!-- Empty state -->
            <div v-if="!hasCampaigns" class="camp-empty">
                <div class="camp-empty-icon">
                    <Icon name="megaphone" :size="36" />
                </div>
                <h3>لا توجد حملات بعد</h3>
                <p>أنشئ حملتك الإعلانية الأولى وابدأ بالوصول إلى جمهورك</p>
                <Link href="/campaigns/create" class="btn btn-primary">
                    <Icon name="plus" :size="14" />
                    إنشاء أول حملة
                </Link>
            </div>

            <!-- Campaigns list -->
            <div v-else class="stack">
                <div
                    v-for="campaign in campaigns.data"
                    :key="campaign.id"
                    class="card camp-card"
                >
                    <!-- Card top row -->
                    <div class="camp-card-head">
                        <div class="camp-name-row">
                            <!-- Platform icon -->
                            <div class="camp-platform-icon" :class="`camp-platform--${campaign.platform}`">
                                <Icon :name="campaign.platform" :size="16" />
                            </div>
                            <div>
                                <div class="camp-name">{{ campaign.name }}</div>
                                <div class="camp-account">
                                    {{ campaign.social_account?.account_name ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="camp-badges">
                            <!-- Status badge -->
                            <span
                                :class="statusBadgeClass(campaign.status)"
                                :style="statusInlineStyle(campaign.status)"
                            >{{ STATUS_LABELS[campaign.status] }}</span>
                            <!-- Objective badge -->
                            <span class="badge badge-neutral">{{ OBJECTIVE_LABELS[campaign.objective] }}</span>
                        </div>
                    </div>

                    <!-- Card body: metrics + dates -->
                    <div class="camp-card-body">
                        <!-- Budget -->
                        <div class="camp-meta-block">
                            <div class="camp-meta-label">الميزانية</div>
                            <div class="camp-meta-value">
                                {{ campaign.budget_amount }} {{ campaign.budget_currency }}
                                <span class="camp-meta-sub">/ {{ BUDGET_TYPE_LABELS[campaign.budget_type] }}</span>
                            </div>
                        </div>

                        <!-- Date range -->
                        <div class="camp-meta-block">
                            <div class="camp-meta-label">المدة</div>
                            <div class="camp-meta-value">
                                {{ formatDate(campaign.starts_at) }}
                                <span class="camp-meta-sep">←</span>
                                {{ formatDate(campaign.ends_at) }}
                            </div>
                        </div>

                        <!-- Insights -->
                        <div class="camp-insights">
                            <div class="camp-insight-item">
                                <div class="camp-insight-label">الإنفاق</div>
                                <div class="camp-insight-value">{{ formatCurrency(campaign.insights?.spend, campaign.budget_currency) }}</div>
                            </div>
                            <div class="camp-insight-item">
                                <div class="camp-insight-label">الوصول</div>
                                <div class="camp-insight-value">{{ formatNumber(campaign.insights?.reach) }}</div>
                            </div>
                            <div class="camp-insight-item">
                                <div class="camp-insight-label">الظهور</div>
                                <div class="camp-insight-value">{{ formatNumber(campaign.insights?.impressions) }}</div>
                            </div>
                            <div class="camp-insight-item">
                                <div class="camp-insight-label">النقرات</div>
                                <div class="camp-insight-value">{{ formatNumber(campaign.insights?.clicks) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card actions -->
                    <div class="camp-card-actions">
                        <Link :href="`/campaigns/${campaign.id}`" class="btn btn-secondary btn-sm">
                            <Icon name="eye" :size="13" />
                            تفاصيل
                        </Link>

                        <button
                            v-if="campaign.status === 'active'"
                            class="btn btn-ghost btn-sm"
                            @click="pauseCampaign(campaign.id)"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                            إيقاف مؤقت
                        </button>

                        <button
                            v-if="campaign.status === 'paused'"
                            class="btn btn-ghost btn-sm"
                            @click="resumeCampaign(campaign.id)"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            استئناف
                        </button>

                        <button
                            class="btn btn-ghost btn-sm"
                            @click="duplicateCampaign(campaign.id)"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            نسخ
                        </button>

                        <button
                            v-if="campaign.status === 'draft'"
                            class="btn btn-ghost btn-sm camp-btn-delete"
                            @click="openDelete(campaign)"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            حذف
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="campaigns.meta?.last_page > 1" class="camp-pagination">
                    <button
                        class="btn btn-ghost btn-sm"
                        :disabled="!prevLink?.url"
                        @click="prevLink?.url && router.get(prevLink.url)"
                    >
                        <Icon name="chevronRight" :size="14" />
                        السابق
                    </button>
                    <span class="camp-pagination-info">
                        صفحة {{ campaigns.meta.current_page }} من {{ campaigns.meta.last_page }}
                    </span>
                    <button
                        class="btn btn-ghost btn-sm"
                        :disabled="!nextLink?.url"
                        @click="nextLink?.url && router.get(nextLink.url)"
                    >
                        التالي
                        <Icon name="chevronLeft" :size="14" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <Modal :show="showDeleteModal" title="تأكيد الحذف" size="sm" @close="closeDelete">
            <div class="delete-confirm">
                <div class="delete-confirm-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <p class="delete-confirm-msg">
                    هل تريد حذف حملة <strong>{{ deleteTarget?.name }}</strong> نهائياً؟
                    لا يمكن التراجع عن هذا الإجراء.
                </p>
            </div>
            <template #footer>
                <button class="btn btn-danger" :disabled="deleteProcessing" @click="confirmDelete">
                    <svg v-if="deleteProcessing" class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    {{ deleteProcessing ? 'جارٍ الحذف...' : 'حذف الحملة' }}
                </button>
                <button class="btn btn-secondary" @click="closeDelete">إلغاء</button>
            </template>
        </Modal>

    </AppLayout>
</template>

<style scoped>
.camp-page { max-width: 960px; margin: 0 auto; }

/* ── Header ── */
.camp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.camp-title { margin: 0; font-size: 22px; font-weight: 700; color: var(--text-primary); }
.camp-sub   { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }

/* ── Empty state ── */
.camp-empty {
    text-align: center;
    padding: 80px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    background: var(--bg-surface);
    border: 1px dashed var(--border-default);
    border-radius: var(--radius-lg);
}
.camp-empty-icon {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: var(--accent-soft);
    display: grid;
    place-items: center;
    color: var(--accent);
    margin-bottom: 4px;
}
.camp-empty h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--text-primary); }
.camp-empty p  { margin: 0; font-size: 13px; color: var(--text-muted); max-width: 320px; }

/* ── Campaign card ── */
.camp-card {
    padding: 0;
    overflow: hidden;
    transition: box-shadow .2s, border-color .2s;
}
.camp-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.07);
    border-color: var(--border-default);
}

/* Card head */
.camp-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    gap: 12px;
    flex-wrap: wrap;
}
.camp-name-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.camp-platform-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.camp-platform--instagram {
    background: linear-gradient(135deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    color: #fff;
}
.camp-platform--facebook {
    background: #1877f2;
    color: #fff;
}
.camp-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
}
.camp-account {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}
.camp-badges {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Card body */
.camp-card-body {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 0;
    flex-wrap: wrap;
}

.camp-meta-block {
    padding: 14px 20px;
    border-left: 1px solid var(--border-subtle);
    min-width: 160px;
}
.camp-meta-block:last-of-type { border-left: none; }
.camp-meta-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 4px;
}
.camp-meta-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
.camp-meta-sub { font-weight: 400; color: var(--text-muted); }
.camp-meta-sep { margin: 0 4px; color: var(--text-muted); font-weight: 400; }

/* Insights row */
.camp-insights {
    display: flex;
    gap: 0;
    flex: 1;
    border-right: 1px solid var(--border-subtle);
    margin-right: auto;
}
.camp-insight-item {
    padding: 14px 16px;
    border-left: 1px solid var(--border-subtle);
    flex: 1;
    min-width: 80px;
}
.camp-insight-item:first-child { border-right: none; }
.camp-insight-label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 4px;
}
.camp-insight-value {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Card actions */
.camp-card-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-top: 1px solid var(--border-subtle);
    background: var(--bg-muted);
    flex-wrap: wrap;
}
.camp-btn-delete { color: var(--error); }
.camp-btn-delete:hover { background: var(--error-bg); color: var(--error); }

/* ── Pagination ── */
.camp-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 8px 0;
}
.camp-pagination-info {
    font-size: 13px;
    color: var(--text-muted);
}

/* ── Delete confirm ── */
.delete-confirm {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
    padding: 8px 0 4px;
}
.delete-confirm-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: var(--error-bg);
    color: var(--error);
    display: grid;
    place-items: center;
}
.delete-confirm-msg {
    margin: 0;
    font-size: 14px;
    color: var(--text-primary);
    line-height: 1.7;
}

/* ── Spinner ── */
.spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

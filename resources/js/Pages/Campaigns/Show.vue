<script setup lang="ts">
// ADS-08, ADS-09, ADS-11
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Modal from '@/Components/Base/Modal.vue'
import Icon from '@/Components/Base/Icon.vue'
import type { Campaign } from '@/Types'

const props = defineProps<{ campaign: Campaign }>()

// ── Status badge ──────────────────────────────────────────────────────────────
const STATUS_LABELS: Record<string, string> = {
    draft:     'مسودة',
    pending:   'في الانتظار',
    active:    'نشطة',
    paused:    'موقوفة',
    completed: 'مكتملة',
    rejected:  'مرفوضة',
}

const OBJECTIVE_LABELS: Record<string, string> = {
    awareness:    'الوعي بالعلامة',
    traffic:      'الزيارات',
    engagement:   'التفاعل',
    conversions:  'التحويلات',
    app_installs: 'تثبيت التطبيق',
    video_views:  'مشاهدات الفيديو',
}

const CURRENCY_LABELS: Record<string, string> = {
    SAR: 'ر.س', AED: 'د.إ', USD: '$', QAR: 'ر.ق', KWD: 'د.ك', BHD: 'د.ب', OMR: 'ر.ع',
}

function statusBadgeClass(status: string) {
    if (status === 'active')   return 'badge badge-brand'
    if (status === 'rejected') return 'badge badge-error'
    return 'badge badge-neutral'
}

const currency = computed(() => CURRENCY_LABELS[props.campaign.budget_currency] ?? props.campaign.budget_currency)

// ── Actions ───────────────────────────────────────────────────────────────────
const processing = ref(false)

function pause() {
    processing.value = true
    router.post(`/campaigns/${props.campaign.id}/pause`, {}, { onFinish: () => { processing.value = false } })
}
function resume() {
    processing.value = true
    router.post(`/campaigns/${props.campaign.id}/resume`, {}, { onFinish: () => { processing.value = false } })
}
function duplicate() {
    router.post(`/campaigns/${props.campaign.id}/duplicate`)
}

// Delete
const showDelete = ref(false)
function confirmDelete() {
    showDelete.value = false
    router.delete(`/campaigns/${props.campaign.id}`)
}

// ── Insights helpers ──────────────────────────────────────────────────────────
const ins = computed(() => props.campaign.insights)

function fmt(val: number | undefined, prefix = '', suffix = '') {
    if (val === undefined || val === null) return '—'
    return prefix + val.toLocaleString('ar') + suffix
}

function fmtPct(val: number | undefined) {
    if (val === undefined || val === null) return '—'
    return (val * 100).toFixed(2) + '%'
}

// ── Date helpers ──────────────────────────────────────────────────────────────
function formatDate(d: string) {
    return new Date(d).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
    <AppLayout
        :title="campaign.name"
        :crumbs="['الرئيسية', 'الحملات', campaign.name]"
    >
        <div class="show-wrap">

            <!-- Back + actions bar -->
            <div class="page-hd">
                <Link href="/campaigns" class="back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    الحملات
                </Link>
                <span class="page-hd-sep">/</span>
                <span class="page-hd-title">{{ campaign.name }}</span>

                <div class="hd-actions">
                    <button
                        v-if="campaign.status === 'active'"
                        class="btn btn-secondary btn-sm"
                        :disabled="processing"
                        @click="pause"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        إيقاف مؤقت
                    </button>
                    <button
                        v-if="campaign.status === 'paused'"
                        class="btn btn-primary btn-sm"
                        :disabled="processing"
                        @click="resume"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        استئناف
                    </button>
                    <button class="btn btn-ghost btn-sm" @click="duplicate">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        نسخ
                    </button>
                    <button
                        v-if="campaign.status === 'draft'"
                        class="btn btn-danger btn-sm"
                        @click="showDelete = true"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6 18.1 20a2 2 0 0 1-2 1.9H7.9a2 2 0 0 1-2-1.9L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        حذف
                    </button>
                </div>
            </div>

            <!-- ── Status banner (pending / rejected) ── -->
            <div v-if="campaign.status === 'pending'" class="alert" style="background:color-mix(in oklab,var(--sand-500) 12%,transparent); border-color:var(--sand-500); margin-bottom:20px;">
                <div class="alert-icon" style="color:var(--sand-700);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                </div>
                <div class="alert-body">
                    <div class="alert-desc">الحملة قيد المراجعة من Meta، سيتم تفعيلها خلال ٢٤ ساعة.</div>
                </div>
            </div>
            <div v-else-if="campaign.status === 'rejected'" class="alert alert-error" style="margin-bottom:20px;">
                <div class="alert-icon"><Icon name="x" :size="14" /></div>
                <div class="alert-body"><div class="alert-desc">رُفضت الحملة من Meta. راجع المحتوى الإبداعي وأعد الإطلاق.</div></div>
            </div>

            <div class="show-grid">

                <!-- ── Left col: insights + creative ── -->
                <div class="stack">

                    <!-- Insights (ADS-08) -->
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>الأداء والإحصائيات</h3>
                                <div class="sub" v-if="campaign.insights_synced_at">
                                    آخر تحديث: {{ formatDate(campaign.insights_synced_at) }}
                                </div>
                            </div>
                            <span :class="statusBadgeClass(campaign.status)">
                                {{ STATUS_LABELS[campaign.status] }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div v-if="!ins || campaign.status === 'draft' || campaign.status === 'pending'" class="insights-empty">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted)"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                <p>ستظهر الإحصائيات بعد تفعيل الحملة</p>
                            </div>
                            <div v-else class="insights-grid">
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmt(ins?.spend, currency + ' ') }}</div>
                                    <div class="insight-label">الإنفاق</div>
                                </div>
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmt(ins?.reach) }}</div>
                                    <div class="insight-label">الوصول</div>
                                </div>
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmt(ins?.impressions) }}</div>
                                    <div class="insight-label">الانطباعات</div>
                                </div>
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmt(ins?.clicks) }}</div>
                                    <div class="insight-label">النقرات</div>
                                </div>
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmtPct(ins?.ctr) }}</div>
                                    <div class="insight-label">معدل النقر CTR</div>
                                </div>
                                <div class="insight-card">
                                    <div class="insight-val">{{ fmt(ins?.cpc, currency + ' ') }}</div>
                                    <div class="insight-label">تكلفة النقرة CPC</div>
                                </div>
                                <div class="insight-card insight-card--wide">
                                    <div class="insight-val">{{ ins?.roas ? ins.roas.toFixed(2) + 'x' : '—' }}</div>
                                    <div class="insight-label">العائد على الإنفاق ROAS</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Creative (ADS-03, ADS-06) -->
                    <div class="card" v-if="campaign.post || campaign.ad_copy || campaign.ad_headline">
                        <div class="card-head"><h3>المحتوى الإبداعي</h3></div>
                        <div class="card-body">
                            <div class="creative-box">
                                <div class="creative-platform">
                                    <span class="platform-dot" :style="campaign.platform === 'instagram' ? 'background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)' : 'background:#1877f2'" />
                                    {{ campaign.platform === 'instagram' ? 'انستجرام' : 'فيسبوك' }}
                                </div>
                                <!-- ad_headline -->
                                <p v-if="campaign.ad_headline" class="creative-headline">{{ campaign.ad_headline }}</p>
                                <!-- ad_copy or linked post -->
                                <p class="creative-text">{{ campaign.ad_copy || campaign.post?.content }}</p>
                                <!-- ad_description -->
                                <p v-if="campaign.ad_description" class="creative-desc">{{ campaign.ad_description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Right col: campaign details ── -->
                <div class="stack">

                    <!-- Campaign info -->
                    <div class="card">
                        <div class="card-head"><h3>تفاصيل الحملة</h3></div>
                        <div class="card-body">
                            <dl class="detail-list">
                                <div class="detail-row">
                                    <dt>الهدف</dt>
                                    <dd>{{ OBJECTIVE_LABELS[campaign.objective] }}</dd>
                                </div>
                                <div class="detail-row">
                                    <dt>المنصة</dt>
                                    <dd>{{ campaign.platform === 'instagram' ? 'انستجرام' : 'فيسبوك' }}</dd>
                                </div>
                                <div class="detail-row">
                                    <dt>الحساب المُعلن</dt>
                                    <dd>{{ campaign.social_account?.account_name ?? '—' }}</dd>
                                </div>
                                <div class="detail-row">
                                    <dt>الميزانية</dt>
                                    <dd>{{ currency }} {{ parseFloat(campaign.budget_amount).toLocaleString('ar') }} / {{ campaign.budget_type === 'daily' ? 'يومياً' : 'إجمالي' }}</dd>
                                </div>
                                <div class="detail-row">
                                    <dt>تاريخ البداية</dt>
                                    <dd>{{ formatDate(campaign.starts_at) }}</dd>
                                </div>
                                <div class="detail-row">
                                    <dt>تاريخ الانتهاء</dt>
                                    <dd>{{ formatDate(campaign.ends_at) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Audience (ADS-04) -->
                    <div class="card">
                        <div class="card-head"><h3>الجمهور المستهدف</h3></div>
                        <div class="card-body stack-sm">
                            <div class="detail-row">
                                <dt class="detail-label">الدول</dt>
                                <dd>
                                    <div class="chips-row">
                                        <span v-for="c in campaign.target_countries" :key="c" class="badge badge-neutral">
                                            {{ { sa:'🇸🇦 السعودية', ae:'🇦🇪 الإمارات', kw:'🇰🇼 الكويت', qa:'🇶🇦 قطر', bh:'🇧🇭 البحرين', om:'🇴🇲 عُمان' }[c] ?? c }}
                                        </span>
                                    </div>
                                </dd>
                            </div>
                            <div class="detail-row">
                                <dt class="detail-label">العمر</dt>
                                <dd>{{ campaign.target_age_min }}–{{ campaign.target_age_max }} سنة</dd>
                            </div>
                            <div class="detail-row">
                                <dt class="detail-label">الجنس</dt>
                                <dd>{{ { all: 'الجميع', male: 'ذكور', female: 'إناث' }[campaign.target_gender] }}</dd>
                            </div>
                            <div v-if="campaign.target_interests?.length" class="detail-row">
                                <dt class="detail-label">الاهتمامات</dt>
                                <dd>
                                    <div class="chips-row">
                                        <span v-for="(interest, i) in campaign.target_interests" :key="i" class="badge badge-neutral">{{ interest }}</span>
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- Meta IDs (shown if active) -->
                    <div v-if="campaign.meta_campaign_id" class="card">
                        <div class="card-head"><h3>معرّفات Meta</h3></div>
                        <div class="card-body">
                            <dl class="detail-list">
                                <div class="detail-row">
                                    <dt>Campaign ID</dt>
                                    <dd class="mono">{{ campaign.meta_campaign_id }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <Modal :show="showDelete" title="تأكيد الحذف" size="sm" @close="showDelete = false">
            <div class="delete-confirm">
                <div class="delete-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6 18.1 20a2 2 0 0 1-2 1.9H7.9a2 2 0 0 1-2-1.9L5 6"/></svg>
                </div>
                <p>هل تريد حذف حملة <strong>{{ campaign.name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <template #footer>
                <button class="btn btn-danger" @click="confirmDelete">حذف الحملة</button>
                <button class="btn btn-secondary" @click="showDelete = false">إلغاء</button>
            </template>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.show-wrap { max-width: 1100px; margin: 0 auto; }

/* Page header */
.page-hd {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 20px; flex-wrap: wrap;
}
.back-link {
    display: inline-flex; align-items: center; gap: 5px;
    color: var(--text-muted); text-decoration: none;
    font-size: 13px; font-weight: 500; transition: color .15s;
}
.back-link:hover { color: var(--text-primary); }
.back-link svg { transform: scaleX(-1); }
.page-hd-sep  { color: var(--border-default); font-size: 14px; }
.page-hd-title { font-size: 16px; font-weight: 700; color: var(--text-primary); flex: 1; }
.hd-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-right: auto; }

/* Layout */
.show-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 860px) { .show-grid { grid-template-columns: 1fr; } }

/* Insights */
.insights-empty {
    display: flex; flex-direction: column; align-items: center;
    gap: 10px; padding: 32px 0; text-align: center;
    color: var(--text-muted); font-size: 13px;
}
.insights-empty p { margin: 0; }

.insights-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.insight-card {
    background: var(--bg-muted);
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}
.insight-card--wide { grid-column: span 3; }
.insight-val   { font-size: 20px; font-weight: 700; color: var(--text-primary); }
.insight-label { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

/* Creative box */
.creative-box {
    background: var(--bg-muted);
    border-radius: 10px;
    padding: 16px;
}
.creative-platform {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 600; color: var(--text-muted);
    margin-bottom: 10px;
}
.platform-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.creative-headline { margin: 0 0 6px; font-size: 15px; font-weight: 700; color: var(--text-primary); }
.creative-text     { margin: 0; font-size: 14px; line-height: 1.7; color: var(--text-primary); white-space: pre-line; }
.creative-desc     { margin: 8px 0 0; font-size: 12px; color: var(--text-muted); line-height: 1.5; }

/* Detail list */
.detail-list { display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0; }
.detail-row  { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.detail-row dt, .detail-label { font-size: 13px; color: var(--text-muted); font-weight: 500; flex-shrink: 0; }
.detail-row dd { font-size: 13px; color: var(--text-primary); font-weight: 600; text-align: left; margin: 0; }
.mono { font-family: monospace; font-size: 12px; color: var(--text-muted); }
.chips-row { display: flex; flex-wrap: wrap; gap: 6px; }

/* Delete modal */
.delete-confirm { display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center; padding: 8px 0; }
.delete-icon {
    width: 56px; height: 56px; border-radius: 50%;
    background: color-mix(in oklab, var(--error) 12%, transparent);
    color: var(--error); display: grid; place-items: center;
}
.delete-confirm p { margin: 0; font-size: 14px; color: var(--text-primary); line-height: 1.6; }
</style>

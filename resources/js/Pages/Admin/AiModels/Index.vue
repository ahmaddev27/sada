<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface ModelRow {
    provider: string
    ai_model: string
    requests: number
    total_input_tokens: number
    total_output_tokens: number
    total_tokens: number
    total_cost_usd: number
    total_cost_sar: number
}

interface TrendRow {
    date: string
    provider: string
    requests: number
    cost_usd: number
}

interface Totals {
    requests: number
    total_tokens: number
    total_cost_usd: number
    total_cost_sar: number
}

const props = defineProps<{
    models: ModelRow[]
    totals: Totals
    trend: TrendRow[]
    period: string
    usdToSar: number
}>()

const period = ref(props.period)

function changePeriod(p: string) {
    period.value = p
    router.get('/admin/ai-models', { period: p }, { preserveState: true, replace: true })
}

const PERIOD_LABELS: Record<string, string> = {
    '1': 'اليوم', '7': '٧ أيام', '30': '٣٠ يوماً', '90': '٩٠ يوماً', 'all': 'الكل',
}

const PROVIDER_META: Record<string, { label: string, cls: string }> = {
    anthropic: { label: 'Anthropic', cls: 'pv--anthropic' },
    openai:    { label: 'OpenAI',    cls: 'pv--openai'    },
    gemini:    { label: 'Google',    cls: 'pv--gemini'    },
    groq:      { label: 'Groq',      cls: 'pv--groq'      },
    unknown:   { label: 'غير محدد',  cls: 'pv--unknown'   },
}

function fmtNum(n: number): string {
    return n.toLocaleString('ar-SA')
}

function fmtTokens(n: number): string {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(2) + 'M'
    if (n >= 1_000)     return (n / 1_000).toFixed(1) + 'K'
    return String(n)
}

function fmtUsd(n: number): string {
    return '$' + n.toFixed(4)
}

function fmtSar(n: number): string {
    return n.toFixed(2) + ' ر.س'
}

// Breakdown by provider for KPI cards
const providerBreakdown = computed(() => {
    const map: Record<string, { requests: number, cost_usd: number }> = {}
    props.models.forEach(m => {
        const pv = m.provider ?? 'unknown'
        if (!map[pv]) map[pv] = { requests: 0, cost_usd: 0 }
        map[pv].requests  += m.requests
        map[pv].cost_usd  += m.total_cost_usd
    })
    return Object.entries(map).map(([provider, v]) => ({
        provider,
        ...v,
        cost_sar: v.cost_usd * props.usdToSar,
    })).sort((a, b) => b.requests - a.requests)
})

// Sort
const sortKey = ref<keyof ModelRow>('requests')
const sortDir = ref<'asc' | 'desc'>('desc')

function toggleSort(key: keyof ModelRow) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'desc' ? 'asc' : 'desc'
    } else {
        sortKey.value = key
        sortDir.value = 'desc'
    }
}

const sortedModels = computed(() => {
    return [...props.models].sort((a, b) => {
        const av = a[sortKey.value] as number
        const bv = b[sortKey.value] as number
        return sortDir.value === 'desc' ? bv - av : av - bv
    })
})
</script>

<template>
    <AdminLayout title="أداء الموديلات">

        <!-- Page head -->
        <div class="page-head">
            <div>
                <div class="page-title-row">
                    <div class="page-title-icon">
                        <Icon name="cpu" :size="18" />
                    </div>
                    <h1 class="page-title">أداء الموديلات</h1>
                </div>
                <p class="page-sub">تكلفة واستخدام كل موديل ذكاء اصطناعي</p>
            </div>
            <!-- Period filter -->
            <div class="segmented">
                <button
                    v-for="(lbl, key) in PERIOD_LABELS"
                    :key="key"
                    :data-active="period === key"
                    @click="changePeriod(key)"
                >{{ lbl }}</button>
            </div>
        </div>

        <!-- KPI row -->
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon--blue">
                    <Icon name="sparkle" :size="18" />
                </div>
                <div>
                    <div class="kpi-value">{{ fmtNum(totals.requests) }}</div>
                    <div class="kpi-label">إجمالي الطلبات</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon--green">
                    <Icon name="cpu" :size="18" />
                </div>
                <div>
                    <div class="kpi-value">{{ fmtTokens(totals.total_tokens) }}</div>
                    <div class="kpi-label">إجمالي التوكنات</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon--amber">
                    <Icon name="credit-card" :size="18" />
                </div>
                <div>
                    <div class="kpi-value">{{ fmtUsd(totals.total_cost_usd) }}</div>
                    <div class="kpi-label">إجمالي التكلفة USD</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon--sand">
                    <Icon name="coins" :size="18" />
                </div>
                <div>
                    <div class="kpi-value">{{ fmtSar(totals.total_cost_sar) }}</div>
                    <div class="kpi-label">إجمالي التكلفة SAR</div>
                </div>
            </div>
        </div>

        <!-- Provider breakdown pills -->
        <div class="card" style="padding:20px 24px;">
            <div class="section-label" style="margin-bottom:14px;">التوزيع حسب المزوّد</div>
            <div class="provider-grid">
                <div v-for="pv in providerBreakdown" :key="pv.provider" class="provider-card">
                    <span :class="['pv-badge', PROVIDER_META[pv.provider]?.cls ?? 'pv--unknown']">
                        {{ PROVIDER_META[pv.provider]?.label ?? pv.provider }}
                    </span>
                    <div class="pv-stat">{{ fmtNum(pv.requests) }} <span>طلب</span></div>
                    <div class="pv-cost">{{ fmtUsd(pv.cost_usd) }} · {{ fmtSar(pv.cost_sar) }}</div>
                </div>
                <div v-if="!providerBreakdown.length" style="color:var(--text-muted);font-size:13px;">
                    لا توجد بيانات في هذه الفترة
                </div>
            </div>
        </div>

        <!-- Models table -->
        <div class="card" style="overflow:hidden;">
            <div class="table-head-bar">
                <div class="section-label">تفاصيل الموديلات</div>
                <span class="badge badge-neutral">{{ models.length }} موديل</span>
            </div>

            <div v-if="!models.length" class="empty-state">
                <Icon name="cpu" :size="32" style="color:var(--text-faint)" />
                <p>لا توجد بيانات في هذه الفترة</p>
            </div>

            <table v-else class="data-table">
                <thead>
                    <tr>
                        <th>المزوّد / الموديل</th>
                        <th class="sortable" @click="toggleSort('requests')">
                            الطلبات
                            <Icon :name="sortKey === 'requests' && sortDir === 'asc' ? 'chevronDown' : 'chevronLeft'" :size="12" />
                        </th>
                        <th class="sortable" @click="toggleSort('total_input_tokens')">
                            Input tokens
                            <Icon :name="sortKey === 'total_input_tokens' && sortDir === 'asc' ? 'chevronDown' : 'chevronLeft'" :size="12" />
                        </th>
                        <th class="sortable" @click="toggleSort('total_output_tokens')">
                            Output tokens
                            <Icon :name="sortKey === 'total_output_tokens' && sortDir === 'asc' ? 'chevronDown' : 'chevronLeft'" :size="12" />
                        </th>
                        <th class="sortable" @click="toggleSort('total_tokens')">
                            المجموع
                            <Icon :name="sortKey === 'total_tokens' && sortDir === 'asc' ? 'chevronDown' : 'chevronLeft'" :size="12" />
                        </th>
                        <th class="sortable" @click="toggleSort('total_cost_usd')">
                            التكلفة USD
                            <Icon :name="sortKey === 'total_cost_usd' && sortDir === 'asc' ? 'chevronDown' : 'chevronLeft'" :size="12" />
                        </th>
                        <th>التكلفة SAR</th>
                        <th>متوسط / طلب</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="m in sortedModels" :key="`${m.provider}-${m.ai_model}`">
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <span :class="['pv-badge', PROVIDER_META[m.provider]?.cls ?? 'pv--unknown']">
                                    {{ PROVIDER_META[m.provider]?.label ?? m.provider }}
                                </span>
                                <span style="font-size:12px;color:var(--text-secondary);font-family:monospace;direction:ltr;text-align:right;">
                                    {{ m.ai_model }}
                                </span>
                            </div>
                        </td>
                        <td><strong>{{ fmtNum(m.requests) }}</strong></td>
                        <td>{{ fmtTokens(m.total_input_tokens) }}</td>
                        <td>{{ fmtTokens(m.total_output_tokens) }}</td>
                        <td><strong>{{ fmtTokens(m.total_tokens) }}</strong></td>
                        <td class="cost-cell">{{ fmtUsd(m.total_cost_usd) }}</td>
                        <td class="cost-cell">{{ fmtSar(m.total_cost_sar) }}</td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            {{ m.requests > 0 ? fmtUsd(m.total_cost_usd / m.requests) : '—' }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>الإجمالي</strong></td>
                        <td><strong>{{ fmtNum(totals.requests) }}</strong></td>
                        <td colspan="2"></td>
                        <td><strong>{{ fmtTokens(totals.total_tokens) }}</strong></td>
                        <td class="cost-cell"><strong>{{ fmtUsd(totals.total_cost_usd) }}</strong></td>
                        <td class="cost-cell"><strong>{{ fmtSar(totals.total_cost_sar) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </AdminLayout>
</template>

<style scoped>
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.page-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}
.page-title-icon {
    width: 36px; height: 36px;
    border-radius: var(--radius-md);
    background: color-mix(in oklab, var(--primary, #0F6F5C) 12%, transparent);
    color: var(--primary, #0F6F5C);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.page-title { font-size: 22px; font-weight: 800; margin: 0; }
.page-sub   { font-size: 13px; color: var(--text-muted); margin: 0; }

/* KPI */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
@media (max-width: 900px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } }

.kpi-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.kpi-icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.kpi-icon--blue   { background: #EFF6FF; color: #2563EB; }
.kpi-icon--green  { background: #F0FDF4; color: var(--primary); }
.kpi-icon--amber  { background: #FFFBEB; color: #D97706; }
.kpi-icon--sand   { background: #FEF3E2; color: var(--accent); }

[data-theme="dark"] .kpi-icon--blue  { background: rgba(37,99,235,.15); }
[data-theme="dark"] .kpi-icon--green { background: rgba(15,111,92,.15); }
[data-theme="dark"] .kpi-icon--amber { background: rgba(217,119,6,.15); }
[data-theme="dark"] .kpi-icon--sand  { background: rgba(200,150,95,.15); }

.kpi-value { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kpi-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

/* Provider breakdown */
.provider-grid {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.provider-card {
    background: var(--bg-muted);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    padding: 14px 18px;
    min-width: 160px;
}
.pv-stat  { font-size: 20px; font-weight: 700; margin-top: 8px; }
.pv-stat span { font-size: 12px; font-weight: 400; color: var(--text-muted); }
.pv-cost  { font-size: 12px; color: var(--text-muted); margin-top: 4px; direction: ltr; text-align: right; }

/* Provider badges */
.pv-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: var(--radius-full);
    font-size: 11px;
    font-weight: 600;
}
.pv--anthropic { background: #FEF3E2; color: #9A3412; }
.pv--openai    { background: #F0FDF4; color: #166534; }
.pv--gemini    { background: #EFF6FF; color: #1D4ED8; }
.pv--groq      { background: #F5F3FF; color: #6D28D9; }
.pv--unknown   { background: var(--bg-muted); color: var(--text-muted); }

[data-theme="dark"] .pv--anthropic { background: rgba(154,52,18,.2); color: #FB923C; }
[data-theme="dark"] .pv--openai    { background: rgba(22,101,52,.2); color: #4ADE80; }
[data-theme="dark"] .pv--gemini    { background: rgba(29,78,216,.2); color: #60A5FA; }
[data-theme="dark"] .pv--groq      { background: rgba(109,40,217,.2); color: #A78BFA; }

/* Table */
.table-head-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
}
.section-label { font-size: 13px; font-weight: 700; color: var(--text-primary); }

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table th {
    padding: 10px 16px;
    text-align: right;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
    background: var(--bg-muted);
    border-bottom: 1px solid var(--border-subtle);
    white-space: nowrap;
}
.data-table th.sortable {
    cursor: pointer;
    user-select: none;
    display: table-cell;
}
.data-table th.sortable:hover { color: var(--text-primary); }

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    vertical-align: middle;
}
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: var(--bg-muted); }

.data-table tfoot td {
    padding: 12px 16px;
    border-top: 2px solid var(--border-default);
    background: var(--bg-muted);
}

.cost-cell {
    font-family: monospace;
    direction: ltr;
    text-align: right;
    color: var(--text-primary);
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: var(--text-muted);
}
.empty-state p { margin-top: 12px; font-size: 14px; }
</style>

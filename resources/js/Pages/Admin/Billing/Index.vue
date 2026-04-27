<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Invoice {
    id: number
    invoice_number: string
    amount: number
    vat_amount: number
    total_amount: number
    currency: string
    status: string
    payment_gateway: string
    tokens_purchased: number
    paid_at: string | null
    created_at: string
    user: { id: number; name: string; email: string } | null
}

interface Package {
    id: number
    name: string
    tokens: number
    price: string
    currency: string
    is_popular: boolean
    is_active: boolean
}

const props = defineProps<{
    invoices: { data: Invoice[]; links: any[]; meta: any }
    filters: { search?: string; status?: string; gateway?: string }
    stats: {
        total_revenue: number
        this_month: number
        this_year: number
        total_invoices: number
        paid_invoices: number
        pending_invoices: number
    }
    revenueChart: { month: string; total: number; count: number }[]
    packages: Package[]
}>()

const search   = ref(props.filters.search  ?? '')
const status   = ref(props.filters.status  ?? '')
const gateway  = ref(props.filters.gateway ?? '')

function applyFilters() {
    router.get('/admin/billing', { search: search.value, status: status.value, gateway: gateway.value }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value  = ''
    status.value  = ''
    gateway.value = ''
    applyFilters()
}

const statusMeta: Record<string, { label: string; cls: string }> = {
    paid:     { label: 'مدفوعة',    cls: 'pill--green'  },
    pending:  { label: 'معلّقة',    cls: 'pill--yellow' },
    failed:   { label: 'فاشلة',     cls: 'pill--red'    },
    refunded: { label: 'مستردّة',   cls: 'pill--gray'   },
}

const gatewayMeta: Record<string, { label: string; cls: string }> = {
    moyasar: { label: 'Moyasar', cls: 'pill--blue'   },
    tap:     { label: 'Tap',     cls: 'pill--purple' },
}

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA', { maximumFractionDigits: 0 }).format(n)
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الفواتير والإيرادات</h1>
                    <p class="page-subtitle">متابعة المدفوعات والإيرادات من الباقات</p>
                </div>
            </div>

            <!-- KPI row -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_revenue) }} ر.س</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#0F6F5C 12%,transparent);color:#0F6F5C">
                            <Icon name="credit-card" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">إجمالي الإيرادات</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.this_month) }} ر.س</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#C8965F 12%,transparent);color:#C8965F">
                            <Icon name="chart" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">هذا الشهر</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.paid_invoices) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#22c55e 12%,transparent);color:#22c55e">
                            <Icon name="check" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">فواتير مدفوعة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.pending_invoices) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#f59e0b 12%,transparent);color:#f59e0b">
                            <Icon name="clock" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">فواتير معلّقة</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" class="inp" placeholder="بحث برقم الفاتورة أو البريد..." @keyup.enter="applyFilters" />
                <select v-model="status" class="inp inp--sm" @change="applyFilters">
                    <option value="">كل الحالات</option>
                    <option value="paid">مدفوعة</option>
                    <option value="pending">معلّقة</option>
                    <option value="failed">فاشلة</option>
                    <option value="refunded">مستردّة</option>
                </select>
                <select v-model="gateway" class="inp inp--sm" @change="applyFilters">
                    <option value="">كل البوابات</option>
                    <option value="moyasar">Moyasar</option>
                    <option value="tap">Tap</option>
                </select>
                <button class="btn btn-ghost btn--sm" @click="resetFilters">إعادة تعيين</button>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المستخدم</th>
                            <th>المبلغ</th>
                            <th>الرصيد</th>
                            <th>البوابة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="invoices.data.length === 0">
                            <td colspan="7" class="empty-row">لا توجد فواتير</td>
                        </tr>
                        <tr v-for="inv in invoices.data" :key="inv.id">
                            <td class="mono">{{ inv.invoice_number }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-name">{{ inv.user?.name ?? '—' }}</div>
                                    <div class="user-email">{{ inv.user?.email ?? '' }}</div>
                                </div>
                            </td>
                            <td class="amount-cell">{{ fmt(inv.total_amount) }} {{ inv.currency }}</td>
                            <td>{{ fmt(inv.tokens_purchased) }} رصيد</td>
                            <td>
                                <span :class="['pill', gatewayMeta[inv.payment_gateway]?.cls ?? 'pill--gray']">
                                    {{ gatewayMeta[inv.payment_gateway]?.label ?? inv.payment_gateway }}
                                </span>
                            </td>
                            <td>
                                <span :class="['pill', statusMeta[inv.status]?.cls ?? 'pill--gray']">
                                    {{ statusMeta[inv.status]?.label ?? inv.status }}
                                </span>
                            </td>
                            <td class="date-cell">{{ inv.paid_at ? new Date(inv.paid_at).toLocaleDateString('ar-SA') : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.meta?.last_page > 1" class="pagination">
                <button
                    v-for="link in invoices.links"
                    :key="link.label"
                    :disabled="!link.url"
                    :class="['page-btn', { active: link.active }]"
                    @click="link.url && router.visit(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 18px 20px; display: flex; flex-direction: column; gap: 8px; }
.kpi-top { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.kpi-value { font-size: 22px; font-weight: 700; color: var(--text-primary); }
.kpi-icon { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }

.filters-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.inp { background: var(--bg-card); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 8px 12px; font-size: 13px; color: var(--text-primary); font-family: var(--font-arabic); outline: none; }
.inp:focus { border-color: var(--primary); }
.inp--sm { padding: 8px 10px; }

.table-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { background: var(--bg-muted); color: var(--text-muted); font-weight: 600; padding: 10px 16px; text-align: right; border-bottom: 1px solid var(--border-subtle); font-size: 12px; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--bg-muted); }
.empty-row { text-align: center; color: var(--text-muted); padding: 32px !important; }

.user-cell { display: flex; flex-direction: column; gap: 2px; }
.user-name  { font-weight: 600; font-size: 13px; }
.user-email { font-size: 11px; color: var(--text-muted); direction: ltr; text-align: right; }
.amount-cell { font-weight: 700; color: var(--primary); }
.mono { font-family: monospace; font-size: 12px; }
.date-cell { font-size: 12px; color: var(--text-muted); }

.pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.pill--green  { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.pill--yellow { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.pill--red    { background: color-mix(in oklab, #ef4444 12%, transparent); color: #dc2626; }
.pill--gray   { background: color-mix(in oklab, #6b7280 12%, transparent); color: #6b7280; }
.pill--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #2563eb; }
.pill--purple { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #7c3aed; }

.pagination { display: flex; align-items: center; gap: 6px; margin-top: 16px; justify-content: center; flex-wrap: wrap; }
.page-btn { padding: 6px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-default); background: var(--bg-card); color: var(--text-primary); font-size: 13px; cursor: pointer; transition: background var(--dur-fast); }
.page-btn:disabled { opacity: 0.4; cursor: default; }
.page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn:hover:not(:disabled):not(.active) { background: var(--bg-muted); }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast), color var(--dur-fast); }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover { background: var(--bg-muted); color: var(--text-primary); }
.btn--sm { padding: 7px 12px; font-size: 12px; }
</style>

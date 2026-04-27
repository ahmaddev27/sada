<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Transaction {
    id: number
    type: string
    amount: number
    balance_after: number
    description: string | null
    reference_type: string | null
    created_at: string
    user: { id: number; name: string; email: string } | null
}

const props = defineProps<{
    transactions: { data: Transaction[]; links: any[]; meta: any }
    filters: { search?: string; type?: string }
    stats: {
        total_transactions: number
        total_granted: number
        total_deducted: number
        total_purchased: number
        today_transactions: number
    }
    volumeChart: { date: string; type: string; total: number; count: number }[]
}>()

const search = ref(props.filters.search ?? '')
const type   = ref(props.filters.type   ?? '')

function applyFilters() {
    router.get('/admin/tokens', { search: search.value, type: type.value }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    type.value   = ''
    applyFilters()
}

const typeMeta: Record<string, { label: string; cls: string; sign: string }> = {
    grant:    { label: 'منحة',    cls: 'pill--green',  sign: '+' },
    deduct:   { label: 'خصم',     cls: 'pill--red',    sign: '−' },
    purchase: { label: 'شراء',    cls: 'pill--blue',   sign: '+' },
    refund:   { label: 'استرداد', cls: 'pill--yellow', sign: '+' },
    expire:   { label: 'انتهاء',  cls: 'pill--gray',   sign: '−' },
}

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(Math.abs(n))
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <div>
                    <h1 class="page-title">سجل الرصيد</h1>
                    <p class="page-subtitle">تدقيق كامل لحركات رصيد المستخدمين</p>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_transactions) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#0F6F5C 12%,transparent);color:#0F6F5C">
                            <Icon name="coins" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">إجمالي الحركات</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_purchased) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#3b82f6 12%,transparent);color:#3b82f6">
                            <Icon name="credit-card" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">رصيد مشترى</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_granted) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#22c55e 12%,transparent);color:#22c55e">
                            <Icon name="plus" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">رصيد ممنوح</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_deducted) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#ef4444 12%,transparent);color:#ef4444">
                            <Icon name="sparkle" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">رصيد مستهلك بالذكاء</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" class="inp" placeholder="بحث بالاسم أو البريد..." @keyup.enter="applyFilters" />
                <select v-model="type" class="inp inp--sm" @change="applyFilters">
                    <option value="">كل الأنواع</option>
                    <option value="grant">منحة</option>
                    <option value="deduct">خصم</option>
                    <option value="purchase">شراء</option>
                    <option value="refund">استرداد</option>
                    <option value="expire">انتهاء صلاحية</option>
                </select>
                <button class="btn btn-ghost btn--sm" @click="resetFilters">إعادة تعيين</button>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>الرصيد بعد</th>
                            <th>الوصف</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="transactions.data.length === 0">
                            <td colspan="7" class="empty-row">لا توجد حركات</td>
                        </tr>
                        <tr v-for="tx in transactions.data" :key="tx.id">
                            <td class="id-cell">{{ tx.id }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-name">{{ tx.user?.name ?? '—' }}</div>
                                    <div class="user-email">{{ tx.user?.email ?? '' }}</div>
                                </div>
                            </td>
                            <td>
                                <span :class="['pill', typeMeta[tx.type]?.cls ?? 'pill--gray']">
                                    {{ typeMeta[tx.type]?.label ?? tx.type }}
                                </span>
                            </td>
                            <td :class="['amount', tx.type === 'deduct' || tx.type === 'expire' ? 'amount--neg' : 'amount--pos']">
                                {{ typeMeta[tx.type]?.sign ?? '' }}{{ fmt(tx.amount) }}
                            </td>
                            <td class="balance-cell">{{ fmt(tx.balance_after) }}</td>
                            <td class="desc-cell">{{ tx.description ?? '—' }}</td>
                            <td class="date-cell">{{ new Date(tx.created_at).toLocaleDateString('ar-SA') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.meta?.last_page > 1" class="pagination">
                <button
                    v-for="link in transactions.links"
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
.admin-page { padding: 28px 32px; max-width: 1200px; }
.page-header { margin-bottom: 24px; }
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

.id-cell { font-size: 11px; color: var(--text-muted); }
.user-cell { display: flex; flex-direction: column; gap: 2px; }
.user-name  { font-weight: 600; font-size: 13px; }
.user-email { font-size: 11px; color: var(--text-muted); direction: ltr; text-align: right; }
.amount { font-weight: 700; font-size: 14px; }
.amount--pos { color: #16a34a; }
.amount--neg { color: #dc2626; }
.balance-cell { font-size: 13px; color: var(--text-muted); }
.desc-cell { font-size: 12px; color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.date-cell { font-size: 12px; color: var(--text-muted); }

.pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.pill--green  { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.pill--yellow { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.pill--red    { background: color-mix(in oklab, #ef4444 12%, transparent); color: #dc2626; }
.pill--gray   { background: color-mix(in oklab, #6b7280 12%, transparent); color: #6b7280; }
.pill--blue   { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #2563eb; }

.pagination { display: flex; gap: 6px; margin-top: 16px; justify-content: center; flex-wrap: wrap; }
.page-btn { padding: 6px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-default); background: var(--bg-card); color: var(--text-primary); font-size: 13px; cursor: pointer; transition: background var(--dur-fast); }
.page-btn:disabled { opacity: 0.4; cursor: default; }
.page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn:hover:not(:disabled):not(.active) { background: var(--bg-muted); }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast); }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover { background: var(--bg-muted); color: var(--text-primary); }
.btn--sm { padding: 7px 12px; font-size: 12px; }
</style>

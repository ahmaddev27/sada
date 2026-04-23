<script setup lang="ts">
// BIL-01 · BIL-02 · BIL-03 · BIL-04 · BIL-07
import { ref, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Modal from '@/Components/Base/Modal.vue'
import Icon from '@/Components/Base/Icon.vue'
import type { PageProps } from '@/Types'

// ── Prop interfaces ────────────────────────────────────────────────────────
interface TokenPackage {
    id: number
    name: string
    name_en: string
    tokens: number
    price: number
    currency: string
    is_popular: boolean
}

interface TokenTransaction {
    id: number
    type: 'purchase' | 'deduction' | 'refund' | 'bonus' | 'expiry'
    amount: number
    balance_after: number
    description: string
    created_at: string
}

interface Invoice {
    id: number
    invoice_number: string
    amount: number
    vat_amount: number
    total_amount: number
    currency: string
    status: 'pending' | 'paid' | 'cancelled'
    tokens_purchased: number
    paid_at: string | null
    created_at: string
}

const props = defineProps<{
    packages: TokenPackage[]
    balance: number
    transactions: { data: TokenTransaction[]; links: any[]; meta: any }
    invoices: Invoice[]
}>()

// ── Auth user (BIL-03) ─────────────────────────────────────────────────────
const page = usePage<PageProps>()
const user = computed(() => page.props.auth.user)

// ── Low balance threshold ─────────────────────────────────────────────────
const isLowBalance = computed(() => props.balance < 100)

// ── Scroll to packages ────────────────────────────────────────────────────
function scrollToPackages() {
    document.getElementById('packages-section')?.scrollIntoView({ behavior: 'smooth' })
}

// ── Checkout modal (BIL-01) ───────────────────────────────────────────────
const checkoutOpen    = ref(false)
const selectedPackage = ref<TokenPackage | null>(null)
const paymentMethod   = ref<'card' | 'apple_pay' | 'stc_pay'>('card')
const paying          = ref(false)
const checkoutError   = ref<string | null>(null)

function openCheckout(pkg: TokenPackage) {
    selectedPackage.value = pkg
    paymentMethod.value   = 'card'
    checkoutError.value   = null
    checkoutOpen.value    = true
}

function closeCheckout() {
    checkoutOpen.value    = false
    selectedPackage.value = null
    checkoutError.value   = null
    paying.value          = false
}

const PAYMENT_METHODS = [
    { key: 'card'      as const, label: 'بطاقة بنكية' },
    { key: 'apple_pay' as const, label: 'Apple Pay'   },
    { key: 'stc_pay'   as const, label: 'STC Pay'     },
]

async function pay() {
    paying.value = true
    checkoutError.value = null
    try {
        const resp = await fetch('/billing/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({
                package_id:     selectedPackage.value?.id,
                payment_method: paymentMethod.value,
            }),
        })
        const data = await resp.json()
        if (data.payment_url) {
            window.location.href = data.payment_url
        } else {
            checkoutError.value = 'حدث خطأ في بدء الدفع. حاول مرة أخرى.'
        }
    } catch {
        checkoutError.value = 'حدث خطأ في الاتصال. حاول مرة أخرى.'
    } finally {
        paying.value = false
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────
function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('ar-SA', {
        year:  'numeric',
        month: 'short',
        day:   'numeric',
    })
}

function formatNumber(n: number): string {
    return n.toLocaleString('ar-SA')
}

function formatCurrency(amount: number, currency: string): string {
    return `${amount.toLocaleString('ar-SA')} ${currency}`
}

// Transaction helpers
const TRANSACTION_TYPE_LABELS: Record<TokenTransaction['type'], string> = {
    purchase:  'شراء',
    deduction: 'خصم',
    refund:    'استرداد',
    bonus:     'مكافأة',
    expiry:    'انتهاء صلاحية',
}

function txBadgeClass(type: TokenTransaction['type']): string {
    switch (type) {
        case 'purchase':
        case 'bonus':
        case 'refund':
            return 'badge badge-brand'
        case 'expiry':
        case 'deduction':
            return 'badge badge-error'
        default:
            return 'badge badge-neutral'
    }
}

function isPositiveTx(type: TokenTransaction['type']): boolean {
    return type === 'purchase' || type === 'bonus' || type === 'refund'
}

// Invoice helpers
const INVOICE_STATUS_LABELS: Record<Invoice['status'], string> = {
    paid:      'مدفوعة',
    pending:   'معلقة',
    cancelled: 'ملغاة',
}

function invoiceStatusClass(status: Invoice['status']): string {
    switch (status) {
        case 'paid':      return 'badge badge-brand'
        case 'pending':   return 'badge badge-neutral'
        case 'cancelled': return 'badge badge-error'
    }
}

// Pagination
const prevLink = computed(() => props.transactions.links.find((l: any) => l.label === '&laquo; Previous' || l.label === '‹') ?? null)
const nextLink = computed(() => props.transactions.links.find((l: any) => l.label === 'Next &raquo;'    || l.label === '›') ?? null)
</script>

<template>
    <AppLayout title="الفوترة والتوكنز" :crumbs="['الرئيسية', 'الفوترة']">
        <div class="billing-page">

            <!-- ── 1. Balance header card (BIL-03, BIL-04) ──────────────── -->
            <div class="balance-card">
                <div class="balance-card-inner">
                    <div class="balance-left">
                        <div class="balance-amount">{{ formatNumber(balance) }}</div>
                        <div class="balance-label">رصيد التوكنز المتاح</div>
                        <div class="balance-hint">كل توكن = جيل محتوى واحد</div>
                    </div>
                    <button class="btn btn-recharge" @click="scrollToPackages">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M12 5v14M5 12h14"/></svg>
                        شحن الرصيد
                    </button>
                </div>

                <!-- Low balance warning -->
                <div v-if="isLowBalance" class="balance-alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>رصيدك منخفض! أعد الشحن لمواصلة التوليد</span>
                </div>
            </div>

            <!-- ── 2. Token Packages (BIL-01) ────────────────────────────── -->
            <div id="packages-section" class="section">
                <div class="section-head">
                    <h2 class="section-title">باقات التوكنز</h2>
                    <p class="section-sub">اختر الباقة المناسبة لاحتياجاتك</p>
                </div>

                <div class="packages-grid">
                    <div
                        v-for="pkg in packages"
                        :key="pkg.id"
                        class="pkg-card"
                        :class="{ 'pkg-card--popular': pkg.is_popular }"
                    >
                        <div v-if="pkg.is_popular" class="pkg-popular-badge">
                            <Icon name="sparkle" :size="11" />
                            الأكثر مبيعاً
                        </div>

                        <div class="pkg-name">{{ pkg.name }}</div>

                        <div class="pkg-tokens">
                            <span class="pkg-tokens-num">{{ formatNumber(pkg.tokens) }}</span>
                            <span class="pkg-tokens-label">توكن</span>
                        </div>

                        <div class="pkg-price">
                            {{ pkg.price.toLocaleString('ar-SA') }}
                            <span class="pkg-currency">{{ pkg.currency }}</span>
                        </div>

                        <button class="btn btn-primary btn-block" @click="openCheckout(pkg)">
                            اشتراء الآن
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── 3. Recent Transactions (BIL-02) ─────────────────────── -->
            <div class="card section">
                <div class="card-head">
                    <h3>المعاملات الأخيرة</h3>
                </div>
                <div class="card-body" style="padding:0;">

                    <!-- Empty state -->
                    <div v-if="!transactions.data.length" class="tx-empty">
                        <Icon name="credit" :size="28" />
                        <span>لا توجد معاملات بعد</span>
                    </div>

                    <!-- Table -->
                    <div v-else class="tx-table-wrap">
                        <table class="tx-table">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الوصف</th>
                                    <th>المبلغ</th>
                                    <th>الرصيد بعد</th>
                                    <th>النوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in transactions.data" :key="tx.id">
                                    <td class="tx-date">{{ formatDate(tx.created_at) }}</td>
                                    <td class="tx-desc">{{ tx.description }}</td>
                                    <td class="tx-amount" :class="isPositiveTx(tx.type) ? 'tx-amount--pos' : 'tx-amount--neg'">
                                        {{ isPositiveTx(tx.type) ? '+' : '-' }}{{ formatNumber(Math.abs(tx.amount)) }}
                                    </td>
                                    <td class="tx-balance">{{ formatNumber(tx.balance_after) }}</td>
                                    <td>
                                        <span :class="txBadgeClass(tx.type)">
                                            {{ TRANSACTION_TYPE_LABELS[tx.type] }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.meta?.last_page > 1" class="tx-pagination">
                        <button
                            class="btn btn-ghost btn-sm"
                            :disabled="!prevLink?.url"
                            @click="prevLink?.url && router.get(prevLink.url)"
                        >
                            <Icon name="chevronRight" :size="14" />
                            السابق
                        </button>
                        <span class="tx-pagination-info">
                            صفحة {{ transactions.meta.current_page }} من {{ transactions.meta.last_page }}
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

            <!-- ── 4. Invoices (BIL-07) ───────────────────────────────────── -->
            <div class="card section">
                <div class="card-head">
                    <h3>الفواتير</h3>
                </div>
                <div class="card-body" style="padding:0;">

                    <!-- Empty state -->
                    <div v-if="!invoices.length" class="tx-empty">
                        <Icon name="archive" :size="28" />
                        <span>لا توجد فواتير بعد</span>
                    </div>

                    <!-- Invoice list -->
                    <div v-else class="inv-list">
                        <div
                            v-for="invoice in invoices"
                            :key="invoice.id"
                            class="inv-row"
                        >
                            <!-- Right side: number + date -->
                            <div class="inv-main">
                                <div class="inv-number">{{ invoice.invoice_number }}</div>
                                <div class="inv-date">{{ formatDate(invoice.created_at) }}</div>
                            </div>

                            <!-- Tokens purchased -->
                            <div class="inv-tokens">
                                <span class="inv-tokens-count">{{ formatNumber(invoice.tokens_purchased) }}</span>
                                <span class="inv-tokens-label">توكن</span>
                            </div>

                            <!-- Amounts -->
                            <div class="inv-amounts">
                                <div class="inv-amount-row">
                                    <span class="inv-amount-label">المبلغ</span>
                                    <span class="inv-amount-val">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                                </div>
                                <div class="inv-amount-row">
                                    <span class="inv-amount-label">ضريبة القيمة المضافة</span>
                                    <span class="inv-amount-val">{{ formatCurrency(invoice.vat_amount, invoice.currency) }}</span>
                                </div>
                                <div class="inv-amount-row inv-amount-row--total">
                                    <span class="inv-amount-label">الإجمالي</span>
                                    <span class="inv-amount-val">{{ formatCurrency(invoice.total_amount, invoice.currency) }}</span>
                                </div>
                            </div>

                            <!-- Status + actions -->
                            <div class="inv-actions">
                                <span :class="invoiceStatusClass(invoice.status)">
                                    {{ INVOICE_STATUS_LABELS[invoice.status] }}
                                </span>
                                <a
                                    v-if="invoice.status === 'paid'"
                                    :href="`/billing/invoices/${invoice.id}/download`"
                                    target="_blank"
                                    class="btn btn-secondary btn-sm"
                                >
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    تحميل
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Checkout Modal (BIL-01) ──────────────────────────────────── -->
        <Modal :show="checkoutOpen" title="إتمام الشراء" size="sm" @close="closeCheckout">
            <div class="checkout-body">

                <!-- Package summary -->
                <div class="checkout-summary">
                    <div class="checkout-summary-row">
                        <span class="checkout-label">الباقة</span>
                        <span class="checkout-value">{{ selectedPackage?.name }}</span>
                    </div>
                    <div class="checkout-summary-row">
                        <span class="checkout-label">التوكنز</span>
                        <span class="checkout-value">{{ selectedPackage ? formatNumber(selectedPackage.tokens) : '' }} توكن</span>
                    </div>
                    <div class="checkout-summary-row checkout-summary-row--price">
                        <span class="checkout-label">السعر</span>
                        <span class="checkout-price">
                            {{ selectedPackage?.price.toLocaleString('ar-SA') }}
                            <span style="font-size:14px;font-weight:500;">{{ selectedPackage?.currency }}</span>
                        </span>
                    </div>
                </div>

                <!-- VAT note -->
                <div class="checkout-vat-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    يُضاف ١٥٪ ضريبة قيمة مضافة للمستخدمين في السعودية
                </div>

                <!-- Payment method -->
                <div class="checkout-methods">
                    <div class="checkout-methods-label">طريقة الدفع</div>
                    <div class="checkout-methods-chips">
                        <button
                            v-for="method in PAYMENT_METHODS"
                            :key="method.key"
                            type="button"
                            class="method-chip"
                            :class="{ 'method-chip--active': paymentMethod === method.key }"
                            @click="paymentMethod = method.key"
                        >
                            {{ method.label }}
                        </button>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="checkoutError" class="alert alert-error checkout-error">
                    {{ checkoutError }}
                </div>
            </div>

            <template #footer>
                <button
                    class="btn btn-primary"
                    :disabled="paying"
                    @click="pay"
                >
                    <svg v-if="paying" class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    {{ paying ? 'جارٍ التحويل للدفع...' : 'الدفع الآن' }}
                </button>
                <button class="btn btn-secondary" :disabled="paying" @click="closeCheckout">إلغاء</button>
            </template>
        </Modal>

    </AppLayout>
</template>

<style scoped>
.billing-page {
    max-width: 960px;
    margin: 0 auto;
}

/* ── Section wrapper ── */
.section { margin-top: 28px; }
.section-head { margin-bottom: 20px; }
.section-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}
.section-sub {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--text-muted);
}

/* ── Balance card ── */
.balance-card {
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    border-radius: var(--radius-lg, 16px);
    padding: 32px 28px 24px;
    color: #fff;
}

.balance-card-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.balance-amount {
    font-size: 48px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -.02em;
    color: #fff;
}

.balance-label {
    margin-top: 8px;
    font-size: 15px;
    font-weight: 600;
    color: rgba(255, 255, 255, .9);
}

.balance-hint {
    margin-top: 4px;
    font-size: 12px;
    color: rgba(255, 255, 255, .65);
}

.btn-recharge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 20px;
    background: #fff;
    color: var(--sada-600, #0A5A4B);
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, box-shadow .15s;
    flex-shrink: 0;
    font-family: var(--font-arabic);
}
.btn-recharge:hover {
    background: rgba(255, 255, 255, .9);
    box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
}

.balance-alert {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 10px 14px;
    background: rgba(255, 255, 255, .15);
    border: 1px solid rgba(255, 255, 255, .3);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    backdrop-filter: blur(4px);
}

/* ── Packages grid ── */
.packages-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 860px) {
    .packages-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .packages-grid { grid-template-columns: 1fr; }
}

/* Package card */
.pkg-card {
    position: relative;
    background: var(--bg-surface);
    border: 1.5px solid var(--border-default);
    border-radius: var(--radius-md, 10px);
    padding: 22px 18px 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: box-shadow .2s, border-color .2s, transform .15s;
}
.pkg-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
    transform: translateY(-2px);
}

.pkg-card--popular {
    border-color: var(--sada-500);
    box-shadow: 0 0 0 3px rgba(15, 111, 92, .08);
}

.pkg-popular-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    position: absolute;
    top: -11px;
    right: 14px;
    padding: 3px 10px;
    background: var(--sada-500);
    color: #fff;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.pkg-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
}

.pkg-tokens {
    display: flex;
    align-items: baseline;
    gap: 5px;
}
.pkg-tokens-num {
    font-size: 32px;
    font-weight: 700;
    color: var(--sada-500);
    line-height: 1;
}
.pkg-tokens-label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
}

.pkg-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}
.pkg-currency {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
    margin-right: 3px;
}

.btn-block {
    width: 100%;
    justify-content: center;
    margin-top: auto;
}

/* ── Transactions table ── */
.tx-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 52px 20px;
    color: var(--text-muted);
    font-size: 14px;
}

.tx-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.tx-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.tx-table thead tr {
    background: var(--bg-muted);
    border-bottom: 1px solid var(--border-subtle);
}

.tx-table th {
    padding: 10px 16px;
    text-align: right;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
}

.tx-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    color: var(--text-primary);
    vertical-align: middle;
}

.tx-table tbody tr:last-child td { border-bottom: none; }

.tx-table tbody tr:hover td { background: var(--bg-muted); }

.tx-date {
    white-space: nowrap;
    color: var(--text-muted);
    font-size: 12px;
}

.tx-desc { max-width: 240px; }

.tx-amount {
    font-weight: 700;
    font-size: 14px;
    white-space: nowrap;
}
.tx-amount--pos { color: #16a34a; }
.tx-amount--neg { color: var(--error, #dc2626); }

.tx-balance {
    color: var(--text-muted);
    font-size: 12px;
}

/* Pagination */
.tx-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 14px 16px;
    border-top: 1px solid var(--border-subtle);
}
.tx-pagination-info {
    font-size: 13px;
    color: var(--text-muted);
}

/* ── Invoices list ── */
.inv-list {
    display: flex;
    flex-direction: column;
}

.inv-row {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
    flex-wrap: wrap;
    transition: background .15s;
}
.inv-row:last-child { border-bottom: none; }
.inv-row:hover { background: var(--bg-muted); }

.inv-main {
    flex: 1;
    min-width: 140px;
}
.inv-number {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    font-family: monospace;
}
.inv-date {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 3px;
}

.inv-tokens {
    display: flex;
    align-items: baseline;
    gap: 4px;
    min-width: 90px;
}
.inv-tokens-count {
    font-size: 16px;
    font-weight: 700;
    color: var(--sada-500);
}
.inv-tokens-label {
    font-size: 11px;
    color: var(--text-muted);
}

.inv-amounts {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 180px;
}
.inv-amount-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
}
.inv-amount-label { color: var(--text-muted); }
.inv-amount-val   { color: var(--text-primary); font-weight: 500; }

.inv-amount-row--total .inv-amount-label,
.inv-amount-row--total .inv-amount-val {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 13px;
}

.inv-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    flex-wrap: wrap;
}

/* ── Checkout modal content ── */
.checkout-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.checkout-summary {
    background: var(--bg-muted);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.checkout-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 14px;
}

.checkout-label { color: var(--text-muted); font-weight: 500; }
.checkout-value { color: var(--text-primary); font-weight: 600; }

.checkout-summary-row--price { padding-top: 8px; border-top: 1px solid var(--border-subtle); }
.checkout-price {
    font-size: 22px;
    font-weight: 700;
    color: var(--sada-500);
}

.checkout-vat-note {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    color: var(--text-muted);
    background: var(--bg-page);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 9px 12px;
    line-height: 1.5;
}

.checkout-methods-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.checkout-methods-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.method-chip {
    padding: 7px 16px;
    border-radius: 8px;
    border: 1.5px solid var(--border-default);
    background: var(--bg-surface);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    transition: border-color .15s, background .15s, color .15s;
    font-family: var(--font-arabic);
}
.method-chip:hover {
    border-color: var(--sada-500);
    background: var(--accent-soft, rgba(15, 111, 92, .06));
}
.method-chip--active {
    border-color: var(--sada-500);
    background: var(--accent-soft, rgba(15, 111, 92, .08));
    color: var(--sada-500);
}

.checkout-error { margin-top: 0; }

/* ── Spinner ── */
.spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

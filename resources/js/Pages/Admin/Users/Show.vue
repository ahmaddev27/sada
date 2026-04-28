<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Workspace      { id: number; name: string; created_at: string }
interface TokenTx        { id: number; type: string; amount: number; balance_after: number; created_at: string }

interface User {
    id: number
    name: string
    email: string
    is_admin: boolean
    token_balance: number
    banned_at: string | null
    created_at: string
    workspaces:         Workspace[]
    token_transactions: TokenTx[]
}

const props = defineProps<{ user: User }>()

const grantAmount = ref(0)
const granting    = ref(false)

function ban() {
    if (! confirm(`حظر ${props.user.name}؟`)) return
    router.post(`/admin/users/${props.user.id}/ban`, {})
}

function unban() {
    router.post(`/admin/users/${props.user.id}/unban`, {})
}

function grant() {
    if (grantAmount.value < 1) return
    granting.value = true
    router.post(
        `/admin/users/${props.user.id}/grant-tokens`,
        { amount: grantAmount.value },
        { onFinish: () => { granting.value = false; grantAmount.value = 0 } },
    )
}

function initials(name: string) {
    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase()
}

const TX_LABELS: Record<string, string> = {
    purchase:    'شراء',
    admin_grant: 'منحة إدارية',
    deduction:   'خصم',
    refund:      'استرداد',
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <Link href="/admin/users" class="bc-link">المستخدمون</Link>
                <span class="bc-sep">/</span>
                <span class="bc-cur">{{ user.name }}</span>
            </div>

            <!-- Hero -->
            <div class="hero-card">
                <div class="hero-avatar">{{ initials(user.name) }}</div>
                <div class="hero-info">
                    <div class="hero-name">
                        {{ user.name }}
                        <span v-if="user.is_admin" class="badge badge--admin">إداري</span>
                        <span v-if="user.banned_at" class="badge badge--banned">محظور</span>
                    </div>
                    <div class="hero-email" dir="ltr">{{ user.email }}</div>
                    <div class="hero-meta">
                        انضم {{ new Date(user.created_at).toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </div>
                </div>
                <div class="hero-kpis">
                    <div class="hero-kpi">
                        <div class="hk-val">{{ user.token_balance.toLocaleString('ar') }}</div>
                        <div class="hk-label">توكن</div>
                    </div>
                    <div class="hero-kpi">
                        <div class="hk-val">{{ user.workspaces.length }}</div>
                        <div class="hk-label">Workspace</div>
                    </div>
                    <div class="hero-kpi">
                        <div class="hk-val">{{ user.token_transactions.length }}</div>
                        <div class="hk-label">معاملة</div>
                    </div>
                </div>
                <div class="hero-actions">
                    <button
                        v-if="!user.is_admin"
                        class="act-btn act-btn--ghost"
                        @click="router.post(`/admin/users/${user.id}/impersonate`)"
                        title="تصفح المنصة بحساب هذا المستخدم"
                    >
                        <Icon name="user-switch" :size="14" style="vertical-align:-2px; margin-left:4px;" />
                        انتقال للحساب
                    </button>
                    <button v-if="!user.banned_at && !user.is_admin" class="act-btn act-btn--danger" @click="ban">حظر</button>
                    <button v-if="user.banned_at" class="act-btn act-btn--success" @click="unban">رفع الحظر</button>
                </div>
            </div>

            <!-- Main grid -->
            <div class="main-grid">

                <!-- Grant tokens -->
                <div class="section-card">
                    <h3 class="section-title">منح توكنات</h3>
                    <div class="grant-row">
                        <input
                            v-model.number="grantAmount"
                            type="number"
                            min="1"
                            class="field-input"
                            placeholder="عدد التوكنات"
                        />
                        <button class="act-btn act-btn--primary" :disabled="granting || grantAmount < 1" @click="grant">
                            {{ granting ? 'جارٍ...' : 'منح' }}
                        </button>
                    </div>
                    <p class="grant-hint">الرصيد الحالي: <strong>{{ user.token_balance.toLocaleString('ar') }}</strong> توكن</p>
                </div>

                <!-- Workspaces -->
                <div class="section-card">
                    <h3 class="section-title">Workspaces <span class="count-badge">{{ user.workspaces.length }}</span></h3>
                    <div v-if="user.workspaces.length" class="ws-list">
                        <div v-for="w in user.workspaces" :key="w.id" class="ws-row">
                            <div class="ws-icon">W</div>
                            <div class="ws-info">
                                <div class="ws-name">{{ w.name }}</div>
                                <div class="ws-date">{{ new Date(w.created_at).toLocaleDateString('ar-SA') }}</div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="empty-text">لا يوجد workspaces</p>
                </div>

            </div>

            <!-- Token transactions -->
            <div class="section-card mt">
                <h3 class="section-title">آخر معاملات التوكن <span class="count-badge">{{ user.token_transactions.length }}</span></h3>
                <div v-if="user.token_transactions.length" class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>المبلغ</th>
                                <th>الرصيد بعد</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in user.token_transactions" :key="t.id">
                                <td>
                                    <span :class="['tx-type', t.type === 'admin_grant' ? 'tx--grant' : t.type === 'deduction' ? 'tx--deduct' : 'tx--default']">
                                        {{ TX_LABELS[t.type] ?? t.type }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="t.amount > 0 ? 'amount--pos' : 'amount--neg'">
                                        {{ t.amount > 0 ? '+' : '' }}{{ t.amount.toLocaleString('ar') }}
                                    </span>
                                </td>
                                <td class="mono">{{ t.balance_after.toLocaleString('ar') }}</td>
                                <td class="muted">{{ new Date(t.created_at).toLocaleDateString('ar-SA') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="empty-text">لا توجد معاملات</p>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 24px 28px; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
.bc-link    { color: var(--sada-500); text-decoration: none; }
.bc-link:hover { text-decoration: underline; }
.bc-sep { color: var(--border-default); }
.bc-cur { color: var(--text-primary); }

/* Hero */
.hero-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.hero-avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff; font-size: 20px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}
.hero-info   { flex: 1; min-width: 160px; }
.hero-name   { font-size: 17px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 3px; }
.hero-email  { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.hero-meta   { font-size: 11px; color: var(--text-muted); }

.badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
.badge--admin  { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #f59e0b; }
.badge--banned { background: color-mix(in oklab, #ef4444 15%, transparent); color: #ef4444; }

.hero-kpis { display: flex; gap: 24px; }
.hero-kpi  { text-align: center; }
.hk-val    { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.hk-label  { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

.hero-actions { display: flex; gap: 8px; }

/* Buttons */
.act-btn {
    height: 34px; padding: 0 16px; border-radius: var(--radius-md);
    font-size: 13px; font-weight: 600; border: none; cursor: pointer;
    font-family: var(--font-arabic); transition: background .15s; white-space: nowrap;
}
.act-btn--primary { background: var(--sada-500); color: #fff; }
.act-btn--primary:hover { background: var(--sada-600); }
.act-btn--primary:disabled { opacity: .4; cursor: not-allowed; }
.act-btn--danger  { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; border: 1px solid color-mix(in oklab, #ef4444 30%, transparent); }
.act-btn--danger:hover  { background: #ef4444; color: #fff; }
.act-btn--success { background: color-mix(in oklab, #10b981 12%, transparent); color: #10b981; border: 1px solid color-mix(in oklab, #10b981 30%, transparent); }
.act-btn--success:hover { background: #10b981; color: #fff; }
.act-btn--ghost   { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.act-btn--ghost:hover   { background: var(--bg-muted); color: var(--text-primary); }

/* Grid */
.main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 0; }
.section-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
}
.section-card.mt { margin-top: 14px; }
.section-title { font-size: 13px; font-weight: 700; color: var(--text-muted); margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
.count-badge { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 1px 8px; border-radius: 99px; font-weight: 600; }

/* Grant */
.grant-row { display: flex; gap: 8px; margin-bottom: 8px; }
.field-input {
    height: 36px; padding: 0 12px; border-radius: var(--radius-md);
    background: var(--bg-page); border: 1px solid var(--border-default);
    color: var(--text-primary); font-size: 13px; flex: 1;
    font-family: var(--font-arabic);
}
.grant-hint { font-size: 12px; color: var(--text-muted); margin: 0; }

/* Workspaces list */
.ws-list { display: flex; flex-direction: column; gap: 8px; }
.ws-row  { display: flex; align-items: center; gap: 10px; }
.ws-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500); font-weight: 700; font-size: 12px;
    display: grid; place-items: center; flex-shrink: 0;
}
.ws-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.ws-date { font-size: 11px; color: var(--text-muted); }

/* Table */
.table-wrap { overflow-x: auto; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 9px 12px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 9px 12px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }

.tx-type    { font-size: 11px; font-weight: 600; padding: 2px 9px; border-radius: 99px; }
.tx--grant  { background: color-mix(in oklab, var(--sada-500) 12%, transparent); color: var(--sada-500); }
.tx--deduct { background: color-mix(in oklab, #ef4444 12%, transparent); color: #ef4444; }
.tx--default{ background: var(--bg-muted); color: var(--text-muted); }

.amount--pos { color: #10b981; font-weight: 700; }
.amount--neg { color: #ef4444; font-weight: 700; }
.mono  { font-variant-numeric: tabular-nums; font-weight: 600; }
.muted { color: var(--text-muted); font-size: 12px; }
.empty-text { font-size: 13px; color: var(--text-muted); margin: 4px 0 0; }

@media (max-width: 700px) { .main-grid { grid-template-columns: 1fr; } .hero-kpis { gap: 16px; } }
</style>

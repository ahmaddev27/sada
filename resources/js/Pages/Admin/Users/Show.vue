<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface User {
    id: number
    name: string
    email: string
    is_admin: boolean
    token_balance: number
    banned_at: string | null
    created_at: string
    workspaces: { id: number; name: string; created_at: string }[]
    token_transactions: { id: number; type: string; amount: number; balance_after: number; created_at: string }[]
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
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="breadcrumb">
                <Link href="/admin/users" class="bc-link">المستخدمون</Link>
                <span class="bc-sep">/</span>
                <span class="bc-cur">{{ user.name }}</span>
            </div>

            <div class="user-header">
                <div class="user-avatar">{{ user.name.charAt(0) }}</div>
                <div class="user-header-info">
                    <h1 class="page-title">{{ user.name }}</h1>
                    <p class="user-email" dir="ltr">{{ user.email }}</p>
                </div>
                <div class="user-actions">
                    <button v-if="!user.banned_at && !user.is_admin" class="admin-btn admin-btn--danger" @click="ban">حظر</button>
                    <button v-if="user.banned_at" class="admin-btn admin-btn--success" @click="unban">رفع الحظر</button>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-card">
                    <h3 class="card-title">المعلومات</h3>
                    <div class="info-row"><span class="info-label">رصيد التوكن</span><span class="info-val green">{{ user.token_balance.toLocaleString('ar') }}</span></div>
                    <div class="info-row"><span class="info-label">Workspaces</span><span class="info-val">{{ user.workspaces.length }}</span></div>
                    <div class="info-row">
                        <span class="info-label">الحالة</span>
                        <span :class="['status-dot', user.banned_at ? 'status-dot--banned' : 'status-dot--active']">{{ user.banned_at ? 'محظور' : 'فعّال' }}</span>
                    </div>
                    <div class="info-row"><span class="info-label">مدير</span><span class="info-val">{{ user.is_admin ? 'نعم' : 'لا' }}</span></div>
                    <div class="info-row last"><span class="info-label">تاريخ التسجيل</span><span class="info-val">{{ new Date(user.created_at).toLocaleDateString('ar-SA') }}</span></div>
                </div>

                <div class="detail-card">
                    <h3 class="card-title">منح توكنات</h3>
                    <div class="grant-row">
                        <input
                            v-model.number="grantAmount"
                            type="number"
                            min="1"
                            class="admin-input"
                            placeholder="عدد التوكنات"
                        />
                        <button class="admin-btn" :disabled="granting || grantAmount < 1" @click="grant">
                            {{ granting ? 'جارٍ...' : 'منح' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="detail-card mt">
                <h3 class="card-title">Workspaces ({{ user.workspaces.length }})</h3>
                <table class="admin-table" v-if="user.workspaces.length">
                    <thead><tr><th>#</th><th>الاسم</th><th>تاريخ الإنشاء</th></tr></thead>
                    <tbody>
                        <tr v-for="w in user.workspaces" :key="w.id">
                            <td class="muted">{{ w.id }}</td>
                            <td>{{ w.name }}</td>
                            <td class="muted">{{ new Date(w.created_at).toLocaleDateString('ar-SA') }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="empty-text">لا يوجد workspaces</p>
            </div>

            <div class="detail-card mt">
                <h3 class="card-title">آخر معاملات التوكن ({{ user.token_transactions.length }})</h3>
                <table class="admin-table" v-if="user.token_transactions.length">
                    <thead><tr><th>النوع</th><th>المبلغ</th><th>الرصيد بعد</th><th>التاريخ</th></tr></thead>
                    <tbody>
                        <tr v-for="t in user.token_transactions" :key="t.id">
                            <td><span class="type-badge">{{ t.type }}</span></td>
                            <td :class="t.amount > 0 ? 'green' : 'red'">{{ t.amount > 0 ? '+' : '' }}{{ t.amount }}</td>
                            <td>{{ t.balance_after.toLocaleString('ar') }}</td>
                            <td class="muted">{{ new Date(t.created_at).toLocaleDateString('ar-SA') }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="empty-text">لا توجد معاملات</p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 24px 28px; max-width: 920px; }

.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
.bc-link    { color: var(--sada-500); text-decoration: none; }
.bc-link:hover { text-decoration: underline; }
.bc-sep  { color: var(--border-default); }
.bc-cur  { color: var(--text-primary); }

.user-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.user-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff; font-size: 20px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}
.user-header-info { flex: 1; min-width: 0; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0 0 2px; }
.user-email  { font-size: 13px; color: var(--text-muted); margin: 0; direction: ltr; text-align: left; }
.user-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 0; }

.detail-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
}
.detail-card.mt { margin-top: 14px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text-muted); margin: 0 0 14px; }

.info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-subtle); }
.info-row.last { border-bottom: none; }
.info-label { font-size: 12px; color: var(--text-muted); }
.info-val   { font-size: 13px; color: var(--text-primary); font-weight: 600; }

.grant-row { display: flex; gap: 8px; }
.admin-input {
    height: 36px; padding: 0 12px; border-radius: var(--radius-md);
    background: var(--bg-page); border: 1px solid var(--border-default);
    color: var(--text-primary); font-size: 13px; flex: 1;
    font-family: var(--font-arabic);
}

.admin-btn {
    height: 36px; padding: 0 18px; border-radius: var(--radius-md);
    background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600;
    border: none; cursor: pointer; white-space: nowrap; font-family: var(--font-arabic);
    transition: background .15s;
}
.admin-btn:hover { background: var(--sada-600); }
.admin-btn:disabled { opacity: .4; cursor: not-allowed; }
.admin-btn--danger  { background: #dc2626; }
.admin-btn--danger:hover { background: #b91c1c; }
.admin-btn--success { background: #059669; }
.admin-btn--success:hover { background: #047857; }

.status-dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.status-dot--active { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.status-dot--banned { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }

.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 8px 12px; color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); background: var(--bg-muted); }
.admin-table td { padding: 9px 12px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }
.muted { color: var(--text-muted) !important; }
.green { color: #10b981 !important; font-weight: 600; }
.red   { color: #ef4444 !important; font-weight: 600; }

.type-badge { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 8px; border-radius: 5px; }
.empty-text { font-size: 13px; color: var(--text-muted); margin: 12px 0 0; }

@media (max-width: 700px) {
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

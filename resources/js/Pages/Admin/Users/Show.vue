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
    router.post(`/admin/users/${props.user.id}/grant-tokens`, { amount: grantAmount.value }, {
        onFinish: () => { granting.value = false; grantAmount.value = 0 },
    })
}

function impersonate() {
    if (! confirm(`تصفح كـ ${props.user.name}؟`)) return
    router.post(`/admin/users/${props.user.id}/impersonate`, {})
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="breadcrumb">
                <Link href="/admin/users" class="bc-link">المستخدمون</Link>
                <span class="bc-sep">/</span>
                <span>{{ user.name }}</span>
            </div>

            <div class="user-header">
                <div class="user-avatar">{{ user.name.charAt(0) }}</div>
                <div>
                    <h1 class="admin-page-title">{{ user.name }}</h1>
                    <p class="user-email" dir="ltr">{{ user.email }}</p>
                </div>
                <div class="user-actions">
                    <button v-if="!user.banned_at && !user.is_admin" class="admin-btn admin-btn--danger" @click="ban">حظر</button>
                    <button v-if="user.banned_at" class="admin-btn admin-btn--green" @click="unban">رفع الحظر</button>
                    <button v-if="!user.is_admin" class="admin-btn admin-btn--ghost" @click="impersonate">دخول كـ هذا المستخدم</button>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Stats -->
                <div class="detail-card">
                    <h3 class="card-title">المعلومات</h3>
                    <div class="info-row"><span class="info-label">الرصيد</span><span class="info-val green">{{ user.token_balance.toLocaleString('ar') }} توكن</span></div>
                    <div class="info-row"><span class="info-label">Workspaces</span><span class="info-val">{{ user.workspaces.length }}</span></div>
                    <div class="info-row"><span class="info-label">الحالة</span><span :class="['status-dot', user.banned_at ? 'status-dot--banned' : 'status-dot--active']">{{ user.banned_at ? 'محظور' : 'فعّال' }}</span></div>
                    <div class="info-row"><span class="info-label">تاريخ التسجيل</span><span class="info-val">{{ new Date(user.created_at).toLocaleDateString('ar-SA') }}</span></div>
                </div>

                <!-- Grant tokens -->
                <div class="detail-card">
                    <h3 class="card-title">منح توكنات</h3>
                    <div class="grant-row">
                        <input v-model.number="grantAmount" type="number" min="1" class="admin-input" placeholder="عدد التوكنات" />
                        <button class="admin-btn" :disabled="granting || grantAmount < 1" @click="grant">
                            {{ granting ? 'جارٍ...' : 'منح' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Workspaces -->
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

            <!-- Token transactions -->
            <div class="detail-card mt">
                <h3 class="card-title">آخر معاملات التوكن</h3>
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
.admin-page { padding: 28px 32px; max-width: 900px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; margin-bottom: 20px; }
.bc-link { color: #93c5fd; text-decoration: none; }
.bc-link:hover { text-decoration: underline; }
.bc-sep { color: #2d3748; }

.user-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.user-avatar { width: 52px; height: 52px; border-radius: 50%; background: #0F6F5C; color: #fff; display: grid; place-items: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }
.admin-page-title { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0 0 2px; }
.user-email { font-size: 13px; color: #64748b; margin: 0; direction: ltr; }
.user-actions { display: flex; gap: 8px; margin-right: auto; flex-wrap: wrap; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.detail-card { background: #161b27; border: 1px solid #1e2535; border-radius: 10px; padding: 18px 20px; }
.detail-card.mt { margin-top: 14px; }
.card-title { font-size: 13px; font-weight: 700; color: #94a3b8; margin: 0 0 14px; }

.info-row   { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #1e2535; }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 12px; color: #64748b; }
.info-val   { font-size: 13px; color: #e2e8f0; font-weight: 600; }

.grant-row { display: flex; gap: 8px; }
.admin-input { height: 36px; padding: 0 12px; border-radius: 8px; background: #0f1117; border: 1px solid #2d3748; color: #e2e8f0; font-size: 13px; flex: 1; }

.admin-btn { height: 36px; padding: 0 16px; border-radius: 8px; background: #0F6F5C; color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; white-space: nowrap; }
.admin-btn:hover { background: #0A5A4B; }
.admin-btn:disabled { opacity: .4; cursor: not-allowed; }
.admin-btn--danger { background: #dc2626; }
.admin-btn--danger:hover { background: #b91c1c; }
.admin-btn--green { background: #059669; }
.admin-btn--green:hover { background: #047857; }
.admin-btn--ghost { background: #1e2535; color: #94a3b8; border: 1px solid #2d3748; }
.admin-btn--ghost:hover { background: #2d3748; color: #e2e8f0; }

.status-dot { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.status-dot--active { background: color-mix(in oklab, #10b981 15%, transparent); color: #10b981; }
.status-dot--banned { background: color-mix(in oklab, #ef4444 15%, transparent); color: #ef4444; }

.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 8px 12px; color: #64748b; font-weight: 600; text-align: right; border-bottom: 1px solid #1e2535; }
.admin-table td { padding: 9px 12px; border-bottom: 1px solid #1e2535; color: #cbd5e1; }
.admin-table tr:last-child td { border-bottom: none; }
.muted { color: #64748b !important; }
.green { color: #4ade80 !important; font-weight: 600; }
.red   { color: #f87171 !important; font-weight: 600; }

.type-badge { font-size: 11px; background: #1e2535; color: #94a3b8; padding: 2px 8px; border-radius: 5px; }
.empty-text { font-size: 13px; color: #475569; margin: 12px 0 0; }
</style>

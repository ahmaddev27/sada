<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'

interface SocialAccount {
    id: number
    provider: string
    account_name: string
    status: string
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
    router.get('/admin/social-accounts', { search: search.value, provider: provider.value, status: status.value }, { preserveState: true, replace: true })
}

function providerLabel(p: string) {
    return { instagram: 'انستجرام', facebook: 'فيسبوك', tiktok: 'تيك توك', snapchat: 'سناب شات' }[p] ?? 'X (تويتر)'
}

function statusLabel(s: string) {
    return { healthy: 'متصل', expired: 'منتهي', revoked: 'ملغى', error: 'خطأ' }[s] ?? s
}

function statusClass(s: string) {
    return { healthy: 'dot--green', expired: 'dot--amber', revoked: 'dot--red', error: 'dot--red' }[s] ?? 'dot--gray'
}

function dt(iso: string | null) {
    return iso ? new Date(iso).toLocaleDateString('ar-SA') : '—'
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <h1 class="page-title">الحسابات المرتبطة</h1>
                <span class="total-badge">{{ accounts.total }} حساب</span>
            </div>

            <!-- Stats bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-val">{{ stats.total }}</div>
                    <div class="stat-label">الكل</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val green">{{ stats.healthy }}</div>
                    <div class="stat-label">متصل</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val amber">{{ stats.expired }}</div>
                    <div class="stat-label">منتهي الصلاحية</div>
                </div>
                <div class="stat-divider" />
                <div class="stat-item">
                    <div class="stat-val red">{{ stats.revoked }}</div>
                    <div class="stat-label">ملغى</div>
                </div>
            </div>

            <div class="filters-row">
                <input v-model="search" class="admin-input" placeholder="بحث باسم الحساب أو الـ workspace..." @keyup.enter="applyFilter" />
                <select v-model="provider" class="admin-select" @change="applyFilter">
                    <option value="">كل المنصات</option>
                    <option value="instagram">انستجرام</option>
                    <option value="facebook">فيسبوك</option>
                    <option value="tiktok">تيك توك</option>
                    <option value="snapchat">سناب شات</option>
                    <option value="twitter">X (تويتر)</option>
                </select>
                <select v-model="status" class="admin-select" @change="applyFilter">
                    <option value="">كل الحالات</option>
                    <option value="healthy">متصل</option>
                    <option value="expired">منتهي</option>
                    <option value="revoked">ملغى</option>
                </select>
                <button class="admin-btn" @click="applyFilter">بحث</button>
            </div>

            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الحساب</th>
                            <th>Workspace</th>
                            <th>المنصة</th>
                            <th>الحالة</th>
                            <th>انتهاء التوكن</th>
                            <th>تاريخ الربط</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in accounts.data" :key="a.id">
                            <td class="muted">{{ a.id }}</td>
                            <td class="bold">{{ a.account_name || '—' }}</td>
                            <td class="muted">{{ a.workspace?.name ?? '—' }}</td>
                            <td><span class="platform-badge">{{ providerLabel(a.provider) }}</span></td>
                            <td><span :class="['dot', statusClass(a.status)]">{{ statusLabel(a.status) }}</span></td>
                            <td :class="a.token_expires_at && new Date(a.token_expires_at) < new Date() ? 'red' : 'muted'">
                                {{ dt(a.token_expires_at) }}
                            </td>
                            <td class="muted">{{ dt(a.created_at) }}</td>
                        </tr>
                        <tr v-if="!accounts.data.length">
                            <td colspan="7" class="empty-row">لا توجد حسابات مرتبطة</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
.admin-page  { padding: 24px 28px; }
.page-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0; }
.total-badge { font-size: 12px; background: var(--bg-muted); color: var(--text-muted); padding: 3px 10px; border-radius: 99px; }

.stats-bar {
    display: flex; align-items: center;
    background: var(--bg-surface); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); padding: 16px 20px;
    margin-bottom: 16px; flex-wrap: wrap; gap: 12px;
}
.stat-item  { text-align: center; flex: 1; min-width: 60px; }
.stat-val   { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
.stat-label { font-size: 10px; color: var(--text-muted); margin-top: 4px; font-weight: 600; }
.stat-divider { width: 1px; height: 36px; background: var(--border-subtle); flex-shrink: 0; }

.filters-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-input  { height: 36px; padding: 0 12px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); min-width: 220px; }
.admin-select { height: 36px; padding: 0 10px; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary); font-size: 13px; font-family: var(--font-arabic); }
.admin-btn    { height: 36px; padding: 0 18px; border-radius: var(--radius-md); background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: var(--font-arabic); transition: background .15s; }
.admin-btn:hover { background: var(--sada-600); }

.table-wrap  { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border-default); }
.admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.admin-table th { padding: 10px 14px; background: var(--bg-muted); color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--border-default); white-space: nowrap; }
.admin-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: var(--bg-muted); }
.muted  { color: var(--text-muted) !important; }
.bold   { font-weight: 600; }
.green  { color: #10b981 !important; }
.amber  { color: #f59e0b !important; }
.red    { color: #ef4444 !important; }
.empty-row { text-align: center; color: var(--text-muted); padding: 24px !important; }

.platform-badge { font-size: 11px; background: var(--bg-muted); color: var(--text-muted); padding: 2px 8px; border-radius: 5px; }
.dot       { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.dot--green { background: color-mix(in oklab, #10b981 14%, transparent); color: #10b981; }
.dot--amber { background: color-mix(in oklab, #f59e0b 14%, transparent); color: #f59e0b; }
.dot--red   { background: color-mix(in oklab, #ef4444 14%, transparent); color: #ef4444; }
.dot--gray  { background: var(--bg-muted); color: var(--text-muted); }

.pagination { display: flex; gap: 4px; margin-top: 16px; flex-wrap: wrap; }
.page-btn { min-width: 34px; height: 34px; display: grid; place-items: center; padding: 0 10px; border-radius: var(--radius-md); font-size: 12px; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-muted); text-decoration: none; cursor: pointer; }
.page-btn--active   { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }
.page-btn--disabled { opacity: .3; cursor: not-allowed; }
</style>

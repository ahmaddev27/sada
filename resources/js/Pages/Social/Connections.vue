<script setup lang="ts">
// CON-10: Connected accounts page
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import Modal from '@/Components/Base/Modal.vue'

interface SocialAccount {
    id: number
    provider: 'instagram' | 'facebook' | 'tiktok' | 'snapchat' | 'x'
    provider_label: string
    provider_account_id: string
    account_name: string
    account_picture_url: string | null
    status: 'healthy' | 'expired' | 'revoked' | 'error'
    status_label: string
    token_expires_at: string | null
    needs_refresh: boolean
    scopes: string[]
}

const props = defineProps<{ accounts: SocialAccount[] }>()

const instagram = computed(() => props.accounts.filter(a => a.provider === 'instagram'))
const facebook  = computed(() => props.accounts.filter(a => a.provider === 'facebook'))

// ── Disconnect modal ────────────────────────────────────────────────
const disconnectTarget  = ref<SocialAccount | null>(null)
const disconnecting     = ref(false)

function askDisconnect(account: SocialAccount) {
    disconnectTarget.value = account
}

function cancelDisconnect() {
    disconnectTarget.value = null
}

function confirmDisconnect() {
    if (!disconnectTarget.value) return
    disconnecting.value = true
    router.delete(`/social/accounts/${disconnectTarget.value.id}`, {
        onFinish: () => {
            disconnecting.value = false
            disconnectTarget.value = null
        },
    })
}

// ── Refresh ─────────────────────────────────────────────────────────
const refreshing = ref<number | null>(null)

function refresh(account: SocialAccount) {
    refreshing.value = account.id
    router.post(`/social/accounts/${account.id}/refresh`, {}, {
        onFinish: () => { refreshing.value = null },
    })
}

function connectMeta() {
    window.location.href = '/social/connect/meta'
}

const statusClass: Record<string, string> = {
    healthy: 'badge-success',
    expired: 'badge-warning',
    revoked: 'badge-error',
    error:   'badge-error',
}

const platforms = [
    { key: 'instagram', label: 'Instagram', accounts: instagram, gradient: 'linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)' },
    { key: 'facebook',  label: 'Facebook',  accounts: facebook,  gradient: '#1877f2' },
]
</script>

<template>
    <AppLayout title="الحسابات المرتبطة" :crumbs="['الحسابات المرتبطة']">
        <div class="connections-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الحسابات المرتبطة</h1>
                    <p class="page-sub">اربط حساباتك على Meta لبدء الجدولة والنشر والإعلانات</p>
                </div>
                <button class="btn btn-primary" @click="connectMeta">
                    <Icon name="plus" :size="15" />
                    ربط حساب Meta
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="accounts.length === 0" class="empty-card">
                <div class="empty-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </div>
                <h3 class="empty-title">لا توجد حسابات مرتبطة</h3>
                <p class="empty-desc">اربط حساب Instagram أو Facebook للبدء في جدولة المنشورات وإطلاق الحملات</p>
                <button class="btn btn-primary" style="margin-top:8px;" @click="connectMeta">
                    <Icon name="plus" :size="15" />
                    ربط حساب Meta
                </button>
            </div>

            <!-- Platform sections -->
            <template v-else>
                <section
                    v-for="plat in platforms"
                    :key="plat.key"
                    v-show="plat.accounts.value.length"
                    class="platform-section"
                >
                    <div class="platform-header">
                        <div class="platform-logo" :style="{ background: plat.gradient }">
                            <Icon :name="plat.key" :size="18" />
                        </div>
                        <h2 class="platform-title">{{ plat.label }}</h2>
                        <span class="count-badge">{{ plat.accounts.value.length }}</span>
                    </div>

                    <div class="accounts-list">
                        <div
                            v-for="a in plat.accounts.value"
                            :key="a.id"
                            class="account-row"
                        >
                            <!-- Avatar -->
                            <div class="account-avatar">
                                <img v-if="a.account_picture_url" :src="a.account_picture_url" :alt="a.account_name" />
                                <Icon v-else name="user" :size="20" />
                            </div>

                            <!-- Info -->
                            <div class="account-info">
                                <span class="account-name">{{ a.account_name }}</span>
                                <span class="account-id" dir="ltr">{{ a.provider_account_id }}</span>
                            </div>

                            <!-- Expiry warning -->
                            <div v-if="a.needs_refresh" class="expiry-hint">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                الرمز منتهٍ
                            </div>

                            <!-- Status badge -->
                            <span :class="['status-badge', `status-badge--${a.status}`]">{{ a.status_label }}</span>

                            <!-- Actions -->
                            <div class="account-actions">
                                <button
                                    v-if="a.needs_refresh"
                                    class="action-btn"
                                    :class="{ 'action-btn--loading': refreshing === a.id }"
                                    title="تجديد الرمز"
                                    @click="refresh(a)"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :style="refreshing === a.id ? 'animation:spin .7s linear infinite' : ''"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                </button>
                                <button
                                    class="action-btn action-btn--danger"
                                    title="فصل الحساب"
                                    @click="askDisconnect(a)"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </template>

            <!-- Coming soon -->
            <div class="coming-soon">
                <span class="coming-label">قريباً:</span>
                <span class="coming-chip">TikTok</span>
                <span class="coming-chip">Snapchat</span>
                <span class="coming-chip">X (Twitter)</span>
            </div>

        </div>

        <!-- ── Disconnect confirmation modal ─────────────────────── -->
        <Modal
            :show="!!disconnectTarget"
            title="فصل الحساب"
            size="sm"
            @close="cancelDisconnect"
        >
            <div class="disconnect-modal-body">
                <div class="disconnect-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <p class="disconnect-text">
                    هل أنت متأكد من فصل حساب
                    <strong>{{ disconnectTarget?.account_name }}</strong>؟<br/>
                    لن تتمكن من النشر على هذا الحساب حتى تعيد ربطه.
                </p>
            </div>

            <template #footer>
                <button
                    class="btn btn-danger"
                    :disabled="disconnecting"
                    @click="confirmDisconnect"
                >
                    {{ disconnecting ? 'جارٍ الفصل...' : 'فصل الحساب' }}
                </button>
                <button class="btn btn-secondary" @click="cancelDisconnect">إلغاء</button>
            </template>
        </Modal>

    </AppLayout>
</template>

<style scoped>
.connections-page {
    max-width: 860px; margin: 0 auto;
    padding: 28px 20px;
    display: flex; flex-direction: column; gap: 20px;
}

/* ── Header ── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; }
.page-sub    { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Empty ── */
.empty-card {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: 16px; padding: 48px 32px;
    text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-500);
    display: grid; place-items: center; margin-bottom: 8px;
}
.empty-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0; }
.empty-desc  { font-size: 13px; color: var(--text-muted); margin: 0; max-width: 380px; line-height: 1.6; }

/* ── Platform section ── */
.platform-section {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: 12px; overflow: hidden;
}
.platform-header {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-bottom: 1px solid var(--border-subtle);
}
.platform-logo {
    width: 34px; height: 34px; border-radius: 9px;
    display: grid; place-items: center; color: #fff; flex-shrink: 0;
}
.platform-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0; flex: 1; }
.count-badge {
    font-size: 11px; font-weight: 700;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500); padding: 2px 8px; border-radius: 99px;
}

/* ── Account rows ── */
.accounts-list { display: flex; flex-direction: column; }
.account-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-subtle);
    transition: background .12s;
}
.account-row:last-child { border-bottom: none; }
.account-row:hover { background: var(--bg-page); }

.account-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    overflow: hidden; background: var(--bg-muted);
    display: grid; place-items: center; color: var(--text-muted);
    flex-shrink: 0;
}
.account-avatar img { width: 100%; height: 100%; object-fit: cover; }

.account-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.account-name { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.account-id   { font-size: 11px; color: var(--text-muted); font-family: monospace; }

.expiry-hint {
    display: flex; align-items: center; gap: 4px;
    font-size: 11px; color: var(--warning); font-weight: 600;
    white-space: nowrap;
}

.status-badge {
    font-size: 11px; font-weight: 600; padding: 3px 10px;
    border-radius: 99px; flex-shrink: 0; white-space: nowrap;
}
.status-badge--healthy { background: color-mix(in oklab, #10B981 15%, transparent); color: #10B981; }
.status-badge--expired { background: color-mix(in oklab, var(--warning) 15%, transparent); color: var(--warning); }
.status-badge--revoked,
.status-badge--error   { background: color-mix(in oklab, var(--error) 15%, transparent); color: var(--error); }

.account-actions { display: flex; gap: 4px; flex-shrink: 0; }
.action-btn {
    width: 30px; height: 30px; border-radius: 7px;
    display: grid; place-items: center;
    background: transparent; border: 1px solid var(--border-subtle);
    color: var(--text-muted); cursor: pointer; transition: all .15s;
}
.action-btn:hover { background: var(--bg-muted); color: var(--text-primary); }
.action-btn--danger:hover { background: color-mix(in oklab, var(--error) 12%, transparent); color: var(--error); border-color: var(--error); }

@keyframes spin { to { transform: rotate(360deg); } }

/* ── Coming soon ── */
.coming-soon {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 18px;
    background: var(--bg-surface); border: 1px dashed var(--border-default);
    border-radius: 10px;
}
.coming-label { font-size: 12px; font-weight: 600; color: var(--text-muted); }
.coming-chip  { font-size: 12px; color: var(--text-muted); background: var(--bg-page); border: 1px solid var(--border-subtle); border-radius: 99px; padding: 3px 12px; }

/* ── Disconnect modal ── */
.disconnect-modal-body { text-align: center; padding: 8px 0 16px; }
.disconnect-icon {
    width: 56px; height: 56px; border-radius: 50%;
    background: color-mix(in oklab, var(--error) 12%, transparent);
    color: var(--error);
    display: grid; place-items: center; margin: 0 auto 16px;
}
.disconnect-text { font-size: 14px; color: var(--text-primary); line-height: 1.7; margin: 0; }
</style>

<script setup lang="ts">
// CON-10: Connected accounts page
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
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

const byProvider = (p: string) => computed(() => props.accounts.filter(a => a.provider === p))

const platforms = [
    {
        key:         'instagram',
        label:       'Instagram',
        note:        'منشورات، ريلز، ستوريز',
        accounts:    byProvider('instagram'),
        bg:          'linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)',
        iconColor:   '#fff',
        connectPath: '/social/connect/meta',
    },
    {
        key:         'facebook',
        label:       'Facebook',
        note:        'منشورات وصفحات',
        accounts:    byProvider('facebook'),
        bg:          '#1877f2',
        iconColor:   '#fff',
        connectPath: '/social/connect/meta',
    },
    {
        key:         'tiktok',
        label:       'TikTok',
        note:        'فيديوهات وكابشن',
        accounts:    byProvider('tiktok'),
        bg:          '#010101',
        iconColor:   '#fff',
        connectPath: '/social/connect/tiktok',
    },
    {
        key:         'snapchat',
        label:       'Snapchat',
        note:        'إعلانات فقط — النشر العضوي غير مدعوم من API',
        accounts:    byProvider('snapchat'),
        bg:          '#FFFC00',
        iconColor:   '#000',
        connectPath: '/social/connect/snapchat',
    },
    {
        key:         'x',
        label:       'X (تويتر)',
        note:        'تغريدات وخيوط — يتطلب اشتراك Basic',
        accounts:    byProvider('x'),
        bg:          '#000000',
        iconColor:   '#fff',
        connectPath: '/social/connect/x',
    },
]

// ── Disconnect modal ─────────────────────────────────────────────────
const disconnectTarget = ref<SocialAccount | null>(null)
const disconnecting    = ref(false)

function askDisconnect(account: SocialAccount) { disconnectTarget.value = account }
function cancelDisconnect()                     { disconnectTarget.value = null }

function confirmDisconnect() {
    if (!disconnectTarget.value) return
    disconnecting.value = true
    router.delete(`/social/accounts/${disconnectTarget.value.id}`, {
        onFinish: () => { disconnecting.value = false; disconnectTarget.value = null },
    })
}

// ── Refresh ──────────────────────────────────────────────────────────
const refreshing = ref<number | null>(null)

function refresh(account: SocialAccount) {
    refreshing.value = account.id
    router.post(`/social/accounts/${account.id}/refresh`, {}, {
        onFinish: () => { refreshing.value = null },
    })
}

function connect(path: string) {
    window.location.href = path
}
</script>

<template>
    <AppLayout title="الحسابات المرتبطة" :crumbs="['الحسابات المرتبطة']">
        <div class="connections-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الحسابات المرتبطة</h1>
                    <p class="page-sub">اربط حساباتك على المنصات لبدء الجدولة والنشر والإعلانات</p>
                </div>
            </div>

            <!-- Platform sections — always all 5 shown -->
            <section v-for="plat in platforms" :key="plat.key" class="platform-section">

                <!-- Section header -->
                <div class="platform-header">
                    <div class="platform-logo" :style="{ background: plat.bg }">
                        <!-- Instagram -->
                        <svg v-if="plat.key === 'instagram'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="#fff" stroke="none"/></svg>
                        <!-- Facebook -->
                        <svg v-else-if="plat.key === 'facebook'" width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        <!-- TikTok -->
                        <svg v-else-if="plat.key === 'tiktok'" width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.24 8.24 0 0 0 4.83 1.55V6.79a4.85 4.85 0 0 1-1.06-.1z"/></svg>
                        <!-- Snapchat -->
                        <svg v-else-if="plat.key === 'snapchat'" width="18" height="18" viewBox="0 0 24 24" :fill="plat.iconColor"><path d="M12 2C9 2 6.5 4.5 6.5 7.5v.8c-.5.1-1.3.3-1.8.7-.3.2-.2.6.1.7.6.2 1.1.4 1.5.8-.3.8-.8 1.5-1.5 2-.4.3-.2.8.3.9 1.5.3 2.5 1.2 4.9 1.2s3.4-.9 4.9-1.2c.5-.1.7-.6.3-.9-.7-.5-1.2-1.2-1.5-2 .4-.4.9-.6 1.5-.8.3-.1.4-.5.1-.7-.5-.4-1.3-.6-1.8-.7v-.8C17.5 4.5 15 2 12 2z"/></svg>
                        <!-- X -->
                        <svg v-else-if="plat.key === 'x'" width="16" height="16" viewBox="0 0 24 24" fill="#fff"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </div>
                    <div class="platform-meta">
                        <h2 class="platform-title">{{ plat.label }}</h2>
                        <span class="platform-note">{{ plat.note }}</span>
                    </div>
                    <span v-if="plat.accounts.value.length" class="count-badge">
                        {{ plat.accounts.value.length }}
                    </span>
                    <button class="btn-connect" @click="connect(plat.connectPath)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        ربط حساب
                    </button>
                </div>

                <!-- Connected accounts list -->
                <div v-if="plat.accounts.value.length" class="accounts-list">
                    <div
                        v-for="a in plat.accounts.value"
                        :key="a.id"
                        class="account-row"
                    >
                        <div class="account-avatar">
                            <img v-if="a.account_picture_url" :src="a.account_picture_url" :alt="a.account_name" />
                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>

                        <div class="account-info">
                            <span class="account-name">{{ a.account_name }}</span>
                            <span class="account-id" dir="ltr">{{ a.provider_account_id }}</span>
                        </div>

                        <div v-if="a.needs_refresh" class="expiry-hint">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            الرمز منتهٍ
                        </div>

                        <span :class="['status-badge', `status-badge--${a.status}`]">{{ a.status_label }}</span>

                        <div class="account-actions">
                            <button
                                v-if="a.needs_refresh"
                                class="action-btn"
                                title="تجديد الرمز"
                                @click="refresh(a)"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :style="refreshing === a.id ? 'animation:spin .7s linear infinite' : ''"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            </button>
                            <button
                                class="action-btn action-btn--danger"
                                title="فصل الحساب"
                                @click="askDisconnect(a)"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty row for this platform -->
                <div v-else class="platform-empty">
                    <span>لا توجد حسابات مرتبطة لهذه المنصة</span>
                </div>

            </section>

        </div>

        <!-- Disconnect confirmation modal -->
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
                <button class="btn btn-danger" :disabled="disconnecting" @click="confirmDisconnect">
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
    display: flex; flex-direction: column; gap: 16px;
}

/* ── Header ── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.page-title  { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; }
.page-sub    { font-size: 13px; color: var(--text-muted); margin: 0; }

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
    width: 36px; height: 36px; border-radius: 10px;
    display: grid; place-items: center; flex-shrink: 0;
}

.platform-meta { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1px; }
.platform-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0; }
.platform-note  { font-size: 11px; color: var(--text-muted); }

.count-badge {
    font-size: 11px; font-weight: 700;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500); padding: 2px 8px; border-radius: 99px; flex-shrink: 0;
}

.btn-connect {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600;
    padding: 6px 14px; border-radius: 7px;
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-500);
    border: 1px solid color-mix(in oklab, var(--sada-500) 25%, transparent);
    cursor: pointer; flex-shrink: 0; transition: all .15s;
}
.btn-connect:hover { background: var(--sada-500); color: #fff; }

/* ── Empty platform row ── */
.platform-empty {
    padding: 14px 18px;
    font-size: 12px; color: var(--text-muted);
    font-style: italic;
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
    width: 36px; height: 36px; border-radius: 50%;
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
    font-size: 11px; color: var(--warning); font-weight: 600; white-space: nowrap;
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

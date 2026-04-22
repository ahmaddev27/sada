<script setup lang="ts">
// CON-10: Connected accounts page
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

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

function connectMeta() {
    router.visit('/social/connect/meta')
}

function disconnect(account: SocialAccount) {
    if (!confirm(`هل أنت متأكد من فصل حساب ${account.account_name}؟`)) return
    router.delete(`/social/accounts/${account.id}`)
}

function refresh(account: SocialAccount) {
    router.post(`/social/accounts/${account.id}/refresh`)
}

const statusClass: Record<string, string> = {
    healthy: 'badge-success',
    expired: 'badge-warning',
    revoked: 'badge-error',
    error:   'badge-error',
}
</script>

<template>
    <AppLayout title="الحسابات المرتبطة">
        <div class="connections-page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الحسابات المرتبطة</h1>
                    <p class="page-sub">اربط حساباتك على منصات التواصل الاجتماعي لبدء النشر</p>
                </div>
                <button class="btn-primary" @click="connectMeta">
                    <Icon name="plus" :size="16" />
                    ربط حساب Meta
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="accounts.length === 0" class="empty-state">
                <div class="empty-icon">
                    <Icon name="instagram" :size="40" />
                </div>
                <h3>لا توجد حسابات مرتبطة</h3>
                <p>اربط حساب Instagram أو Facebook للبدء في جدولة المنشورات</p>
                <button class="btn-primary" @click="connectMeta">ربط حساب Meta</button>
            </div>

            <!-- Platform sections -->
            <template v-else>
                <!-- Instagram -->
                <section v-if="instagram.length" class="platform-section">
                    <div class="platform-header">
                        <div class="platform-logo platform-logo--instagram">
                            <Icon name="instagram" :size="20" />
                        </div>
                        <h2>Instagram</h2>
                        <span class="badge-count">{{ instagram.length }}</span>
                    </div>
                    <div class="accounts-grid">
                        <div v-for="a in instagram" :key="a.id" class="account-card">
                            <div class="account-avatar">
                                <img v-if="a.account_picture_url" :src="a.account_picture_url" :alt="a.account_name" />
                                <Icon v-else name="user" :size="24" />
                            </div>
                            <div class="account-info">
                                <span class="account-name">{{ a.account_name }}</span>
                                <span class="account-id">ID: {{ a.provider_account_id }}</span>
                            </div>
                            <span :class="['badge', statusClass[a.status]]">{{ a.status_label }}</span>
                            <div class="account-actions">
                                <button
                                    v-if="a.needs_refresh"
                                    class="btn-ghost btn-sm"
                                    title="تجديد الرمز"
                                    @click="refresh(a)"
                                >
                                    <Icon name="refresh" :size="15" />
                                </button>
                                <button class="btn-ghost btn-sm btn-danger" title="فصل الحساب" @click="disconnect(a)">
                                    <Icon name="x" :size="15" />
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Facebook -->
                <section v-if="facebook.length" class="platform-section">
                    <div class="platform-header">
                        <div class="platform-logo platform-logo--facebook">
                            <Icon name="facebook" :size="20" />
                        </div>
                        <h2>Facebook</h2>
                        <span class="badge-count">{{ facebook.length }}</span>
                    </div>
                    <div class="accounts-grid">
                        <div v-for="a in facebook" :key="a.id" class="account-card">
                            <div class="account-avatar">
                                <img v-if="a.account_picture_url" :src="a.account_picture_url" :alt="a.account_name" />
                                <Icon v-else name="user" :size="24" />
                            </div>
                            <div class="account-info">
                                <span class="account-name">{{ a.account_name }}</span>
                                <span class="account-id">ID: {{ a.provider_account_id }}</span>
                            </div>
                            <span :class="['badge', statusClass[a.status]]">{{ a.status_label }}</span>
                            <div class="account-actions">
                                <button
                                    v-if="a.needs_refresh"
                                    class="btn-ghost btn-sm"
                                    title="تجديد الرمز"
                                    @click="refresh(a)"
                                >
                                    <Icon name="refresh" :size="15" />
                                </button>
                                <button class="btn-ghost btn-sm btn-danger" title="فصل الحساب" @click="disconnect(a)">
                                    <Icon name="x" :size="15" />
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </template>

            <!-- Coming soon -->
            <section class="coming-soon">
                <h3>قريباً</h3>
                <div class="coming-platforms">
                    <div class="coming-chip">TikTok</div>
                    <div class="coming-chip">Snapchat</div>
                    <div class="coming-chip">X (Twitter)</div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>

<style scoped>
.connections-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.25rem;
}

.page-sub {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Empty state */
.empty-state {
    card: var(--card);
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: var(--accent-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    margin-bottom: 0.5rem;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.empty-state p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Platform section */
.platform-section {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 16px;
    overflow: hidden;
}

.platform-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
}

.platform-header h2 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    flex: 1;
}

.platform-logo {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.platform-logo--instagram { background: linear-gradient(135deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.platform-logo--facebook  { background: #1877f2; }

.badge-count {
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--accent-soft);
    color: var(--accent);
    padding: 0.125rem 0.5rem;
    border-radius: 999px;
}

/* Accounts grid */
.accounts-grid {
    display: flex;
    flex-direction: column;
}

.account-card {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.15s;
}

.account-card:last-child { border-bottom: none; }
.account-card:hover { background: var(--bg-page); }

.account-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    flex-shrink: 0;
}

.account-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.account-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    flex: 1;
    min-width: 0;
}

.account-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.account-id {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-family: monospace;
    direction: ltr;
    text-align: right;
}

.badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    flex-shrink: 0;
}

.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-error   { background: #fee2e2; color: #991b1b; }

[data-theme="dark"] .badge-success { background: #052e16; color: #86efac; }
[data-theme="dark"] .badge-warning { background: #2d1b00; color: #fcd34d; }
[data-theme="dark"] .badge-error   { background: #2d0a0a; color: #fca5a5; }

.account-actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-shrink: 0;
}

.btn-sm {
    padding: 0.375rem;
    border-radius: 6px;
}

.btn-danger:hover {
    background: #fee2e2;
    color: #991b1b;
}

[data-theme="dark"] .btn-danger:hover {
    background: #2d0a0a;
    color: #fca5a5;
}

/* Coming soon */
.coming-soon {
    background: var(--bg-surface);
    border: 1px dashed var(--border-default);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
}

.coming-soon h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    margin: 0 0 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.coming-platforms {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.coming-chip {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-muted);
    background: var(--bg-page);
    border: 1px solid var(--border-subtle);
    border-radius: 999px;
    padding: 0.375rem 1rem;
}
</style>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import { useUiStore } from '@/Stores/ui'
import Icon from '@/Components/Base/Icon.vue'
import Toast from '@/Components/Base/Toast.vue'
import type { PageProps } from '@/Types'
import type { AppNotification } from '@/Types'

const props = defineProps<{ title?: string; crumbs?: string[] }>()

const page = usePage<PageProps>()
const ui   = useUiStore()

const user       = computed(() => page.props.auth?.user)
const ws         = computed(() => page.props.currentWorkspace)
const workspaces = computed(() => page.props.workspaces ?? [])
const flash      = computed(() => page.props.flash)

const mobileOpen   = ref(false)
const wsDropOpen   = ref(false)
const userDropOpen = ref(false)
const notifOpen    = ref(false)
const switching    = ref<number | null>(null)

const notifData        = computed(() => page.props.notifications)
const unreadCount      = computed(() => notifData.value?.unread_count ?? 0)
const recentNotifs     = computed(() => notifData.value?.recent ?? [])

function toggleNotif()  { notifOpen.value = !notifOpen.value; wsDropOpen.value = false; userDropOpen.value = false }
function closeNotif()   { notifOpen.value = false }

function markRead(id: string) {
    router.post('/notifications/read', { id }, { preserveScroll: true, onSuccess: closeNotif })
}
function markAllRead() {
    router.post('/notifications/read', { id: 'all' }, { preserveScroll: true, onSuccess: closeNotif })
}

function notifIcon(type: string): string {
    if (type === 'post_published') return 'check'
    if (type === 'post_failed')    return 'warning'
    return 'bell'
}

function notifColor(type: string): string {
    if (type === 'post_published') return '#10B981'
    if (type === 'post_failed')    return '#EF4444'
    return 'var(--sada-500)'
}

function timeAgo(iso: string): string {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000)
    if (diff < 60)   return 'الآن'
    if (diff < 3600) return Math.floor(diff / 60) + ' د'
    if (diff < 86400) return Math.floor(diff / 3600) + ' س'
    return Math.floor(diff / 86400) + ' ي'
}

watch(flash, (val) => {
    if (val?.success) ui.success(val.success)
    if (val?.error)   ui.error(val.error)
    if (val?.warning) ui.warning(val.warning)
    if (val?.info)    ui.info(val.info)
}, { immediate: true })

const tokenBalance = computed(() => user.value?.token_balance ?? 0)
const tokenMax     = computed(() => 2000)
const tokenPct     = computed(() => Math.min(100, Math.round((tokenBalance.value / tokenMax.value) * 100)))

const userInitials = computed(() => {
    const name = user.value?.name ?? ''
    return name.split(' ').map((w: string) => w[0]).join('').slice(0, 2)
})

const wsInitials = computed(() => {
    const name = ws.value?.name ?? ''
    return name.split(' ').map((w: string) => w[0]).join('').slice(0, 2)
})

const currentPath = computed(() => page.url.split('?')[0])

const navWork = [
    { label: 'الرئيسية',           href: '/dashboard',  icon: 'home' },
    { label: 'توليد محتوى',        href: '/generate',   icon: 'sparkle', badge: 'جديد' },
    { label: 'التقويم',            href: '/calendar',   icon: 'calendar' },
    { label: 'سجل المحتوى',        href: '/posts',      icon: 'clock' },
    { label: 'الحملات الإعلانية',  href: '/campaigns',      icon: 'megaphone' },
    { label: 'المواسم',            href: '/seasonal',       icon: 'moon' },
    { label: 'خطة تسويقية',       href: '/marketing-plan', icon: 'target', badge: 'AI' },
    { label: 'التحليلات',          href: '/analytics',      icon: 'chart' },
]

const navAccount = [
    { label: 'الحسابات المرتبطة', href: '/social/accounts', icon: 'instagram' },
    { label: 'الفوترة',           href: '/billing',         icon: 'credit' },
    { label: 'الإعدادات',         href: '/settings',        icon: 'settings' },
]

function isActive(href: string) {
    return currentPath.value === href || currentPath.value.startsWith(href + '/')
}

function closeMobile()    { mobileOpen.value = false }
function toggleWsDrop()   { wsDropOpen.value = !wsDropOpen.value; userDropOpen.value = false }
function closeWsDrop()    { wsDropOpen.value = false }
function toggleUserDrop() { userDropOpen.value = !userDropOpen.value; wsDropOpen.value = false }
function closeUserDrop()  { userDropOpen.value = false }

function logout() {
    router.post('/logout')
}

function switchWorkspace(id: number) {
    if (id === ws.value?.id) { closeWsDrop(); return; }
    switching.value = id
    router.post(`/workspaces/${id}/switch`, {}, {
        onFinish: () => { switching.value = null; closeWsDrop(); },
    })
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') { closeMobile(); closeWsDrop(); closeUserDrop(); closeNotif(); }
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => {
    window.removeEventListener('keydown', onKey)
})
</script>

<template>
    <!-- Sidebar overlay (mobile) -->
    <div
        class="sidebar-overlay"
        :data-open="mobileOpen"
        @click="closeMobile"
    />

    <!-- Workspace dropdown backdrop — z-index 99 closes dropdown when clicking outside sidebar -->
    <div
        v-if="wsDropOpen"
        style="position:fixed;inset:0;z-index:99;"
        @click="closeWsDrop"
    />

    <div class="app" :data-theme="ui.theme">

        <!-- ═══════════════════ SIDEBAR ═══════════════════ -->
        <aside class="sidebar" :data-open="mobileOpen">

            <!-- Logo + Workspace picker -->
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <img
                        :src="ui.theme === 'dark'
                            ? '/images/logo/sada-horizontal-ar-dark.svg'
                            : '/images/logo/sada-horizontal-ar.svg'"
                        class="logo-horizontal"
                        alt="صدى"
                    />
                </div>

                <!-- Workspace picker button -->
                <div style="position:relative;">
                    <button
                        class="workspace-picker"
                        :class="{ 'workspace-picker--open': wsDropOpen }"
                        @click="toggleWsDrop"
                    >
                        <div class="ws-avatar">
                            <img v-if="ws?.logo_path" :src="`/storage/${ws.logo_path}`" alt="" />
                            <span v-else>{{ wsInitials || 'ع' }}</span>
                        </div>
                        <div class="ws-info">
                            <div class="ws-name">{{ ws?.name || 'اختر مساحة عمل' }}</div>
                            <div class="ws-plan">{{ ws?.business_type || 'Growth' }}</div>
                        </div>
                        <Icon name="chevronDown" :size="14" class="ws-chev" :style="wsDropOpen ? 'transform:rotate(180deg);' : ''" />
                    </button>

                    <!-- Workspace dropdown -->
                    <Transition name="drop">
                        <div v-if="wsDropOpen" class="ws-drop">
                            <div class="ws-drop-label">مساحات العمل</div>

                            <button
                                v-for="w in workspaces"
                                :key="w.id"
                                class="ws-drop-item"
                                :class="{ 'ws-drop-item--active': w.id === ws?.id }"
                                :disabled="switching === w.id"
                                @click="switchWorkspace(w.id)"
                            >
                                <div class="ws-drop-avatar">
                                    <img v-if="w.logo_path" :src="`/storage/${w.logo_path}`" alt="" />
                                    <span v-else>{{ w.name.charAt(0) }}</span>
                                </div>
                                <span class="ws-drop-name">{{ w.name }}</span>
                                <svg
                                    v-if="w.id === ws?.id"
                                    class="ws-drop-check"
                                    width="14" height="14"
                                    viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                ><path d="M20 6 9 17l-5-5"/></svg>
                                <svg
                                    v-else-if="switching === w.id"
                                    class="ws-drop-spin"
                                    width="14" height="14"
                                    viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                ><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            </button>

                            <div class="ws-drop-divider" />

                            <Link href="/workspaces" class="ws-drop-manage" @click="closeWsDrop">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                إدارة المساحات
                            </Link>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">العمل</div>
                <Link
                    v-for="item in navWork"
                    :key="item.href"
                    :href="item.href"
                    class="nav-item"
                    :class="{ active: isActive(item.href) }"
                >
                    <span class="nav-icon">
                        <Icon :name="item.icon" :size="18" />
                    </span>
                    <span class="nav-label">{{ item.label }}</span>
                    <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
                </Link>

                <div class="nav-section" style="margin-top: 8px;">الحساب</div>
                <Link
                    v-for="item in navAccount"
                    :key="item.href"
                    :href="item.href"
                    class="nav-item"
                    :class="{ active: isActive(item.href) }"
                >
                    <span class="nav-icon">
                        <Icon :name="item.icon" :size="18" />
                    </span>
                    <span class="nav-label">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom: Token meter + User -->
            <div class="sidebar-bottom">
                <div class="token-meter">
                    <div class="token-meter-head">
                        <span class="label">التوكنز</span>
                        <span class="val">{{ tokenBalance.toLocaleString('ar') }} / {{ tokenMax.toLocaleString('ar') }}</span>
                    </div>
                    <div class="token-bar">
                        <div class="token-bar-fill" :style="{ width: tokenPct + '%' }" />
                    </div>
                    <button class="token-refill">شحن المزيد</button>
                </div>

                <!-- User dropdown backdrop -->
                <div
                    v-if="userDropOpen"
                    style="position:fixed;inset:0;z-index:99;"
                    @click="closeUserDrop"
                />

                <div style="position:relative;">
                    <button
                        class="user-chip"
                        :class="{ 'user-chip--open': userDropOpen }"
                        @click="toggleUserDrop"
                    >
                        <div class="uc-avatar">
                            <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" />
                            <span v-else>{{ userInitials || 'أح' }}</span>
                        </div>
                        <div class="uc-info">
                            <div class="uc-name">{{ user?.name || 'المستخدم' }}</div>
                            <div class="uc-role">مالك · Admin</div>
                        </div>
                        <Icon name="chevronDown" :size="14" :style="userDropOpen ? 'transform:rotate(180deg);transition:transform .2s' : 'transition:transform .2s'" />
                    </button>

                    <!-- User dropdown menu -->
                    <Transition name="drop">
                        <div v-if="userDropOpen" class="user-drop">
                            <div class="user-drop-info">
                                <div class="user-drop-name">{{ user?.name }}</div>
                                <div class="user-drop-email">{{ user?.email }}</div>
                            </div>
                            <div class="ws-drop-divider" />
                            <Link href="/settings" class="user-drop-item" @click="closeUserDrop">
                                <Icon name="settings" :size="14" />
                                الإعدادات
                            </Link>
                            <button class="user-drop-item user-drop-logout" @click="logout">
                                <Icon name="logout" :size="14" />
                                تسجيل الخروج
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <!-- ═══════════════════ MAIN ═══════════════════ -->
        <div class="app-main">

            <!-- Topbar -->
            <header class="topbar">
                <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                    <button
                        class="btn btn-icon btn-ghost"
                        data-mobile-only="true"
                        aria-label="فتح القائمة"
                        @click="mobileOpen = true"
                    >
                        <Icon name="menu" />
                    </button>

                    <div class="topbar-title">
                        <div v-if="crumbs?.length" class="crumbs">
                            <span v-for="(c, i) in crumbs" :key="i">
                                {{ c }}<span v-if="i < crumbs.length - 1" style="opacity:0.5; margin-right:6px;">•</span>
                            </span>
                        </div>
                        <h1>{{ title || 'لوحة التحكم' }}</h1>
                    </div>
                </div>

                <div class="topbar-actions">
                    <div class="topbar-search" data-desktop-only="true">
                        <Icon name="search" :size="16" style="color:var(--text-muted); flex-shrink:0;" />
                        <input placeholder="ابحث في كل شيء..." />
                        <kbd>⌘K</kbd>
                    </div>

                    <button
                        class="btn btn-icon btn-ghost"
                        :title="ui.theme === 'dark' ? 'وضع النهار' : 'وضع الليل'"
                        @click="ui.toggleTheme()"
                    >
                        <Icon :name="ui.theme === 'dark' ? 'sun' : 'moon'" />
                    </button>

                    <!-- Notification bell -->
                    <div style="position:relative;">
                        <div v-if="notifOpen" style="position:fixed;inset:0;z-index:149;" @click="closeNotif" />
                        <button
                            class="btn btn-icon btn-ghost notif-btn"
                            title="الإشعارات"
                            @click="toggleNotif"
                        >
                            <Icon name="bell" />
                            <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
                        </button>

                        <Transition name="drop">
                            <div v-if="notifOpen" class="notif-drop">
                                <div class="notif-header">
                                    <span class="notif-title">الإشعارات</span>
                                    <button v-if="unreadCount > 0" class="notif-read-all" @click="markAllRead">تحديد الكل كمقروء</button>
                                </div>

                                <div v-if="recentNotifs.length === 0" class="notif-empty">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);margin-bottom:8px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                    <p>لا توجد إشعارات</p>
                                </div>

                                <div v-else class="notif-list">
                                    <component
                                        :is="n.data.link ? 'a' : 'div'"
                                        v-for="n in recentNotifs"
                                        :key="n.id"
                                        :href="n.data.link"
                                        :class="['notif-item', { 'notif-item--unread': !n.read_at }]"
                                        @click="!n.read_at && markRead(n.id)"
                                    >
                                        <div class="notif-icon" :style="{ background: `color-mix(in oklab, ${notifColor(n.data.type)} 14%, transparent)`, color: notifColor(n.data.type) }">
                                            <svg v-if="n.data.type === 'post_published'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            <svg v-else-if="n.data.type === 'post_failed'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                        </div>
                                        <div class="notif-body">
                                            <div class="notif-item-title">{{ n.data.title }}</div>
                                            <div class="notif-item-body">{{ n.data.body }}</div>
                                        </div>
                                        <div class="notif-time">{{ timeAgo(n.created_at) }}</div>
                                    </component>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Impersonation banner -->
            <div v-if="page.props.impersonating" class="impersonate-banner">
                <Icon name="user-switch" :size="14" />
                <span>وضع الانتقال الإداري — أنت تتصفح كـ <strong>{{ user?.name }}</strong></span>
                <button class="impersonate-stop" @click="router.post('/admin/impersonate/stop')">
                    إنهاء الانتقال
                </button>
            </div>

            <!-- Page content -->
            <main class="content-area">
                <slot />
            </main>
        </div>
    </div>

    <Toast />
</template>

<style scoped>
/* ── Workspace dropdown ── */
.workspace-picker--open {
    border-color: var(--border-default) !important;
    background: var(--bg-muted) !important;
}
.workspace-picker .ws-chev {
    transition: transform .2s ease;
    flex-shrink: 0;
}

.ws-drop {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    left: 0;
    z-index: 110;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.14);
    overflow: hidden;
    min-width: 200px;
}

.ws-drop-label {
    padding: 8px 12px 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .06em;
    color: var(--text-muted);
    text-transform: uppercase;
}

.ws-drop-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: var(--font-arabic);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
    transition: background .12s;
    text-align: right;
}
.ws-drop-item:hover { background: var(--bg-muted); }
.ws-drop-item--active { color: var(--sada-600); background: color-mix(in oklab, var(--sada-500) 8%, transparent); }
.ws-drop-item:disabled { opacity: .6; cursor: default; }

.ws-drop-avatar {
    width: 26px; height: 26px;
    border-radius: 6px;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: grid;
    place-items: center;
    overflow: hidden;
}
.ws-drop-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }

.ws-drop-name { flex: 1; text-align: right; }

.ws-drop-check { color: var(--sada-500); flex-shrink: 0; }

.ws-drop-spin {
    flex-shrink: 0;
    animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.ws-drop-divider {
    height: 1px;
    background: var(--border-subtle);
    margin: 4px 0;
}

.ws-drop-manage {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 12px;
    font-family: var(--font-arabic);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    transition: background .12s, color .12s;
}
.ws-drop-manage:hover { background: var(--bg-muted); color: var(--text-primary); }

/* ── Dropdown transition ── */
.drop-enter-active, .drop-leave-active { transition: opacity .15s, transform .15s; }
.drop-enter-from, .drop-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── User dropdown ── */
.user-chip {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: var(--radius-md);
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: var(--font-arabic);
    text-align: right;
    transition: background .15s;
}
.user-chip:hover, .user-chip--open { background: var(--bg-muted); }

.user-drop {
    position: absolute;
    bottom: calc(100% + 6px);
    right: 0;
    left: 0;
    z-index: 110;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 10px;
    box-shadow: 0 -8px 24px rgba(0,0,0,.12);
    overflow: hidden;
    min-width: 200px;
}
.user-drop-info {
    padding: 12px 14px 10px;
}
.user-drop-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
}
.user-drop-email {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
    direction: ltr;
    text-align: left;
}
.user-drop-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 9px 14px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: var(--font-arabic);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
    text-decoration: none;
    transition: background .12s;
    text-align: right;
}
.user-drop-item:hover { background: var(--bg-muted); }
.user-drop-logout { color: var(--error) !important; }

/* ── Flash dismiss button ── */
.alert-dismiss {
    margin-right: auto;
    margin-left: 0;
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    color: currentColor;
    opacity: .6;
    cursor: pointer;
    border-radius: 4px;
    display: grid;
    place-items: center;
    transition: opacity .15s, background .15s;
}
.alert-dismiss:hover { opacity: 1; background: rgba(0,0,0,.08); }

/* ── Flash transition ── */
.flash-enter-active, .flash-leave-active { transition: opacity .2s, transform .2s; }
.flash-enter-from, .flash-leave-to { opacity: 0; transform: translateY(-6px); }

/* ── Notifications ── */
.notif-btn { position: relative; }
.notif-badge {
    position: absolute; top: 4px; right: 4px;
    min-width: 16px; height: 16px; border-radius: 99px;
    background: #EF4444; color: #fff;
    font-size: 9px; font-weight: 700; letter-spacing: 0;
    display: grid; place-items: center; padding: 0 4px;
    border: 2px solid var(--bg-surface);
    pointer-events: none;
}
.notif-drop {
    position: absolute; top: calc(100% + 8px); left: 0;
    width: 340px; max-height: 440px;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,.14);
    z-index: 150; overflow: hidden;
    display: flex; flex-direction: column;
}
.notif-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px 10px;
    border-bottom: 1px solid var(--border-subtle);
    flex-shrink: 0;
}
.notif-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.notif-read-all {
    font-size: 11px; color: var(--sada-500); background: none;
    border: none; cursor: pointer; font-family: var(--font-arabic);
    padding: 0; transition: opacity .15s;
}
.notif-read-all:hover { opacity: 0.7; }
.notif-empty {
    padding: 32px 16px; text-align: center;
    display: flex; flex-direction: column; align-items: center;
    font-size: 13px; color: var(--text-muted);
}
.notif-empty p { margin: 0; }
.notif-list { overflow-y: auto; flex: 1; }
.notif-item {
    display: flex; gap: 10px; align-items: flex-start;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    cursor: pointer; transition: background .12s;
    text-decoration: none; color: inherit;
}
.notif-item:hover { background: var(--bg-muted); }
.notif-item--unread { background: color-mix(in oklab, var(--sada-500) 4%, transparent); }
.notif-icon {
    width: 30px; height: 30px; border-radius: 8px;
    display: grid; place-items: center; flex-shrink: 0;
}
.notif-body { flex: 1; min-width: 0; }
.notif-item-title { font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
.notif-item-body { font-size: 11px; color: var(--text-muted); line-height: 1.5; }
.notif-time { font-size: 10px; color: var(--text-muted); flex-shrink: 0; padding-top: 2px; }

/* ── Impersonation banner ── */
.impersonate-banner {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 20px;
    background: #7c3aed;
    color: #fff;
    font-size: 13px; font-weight: 500;
    position: sticky; top: 0; z-index: 80;
}
.impersonate-banner strong { font-weight: 700; }
.impersonate-stop {
    margin-right: auto;
    padding: 4px 14px;
    border-radius: 6px;
    background: rgba(255,255,255,.2);
    color: #fff; font-size: 12px; font-weight: 700;
    border: 1px solid rgba(255,255,255,.35);
    cursor: pointer; font-family: var(--font-arabic);
    transition: background .15s;
}
.impersonate-stop:hover { background: rgba(255,255,255,.3); }
</style>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import { useUiStore } from '@/Stores/ui'
import Icon from '@/Components/Base/Icon.vue'
import Toast from '@/Components/Base/Toast.vue'
import type { PageProps } from '@/Types'

const props = defineProps<{ title?: string; crumbs?: string[] }>()

const page = usePage<PageProps>()
const ui   = useUiStore()

const user       = computed(() => page.props.auth?.user)
const ws         = computed(() => page.props.currentWorkspace)
const workspaces = computed(() => page.props.workspaces ?? [])
const flash      = computed(() => page.props.flash)

const mobileOpen   = ref(false)
const wsDropOpen   = ref(false)
const switching    = ref<number | null>(null)

const flashVisible = ref(false)
let   flashTimer: ReturnType<typeof setTimeout> | null = null

watch(flash, (val) => {
    if (val?.success || val?.error) {
        flashVisible.value = true
        if (flashTimer) clearTimeout(flashTimer)
        flashTimer = setTimeout(() => { flashVisible.value = false }, 5000)
    } else {
        flashVisible.value = false
    }
}, { immediate: true })

function dismissFlash() {
    if (flashTimer) { clearTimeout(flashTimer); flashTimer = null }
    flashVisible.value = false
}

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
    { label: 'الحملات الإعلانية',  href: '/campaigns',  icon: 'megaphone' },
    { label: 'المواسم',            href: '/seasonal',   icon: 'moon' },
    { label: 'التحليلات',          href: '/analytics',  icon: 'chart' },
]

const navAccount = [
    { label: 'الحسابات المرتبطة', href: '/social/accounts', icon: 'instagram' },
    { label: 'الفوترة',           href: '/billing',         icon: 'credit' },
    { label: 'الإعدادات',         href: '/settings',        icon: 'settings' },
]

function isActive(href: string) {
    return currentPath.value === href || currentPath.value.startsWith(href + '/')
}

function closeMobile() { mobileOpen.value = false }
function toggleWsDrop() { wsDropOpen.value = !wsDropOpen.value }
function closeWsDrop()  { wsDropOpen.value = false }

function switchWorkspace(id: number) {
    if (id === ws.value?.id) { closeWsDrop(); return; }
    switching.value = id
    router.post(`/workspaces/${id}/switch`, {}, {
        onFinish: () => { switching.value = null; closeWsDrop(); },
    })
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') { closeMobile(); closeWsDrop(); }
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => {
    window.removeEventListener('keydown', onKey)
    if (flashTimer) clearTimeout(flashTimer)
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
                    <div class="mark">ص</div>
                    <span>صدى</span>
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

                <div class="user-chip">
                    <div class="uc-avatar">{{ userInitials || 'أح' }}</div>
                    <div class="uc-info">
                        <div class="uc-name">{{ user?.name || 'المستخدم' }}</div>
                        <div class="uc-role">مالك · Admin</div>
                    </div>
                    <Icon name="chevronDown" :size="14" />
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

                    <button class="btn btn-icon btn-ghost" title="الإشعارات">
                        <Icon name="bell" />
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <Transition name="flash">
                <div v-if="flashVisible && (flash?.success || flash?.error)" style="padding: 0 32px; padding-top: 16px;">
                    <div v-if="flash.success" class="alert alert-success">
                        <div class="alert-icon"><Icon name="check" :size="14" /></div>
                        <div class="alert-body"><div class="alert-desc">{{ flash.success }}</div></div>
                        <button class="alert-dismiss" @click="dismissFlash" aria-label="إغلاق">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div v-if="flash.error" class="alert alert-error">
                        <div class="alert-icon"><Icon name="x" :size="14" /></div>
                        <div class="alert-body"><div class="alert-desc">{{ flash.error }}</div></div>
                        <button class="alert-dismiss" @click="dismissFlash" aria-label="إغلاق">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </Transition>

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
</style>

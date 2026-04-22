<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { useUiStore } from '@/Stores/ui'
import Icon from '@/Components/Base/Icon.vue'
import Toast from '@/Components/Base/Toast.vue'
import type { PageProps } from '@/Types'

const props = defineProps<{ title?: string; crumbs?: string[] }>()

const page = usePage<PageProps>()
const ui   = useUiStore()

const user = computed(() => page.props.auth?.user)
const ws   = computed(() => page.props.currentWorkspace)
const flash = computed(() => page.props.flash)

const mobileOpen = ref(false)

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
    { label: 'سجل المحتوى',        href: '/history',    icon: 'clock' },
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

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') closeMobile()
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>

<template>
    <!-- Sidebar overlay (mobile) -->
    <div
        class="sidebar-overlay"
        :data-open="mobileOpen"
        @click="closeMobile"
    />

    <div class="app" :data-theme="ui.theme">

        <!-- ═══════════════════ SIDEBAR ═══════════════════ -->
        <aside class="sidebar" :data-open="mobileOpen">

            <!-- Logo + Workspace -->
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <div class="mark">ص</div>
                    <span>صدى</span>
                </div>

                <button class="workspace-picker">
                    <div class="ws-avatar">{{ wsInitials || 'ع' }}</div>
                    <div class="ws-info">
                        <div class="ws-name">{{ ws?.name || 'اختر مساحة عمل' }}</div>
                        <div class="ws-plan">{{ ws?.business_type || 'Growth' }} · السعودية</div>
                    </div>
                    <Icon name="chevronDown" :size="14" class="ws-chev" />
                </button>
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
                    <!-- Mobile menu trigger -->
                    <button
                        class="btn btn-icon btn-ghost"
                        data-mobile-only="true"
                        aria-label="فتح القائمة"
                        @click="mobileOpen = true"
                    >
                        <Icon name="menu" />
                    </button>

                    <!-- Title + Crumbs -->
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
                    <!-- Search -->
                    <div class="topbar-search" data-desktop-only="true">
                        <Icon name="search" :size="16" style="color:var(--text-muted); flex-shrink:0;" />
                        <input placeholder="ابحث في كل شيء..." />
                        <kbd>⌘K</kbd>
                    </div>

                    <!-- Theme toggle -->
                    <button
                        class="btn btn-icon btn-ghost"
                        :title="ui.theme === 'dark' ? 'وضع النهار' : 'وضع الليل'"
                        @click="ui.toggleTheme()"
                    >
                        <Icon :name="ui.theme === 'dark' ? 'sun' : 'moon'" />
                    </button>

                    <!-- Notifications -->
                    <button class="btn btn-icon btn-ghost" title="الإشعارات">
                        <Icon name="bell" />
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" style="padding: 0 32px; padding-top: 16px;">
                <div v-if="flash.success" class="alert alert-success">
                    <div class="alert-icon">
                        <Icon name="check" :size="14" />
                    </div>
                    <div class="alert-body">
                        <div class="alert-desc">{{ flash.success }}</div>
                    </div>
                </div>
                <div v-if="flash.error" class="alert alert-error">
                    <div class="alert-icon">
                        <Icon name="x" :size="14" />
                    </div>
                    <div class="alert-body">
                        <div class="alert-desc">{{ flash.error }}</div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="content-area">
                <slot />
            </main>
        </div>
    </div>

    <!-- Toast notifications -->
    <Toast />
</template>

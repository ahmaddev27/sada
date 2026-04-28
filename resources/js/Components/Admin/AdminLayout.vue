<script setup lang="ts">
import { computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useUiStore } from '@/Stores/ui'
import Icon from '@/Components/Base/Icon.vue'
import Toast from '@/Components/Base/Toast.vue'
import type { PageProps } from '@/Types'

const page  = usePage<PageProps>()
const ui    = useUiStore()

const user        = computed(() => page.props.auth?.user)
const flash       = computed(() => page.props.flash)
const currentPath = computed(() => page.url)

watch(flash, (val) => {
    if (val?.success) ui.success(val.success)
    if (val?.error)   ui.error(val.error)
    if (val?.warning) ui.warning(val.warning)
}, { immediate: true })

const nav = [
    { href: '/admin',                 label: 'لوحة التحكم',              icon: 'home',      exact: true  },
    { href: '/admin/users',           label: 'المستخدمون',               icon: 'users',     exact: false },
    { href: '/admin/workspaces',      label: 'مساحات العمل',              icon: 'grid',      exact: false },
    { href: '/admin/posts',           label: 'المنشورات',                icon: 'clock',     exact: false },
    { href: '/admin/social-accounts', label: 'الحسابات المرتبطة',        icon: 'instagram', exact: false },
    { href: '/admin/ai-generations',  label: 'توليدات الذكاء الاصطناعي', icon: 'sparkle',   exact: false },
    { href: '/admin/campaigns',       label: 'الحملات الإعلانية',         icon: 'megaphone', exact: false },
    { href: '/admin/billing',         label: 'الفواتير والإيرادات',       icon: 'credit-card', exact: false },
    { href: '/admin/tokens',          label: 'سجل الرصيد',               icon: 'coins',     exact: false },
    { href: '/admin/ai-costs',        label: 'تكاليف الذكاء الاصطناعي',  icon: 'cpu',       exact: false },
    { href: '/admin/ai-models',       label: 'أداء الموديلات',            icon: 'sparkle',   exact: false },
    { href: '/admin/notifications',   label: 'الإشعارات',                icon: 'bell',      exact: false },
    { href: '/admin/system',          label: 'صحة النظام',               icon: 'server',    exact: false },
    { href: '/admin/site-settings',   label: 'إعدادات الموقع',           icon: 'globe',     exact: false },
    { href: '/admin/settings',        label: 'إعدادات النظام',           icon: 'settings',  exact: false },
]

function isActive(href: string, exact: boolean) {
    if (exact) return currentPath.value === href
    return currentPath.value.startsWith(href)
}

const userInitials = computed(() => {
    const name = user.value?.name ?? ''
    return name.split(' ').map((w: string) => w[0]).join('').slice(0, 2) || 'A'
})

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div class="app">

        <!-- ══════════════════ SIDEBAR ══════════════════ -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <!-- Brand -->
                <div class="sidebar-logo">
                    <img
                        :src="ui.theme === 'dark'
                            ? '/images/logo/sada-horizontal-ar-dark.svg'
                            : '/images/logo/sada-horizontal-ar.svg'"
                        class="logo-horizontal"
                        alt="صدى"
                    />
                </div>

                <!-- Admin badge -->
                <div class="admin-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    لوحة الإدارة
                </div>
            </div>

            <!-- Nav -->
            <nav class="sidebar-nav">
                <div class="nav-section">الإدارة</div>
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    :class="['nav-item', { active: isActive(item.href, item.exact) }]"
                >
                    <span class="nav-icon">
                        <Icon :name="item.icon" :size="18" />
                    </span>
                    <span class="nav-label">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom: user -->
            <div class="sidebar-bottom">
                <button class="user-chip" @click="logout">
                    <div class="uc-avatar">
                        <span>{{ userInitials }}</span>
                    </div>
                    <div class="uc-info">
                        <div class="uc-name">{{ user?.name || 'Admin' }}</div>
                        <div class="uc-role">مدير النظام</div>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);flex-shrink:0"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </div>
        </aside>

        <!-- ══════════════════ MAIN ══════════════════ -->
        <div class="app-main">

            <!-- Topbar -->
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                    <div class="admin-topbar-badge">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        لوحة الإدارة
                    </div>
                </div>

                <div class="topbar-actions">
                    <!-- Dark mode toggle -->
                    <button
                        class="btn btn-icon btn-ghost"
                        :title="ui.theme === 'dark' ? 'وضع النهار' : 'وضع الليل'"
                        @click="ui.toggleTheme()"
                    >
                        <Icon :name="ui.theme === 'dark' ? 'sun' : 'moon'" />
                    </button>

                    <!-- Logout -->
                    <button class="btn btn-icon btn-ghost" title="تسجيل الخروج" @click="logout">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </header>

            <!-- Page content -->
            <main class="content-area">
                <slot />
            </main>
        </div>
    </div>

    <Toast />
</template>

<style scoped>
/* ── Match user panel: stronger border for visible separation ─── */
.sidebar {
    border-left: 1px solid var(--border-default);
}
.sidebar-top {
    border-bottom: 1px solid var(--border-default);
}
.sidebar-bottom {
    border-top: 1px solid var(--border-default);
}
.topbar {
    border-bottom: 1px solid var(--border-default);
}

/* Admin badge in sidebar under logo */
.admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: var(--accent-text);
    background: var(--accent-soft);
    padding: 4px 10px;
    border-radius: var(--radius-full);
    margin-top: 10px;
    letter-spacing: 0.02em;
}

/* Admin topbar badge */
.admin-topbar-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent-text);
    background: var(--accent-soft);
    padding: 5px 12px;
    border-radius: var(--radius-full);
}

</style>

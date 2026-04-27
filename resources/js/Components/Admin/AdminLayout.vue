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
    { href: '/admin',                label: 'لوحة التحكم',              icon: 'home',      exact: true  },
    { href: '/admin/users',          label: 'المستخدمون',               icon: 'users',     exact: false },
    { href: '/admin/workspaces',     label: 'Workspaces',               icon: 'grid',      exact: false },
    { href: '/admin/posts',          label: 'المنشورات',                icon: 'clock',     exact: false },
    { href: '/admin/social-accounts',label: 'الحسابات المرتبطة',        icon: 'instagram', exact: false },
    { href: '/admin/ai-generations', label: 'توليدات الذكاء الاصطناعي', icon: 'sparkle',   exact: false },
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

            <!-- Bottom: user + actions -->
            <div class="sidebar-bottom">
                <Link href="/dashboard" class="admin-back-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    العودة للتطبيق
                </Link>

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

/* Back button — matches nav-item style but subtler */
.admin-back-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 10px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    border: 1px solid var(--border-subtle);
    background: transparent;
    transition: background var(--dur-fast), color var(--dur-fast), border-color var(--dur-fast);
    margin-bottom: 4px;
    font-family: var(--font-arabic);
}
.admin-back-btn:hover {
    background: var(--bg-muted);
    color: var(--text-primary);
    border-color: var(--border-default);
}
</style>

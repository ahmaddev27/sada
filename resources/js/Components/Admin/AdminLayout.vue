<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useUiStore } from '@/Stores/ui'
import Toast from '@/Components/Base/Toast.vue'
import type { PageProps } from '@/Types'

const page = usePage<PageProps>()
const ui   = useUiStore()

const currentPath = computed(() => page.url)
const user        = computed(() => page.props.auth?.user)
const flash       = computed(() => page.props.flash)

import { watch } from 'vue'
watch(flash, (val) => {
    if (val?.success) ui.success(val.success)
    if (val?.error)   ui.error(val.error)
}, { immediate: true })

const nav = [
    { href: '/admin',               label: 'لوحة التحكم',              exact: true,  icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { href: '/admin/users',         label: 'المستخدمون',               exact: false, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { href: '/admin/workspaces',    label: 'Workspaces',               exact: false, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 00-1-1h-2a1 1 0 00-1 1v5m4 0H9' },
    { href: '/admin/posts',         label: 'المنشورات',                exact: false, icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { href: '/admin/social-accounts', label: 'الحسابات المرتبطة',     exact: false, icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' },
    { href: '/admin/ai-generations', label: 'توليدات الذكاء الاصطناعي', exact: false, icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
]

function isActive(href: string, exact: boolean) {
    if (exact) return currentPath.value === href
    return currentPath.value.startsWith(href)
}

function logout() {
    router.post('/logout')
}

const mobileOpen = ref(false)
</script>

<template>
    <div class="admin-shell" :data-theme="ui.theme">

        <!-- Mobile overlay -->
        <div v-if="mobileOpen" class="mobile-overlay" @click="mobileOpen = false" />

        <!-- ── Sidebar ── -->
        <aside class="admin-sidebar" :class="{ 'admin-sidebar--open': mobileOpen }">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="brand-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    </div>
                    <div>
                        <div class="brand-name">صدى — الإدارة</div>
                        <div class="brand-sub">Admin Panel</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">الإدارة</div>
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    :class="['nav-item', { 'nav-item--active': isActive(item.href, item.exact) }]"
                    @click="mobileOpen = false"
                >
                    <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icon" />
                    </svg>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="sidebar-footer">
                <Link href="/dashboard" class="back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    العودة للتطبيق
                </Link>
                <button class="logout-btn" @click="logout">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    خروج
                </button>
            </div>
        </aside>

        <!-- ── Main ── -->
        <div class="admin-main">
            <header class="admin-topbar">
                <button class="mobile-menu-btn" @click="mobileOpen = true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="topbar-admin-badge">لوحة الإدارة</div>
                <div class="topbar-user">
                    <span class="user-name">{{ user?.name }}</span>
                    <div class="user-avatar">{{ user?.name?.charAt(0) ?? 'A' }}</div>
                </div>
            </header>

            <main class="admin-content">
                <slot />
            </main>
        </div>
    </div>

    <Toast />
</template>

<style scoped>
/* ── Shell ── */
.admin-shell {
    display: flex;
    min-height: 100vh;
    background: var(--bg-page);
    color: var(--text-primary);
    direction: rtl;
}

/* ── Sidebar ── */
.admin-sidebar {
    width: 240px;
    flex-shrink: 0;
    background: var(--bg-surface);
    border-left: 1px solid var(--border-default);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
}

.sidebar-header {
    padding: 18px 16px 14px;
    border-bottom: 1px solid var(--border-subtle);
    flex-shrink: 0;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.brand-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.brand-sub {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 2px;
}

.sidebar-nav {
    flex: 1;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto;
}

.nav-section {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text-muted);
    padding: 6px 10px 4px;
    margin-top: 4px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary, var(--text-muted));
    text-decoration: none;
    transition: background .12s, color .12s;
}

.nav-item:hover { background: var(--bg-muted); color: var(--text-primary); }
.nav-item--active {
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-500);
    font-weight: 600;
}

.nav-icon { flex-shrink: 0; opacity: .7; }
.nav-item--active .nav-icon { opacity: 1; }

.sidebar-footer {
    padding: 12px 10px;
    border-top: 1px solid var(--border-subtle);
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}

.back-link,
.logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    padding: 8px 10px;
    border-radius: var(--radius-md);
    transition: background .12s, color .12s;
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: var(--font-arabic);
    width: 100%;
    text-align: right;
}

.back-link:hover  { background: var(--bg-muted); color: var(--text-primary); }
.logout-btn:hover { background: color-mix(in oklab, var(--error) 10%, transparent); color: var(--error); }

/* ── Main ── */
.admin-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.admin-topbar {
    height: 56px;
    background: var(--bg-surface);
    border-bottom: 1px solid var(--border-default);
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 24px;
    position: sticky;
    top: 0;
    z-index: 50;
    flex-shrink: 0;
}

.mobile-menu-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: var(--radius-md);
    transition: background .12s;
}
.mobile-menu-btn:hover { background: var(--bg-muted); }

.topbar-admin-badge {
    font-size: 12px;
    font-weight: 700;
    color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    padding: 3px 10px;
    border-radius: 99px;
    flex: 1;
}

.topbar-user {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.admin-content { flex: 1; overflow-y: auto; }

/* ── Mobile ── */
.mobile-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 99;
}

@media (max-width: 768px) {
    .admin-sidebar {
        position: fixed;
        top: 0;
        right: -260px;
        height: 100vh;
        transition: right .25s ease;
    }
    .admin-sidebar--open { right: 0; }
    .mobile-overlay { display: block; }
    .mobile-menu-btn { display: flex; }
}
</style>

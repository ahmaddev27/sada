<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const currentPath = computed(() => page.url)

const nav = [
    { href: '/admin',             label: 'لوحة التحكم', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { href: '/admin/users',       label: 'المستخدمون',  icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { href: '/admin/workspaces',  label: 'Workspaces',  icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 00-1-1h-2a1 1 0 00-1 1v5m4 0H9' },
]
</script>

<template>
    <div class="admin-shell">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="admin-brand-icon">⚙</span>
                <span class="admin-brand-name">لوحة الإدارة</span>
            </div>

            <nav class="admin-nav">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    :class="['admin-nav-item', { 'admin-nav-item--active': currentPath.startsWith(item.href) && (item.href === '/admin' ? currentPath === '/admin' : true) }]"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <div class="admin-sidebar-footer">
                <Link href="/dashboard" class="admin-back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    العودة للتطبيق
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="admin-main">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.admin-shell {
    display: flex; min-height: 100vh;
    background: #0f1117;
    color: #e2e8f0;
    direction: rtl;
}

/* ── Sidebar ── */
.admin-sidebar {
    width: 240px; flex-shrink: 0;
    background: #161b27;
    border-left: 1px solid #1e2535;
    display: flex; flex-direction: column;
    padding: 0;
    position: sticky; top: 0; height: 100vh;
}

.admin-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 20px 18px 16px;
    border-bottom: 1px solid #1e2535;
}
.admin-brand-icon { font-size: 18px; }
.admin-brand-name { font-size: 14px; font-weight: 700; color: #f1f5f9; }

.admin-nav {
    flex: 1; padding: 12px 10px; display: flex; flex-direction: column; gap: 2px;
}
.admin-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 8px;
    font-size: 13px; font-weight: 500; color: #94a3b8;
    text-decoration: none; transition: all .15s;
}
.admin-nav-item:hover { background: #1e2535; color: #e2e8f0; }
.admin-nav-item--active { background: color-mix(in oklab, #0F6F5C 20%, transparent); color: #4ade80; }

.admin-sidebar-footer { padding: 12px 10px; border-top: 1px solid #1e2535; }
.admin-back-link {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; color: #64748b;
    text-decoration: none; padding: 8px 10px; border-radius: 7px;
    transition: all .15s;
}
.admin-back-link:hover { color: #94a3b8; background: #1e2535; }

/* ── Main content ── */
.admin-main { flex: 1; overflow-y: auto; }
</style>

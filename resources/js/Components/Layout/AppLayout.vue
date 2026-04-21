<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUiStore } from '@/Stores/ui';
import Icon from '@/Components/Base/Icon.vue';
import Toast from '@/Components/Base/Toast.vue';
import type { PageProps } from '@/Types';

defineProps<{ title?: string; crumbs?: string[] }>();

const page     = usePage<PageProps>();
const ui       = useUiStore();
const user      = computed(() => page.props.auth.user);
const ws        = computed(() => page.props.currentWorkspace);

const flash     = computed(() => page.props.flash);

const mobileOpen = ref(false);

const tokenPct = computed(() => {
    if (!user.value) return 0;
    const max = 2000; // TODO: pull from subscription plan
    return Math.min(100, Math.round((user.value.token_balance / max) * 100));
});

const navItems = [
    { label: 'الرئيسية',          href: '/dashboard',  icon: 'home' },
    { label: 'توليد محتوى',       href: '/generate',   icon: 'sparkle', badge: 'جديد' },
    { label: 'التقويم',           href: '/calendar',   icon: 'calendar' },
    { label: 'سجل المحتوى',       href: '/history',    icon: 'clock' },
    { label: 'الحملات الإعلانية', href: '/campaigns',  icon: 'megaphone' },
    { label: 'المواسم',           href: '/seasonal',   icon: 'moon' },
    { label: 'التحليلات',         href: '/analytics',  icon: 'chart' },
];

const bottomItems = [
    { label: 'الفوترة',   href: '/billing',  icon: 'credit' },
    { label: 'الإعدادات', href: '/settings', icon: 'settings' },
];

function isActive(href: string): boolean {
    return window.location.pathname.startsWith(href);
}

// Close mobile sidebar on ESC
function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') mobileOpen.value = false;
}
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <div
        class="app"
        :data-collapsed="!ui.sidebarOpen ? 'true' : 'false'"
    >
        <!-- ─── Sidebar ─────────────────────────────────────────── -->
        <aside class="sidebar" :data-open="mobileOpen">

            <!-- Logo + Workspace picker -->
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <div class="mark">ص</div>
                    <span>صدى</span>
                </div>

                <button
                    v-if="ws"
                    class="workspace-picker"
                    title="تبديل مساحة العمل"
                    @click="$inertia.visit('/workspaces')"
                >
                    <div class="ws-avatar">{{ ws.name.charAt(0) }}</div>
                    <div class="ws-info">
                        <div class="ws-name">{{ ws.name }}</div>
                        <div class="ws-plan">{{ ws.default_dialect === 'sa' ? '🇸🇦 السعودية' : ws.default_dialect }}</div>
                    </div>
                    <Icon name="chevronDown" :size="14" class="ws-chevron" style="color: var(--text-faint);" />
                </button>

                <button
                    v-else
                    class="workspace-picker"
                    @click="$inertia.visit('/workspaces')"
                >
                    <div class="ws-avatar">+</div>
                    <div class="ws-info">
                        <div class="ws-name">إنشاء مساحة عمل</div>
                    </div>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">العمل</div>

                <a
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="['nav-item', { active: isActive(item.href) }]"
                    :title="!ui.sidebarOpen ? item.label : undefined"
                >
                    <Icon :name="item.icon" :size="18" />
                    <span>{{ item.label }}</span>
                    <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
                </a>

                <div class="nav-section" style="margin-top: 8px;">الحساب</div>

                <a
                    v-for="item in bottomItems"
                    :key="item.href"
                    :href="item.href"
                    :class="['nav-item', { active: isActive(item.href) }]"
                    :title="!ui.sidebarOpen ? item.label : undefined"
                >
                    <Icon :name="item.icon" :size="18" />
                    <span>{{ item.label }}</span>
                </a>
            </nav>

            <!-- Token meter + User chip -->
            <div class="sidebar-bottom">
                <!-- Token meter -->
                <div v-if="user" class="token-meter">
                    <div class="token-meter-head">
                        <span class="label">التوكنز</span>
                        <span class="val num-tabular">
                            {{ user.token_balance.toLocaleString('ar-EG') }} / 2,000
                        </span>
                    </div>
                    <div class="token-bar">
                        <div class="token-bar-fill" :style="{ width: tokenPct + '%' }" />
                    </div>
                    <button class="token-refill" @click="$inertia.visit('/billing')">
                        شحن المزيد
                    </button>
                </div>

                <!-- User chip -->
                <div v-if="user" class="user-chip">
                    <div class="u-avatar">{{ user.name.charAt(0) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ user.name }}</div>
                        <div class="user-role">{{ user.email }}</div>
                    </div>
                    <Icon name="chevronDown" :size="14" style="color: var(--text-faint); flex-shrink: 0;" />
                </div>

                <!-- Sidebar collapse toggle (desktop) -->
                <button
                    class="btn btn-ghost btn-sm"
                    data-desktop-only="true"
                    style="width: 100%; justify-content: center; gap: 8px;"
                    @click="ui.toggleSidebar()"
                    :title="ui.sidebarOpen ? 'طي القائمة' : 'توسيع القائمة'"
                >
                    <Icon :name="ui.sidebarOpen ? 'chevronRight' : 'chevronLeft'" :size="16" />
                    <span v-if="ui.sidebarOpen" style="font-size: 12px;">طي القائمة</span>
                </button>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div
            class="sidebar-overlay"
            :data-open="mobileOpen"
            @click="mobileOpen = false"
        />

        <!-- ─── Main area ───────────────────────────────────────── -->
        <div class="app-main">

            <!-- Topbar -->
            <header class="topbar">
                <!-- Mobile menu toggle -->
                <button
                    class="btn btn-icon btn-ghost"
                    data-mobile-only="true"
                    aria-label="فتح القائمة"
                    @click="mobileOpen = true"
                >
                    <Icon name="menu" :size="20" />
                </button>

                <!-- Page title / breadcrumbs -->
                <div class="topbar-title">
                    <div v-if="crumbs && crumbs.length" class="crumbs">
                        <span v-for="(crumb, i) in crumbs" :key="i">
                            {{ crumb }}<span v-if="i < crumbs.length - 1" style="opacity: 0.5; margin: 0 4px;">•</span>
                        </span>
                    </div>
                    <h1>{{ title || 'صدى' }}</h1>
                </div>

                <!-- Actions -->
                <div class="topbar-actions">
                    <!-- Search -->
                    <div class="topbar-search" data-desktop-only="true">
                        <Icon name="search" :size="16" style="color: var(--text-muted); flex-shrink: 0;" />
                        <input placeholder="ابحث في كل شيء..." />
                        <kbd style="font-size: 10px; padding: 2px 6px; background: var(--bg-elevated); border-radius: 4px; color: var(--text-muted); font-family: var(--font-mono);">⌘K</kbd>
                    </div>

                    <!-- Theme toggle -->
                    <button
                        class="btn btn-icon btn-ghost"
                        :title="ui.theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
                        @click="ui.toggleTheme()"
                    >
                        <Icon :name="ui.theme === 'dark' ? 'sun' : 'moon'" :size="18" />
                    </button>

                    <!-- Notifications -->
                    <button class="btn btn-icon btn-ghost" title="الإشعارات">
                        <Icon name="bell" :size="18" />
                    </button>

                    <!-- User avatar -->
                    <div
                        v-if="user"
                        style="
                            width: 36px; height: 36px; border-radius: 50%;
                            background: linear-gradient(135deg, var(--sada-400), var(--sada-600));
                            color: #fff; font-weight: 700; font-size: 14px;
                            display: grid; place-items: center; flex-shrink: 0; cursor: pointer;
                        "
                        :title="user.name"
                    >
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div
                v-if="flash?.success || flash?.error"
                style="padding: 12px 32px 0;"
            >
                <div
                    v-if="flash?.success"
                    style="
                        padding: 12px 16px; border-radius: var(--radius-md); font-size: 14px;
                        background: var(--success-bg); color: var(--success);
                        border: 1px solid color-mix(in oklab, var(--success) 20%, transparent);
                    "
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash?.error"
                    style="
                        padding: 12px 16px; border-radius: var(--radius-md); font-size: 14px;
                        background: var(--error-bg); color: var(--error);
                        border: 1px solid color-mix(in oklab, var(--error) 20%, transparent);
                    "
                >
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page content -->
            <main class="content-area">
                <slot />
            </main>
        </div>
    </div>

    <Toast />
</template>

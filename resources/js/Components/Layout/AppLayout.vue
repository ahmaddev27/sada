<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/Types';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const currentWorkspace = computed(() => page.props.currentWorkspace);

const sidebarCollapsed = ref(false);
const toggleSidebar = () => (sidebarCollapsed.value = !sidebarCollapsed.value);

const navItems = [
    { label: 'لوحة التحكم', href: '/dashboard', icon: '⊞' },
    { label: 'إنشاء محتوى', href: '/generate', icon: '✦' },
    { label: 'التقويم', href: '/calendar', icon: '◫' },
    { label: 'المواسم', href: '/seasonal', icon: '◈' },
    { label: 'الحملات', href: '/campaigns', icon: '◉' },
    { label: 'التحليلات', href: '/analytics', icon: '◎' },
    { label: 'الفوترة', href: '/billing', icon: '◑' },
    { label: 'الإعدادات', href: '/settings', icon: '◧' },
];
</script>

<template>
    <div
        dir="rtl"
        class="flex min-h-screen"
        style="background-color: var(--color-bg-page); font-family: var(--font-arabic);"
    >
        <!-- Sidebar -->
        <aside
            :class="[
                'flex flex-col flex-shrink-0 transition-all duration-300 border-l',
                sidebarCollapsed ? 'w-[72px]' : 'w-[260px]',
            ]"
            style="background-color: var(--color-bg-surface); border-color: var(--color-ink-100);"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-4 py-5 border-b" style="border-color: var(--color-ink-100);">
                <div
                    class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                    style="background-color: var(--color-sada-500);"
                >
                    <span class="text-white text-sm font-bold">ص</span>
                </div>
                <span v-if="!sidebarCollapsed" class="text-base font-bold" style="color: var(--color-ink-900);">
                    صدى
                </span>
            </div>

            <!-- Workspace selector -->
            <div
                v-if="!sidebarCollapsed && currentWorkspace"
                class="px-3 py-3 border-b"
                style="border-color: var(--color-ink-100);"
            >
                <button
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm transition-colors"
                    style="background-color: var(--color-bg-muted); color: var(--color-ink-700);"
                >
                    <span class="truncate font-medium">{{ currentWorkspace.name }}</span>
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
                <a
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :title="sidebarCollapsed ? item.label : undefined"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-[var(--color-ink-50)]',
                        sidebarCollapsed ? 'justify-center' : '',
                    ]"
                    style="color: var(--color-ink-600);"
                >
                    <span class="text-base leading-none w-5 text-center flex-shrink-0">{{ item.icon }}</span>
                    <span v-if="!sidebarCollapsed">{{ item.label }}</span>
                </a>
            </nav>

            <!-- Token balance -->
            <div
                v-if="!sidebarCollapsed && user"
                class="mx-3 mb-3 px-3 py-2 rounded-lg"
                style="background-color: var(--color-sada-50); border: 1px solid var(--color-sada-100);"
            >
                <p class="text-xs mb-0.5" style="color: var(--color-sada-700);">رصيد الرموز</p>
                <p class="text-sm font-bold num-tabular" style="color: var(--color-sada-600);">
                    {{ user.token_balance.toLocaleString('ar-SA') }}
                </p>
            </div>

            <!-- Collapse toggle -->
            <div class="p-2 border-t" style="border-color: var(--color-ink-100);">
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center justify-center p-2 rounded-lg transition-colors hover:bg-[var(--color-ink-50)]"
                    style="color: var(--color-ink-400);"
                    :title="sidebarCollapsed ? 'توسيع القائمة' : 'طي القائمة'"
                >
                    <svg
                        :class="['w-4 h-4 transition-transform duration-300', sidebarCollapsed ? 'rotate-180' : '']"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 min-w-0">
            <!-- Top header -->
            <header
                class="flex items-center justify-between px-6 border-b flex-shrink-0"
                style="
                    background-color: var(--color-bg-surface);
                    border-color: var(--color-ink-100);
                    min-height: 56px;
                "
            >
                <slot name="header">
                    <h1 class="text-base font-semibold" style="color: var(--color-ink-900);">
                        <slot name="title" />
                    </h1>
                </slot>

                <div class="flex items-center gap-3">
                    <div
                        v-if="user"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                        style="background-color: var(--color-sada-100); color: var(--color-sada-700);"
                    >
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-8 pt-4">
                <div
                    v-if="$page.props.flash?.success"
                    class="px-4 py-3 rounded-lg text-sm"
                    style="background-color: var(--color-success-bg); color: var(--color-success);"
                >
                    {{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.flash?.error"
                    class="px-4 py-3 rounded-lg text-sm"
                    style="background-color: var(--color-error-bg); color: var(--color-error);"
                >
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

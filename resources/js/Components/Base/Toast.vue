<script setup lang="ts">
import { useUiStore } from '@/Stores/ui';

const ui = useUiStore();

const VARIANTS = {
    success: {
        border: 'var(--success)',
        iconBg: 'var(--success-bg)',
        iconColor: 'var(--success)',
        // checkmark
        paths: ['M20 6 9 17 4 12'],
    },
    error: {
        border: 'var(--error)',
        iconBg: 'var(--error-bg)',
        iconColor: 'var(--error)',
        paths: ['M18 6 6 18M6 6l12 12'],
    },
    warning: {
        border: 'var(--warning)',
        iconBg: 'var(--warning-bg)',
        iconColor: 'var(--warning)',
        // exclamation
        paths: ['M12 9v4M12 17h.01', 'M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'],
    },
    info: {
        border: 'var(--info)',
        iconBg: 'var(--info-bg)',
        iconColor: 'var(--info)',
        paths: ['M12 16v-4M12 8h.01', 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z'],
    },
} as const;
</script>

<template>
    <Teleport to="body">
        <div class="toast-stack" dir="rtl">
            <TransitionGroup name="toast" tag="div" style="display:contents">
                <div
                    v-for="item in ui.items"
                    :key="item.id"
                    class="toast-item"
                    :style="`border-right: 3px solid ${VARIANTS[item.variant].border}`"
                    @click="ui.remove(item.id)"
                >
                    <!-- icon box -->
                    <div
                        class="toast-icon"
                        :style="`background:${VARIANTS[item.variant].iconBg};color:${VARIANTS[item.variant].iconColor}`"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path v-for="(d, i) in VARIANTS[item.variant].paths" :key="i" :d="d" />
                        </svg>
                    </div>

                    <!-- body -->
                    <div class="toast-body">
                        <div class="toast-title">{{ item.title }}</div>
                        <div v-if="item.desc" class="toast-desc">{{ item.desc }}</div>
                    </div>

                    <!-- close -->
                    <button class="toast-close" @click.stop="ui.remove(item.id)" aria-label="إغلاق">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-stack {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 8px;
    pointer-events: none;
}

.toast-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    border-radius: var(--radius-lg);
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    min-width: 280px;
    max-width: 360px;
    font-family: var(--font-arabic);
    pointer-events: all;
    cursor: pointer;
}

.toast-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.toast-body {
    flex: 1;
    min-width: 0;
}

.toast-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
}

.toast-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
    line-height: 1.5;
}

.toast-close {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    border-radius: 4px;
    border: none;
    background: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0;
    transition: background 150ms, color 150ms;
    align-self: flex-start;
}

.toast-close:hover {
    background: var(--bg-muted);
    color: var(--text-primary);
}

/* Transitions */
.toast-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateY(10px); }
.toast-leave-to     { opacity: 0; transform: translateY(8px); }
.toast-move         { transition: transform 0.2s ease; }
</style>

<script setup lang="ts">
import { useToastStore } from '@/Stores/ui';

const toast = useToastStore();
</script>

<template>
    <!-- RTL: slides from right — fixed to top-right (which is top-left visually in RTL) -->
    <Teleport to="body">
        <div
            style="
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            "
            dir="rtl"
        >
            <Transition
                v-for="item in toast.items"
                :key="item.id"
                name="toast"
                appear
            >
                <div
                    :style="`
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        min-width: 280px;
                        max-width: 360px;
                        padding: 12px 16px;
                        border-radius: 10px;
                        box-shadow: 0 8px 24px rgba(0,0,0,.12);
                        font-size: 13px;
                        font-family: var(--font-arabic);
                        pointer-events: all;
                        cursor: pointer;
                        background: ${item.variant === 'success' ? '#065F46' : item.variant === 'error' ? '#991B1B' : item.variant === 'warning' ? '#92400E' : 'var(--color-ink-800)'};
                        color: #fff;
                    `"
                    @click="toast.remove(item.id)"
                >
                    <!-- icon -->
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
                        <path v-if="item.variant === 'success'" d="M20 6 9 17 4 12"/>
                        <path v-else-if="item.variant === 'error'" d="M18 6 6 18M6 6l12 12"/>
                        <path v-else-if="item.variant === 'warning'" d="M12 9v4M12 17h.01"/>
                        <path v-else d="M12 16v-4M12 8h.01M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                    </svg>
                    <span style="line-height: 1.5; flex: 1;">{{ item.message }}</span>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active { transition: all .25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all .2s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(-20px); }
.toast-leave-to     { opacity: 0; transform: translateX(-20px); }
</style>

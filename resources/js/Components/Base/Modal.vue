<script setup lang="ts">
type Size = 'sm' | 'md' | 'lg' | 'xl';

withDefaults(defineProps<{
    show:   boolean;
    title?: string;
    size?:  Size;
}>(), {
    size: 'md',
});

const emit = defineEmits<{ (e: 'close'): void }>();

const widths: Record<Size, string> = {
    sm: '400px',
    md: '540px',
    lg: '720px',
    xl: '900px',
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                dir="rtl"
                style="
                    position: fixed; inset: 0; z-index: 1000;
                    display: flex; align-items: center; justify-content: center;
                    padding: 20px;
                "
            >
                <!-- Backdrop -->
                <div
                    style="position: absolute; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(2px);"
                    @click="emit('close')"
                />

                <!-- Panel -->
                <div
                    :style="`
                        position: relative;
                        width: 100%;
                        max-width: ${widths[size]};
                        background: var(--color-bg-card);
                        border: 1px solid var(--color-border-default);
                        border-radius: 14px;
                        box-shadow: 0 20px 60px rgba(0,0,0,.2);
                        font-family: var(--font-arabic);
                        overflow: hidden;
                    `"
                >
                    <!-- Header -->
                    <div
                        v-if="title || $slots.header"
                        style="
                            display: flex; align-items: center; justify-content: space-between;
                            padding: 18px 20px;
                            border-bottom: 1px solid var(--color-border-subtle);
                        "
                    >
                        <slot name="header">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--color-ink-base);">{{ title }}</h3>
                        </slot>
                        <button
                            style="
                                width: 28px; height: 28px; border-radius: 6px;
                                border: none; background: transparent;
                                color: var(--color-ink-muted); cursor: pointer;
                                display: grid; place-items: center;
                                transition: background .15s;
                            "
                            @click="emit('close')"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div style="padding: 20px;">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div
                        v-if="$slots.footer"
                        style="
                            padding: 16px 20px;
                            border-top: 1px solid var(--color-border-subtle);
                            display: flex; gap: 8px; justify-content: flex-start;
                        "
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all .2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from :deep(> div:last-child) { transform: scale(0.96) translateY(8px); }
.modal-enter-active :deep(> div:last-child) { transition: transform .2s cubic-bezier(0.34, 1.56, 0.64, 1); }
</style>

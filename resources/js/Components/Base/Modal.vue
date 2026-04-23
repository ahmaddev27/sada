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
            <div v-if="show" class="modal-root" dir="rtl">

                <!-- Layer 1: backdrop (blur + dim) -->
                <div class="modal-backdrop" @click="emit('close')" />

                <!-- Layer 2: panel wrapper (flex centering, above backdrop) -->
                <div class="modal-wrap">
                    <div class="modal-panel" :style="`max-width: ${widths[size]};`">

                        <!-- Header -->
                        <div v-if="title || $slots.header" class="modal-header">
                            <slot name="header">
                                <h3 class="modal-title">{{ title }}</h3>
                            </slot>
                            <button class="modal-close" @click="emit('close')" aria-label="إغلاق">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="modal-footer">
                            <slot name="footer" />
                        </div>

                    </div>
                </div>

            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Root: fixed fullscreen, creates the stacking context */
.modal-root {
    position: fixed;
    inset: 0;
    z-index: 1000;
}

/* Layer 1 — backdrop behind everything */
.modal-backdrop {
    position: absolute;
    inset: 0;
    z-index: 0;
    background: rgba(0, 0, 0, .45);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Layer 2 — separate flex container for centering, on top of backdrop */
.modal-wrap {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    pointer-events: none;
}

/* Panel itself */
.modal-panel {
    position: relative;
    width: 100%;
    pointer-events: all;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
    font-family: var(--font-arabic);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-subtle);
    flex-shrink: 0;
}

.modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
}

.modal-close {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: background .15s;
    flex-shrink: 0;
}
.modal-close:hover { background: var(--bg-muted); color: var(--text-primary); }

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.modal-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--border-subtle);
    display: flex;
    gap: 8px;
    justify-content: flex-start;
    flex-shrink: 0;
}

/* ── Transitions ── */
.modal-enter-active,
.modal-leave-active {
    transition: opacity .18s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .modal-panel {
    animation: modal-in .22s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.modal-leave-active .modal-panel {
    animation: modal-out .15s ease forwards;
}

@keyframes modal-in {
    from { opacity: 0; transform: scale(.95) translateY(8px); }
    to   { opacity: 1; transform: none; }
}
@keyframes modal-out {
    from { opacity: 1; transform: none; }
    to   { opacity: 0; transform: scale(.97) translateY(4px); }
}
</style>

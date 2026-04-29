<script setup lang="ts">
import { useConfirmStore } from '@/Stores/confirm'

const store = useConfirmStore()
</script>

<template>
    <Teleport to="body">
        <Transition name="cm">
            <div v-if="store.open" class="cm-backdrop" @click.self="store.doCancel()">
                <div class="cm-box" role="alertdialog" aria-modal="true" :aria-labelledby="'cm-title'">

                    <!-- Icon -->
                    <div :class="['cm-icon', store.dangerous ? 'cm-icon--danger' : 'cm-icon--info']">
                        <svg v-if="store.dangerous" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>

                    <!-- Text -->
                    <h3 id="cm-title" class="cm-title">{{ store.title }}</h3>
                    <p v-if="store.message" class="cm-message">{{ store.message }}</p>

                    <!-- Actions -->
                    <div class="cm-actions">
                        <button class="cm-btn cm-btn--cancel" @click="store.doCancel()">
                            {{ store.cancelText }}
                        </button>
                        <button
                            :class="['cm-btn', store.dangerous ? 'cm-btn--danger' : 'cm-btn--primary']"
                            @click="store.doConfirm()"
                        >
                            {{ store.confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.cm-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0, 0, 0, .45);
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
    backdrop-filter: blur(2px);
}

.cm-box {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    padding: 28px 28px 24px;
    width: 100%; max-width: 380px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}

/* Icon */
.cm-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: grid; place-items: center;
    margin: 0 auto 16px;
}
.cm-icon--danger {
    background: color-mix(in oklab, #ef4444 12%, transparent);
    color: #ef4444;
}
.cm-icon--info {
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
}

/* Text */
.cm-title {
    font-size: 16px; font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 8px;
    line-height: 1.4;
}
.cm-message {
    font-size: 13px; color: var(--text-muted);
    margin: 0 0 22px;
    line-height: 1.6;
}

/* Buttons */
.cm-actions {
    display: flex; gap: 10px; justify-content: center;
}
.cm-btn {
    height: 38px; padding: 0 22px;
    border-radius: var(--radius-md);
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    font-family: var(--font-arabic);
    transition: background .15s, opacity .15s;
    min-width: 90px;
}
.cm-btn--cancel {
    background: var(--bg-muted);
    color: var(--text-secondary);
    border: 1px solid var(--border-default);
}
.cm-btn--cancel:hover { background: var(--border-default); }

.cm-btn--primary {
    background: var(--sada-500);
    color: #fff;
}
.cm-btn--primary:hover { background: var(--sada-600); }

.cm-btn--danger {
    background: #ef4444;
    color: #fff;
}
.cm-btn--danger:hover { background: #dc2626; }

/* Transition */
.cm-enter-active, .cm-leave-active { transition: opacity .18s, transform .18s; }
.cm-enter-from, .cm-leave-to { opacity: 0; }
.cm-enter-from .cm-box, .cm-leave-to .cm-box { transform: scale(.95) translateY(8px); }
</style>

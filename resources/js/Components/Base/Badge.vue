<script setup lang="ts">
type Variant = 'primary' | 'sand' | 'success' | 'warning' | 'error' | 'neutral';

withDefaults(defineProps<{
    variant?: Variant;
}>(), {
    variant: 'primary',
});

const styles: Record<Variant, string> = {
    primary: 'background: color-mix(in oklab, var(--color-sada-500) 14%, transparent); color: var(--color-sada-600);',
    sand:    'background: color-mix(in oklab, var(--color-sand-500) 15%, transparent); color: #9B6B35;',
    success: 'background: #ECFDF5; color: #065F46;',
    warning: 'background: #FFFBEB; color: #92400E;',
    error:   'background: #FEF2F2; color: #991B1B;',
    neutral: 'background: var(--color-bg-page); color: var(--color-ink-muted);',
};
</script>

<template>
    <span :style="`
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
        font-family: var(--font-arabic);
        ${styles[variant]}
    `">
        <slot />
    </span>
</template>

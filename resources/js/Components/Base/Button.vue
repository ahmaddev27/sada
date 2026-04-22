<script setup lang="ts">
type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';
type Size    = 'sm' | 'md' | 'lg';

const props = withDefaults(defineProps<{
    variant?:  Variant;
    size?:     Size;
    disabled?: boolean;
    loading?:  boolean;
    type?:     'button' | 'submit' | 'reset';
}>(), {
    variant:  'primary',
    size:     'md',
    disabled: false,
    loading:  false,
    type:     'button',
});

const styles: Record<Variant, string> = {
    primary:   'background: var(--color-sada-600); color: #fff; border: none;',
    secondary: 'background: transparent; color: var(--color-ink-base); border: 1px solid var(--color-border-default);',
    ghost:     'background: transparent; color: var(--color-ink-muted); border: none;',
    danger:    'background: #DC2626; color: #fff; border: none;',
};

const sizes: Record<Size, string> = {
    sm: 'padding: 6px 12px; font-size: 12px; border-radius: 6px;',
    md: 'padding: 9px 18px; font-size: 14px; border-radius: 8px;',
    lg: 'padding: 12px 24px; font-size: 15px; border-radius: 10px;',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :style="`
            ${styles[variant]}
            ${sizes[size]}
            font-weight: 600;
            font-family: var(--font-arabic);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: ${disabled || loading ? 'not-allowed' : 'pointer'};
            opacity: ${disabled || loading ? 0.6 : 1};
            transition: opacity .15s;
        `"
        @mouseenter="(e) => { if (!disabled && !loading) (e.target as HTMLElement).style.opacity = '0.85'; }"
        @mouseleave="(e) => { if (!disabled && !loading) (e.target as HTMLElement).style.opacity = '1'; }"
    >
        <!-- Loading spinner -->
        <svg
            v-if="loading"
            width="14" height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            style="animation: spin 0.7s linear infinite;"
        >
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
        </svg>
        <slot />
    </button>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

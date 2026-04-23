<script setup lang="ts">
type Variant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'accent-soft';
type Size    = 'sm' | 'md' | 'lg' | 'xl';

withDefaults(defineProps<{
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

const variantClass: Record<Variant, string> = {
    primary:      'btn-primary',
    secondary:    'btn-secondary',
    ghost:        'btn-ghost',
    danger:       'btn-danger',
    'accent-soft':'btn-accent-soft',
};

const sizeClass: Record<Size, string> = {
    sm: 'btn-sm',
    md: '',
    lg: 'btn-lg',
    xl: 'btn-xl',
};
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="btn"
        :class="[variantClass[variant], sizeClass[size]]"
    >
        <svg
            v-if="loading"
            class="spin"
            width="14" height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
        >
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <slot />
    </button>
</template>

<style scoped>
.spin { animation: spin .8s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

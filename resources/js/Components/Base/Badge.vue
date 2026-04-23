<script setup lang="ts">
type Variant = 'primary' | 'sand' | 'success' | 'warning' | 'error' | 'neutral';

withDefaults(defineProps<{
    variant?: Variant;
}>(), {
    variant: 'primary',
});

const variantClass: Record<Variant, string> = {
    primary: 'badge-brand',
    sand:    'badge-sand',
    success: 'badge-success',
    warning: 'badge-warning',
    error:   'badge-error',
    neutral: 'badge-neutral',
};
</script>

<template>
    <span class="badge" :class="variantClass[variant]">
        <slot />
    </span>
</template>

<script setup lang="ts">
type Variant = 'success' | 'error' | 'warning' | 'info' | 'brand';

withDefaults(defineProps<{
    variant?: Variant;
    title?:   string;
}>(), {
    variant: 'info',
});

const iconPath: Record<Variant, string> = {
    success: 'M20 6L9 17l-5-5',
    error:   'M18 6L6 18M6 6l12 12',
    warning: 'M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z',
    info:    'M12 16v-4M12 8h.01',
    brand:   'M12 5v14M5 12h14',
};
</script>

<template>
    <div class="alert" :class="`alert-${variant}`">
        <div class="alert-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path :d="iconPath[variant]" />
            </svg>
        </div>
        <div class="alert-body">
            <div v-if="title" class="alert-title">{{ title }}</div>
            <div class="alert-desc"><slot /></div>
        </div>
    </div>
</template>

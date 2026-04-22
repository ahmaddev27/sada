<script setup lang="ts">
type Variant = 'success' | 'error' | 'warning' | 'info';

withDefaults(defineProps<{
    variant?: Variant;
    title?:   string;
}>(), {
    variant: 'info',
});

const config: Record<Variant, { bg: string; border: string; text: string; icon: string }> = {
    success: { bg: '#ecfdf5', border: '#A7F3D0', text: '#065F46', icon: 'M20 6 9 17 4 12' },
    error:   { bg: '#FEF2F2', border: '#FECACA', text: '#991B1B', icon: 'M18 6 6 18M6 6l12 12' },
    warning: { bg: '#FFFBEB', border: '#FDE68A', text: '#92400E', icon: 'M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z' },
    info:    { bg: '#EFF6FF', border: '#BFDBFE', text: '#1E40AF', icon: 'M12 16v-4M12 8h.01M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z' },
};
</script>

<template>
    <div
        :style="`
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid ${config[variant].border};
            background: ${config[variant].bg};
            color: ${config[variant].text};
            font-size: 13px;
            line-height: 1.6;
        `"
    >
        <svg
            width="16" height="16"
            viewBox="0 0 24 24"
            fill="none"
            :stroke="config[variant].text"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            style="flex-shrink: 0; margin-top: 1px;"
        >
            <path :d="config[variant].icon" />
        </svg>
        <div>
            <p v-if="title" style="font-weight: 600; margin: 0 0 2px;">{{ title }}</p>
            <slot />
        </div>
    </div>
</template>

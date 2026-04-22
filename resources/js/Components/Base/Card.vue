<script setup lang="ts">
withDefaults(defineProps<{
    padding?:   string;
    hoverable?: boolean;
    selected?:  boolean;
}>(), {
    padding:   '20px',
    hoverable: false,
    selected:  false,
});
</script>

<template>
    <div
        :style="`
            background: var(--color-bg-card);
            border: 1px solid ${selected ? 'var(--color-sada-500)' : 'var(--color-border-subtle)'};
            border-radius: 12px;
            padding: ${padding};
            box-shadow: ${selected ? '0 0 0 3px rgba(15,111,92,.12)' : 'none'};
            transition: box-shadow .2s, border-color .2s, transform .2s;
        `"
        :class="{ 'card-hoverable': hoverable }"
    >
        <slot />
    </div>
</template>

<style scoped>
.card-hoverable:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
    transform: translateY(-1px);
    border-color: var(--color-border-default);
    cursor: pointer;
}
</style>

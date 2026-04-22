<script setup lang="ts">
withDefaults(defineProps<{
    hoverable?: boolean;
    selected?:  boolean;
}>(), {
    hoverable: false,
    selected:  false,
});
</script>

<template>
    <div
        class="card"
        :class="{
            'card-hoverable': hoverable,
            'card--selected': selected,
        }"
    >
        <slot />
    </div>
</template>

<style scoped>
.card--selected {
    border-color: var(--sada-500);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--sada-500) 12%, transparent);
}
</style>

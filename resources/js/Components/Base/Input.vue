<script setup lang="ts">
defineProps<{
    modelValue:   string | number;
    label?:       string;
    error?:       string;
    hint?:        string;
    type?:        string;
    placeholder?: string;
    disabled?:    boolean;
    dir?:         'rtl' | 'ltr' | 'auto';
}>();

defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div class="input-group">
        <label v-if="label" class="input-label">{{ label }}</label>

        <input
            :type="type ?? 'text'"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :dir="dir ?? 'rtl'"
            class="input"
            :class="{ 'input--error': error }"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <span v-if="error" class="input-error-msg">{{ error }}</span>
        <span v-else-if="hint" class="input-hint">{{ hint }}</span>
    </div>
</template>

<style scoped>
.input--error {
    border-color: var(--error) !important;
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--error) 15%, transparent);
}
.input-error-msg {
    font-size: 12px;
    color: var(--error);
}
</style>

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
    <div style="display: flex; flex-direction: column; gap: 4px;">
        <label
            v-if="label"
            style="font-size: 13px; font-weight: 500; color: var(--color-ink-base);"
        >{{ label }}</label>

        <input
            :type="type ?? 'text'"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :dir="dir ?? 'rtl'"
            :style="`
                width: 100%;
                padding: 9px 12px;
                font-size: 14px;
                font-family: var(--font-arabic);
                border-radius: 8px;
                outline: none;
                transition: border-color .15s, box-shadow .15s;
                background: var(--color-bg-input);
                color: var(--color-ink-base);
                border: 1px solid ${error ? '#F87171' : 'var(--color-border-default)'};
                box-shadow: ${error ? '0 0 0 3px rgba(248,113,113,.15)' : 'none'};
                opacity: ${disabled ? 0.6 : 1};
                cursor: ${disabled ? 'not-allowed' : 'text'};
            `"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            @focus="(e) => { (e.target as HTMLInputElement).style.borderColor = error ? '#F87171' : 'var(--color-sada-500)'; (e.target as HTMLInputElement).style.boxShadow = error ? '0 0 0 3px rgba(248,113,113,.15)' : '0 0 0 3px rgba(15,111,92,.12)'; }"
            @blur="(e) => { (e.target as HTMLInputElement).style.borderColor = error ? '#F87171' : 'var(--color-border-default)'; (e.target as HTMLInputElement).style.boxShadow = error ? '0 0 0 3px rgba(248,113,113,.15)' : 'none'; }"
        />

        <p v-if="error" style="font-size: 12px; color: #EF4444; margin: 0;">{{ error }}</p>
        <p v-else-if="hint" style="font-size: 12px; color: var(--color-ink-muted); margin: 0;">{{ hint }}</p>
    </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{ token: string }>();

const showPassword = ref(false);
const showConfirm = ref(false);

const page = usePage();
const email = computed(() => (page.props as any).email ?? '');

const form = useForm({
    token: props.token,
    email: email.value,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div
        dir="rtl"
        class="min-h-screen flex items-center justify-center px-4"
        style="background-color: var(--color-bg-page); font-family: var(--font-arabic);"
    >
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold" style="color: var(--color-sada-600);">صدى</h1>
                <p class="mt-1 text-sm" style="color: var(--color-ink-muted);">منصة التسويق الذكي</p>
            </div>

            <!-- Card -->
            <div
                class="rounded-xl p-8 shadow-md"
                style="background-color: var(--color-bg-card); border: 1px solid var(--color-border-default);"
            >
                <h2 class="text-xl font-semibold mb-6" style="color: var(--color-ink-base);">إعادة تعيين كلمة المرور</h2>

                <form @submit.prevent="submit" novalidate>

                    <!-- Email (pre-filled, read-only) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" style="color: var(--color-ink-base);">
                            البريد الإلكتروني
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            dir="ltr"
                            class="w-full rounded-lg px-3 py-2 text-sm outline-none"
                            :class="{ 'ring-2 ring-red-400': form.errors.email }"
                            style="
                                background-color: var(--color-bg-input);
                                border: 1px solid var(--color-border-default);
                                color: var(--color-ink-base);
                            "
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- New password -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" style="color: var(--color-ink-base);">
                            كلمة المرور الجديدة
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full rounded-lg px-3 py-2 text-sm outline-none transition"
                                :class="{ 'ring-2 ring-red-400': form.errors.password }"
                                style="
                                    background-color: var(--color-bg-input);
                                    border: 1px solid var(--color-border-default);
                                    color: var(--color-ink-base);
                                    padding-left: 2.5rem;
                                "
                            />
                            <button
                                type="button"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                                style="color: var(--color-ink-muted);"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? 'إخفاء' : 'إظهار' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        <p class="mt-1 text-xs" style="color: var(--color-ink-muted);">
                            8 أحرف على الأقل، تحتوي على أحرف كبيرة وصغيرة وأرقام.
                        </p>
                    </div>

                    <!-- Confirm password -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1" style="color: var(--color-ink-base);">
                            تأكيد كلمة المرور
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full rounded-lg px-3 py-2 text-sm outline-none transition"
                                :class="{ 'ring-2 ring-red-400': form.errors.password_confirmation }"
                                style="
                                    background-color: var(--color-bg-input);
                                    border: 1px solid var(--color-border-default);
                                    color: var(--color-ink-base);
                                    padding-left: 2.5rem;
                                "
                            />
                            <button
                                type="button"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                                style="color: var(--color-ink-muted);"
                                @click="showConfirm = !showConfirm"
                            >
                                {{ showConfirm ? 'إخفاء' : 'إظهار' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        style="background-color: var(--color-sada-600);"
                    >
                        {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ كلمة المرور الجديدة' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

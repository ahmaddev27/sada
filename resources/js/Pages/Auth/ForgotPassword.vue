<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({ email: '' });

const page = usePage();
const statusMessage = computed(() => (page.props as any).flash?.status ?? null);

const submit = () => form.post('/forgot-password');
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
                <h2 class="text-xl font-semibold mb-2" style="color: var(--color-ink-base);">نسيت كلمة المرور؟</h2>
                <p class="text-sm mb-6" style="color: var(--color-ink-muted);">
                    أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
                </p>

                <!-- Success status -->
                <div
                    v-if="statusMessage"
                    class="mb-4 rounded-lg px-4 py-3 text-sm"
                    style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;"
                >
                    {{ statusMessage }}
                </div>

                <form @submit.prevent="submit" novalidate>
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1" style="color: var(--color-ink-base);">
                            البريد الإلكتروني
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            dir="ltr"
                            class="w-full rounded-lg px-3 py-2 text-sm outline-none transition"
                            :class="{ 'ring-2 ring-red-400': form.errors.email }"
                            style="
                                background-color: var(--color-bg-input);
                                border: 1px solid var(--color-border-default);
                                color: var(--color-ink-base);
                            "
                            placeholder="example@domain.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        style="background-color: var(--color-sada-600);"
                    >
                        {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال رابط الاسترداد' }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm" style="color: var(--color-ink-muted);">
                تذكرت كلمة المرور؟
                <a href="/login" class="font-medium hover:underline" style="color: var(--color-sada-600);">تسجيل الدخول</a>
            </p>
        </div>
    </div>
</template>

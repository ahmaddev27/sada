<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const page = usePage();
const statusMessage = computed(() => (page.props as any).flash?.status ?? null);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
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
                <h2 class="text-xl font-semibold mb-6" style="color: var(--color-ink-base);">تسجيل الدخول</h2>

                <!-- Status (password reset success) -->
                <div
                    v-if="statusMessage"
                    class="mb-4 rounded-lg px-4 py-3 text-sm"
                    style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;"
                >
                    {{ statusMessage }}
                </div>

                <form @submit.prevent="submit" novalidate>

                    <!-- Email -->
                    <div class="mb-4">
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

                    <!-- Password -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-medium" style="color: var(--color-ink-base);">كلمة المرور</label>
                            <a
                                href="/forgot-password"
                                class="text-xs hover:underline"
                                style="color: var(--color-sada-600);"
                            >نسيت كلمة المرور؟</a>
                        </div>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
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
                    </div>

                    <!-- Remember me -->
                    <div class="mb-6 flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded"
                            style="accent-color: var(--color-sada-500);"
                        />
                        <label for="remember" class="text-sm" style="color: var(--color-ink-muted);">تذكّرني لمدة 30 يوماً</label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        style="background-color: var(--color-sada-600);"
                    >
                        {{ form.processing ? 'جارٍ تسجيل الدخول...' : 'تسجيل الدخول' }}
                    </button>

                    <!-- Divider -->
                    <div class="my-5 flex items-center gap-3">
                        <div class="flex-1 h-px" style="background-color: var(--color-border-default);"></div>
                        <span class="text-xs" style="color: var(--color-ink-muted);">أو</span>
                        <div class="flex-1 h-px" style="background-color: var(--color-border-default);"></div>
                    </div>

                    <!-- Google OAuth -->
                    <a
                        href="/auth/google"
                        class="w-full flex items-center justify-center gap-3 rounded-lg py-2.5 text-sm font-medium transition hover:opacity-90"
                        style="
                            background-color: var(--color-bg-input);
                            border: 1px solid var(--color-border-default);
                            color: var(--color-ink-base);
                        "
                    >
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.64 9.2045C17.64 8.5664 17.5827 7.9527 17.4764 7.3636H9V10.845H13.8436C13.635 11.97 13.0009 12.9231 12.0477 13.5613V15.8195H14.9564C16.6582 14.2527 17.64 11.9454 17.64 9.2045Z" fill="#4285F4"/>
                            <path d="M9 18C11.43 18 13.4673 17.1941 14.9564 15.8195L12.0477 13.5613C11.2418 14.1013 10.2109 14.4204 9 14.4204C6.65591 14.4204 4.67182 12.8372 3.96409 10.71H0.957275V13.0418C2.43818 15.9831 5.48182 18 9 18Z" fill="#34A853"/>
                            <path d="M3.96409 10.71C3.78409 10.17 3.68182 9.5931 3.68182 9C3.68182 8.4068 3.78409 7.8299 3.96409 7.2899V4.9581H0.957275C0.347727 6.1731 0 7.5477 0 9C0 10.4522 0.347727 11.8268 0.957275 13.0418L3.96409 10.71Z" fill="#FBBC05"/>
                            <path d="M9 3.5795C10.3214 3.5795 11.5077 4.0336 12.4405 4.9254L15.0218 2.344C13.4632 0.8918 11.4259 0 9 0C5.48182 0 2.43818 2.0168 0.957275 4.9581L3.96409 7.2899C4.67182 5.1627 6.65591 3.5795 9 3.5795Z" fill="#EA4335"/>
                        </svg>
                        الدخول عبر Google
                    </a>
                </form>
            </div>

            <!-- Footer link -->
            <p class="text-center mt-6 text-sm" style="color: var(--color-ink-muted);">
                ليس لديك حساب؟
                <a href="/register" class="font-medium hover:underline" style="color: var(--color-sada-600);">أنشئ حساباً</a>
            </p>
        </div>
    </div>
</template>

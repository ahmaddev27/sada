<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({});

const page = usePage();
const statusMessage = computed(() => (page.props as any).flash?.success ?? null);

const resend = () => form.post('/email/verification-notification');
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
                class="rounded-xl p-8 shadow-md text-center"
                style="background-color: var(--color-bg-card); border: 1px solid var(--color-border-default);"
            >
                <!-- Icon -->
                <div
                    class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full"
                    style="background-color: #ecfdf5;"
                >
                    <svg class="h-8 w-8" style="color: var(--color-sada-600);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>

                <h2 class="text-xl font-semibold mb-3" style="color: var(--color-ink-base);">تحقق من بريدك الإلكتروني</h2>
                <p class="text-sm mb-6" style="color: var(--color-ink-muted);">
                    أرسلنا لك رابط التحقق. يرجى التحقق من صندوق الوارد والضغط على الرابط لتفعيل حسابك.
                </p>

                <!-- Resend success -->
                <div
                    v-if="statusMessage"
                    class="mb-4 rounded-lg px-4 py-3 text-sm"
                    style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;"
                >
                    {{ statusMessage }}
                </div>

                <button
                    type="button"
                    :disabled="form.processing"
                    class="w-full rounded-lg py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                    style="background-color: var(--color-sada-600);"
                    @click="resend"
                >
                    {{ form.processing ? 'جارٍ الإرسال...' : 'إعادة إرسال رابط التحقق' }}
                </button>

                <form method="POST" action="/logout" class="mt-4">
                    <input type="hidden" name="_token" :value="(page.props as any).csrf_token" />
                    <button
                        type="submit"
                        class="text-sm hover:underline"
                        style="color: var(--color-ink-muted);"
                    >
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

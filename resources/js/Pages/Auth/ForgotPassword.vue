<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({ email: '' });

const page = usePage();
const statusMessage = computed(() => (page.props as any).flash?.status ?? null);

const submit = () => form.post('/forgot-password');
</script>

<template>
    <div dir="rtl" class="page-wrap">
        <div class="container">

            <!-- Logo -->
            <div class="logo-block">
                <img src="/images/logo/sada-arch-mark.svg" class="logo-mark" alt="صدى" />
                <h1 class="logo-title">صدى</h1>
                <p class="logo-sub">منصة التسويق الرقمي بالذكاء الاصطناعي</p>
            </div>

            <!-- Card -->
            <div class="card">
                <h2 class="card-heading">نسيت كلمة المرور؟</h2>
                <p class="card-desc">أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين.</p>

                <!-- Success status -->
                <div v-if="statusMessage" class="status-msg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    {{ statusMessage }}
                </div>

                <form @submit.prevent="submit" novalidate>

                    <!-- Email -->
                    <div class="field">
                        <label for="fp-email" class="field-label">البريد الإلكتروني</label>
                        <input
                            id="fp-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            dir="ltr"
                            placeholder="example@domain.com"
                            :class="['field-input', { 'field-input--error': form.errors.email }]"
                            style="text-align: right;"
                        />
                        <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary"
                        style="color: #fff;"
                    >
                        {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال رابط الاسترداد' }}
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <p class="footer-text">
                تذكّرت كلمة المرور؟
                <a href="/login" class="footer-link">تسجيل الدخول</a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.page-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background-color: var(--bg-page);
    font-family: var(--font-arabic);
}

.container {
    width: 100%;
    max-width: 440px;
}

/* Logo */
.logo-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 28px;
    gap: 6px;
}

.logo-mark {
    width: 56px;
    height: 56px;
    object-fit: contain;
    margin-bottom: 4px;
}

.logo-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--ink-900);
    margin: 0;
    line-height: 1.2;
}

.logo-sub {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.5;
}

/* Card */
.card {
    background-color: var(--bg-surface);
    border: 1px solid var(--ink-100);
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07);
    padding: 36px;
}

.card-heading {
    font-size: 18px;
    font-weight: 700;
    color: var(--ink-900);
    margin: 0 0 8px;
    line-height: 1.3;
}

.card-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0 0 24px;
    line-height: 1.6;
}

/* Status */
.status-msg {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background-color: var(--success-bg);
    color: var(--success);
    border: 1px solid color-mix(in oklab, var(--success) 30%, transparent);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Fields */
.field {
    margin-bottom: 20px;
}

.field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-700);
    margin-bottom: 6px;
    line-height: 1.4;
}

.field-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--ink-200);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-family: var(--font-arabic);
    background-color: var(--bg-surface);
    color: var(--ink-900);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    box-sizing: border-box;
    line-height: 1.5;
}
.field-input:focus {
    border-color: var(--sada-500);
    box-shadow: var(--shadow-focus);
}
.field-input--error {
    border-color: var(--error);
}
.field-input--error:focus {
    box-shadow: 0 0 0 3px rgba(181, 50, 47, 0.18);
}

.field-error {
    margin: 5px 0 0;
    font-size: 12px;
    color: var(--error);
    line-height: 1.4;
}

/* Button */
.btn-primary {
    width: 100%;
    height: 48px;
    background-color: var(--sada-500);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-weight: 700;
    font-family: var(--font-arabic);
    cursor: pointer;
    transition: background-color 0.15s;
    line-height: 1;
}
.btn-primary:hover:not(:disabled) { background-color: var(--sada-600); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* Footer */
.footer-text {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
}

.footer-link {
    color: var(--sada-600);
    font-weight: 600;
    text-decoration: none;
}
.footer-link:hover { text-decoration: underline; }
</style>

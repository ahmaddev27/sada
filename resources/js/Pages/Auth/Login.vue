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
                <h2 class="card-heading">تسجيل الدخول</h2>

                <!-- Status message -->
                <div v-if="statusMessage" class="status-msg">
                    {{ statusMessage }}
                </div>

                <form @submit.prevent="submit" novalidate>

                    <!-- Email -->
                    <div class="field">
                        <label for="login-email" class="field-label">البريد الإلكتروني</label>
                        <input
                            id="login-email"
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

                    <!-- Password -->
                    <div class="field">
                        <div class="field-label-row">
                            <label for="login-password" class="field-label">كلمة المرور</label>
                            <a href="/forgot-password" class="forgot-link">نسيت كلمة المرور؟</a>
                        </div>
                        <div class="input-wrap">
                            <input
                                id="login-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="كلمة المرور"
                                :class="['field-input', { 'field-input--error': form.errors.password }]"
                                style="padding-left: 44px;"
                            />
                            <button
                                type="button"
                                class="eye-btn"
                                :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                                @click="showPassword = !showPassword"
                            >
                                <!-- Eye open -->
                                <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <!-- Eye off -->
                                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember me -->
                    <div class="remember-row">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="remember-check"
                        />
                        <label for="remember" class="remember-label">تذكّرني لمدة 30 يوماً</label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary"
                        style="color: #fff;"
                    >
                        {{ form.processing ? 'جارٍ تسجيل الدخول...' : 'تسجيل الدخول' }}
                    </button>

                    <!-- Divider -->
                    <div class="divider">
                        <span class="divider-line" />
                        <span class="divider-text">أو</span>
                        <span class="divider-line" />
                    </div>

                    <!-- Google OAuth -->
                    <a href="/auth/google" class="btn-google">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        الدخول عبر Google
                    </a>
                </form>
            </div>

            <!-- Footer -->
            <p class="footer-text">
                ليس لديك حساب؟
                <a href="/register" class="footer-link">أنشئ حساباً</a>
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
    margin: 0 0 24px;
    line-height: 1.3;
}

/* Status */
.status-msg {
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
    margin-bottom: 18px;
}

.field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-700);
    margin-bottom: 6px;
    line-height: 1.4;
}

.field-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.field-label-row .field-label {
    margin-bottom: 0;
}

.forgot-link {
    font-size: 12px;
    color: var(--sada-600);
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.15s;
}
.forgot-link:hover { opacity: 0.75; text-decoration: underline; }

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

/* Password input wrapper */
.input-wrap {
    position: relative;
}

.input-wrap .field-input {
    padding-left: 44px;
}

.eye-btn {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    padding: 4px;
    cursor: pointer;
    color: var(--text-muted);
    display: grid;
    place-items: center;
    border-radius: 4px;
    transition: color 0.15s;
}
.eye-btn:hover { color: var(--ink-700); }

/* Remember me */
.remember-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
}

.remember-check {
    width: 16px;
    height: 16px;
    accent-color: var(--sada-500);
    cursor: pointer;
    flex-shrink: 0;
}

.remember-label {
    font-size: 13px;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1.4;
}

/* Buttons */
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

/* Divider */
.divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0;
}

.divider-line {
    flex: 1;
    height: 1px;
    background-color: var(--border-default);
    display: block;
}

.divider-text {
    font-size: 12px;
    color: var(--text-muted);
    flex-shrink: 0;
}

/* Google button */
.btn-google {
    width: 100%;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background-color: var(--bg-surface);
    border: 1px solid var(--ink-200);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    font-family: var(--font-arabic);
    color: var(--ink-700);
    text-decoration: none;
    transition: background-color 0.15s, border-color 0.15s;
    box-sizing: border-box;
}
.btn-google:hover {
    background-color: var(--bg-muted);
    border-color: var(--border-strong);
}

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

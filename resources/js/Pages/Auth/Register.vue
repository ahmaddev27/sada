<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
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
            <div class="flex flex-col items-center mb-8">
                <div
                    class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                    style="background-color: var(--color-sada-500);"
                >
                    <span class="text-white text-2xl font-bold">ص</span>
                </div>
                <h1 class="text-2xl font-bold" style="color: var(--color-ink-900);">صدى</h1>
                <p class="text-sm mt-1" style="color: var(--color-ink-500);">منصة التسويق الرقمي بالذكاء الاصطناعي</p>
            </div>

            <!-- Card -->
            <div
                class="rounded-2xl p-8"
                style="
                    background-color: var(--color-bg-surface);
                    box-shadow: var(--shadow-md);
                    border: 1px solid var(--color-ink-100);
                "
            >
                <h2 class="text-lg font-bold mb-6" style="color: var(--color-ink-900);">
                    إنشاء حساب جديد
                </h2>

                <form @submit.prevent="submit" novalidate class="space-y-5">

                    <!-- Name -->
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium mb-1.5"
                            style="color: var(--color-ink-700);"
                        >
                            الاسم الكامل
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            placeholder="مثال: أحمد الغامدي"
                            :class="[
                                'w-full px-4 py-2.5 rounded-lg text-sm border transition-colors',
                                form.errors.name ? 'border-[var(--color-error)]' : 'border-[var(--color-ink-200)]',
                            ]"
                            style="
                                background-color: var(--color-bg-surface);
                                color: var(--color-ink-900);
                                outline: none;
                            "
                            @focus="($event.target as HTMLElement).style.boxShadow = 'var(--shadow-focus)'"
                            @blur="($event.target as HTMLElement).style.boxShadow = 'none'"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs" style="color: var(--color-error);">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium mb-1.5"
                            style="color: var(--color-ink-700);"
                        >
                            البريد الإلكتروني
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="example@company.com"
                            dir="ltr"
                            :class="[
                                'w-full px-4 py-2.5 rounded-lg text-sm border transition-colors',
                                form.errors.email ? 'border-[var(--color-error)]' : 'border-[var(--color-ink-200)]',
                            ]"
                            style="
                                background-color: var(--color-bg-surface);
                                color: var(--color-ink-900);
                                text-align: right;
                                outline: none;
                            "
                            @focus="($event.target as HTMLElement).style.boxShadow = 'var(--shadow-focus)'"
                            @blur="($event.target as HTMLElement).style.boxShadow = 'none'"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs" style="color: var(--color-error);">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium mb-1.5"
                            style="color: var(--color-ink-700);"
                        >
                            كلمة المرور
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="8 أحرف على الأقل"
                                dir="ltr"
                                :class="[
                                    'w-full px-4 py-2.5 rounded-lg text-sm border transition-colors',
                                    form.errors.password ? 'border-[var(--color-error)]' : 'border-[var(--color-ink-200)]',
                                ]"
                                style="
                                    background-color: var(--color-bg-surface);
                                    color: var(--color-ink-900);
                                    padding-left: 40px;
                                    outline: none;
                                "
                                @focus="($event.target as HTMLElement).style.boxShadow = 'var(--shadow-focus)'"
                                @blur="($event.target as HTMLElement).style.boxShadow = 'none'"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                                style="color: var(--color-ink-400);"
                                :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                            >
                                {{ showPassword ? '🙈' : '👁' }}
                            </button>
                        </div>
                        <p class="mt-1 text-xs" style="color: var(--color-ink-400);">
                            يجب أن تحتوي على حرف كبير وحرف صغير ورقم
                        </p>
                        <p v-if="form.errors.password" class="mt-1 text-xs" style="color: var(--color-error);">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Password confirmation -->
                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium mb-1.5"
                            style="color: var(--color-ink-700);"
                        >
                            تأكيد كلمة المرور
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="أعد كتابة كلمة المرور"
                                dir="ltr"
                                class="w-full px-4 py-2.5 rounded-lg text-sm border border-[var(--color-ink-200)] transition-colors"
                                style="
                                    background-color: var(--color-bg-surface);
                                    color: var(--color-ink-900);
                                    padding-left: 40px;
                                    outline: none;
                                "
                                @focus="($event.target as HTMLElement).style.boxShadow = 'var(--shadow-focus)'"
                                @blur="($event.target as HTMLElement).style.boxShadow = 'none'"
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs"
                                style="color: var(--color-ink-400);"
                                :aria-label="showConfirm ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                            >
                                {{ showConfirm ? '🙈' : '👁' }}
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 rounded-lg text-sm font-semibold transition-all mt-2"
                        style="
                            background-color: var(--color-sada-500);
                            color: white;
                        "
                        :style="form.processing ? 'opacity: 0.7; cursor: not-allowed;' : 'cursor: pointer;'"
                        @mouseenter="($event.target as HTMLElement).style.backgroundColor = 'var(--color-sada-600)'"
                        @mouseleave="($event.target as HTMLElement).style.backgroundColor = form.processing ? 'var(--color-sada-500)' : 'var(--color-sada-500)'"
                    >
                        <span v-if="form.processing">جارٍ إنشاء الحساب...</span>
                        <span v-else>إنشاء الحساب</span>
                    </button>

                </form>

                <!-- Login link -->
                <p class="text-center text-sm mt-6" style="color: var(--color-ink-500);">
                    لديك حساب بالفعل؟
                    <a
                        href="/login"
                        class="font-medium"
                        style="color: var(--color-sada-600);"
                    >
                        تسجيل الدخول
                    </a>
                </p>
            </div>

            <!-- Terms -->
            <p class="text-center text-xs mt-4" style="color: var(--color-ink-400);">
                بإنشاء حساب، فأنت توافق على
                <a href="/terms" style="color: var(--color-sada-600);">شروط الاستخدام</a>
                و
                <a href="/privacy" style="color: var(--color-sada-600);">سياسة الخصوصية</a>
            </p>
        </div>
    </div>
</template>

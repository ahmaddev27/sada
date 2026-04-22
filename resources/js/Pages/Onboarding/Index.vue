<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    dialects:      { value: string; label: string }[];
    businessTypes: string[];
}>();

const step = ref<1 | 2>(1);

// Step 1 — workspace
const workspaceForm = useForm({
    name:             '',
    business_type:    '',
    countries:        ['sa'] as string[],
    default_dialect:  'sa',
});

const countries = [
    { code: 'sa', label: 'السعودية'  },
    { code: 'ae', label: 'الإمارات'  },
    { code: 'kw', label: 'الكويت'    },
    { code: 'qa', label: 'قطر'       },
    { code: 'bh', label: 'البحرين'   },
    { code: 'om', label: 'عُمان'     },
];

function toggleCountry(code: string) {
    const idx = workspaceForm.countries.indexOf(code);
    if (idx === -1) {
        workspaceForm.countries.push(code);
    } else if (workspaceForm.countries.length > 1) {
        workspaceForm.countries.splice(idx, 1);
    }
}

function submitWorkspace() {
    workspaceForm.post('/onboarding/workspace');
}

const progressPct = computed(() => (step.value === 1 ? 50 : 100));
</script>

<template>
    <Head title="إعداد حسابك — صدى" />

    <div dir="rtl" class="page-wrap">

        <!-- Header -->
        <div class="onb-header">
            <Link href="/" class="onb-logo">
                <div class="logo-mark">ص</div>
                <span>صدى</span>
            </Link>
            <div class="step-indicator">الخطوة {{ step }} من ٢</div>
        </div>

        <!-- Progress bar -->
        <div class="progress-bar">
            <div class="progress-fill" :style="{ width: progressPct + '%' }"></div>
        </div>

        <!-- ── Step 1: Create workspace ──────────────────────────────── -->
        <div v-if="step === 1" class="onb-container">
            <div class="onb-card">
                <div class="step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <h1 class="onb-title">أنشئ مساحة عملك</h1>
                <p class="onb-sub">مساحة العمل هي المكان الذي تدير منه محتواك وحملاتك.</p>

                <form @submit.prevent="submitWorkspace" novalidate class="onb-form">

                    <!-- Workspace name -->
                    <div class="field">
                        <label class="field-label">اسم مساحة العمل <span class="required">*</span></label>
                        <input
                            v-model="workspaceForm.name"
                            type="text"
                            placeholder="مثال: متجر أنيق، كافيه الرواق..."
                            :class="['field-input', { 'field-input--error': workspaceForm.errors.name }]"
                            autofocus
                        />
                        <p v-if="workspaceForm.errors.name" class="field-error">{{ workspaceForm.errors.name }}</p>
                    </div>

                    <!-- Business type -->
                    <div class="field">
                        <label class="field-label">نوع النشاط التجاري</label>
                        <select v-model="workspaceForm.business_type" class="field-input">
                            <option value="">اختر نوع النشاط (اختياري)</option>
                            <option v-for="t in businessTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>

                    <!-- Countries -->
                    <div class="field">
                        <label class="field-label">الدول المستهدفة</label>
                        <div class="country-grid">
                            <button
                                v-for="c in countries"
                                :key="c.code"
                                type="button"
                                :class="['country-btn', { 'country-btn--active': workspaceForm.countries.includes(c.code) }]"
                                @click="toggleCountry(c.code)"
                            >
                                <span class="country-check" v-if="workspaceForm.countries.includes(c.code)">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                {{ c.label }}
                            </button>
                        </div>
                        <p v-if="workspaceForm.errors.countries" class="field-error">{{ workspaceForm.errors.countries }}</p>
                    </div>

                    <!-- Default dialect -->
                    <div class="field">
                        <label class="field-label">اللهجة الافتراضية للمحتوى</label>
                        <div class="dialect-grid">
                            <button
                                v-for="d in dialects"
                                :key="d.value"
                                type="button"
                                :class="['dialect-btn', { 'dialect-btn--active': workspaceForm.default_dialect === d.value }]"
                                @click="workspaceForm.default_dialect = d.value"
                            >{{ d.label }}</button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn-submit"
                        :disabled="workspaceForm.processing || !workspaceForm.name.trim()"
                    >
                        <span v-if="workspaceForm.processing">جارٍ الإنشاء...</span>
                        <span v-else>متابعة للوحة التحكم</span>
                        <svg v-if="!workspaceForm.processing" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    </button>
                </form>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ── Layout ────────────────────────────────────────────────────────── */
.page-wrap {
    min-height: 100vh;
    background: var(--bg-page);
    color: var(--text-primary);
    font-family: var(--font-arabic);
}

/* ── Header ────────────────────────────────────────────────────────── */
.onb-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 40px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--bg-surface);
}
.onb-logo {
    display: flex; align-items: center; gap: 10px;
    font-weight: 800; font-size: 18px;
    color: var(--text-primary); text-decoration: none;
}
.logo-mark {
    width: 32px; height: 32px; border-radius: 9px;
    background: var(--sada-500); color: #fff;
    font-weight: 800; font-size: 16px;
    display: grid; place-items: center;
}
.step-indicator { font-size: 13px; color: var(--text-muted); font-weight: 500; }

/* ── Progress ──────────────────────────────────────────────────────── */
.progress-bar {
    height: 3px; background: var(--border-subtle);
}
.progress-fill {
    height: 100%;
    background: var(--sada-500);
    transition: width .4s ease;
}

/* ── Container ─────────────────────────────────────────────────────── */
.onb-container {
    max-width: 520px; margin: 48px auto;
    padding: 0 20px;
}
.onb-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 16px; padding: 40px;
}
.step-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
    display: grid; place-items: center;
    margin-bottom: 20px;
}
.onb-title {
    margin: 0 0 8px; font-size: 24px; font-weight: 800;
    letter-spacing: -0.01em; color: var(--text-primary);
}
.onb-sub { margin: 0 0 28px; font-size: 14px; color: var(--text-muted); line-height: 1.6; }

/* ── Form ──────────────────────────────────────────────────────────── */
.onb-form { display: flex; flex-direction: column; gap: 20px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.required { color: var(--sada-500); }
.field-input {
    padding: 10px 12px; border-radius: 8px;
    border: 1px solid var(--border-default);
    background: var(--bg-page); color: var(--text-primary);
    font-family: var(--font-arabic); font-size: 14px;
    outline: none; transition: border-color .15s;
}
.field-input:focus { border-color: var(--sada-500); }
.field-input--error { border-color: #EF4444; }
.field-error { font-size: 12px; color: #EF4444; margin: 0; }

/* ── Country chips ─────────────────────────────────────────────────── */
.country-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.country-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
    border: 1px solid var(--border-default);
    background: var(--bg-page); color: var(--text-muted);
    cursor: pointer; transition: all .15s;
    font-family: var(--font-arabic);
}
.country-btn:hover { border-color: var(--sada-500); color: var(--text-primary); }
.country-btn--active {
    border-color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-500); font-weight: 600;
}
.country-check {
    width: 16px; height: 16px; border-radius: 50%;
    background: var(--sada-500); color: #fff;
    display: grid; place-items: center; flex-shrink: 0;
}

/* ── Dialect chips ─────────────────────────────────────────────────── */
.dialect-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.dialect-btn {
    padding: 7px 14px; border-radius: 8px; font-size: 13px;
    border: 1px solid var(--border-default);
    background: var(--bg-page); color: var(--text-muted);
    cursor: pointer; transition: all .15s;
    font-family: var(--font-arabic); font-weight: 500;
}
.dialect-btn:hover { border-color: var(--sada-500); color: var(--text-primary); }
.dialect-btn--active {
    border-color: var(--sand-500);
    background: color-mix(in oklab, var(--sand-500) 12%, transparent);
    color: var(--sand-600, #9B6B35); font-weight: 600;
}

/* ── Submit ────────────────────────────────────────────────────────── */
.btn-submit {
    width: 100%; padding: 13px 20px; margin-top: 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--sada-600); color: #fff;
    border: none; border-radius: 10px;
    font-size: 15px; font-weight: 700;
    font-family: var(--font-arabic); cursor: pointer;
    transition: opacity .15s;
}
.btn-submit:hover:not(:disabled) { opacity: 0.9; }
.btn-submit:disabled { opacity: 0.55; cursor: not-allowed; }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 560px) {
    .onb-header { padding: 16px 20px; }
    .onb-card { padding: 28px 20px; }
    .onb-title { font-size: 20px; }
}
</style>

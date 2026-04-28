<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    step:          1 | 2 | 3;
    dialects:      { value: string; label: string }[];
    businessTypes: string[];
    personaNiches: string[];
}>();

const progressPct = computed(() => {
    if (props.step === 1) return 33;
    if (props.step === 2) return 66;
    return 100;
});

// ── Step 1: workspace ────────────────────────────────────────────────────
const workspaceForm = useForm({
    name:             '',
    entity_type:      'business' as 'business' | 'persona',
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

// ── Step 2: social connect ───────────────────────────────────────────────
function connectMeta() {
    window.location.href = '/social/connect/meta';
}

function skipSocial() {
    router.post('/onboarding/skip');
}

function completeOnboarding() {
    router.post('/onboarding/complete');
}

const platforms = [
    {
        id:    'instagram',
        name:  'Instagram',
        desc:  'انشر صوراً وريلز وقصص على حساباتك التجارية',
        color: '#E1306C',
        bg:    'color-mix(in oklab, #E1306C 12%, transparent)',
    },
    {
        id:    'facebook',
        name:  'Facebook',
        desc:  'جدول منشوراتك وإعلاناتك على صفحاتك',
        color: '#1877F2',
        bg:    'color-mix(in oklab, #1877F2 12%, transparent)',
    },
];
</script>

<template>
    <Head title="إعداد حسابك — صدى" />

    <div dir="rtl" class="page-wrap">

        <!-- Header -->
        <div class="onb-header">
            <Link href="/" class="onb-logo">
                <img src="/images/logo/sada-arch-mark.svg" class="onb-logo-img" alt="صدى" />
                <span>صدى</span>
            </Link>
            <div class="step-indicator">الخطوة {{ step }} من ٣</div>
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

                    <!-- Entity type — persona vs business -->
                    <div class="field">
                        <label class="field-label">نوع الحساب <span class="required">*</span></label>
                        <div class="entity-grid">
                            <button
                                type="button"
                                :class="['entity-card', { 'entity-card--active': workspaceForm.entity_type === 'business' }]"
                                @click="workspaceForm.entity_type = 'business'; workspaceForm.business_type = ''"
                            >
                                <span class="entity-icon">🏪</span>
                                <span class="entity-name">نشاط تجاري</span>
                                <span class="entity-desc">متجر، مطعم، خدمات...</span>
                            </button>
                            <button
                                type="button"
                                :class="['entity-card', { 'entity-card--active': workspaceForm.entity_type === 'persona' }]"
                                @click="workspaceForm.entity_type = 'persona'; workspaceForm.business_type = ''"
                            >
                                <span class="entity-icon">👤</span>
                                <span class="entity-name">شخصية / مؤثر</span>
                                <span class="entity-desc">حساب شخصي، بيرسونة...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Business type (only for business) -->
                    <div v-if="workspaceForm.entity_type === 'business'" class="field">
                        <label class="field-label">نوع النشاط</label>
                        <select v-model="workspaceForm.business_type" class="field-input">
                            <option value="">اختر نوع النشاط (اختياري)</option>
                            <option v-for="t in businessTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>

                    <!-- Persona niche (only for persona) -->
                    <div v-if="workspaceForm.entity_type === 'persona'" class="field">
                        <label class="field-label">مجال التأثير</label>
                        <select v-model="workspaceForm.business_type" class="field-input">
                            <option value="">اختر مجالك (اختياري)</option>
                            <option v-for="n in personaNiches" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <!-- Countries -->
                    <div class="field">
                        <label class="field-label">الدول المستهدفة</label>
                        <div class="chip-grid">
                            <button
                                v-for="c in countries"
                                :key="c.code"
                                type="button"
                                :class="['chip-btn', { 'chip-btn--active': workspaceForm.countries.includes(c.code) }]"
                                @click="toggleCountry(c.code)"
                            >
                                <span v-if="workspaceForm.countries.includes(c.code)" class="chip-check">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                {{ c.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Default dialect -->
                    <div class="field">
                        <label class="field-label">اللهجة الافتراضية للمحتوى</label>
                        <div class="chip-grid">
                            <button
                                v-for="d in dialects"
                                :key="d.value"
                                type="button"
                                :class="['chip-btn', 'chip-btn--dialect', { 'chip-btn--active-sand': workspaceForm.default_dialect === d.value }]"
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
                        <span v-else>التالي — ربط حساباتك</span>
                        <svg v-if="!workspaceForm.processing" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- ── Step 2: Connect social accounts ──────────────────────── -->
        <div v-else-if="step === 2" class="onb-container">
            <div class="onb-card">
                <div class="step-icon step-icon--social">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h1 class="onb-title">اربط حساباتك الاجتماعية</h1>
                <p class="onb-sub">اربط حساباتك على Meta لتبدأ بجدولة ونشر المحتوى مباشرة من صدى.</p>

                <!-- Platform cards -->
                <div class="platform-list">
                    <div
                        v-for="p in platforms"
                        :key="p.id"
                        class="platform-card"
                    >
                        <div class="platform-icon" :style="{ background: p.bg, color: p.color }">
                            <!-- Instagram -->
                            <svg v-if="p.id === 'instagram'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                            <!-- Facebook -->
                            <svg v-else-if="p.id === 'facebook'" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div class="platform-info">
                            <div class="platform-name">{{ p.name }}</div>
                            <div class="platform-desc">{{ p.desc }}</div>
                        </div>
                    </div>
                </div>

                <!-- Notice -->
                <div class="meta-notice">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    ستنتقل إلى صفحة تسجيل الدخول بـ Meta وتعود تلقائياً بعد الربط.
                </div>

                <!-- Actions -->
                <button class="btn-submit" @click="connectMeta">
                    <svg width="18" height="18" viewBox="0 0 50 50" fill="currentColor" style="opacity:.9"><path d="M25 2C12.318 2 2 12.318 2 25c0 11.15 7.87 20.44 18.36 22.63V30.89h-5.5v-5.89h5.5v-4.49c0-5.44 3.24-8.44 8.19-8.44 2.37 0 4.85.42 4.85.42v5.34h-2.73c-2.69 0-3.53 1.67-3.53 3.38V25h6.01l-.96 5.89h-5.05v16.74C40.13 45.44 48 36.15 48 25 48 12.318 37.682 2 25 2z"/></svg>
                    ربط حساب Meta (Instagram + Facebook)
                </button>

                <button class="btn-skip" @click="skipSocial">
                    تخطّ الآن، سأربط لاحقاً
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ── Step 3: Ready to go ──────────────────────────────────── -->
        <div v-else-if="step === 3" class="onb-container">
            <div class="onb-card onb-card--celebration">
                <div class="celebration-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                </div>
                <h1 class="onb-title">أنت جاهز للانطلاق!</h1>
                <p class="onb-sub">مساحة عملك جاهزة. ابدأ الآن بتوليد محتوى تسويقي باللهجة الخليجية وجدولته مباشرة.</p>

                <!-- Feature highlights -->
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-dot feature-dot--sada"></div>
                        <div>
                            <div class="feature-title">توليد المحتوى بالذكاء الاصطناعي</div>
                            <div class="feature-desc">منشورات بـ 7 لهجات خليجية في ثوانٍ</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-dot feature-dot--sand"></div>
                        <div>
                            <div class="feature-title">جدولة ونشر مباشر</div>
                            <div class="feature-desc">على Instagram وFacebook دون تبديل التطبيقات</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-dot feature-dot--sada"></div>
                        <div>
                            <div class="feature-title">حملات المناسبات الخليجية</div>
                            <div class="feature-desc">26 مناسبة جاهزة — رمضان، اليوم الوطني، وأكثر</div>
                        </div>
                    </div>
                </div>

                <button class="btn-submit" @click="completeOnboarding">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    ابدأ توليد المحتوى
                </button>

                <a href="/social/accounts" class="btn-skip">
                    ربط حسابات إضافية (TikTok، Snapchat، X)
                </a>
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
.onb-logo-img { width: 32px; height: 32px; object-fit: contain; }
.step-indicator { font-size: 13px; color: var(--text-muted); font-weight: 500; }

/* ── Progress ──────────────────────────────────────────────────────── */
.progress-bar { height: 3px; background: var(--border-subtle); }
.progress-fill { height: 100%; background: var(--sada-500); transition: width .5s ease; }

/* ── Container ─────────────────────────────────────────────────────── */
.onb-container { max-width: 520px; margin: 48px auto; padding: 0 20px; }
.onb-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 16px; padding: 40px;
}
.step-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
    display: grid; place-items: center; margin-bottom: 20px;
}
.step-icon--social {
    background: color-mix(in oklab, var(--sand-500) 12%, transparent);
    color: var(--sand-500);
}
.onb-title { margin: 0 0 8px; font-size: 24px; font-weight: 800; letter-spacing: -0.01em; }
.onb-sub   { margin: 0 0 28px; font-size: 14px; color: var(--text-muted); line-height: 1.6; }

/* ── Form ──────────────────────────────────────────────────────────── */
.onb-form { display: flex; flex-direction: column; gap: 20px; }
.field    { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 13px; font-weight: 600; }
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

/* ── Entity type selector ──────────────────────────────────────────── */
.entity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.entity-card {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 16px 12px; border-radius: 10px;
    border: 2px solid var(--border-default);
    background: var(--bg-page); color: var(--text-primary);
    cursor: pointer; transition: all .15s; font-family: var(--font-arabic);
    text-align: center;
}
.entity-card:hover { border-color: var(--sada-500); }
.entity-card--active {
    border-color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
}
.entity-icon { font-size: 24px; line-height: 1; }
.entity-name { font-size: 14px; font-weight: 700; }
.entity-desc { font-size: 11px; color: var(--text-muted); }

/* ── Chips (countries + dialects) ──────────────────────────────────── */
.chip-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.chip-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
    border: 1px solid var(--border-default);
    background: var(--bg-page); color: var(--text-muted);
    cursor: pointer; transition: all .15s; font-family: var(--font-arabic);
}
.chip-btn:hover { border-color: var(--sada-500); color: var(--text-primary); }
.chip-btn--active {
    border-color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-500); font-weight: 600;
}
.chip-btn--active-sand {
    border-color: var(--sand-500);
    background: color-mix(in oklab, var(--sand-500) 12%, transparent);
    color: var(--sand-600, #9B6B35); font-weight: 600;
}
.chip-check {
    width: 15px; height: 15px; border-radius: 50%;
    background: var(--sada-500); color: #fff;
    display: grid; place-items: center; flex-shrink: 0;
}

/* ── Submit / skip ─────────────────────────────────────────────────── */
.btn-submit {
    width: 100%; padding: 13px 20px; margin-top: 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--sada-600); color: #fff;
    border: none; border-radius: 10px;
    font-size: 15px; font-weight: 700;
    font-family: var(--font-arabic); cursor: pointer; transition: opacity .15s;
}
.btn-submit:hover:not(:disabled) { opacity: 0.9; }
.btn-submit:disabled { opacity: 0.55; cursor: not-allowed; }

.btn-skip {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; margin-top: 12px; padding: 10px;
    font-size: 13px; color: var(--text-muted); font-weight: 500;
    background: none; border: none; cursor: pointer;
    font-family: var(--font-arabic); transition: color .15s;
}
.btn-skip:hover { color: var(--text-primary); }

/* ── Step 2: platform cards ────────────────────────────────────────── */
.platform-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.platform-card {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    background: var(--bg-page); border: 1px solid var(--border-subtle);
    border-radius: 10px;
}
.platform-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: grid; place-items: center; flex-shrink: 0;
}
.platform-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.platform-desc { font-size: 12px; color: var(--text-muted); margin-top: 2px; line-height: 1.5; }

.meta-notice {
    display: flex; align-items: flex-start; gap: 8px;
    padding: 10px 12px; margin-bottom: 20px;
    background: color-mix(in oklab, var(--info) 10%, transparent);
    color: var(--text-muted); border-radius: 8px;
    font-size: 12px; line-height: 1.5;
}

/* ── Step 3: celebration ───────────────────────────────────────────── */
.onb-card--celebration {
    text-align: center;
}
.onb-card--celebration .onb-title,
.onb-card--celebration .onb-sub {
    text-align: center;
}
.celebration-icon {
    width: 72px; height: 72px; border-radius: 20px;
    background: color-mix(in oklab, var(--sand-500) 14%, transparent);
    color: var(--sand-500);
    display: grid; place-items: center; margin: 0 auto 20px;
}
.feature-list {
    display: flex; flex-direction: column; gap: 14px;
    margin: 0 0 28px; text-align: right;
}
.feature-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 14px;
    background: var(--bg-page); border: 1px solid var(--border-subtle);
    border-radius: 10px;
}
.feature-dot {
    width: 8px; height: 8px; border-radius: 50%;
    margin-top: 5px; flex-shrink: 0;
}
.feature-dot--sada { background: var(--sada-500); }
.feature-dot--sand { background: var(--sand-500); }
.feature-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.feature-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 560px) {
    .onb-header { padding: 16px 20px; }
    .onb-card { padding: 28px 20px; }
    .onb-title { font-size: 20px; }
}
</style>

<script setup lang="ts">
// ADS-01 → ADS-11
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface SocialAccount {
    id: number
    provider: string
    account_name: string
}

interface Post {
    id: number
    content: string
    platform: string
}

const props = defineProps<{
    socialAccounts: SocialAccount[]
    posts: Post[]
}>()

// ── Wizard step ────────────────────────────────────────────────────────────
const currentStep = ref<1 | 2 | 3 | 4>(1)

const STEPS = [
    { num: 1, label: 'الهدف والمنصة' },
    { num: 2, label: 'الإعلان الإبداعي' },
    { num: 3, label: 'الجمهور' },
    { num: 4, label: 'الميزانية' },
] as const

// ── Validation errors ──────────────────────────────────────────────────────
const stepErrors = ref<Record<number, string>>({})

function clearError(step: number) {
    delete stepErrors.value[step]
}

function validateStep(step: number): boolean {
    clearError(step)
    if (step === 1) {
        if (!form.name.trim()) {
            stepErrors.value[1] = 'يرجى إدخال اسم الحملة'
            return false
        }
        if (!form.objective) {
            stepErrors.value[1] = 'يرجى اختيار هدف الحملة'
            return false
        }
        if (!form.platform) {
            stepErrors.value[1] = 'يرجى اختيار المنصة'
            return false
        }
    }
    if (step === 2) {
        if (adCreativeMode.value === 'existing' && !form.post_id) {
            stepErrors.value[2] = 'يرجى اختيار منشور'
            return false
        }
        if (adCreativeMode.value === 'new' && !form.ad_copy?.trim()) {
            stepErrors.value[2] = 'يرجى كتابة نص الإعلان'
            return false
        }
    }
    if (step === 3) {
        if (!form.target_countries.length) {
            stepErrors.value[3] = 'يرجى اختيار دولة واحدة على الأقل'
            return false
        }
        if (!form.target_age_min || !form.target_age_max) {
            stepErrors.value[3] = 'يرجى تحديد الفئة العمرية'
            return false
        }
    }
    if (step === 4) {
        if (!form.budget_amount) {
            stepErrors.value[4] = 'يرجى تحديد مبلغ الميزانية'
            return false
        }
        if (!form.starts_at || !form.ends_at) {
            stepErrors.value[4] = 'يرجى تحديد تواريخ الحملة'
            return false
        }
    }
    return true
}

function goNext() {
    if (!validateStep(currentStep.value)) return
    if (currentStep.value < 4) currentStep.value = (currentStep.value + 1) as 1 | 2 | 3 | 4
}

function goPrev() {
    if (currentStep.value > 1) currentStep.value = (currentStep.value - 1) as 1 | 2 | 3 | 4
}

// ── Objectives ─────────────────────────────────────────────────────────────
const OBJECTIVES = [
    { id: 'awareness',   label: 'الوعي بالعلامة',   icon: 'bell' },
    { id: 'traffic',     label: 'الزيارات',          icon: 'arrowLeft' },
    { id: 'engagement',  label: 'التفاعل',           icon: 'chart' },
    { id: 'conversions', label: 'التحويلات',         icon: 'check' },
    { id: 'app_installs', label: 'تثبيت التطبيق',   icon: 'plus' },
    { id: 'video_views', label: 'مشاهدات الفيديو',  icon: 'eye' },
] as const

const PLATFORMS = [
    { id: 'instagram', label: 'انستجرام', icon: 'instagram' },
    { id: 'facebook',  label: 'فيسبوك',   icon: 'facebook' },
] as const

// ── Countries ──────────────────────────────────────────────────────────────
const COUNTRY_OPTIONS = [
    { code: 'sa', label: '🇸🇦 السعودية' },
    { code: 'ae', label: '🇦🇪 الإمارات' },
    { code: 'kw', label: '🇰🇼 الكويت' },
    { code: 'qa', label: '🇶🇦 قطر' },
    { code: 'bh', label: '🇧🇭 البحرين' },
    { code: 'om', label: '🇴🇲 عُمان' },
]

// ── Currencies ─────────────────────────────────────────────────────────────
const CURRENCIES = [
    { code: 'SAR', label: 'SAR — ريال سعودي' },
    { code: 'AED', label: 'AED — درهم' },
    { code: 'USD', label: 'USD — دولار' },
    { code: 'QAR', label: 'QAR — ريال قطري' },
    { code: 'KWD', label: 'KWD — دينار كويتي' },
    { code: 'BHD', label: 'BHD — دينار بحريني' },
    { code: 'OMR', label: 'OMR — ريال عُماني' },
]

// ── Ad creative mode ───────────────────────────────────────────────────────
const adCreativeMode = ref<'existing' | 'new'>('existing')

// ── Interests tag input ────────────────────────────────────────────────────
const newInterest = ref('')

function addInterest() {
    const val = newInterest.value.trim()
    if (val && !form.target_interests.includes(val) && form.target_interests.length < 20) {
        form.target_interests.push(val)
        newInterest.value = ''
    }
}
function removeInterest(i: number) {
    form.target_interests.splice(i, 1)
}

// ── Form ───────────────────────────────────────────────────────────────────
const form = useForm({
    name:               '',
    objective:          '' as string,
    platform:           '' as 'instagram' | 'facebook' | '',
    social_account_id:  null as number | null,
    post_id:            null as number | null,
    ad_copy:            '',
    ad_headline:        '',
    ad_description:     '',
    target_countries:   [] as string[],
    target_age_min:     18,
    target_age_max:     45,
    target_gender:      'all' as 'all' | 'male' | 'female',
    target_interests:   [] as string[],
    budget_type:        'daily' as 'daily' | 'lifetime',
    budget_amount:      '',
    budget_currency:    'SAR',
    starts_at:          '',
    ends_at:            '',
    status:             'draft' as 'draft' | 'pending',
})

function toggleCountry(code: string) {
    const idx = form.target_countries.indexOf(code)
    if (idx === -1) {
        form.target_countries.push(code)
    } else {
        form.target_countries.splice(idx, 1)
    }
}

function submit(status: 'draft' | 'pending') {
    if (!validateStep(currentStep.value)) return
    form.status = status
    // Clear ad_copy if using existing post and vice versa
    if (adCreativeMode.value === 'existing') form.ad_copy = ''
    if (adCreativeMode.value === 'new') form.post_id = null
    form.post('/campaigns', { forceFormData: false })
}

// ── Step progress ──────────────────────────────────────────────────────────
function stepState(num: number): 'active' | 'completed' | 'upcoming' {
    if (num === currentStep.value) return 'active'
    if (num < currentStep.value) return 'completed'
    return 'upcoming'
}

// ── Computed helpers ───────────────────────────────────────────────────────
const filteredPosts = computed(() =>
    form.platform
        ? props.posts.filter(p => p.platform === form.platform)
        : props.posts
)
</script>

<template>
    <AppLayout title="إنشاء حملة" :crumbs="['الرئيسية', 'الحملات', 'إنشاء حملة']">
        <div class="create-wrap">

            <!-- Back link -->
            <div class="page-hd" style="margin-bottom:24px;">
                <Link href="/campaigns" class="back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    الحملات
                </Link>
                <span class="page-hd-sep">/</span>
                <h1 class="page-hd-title">إنشاء حملة جديدة</h1>
            </div>

            <!-- ── Step indicator ── -->
            <div class="wizard-steps">
                <div
                    v-for="(step, idx) in STEPS"
                    :key="step.num"
                    class="wizard-step"
                >
                    <div class="step-dot" :data-state="stepState(step.num)">
                        <svg v-if="stepState(step.num) === 'completed'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        <span v-else>{{ step.num }}</span>
                    </div>
                    <div class="step-label" :data-state="stepState(step.num)">{{ step.label }}</div>
                    <div v-if="idx < STEPS.length - 1" class="step-connector" :data-done="step.num < currentStep" />
                </div>
            </div>

            <!-- ── Step content ── -->
            <div class="card wizard-card">

                <!-- ══ Step 1: Objective & Platform ══ -->
                <template v-if="currentStep === 1">
                    <div class="card-head">
                        <div>
                            <h3>الهدف والمنصة</h3>
                            <div class="sub">اختر هدف حملتك الإعلانية والمنصة المستهدفة</div>
                        </div>
                    </div>
                    <div class="card-body stack-lg">

                        <!-- Campaign name -->
                        <div class="input-group">
                            <label class="input-label">اسم الحملة</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="input"
                                placeholder="مثلاً: حملة اليوم الوطني - أكتوبر 2026"
                                maxlength="120"
                            />
                            <span v-if="form.errors.name" class="input-error">{{ form.errors.name }}</span>
                        </div>

                        <!-- Objective -->
                        <div class="input-group">
                            <label class="input-label">هدف الحملة</label>
                            <div class="objective-grid">
                                <button
                                    v-for="obj in OBJECTIVES"
                                    :key="obj.id"
                                    type="button"
                                    class="chip objective-chip"
                                    :data-selected="form.objective === obj.id"
                                    @click="form.objective = obj.id"
                                >
                                    <Icon :name="obj.icon" :size="14" />
                                    {{ obj.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Platform -->
                        <div class="input-group">
                            <label class="input-label">المنصة</label>
                            <div class="platform-row">
                                <button
                                    v-for="pl in PLATFORMS"
                                    :key="pl.id"
                                    type="button"
                                    class="chip platform-chip"
                                    :data-selected="form.platform === pl.id"
                                    @click="form.platform = pl.id"
                                >
                                    <Icon :name="pl.icon" :size="16" />
                                    {{ pl.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Social account -->
                        <div class="input-group">
                            <label class="input-label">الحساب الاجتماعي <span class="input-hint">(اختياري)</span></label>
                            <select v-model="form.social_account_id" class="select">
                                <option :value="null">— اختر حساباً —</option>
                                <option
                                    v-for="acc in socialAccounts"
                                    :key="acc.id"
                                    :value="acc.id"
                                >{{ acc.account_name }} ({{ acc.provider }})</option>
                            </select>
                        </div>

                        <!-- Step error -->
                        <div v-if="stepErrors[1]" class="alert alert-error">
                            <div class="alert-body"><div class="alert-desc">{{ stepErrors[1] }}</div></div>
                        </div>
                    </div>
                </template>

                <!-- ══ Step 2: Ad Creative ══ -->
                <template v-if="currentStep === 2">
                    <div class="card-head">
                        <div>
                            <h3>الإعلان الإبداعي</h3>
                            <div class="sub">اختر محتوى إعلانك — منشور موجود أو نص جديد</div>
                        </div>
                    </div>
                    <div class="card-body stack-lg">

                        <!-- Mode selector -->
                        <div class="segmented creative-mode-seg">
                            <button
                                :data-active="adCreativeMode === 'existing'"
                                @click="adCreativeMode = 'existing'"
                            >
                                <Icon name="eye" :size="13" />
                                استخدم منشوراً موجوداً
                            </button>
                            <button
                                :data-active="adCreativeMode === 'new'"
                                @click="adCreativeMode = 'new'"
                            >
                                <Icon name="sparkle" :size="13" />
                                أنشئ محتوى جديداً
                            </button>
                        </div>

                        <!-- Existing post picker -->
                        <template v-if="adCreativeMode === 'existing'">
                            <div v-if="filteredPosts.length === 0" class="alert alert-error" style="border-color:var(--border-subtle);background:var(--bg-muted);">
                                <div class="alert-body">
                                    <div class="alert-desc" style="color:var(--text-muted);">
                                        لا توجد منشورات منشورة بعد
                                        <Link href="/generate" style="color:var(--accent);margin-right:6px;">أنشئ منشوراً الآن</Link>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="post-picker">
                                <div
                                    v-for="post in filteredPosts"
                                    :key="post.id"
                                    class="post-item"
                                    :class="{ 'post-item--selected': form.post_id === post.id }"
                                    @click="form.post_id = post.id"
                                >
                                    <div class="post-item-check">
                                        <svg v-if="form.post_id === post.id" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    </div>
                                    <div class="post-item-meta">
                                        <span class="post-item-platform">{{ post.platform }}</span>
                                        <p class="post-item-content">{{ post.content }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- New ad copy -->
                        <template v-if="adCreativeMode === 'new'">
                            <!-- Headline -->
                            <div class="input-group">
                                <label class="input-label">
                                    العنوان الرئيسي (Headline)
                                    <span class="input-hint">مطلوب لإعلانات Meta</span>
                                </label>
                                <input
                                    v-model="form.ad_headline"
                                    type="text"
                                    class="input"
                                    placeholder="مثلاً: خصم 50% على جميع المنتجات"
                                    maxlength="40"
                                />
                                <div class="input-hint" style="display:flex;justify-content:space-between;">
                                    <span>يظهر بخط كبير تحت صورة الإعلان · الحد 40 حرفاً</span>
                                    <span :style="form.ad_headline.length > 35 ? 'color:var(--error)' : ''">{{ form.ad_headline.length }}/40</span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="input-group">
                                <label class="input-label">
                                    الوصف (Description)
                                    <span class="input-hint">اختياري</span>
                                </label>
                                <input
                                    v-model="form.ad_description"
                                    type="text"
                                    class="input"
                                    placeholder="مثلاً: تسوّق الآن واحصل على شحن مجاني"
                                    maxlength="30"
                                />
                                <div class="input-hint" style="display:flex;justify-content:space-between;">
                                    <span>يظهر تحت العنوان · الحد 30 حرفاً</span>
                                    <span :style="form.ad_description.length > 27 ? 'color:var(--error)' : ''">{{ form.ad_description.length }}/30</span>
                                </div>
                            </div>

                            <!-- Ad body copy -->
                            <div class="input-group">
                                <label class="input-label">نص الإعلان (Body Copy)</label>
                                <textarea
                                    v-model="form.ad_copy"
                                    class="textarea"
                                    rows="5"
                                    placeholder="اكتب نص إعلانك الرئيسي هنا... كن محدداً وجذاباً"
                                    style="min-height:140px;"
                                />
                                <div class="input-hint" style="display:flex;justify-content:flex-end;">
                                    <span>{{ form.ad_copy.length }} حرف</span>
                                </div>
                            </div>
                        </template>

                        <!-- Step error -->
                        <div v-if="stepErrors[2]" class="alert alert-error">
                            <div class="alert-body"><div class="alert-desc">{{ stepErrors[2] }}</div></div>
                        </div>
                    </div>
                </template>

                <!-- ══ Step 3: Audience ══ -->
                <template v-if="currentStep === 3">
                    <div class="card-head">
                        <div>
                            <h3>الجمهور المستهدف</h3>
                            <div class="sub">حدد خصائص الجمهور الذي تريد الوصول إليه</div>
                        </div>
                    </div>
                    <div class="card-body stack-lg">

                        <!-- Countries -->
                        <div class="input-group">
                            <label class="input-label">الدول المستهدفة</label>
                            <div class="chips-row">
                                <button
                                    v-for="c in COUNTRY_OPTIONS"
                                    :key="c.code"
                                    type="button"
                                    class="chip"
                                    :data-selected="form.target_countries.includes(c.code)"
                                    @click="toggleCountry(c.code)"
                                >{{ c.label }}</button>
                            </div>
                        </div>

                        <!-- Age range -->
                        <div class="input-group">
                            <label class="input-label">الفئة العمرية</label>
                            <div class="age-range-row">
                                <div class="age-input-wrap">
                                    <label class="age-sub-label">من</label>
                                    <input
                                        v-model.number="form.target_age_min"
                                        type="number"
                                        class="input age-input"
                                        min="13"
                                        max="65"
                                    />
                                    <span class="age-unit">سنة</span>
                                </div>
                                <div class="age-separator">—</div>
                                <div class="age-input-wrap">
                                    <label class="age-sub-label">إلى</label>
                                    <input
                                        v-model.number="form.target_age_max"
                                        type="number"
                                        class="input age-input"
                                        min="13"
                                        max="65"
                                    />
                                    <span class="age-unit">سنة</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="input-group">
                            <label class="input-label">الجنس</label>
                            <div class="chips-row">
                                <button type="button" class="chip" :data-selected="form.target_gender === 'all'"    @click="form.target_gender = 'all'">الجميع</button>
                                <button type="button" class="chip" :data-selected="form.target_gender === 'male'"   @click="form.target_gender = 'male'">ذكور</button>
                                <button type="button" class="chip" :data-selected="form.target_gender === 'female'" @click="form.target_gender = 'female'">إناث</button>
                            </div>
                        </div>

                        <!-- Interests -->
                        <div class="input-group">
                            <div class="label-row">
                                <label class="input-label" style="margin:0;">الاهتمامات</label>
                                <span class="badge badge-neutral">{{ form.target_interests.length }}/٢٠</span>
                            </div>
                            <div v-if="form.target_interests.length" class="chips-row" style="margin-bottom:10px;">
                                <span
                                    v-for="(interest, i) in form.target_interests"
                                    :key="i"
                                    class="badge badge-neutral"
                                    style="display:inline-flex;align-items:center;gap:4px;"
                                >
                                    {{ interest }}
                                    <button type="button" class="badge-remove-btn" @click="removeInterest(i)">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>
                            <div v-if="form.target_interests.length < 20" class="row-sm">
                                <input
                                    v-model="newInterest"
                                    type="text"
                                    class="input"
                                    placeholder="مثلاً: الموضة، التقنية، الرياضة..."
                                    @keydown.enter.prevent="addInterest"
                                />
                                <button type="button" class="btn btn-secondary btn-sm" style="flex-shrink:0;" @click="addInterest">إضافة</button>
                            </div>
                        </div>

                        <!-- Step error -->
                        <div v-if="stepErrors[3]" class="alert alert-error">
                            <div class="alert-body"><div class="alert-desc">{{ stepErrors[3] }}</div></div>
                        </div>
                    </div>
                </template>

                <!-- ══ Step 4: Budget & Schedule ══ -->
                <template v-if="currentStep === 4">
                    <div class="card-head">
                        <div>
                            <h3>الميزانية والجدول</h3>
                            <div class="sub">حدد ميزانيتك وتواريخ تشغيل الحملة</div>
                        </div>
                    </div>
                    <div class="card-body stack-lg">

                        <!-- Budget type -->
                        <div class="input-group">
                            <label class="input-label">نوع الميزانية</label>
                            <div class="chips-row">
                                <button type="button" class="chip" :data-selected="form.budget_type === 'daily'"    @click="form.budget_type = 'daily'">يومي</button>
                                <button type="button" class="chip" :data-selected="form.budget_type === 'lifetime'" @click="form.budget_type = 'lifetime'">إجمالي</button>
                            </div>
                        </div>

                        <!-- Budget amount + currency -->
                        <div class="budget-row">
                            <div class="input-group" style="flex:1;">
                                <label class="input-label">مبلغ الميزانية</label>
                                <input
                                    v-model="form.budget_amount"
                                    type="number"
                                    class="input"
                                    min="1"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                            </div>
                            <div class="input-group" style="min-width:200px;">
                                <label class="input-label">العملة</label>
                                <select v-model="form.budget_currency" class="select">
                                    <option
                                        v-for="curr in CURRENCIES"
                                        :key="curr.code"
                                        :value="curr.code"
                                    >{{ curr.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date range -->
                        <div class="budget-row">
                            <div class="input-group" style="flex:1;">
                                <label class="input-label">تاريخ البدء</label>
                                <input
                                    v-model="form.starts_at"
                                    type="date"
                                    class="input"
                                />
                            </div>
                            <div class="input-group" style="flex:1;">
                                <label class="input-label">تاريخ الانتهاء</label>
                                <input
                                    v-model="form.ends_at"
                                    type="date"
                                    class="input"
                                />
                            </div>
                        </div>

                        <!-- Step error -->
                        <div v-if="stepErrors[4]" class="alert alert-error">
                            <div class="alert-body"><div class="alert-desc">{{ stepErrors[4] }}</div></div>
                        </div>

                        <!-- Final action buttons -->
                        <div class="final-actions">
                            <button
                                type="button"
                                class="btn btn-ghost"
                                :disabled="form.processing"
                                @click="submit('draft')"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                حفظ كمسودة
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary btn-lg"
                                :disabled="form.processing"
                                @click="submit('pending')"
                            >
                                <svg v-if="form.processing" class="spin" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <Icon v-else name="megaphone" :size="15" />
                                {{ form.processing ? 'جارٍ الإطلاق...' : 'إطلاق الحملة' }}
                            </button>
                        </div>
                    </div>
                </template>

            </div>

            <!-- ── Wizard navigation ── -->
            <div class="wizard-nav">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    class="btn btn-secondary"
                    @click="goPrev"
                >
                    <Icon name="chevronRight" :size="14" />
                    السابق
                </button>
                <span v-else />

                <button
                    v-if="currentStep < 4"
                    type="button"
                    class="btn btn-primary"
                    @click="goNext"
                >
                    التالي
                    <Icon name="chevronLeft" :size="14" />
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.create-wrap { max-width: 720px; margin: 0 auto; }

/* ── Page header ── */
.page-hd {
    display: flex;
    align-items: center;
    gap: 10px;
}
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: color .15s;
}
.back-link:hover { color: var(--text-primary); }
.back-link svg { transform: scaleX(-1); }
.page-hd-sep   { color: var(--border-default); font-size: 14px; }
.page-hd-title { margin: 0; font-size: 18px; font-weight: 700; color: var(--text-primary); }

/* ── Wizard steps indicator ── */
.wizard-steps {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0;
    margin-bottom: 28px;
    position: relative;
}
.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 1;
    position: relative;
}

.step-dot {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 700;
    border: 2px solid var(--border-default);
    background: var(--bg-surface);
    color: var(--text-muted);
    transition: all .2s;
    position: relative;
    z-index: 1;
}
.step-dot[data-state="active"] {
    border-color: var(--sada-500);
    background: var(--sada-500);
    color: #fff;
    box-shadow: 0 0 0 4px color-mix(in oklab, var(--sada-500) 18%, transparent);
}
.step-dot[data-state="completed"] {
    border-color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
}

.step-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-align: center;
    white-space: nowrap;
    transition: color .2s;
}
.step-label[data-state="active"]    { color: var(--sada-500); }
.step-label[data-state="completed"] { color: var(--text-secondary); }

/* Connector line between dots */
.step-connector {
    position: absolute;
    top: 18px;
    left: calc(-50% + 18px);
    right: calc(50% + 18px);
    height: 2px;
    background: var(--border-default);
    transition: background .3s;
}
.step-connector[data-done="true"] { background: var(--sada-500); }

/* ── Wizard card ── */
.wizard-card { margin-bottom: 16px; }

/* ── Objective grid ── */
.objective-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.objective-chip {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 12px;
    font-size: 13px;
    border-radius: var(--radius-md);
}
@media (max-width: 600px) { .objective-grid { grid-template-columns: repeat(2, 1fr); } }

/* ── Platform chips ── */
.platform-row { display: flex; gap: 10px; }
.platform-chip {
    flex: 1;
    justify-content: center;
    height: 44px;
    font-size: 14px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Chips row ── */
.chips-row { display: flex; flex-wrap: wrap; gap: 8px; }

/* ── Creative mode segmented ── */
.creative-mode-seg {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
}

/* ── Post picker ── */
.post-picker {
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid var(--border-default);
    border-radius: var(--radius-md);
    display: flex;
    flex-direction: column;
    gap: 0;
}
.post-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    cursor: pointer;
    transition: background .15s;
}
.post-item:last-child { border-bottom: none; }
.post-item:hover { background: var(--bg-muted); }
.post-item--selected { background: color-mix(in oklab, var(--sada-500) 6%, transparent); }
.post-item-check {
    width: 20px; height: 20px;
    border-radius: 50%;
    border: 2px solid var(--border-default);
    display: grid;
    place-items: center;
    flex-shrink: 0;
    margin-top: 2px;
    color: var(--sada-500);
    transition: border-color .15s;
}
.post-item--selected .post-item-check { border-color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 12%, transparent); }
.post-item-meta { flex: 1; min-width: 0; }
.post-item-platform {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: block;
}
.post-item-content {
    margin: 0;
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.6;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* ── Age range ── */
.age-range-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.age-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.age-sub-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
    white-space: nowrap;
}
.age-input {
    width: 80px;
    text-align: center;
}
.age-unit { font-size: 13px; color: var(--text-muted); }
.age-separator { font-size: 18px; color: var(--border-default); font-weight: 300; }

/* ── Label + counter row ── */
.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

/* ── Badge remove button ── */
.badge-remove-btn {
    display: inline-flex;
    align-items: center;
    border: none;
    background: transparent;
    cursor: pointer;
    color: inherit;
    padding: 0;
    opacity: .65;
    line-height: 1;
}
.badge-remove-btn:hover { opacity: 1; }

/* ── Budget row ── */
.budget-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

/* ── Final actions ── */
.final-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--border-subtle);
    margin-top: 8px;
    flex-wrap: wrap;
}

/* ── Input error ── */
.input-error {
    font-size: 12px;
    color: var(--error);
    margin-top: 2px;
}

/* ── Wizard navigation ── */
.wizard-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 4px;
}

/* ── Spinner ── */
.spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

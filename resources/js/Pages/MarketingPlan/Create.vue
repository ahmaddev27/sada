<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Occasion { id: number; key: string; name: string; icon: string; color: string; type: string }
interface WorkspaceMeta { id: number; name: string; business_type: string | null }

const props = defineProps<{ workspace: WorkspaceMeta; occasions: Occasion[] }>()

// ── Form state ────────────────────────────────────────────────────────────────
const step = ref(1)
const submitting = ref(false)

const form = ref({
    // Step 1
    business_name: props.workspace.name,
    business_type: props.workspace.business_type ?? '',
    description:   '',
    usp:           '',
    // Step 2
    goal:     'awareness' as string,
    duration: '3_months' as string,
    budget:   '',
    currency: 'SAR' as string,
    // Step 3
    countries: [] as string[],
    age_min:   18,
    age_max:   45,
    gender:    'all' as string,
    interests: [] as string[],
    interestInput: '',
    // Step 4
    platforms: [] as string[],
    occasions: [] as string[],
})

const STEPS = [
    { n: 1, label: 'بيانات العمل' },
    { n: 2, label: 'الأهداف والميزانية' },
    { n: 3, label: 'الجمهور المستهدف' },
    { n: 4, label: 'المنصات والمواسم' },
]

const GOALS = [
    { value: 'awareness',  label: 'زيادة الوعي',           icon: '📢', desc: 'توسيع انتشار علامتك التجارية' },
    { value: 'sales',      label: 'زيادة المبيعات',         icon: '💰', desc: 'تحويل المتابعين إلى مشترين' },
    { value: 'engagement', label: 'تعزيز التفاعل',         icon: '❤️', desc: 'بناء مجتمع نشط حول علامتك' },
    { value: 'leads',      label: 'توليد العملاء',         icon: '🎯', desc: 'استقطاب عملاء محتملين' },
    { value: 'retention',  label: 'الاحتفاظ بالعملاء',    icon: '🔄', desc: 'تعزيز ولاء العملاء الحاليين' },
]

const DURATIONS = [
    { value: '1_month',   label: 'شهر واحد',   sub: 'للإطلاق السريع' },
    { value: '3_months',  label: '3 أشهر',     sub: 'الأنسب للبداية' },
    { value: '6_months',  label: '6 أشهر',     sub: 'خطة نصف سنوية' },
    { value: '12_months', label: 'سنة كاملة',  sub: 'خطة استراتيجية' },
]

const CURRENCIES = ['SAR', 'AED', 'KWD', 'QAR', 'BHD', 'OMR', 'USD']

const COUNTRIES: Record<string, string> = {
    sa: 'السعودية', ae: 'الإمارات', kw: 'الكويت',
    qa: 'قطر',      bh: 'البحرين', om: 'عُمان',
}

const PLATFORMS = [
    { value: 'instagram', label: 'انستجرام',  color: '#E1306C' },
    { value: 'facebook',  label: 'فيسبوك',    color: '#1877F2' },
    { value: 'tiktok',    label: 'تيك توك',   color: '#010101' },
    { value: 'snapchat',  label: 'سناب شات',  color: '#FFFC00', dark: true },
    { value: 'x',         label: 'X (تويتر)', color: '#000000' },
]

const OCCASION_TYPE_LABELS: Record<string, string> = {
    islamic: 'مناسبات إسلامية',
    national: 'أيام وطنية',
    commercial: 'مناسبات تجارية',
}

const groupedOccasions = computed(() => {
    const groups: Record<string, Occasion[]> = {}
    for (const o of props.occasions) {
        if (!groups[o.type]) groups[o.type] = []
        groups[o.type].push(o)
    }
    return groups
})

// ── Step validation ────────────────────────────────────────────────────────────
const step1Valid = computed(() =>
    form.value.business_name.trim().length >= 2 &&
    form.value.description.trim().length >= 10 &&
    form.value.usp.trim().length >= 5
)
const step2Valid = computed(() =>
    form.value.goal && form.value.duration && Number(form.value.budget) > 0
)
const step3Valid = computed(() => form.value.countries.length > 0)
const step4Valid = computed(() => form.value.platforms.length > 0)

const canNext = computed(() => {
    if (step.value === 1) return step1Valid.value
    if (step.value === 2) return step2Valid.value
    if (step.value === 3) return step3Valid.value
    return step4Valid.value
})

// ── Helpers ────────────────────────────────────────────────────────────────────
function toggleItem<T>(arr: T[], item: T) {
    const idx = arr.indexOf(item)
    if (idx >= 0) arr.splice(idx, 1)
    else arr.push(item)
}

function addInterest() {
    const val = form.value.interestInput.trim()
    if (val && !form.value.interests.includes(val)) {
        form.value.interests.push(val)
    }
    form.value.interestInput = ''
}

function removeInterest(i: number) {
    form.value.interests.splice(i, 1)
}

function submit() {
    submitting.value = true
    const { interestInput, ...payload } = form.value
    router.post('/marketing-plan', payload, {
        onError: () => { submitting.value = false },
    })
}
</script>

<template>
    <AppLayout title="خطة تسويقية جديدة" :crumbs="['الخطط التسويقية', 'جديدة']">
        <div class="create-wrap">

            <!-- Progress -->
            <div class="progress-bar-wrap">
                <div
                    v-for="s in STEPS"
                    :key="s.n"
                    :class="['step-dot', { active: step === s.n, done: step > s.n }]"
                    @click="step > s.n && (step = s.n)"
                >
                    <div class="step-circle">
                        <Icon v-if="step > s.n" name="check" :size="12" />
                        <span v-else>{{ s.n }}</span>
                    </div>
                    <span class="step-label">{{ s.label }}</span>
                </div>
                <div class="progress-line">
                    <div class="progress-fill" :style="{ width: ((step - 1) / 3 * 100) + '%' }" />
                </div>
            </div>

            <!-- Card -->
            <div class="step-card">

                <!-- Step 1: Business Info -->
                <div v-if="step === 1">
                    <div class="step-header">
                        <div class="step-icon">📋</div>
                        <div>
                            <h2>بيانات العمل</h2>
                            <p>أخبرنا عن عملك لنبني عليه خطة مخصصة</p>
                        </div>
                    </div>
                    <div class="form-cols">
                        <div class="form-group col-2">
                            <label class="form-label">اسم العمل / العلامة التجارية *</label>
                            <input v-model="form.business_name" type="text" class="input" placeholder="مثال: مطعم البيت الخليجي" />
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">نوع العمل *</label>
                            <input v-model="form.business_type" type="text" class="input" placeholder="مثال: مطعم، متجر إلكتروني، عيادة..." />
                        </div>
                        <div class="form-group col-full">
                            <label class="form-label">وصف المنتج / الخدمة * <span class="form-hint-inline">(كن محدداً)</span></label>
                            <textarea v-model="form.description" class="input" rows="3" placeholder="ماذا تقدم بالضبط؟ مثال: مطعم متخصص في المأكولات الخليجية التقليدية يخدم عائلات الرياض..." />
                            <div class="form-hint">{{ form.description.length }} / 500 حرف</div>
                        </div>
                        <div class="form-group col-full">
                            <label class="form-label">ما الذي يميزك عن المنافسين؟ *</label>
                            <input v-model="form.usp" type="text" class="input" placeholder="مثال: وصفات جدّية أصيلة، توصيل خلال 30 دقيقة، أسعار تنافسية..." />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Goals & Budget -->
                <div v-if="step === 2">
                    <div class="step-header">
                        <div class="step-icon">🎯</div>
                        <div>
                            <h2>الأهداف والميزانية</h2>
                            <p>حدد هدفك الرئيسي وميزانيتك التسويقية</p>
                        </div>
                    </div>
                    <div class="section-title">الهدف الرئيسي</div>
                    <div class="goal-grid">
                        <button
                            v-for="g in GOALS"
                            :key="g.value"
                            type="button"
                            :class="['goal-card', { selected: form.goal === g.value }]"
                            @click="form.goal = g.value"
                        >
                            <span class="goal-emoji">{{ g.icon }}</span>
                            <span class="goal-label">{{ g.label }}</span>
                            <span class="goal-desc">{{ g.desc }}</span>
                        </button>
                    </div>
                    <div class="section-title" style="margin-top:24px">مدة الخطة</div>
                    <div class="duration-grid">
                        <button
                            v-for="d in DURATIONS"
                            :key="d.value"
                            type="button"
                            :class="['duration-card', { selected: form.duration === d.value }]"
                            @click="form.duration = d.value"
                        >
                            <span class="dur-label">{{ d.label }}</span>
                            <span class="dur-sub">{{ d.sub }}</span>
                        </button>
                    </div>
                    <div class="section-title" style="margin-top:24px">الميزانية الإجمالية</div>
                    <div class="budget-row">
                        <input v-model="form.budget" type="number" class="input" placeholder="0" min="0" style="flex:1" />
                        <select v-model="form.currency" class="input" style="width:90px">
                            <option v-for="c in CURRENCIES" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                </div>

                <!-- Step 3: Target Audience -->
                <div v-if="step === 3">
                    <div class="step-header">
                        <div class="step-icon">👥</div>
                        <div>
                            <h2>الجمهور المستهدف</h2>
                            <p>كلما كنت أدق، كانت الخطة أكثر تخصيصاً</p>
                        </div>
                    </div>
                    <div class="section-title">الدول المستهدفة *</div>
                    <div class="country-grid">
                        <button
                            v-for="(label, code) in COUNTRIES"
                            :key="code"
                            type="button"
                            :class="['country-btn', { selected: form.countries.includes(code) }]"
                            @click="toggleItem(form.countries, code)"
                        >
                            {{ label }}
                        </button>
                    </div>
                    <div class="form-cols" style="margin-top:20px">
                        <div class="form-group col-2">
                            <label class="form-label">الفئة العمرية</label>
                            <div style="display:flex;align-items:center;gap:10px">
                                <input v-model.number="form.age_min" type="number" class="input" min="13" max="65" style="flex:1" />
                                <span style="color:var(--text-muted)">—</span>
                                <input v-model.number="form.age_max" type="number" class="input" min="13" max="65" style="flex:1" />
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">الجنس</label>
                            <select v-model="form.gender" class="input">
                                <option value="all">الجنسين</option>
                                <option value="male">ذكور</option>
                                <option value="female">إناث</option>
                            </select>
                        </div>
                        <div class="form-group col-full">
                            <label class="form-label">الاهتمامات (اضغط Enter لإضافة)</label>
                            <div class="tags-input-wrap">
                                <div class="tags-list">
                                    <span v-for="(int, i) in form.interests" :key="i" class="tag-item">
                                        {{ int }}
                                        <button type="button" @click="removeInterest(i)">×</button>
                                    </span>
                                </div>
                                <input
                                    v-model="form.interestInput"
                                    type="text"
                                    class="tags-input"
                                    placeholder="مثال: الطبخ، الرياضة، التكنولوجيا..."
                                    @keydown.enter.prevent="addInterest"
                                />
                            </div>
                            <div class="form-hint">اضغط Enter بعد كل اهتمام</div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Platforms & Seasons -->
                <div v-if="step === 4">
                    <div class="step-header">
                        <div class="step-icon">🚀</div>
                        <div>
                            <h2>المنصات والمواسم</h2>
                            <p>اختر منصاتك وحدد المناسبات التي تريد استغلالها</p>
                        </div>
                    </div>
                    <div class="section-title">المنصات المستهدفة *</div>
                    <div class="platform-grid">
                        <button
                            v-for="p in PLATFORMS"
                            :key="p.value"
                            type="button"
                            :class="['platform-btn', { selected: form.platforms.includes(p.value) }]"
                            @click="toggleItem(form.platforms, p.value)"
                            :style="form.platforms.includes(p.value) ? { borderColor: p.color, background: p.color + '18' } : {}"
                        >
                            <span class="platform-dot" :style="{ background: p.color }" />
                            <span>{{ p.label }}</span>
                        </button>
                    </div>
                    <div class="section-title" style="margin-top:24px">المناسبات الموسمية (اختياري)</div>
                    <p style="font-size:12px;color:var(--text-muted);margin:0 0 14px">اختر المناسبات التي تريد توظيفها في خطتك</p>
                    <div
                        v-for="(group, type) in groupedOccasions"
                        :key="type"
                        style="margin-bottom:16px"
                    >
                        <div class="occasion-group-label">{{ OCCASION_TYPE_LABELS[type] ?? type }}</div>
                        <div class="occasion-grid">
                            <button
                                v-for="o in group"
                                :key="o.id"
                                type="button"
                                :class="['occasion-btn', { selected: form.occasions.includes(o.name) }]"
                                @click="toggleItem(form.occasions, o.name)"
                                :style="form.occasions.includes(o.name) ? { borderColor: o.color, background: o.color + '18' } : {}"
                            >
                                {{ o.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="step-nav">
                    <button
                        v-if="step > 1"
                        type="button"
                        class="btn btn-ghost"
                        @click="step--"
                        :disabled="submitting"
                    >
                        السابق
                    </button>
                    <div style="flex:1" />
                    <button
                        v-if="step < 4"
                        type="button"
                        class="btn btn-primary"
                        :disabled="!canNext"
                        @click="step++"
                    >
                        التالي
                        <Icon name="chevron-left" :size="14" style="transform:scaleX(-1)" />
                    </button>
                    <button
                        v-else
                        type="button"
                        class="btn btn-primary btn-generate"
                        :disabled="!canNext || submitting"
                        @click="submit"
                    >
                        <span v-if="submitting" class="spin-dot" />
                        <Icon v-else name="sparkle" :size="16" />
                        {{ submitting ? 'جارٍ التوليد...' : 'توليد الخطة التسويقية' }}
                    </button>
                </div>
            </div>

            <!-- Generating overlay -->
            <Transition name="fade">
                <div v-if="submitting" class="generating-overlay">
                    <div class="generating-card">
                        <div class="gen-spinner" />
                        <h3>الذكاء الاصطناعي يبني خطتك...</h3>
                        <p>جارٍ تحليل عملك وبناء خطة تسويقية شاملة ومخصصة للسوق الخليجي</p>
                        <div class="gen-steps">
                            <div class="gen-step">✓ تحليل بيانات العمل</div>
                            <div class="gen-step">✓ دراسة الجمهور المستهدف</div>
                            <div class="gen-step anim">⏳ بناء استراتيجية المحتوى...</div>
                        </div>
                    </div>
                </div>
            </Transition>

        </div>
    </AppLayout>
</template>

<style scoped>
.create-wrap { max-width: 780px; margin: 0 auto; padding: 0 4px 60px; }

/* Progress bar */
.progress-bar-wrap {
    display: flex; align-items: flex-start; justify-content: center;
    gap: 0; margin-bottom: 32px; position: relative;
}
.progress-line {
    position: absolute; top: 20px; left: 15%; right: 15%; height: 2px;
    background: var(--border-default); z-index: 0;
}
.progress-fill { height: 100%; background: var(--sada-500); transition: width 0.4s ease; }
.step-dot {
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; flex: 1; position: relative; z-index: 1;
    cursor: default;
}
.step-dot.done { cursor: pointer; }
.step-circle {
    width: 40px; height: 40px; border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 700;
    background: var(--bg-subtle); border: 2px solid var(--border-default);
    color: var(--text-muted); transition: all 0.2s;
}
.step-dot.active .step-circle { background: var(--sada-500); border-color: var(--sada-500); color: #fff; }
.step-dot.done .step-circle   { background: var(--sada-500); border-color: var(--sada-500); color: #fff; }
.step-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-align: center; white-space: nowrap; }
.step-dot.active .step-label { color: var(--sada-500); }
.step-dot.done .step-label   { color: var(--text-secondary); }

/* Step card */
.step-card {
    background: var(--bg-card); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg); padding: 32px;
}
.step-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; }
.step-icon   { font-size: 32px; line-height: 1; }
.step-header h2 { font-size: 18px; font-weight: 700; margin: 0 0 4px; }
.step-header p  { font-size: 13px; color: var(--text-muted); margin: 0; }

/* Form */
.form-cols  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-2  { grid-column: span 1; }
.col-full { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.form-hint  { font-size: 11px; color: var(--text-muted); }
.form-hint-inline { font-weight: 400; color: var(--text-muted); }
.section-title { font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 12px; }

/* Goals */
.goal-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.goal-card {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 14px 8px;
    background: var(--bg-subtle); border: 2px solid var(--border-default);
    border-radius: var(--radius-md); cursor: pointer;
    transition: all 0.15s; text-align: center;
}
.goal-card:hover { border-color: var(--sada-300, #6ab7a0); }
.goal-card.selected { border-color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 8%, transparent); }
.goal-emoji { font-size: 24px; line-height: 1; }
.goal-label { font-size: 12px; font-weight: 700; color: var(--text-primary); }
.goal-desc  { font-size: 10px; color: var(--text-muted); line-height: 1.4; }

/* Duration */
.duration-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.duration-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 14px 8px;
    background: var(--bg-subtle); border: 2px solid var(--border-default);
    border-radius: var(--radius-md); cursor: pointer; transition: all 0.15s;
}
.duration-card:hover { border-color: var(--sada-300, #6ab7a0); }
.duration-card.selected { border-color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 8%, transparent); }
.dur-label { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.dur-sub   { font-size: 10px; color: var(--text-muted); }

/* Budget row */
.budget-row { display: flex; gap: 10px; align-items: center; }

/* Countries */
.country-grid { display: flex; gap: 8px; flex-wrap: wrap; }
.country-btn {
    padding: 8px 16px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
    background: var(--bg-subtle); border: 2px solid var(--border-default);
    cursor: pointer; transition: all 0.15s; color: var(--text-primary);
}
.country-btn:hover   { border-color: var(--sada-300, #6ab7a0); }
.country-btn.selected { border-color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 10%, transparent); color: var(--sada-600); }

/* Tags input */
.tags-input-wrap {
    border: 1px solid var(--border-default); border-radius: var(--radius-sm);
    padding: 8px 10px; background: var(--bg-card);
    display: flex; flex-wrap: wrap; gap: 6px; min-height: 44px;
}
.tags-list { display: contents; }
.tag-item {
    display: inline-flex; align-items: center; gap: 4px;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-600); padding: 2px 8px; border-radius: var(--radius-full);
    font-size: 12px; font-weight: 600;
}
.tag-item button { background: none; border: none; cursor: pointer; color: inherit; font-size: 14px; padding: 0; line-height: 1; }
.tags-input { border: none; outline: none; background: transparent; font-family: var(--font-arabic); font-size: 13px; flex: 1; min-width: 160px; }

/* Platforms */
.platform-grid { display: flex; gap: 10px; flex-wrap: wrap; }
.platform-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
    background: var(--bg-subtle); border: 2px solid var(--border-default);
    cursor: pointer; transition: all 0.15s; color: var(--text-primary);
}
.platform-btn:hover { border-color: var(--border-hover, #bbb); }
.platform-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

/* Occasions */
.occasion-group-label { font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 8px; }
.occasion-grid { display: flex; gap: 6px; flex-wrap: wrap; }
.occasion-btn {
    padding: 6px 12px; border-radius: var(--radius-full); font-size: 12px; font-weight: 600;
    background: var(--bg-subtle); border: 1px solid var(--border-default);
    cursor: pointer; transition: all 0.15s; color: var(--text-muted);
}
.occasion-btn:hover    { color: var(--text-primary); border-color: var(--border-hover, #bbb); }
.occasion-btn.selected { color: var(--text-primary); font-weight: 700; }

/* Step nav */
.step-nav { display: flex; align-items: center; gap: 10px; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-default); }
.btn-generate { gap: 8px; padding: 12px 24px; font-size: 15px; }
.spin-dot { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; animation: spin 0.8s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Generating overlay */
.generating-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000;
}
.generating-card {
    background: var(--bg-card); border-radius: var(--radius-lg);
    padding: 40px 48px; text-align: center; max-width: 420px; width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
}
.gen-spinner {
    width: 60px; height: 60px; border-radius: 50%;
    border: 4px solid color-mix(in oklab, var(--sada-500) 20%, transparent);
    border-top-color: var(--sada-500);
    animation: spin 0.9s linear infinite;
    margin: 0 auto 20px;
}
.generating-card h3 { font-size: 18px; font-weight: 700; margin: 0 0 10px; }
.generating-card p  { font-size: 13px; color: var(--text-muted); margin: 0 0 20px; line-height: 1.7; }
.gen-steps { display: flex; flex-direction: column; gap: 8px; text-align: right; }
.gen-step  { font-size: 13px; color: var(--text-secondary); padding: 6px 10px; background: var(--bg-subtle); border-radius: var(--radius-sm); }
.gen-step.anim { color: var(--sada-500); font-weight: 600; }

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 600px) {
    .step-card { padding: 20px; }
    .form-cols { grid-template-columns: 1fr; }
    .col-2, .col-full { grid-column: span 1; }
    .goal-grid { grid-template-columns: repeat(3, 1fr); }
    .duration-grid { grid-template-columns: repeat(2, 1fr); }
    .step-label { display: none; }
}
</style>

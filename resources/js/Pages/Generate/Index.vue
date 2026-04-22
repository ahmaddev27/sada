<script setup lang="ts">
// CG-01→CG-11
import { ref, computed, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import type { PageProps } from '@/Types'

interface SocialAccount {
    id: number
    provider: string
    account_name: string
    provider_account_id: string
}

interface Variation {
    title: string
    body: string
    tags: string[]
    char_count: number
}

defineProps<{ socialAccounts: SocialAccount[] }>()

const page = usePage<PageProps>()
const wsDialect = page.props.currentWorkspace?.default_dialect ?? 'sa'

// ── Form state ─────────────────────────────────────────────
const contentType   = ref<string>('post')
const platform      = ref<string>('instagram')
const dialect       = ref<string>(wsDialect)
const prompt        = ref<string>('')
const useBrand      = ref<boolean>(true)
const includeEmojis = ref<boolean>(true)
const length        = ref<string>('med')
const cta           = ref<string>('')
const advancedOpen  = ref<boolean>(false)

// ── Occasion context (from seasonal redirect) ───────────────
const occasionName  = ref<string>('')
const occasionBanner = ref<string>('')

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    const key    = params.get('occasion_key')
    const name   = params.get('occasion_name')
    const tags   = params.get('hashtags')
    if (name) {
        occasionName.value   = name
        occasionBanner.value = name
        if (!prompt.value) {
            prompt.value = `محتوى خاص بمناسبة ${name}`
        }
        if (tags && !cta.value) {
            // pre-append hashtags hint into prompt
            prompt.value += ` — ${tags.replace(/,/g, ' ')}`
        }
    }
})

// ── Schedule modal ───────────────────────────────────────────
const scheduleModal = ref<boolean>(false)
const schedDate     = ref<string>('')
const schedTime     = ref<string>('09:00')

const minDate = computed(() => {
    const d = new Date()
    d.setMinutes(d.getMinutes() + 5)
    return d.toISOString().slice(0, 10)
})

const schedValid = computed(() => {
    if (!schedDate.value || !schedTime.value) return false
    const dt = new Date(`${schedDate.value}T${schedTime.value}`)
    return dt > new Date()
})

const schedPreview = computed(() => {
    if (!schedDate.value || !schedTime.value) return ''
    return new Date(`${schedDate.value}T${schedTime.value}`)
        .toLocaleString('ar-SA', {
            weekday: 'long', year: 'numeric',
            month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        })
})

function setPreset(offset: number, hour = 9) {
    const d = new Date()
    d.setDate(d.getDate() + offset)
    schedDate.value = d.toISOString().slice(0, 10)
    schedTime.value = `${String(hour).padStart(2, '0')}:00`
}

function openScheduleModal() {
    if (!selectedVariation.value) return
    schedDate.value = ''
    schedTime.value = '09:00'
    scheduleModal.value = true
}

function confirmSchedule() {
    if (!schedValid.value || !selectedVariation.value) return
    router.post('/generate/save', {
        content:       isEditing.value ? editingBody.value : selectedVariation.value.body,
        hashtags:      selectedVariation.value.tags,
        platform:      platform.value,
        content_type:  contentType.value,
        dialect:       dialect.value,
        action:        'schedule',
        scheduled_for: `${schedDate.value}T${schedTime.value}`,
    })
    scheduleModal.value = false
}

// ── Output state ────────────────────────────────────────────
const variations    = ref<Variation[]>([])
const selectedIdx   = ref<number>(0)
const loading       = ref<boolean>(false)
const error         = ref<string>('')
const tokensCharged = ref<number>(0)

const selectedVariation = computed(() => variations.value[selectedIdx.value] ?? null)

// ── Platform limits (CG-11) ─────────────────────────────────
const PLATFORM_LIMITS: Record<string, number> = {
    instagram: 2200, facebook: 63206,
    tiktok: 2200, snapchat: 250, x: 280,
}
const charLimit = computed(() => PLATFORM_LIMITS[platform.value] ?? 2200)

// ── Editing ─────────────────────────────────────────────────
const editingBody = ref<string>('')
const isEditing   = ref<boolean>(false)

watch(selectedIdx, () => {
    editingBody.value = selectedVariation.value?.body ?? ''
    isEditing.value   = false
})

function startEdit() {
    editingBody.value = selectedVariation.value?.body ?? ''
    isEditing.value   = true
}
function saveEdit() {
    if (selectedVariation.value) {
        selectedVariation.value.body       = editingBody.value
        selectedVariation.value.char_count = editingBody.value.length
    }
    isEditing.value = false
}

// ── Generate ─────────────────────────────────────────────────
async function generate() {
    if (!prompt.value.trim()) {
        error.value = 'يرجى إدخال وصف الفكرة.'
        return
    }
    loading.value   = true
    error.value     = ''
    variations.value = []

    try {
        const resp = await fetch('/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            },
            body: JSON.stringify({
                content_type:   contentType.value,
                platform:       platform.value,
                dialect:        dialect.value,
                prompt:         prompt.value,
                use_brand:      useBrand.value,
                include_emojis: includeEmojis.value,
                length:         length.value,
                cta:            cta.value,
            }),
        })

        const data = await resp.json()

        if (!resp.ok) {
            error.value = data.error ?? 'فشل التوليد. حاول مرة أخرى.'
            return
        }

        variations.value  = data.variations
        tokensCharged.value = data.tokens_charged
        selectedIdx.value  = 0
        editingBody.value  = data.variations[0]?.body ?? ''
    } catch {
        error.value = 'حدث خطأ في الاتصال. حاول مرة أخرى.'
    } finally {
        loading.value = false
    }
}

// ── Save ─────────────────────────────────────────────────────
function savePost(action: 'draft' | 'publish') {
    if (!selectedVariation.value) return
    router.post('/generate/save', {
        content:      isEditing.value ? editingBody.value : selectedVariation.value.body,
        hashtags:     selectedVariation.value.tags,
        platform:     platform.value,
        content_type: contentType.value,
        dialect:      dialect.value,
        action,
    })
}

function copyToClipboard() {
    const text = (isEditing.value ? editingBody.value : selectedVariation.value?.body) ?? ''
    const tags  = selectedVariation.value?.tags.join(' ') ?? ''
    navigator.clipboard.writeText(text + (tags ? '\n\n' + tags : ''))
}

// ── Consts ───────────────────────────────────────────────────
const CONTENT_TYPES = [
    { id: 'post',         label: 'منشور',  icon: 'image' },
    { id: 'reel',         label: 'ريل',    icon: 'video' },
    { id: 'story',        label: 'قصة',    icon: 'star' },
    { id: 'ad',           label: 'إعلان',  icon: 'megaphone' },
]

const PLATFORMS = [
    { id: 'instagram', label: 'انستجرام', icon: 'instagram' },
    { id: 'facebook',  label: 'فيسبوك',   icon: 'facebook' },
]

const DIALECTS = [
    { id: 'fos', label: 'الفصحى',      flag: '🌐' },
    { id: 'sa',  label: 'السعودية',    flag: '🇸🇦' },
    { id: 'ae',  label: 'الإماراتية',  flag: '🇦🇪' },
    { id: 'kw',  label: 'الكويتية',    flag: '🇰🇼' },
    { id: 'qa',  label: 'القطرية',     flag: '🇶🇦' },
    { id: 'bh',  label: 'البحرينية',   flag: '🇧🇭' },
    { id: 'om',  label: 'العُمانية',   flag: '🇴🇲' },
]

const selectedDialectLabel = computed(() => DIALECTS.find(d => d.id === dialect.value)?.label ?? '')
</script>

<template>
    <AppLayout title="توليد محتوى" :crumbs="['الرئيسية', 'توليد محتوى']">

        <!-- Schedule modal -->
        <Teleport to="body">
            <div v-if="scheduleModal" class="sched-backdrop" @click.self="scheduleModal = false">
                <div class="sched-modal">

                    <!-- Head -->
                    <div class="sched-head">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="sched-head-icon">
                                <Icon name="calendar" :size="16" style="color:var(--accent)" />
                            </div>
                            <div>
                                <h3 style="margin:0;font-size:15px;font-weight:700">جدولة المنشور</h3>
                                <p style="margin:2px 0 0;font-size:12px;color:var(--text-muted)">اختر متى يُنشر هذا المحتوى</p>
                            </div>
                        </div>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="scheduleModal = false">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="sched-body">

                        <!-- Quick presets -->
                        <div>
                            <div class="sched-section-label">اختيار سريع</div>
                            <div class="sched-presets">
                                <button class="sched-preset" @click="setPreset(0, 10)">
                                    <span class="sched-preset-icon">☀️</span>
                                    <span class="sched-preset-title">اليوم</span>
                                    <span class="sched-preset-sub">10:00 ص</span>
                                </button>
                                <button class="sched-preset" @click="setPreset(1, 9)">
                                    <span class="sched-preset-icon">📅</span>
                                    <span class="sched-preset-title">غداً</span>
                                    <span class="sched-preset-sub">09:00 ص</span>
                                </button>
                                <button class="sched-preset" @click="setPreset(1, 19)">
                                    <span class="sched-preset-icon">🌙</span>
                                    <span class="sched-preset-title">غداً مساءً</span>
                                    <span class="sched-preset-sub">07:00 م</span>
                                </button>
                                <button class="sched-preset" @click="setPreset(7, 9)">
                                    <span class="sched-preset-icon">📆</span>
                                    <span class="sched-preset-title">الأسبوع القادم</span>
                                    <span class="sched-preset-sub">09:00 ص</span>
                                </button>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="flex:1;height:1px;background:var(--border-subtle)" />
                            <span style="font-size:11px;color:var(--text-muted);font-weight:600">أو اختر يدوياً</span>
                            <div style="flex:1;height:1px;background:var(--border-subtle)" />
                        </div>

                        <!-- Date + Time inputs -->
                        <div class="sched-inputs">
                            <div class="sched-field">
                                <label class="sched-field-label">
                                    <Icon name="calendar" :size="12" /> التاريخ
                                </label>
                                <input
                                    v-model="schedDate"
                                    type="date"
                                    class="sched-input"
                                    :min="minDate"
                                />
                            </div>
                            <div class="sched-field">
                                <label class="sched-field-label">
                                    <Icon name="clock" :size="12" /> الوقت
                                </label>
                                <input
                                    v-model="schedTime"
                                    type="time"
                                    class="sched-input"
                                />
                            </div>
                        </div>

                        <!-- Preview -->
                        <Transition name="fade">
                            <div v-if="schedPreview" class="sched-preview">
                                <Icon name="check" :size="14" style="color:var(--success);flex-shrink:0" />
                                <span>سيُنشر: <strong>{{ schedPreview }}</strong></span>
                            </div>
                        </Transition>

                    </div>

                    <!-- Footer -->
                    <div class="sched-foot">
                        <button class="btn btn-ghost btn-sm" @click="scheduleModal = false">إلغاء</button>
                        <button
                            class="btn btn-primary btn-sm"
                            :disabled="!schedValid"
                            @click="confirmSchedule"
                        >
                            <Icon name="calendar" :size="14" />
                            تأكيد الجدولة
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <div class="gen-grid">

            <!-- ═══ RIGHT PANEL: Input (sticky) ═══ -->
            <div class="card gen-input-panel">
                <div class="card-head">
                    <div>
                        <h3>لوحة التوليد</h3>
                        <div class="sub">اضبط المعايير ثم ولّد ٣ خيارات</div>
                    </div>
                    <span class="badge badge-brand">
                        <Icon name="sparkle" :size="12" />
                        40 توكن
                    </span>
                </div>

                <div class="card-body stack-lg">

                    <!-- Occasion banner (from Seasonal redirect) -->
                    <div v-if="occasionBanner" class="occasion-banner">
                        <Icon name="moon" :size="14" />
                        <span>مناسبة: <strong>{{ occasionBanner }}</strong></span>
                        <button class="btn btn-icon btn-ghost" style="width:18px;height:18px;padding:0;" @click="occasionBanner = ''; prompt = ''">
                            <Icon name="x" :size="11" />
                        </button>
                    </div>

                    <!-- Content type (CG-01) -->
                    <div class="input-group">
                        <label class="input-label">نوع المحتوى</label>
                        <div class="segmented" style="width:100%; display:grid; grid-template-columns:repeat(4,1fr);">
                            <button
                                v-for="t in CONTENT_TYPES"
                                :key="t.id"
                                :data-active="contentType === t.id"
                                @click="contentType = t.id"
                            >
                                <Icon :name="t.icon" :size="13" style="vertical-align:-2px; margin-left:3px;" />
                                {{ t.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Platform (CG-02) -->
                    <div class="input-group">
                        <label class="input-label">المنصة</label>
                        <div style="display:flex; gap:8px;">
                            <button
                                v-for="p in PLATFORMS"
                                :key="p.id"
                                class="chip"
                                :data-selected="platform === p.id"
                                style="flex:1; justify-content:center; height:40px; font-size:14px; border-radius:var(--radius-md);"
                                @click="platform = p.id"
                            >
                                <Icon :name="p.icon" :size="16" />
                                {{ p.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Dialect (CG-03) -->
                    <div class="input-group">
                        <label class="input-label">اللهجة</label>
                        <div class="dialect-wrap">
                            <button
                                v-for="d in DIALECTS"
                                :key="d.id"
                                class="dialect-chip"
                                :class="{ 'dialect-chip--active': dialect === d.id }"
                                @click="dialect = d.id"
                            >
                                <span>{{ d.flag }}</span>
                                <span>{{ d.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="stack-sm">
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label">استخدام هوية العلامة</div>
                                <div class="toggle-hint">يطبّق نبرة العلامة والكلمات المحظورة</div>
                            </div>
                            <button
                                class="toggle"
                                :data-on="useBrand"
                                @click="useBrand = !useBrand"
                                type="button"
                            />
                        </div>
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label">إيموجيات في المحتوى</div>
                                <div class="toggle-hint">مناسبة للسياق الخليجي</div>
                            </div>
                            <button
                                class="toggle"
                                :data-on="includeEmojis"
                                @click="includeEmojis = !includeEmojis"
                                type="button"
                            />
                        </div>
                    </div>

                    <!-- Prompt (CG-04) -->
                    <div class="input-group">
                        <label class="input-label">الفكرة</label>
                        <textarea
                            v-model="prompt"
                            class="textarea"
                            placeholder="اكتب وصفاً قصيراً للفكرة... مثال: خصم 30% بمناسبة اليوم الوطني"
                            style="min-height:96px;"
                            maxlength="500"
                        />
                        <div class="input-hint" style="display:flex; justify-content:space-between;">
                            <span>كن محدداً للحصول على أفضل النتائج</span>
                            <span>{{ prompt.length }} / 500</span>
                        </div>
                    </div>

                    <!-- Advanced -->
                    <div>
                        <button
                            class="btn btn-ghost btn-sm"
                            style="padding:0; color:var(--text-muted);"
                            type="button"
                            @click="advancedOpen = !advancedOpen"
                        >
                            <Icon :name="advancedOpen ? 'chevronDown' : 'chevronLeft'" :size="14" />
                            إعدادات متقدمة
                        </button>

                        <div v-if="advancedOpen" class="advanced-panel">
                            <div class="input-group">
                                <label class="input-label" style="font-size:12px;">طول النص</label>
                                <select v-model="length" class="select" style="height:34px; font-size:13px;">
                                    <option value="short">قصير · 50-100 حرف</option>
                                    <option value="med">متوسط · 150-250 حرف</option>
                                    <option value="long">طويل · 300+ حرف</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label class="input-label" style="font-size:12px;">CTA</label>
                                <input v-model="cta" class="input" style="height:34px; font-size:13px;" placeholder="مثلاً: اطلب الآن" />
                            </div>
                        </div>
                    </div>

                    <!-- Error -->
                    <div v-if="error" class="alert alert-error">
                        <div class="alert-body">
                            <div class="alert-desc">{{ error }}</div>
                        </div>
                    </div>

                    <!-- Generate button -->
                    <button
                        class="btn btn-primary btn-lg"
                        style="width:100%;"
                        :disabled="loading || !prompt.trim()"
                        @click="generate"
                    >
                        <Icon name="sparkle" :size="16" />
                        {{ loading ? 'جارٍ التوليد...' : 'ولّد المحتوى (٣ خيارات)' }}
                    </button>

                </div>
            </div>

            <!-- ═══ LEFT PANEL: Output ═══ -->
            <div class="stack">

                <!-- Loading skeletons -->
                <template v-if="loading">
                    <div class="card" v-for="i in 3" :key="i" style="padding:0; overflow:hidden;">
                        <div class="skeleton" style="height:44px;" />
                        <div style="padding:16px;" class="stack-sm">
                            <div class="skeleton" style="height:14px;" />
                            <div class="skeleton" style="height:14px; width:80%;" />
                            <div class="skeleton" style="height:14px; width:60%;" />
                        </div>
                    </div>
                </template>

                <!-- Empty state -->
                <div v-else-if="!variations.length" class="gen-empty">
                    <div class="gen-empty-icon">
                        <Icon name="sparkle" :size="36" />
                    </div>
                    <h3>ولّد محتواك الأول</h3>
                    <p>اكتب فكرة في اللوحة على اليسار واضغط "ولّد المحتوى"</p>
                </div>

                <!-- Variations (CG-05, CG-07) -->
                <template v-else>
                    <!-- Header -->
                    <div class="section-head" style="margin-bottom:12px;">
                        <div style="font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px;">
                            <Icon name="sparkle" :size="14" style="color:var(--accent);" />
                            ٣ خيارات مولّدة · {{ selectedDialectLabel }}
                        </div>
                        <button class="btn btn-sm btn-ghost" @click="generate">
                            <Icon name="sparkle" :size="14" />
                            ولّد المزيد
                        </button>
                    </div>

                    <!-- Variation cards -->
                    <div
                        v-for="(v, i) in variations"
                        :key="i"
                        class="card card-hoverable variation-card"
                        :class="{ 'variation-card--selected': selectedIdx === i }"
                        @click="selectedIdx = i; isEditing = false"
                    >
                        <div class="variation-header" :class="{ 'variation-header--selected': selectedIdx === i }">
                            <span>{{ v.title }}</span>
                            <Icon v-if="selectedIdx === i" name="check" :size="14" />
                        </div>
                        <div style="padding:16px;">
                            <!-- Body (editable when selected) -->
                            <template v-if="selectedIdx === i && isEditing">
                                <textarea
                                    v-model="editingBody"
                                    class="textarea"
                                    style="min-height:120px; margin-bottom:8px;"
                                    :maxlength="charLimit"
                                    @click.stop
                                />
                                <div style="font-size:11px; color:var(--text-muted); text-align:left; margin-bottom:8px;">
                                    {{ editingBody.length }} / {{ charLimit }}
                                </div>
                            </template>
                            <p v-else class="variation-body">{{ v.body }}</p>

                            <!-- Hashtags -->
                            <div class="tags-row">
                                <span v-for="(tag, ti) in v.tags" :key="ti" class="tag-chip">{{ tag }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="variation-actions" @click.stop>
                                <span style="font-size:11px; color:var(--text-faint);">{{ v.char_count }} حرف</span>
                                <div style="display:flex; gap:4px;">
                                    <button class="btn btn-sm btn-ghost" @click="copyToClipboard">
                                        <Icon name="copy" :size="14" /> نسخ
                                    </button>
                                    <template v-if="selectedIdx === i">
                                        <button v-if="!isEditing" class="btn btn-sm btn-ghost" @click.stop="startEdit">
                                            <Icon name="edit" :size="14" /> تعديل
                                        </button>
                                        <button v-else class="btn btn-sm btn-accent-soft" @click.stop="saveEdit">
                                            <Icon name="check" :size="14" /> حفظ التعديل
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview (CG-07) -->
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>معاينة مباشرة</h3>
                                <div class="sub">كيف سيبدو على {{ platform === 'instagram' ? 'انستجرام' : 'فيسبوك' }}</div>
                            </div>
                        </div>
                        <div class="card-body" style="background:var(--bg-muted);">
                            <div class="ig-preview">
                                <!-- Header -->
                                <div class="ig-header">
                                    <div class="ig-avatar">أن</div>
                                    <div>
                                        <div style="font-size:13px; font-weight:700;">متجر_أنيق</div>
                                        <div style="font-size:11px; color:var(--text-muted);">الرياض، السعودية</div>
                                    </div>
                                </div>
                                <!-- Image placeholder -->
                                <div class="ig-image">
                                    <div style="text-align:center; color:var(--sand-700);">
                                        <div style="font-size:40px; font-weight:800;">📸</div>
                                        <div style="font-size:12px; margin-top:4px;">صورة المنشور</div>
                                    </div>
                                </div>
                                <!-- Caption -->
                                <div class="ig-caption">
                                    <span style="font-weight:700; margin-left:6px;">متجر_أنيق</span>
                                    <span style="white-space:pre-line;">{{ selectedVariation?.body }}</span>
                                    <div style="margin-top:6px; color:var(--info);">{{ selectedVariation?.tags.join(' ') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action bar (CG-08) -->
                    <div class="action-bar">
                        <div style="font-size:12px; color:var(--text-muted);">
                            الخيار المختار: <strong style="color:var(--text-primary);">{{ selectedVariation?.title }}</strong>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn btn-sm btn-ghost" @click="savePost('draft')">حفظ كمسودة</button>
                            <button class="btn btn-sm btn-secondary" @click="openScheduleModal">
                                <Icon name="calendar" :size="14" /> جدولة
                            </button>
                            <button class="btn btn-sm btn-primary" @click="savePost('publish')">
                                <Icon name="arrowLeft" :size="14" /> نشر فوري
                            </button>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Layout ── */
.gen-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) { .gen-grid { grid-template-columns: 1fr; } }

.gen-input-panel { position: sticky; top: 80px; }

/* ── Dialect chips ── */
.dialect-wrap {
    display: flex; gap: 6px; flex-wrap: wrap;
    padding: 4px;
    background: var(--bg-muted);
    border-radius: 12px;
}
.dialect-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 10px;
    border-radius: var(--radius-full);
    font-size: 12px; font-weight: 500;
    color: var(--text-secondary);
    background: transparent;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all var(--dur-fast);
}
.dialect-chip:hover { background: var(--bg-surface); }
.dialect-chip--active {
    background: var(--bg-surface);
    border-color: var(--accent);
    color: var(--accent-text);
    font-weight: 600;
}

/* ── Toggle row ── */
.toggle-row {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px;
    padding: 6px 0;
}
.toggle-label { font-size: 13px; font-weight: 600; }
.toggle-hint  { font-size: 12px; color: var(--text-muted); }

/* ── Advanced panel ── */
.advanced-panel {
    margin-top: 12px; padding: 14px;
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}

/* ── Empty state ── */
.gen-empty {
    background: var(--bg-surface);
    border: 1px dashed var(--border-default);
    border-radius: var(--radius-lg);
    padding: 48px 24px;
    text-align: center;
    display: flex; flex-direction: column;
    align-items: center; gap: 12px;
}
.gen-empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--accent-soft);
    display: grid; place-items: center;
    color: var(--accent);
}
.gen-empty h3 { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0; }
.gen-empty p  { font-size: 13px; color: var(--text-muted); margin: 0; max-width: 300px; }

/* ── Variation card ── */
.variation-card {
    cursor: pointer;
    border-color: var(--border-subtle);
    transition: border-color var(--dur-fast), box-shadow var(--dur-fast);
}
.variation-card--selected {
    border-color: var(--accent);
    box-shadow: var(--shadow-focus);
}
.variation-header {
    padding: 12px 16px;
    background: var(--bg-muted);
    border-bottom: 1px solid var(--border-subtle);
    display: flex; justify-content: space-between; align-items: center;
    color: var(--text-secondary); font-size: 13px; font-weight: 700;
}
.variation-header--selected {
    background: var(--accent-soft);
    color: var(--accent-text);
}
.variation-body {
    margin: 0; font-size: 14px; line-height: 1.7;
    color: var(--text-primary); white-space: pre-line;
}
.tags-row {
    display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px;
}
.tag-chip {
    font-size: 12px; font-weight: 500;
    color: var(--accent-text);
    padding: 3px 8px;
    background: var(--accent-soft);
    border-radius: 6px;
}
.variation-actions {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 14px; padding-top: 10px;
    border-top: 1px solid var(--border-subtle);
}

/* ── Instagram preview ── */
.ig-preview {
    max-width: 400px; margin: 0 auto;
    background: var(--bg-surface);
    border-radius: 12px; overflow: hidden;
    border: 1px solid var(--border-default);
    direction: rtl;
}
.ig-header {
    padding: 12px; display: flex; align-items: center; gap: 10px;
}
.ig-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--sada-500), var(--sand-500));
    display: grid; place-items: center;
    color: #fff; font-weight: 700; font-size: 14px;
}
.ig-image {
    aspect-ratio: 1/1;
    background: linear-gradient(135deg, var(--sand-100), var(--sand-200));
    display: grid; place-items: center;
}
.ig-caption {
    padding: 10px 12px 14px;
    font-size: 13px; line-height: 1.7;
}

/* ── Action bar ── */
.action-bar {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 14px;
    display: flex; gap: 10px;
    align-items: center; justify-content: space-between;
}

/* ── Occasion banner ── */
.occasion-banner {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px;
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
    border: 1px solid color-mix(in oklab, var(--sada-500) 25%, transparent);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--sada-600);
}
.occasion-banner strong { font-weight: 700; }
.occasion-banner button { margin-right: auto; }

/* ── Schedule modal ── */
.sched-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 500;
    display: grid; place-items: center;
    padding: 20px;
}
.sched-modal {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    width: 460px; max-width: 100%;
    overflow: hidden;
}
.sched-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-subtle);
}
.sched-head-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: color-mix(in oklab, var(--accent) 10%, transparent);
    display: grid; place-items: center; flex-shrink: 0;
}
.sched-body {
    padding: 20px;
    display: flex; flex-direction: column; gap: 16px;
}
.sched-section-label {
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px;
}

/* Quick presets */
.sched-presets {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
}
.sched-preset {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 10px 6px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    background: var(--bg-surface);
    cursor: pointer; font-family: var(--font-arabic);
    transition: all .15s;
}
.sched-preset:hover {
    border-color: var(--accent);
    background: color-mix(in oklab, var(--accent) 6%, transparent);
}
.sched-preset-icon { font-size: 18px; line-height: 1; }
.sched-preset-title { font-size: 12px; font-weight: 600; color: var(--text-primary); }
.sched-preset-sub   { font-size: 10px; color: var(--text-muted); }

/* Date + Time inputs */
.sched-inputs {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.sched-field { display: flex; flex-direction: column; gap: 6px; }
.sched-field-label {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600; color: var(--text-secondary);
}
.sched-input {
    height: 40px;
    padding: 0 12px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    background: var(--bg-surface);
    color: var(--text-primary);
    font-size: 13px;
    font-family: var(--font-arabic);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
}
.sched-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--accent) 15%, transparent);
}

/* Preview */
.sched-preview {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 14px;
    background: color-mix(in oklab, var(--success) 8%, transparent);
    border: 1px solid color-mix(in oklab, var(--success) 25%, transparent);
    border-radius: var(--radius-sm);
    font-size: 13px; color: var(--text-primary);
}

.sched-foot {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 14px 20px;
    border-top: 1px solid var(--border-subtle);
    background: var(--bg-muted);
}

/* Fade transition */
.fade-enter-active, .fade-leave-active { transition: opacity .2s, transform .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(4px); }
</style>

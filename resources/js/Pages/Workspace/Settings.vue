<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
import LogoCropper from '@/Components/Base/LogoCropper.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    workspace: {
        id: number;
        name: string;
        business_type: string | null;
        countries: string[] | null;
        default_dialect: string;
        logo_path: string | null;
    };
    brandIdentity: {
        description: string | null;
        tone: string | null;
        target_audience: string | null;
        banned_words: string[] | null;
        example_posts: string[] | null;
    } | null;
}>();

const activeTab = ref<'general' | 'brand'>('general');

const dialects: Record<string, string> = {
    fos: 'الفصحى',
    sa:  '🇸🇦 السعودية',
    ae:  '🇦🇪 الإمارات',
    kw:  '🇰🇼 الكويت',
    qa:  '🇶🇦 قطر',
    bh:  '🇧🇭 البحرين',
    om:  '🇴🇲 عُمان',
};

const countryOptions = [
    { code: 'sa', label: '🇸🇦 السعودية' },
    { code: 'ae', label: '🇦🇪 الإمارات' },
    { code: 'kw', label: '🇰🇼 الكويت' },
    { code: 'qa', label: '🇶🇦 قطر' },
    { code: 'bh', label: '🇧🇭 البحرين' },
    { code: 'om', label: '🇴🇲 عُمان' },
];

const tones = ['ودّية', 'عصرية', 'رسمية', 'فاخرة', 'مرحة', 'احترافية'];

// ── Logo cropper ───────────────────────────────────────────────────────────
const cropperOpen    = ref(false)
const pendingFile    = ref<File | null>(null)
const logoPreviewUrl = ref<string | null>(
    props.workspace.logo_path ? `/storage/${props.workspace.logo_path}` : null
)

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null
    if (!file) return
    pendingFile.value = file
    cropperOpen.value = true
    // reset input so same file can be re-selected
    ;(e.target as HTMLInputElement).value = ''
}

function onCrop(blob: Blob) {
    const file = new File([blob], 'logo.jpg', { type: 'image/jpeg' })
    wsForm.logo      = file
    logoPreviewUrl.value = URL.createObjectURL(blob)
    cropperOpen.value    = false
    pendingFile.value    = null
}

function closeCropper() {
    cropperOpen.value = false
    pendingFile.value = null
}

// ── Workspace form ─────────────────────────────────────────────────────────
const wsForm = useForm({
    name:            props.workspace.name,
    business_type:   props.workspace.business_type ?? '',
    countries:       props.workspace.countries ?? ['sa'],
    default_dialect: props.workspace.default_dialect,
    logo:            null as File | null,
});

function toggleCountry(code: string) {
    const idx = wsForm.countries.indexOf(code);
    if (idx === -1) {
        wsForm.countries.push(code);
    } else if (wsForm.countries.length > 1) {
        wsForm.countries.splice(idx, 1);
    }
}

function submitWs() {
    wsForm.post(`/workspaces/${props.workspace.id}`, { forceFormData: true });
}

// ── Brand identity form ────────────────────────────────────────────────────
const newBannedWord  = ref('');
const newExamplePost = ref('');

const brandForm = useForm({
    description:     props.brandIdentity?.description     ?? '',
    tone:            props.brandIdentity?.tone            ?? '',
    target_audience: props.brandIdentity?.target_audience ?? '',
    banned_words:    [...(props.brandIdentity?.banned_words  ?? [])] as string[],
    example_posts:   [...(props.brandIdentity?.example_posts ?? [])] as string[],
});

function addBannedWord() {
    const w = newBannedWord.value.trim();
    if (w && brandForm.banned_words.length < 10 && !brandForm.banned_words.includes(w)) {
        brandForm.banned_words.push(w);
        newBannedWord.value = '';
    }
}
function removeBannedWord(i: number) { brandForm.banned_words.splice(i, 1); }

function addExamplePost() {
    const p = newExamplePost.value.trim();
    if (p && brandForm.example_posts.length < 5) {
        brandForm.example_posts.push(p);
        newExamplePost.value = '';
    }
}
function removeExamplePost(i: number) { brandForm.example_posts.splice(i, 1); }

function submitBrand() {
    brandForm.post(`/workspaces/${props.workspace.id}/brand`);
}
</script>

<template>
    <AppLayout :title="`إعدادات — ${workspace.name}`">
        <div class="settings-wrap">

            <!-- Page header -->
            <div class="page-hd">
                <a href="/workspaces" class="back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    مساحات العمل
                </a>
                <span class="page-hd-sep">/</span>
                <h1 class="page-hd-title">{{ workspace.name }}</h1>
            </div>

            <!-- Tabs -->
            <div class="cmp-tabs" style="margin-bottom:24px">
                <button
                    :data-active="activeTab === 'general'"
                    @click="activeTab = 'general'"
                >معلومات المساحة</button>
                <button
                    :data-active="activeTab === 'brand'"
                    @click="activeTab = 'brand'"
                >هوية العلامة التجارية</button>
            </div>

            <!-- ── General tab ─────────────────────────────────────── -->
            <div v-if="activeTab === 'general'" class="card">
                <div class="card-head">
                    <h3>معلومات المساحة</h3>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submitWs" class="stack">

                        <!-- Logo upload -->
                        <div class="input-group">
                            <label class="input-label">شعار المساحة</label>
                            <div class="logo-row">
                                <div class="logo-preview">
                                    <img v-if="logoPreviewUrl" :src="logoPreviewUrl" alt="logo" />
                                    <span v-else>{{ workspace.name.charAt(0) }}</span>
                                </div>
                                <div class="logo-actions">
                                    <label class="btn btn-secondary btn-sm" style="cursor:pointer">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.4 18A5 5 0 0 0 18 8.4a8 8 0 1 0-11.6 8.6"/></svg>
                                        {{ wsForm.logo ? 'تغيير الشعار' : 'رفع شعار' }}
                                        <input
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp"
                                            style="display:none"
                                            @change="onFileChange"
                                        />
                                    </label>
                                    <button
                                        v-if="wsForm.logo"
                                        type="button"
                                        class="btn btn-ghost btn-sm"
                                        @click="cropperOpen = true"
                                        title="إعادة الاقتصاص"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M18 22V8a2 2 0 0 0-2-2H2"/></svg>
                                        اقتصاص
                                    </button>
                                </div>
                                <span class="hint-text">PNG, JPG, WEBP — أقصى 2MB</span>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="input-group">
                            <label class="input-label">اسم المساحة</label>
                            <input
                                v-model="wsForm.name"
                                type="text"
                                class="input"
                                placeholder="مثلاً: متجر أنيق"
                            />
                            <span v-if="wsForm.errors.name" class="input-error">{{ wsForm.errors.name }}</span>
                        </div>

                        <!-- Business type -->
                        <div class="input-group">
                            <label class="input-label">نوع النشاط <span class="input-hint">(اختياري)</span></label>
                            <input
                                v-model="wsForm.business_type"
                                type="text"
                                class="input"
                                placeholder="مثلاً: تجارة إلكترونية"
                            />
                        </div>

                        <!-- Countries -->
                        <div class="input-group">
                            <label class="input-label">الدول المستهدفة</label>
                            <div class="chips-row">
                                <button
                                    v-for="c in countryOptions"
                                    :key="c.code"
                                    type="button"
                                    class="chip"
                                    :data-selected="wsForm.countries.includes(c.code)"
                                    @click="toggleCountry(c.code)"
                                >{{ c.label }}</button>
                            </div>
                        </div>

                        <!-- Default dialect -->
                        <div class="input-group">
                            <label class="input-label">اللهجة الافتراضية</label>
                            <select v-model="wsForm.default_dialect" class="select">
                                <option v-for="(label, code) in dialects" :key="code" :value="code">{{ label }}</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" :disabled="wsForm.processing">
                                <svg v-if="wsForm.processing" class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                {{ wsForm.processing ? 'جارٍ الحفظ...' : 'حفظ التغييرات' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Brand tab ────────────────────────────────────────── -->
            <div v-if="activeTab === 'brand'" class="card">
                <div class="card-head">
                    <div>
                        <h3>هوية العلامة التجارية</h3>
                        <p class="sub">تساعد الذكاء الاصطناعي على فهم أسلوبك ونبرتك</p>
                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submitBrand" class="stack">

                        <!-- Description (BI-01) -->
                        <div class="input-group">
                            <label class="input-label">وصف العلامة التجارية</label>
                            <textarea
                                v-model="brandForm.description"
                                class="textarea"
                                rows="3"
                                placeholder="اكتب وصفاً مختصراً لعلامتك التجارية، منتجاتك أو خدماتك..."
                            />
                        </div>

                        <!-- Tone (BI-02) -->
                        <div class="input-group">
                            <label class="input-label">نبرة الصوت</label>
                            <div class="chips-row">
                                <button
                                    v-for="tone in tones"
                                    :key="tone"
                                    type="button"
                                    class="chip"
                                    :data-selected="brandForm.tone === tone"
                                    @click="brandForm.tone = tone"
                                >{{ tone }}</button>
                            </div>
                        </div>

                        <!-- Target audience -->
                        <div class="input-group">
                            <label class="input-label">الجمهور المستهدف</label>
                            <textarea
                                v-model="brandForm.target_audience"
                                class="textarea"
                                rows="2"
                                placeholder="مثلاً: شباب خليجيون من ١٨-٣٥ سنة مهتمون بالموضة والتقنية"
                                style="min-height:72px"
                            />
                        </div>

                        <!-- Banned words (BI-03) -->
                        <div class="input-group">
                            <div class="label-row">
                                <label class="input-label" style="margin:0">الكلمات المحظورة</label>
                                <span class="badge badge-neutral">{{ brandForm.banned_words.length }}/١٠</span>
                            </div>
                            <div v-if="brandForm.banned_words.length" class="chips-row" style="margin-bottom:10px">
                                <span
                                    v-for="(word, i) in brandForm.banned_words"
                                    :key="i"
                                    class="badge badge-error"
                                    style="display:inline-flex;align-items:center;gap:4px;cursor:default"
                                >
                                    {{ word }}
                                    <button
                                        type="button"
                                        class="badge-remove-btn"
                                        @click="removeBannedWord(i)"
                                        title="حذف"
                                    >
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>
                            <div v-if="brandForm.banned_words.length < 10" class="row-sm">
                                <input
                                    v-model="newBannedWord"
                                    type="text"
                                    class="input"
                                    placeholder="أضف كلمة محظورة..."
                                    @keydown.enter.prevent="addBannedWord"
                                />
                                <button type="button" class="btn btn-secondary btn-sm" style="flex-shrink:0" @click="addBannedWord">إضافة</button>
                            </div>
                        </div>

                        <!-- Example posts (BI-04) -->
                        <div class="input-group">
                            <div class="label-row">
                                <label class="input-label" style="margin:0">منشورات نموذجية</label>
                                <span class="badge badge-neutral">{{ brandForm.example_posts.length }}/٥</span>
                            </div>
                            <div v-if="brandForm.example_posts.length" class="stack-sm" style="margin-bottom:10px">
                                <div
                                    v-for="(post, i) in brandForm.example_posts"
                                    :key="i"
                                    class="example-post"
                                >
                                    <span>{{ post }}</span>
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-ghost btn-icon-sm"
                                        title="حذف"
                                        @click="removeExamplePost(i)"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div v-if="brandForm.example_posts.length < 5" class="row-sm" style="align-items:flex-end">
                                <textarea
                                    v-model="newExamplePost"
                                    class="textarea"
                                    rows="2"
                                    placeholder="الصق منشوراً سابقاً يمثل أسلوبك..."
                                    style="min-height:60px"
                                />
                                <button type="button" class="btn btn-secondary btn-sm" style="flex-shrink:0" @click="addExamplePost">إضافة</button>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" :disabled="brandForm.processing">
                                <svg v-if="brandForm.processing" class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                {{ brandForm.processing ? 'جارٍ الحفظ...' : 'حفظ الهوية التجارية' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Logo cropper modal -->
        <LogoCropper
            :show="cropperOpen"
            :file="pendingFile"
            @close="closeCropper"
            @crop="onCrop"
        />
    </AppLayout>
</template>

<style scoped>
.settings-wrap { max-width: 720px; margin: 0 auto; }

/* Page header */
.page-hd {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
    flex-wrap: wrap;
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
.page-hd-sep  { color: var(--border-default); font-size: 14px; }
.page-hd-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Logo */
.logo-row {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.logo-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.logo-preview {
    width: 56px; height: 56px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff;
    font-weight: 700;
    font-size: 22px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.logo-preview img { width: 100%; height: 100%; object-fit: cover; }
.hint-text { font-size: 12px; color: var(--text-muted); }

/* Chips row */
.chips-row { display: flex; flex-wrap: wrap; gap: 8px; }

/* Label + counter row */
.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

/* Badge remove button */
.badge-remove-btn {
    display: inline-flex;
    align-items: center;
    border: none;
    background: transparent;
    cursor: pointer;
    color: inherit;
    padding: 0;
    opacity: 0.65;
    line-height: 1;
}
.badge-remove-btn:hover { opacity: 1; }

/* Example post item */
.example-post {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 12px;
    background: var(--bg-page);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.6;
}
.example-post > span { flex: 1; }

/* Input error hint */
.input-error {
    font-size: 12px;
    color: var(--error);
    margin-top: 2px;
}

/* Spinner */
.spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

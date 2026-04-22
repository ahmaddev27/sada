<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/Base/Card.vue';
import Button from '@/Components/Base/Button.vue';
import Input from '@/Components/Base/Input.vue';
import Alert from '@/Components/Base/Alert.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const page = usePage();
const flashSuccess = computed(() => (page.props as any).flash?.success ?? null);

const dialects = {
    fos: 'الفصحى',
    sa:  '🇸🇦 السعودية',
    ae:  '🇦🇪 الإمارات',
    kw:  '🇰🇼 الكويت',
    qa:  '🇶🇦 قطر',
    bh:  '🇧🇭 البحرين',
    om:  '🇴🇲 عُمان',
};

const countries = [
    { code: 'sa', label: '🇸🇦 السعودية' },
    { code: 'ae', label: '🇦🇪 الإمارات' },
    { code: 'kw', label: '🇰🇼 الكويت' },
    { code: 'qa', label: '🇶🇦 قطر' },
    { code: 'bh', label: '🇧🇭 البحرين' },
    { code: 'om', label: '🇴🇲 عُمان' },
];

// Workspace form
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

// Brand identity form
const newBannedWord  = ref('');
const newExamplePost = ref('');

const brandForm = useForm({
    description:     props.brandIdentity?.description ?? '',
    tone:            props.brandIdentity?.tone ?? '',
    target_audience: props.brandIdentity?.target_audience ?? '',
    banned_words:    [...(props.brandIdentity?.banned_words ?? [])] as string[],
    example_posts:   [...(props.brandIdentity?.example_posts ?? [])] as string[],
});

function addBannedWord() {
    const w = newBannedWord.value.trim();
    if (w && brandForm.banned_words.length < 10 && !brandForm.banned_words.includes(w)) {
        brandForm.banned_words.push(w);
        newBannedWord.value = '';
    }
}

function removeBannedWord(i: number) {
    brandForm.banned_words.splice(i, 1);
}

function addExamplePost() {
    const p = newExamplePost.value.trim();
    if (p && brandForm.example_posts.length < 5) {
        brandForm.example_posts.push(p);
        newExamplePost.value = '';
    }
}

function removeExamplePost(i: number) {
    brandForm.example_posts.splice(i, 1);
}

function submitBrand() {
    brandForm.post(`/workspaces/${props.workspace.id}/brand`);
}

const activeTab = ref<'general' | 'brand'>('general');
</script>

<template>
    <AppLayout :title="`إعدادات — ${workspace.name}`">
        <div style="max-width: 760px; margin: 0 auto;">

            <!-- Page title -->
            <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                <a
                    href="/workspaces"
                    style="color: var(--color-ink-muted); text-decoration: none; font-size: 13px;"
                >← مساحات العمل</a>
                <span style="color: var(--color-border-default);">/</span>
                <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: var(--color-ink-base);">
                    {{ workspace.name }}
                </h1>
            </div>

            <!-- Flash -->
            <Alert v-if="flashSuccess" variant="success" style="margin-bottom: 16px;">{{ flashSuccess }}</Alert>

            <!-- Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0;">
                <button
                    v-for="tab in [{ key: 'general', label: 'معلومات المساحة' }, { key: 'brand', label: 'هوية العلامة التجارية' }]"
                    :key="tab.key"
                    :style="`
                        padding: 10px 16px; font-size: 13px; font-weight: 600;
                        border: none; background: transparent; cursor: pointer;
                        border-bottom: 2px solid ${activeTab === tab.key ? 'var(--color-sada-500)' : 'transparent'};
                        color: ${activeTab === tab.key ? 'var(--color-sada-600)' : 'var(--color-ink-muted)'};
                        margin-bottom: -1px;
                        font-family: var(--font-arabic);
                    `"
                    @click="activeTab = tab.key as any"
                >{{ tab.label }}</button>
            </div>

            <!-- ── General tab ─────────────────────────────────────── -->
            <Card v-if="activeTab === 'general'" padding="24px">
                <form @submit.prevent="submitWs" style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Logo upload -->
                    <div>
                        <p style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); margin: 0 0 8px;">شعار المساحة</p>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="
                                width: 56px; height: 56px; border-radius: 12px; overflow: hidden;
                                background: linear-gradient(135deg, var(--color-sada-500), var(--color-sada-600));
                                color: #fff; font-weight: 700; font-size: 22px;
                                display: grid; place-items: center;
                            ">
                                <img v-if="workspace.logo_path" :src="`/storage/${workspace.logo_path}`" style="width: 100%; height: 100%; object-fit: cover;" />
                                <span v-else>{{ workspace.name.charAt(0) }}</span>
                            </div>
                            <label style="
                                padding: 8px 14px; font-size: 13px; font-weight: 600;
                                border: 1px solid var(--color-border-default); border-radius: 8px;
                                color: var(--color-ink-base); cursor: pointer; font-family: var(--font-arabic);
                            ">
                                رفع شعار
                                <input type="file" accept="image/png,image/jpeg,image/svg+xml" style="display: none;" @change="(e) => wsForm.logo = (e.target as HTMLInputElement).files?.[0] ?? null" />
                            </label>
                            <span style="font-size: 11px; color: var(--color-ink-muted);">PNG, JPG, SVG — أقصى 2MB</span>
                        </div>
                    </div>

                    <Input v-model="wsForm.name" label="اسم المساحة" :error="wsForm.errors.name" />
                    <Input v-model="wsForm.business_type" label="نوع النشاط" placeholder="مثلاً: تجارة إلكترونية" />

                    <!-- Countries -->
                    <div>
                        <p style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); margin: 0 0 8px;">الدول المستهدفة</p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button
                                v-for="c in countries"
                                :key="c.code"
                                type="button"
                                :style="`
                                    padding: 5px 12px; border-radius: 99px; font-size: 12px; font-weight: 600;
                                    cursor: pointer; border: 1px solid;
                                    border-color: ${wsForm.countries.includes(c.code) ? 'var(--color-sada-500)' : 'var(--color-border-default)'};
                                    background: ${wsForm.countries.includes(c.code) ? 'color-mix(in oklab, var(--color-sada-500) 12%, transparent)' : 'transparent'};
                                    color: ${wsForm.countries.includes(c.code) ? 'var(--color-sada-600)' : 'var(--color-ink-muted)'};
                                    font-family: var(--font-arabic);
                                `"
                                @click="toggleCountry(c.code)"
                            >{{ c.label }}</button>
                        </div>
                    </div>

                    <!-- Dialect -->
                    <div>
                        <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); display: block; margin-bottom: 4px;">اللهجة الافتراضية</label>
                        <select
                            v-model="wsForm.default_dialect"
                            style="
                                width: 100%; padding: 9px 12px; font-size: 14px;
                                border-radius: 8px; border: 1px solid var(--color-border-default);
                                background: var(--color-bg-input); color: var(--color-ink-base);
                                font-family: var(--font-arabic); outline: none;
                            "
                        >
                            <option v-for="(label, code) in dialects" :key="code" :value="code">{{ label }}</option>
                        </select>
                    </div>

                    <Button type="submit" :loading="wsForm.processing">حفظ التغييرات</Button>
                </form>
            </Card>

            <!-- ── Brand identity tab ──────────────────────────────── -->
            <Card v-if="activeTab === 'brand'" padding="24px">
                <form @submit.prevent="submitBrand" style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Description -->
                    <div>
                        <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); display: block; margin-bottom: 4px;">وصف العلامة التجارية</label>
                        <textarea
                            v-model="brandForm.description"
                            rows="3"
                            placeholder="اكتب وصفاً مختصراً لعلامتك التجارية، منتجاتك أو خدماتك..."
                            style="
                                width: 100%; padding: 9px 12px; font-size: 14px;
                                border-radius: 8px; border: 1px solid var(--color-border-default);
                                background: var(--color-bg-input); color: var(--color-ink-base);
                                font-family: var(--font-arabic); outline: none; resize: vertical;
                                line-height: 1.6; box-sizing: border-box;
                            "
                        />
                    </div>

                    <!-- Tone -->
                    <div>
                        <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); display: block; margin-bottom: 8px;">نبرة الصوت</label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button
                                v-for="tone in ['ودّية', 'عصرية', 'رسمية', 'فاخرة', 'مرحة', 'احترافية']"
                                :key="tone"
                                type="button"
                                :style="`
                                    padding: 5px 12px; border-radius: 99px; font-size: 12px; font-weight: 600;
                                    cursor: pointer; border: 1px solid; font-family: var(--font-arabic);
                                    border-color: ${brandForm.tone === tone ? 'var(--color-sada-500)' : 'var(--color-border-default)'};
                                    background: ${brandForm.tone === tone ? 'color-mix(in oklab, var(--color-sada-500) 12%, transparent)' : 'transparent'};
                                    color: ${brandForm.tone === tone ? 'var(--color-sada-600)' : 'var(--color-ink-muted)'};
                                `"
                                @click="brandForm.tone = tone"
                            >{{ tone }}</button>
                        </div>
                    </div>

                    <!-- Target audience -->
                    <div>
                        <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); display: block; margin-bottom: 4px;">الجمهور المستهدف</label>
                        <textarea
                            v-model="brandForm.target_audience"
                            rows="2"
                            placeholder="مثلاً: شباب خليجيون من ١٨-٣٥ سنة مهتمون بالموضة والتقنية"
                            style="
                                width: 100%; padding: 9px 12px; font-size: 14px;
                                border-radius: 8px; border: 1px solid var(--color-border-default);
                                background: var(--color-bg-input); color: var(--color-ink-base);
                                font-family: var(--font-arabic); outline: none; resize: vertical;
                                line-height: 1.6; box-sizing: border-box;
                            "
                        />
                    </div>

                    <!-- Banned words (BI-03) -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base);">الكلمات المحظورة</label>
                            <span style="font-size: 11px; color: var(--color-ink-muted);">{{ brandForm.banned_words.length }}/١٠</span>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                            <span
                                v-for="(word, i) in brandForm.banned_words"
                                :key="i"
                                style="
                                    display: inline-flex; align-items: center; gap: 4px;
                                    padding: 3px 10px; border-radius: 99px; font-size: 12px;
                                    background: #FEF2F2; color: #991B1B; font-weight: 600;
                                "
                            >
                                {{ word }}
                                <button type="button" style="border: none; background: transparent; cursor: pointer; color: #991B1B; font-size: 14px; padding: 0 0 0 2px; line-height: 1;" @click="removeBannedWord(i)">×</button>
                            </span>
                        </div>
                        <div v-if="brandForm.banned_words.length < 10" style="display: flex; gap: 8px;">
                            <input
                                v-model="newBannedWord"
                                type="text"
                                placeholder="أضف كلمة محظورة..."
                                style="
                                    flex: 1; padding: 7px 12px; font-size: 13px;
                                    border-radius: 8px; border: 1px solid var(--color-border-default);
                                    background: var(--color-bg-input); color: var(--color-ink-base);
                                    font-family: var(--font-arabic); outline: none;
                                "
                                @keydown.enter.prevent="addBannedWord"
                            />
                            <Button type="button" variant="secondary" size="sm" @click="addBannedWord">إضافة</Button>
                        </div>
                    </div>

                    <!-- Example posts (BI-04) -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base);">منشورات نموذجية</label>
                            <span style="font-size: 11px; color: var(--color-ink-muted);">{{ brandForm.example_posts.length }}/٥</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px;">
                            <div
                                v-for="(post, i) in brandForm.example_posts"
                                :key="i"
                                style="
                                    display: flex; gap: 8px; align-items: flex-start;
                                    padding: 10px 12px; border-radius: 8px;
                                    background: var(--color-bg-page);
                                    border: 1px solid var(--color-border-subtle);
                                    font-size: 13px; color: var(--color-ink-base); line-height: 1.6;
                                "
                            >
                                <span style="flex: 1;">{{ post }}</span>
                                <button type="button" style="border: none; background: transparent; cursor: pointer; color: var(--color-ink-muted); font-size: 16px; line-height: 1;" @click="removeExamplePost(i)">×</button>
                            </div>
                        </div>
                        <div v-if="brandForm.example_posts.length < 5" style="display: flex; gap: 8px; align-items: flex-end;">
                            <textarea
                                v-model="newExamplePost"
                                rows="2"
                                placeholder="الصق منشوراً سابقاً يمثل أسلوبك..."
                                style="
                                    flex: 1; padding: 7px 12px; font-size: 13px;
                                    border-radius: 8px; border: 1px solid var(--color-border-default);
                                    background: var(--color-bg-input); color: var(--color-ink-base);
                                    font-family: var(--font-arabic); outline: none; resize: vertical;
                                "
                            />
                            <Button type="button" variant="secondary" size="sm" @click="addExamplePost">إضافة</Button>
                        </div>
                    </div>

                    <Button type="submit" :loading="brandForm.processing">حفظ الهوية التجارية</Button>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

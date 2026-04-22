<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Button from '@/Components/Base/Button.vue';
import Modal from '@/Components/Base/Modal.vue';
import Input from '@/Components/Base/Input.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    workspaces: Array<{
        id: number;
        name: string;
        business_type: string | null;
        countries: string[] | null;
        default_dialect: string;
        logo_path: string | null;
    }>;
    currentWorkspaceId: number | null;
}>();

const showCreate  = ref(false);
const showArchive = ref(false);
const switching   = ref<number | null>(null);
const archiveTarget = ref<{ id: number; name: string } | null>(null);

const form = useForm({
    name:            '',
    business_type:   '',
    countries:       ['sa'] as string[],
    default_dialect: 'sa',
});

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

function toggleCountry(code: string) {
    const idx = form.countries.indexOf(code);
    if (idx === -1) {
        form.countries.push(code);
    } else if (form.countries.length > 1) {
        form.countries.splice(idx, 1);
    }
}

function submit() {
    form.post('/workspaces', {
        onSuccess: () => { showCreate.value = false; form.reset(); form.countries = ['sa']; },
    });
}

function switchWorkspace(id: number) {
    if (id === props.currentWorkspaceId) return;
    switching.value = id;
    router.post(`/workspaces/${id}/switch`, {}, {
        onFinish: () => { switching.value = null; },
    });
}

function openArchive(id: number, name: string) {
    archiveTarget.value = { id, name };
    showArchive.value = true;
}

function confirmArchive() {
    if (!archiveTarget.value) return;
    router.post(`/workspaces/${archiveTarget.value.id}/archive`, {}, {
        onFinish: () => { showArchive.value = false; archiveTarget.value = null; },
    });
}

function cancelArchive() {
    showArchive.value = false;
    archiveTarget.value = null;
}

function closeModal() {
    showCreate.value = false;
    form.reset();
    form.countries = ['sa'];
}
</script>

<template>
    <AppLayout title="مساحات العمل">
        <div class="ws-page">

            <!-- Header -->
            <div class="ws-header">
                <div>
                    <h1 class="ws-title">مساحات العمل</h1>
                    <p class="ws-sub">أدر مساحاتك ومشاريعك التسويقية</p>
                </div>
                <Button @click="showCreate = true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M12 5v14M5 12h14"/></svg>
                    مساحة جديدة
                </Button>
            </div>

            <!-- Empty state -->
            <div v-if="workspaces.length === 0" class="ws-empty">
                <div class="ws-empty-icon">🏢</div>
                <p class="ws-empty-title">لا توجد مساحات عمل بعد</p>
                <p class="ws-empty-desc">أنشئ مساحتك الأولى للبدء بإدارة تسويقك</p>
                <Button @click="showCreate = true">إنشاء مساحة عمل</Button>
            </div>

            <!-- Workspace grid -->
            <div v-else class="ws-grid">
                <div
                    v-for="ws in workspaces"
                    :key="ws.id"
                    class="ws-card"
                    :class="{ 'ws-card--active': ws.id === currentWorkspaceId }"
                >
                    <!-- Active ribbon -->
                    <div v-if="ws.id === currentWorkspaceId" class="ws-active-ribbon">
                        <span class="ws-active-dot" />
                        مفعّلة
                    </div>

                    <!-- Card head -->
                    <div class="ws-card-head">
                        <div class="ws-avatar">
                            <img v-if="ws.logo_path" :src="`/storage/${ws.logo_path}`" class="ws-avatar-img" />
                            <span v-else>{{ ws.name.charAt(0) }}</span>
                        </div>
                        <div class="ws-card-info">
                            <h3 class="ws-card-name">{{ ws.name }}</h3>
                            <p class="ws-card-type">{{ ws.business_type ?? 'غير محدد' }}</p>
                        </div>
                    </div>

                    <!-- Dialect badge -->
                    <div class="ws-dialect">
                        {{ dialects[ws.default_dialect] ?? ws.default_dialect }}
                    </div>

                    <!-- Actions -->
                    <div class="ws-card-actions">
                        <button
                            class="ws-btn-switch"
                            :class="{ 'ws-btn-switch--active': ws.id === currentWorkspaceId }"
                            :disabled="ws.id === currentWorkspaceId || switching === ws.id"
                            @click="switchWorkspace(ws.id)"
                        >
                            <svg v-if="ws.id === currentWorkspaceId" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <svg v-else-if="switching === ws.id" class="spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ ws.id === currentWorkspaceId ? 'مفعّلة' : (switching === ws.id ? 'جارٍ التبديل…' : 'تفعيل') }}
                        </button>

                        <a :href="`/workspaces/${ws.id}/settings`" class="ws-btn-settings">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            إعدادات
                        </a>

                        <button
                            v-if="ws.id !== currentWorkspaceId"
                            class="ws-btn-archive"
                            title="أرشفة"
                            @click="openArchive(ws.id, ws.name)"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Archive confirm modal ── -->
        <Modal :show="showArchive" title="تأكيد الأرشفة" size="sm" @close="cancelArchive">
            <div class="archive-confirm">
                <div class="archive-confirm-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                </div>
                <p class="archive-confirm-msg">
                    هل تريد أرشفة مساحة العمل
                    <strong>{{ archiveTarget?.name }}</strong>؟
                    لن تظهر في قائمة المساحات النشطة.
                </p>
            </div>
            <template #footer>
                <button class="btn btn-danger" @click="confirmArchive">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                    أرشفة المساحة
                </button>
                <button class="btn btn-secondary" @click="cancelArchive">إلغاء</button>
            </template>
        </Modal>

        <!-- ── Create modal ── -->
        <Modal :show="showCreate" title="إنشاء مساحة عمل جديدة" size="md" @close="closeModal">
            <form @submit.prevent="submit" class="create-form">
                <Input
                    v-model="form.name"
                    label="اسم المساحة"
                    placeholder="مثلاً: متجر أنيق"
                    :error="form.errors.name"
                />
                <Input
                    v-model="form.business_type"
                    label="نوع النشاط (اختياري)"
                    placeholder="مثلاً: تجارة إلكترونية"
                />

                <!-- Countries -->
                <div>
                    <p class="field-label">الدول المستهدفة</p>
                    <div class="country-chips">
                        <button
                            v-for="c in countryOptions"
                            :key="c.code"
                            type="button"
                            class="country-chip"
                            :class="{ 'country-chip--on': form.countries.includes(c.code) }"
                            @click="toggleCountry(c.code)"
                        >{{ c.label }}</button>
                    </div>
                </div>

                <!-- Dialect -->
                <div>
                    <label class="field-label">اللهجة الافتراضية</label>
                    <select v-model="form.default_dialect" class="field-select">
                        <option v-for="(label, code) in dialects" :key="code" :value="code">{{ label }}</option>
                    </select>
                </div>
            </form>

            <template #footer>
                <Button :loading="form.processing" @click="submit">إنشاء المساحة</Button>
                <Button variant="ghost" @click="closeModal">إلغاء</Button>
            </template>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* ── Page shell ── */
.ws-page { max-width: 960px; margin: 0 auto; }

.ws-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 16px;
}
.ws-title { margin: 0; font-size: 22px; font-weight: 700; color: var(--text-primary); }
.ws-sub   { margin: 4px 0 0; font-size: 13px; color: var(--text-muted); }

/* ── Empty ── */
.ws-empty {
    text-align: center;
    padding: 80px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.ws-empty-icon  { font-size: 48px; margin-bottom: 4px; }
.ws-empty-title { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0; }
.ws-empty-desc  { font-size: 13px; color: var(--text-muted); margin: 0 0 12px; }

/* ── Grid ── */
.ws-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

/* ── Card ── */
.ws-card {
    position: relative;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 20px;
    transition: box-shadow .2s, border-color .2s;
}
.ws-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    border-color: var(--border-default);
}
.ws-card--active {
    border-color: var(--sada-500);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--sada-500) 12%, transparent);
}

/* ── Active ribbon ── */
.ws-active-ribbon {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: absolute;
    top: 12px;
    left: 12px;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-600);
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 99px;
}
.ws-active-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--sada-500);
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .5; transform: scale(1.3); }
}

/* ── Card head ── */
.ws-card-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
    margin-top: 8px; /* space under the ribbon */
}
.ws-card--active .ws-card-head { margin-top: 24px; }

.ws-avatar {
    width: 46px; height: 46px;
    border-radius: 12px;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--sada-500), var(--sada-600));
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    display: grid;
    place-items: center;
    overflow: hidden;
}
.ws-avatar-img { width: 100%; height: 100%; object-fit: cover; }

.ws-card-info { flex: 1; min-width: 0; }
.ws-card-name {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ws-card-type {
    margin: 3px 0 0;
    font-size: 12px;
    color: var(--text-muted);
}

/* ── Dialect badge ── */
.ws-dialect {
    display: inline-block;
    background: var(--bg-muted);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 99px;
    margin-bottom: 16px;
}

/* ── Actions row ── */
.ws-card-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.ws-btn-switch {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--sada-500);
    background: var(--sada-500);
    color: #fff;
    transition: background .15s, opacity .15s;
    font-family: var(--font-arabic);
}
.ws-btn-switch:hover:not(:disabled) { background: var(--sada-600); border-color: var(--sada-600); }
.ws-btn-switch--active {
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    border-color: var(--sada-500);
    color: var(--sada-600);
    cursor: default;
}
.ws-btn-switch:disabled { opacity: .7; }

.ws-btn-settings {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid var(--border-default);
    color: var(--text-muted);
    text-decoration: none;
    background: transparent;
    transition: background .15s, color .15s;
    font-family: var(--font-arabic);
    white-space: nowrap;
}
.ws-btn-settings:hover { background: var(--bg-muted); color: var(--text-primary); }

.ws-btn-archive {
    padding: 7px;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition: background .15s, color .15s;
    display: grid;
    place-items: center;
}
.ws-btn-archive:hover { background: var(--error-bg); color: var(--error); border-color: var(--error); }

/* ── Spinner ── */
.spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Create form ── */
.create-form { display: flex; flex-direction: column; gap: 18px; }

.field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 8px;
}

.country-chips { display: flex; gap: 8px; flex-wrap: wrap; }

.country-chip {
    padding: 5px 12px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid var(--border-default);
    background: transparent;
    color: var(--text-muted);
    transition: all .15s;
    font-family: var(--font-arabic);
}
.country-chip:hover { border-color: var(--sada-500); color: var(--sada-600); }
.country-chip--on {
    border-color: var(--sada-500);
    background: color-mix(in oklab, var(--sada-500) 10%, transparent);
    color: var(--sada-600);
}

.field-select {
    width: 100%;
    padding: 9px 12px;
    font-size: 14px;
    border-radius: 8px;
    border: 1.5px solid var(--border-default);
    background: var(--bg-surface);
    color: var(--text-primary);
    font-family: var(--font-arabic);
    outline: none;
    transition: border-color .15s;
}
.field-select:focus { border-color: var(--sada-500); }

/* ── Archive confirm ── */
.archive-confirm {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
    padding: 8px 0 4px;
}
.archive-confirm-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: var(--error-bg);
    color: var(--error);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.archive-confirm-msg {
    margin: 0;
    font-size: 14px;
    color: var(--text-primary);
    line-height: 1.7;
}
</style>

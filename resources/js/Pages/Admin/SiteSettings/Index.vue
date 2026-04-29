<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import { useUiStore } from '@/Stores/ui'

const ui = useUiStore()

interface SettingItem {
    key: string
    value: string | boolean | null
    type: string
    label_ar: string
    is_public: boolean
}

type Groups = Record<string, SettingItem[]>

const props = defineProps<{ groups: Groups }>()

// ── Active tab ─────────────────────────────────────────────
const GROUP_META: Record<string, { label: string, icon: string }> = {
    general:  { label: 'عام',             icon: 'home'        },
    branding: { label: 'الهوية البصرية',  icon: 'image'       },
    contact:  { label: 'التواصل',         icon: 'phone'       },
    seo:      { label: 'SEO',             icon: 'globe'       },
    social:   { label: 'السوشيال',        icon: 'instagram'   },
}

const groupKeys = computed(() => Object.keys(props.groups).filter(g => GROUP_META[g]))
const activeTab = ref(groupKeys.value[0] ?? 'general')

// ── Editable state: flat key → string|null ─────────────────
const form = reactive<Record<string, string>>({})

// Initialize form values from props
Object.values(props.groups).forEach(items => {
    items.forEach(item => {
        form[item.key] = item.type === 'bool'
            ? (item.value ? '1' : '0')
            : ((item.value as string | null) ?? '')
    })
})

// ── Image upload state ─────────────────────────────────────
const imagePreview = reactive<Record<string, string>>({})
const imageUploading = reactive<Record<string, boolean>>({})

// Init image previews from existing values
Object.values(props.groups).forEach(items => {
    items.forEach(item => {
        if (item.type === 'image' && item.value) {
            imagePreview[item.key] = item.value as string
        }
    })
})

async function uploadImage(key: string, event: Event) {
    const input = event.target as HTMLInputElement
    const file  = input.files?.[0]
    if (!file) return

    imageUploading[key] = true
    const fd = new FormData()
    fd.append('image', file)
    fd.append('_token', (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '')

    try {
        const res = await fetch(`/admin/site-settings/image/${key}`, { method: 'POST', body: fd })
        const data = await res.json()
        if (data.url) {
            imagePreview[key] = data.url
            form[key]         = data.url
        }
    } catch {
        ui.error('فشل رفع الصورة.')
    } finally {
        imageUploading[key] = false
        input.value = ''
    }
}

// ── Save ───────────────────────────────────────────────────
const saving = ref(false)

function save() {
    saving.value = true
    router.post('/admin/site-settings', { settings: form }, {
        onFinish: () => { saving.value = false },
    })
}

// ── Copy to clipboard helper ───────────────────────────────
const copiedKey = ref('')
function copyVal(key: string) {
    navigator.clipboard?.writeText(form[key] ?? '')
    copiedKey.value = key
    setTimeout(() => { copiedKey.value = '' }, 1500)
}
</script>

<template>
    <AdminLayout title="إعدادات الموقع">

        <div class="page-head">
            <div>
                <h1 class="page-title">إعدادات الموقع</h1>
                <p class="page-sub">كل إعدادات المنصة تُقرأ من هنا ديناميكياً</p>
            </div>
            <button class="btn btn-primary" :disabled="saving" @click="save">
                <Icon name="check" :size="15" />
                {{ saving ? 'جارٍ الحفظ...' : 'حفظ جميع الإعدادات' }}
            </button>
        </div>

        <div class="ss-layout">

            <!-- Tab sidebar -->
            <nav class="ss-tabs">
                <button
                    v-for="gk in groupKeys"
                    :key="gk"
                    :class="['ss-tab', { 'ss-tab--active': activeTab === gk }]"
                    @click="activeTab = gk"
                >
                    <Icon :name="GROUP_META[gk]?.icon ?? 'settings'" :size="16" />
                    {{ GROUP_META[gk]?.label ?? gk }}
                    <span class="ss-tab-count">{{ groups[gk]?.length ?? 0 }}</span>
                </button>
            </nav>

            <!-- Settings panel -->
            <div class="ss-panel card">
                <div class="ss-panel-head">
                    <Icon :name="GROUP_META[activeTab]?.icon ?? 'settings'" :size="18" style="color:var(--accent)" />
                    <h2>{{ GROUP_META[activeTab]?.label ?? activeTab }}</h2>
                </div>

                <div class="ss-fields">
                    <template v-for="item in (groups[activeTab] ?? [])" :key="item.key">

                        <!-- IMAGE type -->
                        <div v-if="item.type === 'image'" class="ss-field">
                            <label class="ss-label">
                                {{ item.label_ar }}
                                <span class="ss-key">{{ item.key }}</span>
                            </label>
                            <div class="image-upload-row">
                                <div class="image-preview-box">
                                    <img
                                        v-if="imagePreview[item.key]"
                                        :src="imagePreview[item.key]"
                                        class="image-preview"
                                        :alt="item.label_ar"
                                    />
                                    <div v-else class="image-placeholder">
                                        <Icon name="image" :size="24" style="color:var(--text-faint)" />
                                        <span>لا توجد صورة</span>
                                    </div>
                                </div>
                                <div class="image-upload-actions">
                                    <label class="btn btn-sm btn-secondary" :class="{ 'opacity-50': imageUploading[item.key] }">
                                        <Icon name="arrowLeft" :size="14" style="transform:rotate(270deg)" />
                                        {{ imageUploading[item.key] ? 'جارٍ الرفع...' : 'رفع صورة' }}
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/svg+xml,image/webp"
                                            style="display:none"
                                            :disabled="imageUploading[item.key]"
                                            @change="uploadImage(item.key, $event)"
                                        />
                                    </label>
                                    <button
                                        v-if="imagePreview[item.key]"
                                        class="btn btn-sm btn-ghost"
                                        style="color:var(--danger)"
                                        @click="imagePreview[item.key] = ''; form[item.key] = ''"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </div>
                            <div class="ss-hint">PNG أو SVG أو WebP · الحد الأقصى 2MB</div>
                        </div>

                        <!-- BOOL type -->
                        <div v-else-if="item.type === 'bool'" class="ss-field ss-field--inline">
                            <div>
                                <div class="ss-label">
                                    {{ item.label_ar }}
                                    <span class="ss-key">{{ item.key }}</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="toggle"
                                :data-on="form[item.key] === '1' ? 'true' : 'false'"
                                @click="form[item.key] = form[item.key] === '1' ? '0' : '1'"
                            />
                        </div>

                        <!-- TEXT (textarea) type -->
                        <div v-else-if="item.type === 'text'" class="ss-field">
                            <label class="ss-label" :for="`field-${item.key}`">
                                {{ item.label_ar }}
                                <span class="ss-key">{{ item.key }}</span>
                                <span v-if="!item.is_public" class="ss-private-badge">خاص</span>
                            </label>
                            <textarea
                                :id="`field-${item.key}`"
                                v-model="form[item.key]"
                                class="textarea"
                                rows="3"
                            />
                        </div>

                        <!-- URL / EMAIL / PHONE / STRING types -->
                        <div v-else class="ss-field">
                            <label class="ss-label" :for="`field-${item.key}`">
                                {{ item.label_ar }}
                                <span class="ss-key">{{ item.key }}</span>
                                <span v-if="!item.is_public" class="ss-private-badge">خاص</span>
                            </label>
                            <div class="input-with-copy">
                                <input
                                    :id="`field-${item.key}`"
                                    v-model="form[item.key]"
                                    class="input"
                                    :type="item.type === 'email' ? 'email' : item.type === 'url' ? 'url' : 'text'"
                                    :placeholder="item.type === 'url' ? 'https://' : item.type === 'email' ? 'example@domain.com' : item.type === 'phone' ? '+966 5x xxx xxxx' : ''"
                                    :dir="item.type === 'url' || item.type === 'email' ? 'ltr' : 'rtl'"
                                />
                                <button
                                    v-if="form[item.key]"
                                    class="btn btn-icon btn-ghost btn-sm copy-btn"
                                    :title="copiedKey === item.key ? 'تم النسخ!' : 'نسخ'"
                                    @click="copyVal(item.key)"
                                >
                                    <Icon :name="copiedKey === item.key ? 'check' : 'copy'" :size="13" />
                                </button>
                            </div>
                        </div>

                    </template>
                </div>

                <!-- Panel footer -->
                <div class="ss-panel-foot">
                    <button class="btn btn-primary" :disabled="saving" @click="save">
                        <Icon name="check" :size="14" />
                        {{ saving ? 'جارٍ الحفظ...' : 'حفظ' }}
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--text-muted); margin: 0; }

/* Layout */
.ss-layout {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 768px) { .ss-layout { grid-template-columns: 1fr; } }

/* Tab sidebar */
.ss-tabs {
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 8px;
    position: sticky;
    top: 80px;
}
.ss-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: right;
    transition: all var(--dur-fast);
}
.ss-tab:hover { background: var(--bg-muted); color: var(--text-primary); }
.ss-tab--active {
    background: var(--accent-soft);
    color: var(--accent-text);
    font-weight: 600;
}
.ss-tab-count {
    margin-right: auto;
    font-size: 11px;
    font-weight: 600;
    background: var(--bg-muted);
    color: var(--text-muted);
    padding: 1px 6px;
    border-radius: var(--radius-full);
}
.ss-tab--active .ss-tab-count {
    background: rgba(255,255,255,.3);
    color: var(--accent-text);
}

/* Panel */
.ss-panel { padding: 0; overflow: hidden; }

.ss-panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-subtle);
}
.ss-panel-head h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.ss-fields {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.ss-field { display: flex; flex-direction: column; gap: 6px; }
.ss-field--inline {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-subtle);
}
.ss-field--inline:last-child { border-bottom: none; }

.ss-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
.ss-key {
    font-size: 11px;
    font-weight: 400;
    color: var(--text-muted);
    font-family: monospace;
    background: var(--bg-muted);
    padding: 1px 6px;
    border-radius: 4px;
}
.ss-private-badge {
    font-size: 10px;
    font-weight: 600;
    color: #92400E;
    background: #FEF3C7;
    padding: 1px 6px;
    border-radius: var(--radius-full);
}
[data-theme="dark"] .ss-private-badge { background: rgba(146,64,14,.2); color: #FCD34D; }

.ss-hint { font-size: 11px; color: var(--text-muted); }

/* Input with copy button */
.input-with-copy {
    position: relative;
    display: flex;
    align-items: center;
}
.input-with-copy .input { flex: 1; padding-left: 36px; }
.copy-btn {
    position: absolute;
    left: 6px;
    color: var(--text-muted);
}

/* Image upload */
.image-upload-row {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}
.image-preview-box {
    width: 120px;
    height: 80px;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--bg-muted);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.image-preview { width: 100%; height: 100%; object-fit: contain; }
.image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--text-muted);
}
.image-upload-actions { display: flex; flex-direction: column; gap: 8px; }

/* Panel footer */
.ss-panel-foot {
    padding: 16px 24px;
    border-top: 1px solid var(--border-subtle);
    display: flex;
    justify-content: flex-end;
    background: var(--bg-muted);
}
</style>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import { useConfirmStore } from '@/Stores/confirm'

const confirmStore = useConfirmStore()

interface Template {
    id: number
    platform: 'instagram' | 'facebook' | 'tiktok' | 'snapchat' | 'x' | 'all'
    content_template: string
    hashtags: string[]
    tone: string | null
    active: boolean
    sort_order: number
}

interface OccasionMeta {
    id: number
    key: string
    name: string
    icon: string
}

const props = defineProps<{
    occasion: OccasionMeta
    templates: Template[]
}>()

const PLATFORM_LABELS: Record<string, string> = {
    all: 'كل المنصات', instagram: 'انستجرام', facebook: 'فيسبوك',
    tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X (تويتر)',
}
const PLATFORM_COLORS: Record<string, string> = {
    all: '#5E6A64', instagram: '#E1306C', facebook: '#1877F2',
    tiktok: '#000', snapchat: '#FFFC00', x: '#000',
}

const showModal   = ref(false)
const editTarget  = ref<Template | null>(null)
const saving      = ref(false)

const defaultForm = () => ({
    platform: 'all' as Template['platform'],
    content_template: '',
    hashtags_raw: '',
    tone: '',
    sort_order: 0,
})
const form = ref(defaultForm())

function openAdd() {
    form.value = defaultForm()
    editTarget.value = null
    showModal.value = true
}

function openEdit(t: Template) {
    form.value = {
        platform: t.platform,
        content_template: t.content_template,
        hashtags_raw: (t.hashtags ?? []).join('\n'),
        tone: t.tone ?? '',
        sort_order: t.sort_order,
    }
    editTarget.value = t
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editTarget.value = null
}

function submit() {
    saving.value = true
    const payload = {
        ...form.value,
        hashtags: form.value.hashtags_raw.split(/[\n,]+/).map(h => h.trim()).filter(Boolean),
    }
    const base = `/admin/seasonal/${props.occasion.id}/templates`
    if (editTarget.value) {
        router.patch(`${base}/${editTarget.value.id}`, payload, {
            onFinish: () => { saving.value = false; closeModal() },
        })
    } else {
        router.post(base, payload, {
            onFinish: () => { saving.value = false; closeModal() },
        })
    }
}

async function deleteTemplate(t: Template) {
    const ok = await confirmStore.ask({ title: 'حذف هذا القالب؟', confirmText: 'حذف', dangerous: true })
    if (!ok) return
    router.delete(`/admin/seasonal/${props.occasion.id}/templates/${t.id}`)
}
</script>

<template>
    <AdminLayout>
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <Link href="/admin/seasonal" class="bc-link">المناسبات الموسمية</Link>
            <Icon name="chevron-left" :size="14" style="color:var(--text-muted);transform:scaleX(-1)" />
            <span>{{ occasion.name }}</span>
        </div>

        <div class="page-header">
            <div>
                <h1 class="page-title">قوالب المحتوى — {{ occasion.name }}</h1>
                <p class="page-subtitle">{{ templates.length }} قالب محتوى لهذه المناسبة</p>
            </div>
            <button class="btn btn-primary" @click="openAdd">
                <Icon name="plus" :size="16" />
                إضافة قالب
            </button>
        </div>

        <!-- Templates grid -->
        <div v-if="templates.length" class="templates-grid">
            <div v-for="t in templates" :key="t.id" class="template-card">
                <div class="tc-header">
                    <span class="tc-platform" :style="{ color: PLATFORM_COLORS[t.platform] ?? '#5E6A64' }">
                        {{ PLATFORM_LABELS[t.platform] ?? t.platform }}
                    </span>
                    <span v-if="t.tone" class="tc-tone">{{ t.tone }}</span>
                    <div class="tc-actions">
                        <button class="btn btn-sm btn-ghost" @click="openEdit(t)" title="تعديل">
                            <Icon name="edit" :size="13" />
                        </button>
                        <button class="btn btn-sm btn-ghost btn-danger" @click="deleteTemplate(t)" title="حذف">
                            <Icon name="trash" :size="13" />
                        </button>
                    </div>
                </div>
                <div class="tc-content">{{ t.content_template }}</div>
                <div v-if="t.hashtags?.length" class="tc-hashtags">
                    <span v-for="h in t.hashtags" :key="h" class="tc-hash">{{ h }}</span>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <Icon name="sparkle" :size="40" style="color:var(--text-muted);opacity:.4;margin-bottom:12px" />
            <p>لا توجد قوالب لهذه المناسبة بعد</p>
            <button class="btn btn-primary" style="margin-top:12px" @click="openAdd">إضافة أول قالب</button>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-panel">
                <div class="modal-header">
                    <h2>{{ editTarget ? 'تعديل القالب' : 'إضافة قالب جديد' }}</h2>
                    <button class="btn btn-icon btn-ghost" @click="closeModal">
                        <Icon name="x" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submit" class="modal-body">
                    <div class="form-group">
                        <label class="form-label">المنصة *</label>
                        <select v-model="form.platform" class="input">
                            <option v-for="(label, val) in PLATFORM_LABELS" :key="val" :value="val">{{ label }}</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:12px">
                        <label class="form-label">نص القالب *</label>
                        <textarea v-model="form.content_template" class="input" rows="6" required placeholder="اكتب نص المنشور هنا... يمكنك استخدام {business_name} و {product} كمتغيرات" />
                        <div class="form-hint">المتغيرات المتاحة: {business_name} · {product} · {offer}</div>
                    </div>
                    <div class="form-group" style="margin-top:12px">
                        <label class="form-label">الهاشتاقات (سطر لكل هاشتاق)</label>
                        <textarea v-model="form.hashtags_raw" class="input" rows="3" dir="ltr" placeholder="#رمضان&#10;#رمضان_كريم" />
                    </div>
                    <div class="form-row" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">الأسلوب (tone)</label>
                            <input v-model="form.tone" type="text" class="input" placeholder="formal / casual / promotional" dir="ltr" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">الترتيب</label>
                            <input v-model.number="form.sort_order" type="number" class="input" min="0" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" @click="closeModal">إلغاء</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'جارٍ الحفظ...' : (editTarget ? 'حفظ التعديلات' : 'إضافة القالب') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
.bc-link    { color: var(--sada-500); text-decoration: none; }
.bc-link:hover { text-decoration: underline; }

.page-header   { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; gap: 16px; }
.page-title    { font-size: 20px; font-weight: 700; margin: 0 0 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); margin: 0; }

.templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.template-card  { background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 16px; display: flex; flex-direction: column; gap: 12px; }

.tc-header   { display: flex; align-items: center; gap: 8px; }
.tc-platform { font-weight: 700; font-size: 13px; }
.tc-tone     { font-size: 11px; padding: 2px 8px; border-radius: var(--radius-full); background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-default); }
.tc-actions  { margin-right: auto; display: flex; gap: 4px; }
.tc-content  { font-size: 13px; line-height: 1.7; color: var(--text-primary); white-space: pre-wrap; background: var(--bg-subtle); border-radius: var(--radius-sm); padding: 10px 12px; }
.tc-hashtags { display: flex; flex-wrap: wrap; gap: 4px; }
.tc-hash     { font-size: 11px; color: var(--sada-500); background: var(--sada-50, #e8f5f1); padding: 2px 8px; border-radius: var(--radius-full); }

.empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.empty-state p { margin: 0; font-size: 14px; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 24px; }
.modal-panel   { background: var(--bg-surface); border-radius: var(--radius-lg); width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 90vh; overflow-y: auto; }
.modal-header  { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid var(--border-default); }
.modal-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
.modal-body    { padding: 20px 24px; }
.modal-footer  { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 0 0; margin-top: 8px; }

.form-group  { display: flex; flex-direction: column; gap: 6px; }
.form-label  { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.form-hint   { font-size: 11px; color: var(--text-muted); }

.btn-danger:hover { color: #dc2626; }
</style>

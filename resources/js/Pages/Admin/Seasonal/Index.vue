<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Occasion {
    id: number
    key: string
    name: string
    subtitle: string | null
    date: string
    end_date: string | null
    icon: string
    color: string
    countries: string[]
    featured: boolean
    hashtags: string[]
    is_recurring: boolean
    type: 'islamic' | 'national' | 'commercial'
    active: boolean
    sort_order: number
    templates_count: number
}

const props = defineProps<{ occasions: Occasion[] }>()

const typeFilter  = ref<'all' | 'islamic' | 'national' | 'commercial'>('all')
const searchQuery = ref('')
const showAdd     = ref(false)
const editTarget  = ref<Occasion | null>(null)

const COUNTRIES: Record<string, string> = {
    sa: 'السعودية', ae: 'الإمارات', kw: 'الكويت',
    qa: 'قطر',      bh: 'البحرين', om: 'عُمان',
}
const TYPES: Record<string, string> = {
    islamic: 'إسلامية', national: 'وطنية', commercial: 'تجارية / اجتماعية',
}
const TYPE_COLORS: Record<string, string> = {
    islamic: 'badge-green', national: 'badge-blue', commercial: 'badge-sand',
}
const PLATFORM_OPTIONS = [
    { value: 'all',       label: 'كل المنصات' },
    { value: 'instagram', label: 'انستجرام' },
    { value: 'facebook',  label: 'فيسبوك' },
    { value: 'tiktok',    label: 'تيك توك' },
    { value: 'snapchat',  label: 'سناب شات' },
    { value: 'x',         label: 'X (تويتر)' },
]

// ── Form state ────────────────────────────────────────────────────────────────
const defaultForm = () => ({
    key: '', name: '', subtitle: '', date: '', end_date: '',
    icon: 'star', color: '#0F6F5C',
    countries: [] as string[], featured: false,
    hashtags_raw: '', is_recurring: true,
    type: 'commercial' as 'islamic' | 'national' | 'commercial',
    sort_order: 0,
})
const form = ref(defaultForm())
const saving = ref(false)

const filtered = computed(() => {
    let list = props.occasions
    if (typeFilter.value !== 'all') list = list.filter(o => o.type === typeFilter.value)
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim().toLowerCase()
        list = list.filter(o => o.name.includes(q) || o.key.includes(q))
    }
    return list
})

const kpi = computed(() => ({
    total:    props.occasions.length,
    active:   props.occasions.filter(o => o.active).length,
    featured: props.occasions.filter(o => o.featured).length,
    templates: props.occasions.reduce((s, o) => s + o.templates_count, 0),
}))

function openAdd() {
    form.value = defaultForm()
    editTarget.value = null
    showAdd.value = true
}

function openEdit(o: Occasion) {
    form.value = {
        key: o.key, name: o.name, subtitle: o.subtitle ?? '',
        date: o.date, end_date: o.end_date ?? '',
        icon: o.icon, color: o.color,
        countries: [...o.countries], featured: o.featured,
        hashtags_raw: (o.hashtags ?? []).join('\n'),
        is_recurring: o.is_recurring, type: o.type, sort_order: o.sort_order,
    }
    editTarget.value = o
    showAdd.value = true
}

function closeModal() {
    showAdd.value = false
    editTarget.value = null
}

function toggleCountry(c: string) {
    const idx = form.value.countries.indexOf(c)
    if (idx >= 0) form.value.countries.splice(idx, 1)
    else form.value.countries.push(c)
}

function submitForm() {
    saving.value = true
    const payload = {
        ...form.value,
        hashtags: form.value.hashtags_raw
            .split(/[\n,]+/)
            .map(h => h.trim())
            .filter(Boolean),
    }
    if (editTarget.value) {
        router.patch(`/admin/seasonal/${editTarget.value.id}`, payload, {
            onFinish: () => { saving.value = false; closeModal() },
        })
    } else {
        router.post('/admin/seasonal', payload, {
            onFinish: () => { saving.value = false; closeModal() },
        })
    }
}

function toggleActive(o: Occasion) {
    router.post(`/admin/seasonal/${o.id}/toggle`)
}

function deleteOccasion(o: Occasion) {
    if (confirm(`حذف مناسبة "${o.name}"؟ سيتم حذف جميع القوالب المرتبطة بها.`)) {
        router.delete(`/admin/seasonal/${o.id}`)
    }
}
</script>

<template>
    <AdminLayout>
        <div class="page-header">
            <div>
                <h1 class="page-title">المناسبات الموسمية</h1>
                <p class="page-subtitle">إدارة {{ occasions.length }} مناسبة خليجية وإسلامية وتجارية</p>
            </div>
            <button class="btn btn-primary" @click="openAdd">
                <Icon name="plus" :size="16" />
                إضافة مناسبة
            </button>
        </div>

        <!-- KPI cards -->
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-value">{{ kpi.total }}</div>
                <div class="kpi-label">إجمالي المناسبات</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value text-green">{{ kpi.active }}</div>
                <div class="kpi-label">مفعّلة</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value text-sand">{{ kpi.featured }}</div>
                <div class="kpi-label">مميّزة</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value">{{ kpi.templates }}</div>
                <div class="kpi-label">قوالب محتوى</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
            <input
                v-model="searchQuery"
                type="text"
                class="input"
                placeholder="بحث في المناسبات..."
                style="width:220px"
            />
            <div class="segmented">
                <button
                    v-for="t in (['all','islamic','national','commercial'] as const)"
                    :key="t"
                    :class="['seg-btn', { active: typeFilter === t }]"
                    @click="typeFilter = t"
                >
                    {{ t === 'all' ? 'الكل' : TYPES[t] }}
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="card" style="padding:0;overflow:hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المناسبة</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>الدول</th>
                        <th>القوالب</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in filtered" :key="o.id">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="occ-icon" :style="{ background: o.color + '22', color: o.color }">
                                    <Icon :name="o.icon" :size="16" />
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:14px">{{ o.name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ o.key }}</div>
                                </div>
                                <span v-if="o.featured" class="badge badge-sand" style="font-size:10px">مميّزة</span>
                            </div>
                        </td>
                        <td><span :class="['badge', TYPE_COLORS[o.type]]">{{ TYPES[o.type] }}</span></td>
                        <td style="font-size:13px;white-space:nowrap">
                            {{ o.date }}
                            <span v-if="o.end_date" style="color:var(--text-muted)"> → {{ o.end_date }}</span>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                <span v-for="c in o.countries" :key="c" class="country-chip">{{ c.toUpperCase() }}</span>
                            </div>
                        </td>
                        <td>
                            <a :href="`/admin/seasonal/${o.id}/templates`" class="link-action">
                                {{ o.templates_count }} قالب
                            </a>
                        </td>
                        <td>
                            <button
                                :class="['toggle-btn', o.active ? 'on' : 'off']"
                                @click="toggleActive(o)"
                                :title="o.active ? 'إيقاف' : 'تفعيل'"
                            >
                                <span class="toggle-knob" />
                            </button>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button class="btn btn-sm btn-ghost" @click="openEdit(o)" title="تعديل">
                                    <Icon name="edit" :size="14" />
                                </button>
                                <button class="btn btn-sm btn-ghost btn-danger" @click="deleteOccasion(o)" title="حذف">
                                    <Icon name="trash" :size="14" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            لا توجد مناسبات مطابقة
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add / Edit Modal -->
        <div v-if="showAdd" class="modal-overlay" @click.self="closeModal">
            <div class="modal-panel" style="max-width:600px">
                <div class="modal-header">
                    <h2>{{ editTarget ? 'تعديل المناسبة' : 'إضافة مناسبة جديدة' }}</h2>
                    <button class="btn btn-icon btn-ghost" @click="closeModal">
                        <Icon name="x" :size="18" />
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group" v-if="!editTarget">
                            <label class="form-label">المفتاح (key) *</label>
                            <input v-model="form.key" type="text" class="input" placeholder="ramadan" dir="ltr" required />
                            <div class="form-hint">أحرف إنجليزية صغيرة وشرطة سفلية فقط</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">اسم المناسبة *</label>
                            <input v-model="form.name" type="text" class="input" placeholder="رمضان المبارك" required />
                        </div>
                        <div class="form-group" style="grid-column:span 2">
                            <label class="form-label">الوصف المختصر</label>
                            <input v-model="form.subtitle" type="text" class="input" placeholder="شهر القرآن والعطاء" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">تاريخ البداية *</label>
                            <input v-model="form.date" type="date" class="input" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">تاريخ النهاية</label>
                            <input v-model="form.end_date" type="date" class="input" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">الأيقونة</label>
                            <select v-model="form.icon" class="input">
                                <option v-for="ic in ['star','moon','crown','heart','flash','sparkle']" :key="ic" :value="ic">{{ ic }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">اللون</label>
                            <div style="display:flex;align-items:center;gap:8px">
                                <input v-model="form.color" type="color" style="width:44px;height:38px;border-radius:8px;border:1px solid var(--border-default);cursor:pointer" />
                                <input v-model="form.color" type="text" class="input" dir="ltr" placeholder="#0F6F5C" style="flex:1" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">النوع *</label>
                            <select v-model="form.type" class="input">
                                <option value="islamic">إسلامية</option>
                                <option value="national">وطنية</option>
                                <option value="commercial">تجارية / اجتماعية</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">الترتيب</label>
                            <input v-model.number="form.sort_order" type="number" class="input" min="0" />
                        </div>
                        <div class="form-group" style="grid-column:span 2">
                            <label class="form-label">الدول</label>
                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button
                                    v-for="(label, code) in COUNTRIES"
                                    :key="code"
                                    type="button"
                                    :class="['country-toggle', { selected: form.countries.includes(code) }]"
                                    @click="toggleCountry(code)"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                        <div class="form-group" style="grid-column:span 2">
                            <label class="form-label">الهاشتاقات (سطر لكل هاشتاق)</label>
                            <textarea v-model="form.hashtags_raw" class="input" rows="3" dir="ltr" placeholder="#رمضان&#10;#رمضان_كريم" />
                        </div>
                        <div class="form-group" style="display:flex;gap:24px;align-items:center">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                <input v-model="form.featured" type="checkbox" class="checkbox" />
                                <span>مميّزة (featured)</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                <input v-model="form.is_recurring" type="checkbox" class="checkbox" />
                                <span>متكررة سنوياً</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" @click="closeModal">إلغاء</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'جارٍ الحفظ...' : (editTarget ? 'حفظ التعديلات' : 'إضافة المناسبة') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
}
.page-title   { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); margin: 0; }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card { background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 18px 20px; }
.kpi-value { font-size: 26px; font-weight: 700; color: var(--text-primary); }
.kpi-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
.text-green { color: #16a34a; }
.text-sand  { color: var(--sand-500); }

.filters-bar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.segmented   { display: flex; background: var(--bg-subtle); border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border-default); }
.seg-btn     { padding: 7px 14px; font-size: 13px; background: transparent; color: var(--text-muted); border: none; cursor: pointer; transition: all 0.15s; }
.seg-btn.active { background: var(--sada-500); color: #fff; font-weight: 600; }

.data-table  { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 12px 16px; background: var(--bg-subtle); color: var(--text-muted); font-weight: 600; font-size: 12px; text-align: right; border-bottom: 1px solid var(--border-default); }
.data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-default); color: var(--text-primary); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--bg-subtle); }

.occ-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.badge { display: inline-flex; padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; }
.badge-green { background: #dcfce7; color: #15803d; }
.badge-blue  { background: #dbeafe; color: #1d4ed8; }
.badge-sand  { background: var(--sand-100, #fdf5ec); color: var(--sand-700, #a0714a); }

.country-chip { display: inline-block; padding: 1px 6px; background: var(--bg-subtle); border: 1px solid var(--border-default); border-radius: 4px; font-size: 10px; font-weight: 600; color: var(--text-muted); }

.link-action { color: var(--sada-500); font-weight: 600; text-decoration: none; }
.link-action:hover { text-decoration: underline; }

/* Toggle */
.toggle-btn { display: inline-flex; width: 38px; height: 22px; border-radius: 11px; padding: 2px; border: none; cursor: pointer; transition: background 0.2s; position: relative; }
.toggle-btn.on  { background: var(--sada-500); }
.toggle-btn.off { background: var(--border-default); }
.toggle-knob { position: absolute; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #fff; transition: right 0.2s; }
.toggle-btn.on  .toggle-knob { right: 2px; }
.toggle-btn.off .toggle-knob { right: 18px; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 24px; }
.modal-panel   { background: var(--bg-surface); border-radius: var(--radius-lg); width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 90vh; overflow-y: auto; }
.modal-header  { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid var(--border-default); }
.modal-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
.modal-body    { padding: 20px 24px; }
.modal-footer  { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid var(--border-default); margin-top: 8px; }

.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group  { display: flex; flex-direction: column; gap: 6px; }
.form-label  { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.form-hint   { font-size: 11px; color: var(--text-muted); }
.checkbox    { width: 16px; height: 16px; cursor: pointer; accent-color: var(--sada-500); }

.country-toggle { padding: 6px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; border: 1px solid var(--border-default); background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.15s; }
.country-toggle.selected { background: var(--sada-500); color: #fff; border-color: var(--sada-500); }

.btn-danger:hover { color: #dc2626; }

@media (max-width: 768px) {
    .kpi-row { grid-template-columns: repeat(2, 1fr); }
    .form-grid-2 { grid-template-columns: 1fr; }
    .form-grid-2 .form-group[style*="span 2"] { grid-column: span 1; }
}
</style>

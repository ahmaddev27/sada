<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Flag {
    key: string
    label: string
    enabled: boolean
}

interface Package {
    id: number
    name: string
    name_en: string
    tokens: number
    price: string
    currency: string
    is_popular: boolean
    is_active: boolean
    sort_order: number
}

const props = defineProps<{
    flags:    Flag[]
    packages: Package[]
}>()

// Feature flags — local copy for optimistic UI
const flagsState = reactive(
    props.flags.map(f => ({ ...f }))
)

const savingFlags = ref(false)

function saveFlags() {
    savingFlags.value = true
    router.post('/admin/settings/flags', {
        flags: flagsState.map(f => ({ key: f.key, enabled: f.enabled })),
    }, {
        preserveState: true,
        onFinish: () => { savingFlags.value = false },
    })
}

// Package editing
const editingPkg  = ref<Package | null>(null)
const pkgForm     = useForm({ price: '', tokens: 0, is_active: true, is_popular: false })

function openPkg(pkg: Package) {
    editingPkg.value = { ...pkg }
    pkgForm.price      = pkg.price
    pkgForm.tokens     = pkg.tokens
    pkgForm.is_active  = pkg.is_active
    pkgForm.is_popular = pkg.is_popular
}

function savePkg() {
    if (!editingPkg.value) return
    pkgForm.post(`/admin/settings/packages/${editingPkg.value.id}`, {
        onSuccess: () => { editingPkg.value = null },
    })
}

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <div>
                    <h1 class="page-title">الإعدادات</h1>
                    <p class="page-subtitle">إدارة ميزات المنصة وأسعار الباقات</p>
                </div>
            </div>

            <!-- Feature flags -->
            <div class="settings-section">
                <div class="section-header">
                    <h2 class="section-title">ميزات المنصة</h2>
                    <button class="btn btn-primary btn--sm" :disabled="savingFlags" @click="saveFlags">
                        <Icon name="check" :size="14" />
                        {{ savingFlags ? 'جارٍ الحفظ...' : 'حفظ التغييرات' }}
                    </button>
                </div>
                <div class="flags-card">
                    <div v-for="flag in flagsState" :key="flag.key" class="flag-row">
                        <div class="flag-info">
                            <div class="flag-label">{{ flag.label }}</div>
                            <div class="flag-key">{{ flag.key }}</div>
                        </div>
                        <button
                            :class="['toggle', { 'toggle--on': flag.enabled }]"
                            @click="flag.enabled = !flag.enabled"
                        >
                            <span class="toggle-knob" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Token packages -->
            <div class="settings-section">
                <div class="section-header">
                    <h2 class="section-title">باقات الرصيد</h2>
                </div>
                <div class="packages-grid">
                    <div v-for="pkg in packages" :key="pkg.id" class="package-card">
                        <div class="pkg-header">
                            <span class="pkg-name">{{ pkg.name }}</span>
                            <div class="pkg-badges">
                                <span v-if="pkg.is_popular" class="pill pill--accent">الأكثر طلباً</span>
                                <span :class="['pill', pkg.is_active ? 'pill--green' : 'pill--gray']">
                                    {{ pkg.is_active ? 'مفعّلة' : 'معطّلة' }}
                                </span>
                            </div>
                        </div>
                        <div class="pkg-stats">
                            <div class="pkg-tokens">{{ fmt(pkg.tokens) }} رصيد</div>
                            <div class="pkg-price">{{ pkg.price }} {{ pkg.currency }}</div>
                        </div>
                        <button class="btn btn-ghost btn--sm pkg-edit" @click="openPkg(pkg)">
                            <Icon name="edit" :size="14" />
                            تعديل
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package edit modal -->
        <Teleport to="body">
            <div v-if="editingPkg" class="modal-backdrop" @click.self="editingPkg = null">
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">تعديل الباقة — {{ editingPkg.name }}</h3>
                        <button class="btn-close" @click="editingPkg = null">
                            <Icon name="x" :size="18" />
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="field">
                            <label class="field-label">السعر ({{ editingPkg.currency }})</label>
                            <input v-model="pkgForm.price" type="number" class="inp" min="1" />
                            <div v-if="pkgForm.errors.price" class="field-err">{{ pkgForm.errors.price }}</div>
                        </div>
                        <div class="field">
                            <label class="field-label">الرصيد المُمنوح</label>
                            <input v-model="pkgForm.tokens" type="number" class="inp" min="100" />
                            <div v-if="pkgForm.errors.tokens" class="field-err">{{ pkgForm.errors.tokens }}</div>
                        </div>
                        <div class="toggles-row">
                            <div class="toggle-field">
                                <span class="field-label">مفعّلة</span>
                                <button :class="['toggle', { 'toggle--on': pkgForm.is_active }]" @click="pkgForm.is_active = !pkgForm.is_active">
                                    <span class="toggle-knob" />
                                </button>
                            </div>
                            <div class="toggle-field">
                                <span class="field-label">الأكثر طلباً</span>
                                <button :class="['toggle', { 'toggle--on': pkgForm.is_popular }]" @click="pkgForm.is_popular = !pkgForm.is_popular">
                                    <span class="toggle-knob" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-ghost" @click="editingPkg = null">إلغاء</button>
                        <button class="btn btn-primary" :disabled="pkgForm.processing" @click="savePkg">
                            {{ pkgForm.processing ? 'جارٍ الحفظ...' : 'حفظ' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; max-width: 900px; }
.page-header { margin-bottom: 28px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.settings-section { margin-bottom: 32px; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); }

/* Flags */
.flags-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.flag-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--border-subtle); }
.flag-row:last-child { border-bottom: none; }
.flag-info { flex: 1; }
.flag-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.flag-key { font-size: 11px; color: var(--text-muted); font-family: monospace; margin-top: 2px; }

/* Toggle */
.toggle { position: relative; width: 42px; height: 24px; border-radius: 99px; background: var(--border-default); border: none; cursor: pointer; transition: background var(--dur-fast); padding: 0; flex-shrink: 0; }
.toggle--on { background: var(--primary); }
.toggle-knob { position: absolute; top: 3px; right: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; transition: transform var(--dur-fast); box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.toggle--on .toggle-knob { transform: translateX(-18px); }

/* Packages */
.packages-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.package-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.pkg-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
.pkg-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.pkg-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.pkg-stats { display: flex; flex-direction: column; gap: 4px; }
.pkg-tokens { font-size: 20px; font-weight: 700; color: var(--text-primary); }
.pkg-price  { font-size: 14px; color: var(--primary); font-weight: 600; }
.pkg-edit { width: 100%; justify-content: center; }

.pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.pill--green  { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.pill--gray   { background: color-mix(in oklab, #6b7280 12%, transparent); color: #6b7280; }
.pill--accent { background: color-mix(in oklab, #C8965F 12%, transparent); color: #C8965F; }

/* Modal */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal { background: var(--bg-card); border-radius: var(--radius-lg); padding: 28px; width: 420px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.modal-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }
.btn-close { background: transparent; border: none; cursor: pointer; color: var(--text-muted); padding: 4px; border-radius: var(--radius-sm); }
.btn-close:hover { background: var(--bg-muted); }
.modal-body { display: flex; flex-direction: column; gap: 16px; }
.modal-footer { display: flex; justify-content: flex-start; gap: 10px; margin-top: 24px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.field-err { font-size: 12px; color: #dc2626; }
.toggles-row { display: flex; gap: 24px; }
.toggle-field { display: flex; align-items: center; gap: 10px; }

.inp { background: var(--bg-page); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 9px 12px; font-size: 13px; color: var(--text-primary); font-family: var(--font-arabic); outline: none; width: 100%; box-sizing: border-box; }
.inp:focus { border-color: var(--primary); }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast), opacity var(--dur-fast); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover { background: var(--bg-muted); color: var(--text-primary); }
.btn--sm { padding: 7px 12px; font-size: 12px; }
</style>

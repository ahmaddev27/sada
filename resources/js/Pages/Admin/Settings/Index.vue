<script setup lang="ts">
import { reactive, ref } from 'vue'
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

const flagsState  = reactive(props.flags.map(f => ({ ...f })))
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

const editingPkg = ref<Package | null>(null)
const pkgForm    = useForm({ price: '', tokens: 0, is_active: true, is_popular: false })

function openPkg(pkg: Package) {
    editingPkg.value   = { ...pkg }
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

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الإعدادات</h1>
                    <p class="page-subtitle">إدارة ميزات المنصة وأسعار الباقات</p>
                </div>
            </div>

            <!-- ══ Feature Flags ══════════════════════════════════════════════════ -->
            <div class="section-block">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">ميزات المنصة</h2>
                        <p class="section-desc">تفعيل وإيقاف الميزات لجميع المستخدمين</p>
                    </div>
                    <button class="btn btn-primary btn-sm" :disabled="savingFlags" @click="saveFlags">
                        <Icon name="check" :size="14" />
                        {{ savingFlags ? 'جارٍ الحفظ...' : 'حفظ التغييرات' }}
                    </button>
                </div>

                <div class="flags-card">
                    <div class="flags-grid">
                        <div v-for="flag in flagsState" :key="flag.key" class="flag-row">
                            <div class="flag-info">
                                <div class="flag-label">{{ flag.label }}</div>
                                <code class="flag-key">{{ flag.key }}</code>
                            </div>
                            <button
                                type="button"
                                class="toggle"
                                :data-on="String(flag.enabled)"
                                :aria-pressed="flag.enabled"
                                :title="flag.enabled ? 'مفعّل — اضغط لإيقاف' : 'موقف — اضغط لتفعيل'"
                                @click="flag.enabled = !flag.enabled"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ Token Packages ═════════════════════════════════════════════════ -->
            <div class="section-block">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">باقات الرصيد</h2>
                        <p class="section-desc">أسعار ورصيد كل باقة اشتراك</p>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="packages.length === 0" class="empty-state">
                    <Icon name="coins" :size="32" style="color:var(--text-faint)" />
                    <p>لا توجد باقات مضافة بعد</p>
                </div>

                <div v-else class="packages-grid">
                    <div v-for="pkg in packages" :key="pkg.id" class="package-card">
                        <div class="pkg-header">
                            <div class="pkg-name">{{ pkg.name }}</div>
                            <div class="pkg-badges">
                                <span v-if="pkg.is_popular" class="badge badge-sand">
                                    <Icon name="sparkle" :size="10" /> الأكثر طلباً
                                </span>
                                <span :class="['badge', pkg.is_active ? 'badge-success' : 'badge-neutral']">
                                    {{ pkg.is_active ? 'مفعّلة' : 'معطّلة' }}
                                </span>
                            </div>
                        </div>
                        <div class="pkg-stats">
                            <div class="pkg-stat">
                                <span class="pkg-stat-val">{{ fmt(pkg.tokens) }}</span>
                                <span class="pkg-stat-lbl">رصيد</span>
                            </div>
                            <div class="pkg-divider"></div>
                            <div class="pkg-stat">
                                <span class="pkg-stat-val pkg-stat-val--price">{{ pkg.price }}</span>
                                <span class="pkg-stat-lbl">{{ pkg.currency }}</span>
                            </div>
                        </div>
                        <button class="btn btn-secondary btn-sm btn-full" @click="openPkg(pkg)">
                            <Icon name="edit" :size="13" />
                            تعديل الباقة
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Package edit modal -->
        <Teleport to="body">
            <div v-if="editingPkg" class="modal-backdrop" @click.self="editingPkg = null">
                <div class="modal modal-sm">
                    <div class="modal-head">
                        <div>
                            <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--text-primary)">تعديل — {{ editingPkg.name }}</h3>
                        </div>
                        <button class="btn btn-icon btn-ghost" @click="editingPkg = null">
                            <Icon name="x" :size="18" />
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group">
                            <label class="input-label">السعر ({{ editingPkg.currency }})</label>
                            <input v-model="pkgForm.price" type="number" class="input" min="1" />
                            <span v-if="pkgForm.errors.price" class="field-err">{{ pkgForm.errors.price }}</span>
                        </div>
                        <div class="input-group" style="margin-top:14px">
                            <label class="input-label">الرصيد المُمنوح</label>
                            <input v-model="pkgForm.tokens" type="number" class="input" min="100" />
                            <span v-if="pkgForm.errors.tokens" class="field-err">{{ pkgForm.errors.tokens }}</span>
                        </div>
                        <div class="toggle-fields" style="margin-top:16px">
                            <div class="toggle-field">
                                <span class="input-label">مفعّلة</span>
                                <button
                                    type="button"
                                    class="toggle"
                                    :data-on="String(pkgForm.is_active)"
                                    @click="pkgForm.is_active = !pkgForm.is_active"
                                />
                            </div>
                            <div class="toggle-field">
                                <span class="input-label">الأكثر طلباً</span>
                                <button
                                    type="button"
                                    class="toggle"
                                    :data-on="String(pkgForm.is_popular)"
                                    @click="pkgForm.is_popular = !pkgForm.is_popular"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button class="btn btn-secondary" @click="editingPkg = null">إلغاء</button>
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
.admin-page { padding: 28px 32px; display: flex; flex-direction: column; gap: 28px; }

.page-header   { display: flex; align-items: center; justify-content: space-between; }
.page-title    { font-size: 22px; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); margin: 0; }

/* ── Section blocks ──────────────────────────────────────────────────── */
.section-block  { display: flex; flex-direction: column; gap: 16px; }
.section-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.section-title  { font-size: 16px; font-weight: 700; color: var(--text-primary); margin: 0 0 3px; }
.section-desc   { font-size: 12px; color: var(--text-muted); margin: 0; }

/* ── Feature flags ───────────────────────────────────────────────────── */
.flags-card  { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.flags-grid  { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
.flag-row {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 14px 20px; border-bottom: 1px solid var(--border-subtle);
    transition: background var(--dur-fast);
}
.flag-row:last-child { border-bottom: none; }
.flag-row:hover { background: var(--bg-muted); }
.flag-info  { flex: 1; min-width: 0; }
.flag-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.flag-key   { display: block; font-size: 11px; color: var(--text-muted); font-family: monospace; margin-top: 3px; background: none; padding: 0; border: none; }

/* ── Token packages ──────────────────────────────────────────────────── */
.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}
.package-card {
    background: var(--bg-surface); border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg); padding: 20px;
    display: flex; flex-direction: column; gap: 14px;
    transition: box-shadow var(--dur-fast), border-color var(--dur-fast);
}
.package-card:hover { border-color: var(--border-default); box-shadow: 0 2px 12px rgba(0,0,0,.06); }

.pkg-header  { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.pkg-name    { font-size: 15px; font-weight: 700; color: var(--text-primary); }
.pkg-badges  { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }

.pkg-stats     { display: flex; align-items: center; gap: 12px; }
.pkg-stat      { display: flex; flex-direction: column; gap: 2px; }
.pkg-stat-val  { font-size: 20px; font-weight: 800; color: var(--text-primary); line-height: 1.1; }
.pkg-stat-val--price { color: var(--accent); font-size: 18px; }
.pkg-stat-lbl  { font-size: 11px; color: var(--text-muted); }
.pkg-divider   { width: 1px; height: 32px; background: var(--border-subtle); }

/* ── Empty state ─────────────────────────────────────────────────────── */
.empty-state {
    background: var(--bg-surface); border: 1px dashed var(--border-subtle);
    border-radius: var(--radius-lg); padding: 48px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    color: var(--text-muted); font-size: 13px;
}

/* ── Modal extras ────────────────────────────────────────────────────── */
.toggle-fields { display: flex; gap: 24px; }
.toggle-field  { display: flex; align-items: center; gap: 10px; }
.field-err     { font-size: 12px; color: var(--error); }

/* ── Global util overrides ───────────────────────────────────────────── */
.btn-full { width: 100%; justify-content: center; }
</style>

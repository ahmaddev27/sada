<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Broadcast {
    id: number
    action: string
    payload: { title: string; body: string; audience: string; target_count: number }
    created_at: string
    admin: { id: number; name: string } | null
}

const props = defineProps<{
    recentBroadcasts: Broadcast[]
    stats: { total_users: number; verified_users: number; total_broadcasts: number }
}>()

const form = useForm({
    title:    '',
    body:     '',
    audience: 'all',
})

function send() {
    form.post('/admin/notifications/broadcast', {
        onSuccess: () => {
            form.reset()
        },
    })
}

function audienceLabel(a: string) {
    return a === 'verified' ? 'المستخدمون الموثّقون' : 'جميع المستخدمين'
}

function targetCount(a: string) {
    return a === 'verified' ? props.stats.verified_users : props.stats.total_users
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
                    <h1 class="page-title">الإشعارات</h1>
                    <p class="page-subtitle">إرسال إشعارات جماعية لمستخدمي المنصة</p>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_users) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#0F6F5C 12%,transparent);color:#0F6F5C">
                            <Icon name="users" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">إجمالي المستخدمين</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.verified_users) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#22c55e 12%,transparent);color:#22c55e">
                            <Icon name="check" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">مستخدمون موثّقون</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_broadcasts) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#C8965F 12%,transparent);color:#C8965F">
                            <Icon name="bell" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">إشعارات أُرسلت</div>
                </div>
            </div>

            <div class="two-col">
                <!-- Compose form -->
                <div class="compose-card">
                    <h2 class="section-title">إرسال إشعار جديد</h2>

                    <div class="field">
                        <label class="field-label">عنوان الإشعار</label>
                        <input
                            v-model="form.title"
                            class="inp"
                            placeholder="مثال: تحديث جديد في المنصة"
                            maxlength="100"
                        />
                        <div class="char-count">{{ form.title.length }}/100</div>
                        <div v-if="form.errors.title" class="field-err">{{ form.errors.title }}</div>
                    </div>

                    <div class="field">
                        <label class="field-label">نص الإشعار</label>
                        <textarea
                            v-model="form.body"
                            class="inp inp--textarea"
                            placeholder="تفاصيل الإشعار..."
                            maxlength="500"
                            rows="4"
                        />
                        <div class="char-count">{{ form.body.length }}/500</div>
                        <div v-if="form.errors.body" class="field-err">{{ form.errors.body }}</div>
                    </div>

                    <div class="field">
                        <label class="field-label">الجمهور المستهدف</label>
                        <select v-model="form.audience" class="inp">
                            <option value="all">جميع المستخدمين ({{ fmt(stats.total_users) }})</option>
                            <option value="verified">المستخدمون الموثّقون فقط ({{ fmt(stats.verified_users) }})</option>
                        </select>
                    </div>

                    <div class="send-row">
                        <div class="target-preview">
                            <Icon name="users" :size="14" />
                            سيُرسل إلى {{ fmt(targetCount(form.audience)) }} مستخدم
                        </div>
                        <button
                            class="btn btn-primary"
                            :disabled="form.processing || !form.title || !form.body"
                            @click="send"
                        >
                            <Icon name="send" :size="16" />
                            {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال الإشعار' }}
                        </button>
                    </div>
                </div>

                <!-- Recent broadcasts -->
                <div class="history-card">
                    <h2 class="section-title">آخر الإشعارات المُرسلة</h2>
                    <div v-if="recentBroadcasts.length === 0" class="empty-state">
                        <Icon name="bell" :size="32" />
                        <p>لم يُرسل أي إشعار بعد</p>
                    </div>
                    <div v-else class="history-list">
                        <div v-for="b in recentBroadcasts" :key="b.id" class="history-item">
                            <div class="hist-header">
                                <span class="hist-title">{{ b.payload?.title }}</span>
                                <span class="hist-date">{{ new Date(b.created_at).toLocaleDateString('ar-SA') }}</span>
                            </div>
                            <p class="hist-body">{{ b.payload?.body }}</p>
                            <div class="hist-meta">
                                <span class="pill pill--blue">{{ audienceLabel(b.payload?.audience) }}</span>
                                <span class="hist-count">{{ fmt(b.payload?.target_count ?? 0) }} مستخدم</span>
                                <span class="hist-admin">بواسطة: {{ b.admin?.name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; }
.page-header { margin-bottom: 24px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 18px 20px; display: flex; flex-direction: column; gap: 8px; }
.kpi-top { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.kpi-value { font-size: 22px; font-weight: 700; color: var(--text-primary); }
.kpi-icon { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

.compose-card,
.history-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 24px; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; }

.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.field-err { font-size: 12px; color: #dc2626; }
.char-count { font-size: 11px; color: var(--text-muted); text-align: left; }

.inp { background: var(--bg-page); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 9px 12px; font-size: 13px; color: var(--text-primary); font-family: var(--font-arabic); outline: none; width: 100%; box-sizing: border-box; }
.inp:focus { border-color: var(--primary); }
.inp--textarea { resize: vertical; }

.send-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 8px; }
.target-preview { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); }

.history-list { display: flex; flex-direction: column; gap: 12px; max-height: 500px; overflow-y: auto; }
.history-item { padding: 14px; background: var(--bg-muted); border-radius: var(--radius-md); border: 1px solid var(--border-subtle); }
.hist-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.hist-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.hist-date { font-size: 11px; color: var(--text-muted); }
.hist-body { font-size: 12px; color: var(--text-secondary); margin-bottom: 8px; line-height: 1.5; }
.hist-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.hist-count { font-size: 11px; color: var(--text-muted); }
.hist-admin { font-size: 11px; color: var(--text-muted); margin-right: auto; }

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 40px; color: var(--text-muted); text-align: center; }

.pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.pill--blue { background: color-mix(in oklab, #3b82f6 12%, transparent); color: #2563eb; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast), opacity var(--dur-fast); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); }
</style>

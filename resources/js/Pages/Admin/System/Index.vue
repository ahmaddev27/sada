<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface StatusResult {
    status: 'ok' | 'degraded' | 'error' | 'unknown'
    latency_ms?: number
    message?: string
    pending?: number
    failed?: number
    driver?: string
}

const props = defineProps<{
    dbStatus:    StatusResult
    cacheStatus: StatusResult
    queueStats:  StatusResult
    disk: { free_gb: number; total_gb: number; used_pct: number }
    info: Record<string, string>
}>()

const refreshing = ref(false)

function refresh() {
    refreshing.value = true
    router.reload({ onFinish: () => { refreshing.value = false } })
}

function statusCls(s: string) {
    if (s === 'ok')      return 'status--ok'
    if (s === 'degraded') return 'status--warn'
    if (s === 'error')   return 'status--err'
    return 'status--unknown'
}

function statusLabel(s: string) {
    if (s === 'ok')      return 'يعمل'
    if (s === 'degraded') return 'مدمّر جزئياً'
    if (s === 'error')   return 'خطأ'
    return 'غير معروف'
}

const diskCls = props.disk.used_pct >= 90 ? 'status--err' : props.disk.used_pct >= 75 ? 'status--warn' : 'status--ok'
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <div>
                    <h1 class="page-title">صحة النظام</h1>
                    <p class="page-subtitle">مراقبة حالة الخدمات والبنية التحتية</p>
                </div>
                <button class="btn btn-ghost" :disabled="refreshing" @click="refresh">
                    <Icon name="refresh" :size="15" />
                    {{ refreshing ? 'جارٍ التحديث...' : 'تحديث' }}
                </button>
            </div>

            <!-- Services -->
            <h2 class="section-title">حالة الخدمات</h2>
            <div class="services-grid">
                <!-- Database -->
                <div class="service-card">
                    <div class="service-header">
                        <div class="service-icon">
                            <Icon name="server" :size="20" />
                        </div>
                        <div class="service-info">
                            <div class="service-name">قاعدة البيانات</div>
                            <div class="service-sub">{{ info.db_driver?.toUpperCase() }}</div>
                        </div>
                        <span :class="['status-dot', statusCls(dbStatus.status)]" />
                    </div>
                    <div class="service-body">
                        <div :class="['status-badge', statusCls(dbStatus.status)]">{{ statusLabel(dbStatus.status) }}</div>
                        <div v-if="dbStatus.latency_ms" class="service-metric">
                            زمن الاستجابة: <strong>{{ dbStatus.latency_ms }} ms</strong>
                        </div>
                        <div v-if="dbStatus.message" class="service-error">{{ dbStatus.message }}</div>
                    </div>
                </div>

                <!-- Cache -->
                <div class="service-card">
                    <div class="service-header">
                        <div class="service-icon">
                            <Icon name="refresh" :size="20" />
                        </div>
                        <div class="service-info">
                            <div class="service-name">الـ Cache</div>
                            <div class="service-sub">{{ info.cache_driver?.toUpperCase() }}</div>
                        </div>
                        <span :class="['status-dot', statusCls(cacheStatus.status)]" />
                    </div>
                    <div class="service-body">
                        <div :class="['status-badge', statusCls(cacheStatus.status)]">{{ statusLabel(cacheStatus.status) }}</div>
                        <div v-if="cacheStatus.message" class="service-error">{{ cacheStatus.message }}</div>
                    </div>
                </div>

                <!-- Queue -->
                <div class="service-card">
                    <div class="service-header">
                        <div class="service-icon">
                            <Icon name="clock" :size="20" />
                        </div>
                        <div class="service-info">
                            <div class="service-name">طابور المهام</div>
                            <div class="service-sub">{{ (queueStats.driver ?? info.queue_driver)?.toUpperCase() }}</div>
                        </div>
                        <span :class="['status-dot', statusCls(queueStats.status)]" />
                    </div>
                    <div class="service-body">
                        <div :class="['status-badge', statusCls(queueStats.status)]">{{ statusLabel(queueStats.status) }}</div>
                        <div v-if="queueStats.pending !== undefined" class="service-metric">
                            معلّق: <strong>{{ queueStats.pending }}</strong> · فاشل: <strong>{{ queueStats.failed }}</strong>
                        </div>
                        <div v-if="queueStats.message" class="service-error">{{ queueStats.message }}</div>
                    </div>
                </div>

                <!-- Disk -->
                <div class="service-card">
                    <div class="service-header">
                        <div class="service-icon">
                            <Icon name="server" :size="20" />
                        </div>
                        <div class="service-info">
                            <div class="service-name">مساحة التخزين</div>
                            <div class="service-sub">{{ disk.total_gb }} GB إجمالي</div>
                        </div>
                        <span :class="['status-dot', diskCls]" />
                    </div>
                    <div class="service-body">
                        <div class="disk-bar-wrap">
                            <div class="disk-bar">
                                <div class="disk-fill" :class="diskCls" :style="`width:${disk.used_pct}%`" />
                            </div>
                            <span class="disk-pct">{{ disk.used_pct }}%</span>
                        </div>
                        <div class="service-metric">
                            مستخدم: {{ (disk.total_gb - disk.free_gb).toFixed(1) }} GB · متبقٍّ: {{ disk.free_gb }} GB
                        </div>
                    </div>
                </div>
            </div>

            <!-- System info -->
            <h2 class="section-title" style="margin-top:28px;">معلومات البيئة</h2>
            <div class="info-card">
                <div v-for="(value, key) in info" :key="key" class="info-row">
                    <span class="info-key">{{ key }}</span>
                    <span class="info-val">{{ value }}</span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; max-width: 900px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }

.services-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.service-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 20px; }
.service-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.service-icon { width: 38px; height: 38px; border-radius: var(--radius-md); background: var(--bg-muted); display: flex; align-items: center; justify-content: center; color: var(--text-muted); flex-shrink: 0; }
.service-info { flex: 1; min-width: 0; }
.service-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.service-sub  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

.status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.status-dot.status--ok      { background: #22c55e; }
.status-dot.status--warn    { background: #f59e0b; }
.status-dot.status--err     { background: #ef4444; }
.status-dot.status--unknown { background: #6b7280; }

.service-body { display: flex; flex-direction: column; gap: 8px; }
.status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; width: fit-content; }
.status-badge.status--ok      { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.status-badge.status--warn    { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.status-badge.status--err     { background: color-mix(in oklab, #ef4444 12%, transparent); color: #dc2626; }
.status-badge.status--unknown { background: color-mix(in oklab, #6b7280 12%, transparent); color: #6b7280; }
.service-metric { font-size: 12px; color: var(--text-muted); }
.service-error  { font-size: 12px; color: #dc2626; font-family: monospace; }

.disk-bar-wrap { display: flex; align-items: center; gap: 10px; }
.disk-bar { flex: 1; height: 6px; background: var(--bg-muted); border-radius: 99px; overflow: hidden; }
.disk-fill { height: 100%; border-radius: 99px; transition: width .3s; }
.disk-fill.status--ok   { background: #22c55e; }
.disk-fill.status--warn { background: #f59e0b; }
.disk-fill.status--err  { background: #ef4444; }
.disk-pct { font-size: 12px; font-weight: 700; color: var(--text-primary); flex-shrink: 0; }

.info-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.info-row { display: flex; align-items: center; justify-content: space-between; padding: 11px 20px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-key { color: var(--text-muted); font-weight: 500; }
.info-val { color: var(--text-primary); font-weight: 600; font-family: monospace; font-size: 12px; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast), opacity var(--dur-fast); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover:not(:disabled) { background: var(--bg-muted); color: var(--text-primary); }
</style>

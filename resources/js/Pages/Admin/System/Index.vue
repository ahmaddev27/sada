<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
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

const refreshing  = ref(false)
const clearingCache = ref<string | null>(null)
const optimizing  = ref(false)

function refresh() {
    refreshing.value = true
    router.reload({ onFinish: () => { refreshing.value = false } })
}

function clearCache(what: string) {
    clearingCache.value = what
    router.post('/admin/system/clear-cache', { what }, {
        onFinish: () => { clearingCache.value = null },
    })
}

function optimize() {
    optimizing.value = true
    router.post('/admin/system/optimize', {}, {
        onFinish: () => { optimizing.value = false },
    })
}

function statusCls(s: string) {
    if (s === 'ok')       return 'ok'
    if (s === 'degraded') return 'warn'
    if (s === 'error')    return 'err'
    return 'unknown'
}

function statusLabel(s: string) {
    if (s === 'ok')       return 'يعمل'
    if (s === 'degraded') return 'مدمّر جزئياً'
    if (s === 'error')    return 'خطأ'
    return 'غير معروف'
}

const diskLevel = props.disk.used_pct >= 90 ? 'err' : props.disk.used_pct >= 75 ? 'warn' : 'ok'

const infoLabels: Record<string, string> = {
    laravel_version: 'Laravel',
    php_version:     'PHP',
    env:             'البيئة',
    debug:           'وضع التطوير',
    timezone:        'المنطقة الزمنية',
    queue_driver:    'محرك الطابور',
    cache_driver:    'محرك الكاش',
    db_driver:       'قاعدة البيانات',
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <!-- Header -->
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

            <div class="layout-two">
                <!-- Left column: services + actions -->
                <div class="col-main">
                    <!-- Services -->
                    <h2 class="section-title">حالة الخدمات</h2>
                    <div class="services-grid">
                        <!-- Database -->
                        <div :class="['service-card', `border-${statusCls(dbStatus.status)}`]">
                            <div class="service-header">
                                <div :class="['service-icon', `icon-${statusCls(dbStatus.status)}`]">
                                    <Icon name="server" :size="20" />
                                </div>
                                <div class="service-info">
                                    <div class="service-name">قاعدة البيانات</div>
                                    <div class="service-sub">{{ info.db_driver?.toUpperCase() }}</div>
                                </div>
                                <span :class="['dot', `dot--${statusCls(dbStatus.status)}`]" />
                            </div>
                            <div class="service-body">
                                <span :class="['status-pill', `pill--${statusCls(dbStatus.status)}`]">{{ statusLabel(dbStatus.status) }}</span>
                                <div v-if="dbStatus.latency_ms" class="metric">زمن الاستجابة: <strong>{{ dbStatus.latency_ms }} ms</strong></div>
                                <div v-if="dbStatus.message" class="err-txt">{{ dbStatus.message }}</div>
                            </div>
                        </div>

                        <!-- Cache -->
                        <div :class="['service-card', `border-${statusCls(cacheStatus.status)}`]">
                            <div class="service-header">
                                <div :class="['service-icon', `icon-${statusCls(cacheStatus.status)}`]">
                                    <Icon name="refresh" :size="20" />
                                </div>
                                <div class="service-info">
                                    <div class="service-name">الكاش</div>
                                    <div class="service-sub">{{ info.cache_driver?.toUpperCase() }}</div>
                                </div>
                                <span :class="['dot', `dot--${statusCls(cacheStatus.status)}`]" />
                            </div>
                            <div class="service-body">
                                <span :class="['status-pill', `pill--${statusCls(cacheStatus.status)}`]">{{ statusLabel(cacheStatus.status) }}</span>
                                <div v-if="cacheStatus.message" class="err-txt">{{ cacheStatus.message }}</div>
                            </div>
                        </div>

                        <!-- Queue -->
                        <div :class="['service-card', `border-${statusCls(queueStats.status)}`]">
                            <div class="service-header">
                                <div :class="['service-icon', `icon-${statusCls(queueStats.status)}`]">
                                    <Icon name="clock" :size="20" />
                                </div>
                                <div class="service-info">
                                    <div class="service-name">طابور المهام</div>
                                    <div class="service-sub">{{ (queueStats.driver ?? info.queue_driver)?.toUpperCase() }}</div>
                                </div>
                                <span :class="['dot', `dot--${statusCls(queueStats.status)}`]" />
                            </div>
                            <div class="service-body">
                                <span :class="['status-pill', `pill--${statusCls(queueStats.status)}`]">{{ statusLabel(queueStats.status) }}</span>
                                <div v-if="queueStats.pending !== undefined" class="metric">
                                    معلّق: <strong>{{ queueStats.pending }}</strong> &nbsp;·&nbsp; فاشل: <strong class="err-count">{{ queueStats.failed }}</strong>
                                </div>
                                <div v-if="queueStats.message" class="err-txt">{{ queueStats.message }}</div>
                            </div>
                        </div>

                        <!-- Disk -->
                        <div :class="['service-card', `border-${diskLevel}`]">
                            <div class="service-header">
                                <div :class="['service-icon', `icon-${diskLevel}`]">
                                    <Icon name="server" :size="20" />
                                </div>
                                <div class="service-info">
                                    <div class="service-name">مساحة التخزين</div>
                                    <div class="service-sub">{{ disk.total_gb }} GB إجمالي</div>
                                </div>
                                <span :class="['dot', `dot--${diskLevel}`]" />
                            </div>
                            <div class="service-body">
                                <div class="disk-wrap">
                                    <div class="disk-track">
                                        <div :class="['disk-fill', `fill--${diskLevel}`]" :style="`width:${disk.used_pct}%`" />
                                    </div>
                                    <span class="disk-pct">{{ disk.used_pct }}%</span>
                                </div>
                                <div class="metric">
                                    مستخدم: <strong>{{ (disk.total_gb - disk.free_gb).toFixed(1) }} GB</strong>
                                    &nbsp;·&nbsp; متبقٍّ: <strong>{{ disk.free_gb }} GB</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cache management -->
                    <h2 class="section-title mt">إدارة الكاش</h2>
                    <div class="actions-card">
                        <div class="action-row">
                            <div class="action-info">
                                <div class="action-label">كاش التطبيق</div>
                                <div class="action-desc">مسح كاش البيانات والنتائج المحفوظة</div>
                            </div>
                            <button
                                class="btn btn-warn"
                                :disabled="clearingCache !== null"
                                @click="clearCache('app')"
                            >
                                <Icon name="trash" :size="14" />
                                {{ clearingCache === 'app' ? 'جارٍ المسح...' : 'مسح' }}
                            </button>
                        </div>
                        <div class="action-row">
                            <div class="action-info">
                                <div class="action-label">كاش الإعدادات</div>
                                <div class="action-desc">مسح config cache (بعد تعديل .env)</div>
                            </div>
                            <button
                                class="btn btn-warn"
                                :disabled="clearingCache !== null"
                                @click="clearCache('config')"
                            >
                                <Icon name="trash" :size="14" />
                                {{ clearingCache === 'config' ? 'جارٍ المسح...' : 'مسح' }}
                            </button>
                        </div>
                        <div class="action-row">
                            <div class="action-info">
                                <div class="action-label">كاش المسارات</div>
                                <div class="action-desc">مسح route cache (بعد تعديل routes)</div>
                            </div>
                            <button
                                class="btn btn-warn"
                                :disabled="clearingCache !== null"
                                @click="clearCache('route')"
                            >
                                <Icon name="trash" :size="14" />
                                {{ clearingCache === 'route' ? 'جارٍ المسح...' : 'مسح' }}
                            </button>
                        </div>
                        <div class="action-row">
                            <div class="action-info">
                                <div class="action-label">كاش العروض</div>
                                <div class="action-desc">مسح view cache (Blade compiled views)</div>
                            </div>
                            <button
                                class="btn btn-warn"
                                :disabled="clearingCache !== null"
                                @click="clearCache('view')"
                            >
                                <Icon name="trash" :size="14" />
                                {{ clearingCache === 'view' ? 'جارٍ المسح...' : 'مسح' }}
                            </button>
                        </div>
                        <div class="action-row action-row--highlight">
                            <div class="action-info">
                                <div class="action-label">تحسين الأداء</div>
                                <div class="action-desc">config:cache + route:cache + view:cache (للـ production)</div>
                            </div>
                            <button
                                class="btn btn-primary"
                                :disabled="optimizing"
                                @click="optimize"
                            >
                                <Icon name="sparkle" :size="14" />
                                {{ optimizing ? 'جارٍ التحسين...' : 'Optimize' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right column: system info -->
                <div class="col-side">
                    <h2 class="section-title">معلومات البيئة</h2>
                    <div class="info-card">
                        <div v-for="(value, key) in info" :key="key" class="info-row">
                            <span class="info-key">{{ infoLabels[key] ?? key }}</span>
                            <span class="info-val">{{ value }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px; }
.mt { margin-top: 28px; }

.layout-two { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
.col-main { display: flex; flex-direction: column; }
.col-side { position: sticky; top: 24px; }

/* Services */
.services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.service-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 18px 20px; border-right-width: 3px; }
.service-card.border-ok      { border-right-color: #22c55e; }
.service-card.border-warn    { border-right-color: #f59e0b; }
.service-card.border-err     { border-right-color: #ef4444; }
.service-card.border-unknown { border-right-color: var(--border-subtle); }

.service-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.service-icon { width: 36px; height: 36px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.icon-ok      { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.icon-warn    { background: color-mix(in oklab, #f59e0b 12%, transparent); color: #d97706; }
.icon-err     { background: color-mix(in oklab, #ef4444 12%, transparent); color: #dc2626; }
.icon-unknown { background: var(--bg-muted); color: var(--text-muted); }

.service-info { flex: 1; }
.service-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.service-sub  { font-size: 11px; color: var(--text-muted); margin-top: 2px; font-family: monospace; }

.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dot--ok      { background: #22c55e; box-shadow: 0 0 0 3px color-mix(in oklab, #22c55e 25%, transparent); }
.dot--warn    { background: #f59e0b; box-shadow: 0 0 0 3px color-mix(in oklab, #f59e0b 25%, transparent); }
.dot--err     { background: #ef4444; box-shadow: 0 0 0 3px color-mix(in oklab, #ef4444 25%, transparent); }
.dot--unknown { background: #9ca3af; }

.service-body { display: flex; flex-direction: column; gap: 6px; }
.status-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; width: fit-content; }
.pill--ok      { background: color-mix(in oklab, #22c55e 15%, transparent); color: #16a34a; }
.pill--warn    { background: color-mix(in oklab, #f59e0b 15%, transparent); color: #d97706; }
.pill--err     { background: color-mix(in oklab, #ef4444 15%, transparent); color: #dc2626; }
.pill--unknown { background: var(--bg-muted); color: var(--text-muted); }
.metric { font-size: 12px; color: var(--text-muted); }
.err-txt   { font-size: 11px; color: #dc2626; font-family: monospace; word-break: break-all; }
.err-count { color: #dc2626; }

.disk-wrap  { display: flex; align-items: center; gap: 10px; }
.disk-track { flex: 1; height: 6px; background: var(--bg-muted); border-radius: 99px; overflow: hidden; }
.disk-fill  { height: 100%; border-radius: 99px; }
.fill--ok   { background: #22c55e; }
.fill--warn { background: #f59e0b; }
.fill--err  { background: #ef4444; }
.disk-pct   { font-size: 12px; font-weight: 700; color: var(--text-primary); min-width: 38px; text-align: right; }

/* Cache actions */
.actions-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.action-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; border-bottom: 1px solid var(--border-subtle); }
.action-row:last-child { border-bottom: none; }
.action-row--highlight { background: color-mix(in oklab, var(--primary) 4%, transparent); }
.action-info { flex: 1; }
.action-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.action-desc  { font-size: 11px; color: var(--text-muted); margin-top: 2px; font-family: monospace; }

/* System info */
.info-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.info-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 16px; border-bottom: 1px solid var(--border-subtle); }
.info-row:last-child { border-bottom: none; }
.info-key { font-size: 12px; color: var(--text-muted); font-weight: 500; white-space: nowrap; }
.info-val { font-size: 12px; color: var(--text-primary); font-weight: 600; font-family: monospace; text-align: left; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast), opacity var(--dur-fast); white-space: nowrap; flex-shrink: 0; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover:not(:disabled) { background: var(--bg-muted); color: var(--text-primary); }
.btn-warn { background: color-mix(in oklab, #ef4444 10%, transparent); color: #dc2626; border: 1px solid color-mix(in oklab, #ef4444 25%, transparent); }
.btn-warn:hover:not(:disabled) { background: color-mix(in oklab, #ef4444 18%, transparent); }
</style>

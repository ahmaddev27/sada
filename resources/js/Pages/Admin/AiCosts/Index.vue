<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface Generation {
    id: number
    agent_type: string
    platform: string | null
    dialect: string | null
    input_tokens: number
    output_tokens: number
    sada_tokens_charged: number
    cached: boolean
    created_at: string
    workspace: { id: number; name: string } | null
    user: { id: number; name: string } | null
}

interface AgentStat {
    agent_type: string
    count: number
    tokens: number
    cached_count: number
}

const props = defineProps<{
    generations: { data: Generation[]; links: any[]; meta: any }
    filters: { search?: string; agent_type?: string; platform?: string }
    stats: {
        total_generations: number
        today_generations: number
        total_tokens_billed: number
        total_input_tokens: number
        total_output_tokens: number
        cached_count: number
        cached_savings: number
    }
    byAgentType: AgentStat[]
    dailyChart: { date: string; count: number; tokens: number; cached: number }[]
}>()

const search     = ref(props.filters.search     ?? '')
const agentType  = ref(props.filters.agent_type ?? '')
const platform   = ref(props.filters.platform   ?? '')

function applyFilters() {
    router.get('/admin/ai-costs', {
        search:     search.value,
        agent_type: agentType.value,
        platform:   platform.value,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value    = ''
    agentType.value = ''
    platform.value  = ''
    applyFilters()
}

const agentLabels: Record<string, string> = {
    content_generator: 'توليد المحتوى',
    caption_writer:    'كتابة التعليقات',
    hashtag_generator: 'توليد الهاشتاقات',
    analytics:         'تحليلات',
    seasonal:          'موسمي',
}

function fmt(n: number) {
    return new Intl.NumberFormat('ar-SA').format(n)
}

function cacheRate(stat: AgentStat) {
    if (!stat.count) return '0%'
    return Math.round(stat.cached_count / stat.count * 100) + '%'
}
</script>

<template>
    <AdminLayout>
        <div class="admin-page">
            <div class="page-header">
                <div>
                    <h1 class="page-title">تكاليف الذكاء الاصطناعي</h1>
                    <p class="page-subtitle">مراقبة استهلاك الرموز وتكاليف التوليد</p>
                </div>
            </div>

            <!-- KPIs -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_generations) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#0F6F5C 12%,transparent);color:#0F6F5C">
                            <Icon name="sparkle" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">إجمالي التوليدات</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.total_tokens_billed) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#C8965F 12%,transparent);color:#C8965F">
                            <Icon name="coins" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">رصيد مُفوتر</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.cached_count) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#22c55e 12%,transparent);color:#22c55e">
                            <Icon name="refresh" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">نتائج محفوظة (Cache)</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-top">
                        <div class="kpi-value">{{ fmt(stats.today_generations) }}</div>
                        <div class="kpi-icon" style="background:color-mix(in oklab,#3b82f6 12%,transparent);color:#3b82f6">
                            <Icon name="chart" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">توليدات اليوم</div>
                </div>
            </div>

            <!-- Breakdown by agent type -->
            <div class="breakdown-card">
                <h2 class="section-title">توزيع حسب نوع الوكيل</h2>
                <div class="breakdown-grid">
                    <div v-for="stat in byAgentType" :key="stat.agent_type" class="breakdown-row">
                        <div class="breakdown-label">{{ agentLabels[stat.agent_type] ?? stat.agent_type }}</div>
                        <div class="breakdown-stats">
                            <span class="stat-chip">{{ fmt(stat.count) }} توليد</span>
                            <span class="stat-chip accent">{{ fmt(stat.tokens) }} رصيد</span>
                            <span class="stat-chip green">{{ cacheRate(stat) }} cache</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <input v-model="search" class="inp" placeholder="بحث بـ Workspace..." @keyup.enter="applyFilters" />
                <select v-model="agentType" class="inp inp--sm" @change="applyFilters">
                    <option value="">كل الوكلاء</option>
                    <option v-for="(label, key) in agentLabels" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="platform" class="inp inp--sm" @change="applyFilters">
                    <option value="">كل المنصات</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="tiktok">TikTok</option>
                    <option value="snapchat">Snapchat</option>
                    <option value="x">X</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
                <button class="btn btn-ghost btn--sm" @click="resetFilters">إعادة تعيين</button>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Workspace</th>
                            <th>المستخدم</th>
                            <th>الوكيل</th>
                            <th>المنصة</th>
                            <th>رموز الدخل</th>
                            <th>رموز الخرج</th>
                            <th>مُفوتر</th>
                            <th>Cache</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="generations.data.length === 0">
                            <td colspan="10" class="empty-row">لا توجد بيانات</td>
                        </tr>
                        <tr v-for="g in generations.data" :key="g.id">
                            <td class="id-cell">{{ g.id }}</td>
                            <td class="ws-cell">{{ g.workspace?.name ?? '—' }}</td>
                            <td class="ws-cell">{{ g.user?.name ?? '—' }}</td>
                            <td><span class="pill pill--purple">{{ agentLabels[g.agent_type] ?? g.agent_type }}</span></td>
                            <td><span class="pill pill--gray">{{ g.platform ?? '—' }}</span></td>
                            <td class="num-cell">{{ fmt(g.input_tokens) }}</td>
                            <td class="num-cell">{{ fmt(g.output_tokens) }}</td>
                            <td class="amount">{{ fmt(g.sada_tokens_charged) }}</td>
                            <td>
                                <span :class="['pill', g.cached ? 'pill--green' : 'pill--gray']">
                                    {{ g.cached ? 'نعم' : 'لا' }}
                                </span>
                            </td>
                            <td class="date-cell">{{ new Date(g.created_at).toLocaleDateString('ar-SA') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="generations.meta?.last_page > 1" class="pagination">
                <button
                    v-for="link in generations.links"
                    :key="link.label"
                    :disabled="!link.url"
                    :class="['page-btn', { active: link.active }]"
                    @click="link.url && router.visit(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-page { padding: 28px 32px; }
.page-header { margin-bottom: 24px; }
.page-title  { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 18px 20px; display: flex; flex-direction: column; gap: 8px; }
.kpi-top { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.kpi-value { font-size: 22px; font-weight: 700; color: var(--text-primary); }
.kpi-icon { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }

.breakdown-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.breakdown-grid { display: flex; flex-direction: column; gap: 10px; }
.breakdown-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-subtle); }
.breakdown-row:last-child { border-bottom: none; }
.breakdown-label { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.breakdown-stats { display: flex; gap: 8px; }
.stat-chip { font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 99px; background: var(--bg-muted); color: var(--text-muted); }
.stat-chip.accent { background: color-mix(in oklab, #C8965F 12%, transparent); color: #C8965F; }
.stat-chip.green  { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }

.filters-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.inp { background: var(--bg-card); border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 8px 12px; font-size: 13px; color: var(--text-primary); font-family: var(--font-arabic); outline: none; }
.inp:focus { border-color: var(--primary); }
.inp--sm { padding: 8px 10px; }

.table-card { background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { background: var(--bg-muted); color: var(--text-muted); font-weight: 600; padding: 10px 16px; text-align: right; border-bottom: 1px solid var(--border-subtle); font-size: 12px; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-primary); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--bg-muted); }
.empty-row { text-align: center; color: var(--text-muted); padding: 32px !important; }

.id-cell { font-size: 11px; color: var(--text-muted); }
.ws-cell { font-size: 13px; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.num-cell { font-size: 12px; color: var(--text-muted); }
.amount { font-weight: 700; color: var(--primary); }
.date-cell { font-size: 12px; color: var(--text-muted); }

.pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.pill--green  { background: color-mix(in oklab, #22c55e 12%, transparent); color: #16a34a; }
.pill--gray   { background: color-mix(in oklab, #6b7280 12%, transparent); color: #6b7280; }
.pill--purple { background: color-mix(in oklab, #8b5cf6 12%, transparent); color: #7c3aed; }

.pagination { display: flex; gap: 6px; margin-top: 16px; justify-content: center; flex-wrap: wrap; }
.page-btn { padding: 6px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-default); background: var(--bg-card); color: var(--text-primary); font-size: 13px; cursor: pointer; transition: background var(--dur-fast); }
.page-btn:disabled { opacity: 0.4; cursor: default; }
.page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn:hover:not(:disabled):not(.active) { background: var(--bg-muted); }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; font-family: var(--font-arabic); cursor: pointer; border: none; transition: background var(--dur-fast); }
.btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border-default); }
.btn-ghost:hover { background: var(--bg-muted); color: var(--text-primary); }
.btn--sm { padding: 7px 12px; font-size: 12px; }
</style>

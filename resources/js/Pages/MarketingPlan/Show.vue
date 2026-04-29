<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import { useConfirmStore } from '@/Stores/confirm'

const confirmStore = useConfirmStore()

interface PlanData {
    title: string
    executive_summary: string
    audience_analysis: {
        primary_segment: string
        pain_points: string[]
        buying_triggers: string[]
    }
    goals: Array<{ title: string; kpi: string; target: string; timeframe: string }>
    content_pillars: Array<{ name: string; description: string; percentage: number; examples: string[] }>
    platform_strategy: Array<{ platform: string; role: string; content_types: string[]; frequency: string; budget_pct: number; tips: string[] }>
    monthly_plan: Array<{
        month: number; theme: string; key_occasions: string[]
        focus: string
        weekly_breakdown: Array<{ week: number; theme: string; content_ideas: string[] }>
    }>
    budget_breakdown: Array<{ category: string; percentage: number; notes: string }>
    content_samples: Array<{ platform: string; type: string; caption: string; hashtags: string[] }>
    recommendations: string[]
}

interface Plan {
    id: number
    title: string
    status: string
    inputs: Record<string, any>
    plan: PlanData | null
    ai_provider: string | null
    ai_model: string | null
    cost_usd: string | null
    created_at: string
}

const props = defineProps<{ plan: Plan }>()

const data = computed(() => props.plan.plan)

const PLATFORM_COLORS: Record<string, string> = {
    instagram: '#E1306C', facebook: '#1877F2', tiktok: '#010101',
    snapchat: '#F7B731', x: '#333', انستجرام: '#E1306C', فيسبوك: '#1877F2',
    'تيك توك': '#010101', 'سناب شات': '#F7B731',
}

const BUDGET_COLORS = [
    '#0F6F5C', '#C8965F', '#7C3AED', '#0284C7',
    '#F59E0B', '#EF4444', '#EC4899', '#10B981',
]

const GOAL_LABELS: Record<string, string> = {
    awareness: 'زيادة الوعي', sales: 'زيادة المبيعات',
    engagement: 'تعزيز التفاعل', leads: 'توليد العملاء',
    retention: 'الاحتفاظ بالعملاء',
}
const DURATION_LABELS: Record<string, string> = {
    '1_month': 'شهر واحد', '3_months': '3 أشهر',
    '6_months': '6 أشهر', '12_months': 'سنة كاملة',
}

function printPlan() {
    window.print()
}

async function deletePlan() {
    const ok = await confirmStore.ask({
        title: 'حذف الخطة التسويقية؟',
        message: 'سيتم حذف هذه الخطة نهائياً ولا يمكن التراجع عن هذا الإجراء.',
        confirmText: 'حذف',
        dangerous: true,
    })
    if (!ok) return
    router.delete(`/marketing-plan/${props.plan.id}`)
}
</script>

<template>
    <AppLayout :title="plan.title" :crumbs="['الخطط التسويقية', plan.title]">
        <div class="plan-wrap">

            <!-- Plan header -->
            <div class="plan-header card">
                <div class="plan-header-left">
                    <div class="plan-header-icon">
                        <Icon name="sparkle" :size="24" />
                    </div>
                    <div>
                        <h1 class="plan-header-title">{{ plan.title }}</h1>
                        <div class="plan-header-meta">
                            <span v-if="plan.inputs?.goal" class="pmeta-tag green">
                                {{ GOAL_LABELS[plan.inputs.goal] ?? plan.inputs.goal }}
                            </span>
                            <span v-if="plan.inputs?.duration" class="pmeta-tag blue">
                                {{ DURATION_LABELS[plan.inputs.duration] ?? plan.inputs.duration }}
                            </span>
                            <span v-if="plan.inputs?.budget" class="pmeta-tag sand">
                                {{ Number(plan.inputs.budget).toLocaleString('ar') }} {{ plan.inputs.currency }}
                            </span>
                            <span v-if="plan.ai_model" class="pmeta-tag gray" dir="ltr">
                                {{ plan.ai_model }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="plan-header-actions">
                    <button class="btn btn-ghost" @click="printPlan" title="طباعة / تصدير PDF">
                        <Icon name="download" :size="16" />
                        تصدير PDF
                    </button>
                    <Link href="/marketing-plan/create" class="btn btn-primary">
                        <Icon name="sparkle" :size="16" />
                        خطة جديدة
                    </Link>
                    <button class="btn btn-ghost btn-danger-hover" @click="deletePlan" title="حذف">
                        <Icon name="trash" :size="16" />
                    </button>
                </div>
            </div>

            <div v-if="!data" class="no-plan card">
                <p>لا توجد بيانات خطة — ربما فشل التوليد</p>
                <Link href="/marketing-plan/create" class="btn btn-primary" style="margin-top:12px">إنشاء خطة جديدة</Link>
            </div>

            <template v-else>

                <!-- Executive Summary -->
                <div class="section-card card">
                    <div class="section-head">
                        <span class="section-num">01</span>
                        <h2>الملخص التنفيذي</h2>
                    </div>
                    <p class="summary-text">{{ data.executive_summary }}</p>
                </div>

                <!-- Audience Analysis -->
                <div class="section-card card" v-if="data.audience_analysis">
                    <div class="section-head">
                        <span class="section-num">02</span>
                        <h2>تحليل الجمهور المستهدف</h2>
                    </div>
                    <div class="audience-grid">
                        <div class="aud-segment">
                            <div class="aud-label">الشريحة الرئيسية</div>
                            <p>{{ data.audience_analysis.primary_segment }}</p>
                        </div>
                        <div>
                            <div class="aud-label">نقاط الألم</div>
                            <ul class="bullet-list">
                                <li v-for="p in data.audience_analysis.pain_points" :key="p">{{ p }}</li>
                            </ul>
                        </div>
                        <div>
                            <div class="aud-label">محفزات الشراء</div>
                            <ul class="bullet-list trigger">
                                <li v-for="t in data.audience_analysis.buying_triggers" :key="t">{{ t }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Goals -->
                <div class="section-card card" v-if="data.goals?.length">
                    <div class="section-head">
                        <span class="section-num">03</span>
                        <h2>الأهداف والمؤشرات</h2>
                    </div>
                    <div class="goals-grid">
                        <div v-for="(g, i) in data.goals" :key="i" class="goal-row">
                            <div class="goal-num">{{ i + 1 }}</div>
                            <div class="goal-content">
                                <div class="goal-title">{{ g.title }}</div>
                                <div class="goal-meta">
                                    <span class="gmeta">📊 {{ g.kpi }}</span>
                                    <span class="gmeta gmeta-target">🎯 {{ g.target }}</span>
                                    <span class="gmeta">⏰ {{ g.timeframe }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Pillars -->
                <div class="section-card card" v-if="data.content_pillars?.length">
                    <div class="section-head">
                        <span class="section-num">04</span>
                        <h2>ركائز المحتوى</h2>
                    </div>
                    <div class="pillars-grid">
                        <div v-for="(p, i) in data.content_pillars" :key="i" class="pillar-card">
                            <div class="pillar-bar-wrap">
                                <div
                                    class="pillar-bar"
                                    :style="{ width: p.percentage + '%', background: BUDGET_COLORS[i % BUDGET_COLORS.length] }"
                                />
                                <span class="pillar-pct">{{ p.percentage }}%</span>
                            </div>
                            <div class="pillar-name">{{ p.name }}</div>
                            <div class="pillar-desc">{{ p.description }}</div>
                            <ul class="pillar-examples">
                                <li v-for="ex in p.examples" :key="ex">{{ ex }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Platform Strategy -->
                <div class="section-card card" v-if="data.platform_strategy?.length">
                    <div class="section-head">
                        <span class="section-num">05</span>
                        <h2>استراتيجية المنصات</h2>
                    </div>
                    <div class="platforms-grid">
                        <div v-for="p in data.platform_strategy" :key="p.platform" class="platform-card">
                            <div
                                class="platform-header"
                                :style="{ background: (PLATFORM_COLORS[p.platform] ?? '#5E6A64') + '18', borderColor: PLATFORM_COLORS[p.platform] ?? '#5E6A64' }"
                            >
                                <span class="platform-name" :style="{ color: PLATFORM_COLORS[p.platform] ?? '#5E6A64' }">
                                    {{ p.platform }}
                                </span>
                                <span class="platform-budget">{{ p.budget_pct }}% من الميزانية</span>
                            </div>
                            <div class="platform-body">
                                <div class="plat-row">
                                    <span class="plat-label">الدور:</span>
                                    <span>{{ p.role }}</span>
                                </div>
                                <div class="plat-row">
                                    <span class="plat-label">نوع المحتوى:</span>
                                    <div class="content-types">
                                        <span v-for="ct in p.content_types" :key="ct" class="ct-tag">{{ ct }}</span>
                                    </div>
                                </div>
                                <div class="plat-row">
                                    <span class="plat-label">التردد:</span>
                                    <span>{{ p.frequency }}</span>
                                </div>
                                <ul class="plat-tips" v-if="p.tips?.length">
                                    <li v-for="t in p.tips" :key="t">{{ t }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Plan -->
                <div class="section-card card" v-if="data.monthly_plan?.length">
                    <div class="section-head">
                        <span class="section-num">06</span>
                        <h2>الخطة الشهرية</h2>
                    </div>
                    <div class="monthly-wrap">
                        <div v-for="m in data.monthly_plan" :key="m.month" class="month-block">
                            <div class="month-header">
                                <div class="month-num">الشهر {{ m.month }}</div>
                                <div class="month-theme">{{ m.theme }}</div>
                                <div class="month-focus">{{ m.focus }}</div>
                                <div v-if="m.key_occasions?.length" class="month-occasions">
                                    <span v-for="o in m.key_occasions" :key="o" class="occasion-chip">{{ o }}</span>
                                </div>
                            </div>
                            <div class="weeks-grid">
                                <div v-for="w in m.weekly_breakdown" :key="w.week" class="week-card">
                                    <div class="week-label">الأسبوع {{ w.week }} — {{ w.theme }}</div>
                                    <ul class="week-ideas">
                                        <li v-for="idea in w.content_ideas" :key="idea">{{ idea }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget -->
                <div class="section-card card" v-if="data.budget_breakdown?.length">
                    <div class="section-head">
                        <span class="section-num">07</span>
                        <h2>توزيع الميزانية</h2>
                    </div>
                    <div class="budget-grid">
                        <div v-for="(b, i) in data.budget_breakdown" :key="i" class="budget-item">
                            <div class="budget-bar-wrap">
                                <div class="budget-cat">{{ b.category }}</div>
                                <div class="budget-track">
                                    <div
                                        class="budget-fill"
                                        :style="{ width: b.percentage + '%', background: BUDGET_COLORS[i % BUDGET_COLORS.length] }"
                                    />
                                </div>
                                <div class="budget-pct" :style="{ color: BUDGET_COLORS[i % BUDGET_COLORS.length] }">{{ b.percentage }}%</div>
                            </div>
                            <div class="budget-notes">{{ b.notes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Content Samples -->
                <div class="section-card card" v-if="data.content_samples?.length">
                    <div class="section-head">
                        <span class="section-num">08</span>
                        <h2>نماذج محتوى جاهزة للاستخدام</h2>
                    </div>
                    <div class="samples-grid">
                        <div v-for="(s, i) in data.content_samples" :key="i" class="sample-card">
                            <div class="sample-header">
                                <span class="sample-platform" :style="{ color: PLATFORM_COLORS[s.platform] ?? '#5E6A64' }">
                                    {{ s.platform }}
                                </span>
                                <span class="sample-type">{{ s.type }}</span>
                            </div>
                            <p class="sample-caption">{{ s.caption }}</p>
                            <div class="sample-tags">
                                <span v-for="h in s.hashtags" :key="h" class="hash-tag">{{ h }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="section-card card" v-if="data.recommendations?.length">
                    <div class="section-head">
                        <span class="section-num">09</span>
                        <h2>التوصيات الاستراتيجية</h2>
                    </div>
                    <div class="recommendations-list">
                        <div v-for="(r, i) in data.recommendations" :key="i" class="rec-item">
                            <div class="rec-num">{{ i + 1 }}</div>
                            <p>{{ r }}</p>
                        </div>
                    </div>
                </div>

            </template>

            <!-- Footer nav -->
            <div class="footer-nav">
                <Link href="/marketing-plan" class="btn btn-ghost">
                    <Icon name="chevron-left" :size="14" style="transform:scaleX(1)" />
                    كل الخطط
                </Link>
                <Link href="/marketing-plan/create" class="btn btn-primary">
                    <Icon name="sparkle" :size="16" />
                    إنشاء خطة جديدة
                </Link>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.plan-wrap { max-width: 960px; margin: 0 auto; padding: 0 4px 60px; display: flex; flex-direction: column; gap: 20px; }

.card { background: var(--bg-card); border: 1px solid var(--border-default); border-radius: var(--radius-lg); padding: 24px 28px; }

/* Plan header */
.plan-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.plan-header-left { display: flex; align-items: flex-start; gap: 16px; flex: 1; min-width: 0; }
.plan-header-icon {
    width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500); display: grid; place-items: center;
}
.plan-header-title { font-size: 20px; font-weight: 700; margin: 0 0 10px; }
.plan-header-meta  { display: flex; gap: 8px; flex-wrap: wrap; }
.pmeta-tag {
    display: inline-flex; padding: 3px 10px; border-radius: var(--radius-full);
    font-size: 11px; font-weight: 600;
}
.pmeta-tag.green { background: #dcfce7; color: #15803d; }
.pmeta-tag.blue  { background: #dbeafe; color: #1d4ed8; }
.pmeta-tag.sand  { background: #fdf5ec; color: #a0714a; }
.pmeta-tag.gray  { background: var(--bg-subtle); color: var(--text-muted); font-family: monospace; border: 1px solid var(--border-default); }
.plan-header-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }
.btn-danger-hover:hover { color: #dc2626; }

.no-plan { text-align: center; padding: 40px; }

/* Sections */
.section-card { }
.section-head  { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-default); padding-bottom: 14px; }
.section-num   { font-size: 11px; font-weight: 800; color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 12%, transparent); padding: 3px 8px; border-radius: 4px; letter-spacing: 0.04em; }
.section-head h2 { font-size: 17px; font-weight: 700; margin: 0; }

/* Executive summary */
.summary-text { font-size: 14px; line-height: 1.9; color: var(--text-secondary); margin: 0; }

/* Audience */
.audience-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
.aud-segment   { grid-column: span 1; }
.aud-label     { font-size: 12px; font-weight: 700; color: var(--sada-500); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px; }
.aud-segment p { font-size: 13px; line-height: 1.7; margin: 0; color: var(--text-secondary); }
.bullet-list   { margin: 0; padding-right: 18px; display: flex; flex-direction: column; gap: 6px; }
.bullet-list li { font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
.bullet-list.trigger li::marker { content: "✦ "; color: var(--sand-500); }

/* Goals */
.goals-grid { display: flex; flex-direction: column; gap: 10px; }
.goal-row   { display: flex; align-items: flex-start; gap: 14px; padding: 14px; background: var(--bg-subtle); border-radius: var(--radius-md); }
.goal-num   { width: 28px; height: 28px; border-radius: 50%; background: var(--sada-500); color: #fff; font-size: 13px; font-weight: 700; display: grid; place-items: center; flex-shrink: 0; }
.goal-content { flex: 1; }
.goal-title { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
.goal-meta  { display: flex; gap: 12px; flex-wrap: wrap; }
.gmeta      { font-size: 12px; color: var(--text-muted); }
.gmeta-target { color: var(--sada-600); font-weight: 600; }

/* Content Pillars */
.pillars-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.pillar-card  { background: var(--bg-subtle); border-radius: var(--radius-md); padding: 16px; }
.pillar-bar-wrap { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.pillar-bar  { height: 6px; border-radius: 3px; flex-shrink: 0; }
.pillar-pct  { font-size: 13px; font-weight: 800; color: var(--text-primary); }
.pillar-name { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.pillar-desc { font-size: 12px; color: var(--text-muted); margin-bottom: 10px; line-height: 1.6; }
.pillar-examples { margin: 0; padding-right: 16px; display: flex; flex-direction: column; gap: 4px; }
.pillar-examples li { font-size: 12px; color: var(--text-secondary); }

/* Platform Strategy */
.platforms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.platform-card  { border: 1px solid var(--border-default); border-radius: var(--radius-md); overflow: hidden; }
.platform-header { padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-default); }
.platform-name   { font-weight: 700; font-size: 14px; }
.platform-budget { font-size: 11px; color: var(--text-muted); font-weight: 600; }
.platform-body  { padding: 14px 16px; display: flex; flex-direction: column; gap: 8px; }
.plat-row       { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; }
.plat-label     { font-weight: 700; color: var(--text-muted); flex-shrink: 0; }
.content-types  { display: flex; gap: 4px; flex-wrap: wrap; }
.ct-tag         { background: var(--bg-subtle); padding: 2px 8px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; color: var(--text-muted); border: 1px solid var(--border-default); }
.plat-tips      { margin: 4px 0 0 0; padding-right: 16px; display: flex; flex-direction: column; gap: 4px; }
.plat-tips li   { font-size: 12px; color: var(--text-muted); }

/* Monthly Plan */
.monthly-wrap { display: flex; flex-direction: column; gap: 24px; }
.month-block  { border: 1px solid var(--border-default); border-radius: var(--radius-md); overflow: hidden; }
.month-header { background: color-mix(in oklab, var(--sada-500) 6%, transparent); padding: 16px 20px; border-bottom: 1px solid var(--border-default); }
.month-num    { font-size: 11px; font-weight: 800; color: var(--sada-500); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.month-theme  { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.month-focus  { font-size: 13px; color: var(--text-muted); margin-bottom: 10px; }
.month-occasions { display: flex; gap: 6px; flex-wrap: wrap; }
.occasion-chip { font-size: 11px; font-weight: 600; padding: 2px 10px; border-radius: var(--radius-full); background: color-mix(in oklab, var(--sand-500, #C8965F) 15%, transparent); color: #a0714a; }
.weeks-grid   { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.week-card    { padding: 14px 16px; border-right: 1px solid var(--border-default); }
.week-card:first-child { border-right: none; }
.week-label   { font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 6px; }
.week-ideas   { margin: 0; padding-right: 14px; display: flex; flex-direction: column; gap: 5px; }
.week-ideas li { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }

/* Budget */
.budget-grid { display: flex; flex-direction: column; gap: 14px; }
.budget-item { }
.budget-bar-wrap { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; }
.budget-cat  { width: 160px; font-size: 13px; font-weight: 700; color: var(--text-primary); flex-shrink: 0; }
.budget-track { flex: 1; height: 8px; background: var(--bg-subtle); border-radius: 4px; overflow: hidden; }
.budget-fill { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
.budget-pct  { width: 40px; font-size: 14px; font-weight: 800; text-align: left; flex-shrink: 0; }
.budget-notes { font-size: 12px; color: var(--text-muted); padding-right: 172px; }

/* Content Samples */
.samples-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.sample-card  { border: 1px solid var(--border-default); border-radius: var(--radius-md); padding: 16px; }
.sample-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.sample-platform { font-weight: 700; font-size: 13px; }
.sample-type     { font-size: 11px; color: var(--text-muted); background: var(--bg-subtle); padding: 2px 8px; border-radius: var(--radius-full); border: 1px solid var(--border-default); }
.sample-caption  { font-size: 13px; line-height: 1.7; color: var(--text-secondary); margin: 0 0 10px; }
.sample-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.hash-tag    { font-size: 11px; color: var(--sada-500); background: color-mix(in oklab, var(--sada-500) 8%, transparent); padding: 2px 8px; border-radius: var(--radius-full); }

/* Recommendations */
.recommendations-list { display: flex; flex-direction: column; gap: 10px; }
.rec-item { display: flex; align-items: flex-start; gap: 14px; padding: 14px 16px; background: var(--bg-subtle); border-radius: var(--radius-md); }
.rec-num  { width: 28px; height: 28px; border-radius: 50%; background: var(--sand-500, #C8965F); color: #fff; font-size: 13px; font-weight: 700; display: grid; place-items: center; flex-shrink: 0; }
.rec-item p { font-size: 13px; color: var(--text-secondary); line-height: 1.7; margin: 0; }

/* Footer nav */
.footer-nav { display: flex; justify-content: space-between; align-items: center; padding-top: 8px; }

@media (max-width: 768px) {
    .card { padding: 18px; }
    .audience-grid { grid-template-columns: 1fr; }
    .weeks-grid    { grid-template-columns: repeat(2, 1fr); }
    .plan-header   { flex-direction: column; }
    .budget-notes  { padding-right: 0; }
    .budget-cat    { width: auto; }
}

@media print {
    .plan-header-actions, .footer-nav { display: none !important; }
    .plan-wrap { max-width: 100%; }
}
</style>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'
import Modal from '@/Components/Base/Modal.vue'

interface PlanSummary {
    id: number
    title: string
    status: 'generating' | 'completed' | 'failed'
    goal: string | null
    duration: string | null
    platforms: string[]
    cost_usd: string | null
    created_at: string
}

defineProps<{ plans: PlanSummary[] }>()

const GOAL_LABELS: Record<string, string> = {
    awareness:  'زيادة الوعي',
    sales:      'زيادة المبيعات',
    engagement: 'تعزيز التفاعل',
    leads:      'توليد العملاء',
    retention:  'الاحتفاظ بالعملاء',
}
const DURATION_LABELS: Record<string, string> = {
    '1_month':   'شهر واحد',
    '3_months':  '3 أشهر',
    '6_months':  '6 أشهر',
    '12_months': 'سنة كاملة',
}
const PLATFORM_LABELS: Record<string, string> = {
    instagram: 'انستجرام',
    facebook:  'فيسبوك',
    tiktok:    'تيك توك',
    snapchat:  'سناب شات',
    x:         'إكس',
}

// ── Delete modal ───────────────────────────────────────────────────────────────
const showDeleteModal  = ref(false)
const deleteTarget     = ref<PlanSummary | null>(null)
const deleteProcessing = ref(false)

function openDelete(plan: PlanSummary) {
    deleteTarget.value = plan
    showDeleteModal.value = true
}
function closeDelete() {
    showDeleteModal.value = false
    deleteTarget.value = null
}
function confirmDelete() {
    if (!deleteTarget.value) return
    deleteProcessing.value = true
    router.delete(`/marketing-plan/${deleteTarget.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false
            closeDelete()
        },
    })
}

function timeAgo(iso: string): string {
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000)
    if (diff < 3600)  return Math.floor(diff / 60) + ' دقيقة مضت'
    if (diff < 86400) return Math.floor(diff / 3600) + ' ساعة مضت'
    return Math.floor(diff / 86400) + ' يوم مضت'
}
</script>

<template>
    <AppLayout title="الخطط التسويقية" :crumbs="['الخطط التسويقية']">
        <div class="page-wrap">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">الخطط التسويقية</h1>
                    <p class="page-sub">خطط تسويقية مخصصة بالذكاء الاصطناعي للسوق الخليجي</p>
                </div>
                <Link href="/marketing-plan/create" class="btn btn-primary">
                    <Icon name="sparkle" :size="16" />
                    إنشاء خطة جديدة
                </Link>
            </div>

            <!-- Empty state -->
            <div v-if="plans.length === 0" class="empty-hero">
                <div class="empty-icon">
                    <Icon name="sparkle" :size="36" />
                </div>
                <h2>لا توجد خطط تسويقية بعد</h2>
                <p>أنشئ خطتك الأولى وسيقوم الذكاء الاصطناعي بإنشاء خطة تسويقية شاملة مخصصة لعملك</p>
                <Link href="/marketing-plan/create" class="btn btn-primary btn-lg">
                    <Icon name="sparkle" :size="18" />
                    إنشاء أول خطة تسويقية
                </Link>
            </div>

            <!-- Plans grid -->
            <div v-else class="plans-grid">
                <div v-for="plan in plans" :key="plan.id" class="plan-card">
                    <!-- Status indicator -->
                    <div class="plan-status-bar" :class="plan.status" />

                    <div class="plan-body">
                        <div class="plan-head">
                            <div class="plan-icon">
                                <Icon name="sparkle" :size="20" />
                            </div>
                            <div class="plan-meta">
                                <div class="plan-title">{{ plan.title }}</div>
                                <div class="plan-time">{{ timeAgo(plan.created_at) }}</div>
                            </div>
                            <div v-if="plan.status === 'failed'" class="plan-failed-badge">فشل</div>
                        </div>

                        <div class="plan-tags">
                            <span v-if="plan.goal" class="ptag ptag-green">
                                {{ GOAL_LABELS[plan.goal] ?? plan.goal }}
                            </span>
                            <span v-if="plan.duration" class="ptag ptag-blue">
                                {{ DURATION_LABELS[plan.duration] ?? plan.duration }}
                            </span>
                            <span v-for="p in plan.platforms.slice(0, 3)" :key="p" class="ptag ptag-gray">
                                {{ PLATFORM_LABELS[p] ?? p }}
                            </span>
                        </div>
                    </div>

                    <div class="plan-footer">
                        <Link
                            v-if="plan.status === 'completed'"
                            :href="`/marketing-plan/${plan.id}`"
                            class="btn btn-sm btn-primary"
                        >
                            عرض الخطة
                        </Link>
                        <span v-else-if="plan.status === 'generating'" class="generating-badge">
                            <span class="spin-dot" />
                            جارٍ التوليد...
                        </span>
                        <button class="btn btn-sm btn-ghost btn-danger" @click="openDelete(plan)" title="حذف">
                            <Icon name="trash" :size="14" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" title="تأكيد الحذف" size="sm" @close="closeDelete">
            <div class="delete-confirm">
                <div class="delete-confirm-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                <p class="delete-confirm-msg">
                    هل تريد حذف خطة <strong>{{ deleteTarget?.title }}</strong> نهائياً؟
                    لا يمكن التراجع عن هذا الإجراء.
                </p>
            </div>
            <template #footer>
                <button class="btn btn-danger" :disabled="deleteProcessing" @click="confirmDelete">
                    <span v-if="deleteProcessing" class="delete-spin" />
                    {{ deleteProcessing ? 'جارٍ الحذف...' : 'حذف الخطة' }}
                </button>
                <button class="btn btn-secondary" :disabled="deleteProcessing" @click="closeDelete">إلغاء</button>
            </template>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.page-wrap   { max-width: 1100px; margin: 0 auto; padding: 0 4px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; gap: 16px; flex-wrap: wrap; }
.page-title  { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.page-sub    { font-size: 13px; color: var(--text-muted); margin: 0; }

.empty-hero {
    text-align: center; padding: 72px 20px;
    background: var(--bg-card); border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
}
.empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500);
    display: grid; place-items: center; margin: 0 auto 20px;
}
.empty-hero h2  { font-size: 20px; font-weight: 700; margin: 0 0 10px; }
.empty-hero p   { font-size: 14px; color: var(--text-muted); margin: 0 auto 24px; max-width: 400px; line-height: 1.7; }
.btn-lg { padding: 12px 28px; font-size: 15px; gap: 10px; }

.plans-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.plan-card  {
    background: var(--bg-card); border: 1px solid var(--border-default);
    border-radius: var(--radius-md); overflow: hidden;
    display: flex; flex-direction: column;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.plan-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); border-color: var(--sada-200, #a8d5c5); }

.plan-status-bar { height: 3px; }
.plan-status-bar.completed  { background: var(--sada-500); }
.plan-status-bar.generating { background: #f59e0b; }
.plan-status-bar.failed     { background: #ef4444; }

.plan-body   { padding: 18px 18px 14px; flex: 1; }
.plan-head   { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.plan-icon   {
    width: 40px; height: 40px; border-radius: var(--radius-md); flex-shrink: 0;
    background: color-mix(in oklab, var(--sada-500) 12%, transparent);
    color: var(--sada-500); display: grid; place-items: center;
}
.plan-meta   { flex: 1; min-width: 0; }
.plan-title  { font-weight: 700; font-size: 14px; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.plan-time   { font-size: 11px; color: var(--text-muted); }
.plan-failed-badge { font-size: 10px; font-weight: 700; color: #ef4444; background: #fee2e2; padding: 2px 8px; border-radius: var(--radius-full); }

.plan-tags  { display: flex; gap: 6px; flex-wrap: wrap; }
.ptag       { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: var(--radius-full); font-size: 11px; font-weight: 600; }
.ptag-green { background: #dcfce7; color: #15803d; }
.ptag-blue  { background: #dbeafe; color: #1d4ed8; }
.ptag-gray  { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-default); }

.plan-footer { padding: 12px 18px; border-top: 1px solid var(--border-default); display: flex; align-items: center; gap: 8px; }
.generating-badge { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #f59e0b; font-weight: 600; }
.spin-dot { width: 8px; height: 8px; border-radius: 50%; border: 2px solid #f59e0b; border-top-color: transparent; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.btn-danger:hover { color: #dc2626; }

.delete-confirm { display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 8px 0; text-align: center; }
.delete-confirm-icon {
    width: 60px; height: 60px; border-radius: 50%;
    background: #fee2e2; color: #dc2626;
    display: grid; place-items: center;
}
.delete-confirm-msg { font-size: 14px; color: var(--text-secondary); line-height: 1.7; margin: 0; }
.delete-confirm-msg strong { color: var(--text-primary); }
.delete-spin { width: 14px; height: 14px; border-radius: 50%; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; animation: spin 0.8s linear infinite; display: inline-block; }
</style>

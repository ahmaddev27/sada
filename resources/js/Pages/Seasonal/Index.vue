<script setup lang="ts">
// SE-01→SE-08: Seasonal Engine
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface CountdownDetail {
    days: number
    hours: number
    minutes: number
}

interface Occasion {
    key: string
    name: string
    subtitle: string
    date: string
    end_date: string | null
    icon: string
    color: string
    countries: string[]
    templates: number
    featured: boolean
    hashtags: string[]
    days_until: number
    status: 'active' | 'upcoming' | 'passed'
    countdown: string
    countdown_detail?: CountdownDetail
}

interface TodayDates {
    gregorian: string
    hijri: string
}

const props = defineProps<{
    occasions: Occasion[]
    upcoming: Occasion | null
    featured: Occasion[]
    country: string
    today: TodayDates
}>()

// ── Filters ──────────────────────────────────────────────────
const selectedCountry = ref(props.country)
const filterStatus    = ref<'all' | 'active' | 'upcoming' | 'passed'>('all')

const COUNTRY_OPTIONS = [
    { value: 'all', label: 'كل الخليج' },
    { value: 'sa',  label: 'السعودية' },
    { value: 'ae',  label: 'الإمارات' },
    { value: 'kw',  label: 'الكويت' },
    { value: 'qa',  label: 'قطر' },
    { value: 'bh',  label: 'البحرين' },
    { value: 'om',  label: 'عُمان' },
]

function applyCountry(c: string) {
    selectedCountry.value = c
    router.get('/seasonal', { country: c }, { preserveState: true, replace: true })
}

const filtered = computed(() =>
    props.occasions.filter(o =>
        filterStatus.value === 'all' || o.status === filterStatus.value
    )
)

const counts = computed(() => ({
    all:      props.occasions.length,
    active:   props.occasions.filter(o => o.status === 'active').length,
    upcoming: props.occasions.filter(o => o.status === 'upcoming').length,
    passed:   props.occasions.filter(o => o.status === 'passed').length,
}))

// ── Generate ─────────────────────────────────────────────────
function generateFor(key: string) {
    window.location.href = `/seasonal/${key}/generate`
}
</script>

<template>
    <AppLayout title="المواسم">
        <div class="stack-lg">

            <!-- Hero: next upcoming occasion -->
            <div v-if="upcoming" class="seasonal-hero">
                <!-- Islamic geometric pattern -->
                <svg class="seasonal-hero-pattern" aria-hidden="true">
                    <defs>
                        <pattern id="islamic-geo" width="60" height="60" patternUnits="userSpaceOnUse">
                            <path d="M30 0 L60 30 L30 60 L0 30 Z M30 15 L45 30 L30 45 L15 30 Z" fill="none" stroke="#fff" stroke-width="0.8" />
                            <circle cx="30" cy="30" r="4" fill="none" stroke="#fff" stroke-width="0.6" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#islamic-geo)" />
                </svg>

                <!-- Dual calendar badge -->
                <div class="seasonal-calendar-badge">
                    <div class="cal-item">
                        <div class="cal-label">هجري</div>
                        <div class="cal-val">{{ today.hijri }}</div>
                    </div>
                    <div class="cal-divider" />
                    <div class="cal-item">
                        <div class="cal-label">ميلادي</div>
                        <div class="cal-val">{{ today.gregorian }}</div>
                    </div>
                </div>

                <div class="seasonal-hero-content">
                    <div class="seasonal-hero-eyebrow">
                        {{ upcoming.status === 'active' ? 'جارٍ الآن' : 'المناسبة القادمة' }}
                    </div>
                    <h2 class="seasonal-hero-title">{{ upcoming.name }}</h2>
                    <p class="seasonal-hero-sub">{{ upcoming.subtitle }}</p>

                    <!-- Countdown -->
                    <div v-if="upcoming.countdown_detail && upcoming.status === 'upcoming'" class="seasonal-countdown">
                        <div class="countdown-block">
                            <div class="countdown-num">{{ String(upcoming.countdown_detail.days).padStart(2, '0') }}</div>
                            <div class="countdown-label">يوم</div>
                        </div>
                        <div class="countdown-block">
                            <div class="countdown-num">{{ String(upcoming.countdown_detail.hours).padStart(2, '0') }}</div>
                            <div class="countdown-label">ساعة</div>
                        </div>
                        <div class="countdown-block">
                            <div class="countdown-num">{{ String(upcoming.countdown_detail.minutes).padStart(2, '0') }}</div>
                            <div class="countdown-label">دقيقة</div>
                        </div>
                    </div>
                    <div v-else class="seasonal-active-badge">● جارٍ الآن</div>

                    <div class="seasonal-hero-actions">
                        <button class="btn btn-lg seasonal-btn-primary" @click="generateFor(upcoming.key)">
                            <Icon name="sparkle" :size="16" /> ولّد حملة جاهزة
                        </button>
                        <button class="btn btn-lg seasonal-btn-ghost">
                            تصفح {{ upcoming.templates }} قالباً
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters row -->
            <div class="seasonal-filters-row">
                <!-- Status tabs -->
                <div class="segmented">
                    <button
                        v-for="f in [
                            { id: 'all',      label: 'الكل',     count: counts.all },
                            { id: 'active',   label: 'جارٍ',     count: counts.active },
                            { id: 'upcoming', label: 'قادمة',    count: counts.upcoming },
                            { id: 'passed',   label: 'انتهت',    count: counts.passed },
                        ]"
                        :key="f.id"
                        :data-active="filterStatus === f.id"
                        @click="filterStatus = (f.id as any)"
                    >
                        {{ f.label }}
                        <span v-if="f.count" class="seg-count">{{ f.count }}</span>
                    </button>
                </div>

                <!-- Country chips -->
                <div class="country-chips">
                    <button
                        v-for="c in COUNTRY_OPTIONS"
                        :key="c.value"
                        class="chip"
                        :data-active="selectedCountry === c.value"
                        @click="applyCountry(c.value)"
                    >{{ c.label }}</button>
                </div>
            </div>

            <!-- Section header -->
            <div class="section-head">
                <div>
                    <h2 style="margin:0;font-size:20px;font-weight:800">مناسبات خليجية</h2>
                    <div class="sub">كل موسم مرفق بقوالب جاهزة، هاشتاجات، وأفكار بصرية</div>
                </div>
            </div>

            <!-- Occasions grid -->
            <div class="seasonal-grid">
                <div
                    v-for="o in filtered"
                    :key="o.key"
                    class="season-card"
                    :class="{ 'season-card--passed': o.status === 'passed' }"
                >
                    <!-- Card header band -->
                    <div class="season-card-band" :style="{ background: `linear-gradient(135deg, ${o.color}, color-mix(in oklab, ${o.color} 70%, #0E1512))` }">
                        <!-- Dot pattern -->
                        <svg class="band-pattern" aria-hidden="true">
                            <defs>
                                <pattern :id="`bp-${o.key}`" width="24" height="24" patternUnits="userSpaceOnUse">
                                    <circle cx="12" cy="12" r="1.2" fill="#fff" />
                                    <path d="M12 3 L21 12 L12 21 L3 12 Z" fill="none" stroke="#fff" stroke-width="0.4" />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" :fill="`url(#bp-${o.key})`" />
                        </svg>

                        <Icon :name="o.icon" :size="24" style="color:#fff;position:relative" />
                        <span v-if="o.featured" class="featured-badge">مميّز</span>
                        <span v-if="o.status === 'active'" class="active-dot" />
                    </div>

                    <!-- Card body -->
                    <div class="season-card-body">
                        <div class="season-name">{{ o.name }}</div>
                        <div class="season-date">{{ o.date }}</div>

                        <!-- Hashtags preview -->
                        <div class="season-tags">
                            <span
                                v-for="tag in o.hashtags.slice(0, 2)"
                                :key="tag"
                                class="badge badge-neutral"
                                style="font-size:10px"
                            >{{ tag }}</span>
                            <span v-if="o.hashtags.length > 2" class="badge badge-neutral" style="font-size:10px">
                                +{{ o.hashtags.length - 2 }}
                            </span>
                        </div>

                        <div class="season-footer">
                            <div class="season-countdown" :style="{ color: o.status === 'active' ? o.color : 'var(--accent)' }">
                                {{ o.countdown }}
                            </div>
                            <div class="season-templates">{{ o.templates }} قالب</div>
                        </div>

                        <button
                            class="btn btn-sm btn-secondary"
                            style="width:100%;margin-top:12px"
                            :disabled="o.status === 'passed'"
                            @click="generateFor(o.key)"
                        >
                            <Icon name="sparkle" :size="13" />
                            ولّد محتوى
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="filtered.length === 0" style="padding:60px;text-align:center;color:var(--text-muted)">
                <Icon name="moon" :size="32" style="opacity:0.3;margin-bottom:12px" />
                <p style="margin:0;font-size:14px">لا توجد مناسبات للفلتر المحدد.</p>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Hero ──────────────────────────────────────────────── */
.seasonal-hero {
    position: relative;
    border-radius: 20px;
    padding: 36px 40px;
    background: linear-gradient(120deg, var(--sada-700), var(--sada-500) 55%, var(--sada-400));
    color: #fff;
    overflow: hidden;
    min-height: 280px;
}
.seasonal-hero-pattern {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.12;
    pointer-events: none;
}
.seasonal-calendar-badge {
    position: absolute;
    top: 24px;
    left: 32px;
    display: flex;
    gap: 20px;
    background: rgba(255,255,255,0.12);
    padding: 10px 16px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
    font-size: 12px;
}
.cal-item { text-align: center; }
.cal-label { font-size: 10px; opacity: 0.8; margin-bottom: 2px; }
.cal-val   { font-weight: 700; font-size: 13px; }
.cal-divider { width: 1px; background: rgba(255,255,255,0.3); }

.seasonal-hero-content { position: relative; max-width: 620px; }
.seasonal-hero-eyebrow { font-size: 12px; font-weight: 600; opacity: 0.85; letter-spacing: 0.05em; margin-bottom: 8px; }
.seasonal-hero-title   { margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.15; }
.seasonal-hero-sub     { font-size: 14px; opacity: 0.9; margin: 6px 0 0; }

.seasonal-countdown {
    display: flex;
    gap: 16px;
    margin-top: 24px;
}
.countdown-block {
    background: rgba(255,255,255,0.15);
    padding: 14px 20px;
    border-radius: 12px;
    text-align: center;
    min-width: 84px;
    backdrop-filter: blur(8px);
}
.countdown-num   { font-size: 32px; font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1; }
.countdown-label { font-size: 11px; opacity: 0.85; margin-top: 4px; }

.seasonal-active-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 20px;
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 99px;
    font-size: 14px;
    font-weight: 700;
    backdrop-filter: blur(8px);
}

.seasonal-hero-actions {
    display: flex;
    gap: 10px;
    margin-top: 28px;
}
.seasonal-btn-primary { background: #fff; color: var(--sada-700); font-weight: 700; }
.seasonal-btn-ghost   { color: #fff; border: 1px solid rgba(255,255,255,0.3); }
.seasonal-btn-primary:hover { background: rgba(255,255,255,0.9); }
.seasonal-btn-ghost:hover   { background: rgba(255,255,255,0.1); }

/* ── Filters ─────────────────────────────────────────── */
.seasonal-filters-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.country-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-right: auto;
}
.seg-count {
    font-size: 10px;
    background: var(--border-subtle);
    border-radius: 99px;
    padding: 1px 6px;
    margin-right: 4px;
}
[data-active="true"] .seg-count {
    background: rgba(255,255,255,0.25);
}

/* ── Section head ────────────────────────────────────── */
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sub { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

/* ── Grid ────────────────────────────────────────────── */
.seasonal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

/* ── Season Card ─────────────────────────────────────── */
.season-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform var(--dur-fast), box-shadow var(--dur-fast);
}
.season-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.season-card--passed { opacity: 0.55; }

.season-card-band {
    height: 100px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    padding: 12px;
}
.band-pattern {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.25;
}
.featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 10px;
    font-weight: 700;
    background: rgba(255,255,255,0.25);
    color: #fff;
    padding: 3px 8px;
    border-radius: 99px;
    backdrop-filter: blur(8px);
}
.active-dot {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 0 3px rgba(74,222,128,0.3);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(74,222,128,0.3); }
    50%       { box-shadow: 0 0 0 6px rgba(74,222,128,0.1); }
}

.season-card-body { padding: 16px; }
.season-name      { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.season-date      { font-size: 12px; color: var(--text-muted); margin-bottom: 10px; }
.season-tags      { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 12px; }
.season-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid var(--border-subtle);
}
.season-countdown  { font-size: 12px; font-weight: 700; }
.season-templates  { font-size: 12px; color: var(--text-muted); }

@media (max-width: 700px) {
    .seasonal-hero { padding: 24px 20px; min-height: auto; }
    .seasonal-hero-title { font-size: 24px; }
    .seasonal-calendar-badge { display: none; }
    .countdown-block { min-width: 64px; padding: 10px 12px; }
    .countdown-num { font-size: 24px; }
    .seasonal-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .seasonal-grid { grid-template-columns: 1fr; }
}
</style>

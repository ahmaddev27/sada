<script setup lang="ts">
// SCH-07: content calendar
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Icon from '@/Components/Base/Icon.vue'

interface CalendarPost {
    id: number
    content: string
    platform: string
    content_type: string
    status: string
    scheduled_for: string | null
    hashtags: string[] | null
}

interface DraftPost {
    id: number
    content: string
    platform: string
    content_type: string
    status: string
    created_at: string
}

const props = defineProps<{
    posts: CalendarPost[]
    drafts: DraftPost[]
    year: number
    month: number
}>()

// ── Navigation ──────────────────────────────────────────────
function navigate(delta: number) {
    let m = props.month + delta
    let y = props.year
    if (m < 1)  { m = 12; y-- }
    if (m > 12) { m = 1;  y++ }
    router.get('/calendar', { year: y, month: m }, { preserveState: false })
}

function goToday() {
    router.get('/calendar', {}, { preserveState: false })
}

// ── Filter ──────────────────────────────────────────────────
const filterStatus   = ref<string>('all')
const filterPlatform = ref<string>('all')
const calView        = ref<'month' | 'list'>('month')

const filteredPosts = computed(() => props.posts.filter(p => {
    if (filterStatus.value   !== 'all' && p.status   !== filterStatus.value)   return false
    if (filterPlatform.value !== 'all' && p.platform !== filterPlatform.value) return false
    return true
}))

// ── Calendar grid ───────────────────────────────────────────
const ARABIC_MONTHS = [
    '', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
]
const DAYS_OF_WEEK = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']

const monthLabel = computed(() => `${ARABIC_MONTHS[props.month]} ${props.year}`)

const cells = computed(() => {
    const daysInMonth = new Date(props.year, props.month, 0).getDate()
    const firstDay    = new Date(props.year, props.month - 1, 1).getDay()
    const arr: (number | null)[] = []
    for (let i = 0; i < firstDay; i++) arr.push(null)
    for (let d = 1; d <= daysInMonth; d++) arr.push(d)
    while (arr.length % 7 !== 0) arr.push(null)
    return arr
})

function postsForDay(day: number): CalendarPost[] {
    return filteredPosts.value.filter(p => {
        if (!p.scheduled_for) return false
        const d = new Date(p.scheduled_for)
        return d.getFullYear() === props.year &&
               d.getMonth() + 1 === props.month &&
               d.getDate() === day
    })
}

const today = new Date()
function isToday(day: number): boolean {
    return today.getFullYear() === props.year &&
           today.getMonth() + 1 === props.month &&
           today.getDate() === day
}

// ── Status counts ───────────────────────────────────────────
const counts = computed(() => ({
    all:       props.posts.length,
    scheduled: props.posts.filter(p => p.status === 'scheduled').length,
    draft:     props.drafts.length,
    published: props.posts.filter(p => p.status === 'published').length,
}))

// ── Platform colours ────────────────────────────────────────
const PLATFORM_COLORS: Record<string, string> = {
    instagram: 'var(--sada-500)',
    facebook:  'var(--info)',
    tiktok:    'var(--ink-700)',
    snapchat:  'var(--warning)',
    x:         'var(--ink-500)',
}

function platformColor(platform: string): string {
    return PLATFORM_COLORS[platform] ?? 'var(--sada-500)'
}

function platformLabel(p: string): string {
    const map: Record<string, string> = {
        instagram: 'انستجرام', facebook: 'فيسبوك',
        tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X',
    }
    return map[p] ?? p
}

function statusLabel(s: string): string {
    const map: Record<string, string> = { scheduled: 'مجدول', published: 'منشور', failed: 'فشل', draft: 'مسودة' }
    return map[s] ?? s
}

function statusBadge(s: string): string {
    const map: Record<string, string> = {
        scheduled: 'badge-info', published: 'badge-success',
        failed: 'badge-error', draft: 'badge-neutral',
    }
    return map[s] ?? 'badge-neutral'
}

function formatTime(dt: string | null): string {
    if (!dt) return ''
    return new Date(dt).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}

// ── Delete ──────────────────────────────────────────────────
function deletePost(id: number) {
    if (!confirm('حذف هذا المنشور؟')) return
    router.delete(`/posts/${id}`, { preserveState: false })
}
</script>

<template>
    <AppLayout title="التقويم">
        <div class="stack">
            <div class="cal-grid">
                <!-- Sidebar filters -->
                <div class="stack">
                    <div class="card">
                        <div class="card-head"><h3>فلاتر</h3></div>
                        <div class="card-body stack-sm">
                            <div class="stack-sm">
                                <div style="font-size:12px;font-weight:600;color:var(--text-muted)">الحالة</div>
                                <button
                                    v-for="f in [
                                        { id: 'all',       label: 'الكل',    count: counts.all },
                                        { id: 'scheduled', label: 'مجدولة',  count: counts.scheduled, dot: 'var(--info)' },
                                        { id: 'draft',     label: 'مسودة',   count: counts.draft,     dot: 'var(--warning)' },
                                        { id: 'published', label: 'منشورة',  count: counts.published, dot: 'var(--success)' },
                                    ]"
                                    :key="f.id"
                                    class="cal-filter-item"
                                    :data-active="filterStatus === f.id"
                                    @click="filterStatus = f.id"
                                >
                                    <span v-if="f.dot" class="dot" :style="{ background: f.dot }" />
                                    <span v-else style="width:7px;display:inline-block" />
                                    <span>{{ f.label }}</span>
                                    <span class="cal-filter-count">{{ f.count }}</span>
                                </button>
                            </div>

                            <hr class="divider" />

                            <div class="stack-sm">
                                <div style="font-size:12px;font-weight:600;color:var(--text-muted)">المنصة</div>
                                <label
                                    v-for="p in ['all','instagram','facebook','tiktok','snapchat','x']"
                                    :key="p"
                                    class="cal-platform-label"
                                >
                                    <input
                                        type="radio"
                                        :value="p"
                                        v-model="filterPlatform"
                                        style="accent-color:var(--accent)"
                                    />
                                    <Icon :name="p === 'all' ? 'sparkle' : p" :size="14" />
                                    {{ p === 'all' ? 'الكل' : platformLabel(p) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Drafts panel -->
                    <div class="card" v-if="drafts.length">
                        <div class="card-head"><h3>المسودات</h3><span class="badge badge-neutral">{{ drafts.length }}</span></div>
                        <div class="card-body stack-sm">
                            <div
                                v-for="d in drafts.slice(0, 5)"
                                :key="d.id"
                                class="draft-item"
                            >
                                <Icon :name="d.platform" :size="13" style="color:var(--text-muted);flex-shrink:0" />
                                <span class="draft-title">{{ d.content.slice(0, 45) }}...</span>
                            </div>
                            <a href="/generate" class="btn btn-sm btn-ghost" style="width:100%;justify-content:center;margin-top:4px">
                                + توليد محتوى
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calendar main -->
                <div class="card" style="padding:0;overflow:hidden">
                    <!-- Header -->
                    <div class="card-head" style="gap:12px;padding:16px 20px">
                        <div style="display:flex;align-items:center;gap:12px">
                            <button class="btn btn-icon btn-icon-sm btn-ghost" @click="navigate(-1)">
                                <Icon name="chevronRight" :size="16" />
                            </button>
                            <h3 style="font-size:18px;min-width:140px;text-align:center">{{ monthLabel }}</h3>
                            <button class="btn btn-icon btn-icon-sm btn-ghost" @click="navigate(1)">
                                <Icon name="chevronLeft" :size="16" />
                            </button>
                            <button class="btn btn-sm btn-ghost" style="margin-right:8px" @click="goToday">اليوم</button>
                        </div>
                        <div style="display:flex;gap:8px">
                            <div class="segmented">
                                <button :data-active="calView === 'month'" @click="calView = 'month'">شهري</button>
                                <button :data-active="calView === 'list'"  @click="calView = 'list'">قائمة</button>
                            </div>
                            <a href="/generate" class="btn btn-sm btn-primary">
                                <Icon name="plus" :size="14" /> منشور جديد
                            </a>
                        </div>
                    </div>

                    <!-- Month view -->
                    <template v-if="calView === 'month'">
                        <!-- Day-of-week headers -->
                        <div class="cal-day-headers">
                            <div v-for="d in DAYS_OF_WEEK" :key="d" class="cal-day-header">{{ d }}</div>
                        </div>

                        <!-- Grid cells -->
                        <div class="cal-grid-cells">
                            <div
                                v-for="(day, idx) in cells"
                                :key="idx"
                                class="cal-cell"
                                :class="{
                                    'cal-cell--empty': !day,
                                    'cal-cell--border-right': idx % 7 < 6,
                                    'cal-cell--border-bottom': Math.floor(idx / 7) < Math.floor((cells.length - 1) / 7),
                                }"
                            >
                                <template v-if="day">
                                    <div class="cal-cell-head">
                                        <span
                                            class="cal-day-num"
                                            :class="{ 'cal-day-num--today': isToday(day) }"
                                        >{{ day }}</span>
                                    </div>
                                    <div class="cal-cell-posts">
                                        <div
                                            v-for="(post, pi) in postsForDay(day).slice(0, 2)"
                                            :key="post.id"
                                            class="cal-post-chip"
                                            :style="{ '--chip-color': platformColor(post.platform) }"
                                            :title="post.content.slice(0, 80)"
                                        >
                                            <span class="dot" :style="{ background: platformColor(post.platform), width: '5px', height: '5px' }" />
                                            {{ post.content.slice(0, 22) }}…
                                        </div>
                                        <div
                                            v-if="postsForDay(day).length > 2"
                                            class="cal-more"
                                        >+{{ postsForDay(day).length - 2 }} أخرى</div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- List view -->
                    <template v-else>
                        <div class="stack-sm" style="padding:20px">
                            <div
                                v-if="filteredPosts.length === 0"
                                style="padding:40px;text-align:center;color:var(--text-muted)"
                            >لا توجد منشورات لهذا الشهر</div>

                            <div
                                v-for="post in filteredPosts"
                                :key="post.id"
                                class="cal-list-item"
                            >
                                <div class="cal-list-time">
                                    {{ formatTime(post.scheduled_for) }}
                                </div>
                                <div class="cal-list-dot" :style="{ background: platformColor(post.platform) }" />
                                <div class="cal-list-body">
                                    <div class="cal-list-title">{{ post.content.slice(0, 60) }}…</div>
                                    <div class="cal-list-meta">
                                        <Icon :name="post.platform" :size="12" />
                                        {{ platformLabel(post.platform) }} ·
                                        <span :class="['badge', statusBadge(post.status)]" style="font-size:10px;padding:1px 6px">
                                            {{ statusLabel(post.status) }}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    class="btn btn-icon btn-icon-sm btn-ghost"
                                    style="color:var(--error)"
                                    @click="deletePost(post.id)"
                                    title="حذف"
                                >
                                    <Icon name="trash" :size="14" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cal-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 20px;
    align-items: start;
}

.cal-filter-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
    text-align: right;
    background: transparent;
    color: var(--text-secondary);
    border: none;
    transition: background var(--dur-fast);
}
.cal-filter-item[data-active="true"] {
    background: var(--accent-soft);
    color: var(--accent-text);
    font-weight: 600;
}
.cal-filter-count {
    margin-right: auto;
    font-size: 11px;
    color: var(--text-muted);
}
.cal-platform-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    cursor: pointer;
    color: var(--text-secondary);
}
.draft-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    background: var(--bg-muted);
}
.draft-title {
    font-size: 12px;
    color: var(--text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cal-day-headers {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border-bottom: 1px solid var(--border-subtle);
}
.cal-day-header {
    padding: 10px 12px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    text-align: center;
    border-left: 1px solid var(--border-subtle);
}
.cal-day-header:last-child { border-left: none; }

.cal-grid-cells {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}
.cal-cell {
    min-height: 110px;
    padding: 8px;
    background: var(--bg-surface);
    transition: background var(--dur-fast);
}
.cal-cell--empty { background: var(--bg-muted); }
.cal-cell--border-right  { border-left: 1px solid var(--border-subtle); }
.cal-cell--border-bottom { border-bottom: 1px solid var(--border-subtle); }
.cal-cell-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.cal-day-num {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-primary);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: grid;
    place-items: center;
}
.cal-day-num--today {
    background: var(--accent);
    color: #fff;
    font-weight: 700;
}
.cal-cell-posts { display: flex; flex-direction: column; gap: 3px; }
.cal-post-chip {
    font-size: 11px;
    padding: 3px 6px;
    border-radius: 4px;
    background: color-mix(in oklab, var(--chip-color) 18%, transparent);
    color: var(--chip-color);
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}
.cal-more {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 600;
    padding: 0 6px;
}

/* List view */
.cal-list-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    border-radius: 8px;
    background: var(--bg-muted);
}
.cal-list-time {
    font-size: 12px;
    color: var(--text-muted);
    font-family: var(--font-mono);
    min-width: 48px;
    text-align: center;
}
.cal-list-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.cal-list-body { flex: 1; min-width: 0; }
.cal-list-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cal-list-meta {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 3px;
}

@media (max-width: 900px) {
    .cal-grid { grid-template-columns: 1fr; }
}
</style>

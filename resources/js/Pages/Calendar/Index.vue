<script setup lang="ts">
// SCH-07: content calendar
import { ref, computed, nextTick } from 'vue'
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

// ── Month/Year picker ───────────────────────────────────────
const pickerOpen  = ref(false)
const pickerYear  = ref(props.year)

const ARABIC_MONTHS = [
    '', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
]
const DAYS_OF_WEEK = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
const monthLabel  = computed(() => `${ARABIC_MONTHS[props.month]} ${props.year}`)

function openPicker() {
    pickerYear.value = props.year
    pickerOpen.value = true
}

function selectMonth(m: number) {
    pickerOpen.value = false
    router.get('/calendar', { year: pickerYear.value, month: m }, { preserveState: false })
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

// ── Counts ──────────────────────────────────────────────────
const counts = computed(() => ({
    all:       props.posts.length,
    scheduled: props.posts.filter(p => p.status === 'scheduled').length,
    draft:     props.drafts.length,
    published: props.posts.filter(p => p.status === 'published').length,
}))

// ── Helpers ─────────────────────────────────────────────────
const PLATFORM_COLORS: Record<string, string> = {
    instagram: 'var(--sada-500)',
    facebook:  'var(--info)',
    tiktok:    'var(--ink-700)',
    snapchat:  'var(--warning)',
    x:         'var(--ink-500)',
}
const PLATFORM_ICONS: Record<string, string> = {
    instagram: 'instagram', facebook: 'facebook',
    tiktok: 'tiktok', snapchat: 'snapchat', x: 'x-brand',
}
const PLATFORM_LABELS: Record<string, string> = {
    instagram: 'انستجرام', facebook: 'فيسبوك',
    tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X',
}
const CONTENT_TYPE_LABELS: Record<string, string> = {
    post: 'منشور', reel: 'ريلز', story: 'قصة',
    ad: 'إعلان', thread: 'خيط', snap_caption: 'كابشن',
}
const STATUS_LABELS: Record<string, string>  = { scheduled: 'مجدول', published: 'منشور', failed: 'فشل', draft: 'مسودة' }
const STATUS_BADGES: Record<string, string>  = { scheduled: 'badge-info', published: 'badge-success', failed: 'badge-error', draft: 'badge-neutral' }

function platformColor(p: string) { return PLATFORM_COLORS[p] ?? 'var(--sada-500)' }
function platformIcon(p: string)  { return PLATFORM_ICONS[p]  ?? 'instagram' }
function platformLabel(p: string) { return PLATFORM_LABELS[p] ?? p }

function formatDateTime(dt: string | null): string {
    if (!dt) return '—'
    return new Date(dt).toLocaleString('ar-SA', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}
function formatTime(dt: string | null): string {
    if (!dt) return ''
    return new Date(dt).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}
function formatDate(dt: string | null): string {
    if (!dt) return '—'
    return new Date(dt).toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' })
}

// ── Detail modal ────────────────────────────────────────────
const detailPost       = ref<CalendarPost | null>(null)
const rescheduleMode   = ref(false)
const rescheduleFor    = ref('')
const rescheduling     = ref(false)

function openDetail(post: CalendarPost) {
    detailPost.value     = post
    rescheduleMode.value = false
    rescheduleFor.value  = ''
}
function closeDetail() {
    detailPost.value = null
    rescheduleMode.value = false
}

function minReschedule(): string {
    const d = new Date()
    d.setMinutes(d.getMinutes() + 5)
    return d.toISOString().slice(0, 16)
}

function confirmReschedule() {
    if (!rescheduleFor.value || !detailPost.value) return
    rescheduling.value = true
    router.post(`/posts/${detailPost.value.id}/reschedule`, {
        scheduled_for: rescheduleFor.value,
    }, {
        onFinish: () => { rescheduling.value = false; closeDetail() },
    })
}

// ── Delete modal ────────────────────────────────────────────
const deleteModal    = ref(false)
const postToDelete   = ref<CalendarPost | null>(null)
const deleting       = ref(false)

function openDeleteModal(post: CalendarPost, e?: Event) {
    e?.stopPropagation()
    postToDelete.value = post
    deleteModal.value  = true
}
function cancelDelete() {
    deleteModal.value  = false
    postToDelete.value = null
}

// Close detail first, then open delete modal — prevents stacked modals
function deleteFromDetail(post: CalendarPost) {
    closeDetail()
    nextTick(() => openDeleteModal(post))
}
function confirmDelete() {
    if (!postToDelete.value) return
    deleting.value = true
    router.delete(`/posts/${postToDelete.value.id}`, {
        preserveState: false,
        onFinish: () => {
            deleting.value    = false
            deleteModal.value = false
            postToDelete.value = null
            closeDetail()
        },
    })
}
</script>

<template>
    <AppLayout title="التقويم">

        <!-- ══ Picker backdrop ══ -->
        <div v-if="pickerOpen" style="position:fixed;inset:0;z-index:90;" @click="pickerOpen = false" />

        <!-- ══ Delete confirm modal ══ -->
        <Teleport to="body">
            <div v-if="deleteModal" class="modal-backdrop" @click.self="cancelDelete">
                <div class="modal-box">
                    <div class="modal-head">
                        <h3>تأكيد الحذف</h3>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="cancelDelete">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="color:var(--text-muted);font-size:13px;margin:0 0 12px">
                            هل أنت متأكد من حذف هذا المنشور؟ لا يمكن التراجع عن هذا الإجراء.
                        </p>
                        <div v-if="postToDelete" class="modal-preview">
                            <div class="modal-preview-meta">
                                <Icon :name="platformIcon(postToDelete.platform)" :size="13" />
                                <span>{{ platformLabel(postToDelete.platform) }}</span>
                                <span :class="['badge', STATUS_BADGES[postToDelete.status] ?? 'badge-neutral']" style="font-size:10px">
                                    {{ STATUS_LABELS[postToDelete.status] ?? postToDelete.status }}
                                </span>
                            </div>
                            <p class="modal-preview-text">{{ postToDelete.content.slice(0, 120) }}{{ postToDelete.content.length > 120 ? '…' : '' }}</p>
                        </div>
                    </div>
                    <div class="modal-foot">
                        <button class="btn btn-ghost btn-sm" @click="cancelDelete">إلغاء</button>
                        <button class="btn btn-danger btn-sm" :disabled="deleting" @click="confirmDelete">
                            <Icon name="trash" :size="13" />
                            {{ deleting ? 'جارٍ الحذف…' : 'حذف المنشور' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ Detail modal ══ -->
        <Teleport to="body">
            <div v-if="detailPost" class="modal-backdrop" @click.self="closeDetail">
                <div class="modal-box modal-box--lg">
                    <div class="modal-head">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="detail-platform-dot" :style="{ background: platformColor(detailPost.platform) }" />
                            <h3>تفاصيل المنشور</h3>
                        </div>
                        <button class="btn btn-icon btn-ghost btn-sm" @click="closeDetail">
                            <Icon name="x" :size="14" />
                        </button>
                    </div>

                    <div class="modal-body stack-sm">
                        <!-- Meta chips -->
                        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                            <span :class="['badge', STATUS_BADGES[detailPost.status] ?? 'badge-neutral']">
                                {{ STATUS_LABELS[detailPost.status] ?? detailPost.status }}
                            </span>
                            <span class="badge badge-neutral" style="display:flex;align-items:center;gap:5px">
                                <Icon :name="platformIcon(detailPost.platform)" :size="12" />
                                {{ platformLabel(detailPost.platform) }}
                            </span>
                            <span class="badge badge-neutral">
                                {{ CONTENT_TYPE_LABELS[detailPost.content_type] ?? detailPost.content_type }}
                            </span>
                        </div>

                        <!-- Scheduled time -->
                        <div v-if="detailPost.scheduled_for" class="detail-time-row">
                            <Icon name="calendar" :size="14" style="color:var(--text-muted)" />
                            <span style="font-size:13px;color:var(--text-secondary)">
                                {{ formatDateTime(detailPost.scheduled_for) }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="detail-content">{{ detailPost.content }}</div>

                        <!-- Hashtags -->
                        <div v-if="detailPost.hashtags?.length" class="tags-row">
                            <span v-for="(tag, i) in detailPost.hashtags" :key="i" class="tag-chip">{{ tag }}</span>
                        </div>

                        <!-- Reschedule form -->
                        <div v-if="rescheduleMode" class="reschedule-box">
                            <label class="input-label" style="margin-bottom:6px;display:block">وقت النشر الجديد</label>
                            <input
                                v-model="rescheduleFor"
                                type="datetime-local"
                                class="input"
                                :min="minReschedule()"
                                style="width:100%"
                            />
                        </div>
                    </div>

                    <div class="modal-foot">
                        <button
                            class="btn btn-danger btn-sm"
                            @click="deleteFromDetail(detailPost!)"
                        >
                            <Icon name="trash" :size="13" /> حذف
                        </button>

                        <div style="display:flex;gap:8px;margin-right:auto">
                            <template v-if="rescheduleMode">
                                <button class="btn btn-ghost btn-sm" @click="rescheduleMode = false">إلغاء</button>
                                <button
                                    class="btn btn-primary btn-sm"
                                    :disabled="!rescheduleFor || rescheduling"
                                    @click="confirmReschedule"
                                >
                                    <Icon name="calendar" :size="13" />
                                    {{ rescheduling ? 'جارٍ…' : 'تأكيد الجدولة' }}
                                </button>
                            </template>
                            <template v-else>
                                <button
                                    v-if="detailPost.status === 'scheduled'"
                                    class="btn btn-secondary btn-sm"
                                    @click="rescheduleMode = true"
                                >
                                    <Icon name="calendar" :size="13" /> إعادة جدولة
                                </button>
                                <a href="/generate" class="btn btn-primary btn-sm">
                                    <Icon name="sparkle" :size="13" /> إعادة توليد
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <div class="stack">
            <div class="cal-grid">

                <!-- ══ Sidebar ══ -->
                <div class="stack">
                    <div class="card">
                        <div class="card-head"><h3>فلاتر</h3></div>
                        <div class="card-body stack-sm">
                            <div class="stack-sm">
                                <div style="font-size:12px;font-weight:600;color:var(--text-muted)">الحالة</div>
                                <button
                                    v-for="f in [
                                        { id: 'all',       label: 'الكل',   count: counts.all },
                                        { id: 'scheduled', label: 'مجدولة', count: counts.scheduled, dot: 'var(--info)' },
                                        { id: 'draft',     label: 'مسودة',  count: counts.draft,     dot: 'var(--warning)' },
                                        { id: 'published', label: 'منشورة', count: counts.published,  dot: 'var(--success)' },
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
                                    <input type="radio" :value="p" v-model="filterPlatform" style="accent-color:var(--accent)" />
                                    <Icon :name="p === 'all' ? 'sparkle' : platformIcon(p)" :size="14" />
                                    {{ p === 'all' ? 'الكل' : platformLabel(p) }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Drafts -->
                    <div class="card" v-if="drafts.length">
                        <div class="card-head">
                            <h3>المسودات</h3>
                            <span class="badge badge-neutral">{{ drafts.length }}</span>
                        </div>
                        <div class="card-body stack-sm">
                            <div
                                v-for="d in drafts.slice(0, 5)"
                                :key="d.id"
                                class="draft-item"
                            >
                                <Icon :name="platformIcon(d.platform)" :size="13" style="color:var(--text-muted);flex-shrink:0" />
                                <span class="draft-title">{{ d.content.slice(0, 45) }}…</span>
                            </div>
                            <a href="/generate" class="btn btn-sm btn-ghost" style="width:100%;justify-content:center;margin-top:4px">
                                + توليد محتوى
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ══ Calendar main ══ -->
                <div class="card" style="padding:0;overflow:hidden">

                    <!-- Header -->
                    <div class="card-head" style="gap:12px;padding:16px 20px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <button class="btn btn-icon btn-icon-sm btn-ghost" @click="navigate(-1)">
                                <Icon name="chevronRight" :size="16" />
                            </button>

                            <!-- Month/Year picker trigger -->
                            <div style="position:relative;">
                                <button
                                    class="month-label-btn"
                                    :class="{ 'month-label-btn--open': pickerOpen }"
                                    @click.stop="openPicker"
                                >
                                    <span>{{ monthLabel }}</span>
                                    <Icon name="chevronDown" :size="13" :style="pickerOpen ? 'transform:rotate(180deg)' : ''" style="transition:transform .2s" />
                                </button>

                                <!-- Month picker dropdown -->
                                <Transition name="drop">
                                    <div v-if="pickerOpen" class="month-picker" @click.stop>
                                        <!-- Year nav -->
                                        <div class="month-picker-year">
                                            <button class="btn btn-icon btn-ghost btn-sm" @click="pickerYear--">
                                                <Icon name="chevronRight" :size="14" />
                                            </button>
                                            <span style="font-size:15px;font-weight:700">{{ pickerYear }}</span>
                                            <button class="btn btn-icon btn-ghost btn-sm" @click="pickerYear++">
                                                <Icon name="chevronLeft" :size="14" />
                                            </button>
                                        </div>
                                        <!-- Month grid -->
                                        <div class="month-picker-grid">
                                            <button
                                                v-for="(name, idx) in ARABIC_MONTHS.slice(1)"
                                                :key="idx"
                                                class="month-picker-item"
                                                :class="{
                                                    'month-picker-item--current': pickerYear === props.year && (idx + 1) === props.month,
                                                    'month-picker-item--today': pickerYear === today.getFullYear() && (idx + 1) === today.getMonth() + 1,
                                                }"
                                                @click="selectMonth(idx + 1)"
                                            >{{ name }}</button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <button class="btn btn-icon btn-icon-sm btn-ghost" @click="navigate(1)">
                                <Icon name="chevronLeft" :size="16" />
                            </button>
                            <button class="btn btn-sm btn-ghost" style="margin-right:4px" @click="goToday">اليوم</button>
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
                        <div class="cal-day-headers">
                            <div v-for="d in DAYS_OF_WEEK" :key="d" class="cal-day-header">{{ d }}</div>
                        </div>
                        <div class="cal-grid-cells">
                            <div
                                v-for="(day, idx) in cells"
                                :key="idx"
                                class="cal-cell"
                                :class="{
                                    'cal-cell--empty':        !day,
                                    'cal-cell--border-right':  idx % 7 < 6,
                                    'cal-cell--border-bottom': Math.floor(idx / 7) < Math.floor((cells.length - 1) / 7),
                                }"
                            >
                                <template v-if="day">
                                    <div class="cal-cell-head">
                                        <span class="cal-day-num" :class="{ 'cal-day-num--today': isToday(day) }">{{ day }}</span>
                                        <span v-if="postsForDay(day).length" class="cal-day-count">
                                            {{ postsForDay(day).length }}
                                        </span>
                                    </div>
                                    <div class="cal-cell-posts">
                                        <button
                                            v-for="post in postsForDay(day).slice(0, 2)"
                                            :key="post.id"
                                            class="cal-post-chip"
                                            :style="{ '--chip-color': platformColor(post.platform) }"
                                            @click="openDetail(post)"
                                        >
                                            <span class="dot" :style="{ background: platformColor(post.platform), width: '5px', height: '5px' }" />
                                            {{ post.content.slice(0, 20) }}…
                                        </button>
                                        <button
                                            v-if="postsForDay(day).length > 2"
                                            class="cal-more"
                                            @click="openDetail(postsForDay(day)[2])"
                                        >+{{ postsForDay(day).length - 2 }} أخرى</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- List view -->
                    <template v-else>
                        <div style="padding:16px">
                            <div
                                v-if="filteredPosts.length === 0"
                                style="padding:40px;text-align:center;color:var(--text-muted)"
                            >لا توجد منشورات لهذا الشهر</div>

                            <div class="list-group">
                                <button
                                    v-for="post in filteredPosts"
                                    :key="post.id"
                                    class="cal-list-item"
                                    @click="openDetail(post)"
                                >
                                    <div class="cal-list-time">
                                        <div style="font-size:12px;font-weight:700">
                                            {{ new Date(post.scheduled_for!).getDate() }}
                                        </div>
                                        <div style="font-size:10px;color:var(--text-muted)">
                                            {{ ARABIC_MONTHS[new Date(post.scheduled_for!).getMonth() + 1]?.slice(0,3) }}
                                        </div>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                                            {{ formatTime(post.scheduled_for) }}
                                        </div>
                                    </div>
                                    <div class="cal-list-dot" :style="{ background: platformColor(post.platform) }" />
                                    <div class="cal-list-body">
                                        <div class="cal-list-title">{{ post.content.slice(0, 70) }}…</div>
                                        <div class="cal-list-meta">
                                            <Icon :name="platformIcon(post.platform)" :size="12" />
                                            {{ platformLabel(post.platform) }} ·
                                            <span :class="['badge', STATUS_BADGES[post.status]]" style="font-size:10px;padding:1px 6px">
                                                {{ STATUS_LABELS[post.status] }}
                                            </span>
                                        </div>
                                    </div>
                                    <button
                                        class="btn btn-icon btn-icon-sm btn-ghost"
                                        style="color:var(--error);flex-shrink:0"
                                        title="حذف"
                                        @click="openDeleteModal(post, $event)"
                                    >
                                        <Icon name="trash" :size="14" />
                                    </button>
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

/* ── Month label button ── */
.month-label-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-subtle);
    background: var(--bg-surface);
    cursor: pointer;
    font-family: var(--font-arabic);
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    min-width: 160px;
    justify-content: space-between;
    transition: background .15s, border-color .15s;
}
.month-label-btn:hover, .month-label-btn--open {
    background: var(--bg-muted);
    border-color: var(--accent);
}

/* ── Month picker dropdown ── */
.month-picker {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    z-index: 100;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    box-shadow: 0 12px 32px rgba(0,0,0,.14);
    padding: 16px;
    width: 260px;
}
.month-picker-year {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px;
}
.month-picker-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.month-picker-item {
    padding: 8px 4px;
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    font-family: var(--font-arabic);
    font-size: 12px;
    font-weight: 500;
    color: var(--text-primary);
    text-align: center;
    transition: background .12s, border-color .12s;
}
.month-picker-item:hover { background: var(--bg-muted); }
.month-picker-item--current {
    background: var(--accent-soft);
    border-color: var(--accent);
    color: var(--accent-text);
    font-weight: 700;
}
.month-picker-item--today { font-weight: 700; }

/* ── Filters ── */
.cal-filter-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 10px; border-radius: 8px;
    cursor: pointer; font-size: 13px; font-weight: 500;
    width: 100%; text-align: right;
    background: transparent; color: var(--text-secondary); border: none;
    transition: background var(--dur-fast);
}
.cal-filter-item[data-active="true"] { background: var(--accent-soft); color: var(--accent-text); font-weight: 600; }
.cal-filter-count { margin-right: auto; font-size: 11px; color: var(--text-muted); }

.cal-platform-label { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: var(--text-secondary); }

.draft-item { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 6px; background: var(--bg-muted); }
.draft-title { font-size: 12px; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Calendar grid ── */
.cal-day-headers { display: grid; grid-template-columns: repeat(7, 1fr); border-bottom: 1px solid var(--border-subtle); }
.cal-day-header {
    padding: 10px 12px; font-size: 12px; font-weight: 600;
    color: var(--text-muted); text-align: center;
    border-left: 1px solid var(--border-subtle);
}
.cal-day-header:last-child { border-left: none; }

.cal-grid-cells { display: grid; grid-template-columns: repeat(7, 1fr); }
.cal-cell {
    min-height: 110px; padding: 8px;
    background: var(--bg-surface);
    transition: background var(--dur-fast);
}
.cal-cell--empty { background: var(--bg-muted); }
.cal-cell--border-right  { border-left: 1px solid var(--border-subtle); }
.cal-cell--border-bottom { border-bottom: 1px solid var(--border-subtle); }

.cal-cell-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.cal-day-num {
    font-size: 13px; font-weight: 500; color: var(--text-primary);
    width: 24px; height: 24px; border-radius: 50%;
    display: grid; place-items: center;
}
.cal-day-num--today { background: var(--accent); color: #fff; font-weight: 700; }
.cal-day-count {
    font-size: 10px; font-weight: 700;
    color: var(--text-muted);
}
.cal-cell-posts { display: flex; flex-direction: column; gap: 3px; }
.cal-post-chip {
    font-size: 11px; padding: 3px 6px; border-radius: 4px;
    background: color-mix(in oklab, var(--chip-color) 14%, transparent);
    color: var(--chip-color); font-weight: 600;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    display: flex; align-items: center; gap: 4px;
    cursor: pointer; border: none; width: 100%; text-align: right;
    transition: background .12s;
}
.cal-post-chip:hover { background: color-mix(in oklab, var(--chip-color) 24%, transparent); }
.cal-more {
    font-size: 10px; color: var(--text-muted); font-weight: 600;
    padding: 2px 6px; cursor: pointer; border: none;
    background: transparent; text-align: right; border-radius: 4px;
}
.cal-more:hover { background: var(--bg-muted); }

/* ── List view ── */
.list-group { display: flex; flex-direction: column; gap: 6px; }
.cal-list-item {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 14px; border-radius: 10px;
    background: var(--bg-muted);
    border: 1px solid transparent;
    cursor: pointer; width: 100%; text-align: right;
    font-family: var(--font-arabic);
    transition: background .12s, border-color .12s;
}
.cal-list-item:hover { background: var(--bg-surface); border-color: var(--border-subtle); }
.cal-list-time { min-width: 44px; text-align: center; flex-shrink: 0; }
.cal-list-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.cal-list-body { flex: 1; min-width: 0; }
.cal-list-title { font-size: 13px; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cal-list-meta { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; margin-top: 3px; }

/* ── Modals ── */
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 500;
    display: grid; place-items: center;
    padding: 20px;
}
.modal-box {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    width: 420px;
    max-width: 100%;
    overflow: hidden;
}
.modal-box--lg { width: 560px; }
.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-subtle);
}
.modal-head h3 { margin: 0; font-size: 15px; font-weight: 700; }
.modal-body { padding: 20px; }
.modal-foot {
    display: flex; align-items: center; gap: 8px;
    padding: 14px 20px;
    border-top: 1px solid var(--border-subtle);
    background: var(--bg-muted);
}

.modal-preview {
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    border: 1px solid var(--border-subtle);
}
.modal-preview-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 12px; color: var(--text-muted); }
.modal-preview-text { margin: 0; font-size: 13px; color: var(--text-primary); line-height: 1.6; }

/* ── Detail modal ── */
.detail-platform-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.detail-time-row { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: var(--bg-muted); border-radius: var(--radius-sm); }
.detail-content {
    font-size: 14px; line-height: 1.75;
    color: var(--text-primary);
    white-space: pre-wrap;
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    max-height: 240px;
    overflow-y: auto;
}
.tags-row { display: flex; flex-wrap: wrap; gap: 6px; }
.tag-chip { font-size: 12px; font-weight: 500; color: var(--accent-text); padding: 3px 8px; background: var(--accent-soft); border-radius: 6px; }

.reschedule-box {
    padding: 14px;
    background: color-mix(in oklab, var(--accent) 6%, transparent);
    border: 1px solid color-mix(in oklab, var(--accent) 20%, transparent);
    border-radius: var(--radius-md);
}

/* ── Dropdown transition ── */
.drop-enter-active, .drop-leave-active { transition: opacity .15s, transform .15s; }
.drop-enter-from, .drop-leave-to { opacity: 0; transform: translateY(-6px); }

@media (max-width: 900px) { .cal-grid { grid-template-columns: 1fr; } }
</style>

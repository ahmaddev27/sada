<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Icon from '@/Components/Base/Icon.vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/Types';

const props = defineProps<{
    stats: {
        token_balance: number;
        posts: { total: number; published: number; scheduled: number; draft: number; failed: number };
        social_accounts: number;
        workspaces: number;
    };
    recentPosts: Array<{
        id: number;
        content: string;
        platform: string;
        status: string;
        created_at: string;
        scheduled_for: string | null;
    }>;
    socialAccounts: Array<{
        id: number;
        provider: string;
        account_name: string;
        status: string;
    }>;
}>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user);
const firstName = computed(() => user.value?.name?.split(' ')[0] ?? 'مرحباً');

const tokenPct = computed(() =>
    Math.min(100, Math.round((props.stats.token_balance / 2000) * 100))
);

const kpis = computed(() => [
    {
        label: 'إجمالي المنشورات',
        value: props.stats.posts.total,
        icon: 'megaphone',
        color: 'var(--sada-500)',
        bg: 'color-mix(in oklab, var(--sada-500) 14%, transparent)',
        href: '/posts',
        delta: null,
    },
    {
        label: 'منشورة',
        value: props.stats.posts.published,
        icon: 'check',
        color: 'var(--success)',
        bg: 'color-mix(in oklab, var(--success) 14%, transparent)',
        href: '/posts?status=published',
        delta: null,
    },
    {
        label: 'مجدولة',
        value: props.stats.posts.scheduled,
        icon: 'calendar',
        color: 'var(--info)',
        bg: 'color-mix(in oklab, var(--info) 14%, transparent)',
        href: '/posts?status=scheduled',
        delta: null,
    },
    {
        label: 'مسودات',
        value: props.stats.posts.draft,
        icon: 'edit',
        color: 'var(--warning)',
        bg: 'color-mix(in oklab, var(--warning) 14%, transparent)',
        href: '/posts?status=draft',
        delta: null,
    },
]);

const STATUS_MAP: Record<string, { label: string; color: string; bg: string }> = {
    published: { label: 'منشور',  color: 'var(--success)', bg: 'var(--success-bg)' },
    scheduled: { label: 'مجدول',  color: 'var(--info)',    bg: 'var(--info-bg)'    },
    draft:     { label: 'مسودة',  color: 'var(--warning)', bg: 'var(--warning-bg)' },
    failed:    { label: 'فشل',    color: 'var(--error)',   bg: 'var(--error-bg)'   },
};

const PLATFORM_ICON: Record<string, string> = {
    instagram: 'instagram', facebook: 'facebook',
    tiktok: 'tiktok', snapchat: 'snapchat', x: 'x-brand',
};
const PLATFORM_LABEL: Record<string, string> = {
    instagram: 'إنستغرام', facebook: 'فيسبوك',
    tiktok: 'تيك توك', snapchat: 'سناب شات', x: 'X',
};
const PLATFORM_COLOR: Record<string, string> = {
    instagram: '#E1306C', facebook: '#1877F2',
    tiktok: '#010101', snapchat: '#F7A800', x: '#000000',
};

const quickActions = [
    { label: 'توليد محتوى', icon: 'sparkle',   href: '/generate',  color: 'var(--sada-500)', bg: 'color-mix(in oklab, var(--sada-500) 14%, transparent)' },
    { label: 'عرض التقويم', icon: 'calendar',  href: '/calendar',  color: 'var(--info)',      bg: 'color-mix(in oklab, var(--info) 14%, transparent)'      },
    { label: 'حملة موسمية', icon: 'moon',      href: '/seasonal',  color: 'var(--warning)',   bg: 'color-mix(in oklab, var(--warning) 14%, transparent)'   },
    { label: 'الحملات',     icon: 'megaphone', href: '/campaigns', color: '#8B5CF6',          bg: 'color-mix(in oklab, #8B5CF6 14%, transparent)'          },
];

function relativeTime(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const m = Math.floor(diff / 60000);
    if (m < 1)  return 'الآن';
    if (m < 60) return `منذ ${m} دقيقة`;
    const h = Math.floor(m / 60);
    if (h < 24) return `منذ ${h} ساعة`;
    const d = Math.floor(h / 24);
    return `منذ ${d} يوم`;
}

function formatScheduled(iso: string | null): string {
    if (!iso) return '';
    return new Date(iso).toLocaleString('ar-SA', {
        month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}
</script>

<template>
    <AppLayout title="لوحة التحكم" :crumbs="['الرئيسية']">
        <div class="dash" dir="rtl">

            <!-- ① Welcome banner + quick actions -->
            <div class="banner-row">
                <div class="welcome-banner">
                    <!-- Decorative rings -->
                    <svg class="banner-deco" width="200" height="200" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                        <circle cx="100" cy="100" r="90" stroke="white" stroke-width="1.2" opacity="0.5"/>
                        <circle cx="100" cy="100" r="60" stroke="white" stroke-width="1.2" opacity="0.5"/>
                        <circle cx="100" cy="100" r="30" stroke="white" stroke-width="1.2" opacity="0.5"/>
                        <line x1="10" y1="100" x2="190" y2="100" stroke="white" stroke-width="1" opacity="0.4"/>
                        <line x1="100" y1="10" x2="100" y2="190" stroke="white" stroke-width="1" opacity="0.4"/>
                    </svg>
                    <div class="banner-body">
                        <div class="banner-title">
                            صباح الخير، {{ firstName }} ☀️
                        </div>
                        <div class="banner-desc">
                            <span v-if="stats.posts.scheduled > 0">
                                لديك <strong>{{ stats.posts.scheduled }}</strong> منشور مجدول — استمر!
                            </span>
                            <span v-else>ابدأ بتوليد محتوى جديد اليوم.</span>
                        </div>
                        <div class="banner-actions">
                            <Link href="/generate" class="banner-btn-primary">
                                <Icon name="sparkle" :size="15" />
                                ولّد منشوراً جديداً
                            </Link>
                            <Link href="/calendar" class="banner-btn-ghost">
                                <Icon name="calendar" :size="15" />
                                عرض الجدول
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Token usage card -->
                <div class="token-card">
                    <div class="token-card-head">
                        <div class="token-icon">
                            <Icon name="sparkle" :size="16" />
                        </div>
                        <div>
                            <div class="token-title">رصيد التوكنز</div>
                            <div class="token-sub">{{ tokenPct }}٪ مستهلك</div>
                        </div>
                    </div>
                    <div class="token-value">{{ stats.token_balance.toLocaleString('ar-SA') }}</div>
                    <div class="token-bar-wrap">
                        <div
                            class="token-bar-fill"
                            :style="`width:${tokenPct}%;background:${tokenPct > 80 ? 'var(--warning)' : 'var(--sada-500)'}`"
                        />
                    </div>
                    <Link href="/billing" class="token-refill-btn">شحن المزيد</Link>
                </div>
            </div>

            <!-- ② KPI strip -->
            <div class="kpi-grid">
                <Link
                    v-for="k in kpis"
                    :key="k.label"
                    :href="k.href"
                    class="kpi-card"
                >
                    <div class="kpi-top">
                        <div class="kpi-value">{{ k.value }}</div>
                        <div class="kpi-icon" :style="`background:${k.bg};color:${k.color}`">
                            <Icon :name="k.icon" :size="20" />
                        </div>
                    </div>
                    <div class="kpi-label">{{ k.label }}</div>
                </Link>
            </div>

            <!-- ③ Bottom grid: posts timeline + quick actions -->
            <div class="bottom-grid">

                <!-- Recent posts timeline -->
                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h3 class="panel-title">آخر المنشورات</h3>
                            <div class="panel-sub">{{ stats.posts.total }} منشور إجمالاً</div>
                        </div>
                        <Link href="/posts" class="panel-link">عرض الكل</Link>
                    </div>

                    <div v-if="recentPosts.length === 0" class="empty-state">
                        <Icon name="megaphone" :size="28" />
                        <p>لا منشورات بعد —
                            <Link href="/generate" class="panel-link">أنشئ أول منشور</Link>
                        </p>
                    </div>

                    <div v-else class="timeline">
                        <div v-for="post in recentPosts" :key="post.id" class="timeline-item">
                            <div
                                class="timeline-dot"
                                :style="`background:${PLATFORM_COLOR[post.platform] ?? 'var(--sada-500)'}`"
                            />
                            <div class="timeline-body">
                                <div class="timeline-text">{{ post.content }}</div>
                                <div class="timeline-meta">
                                    <span class="timeline-time">
                                        <Icon name="clock" :size="11" />
                                        {{ post.scheduled_for ? formatScheduled(post.scheduled_for) : relativeTime(post.created_at) }}
                                    </span>
                                    <span class="platform-tag">
                                        <Icon :name="PLATFORM_ICON[post.platform] ?? 'megaphone'" :size="11" />
                                        {{ PLATFORM_LABEL[post.platform] ?? post.platform }}
                                    </span>
                                    <span
                                        class="status-badge"
                                        :style="`background:${STATUS_MAP[post.status]?.bg};color:${STATUS_MAP[post.status]?.color}`"
                                    >
                                        {{ STATUS_MAP[post.status]?.label ?? post.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick actions + social accounts -->
                <div class="side-col">

                    <!-- Quick actions -->
                    <div class="panel">
                        <div class="panel-head" style="padding-bottom:12px">
                            <h3 class="panel-title">إجراءات سريعة</h3>
                        </div>
                        <div class="quick-grid">
                            <Link
                                v-for="a in quickActions"
                                :key="a.label"
                                :href="a.href"
                                class="quick-btn"
                            >
                                <div class="quick-icon" :style="`background:${a.bg};color:${a.color}`">
                                    <Icon :name="a.icon" :size="18" />
                                </div>
                                <span class="quick-label">{{ a.label }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Social accounts -->
                    <div class="panel">
                        <div class="panel-head">
                            <h3 class="panel-title">الحسابات المتصلة</h3>
                            <Link href="/social/accounts" class="panel-link">إدارة</Link>
                        </div>

                        <div v-if="socialAccounts.length === 0" class="empty-state" style="padding:20px">
                            <Icon name="instagram" :size="22" />
                            <p style="font-size:12px">
                                <Link href="/social/accounts" class="panel-link">ربط حساب</Link>
                            </p>
                        </div>

                        <div v-else class="account-list">
                            <div v-for="acc in socialAccounts" :key="acc.id" class="account-row">
                                <div
                                    class="account-icon"
                                    :style="`background:color-mix(in oklab,${PLATFORM_COLOR[acc.provider] ?? '#888'} 12%,transparent);color:${PLATFORM_COLOR[acc.provider] ?? '#888'}`"
                                >
                                    <Icon :name="PLATFORM_ICON[acc.provider] ?? 'user'" :size="14" />
                                </div>
                                <div class="account-meta">
                                    <div class="account-name">{{ acc.account_name }}</div>
                                    <div class="account-provider">{{ PLATFORM_LABEL[acc.provider] ?? acc.provider }}</div>
                                </div>
                                <span
                                    class="status-badge"
                                    :style="acc.status === 'active'
                                        ? 'background:var(--success-bg);color:var(--success)'
                                        : 'background:var(--error-bg);color:var(--error)'"
                                >
                                    {{ acc.status === 'active' ? 'نشط' : 'منتهي' }}
                                </span>
                            </div>
                        </div>

                        <Link href="/social/accounts" class="connect-btn">
                            <Icon name="plus" :size="13" />
                            ربط حساب جديد
                        </Link>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.dash {
    padding: 24px 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Welcome banner ── */
.banner-row {
    display: flex;
    gap: 16px;
    align-items: stretch;
}

.welcome-banner {
    flex: 1;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--sada-600) 0%, var(--sada-400) 100%);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    color: #fff;
}

.banner-deco {
    position: absolute;
    left: -20px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    opacity: 0.5;
}

.banner-body { position: relative; z-index: 1; }

.banner-title {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 6px;
    line-height: 1.3;
}

.banner-desc {
    font-size: 14px;
    opacity: 0.88;
    margin: 0 0 20px;
    line-height: 1.6;
}
.banner-desc strong { font-weight: 700; }

.banner-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.banner-btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 0 18px; height: 38px;
    background: #fff; color: var(--sada-700);
    border-radius: var(--radius-md);
    font-size: 14px; font-weight: 700;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
}
.banner-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.18); }

.banner-btn-ghost {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 0 18px; height: 38px;
    background: rgba(255,255,255,0.15); color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: var(--radius-md);
    font-size: 14px; font-weight: 600;
    text-decoration: none;
    transition: background 0.15s;
}
.banner-btn-ghost:hover { background: rgba(255,255,255,0.25); }

/* Token card */
.token-card {
    width: 220px;
    flex-shrink: 0;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.token-card-head { display: flex; align-items: center; gap: 10px; }

.token-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: var(--sada-50);
    color: var(--sada-500);
    display: grid; place-items: center;
    flex-shrink: 0;
}

.token-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.token-sub   { font-size: 11px; color: var(--text-muted); }

.token-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.token-bar-wrap {
    height: 5px;
    background: var(--border-subtle);
    border-radius: 3px;
    overflow: hidden;
}
.token-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.4s ease;
}

.token-refill-btn {
    display: block;
    text-align: center;
    padding: 7px;
    background: var(--sada-50);
    color: var(--sada-600);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
}
.token-refill-btn:hover { background: var(--sada-100, #E6F4F0); }

/* ── KPI grid ── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.kpi-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 18px 20px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: box-shadow 0.15s, border-color 0.15s, transform 0.15s;
}
.kpi-card:hover {
    border-color: var(--sada-300);
    box-shadow: 0 4px 16px rgba(15,111,92,.10);
    transform: translateY(-1px);
}

.kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: grid; place-items: center;
    flex-shrink: 0;
}

.kpi-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}
.kpi-label {
    font-size: 12px;
    color: var(--text-muted);
}

/* ── Bottom grid ── */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 16px;
    align-items: start;
}

/* Panel */
.panel {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 16px;
}
.panel:last-child { margin-bottom: 0; }

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 14px;
    border-bottom: 1px solid var(--border-subtle);
}
.panel-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 2px;
}
.panel-sub { font-size: 12px; color: var(--text-muted); }
.panel-link {
    font-size: 12px;
    color: var(--sada-600);
    text-decoration: none;
    font-weight: 600;
}
.panel-link:hover { text-decoration: underline; }

/* Empty state */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px 20px;
    color: var(--text-muted);
    font-size: 13px;
    text-align: center;
}
.empty-state p { margin: 0; }

/* Timeline */
.timeline { padding: 4px 0; }

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-subtle);
    transition: background 0.1s;
}
.timeline-item:last-child { border-bottom: none; }
.timeline-item:hover { background: var(--bg-muted); }

.timeline-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 5px;
}

.timeline-body { flex: 1; min-width: 0; }

.timeline-text {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 5px;
    line-height: 1.4;
}

.timeline-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.timeline-time {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    color: var(--text-muted);
}

.platform-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    background: var(--bg-muted);
    color: var(--text-muted);
}

.status-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 20px;
}

/* Quick actions */
.side-col { display: flex; flex-direction: column; }

.quick-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding: 14px 16px;
}

.quick-btn {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 14px;
    background: var(--bg-muted);
    border: 1px solid transparent;
    border-radius: var(--radius-md);
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s, transform 0.15s;
}
.quick-btn:hover {
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
    border-color: color-mix(in oklab, var(--sada-500) 25%, transparent);
    transform: translateY(-1px);
}

.quick-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: grid; place-items: center;
}

.quick-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.3;
}

/* Social accounts */
.account-list { padding: 4px 0; }

.account-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border-subtle);
}
.account-row:last-child { border-bottom: none; }

.account-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: grid; place-items: center;
    flex-shrink: 0;
}

.account-meta { flex: 1; min-width: 0; }
.account-name { font-size: 12px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.account-provider { font-size: 11px; color: var(--text-muted); text-transform: capitalize; }

.connect-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 10px 14px 12px;
    padding: 7px;
    border: 1px dashed var(--border-default);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
    color: var(--sada-600);
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s;
}
.connect-btn:hover { background: var(--bg-muted); border-color: var(--sada-400); }

@media (max-width: 1100px) {
    .banner-row { flex-direction: column; }
    .token-card { width: 100%; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .bottom-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .dash { padding: 16px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; }
    .quick-grid { grid-template-columns: 1fr 1fr; }
}
</style>

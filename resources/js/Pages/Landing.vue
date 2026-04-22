<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useUiStore } from '@/Stores/ui';

const ui = useUiStore();
const mobileMenuOpen = ref(false);

const features = [
    { icon: 'sparkle',   title: 'توليد محتوى بلهجتك',    desc: 'فصحى، سعودي، إماراتي، كويتي، قطري، بحريني، عُماني — كلها مع فهم عميق للسياق.',           color: '#0F6F5C' },
    { icon: 'moon',      title: 'حملات موسمية بضغطة',    desc: 'رمضان، العيدين، اليوم الوطني، التأسيس — قوالب وأفكار جاهزة ومحترمة.',                    color: '#C8965F' },
    { icon: 'calendar',  title: 'جدولة ذكية على Meta',   desc: 'انشر على انستجرام وفيسبوك من جدول واحد، مع اقتراح أفضل أوقات النشر.',                     color: '#3B82F6' },
    { icon: 'megaphone', title: 'إعلانات ممولة',          desc: 'أطلق حملات مدفوعة على Meta من نفس المكان — بميزانية صغيرة أو كبيرة.',                     color: '#F59E0B' },
    { icon: 'chart',     title: 'تحليلات وتقارير',        desc: 'رؤى ذكية بالعربية + تقارير قابلة للتخصيص بشعارك (مثالية للوكالات).',                     color: '#10B981' },
    { icon: 'building',  title: 'Workspaces للوكالات',   desc: 'أدر عملاءك في مساحات منفصلة، بفواتير وأذونات مستقلة.',                                    color: '#0A5A4B' },
];

const plans = [
    {
        name: 'Starter', price: '٩٩', tokens: '١٬٠٠٠', featured: false,
        features: ['مستخدم واحد', 'مساحة عمل واحدة', 'انستجرام + فيسبوك', 'توليد محتوى بـ٣ لهجات', 'دعم عبر البريد'],
    },
    {
        name: 'Growth', price: '٢٩٩', tokens: '٢٬٠٠٠', featured: true,
        features: ['حتى ٣ مستخدمين', '٣ مساحات عمل', 'كل المنصات والميزات', 'كل اللهجات الخليجية', 'حملات إعلانية Meta', 'دعم فوري'],
    },
    {
        name: 'Agency', price: '٧٩٩', tokens: '١٠٬٠٠٠', featured: false,
        features: ['مستخدمون غير محدودين', 'مساحات غير محدودة', 'White-label تقارير', 'API وصلاحيات متقدمة', 'مدير حساب مخصص'],
    },
];

const brands  = ['متجر أنيق', 'قهوة الرواق', 'طلبات فاست', 'لاونج ٤٢', 'بيت الورد', 'نكهات'];

const navLinks = [
    { label: 'المميزات', href: '#features' },
    { label: 'التسعير',  href: '#pricing'  },
    { label: 'المواسم',  href: '#seasonal' },
    { label: 'المدونة',  href: '#blog'     },
];
</script>

<template>
    <Head title="صدى — منصة التسويق الذكي للسوق الخليجي" />

    <div class="landing" dir="rtl">

        <!-- ── Navbar ──────────────────────────────────────────────── -->
        <nav class="navbar">
            <div class="nav-inner">
                <!-- Logo -->
                <a href="/" class="nav-logo">
                    <div class="nav-logo-mark">ص</div>
                    صدى
                </a>

                <!-- Desktop links -->
                <div class="nav-links">
                    <a
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href"
                        class="nav-link"
                    >{{ link.label }}</a>
                </div>

                <!-- Desktop actions -->
                <div class="nav-actions">
                    <button
                        class="theme-btn"
                        @click="ui.toggleTheme()"
                        :title="ui.theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
                    >
                        <svg v-if="ui.theme === 'dark'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                            <circle cx="12" cy="12" r="5"/>
                        </svg>
                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <Link href="/login"    class="btn-outline">تسجيل دخول</Link>
                    <Link href="/register" class="btn-primary-sm">
                        ابدأ مجاناً
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    </Link>
                </div>

                <!-- Mobile hamburger -->
                <button class="hamburger" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="القائمة">
                    <svg v-if="!mobileMenuOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6"  x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    <svg v-else                width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Mobile dropdown menu -->
            <div v-if="mobileMenuOpen" class="mobile-menu">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="mobile-link"
                    @click="mobileMenuOpen = false"
                >{{ link.label }}</a>
                <div class="mobile-actions">
                    <Link href="/login"    class="btn-outline w-full text-center">تسجيل دخول</Link>
                    <Link href="/register" class="btn-primary-sm w-full justify-center">ابدأ مجاناً</Link>
                </div>
            </div>
        </nav>

        <!-- ── Hero ───────────────────────────────────────────────── -->
        <section class="section hero-section">
            <div class="hero-grid">
                <!-- Text -->
                <div>
                    <span class="hero-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                        جديد — لهجات خليجية بدقة
                    </span>

                    <h1 class="hero-title">
                        محتوى تسويقي خليجي
                        <span class="hero-gradient">يتكلم لغتك.</span>
                    </h1>

                    <p class="hero-desc">
                        منصة ذكاء اصطناعي كاملة لتوليد المحتوى، جدولة النشر، وإطلاق الحملات —
                        بلهجتك، وبمواسمك، ومن مكان واحد.
                    </p>

                    <div class="hero-ctas">
                        <Link href="/register" class="btn-hero-primary">
                            جرّب مجاناً
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                        </Link>
                        <button class="btn-hero-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                            شاهد عرضاً مباشراً
                        </button>
                    </div>

                    <div class="trust-badges">
                        <span class="trust-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>٣٠ يوم مجاناً</span>
                        <span class="trust-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>بدون بطاقة ائتمان</span>
                        <span class="trust-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>دعم عربي ٢٤/٧</span>
                    </div>
                </div>

                <!-- Mockup card -->
                <div class="hero-mockup">
                    <div class="mockup-card">
                        <!-- Window chrome -->
                        <div class="mockup-chrome">
                            <span class="chrome-dot" style="background:#FF5F57;"></span>
                            <span class="chrome-dot" style="background:#FEBC2E;"></span>
                            <span class="chrome-dot" style="background:#28C840;"></span>
                        </div>

                        <div class="mockup-inner">
                            <!-- Controls -->
                            <div class="mockup-controls">
                                <div class="mockup-label">اللهجة</div>
                                <div class="dialect-chips">
                                    <span class="chip chip-active">🇸🇦 سعودي</span>
                                    <span class="chip">🇦🇪 إماراتي</span>
                                    <span class="chip">فصحى</span>
                                </div>
                                <div class="mockup-textarea">خصم ٣٠٪ بمناسبة اليوم الوطني...</div>
                                <div class="mockup-generate-btn">✨ ولّد</div>
                            </div>

                            <!-- Preview -->
                            <div class="mockup-preview">
                                <div class="mockup-img-area">
                                    <div class="mockup-discount">
                                        <div class="discount-num">٣٠٪</div>
                                        <div class="discount-label">خصم</div>
                                    </div>
                                </div>
                                <div class="mockup-caption">
                                    <strong>متجر_أنيق</strong> عطيناكم ٣٠٪ خصم على كل شي 🇸🇦
                                </div>
                            </div>
                        </div>

                        <!-- Floating metric -->
                        <div class="metric-float">
                            <div class="metric-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            </div>
                            <div>
                                <div class="metric-label">التفاعل هذا الأسبوع</div>
                                <div class="metric-value">+٤٥٪</div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative blob -->
                    <div class="hero-blob"></div>
                </div>
            </div>
        </section>

        <!-- ── Social proof ───────────────────────────────────────── -->
        <section class="section proof-section">
            <div class="proof-label">موثوق من أكثر من ١٥٠ متجراً وعلامة خليجية</div>
            <div class="proof-brands">
                <span v-for="brand in brands" :key="brand" class="proof-brand">{{ brand }}</span>
            </div>
        </section>

        <!-- ── Features ───────────────────────────────────────────── -->
        <section id="features" class="section features-section">
            <div class="section-header">
                <div class="section-eyebrow">المميزات</div>
                <h2 class="section-title">كل ما تحتاجه لتسويقك الرقمي</h2>
            </div>

            <div class="features-grid">
                <div v-for="feature in features" :key="feature.title" class="feature-card">
                    <div class="feature-icon" :style="{ background: `color-mix(in oklab, ${feature.color} 14%, transparent)`, color: feature.color }">
                        <svg v-if="feature.icon === 'sparkle'"   width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                        <svg v-else-if="feature.icon === 'moon'"      width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg v-else-if="feature.icon === 'calendar'"  width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <svg v-else-if="feature.icon === 'megaphone'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                        <svg v-else-if="feature.icon === 'chart'"     width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <svg v-else-if="feature.icon === 'building'"  width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <h3 class="feature-title">{{ feature.title }}</h3>
                    <p class="feature-desc">{{ feature.desc }}</p>
                </div>
            </div>
        </section>

        <!-- ── Pricing ────────────────────────────────────────────── -->
        <section id="pricing" class="section pricing-section">
            <div class="section-header">
                <h2 class="section-title">أسعار شفافة، بلا مفاجآت</h2>
                <p class="section-subtitle">ابدأ مجاناً، وارقّ عندما تحتاج. دفع بالريال/الدرهم عبر Moyasar & Tap.</p>
            </div>

            <div class="pricing-grid">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    :class="['pricing-card', { 'pricing-card--featured': plan.featured }]"
                >
                    <div v-if="plan.featured" class="popular-badge">الأكثر شعبية</div>

                    <div class="plan-name">{{ plan.name }}</div>
                    <div class="plan-price-row">
                        <span class="plan-price">{{ plan.price }}</span>
                        <span class="plan-currency">ر.س / شهر</span>
                    </div>
                    <div class="plan-tokens">{{ plan.tokens }} توكن/شهر</div>

                    <Link
                        href="/register"
                        :class="['plan-cta', plan.featured ? 'plan-cta--featured' : 'plan-cta--default']"
                    >ابدأ بـ{{ plan.name }}</Link>

                    <div class="plan-features">
                        <div v-for="feat in plan.features" :key="feat" class="plan-feature">
                            <svg style="flex-shrink:0;margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="plan.featured ? 'var(--sand-500)' : 'var(--sada-500)'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>{{ feat }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Testimonial ────────────────────────────────────────── -->
        <section class="section testimonial-section">
            <blockquote class="testimonial-quote">
                «لأول مرة، أداة تسويقية تفهم لهجتنا الخليجية وتحترم مواسمنا.
                صدى وفّرت لنا ساعات كل أسبوع.»
            </blockquote>
            <div class="testimonial-author">
                <div class="testimonial-avatar">نس</div>
                <div>
                    <div class="testimonial-name">نورة السبيعي</div>
                    <div class="testimonial-role">مديرة التسويق، متجر أنيق</div>
                </div>
            </div>
        </section>

        <!-- ── CTA banner ─────────────────────────────────────────── -->
        <section class="section cta-section">
            <div class="cta-banner">
                <h2 class="cta-title">ابدأ تسويقك بالذكاء الاصطناعي اليوم</h2>
                <p class="cta-sub">٣٠ يوماً مجاناً، بدون بطاقة ائتمان.</p>
                <Link href="/register" class="btn-cta">
                    أنشئ حسابك مجاناً
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </Link>
            </div>
        </section>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer class="footer">
            <div class="footer-inner">
                <div class="footer-brand">
                    <div class="footer-logo">ص</div>
                    <span class="footer-name">صدى</span>
                    <span>© ٢٠٢٦ — جميع الحقوق محفوظة</span>
                </div>
                <div class="footer-links">
                    <a href="#" class="footer-link">الخصوصية</a>
                    <a href="#" class="footer-link">الشروط</a>
                    <a href="#" class="footer-link">الدعم</a>
                    <a href="#" class="footer-link">تواصل</a>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
/* ── Base ──────────────────────────────────────────────────────────── */
.landing {
    background: var(--bg-page);
    color: var(--text-primary);
    font-family: var(--font-arabic);
    min-height: 100vh;
    scroll-behavior: smooth;
}

/* ── Navbar ────────────────────────────────────────────────────────── */
.navbar {
    position: sticky; top: 0; z-index: 40;
    background: color-mix(in oklab, var(--bg-page) 85%, transparent);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border-subtle);
}
.nav-inner {
    max-width: 1280px; margin: 0 auto;
    padding: 0 40px;
    height: 64px;
    display: flex; align-items: center; gap: 40px;
}
.nav-logo {
    display: flex; align-items: center; gap: 10px;
    font-weight: 800; font-size: 20px;
    color: var(--text-primary); text-decoration: none;
    flex-shrink: 0;
}
.nav-logo-mark {
    width: 32px; height: 32px;
    background: var(--sada-500); border-radius: 9px;
    color: #fff; font-weight: 800; font-size: 16px;
    display: grid; place-items: center;
}
.nav-links {
    display: flex; align-items: center; gap: 24px; flex: 1;
}
.nav-link {
    font-size: 14px; color: var(--text-muted); font-weight: 500;
    text-decoration: none; transition: color .15s;
}
.nav-link:hover { color: var(--text-primary); }
.nav-actions { display: flex; gap: 8px; align-items: center; }
.theme-btn {
    width: 38px; height: 38px; border-radius: 10px;
    display: grid; place-items: center; cursor: pointer;
    background: var(--bg-muted); color: var(--text-muted);
    border: 1px solid var(--border-subtle); transition: all .15s;
}
.theme-btn:hover { opacity: 0.7; }
.btn-outline {
    padding: 8px 16px; font-size: 14px; font-weight: 500;
    color: var(--text-primary); background: transparent;
    border: 1px solid var(--border-default); border-radius: 8px;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: opacity .15s; white-space: nowrap;
}
.btn-outline:hover { opacity: 0.7; }
.btn-primary-sm {
    padding: 8px 16px; font-size: 14px; font-weight: 600;
    color: #fff; background: var(--sada-600);
    border-radius: 8px; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
    transition: opacity .15s; white-space: nowrap; border: none; cursor: pointer;
}
.btn-primary-sm:hover { opacity: 0.9; }
.hamburger {
    display: none;
    margin-right: auto;
    width: 40px; height: 40px; border-radius: 8px;
    background: var(--bg-muted); color: var(--text-primary);
    border: 1px solid var(--border-subtle);
    align-items: center; justify-content: center; cursor: pointer;
}
.mobile-menu {
    padding: 12px 16px 20px;
    border-top: 1px solid var(--border-subtle);
    display: flex; flex-direction: column; gap: 4px;
}
.mobile-link {
    padding: 10px 12px; border-radius: 8px;
    font-size: 15px; color: var(--text-muted); text-decoration: none;
    font-weight: 500; transition: background .15s;
}
.mobile-link:hover { background: var(--bg-muted); color: var(--text-primary); }
.mobile-actions { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
.w-full  { width: 100%; }
.text-center { text-align: center; }
.justify-center { justify-content: center; }

/* ── Section base ──────────────────────────────────────────────────── */
.section { padding: 80px 40px; max-width: 1280px; margin: 0 auto; }

/* ── Hero ──────────────────────────────────────────────────────────── */
.hero-section { padding-top: 80px; padding-bottom: 60px; }
.hero-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 56px; align-items: center;
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px;
    background: color-mix(in oklab, var(--sand-500) 15%, transparent);
    color: var(--sand-600, #9B6B35);
    border-radius: 99px; font-size: 12px; font-weight: 600;
    margin-bottom: 20px;
}
.hero-title {
    margin: 0; font-size: 52px; font-weight: 800;
    letter-spacing: -0.02em; line-height: 1.1;
    color: var(--text-primary);
}
.hero-gradient {
    display: block;
    background: linear-gradient(90deg, var(--sada-500), var(--sand-500));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-desc {
    margin: 20px 0 32px;
    font-size: 17px; line-height: 1.7;
    color: var(--text-muted); max-width: 520px;
}
.hero-ctas { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-hero-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 24px;
    background: var(--sada-600); color: #fff;
    border-radius: 10px; font-size: 15px; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer; transition: opacity .15s;
}
.btn-hero-primary:hover { opacity: 0.9; }
.btn-hero-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 24px;
    background: var(--bg-surface); color: var(--text-primary);
    border: 1px solid var(--border-default);
    border-radius: 10px; font-size: 15px; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
}
.btn-hero-secondary:hover { opacity: 0.7; }
.trust-badges {
    margin-top: 28px; display: flex; gap: 24px; flex-wrap: wrap;
    font-size: 13px; color: var(--text-muted);
}
.trust-item { display: flex; gap: 6px; align-items: center; }

/* ── Hero mockup ───────────────────────────────────────────────────── */
.hero-mockup { position: relative; }
.mockup-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 20px; padding: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,.10);
    position: relative; overflow: hidden;
}
.mockup-chrome { display: flex; gap: 6px; margin-bottom: 16px; }
.chrome-dot { width: 10px; height: 10px; border-radius: 50%; }
.mockup-inner {
    background: var(--bg-page); border-radius: 12px; padding: 16px;
    display: grid; grid-template-columns: 1fr 1.4fr; gap: 14px;
}
.mockup-controls {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 10px; padding: 14px;
}
.mockup-label { font-size: 10px; color: var(--text-muted); margin-bottom: 4px; }
.dialect-chips { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 10px; }
.chip {
    font-size: 10px; padding: 3px 8px;
    background: var(--bg-page); color: var(--text-muted);
    border-radius: 99px;
}
.chip-active {
    background: color-mix(in oklab, var(--sand-500) 18%, transparent);
    color: var(--sand-600, #9B6B35); font-weight: 600;
}
.mockup-textarea {
    font-size: 10px; color: var(--text-muted);
    padding: 10px; background: var(--bg-page);
    border-radius: 6px; line-height: 1.6;
}
.mockup-generate-btn {
    margin-top: 10px; padding: 6px 10px;
    background: var(--sada-500); color: #fff;
    border-radius: 6px; text-align: center;
    font-size: 10px; font-weight: 700;
}
.mockup-preview {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 10px; overflow: hidden;
}
.mockup-img-area {
    aspect-ratio: 1/1;
    background: linear-gradient(135deg, #fef3e2, #f6d5a8);
    display: grid; place-items: center;
}
.mockup-discount { text-align: center; color: #9B6B35; }
.discount-num { font-size: 34px; font-weight: 800; }
.discount-label { font-size: 10px; font-weight: 600; }
.mockup-caption {
    padding: 10px; font-size: 10px; line-height: 1.5;
    color: var(--text-primary);
}
.metric-float {
    position: absolute; bottom: 16px; left: 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border-default);
    border-radius: 10px; padding: 10px 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    display: flex; gap: 10px; align-items: center;
}
.metric-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: color-mix(in oklab, #10B981 14%, transparent);
    color: #10B981;
    display: grid; place-items: center;
}
.metric-label { font-size: 10px; color: var(--text-muted); }
.metric-value { font-size: 13px; font-weight: 800; color: var(--text-primary); }
.hero-blob {
    position: absolute; top: -40px; left: -40px;
    width: 200px; height: 200px; border-radius: 50%;
    background: radial-gradient(circle, color-mix(in oklab, var(--sada-500) 20%, transparent), transparent 70%);
    pointer-events: none; z-index: -1;
}

/* ── Social proof ──────────────────────────────────────────────────── */
.proof-section { padding-top: 20px; padding-bottom: 60px; text-align: center; }
.proof-label { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; font-weight: 500; }
.proof-brands { display: flex; gap: 48px; justify-content: center; align-items: center; flex-wrap: wrap; opacity: 0.45; }
.proof-brand { font-size: 18px; font-weight: 700; }

/* ── Section headers ───────────────────────────────────────────────── */
.section-header { text-align: center; margin-bottom: 48px; }
.section-eyebrow { font-size: 13px; font-weight: 700; color: var(--sand-500); margin-bottom: 8px; letter-spacing: 0.05em; }
.section-title { margin: 0; font-size: 40px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary); }
.section-subtitle { font-size: 15px; color: var(--text-muted); margin-top: 8px; }

/* ── Features ──────────────────────────────────────────────────────── */
.features-section { background: transparent; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.feature-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 12px; padding: 24px;
    transition: box-shadow .2s, transform .2s;
}
.feature-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-2px); }
.feature-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: grid; place-items: center; margin-bottom: 16px;
}
.feature-title { margin: 0 0 6px; font-size: 16px; font-weight: 700; color: var(--text-primary); }
.feature-desc  { margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.7; }

/* ── Pricing ───────────────────────────────────────────────────────── */
.pricing-section { max-width: 1180px; }
.pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: start; }
.pricing-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 14px; padding: 28px;
    position: relative;
}
.pricing-card--featured {
    border: 2px solid var(--sand-500);
    transform: scale(1.03);
}
.popular-badge {
    position: absolute; top: -14px; right: 24px;
    padding: 4px 14px; font-size: 11px; font-weight: 700;
    background: var(--sand-500); color: #fff; border-radius: 99px;
}
.plan-name { font-size: 18px; font-weight: 800; margin-bottom: 10px; color: var(--text-primary); }
.plan-price-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px; }
.plan-price { font-size: 44px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary); }
.plan-currency { font-size: 14px; color: var(--text-muted); }
.plan-tokens { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
.plan-cta {
    display: block; padding: 10px; text-align: center;
    border-radius: 8px; margin-bottom: 24px;
    font-weight: 600; font-size: 14px;
    text-decoration: none; transition: opacity .15s;
}
.plan-cta--featured { background: var(--sada-600); color: #fff; border: none; }
.plan-cta--default  { background: transparent; color: var(--text-primary); border: 1px solid var(--border-default); }
.plan-cta:hover { opacity: 0.8; }
.plan-features { display: flex; flex-direction: column; gap: 10px; }
.plan-feature { display: flex; gap: 8px; font-size: 13px; align-items: flex-start; color: var(--text-primary); }

/* ── Testimonial ───────────────────────────────────────────────────── */
.testimonial-section { max-width: 900px; text-align: center; }
.testimonial-quote {
    font-size: 26px; font-weight: 700; line-height: 1.5;
    letter-spacing: -0.01em; color: var(--text-primary);
    margin: 0 0 28px;
}
.testimonial-author { display: flex; gap: 14px; justify-content: center; align-items: center; }
.testimonial-avatar {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #D4A76A, #9B6B35);
    color: #fff; font-weight: 700; font-size: 18px;
    display: grid; place-items: center;
}
.testimonial-name { font-size: 14px; font-weight: 700; color: var(--text-primary); text-align: right; }
.testimonial-role { font-size: 12px; color: var(--text-muted); text-align: right; }

/* ── CTA Banner ────────────────────────────────────────────────────── */
.cta-section { max-width: 900px; padding-top: 0; }
.cta-banner {
    background: linear-gradient(135deg, var(--sada-600), var(--sada-500));
    border-radius: 20px; padding: 48px 40px;
    text-align: center; color: #fff;
}
.cta-title { margin: 0 0 12px; font-size: 32px; font-weight: 800; letter-spacing: -0.02em; }
.cta-sub   { margin: 0 0 28px; font-size: 15px; opacity: 0.85; }
.btn-cta {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px;
    background: #fff; color: var(--sada-600);
    border-radius: 10px; font-size: 15px; font-weight: 700;
    text-decoration: none; transition: opacity .15s;
}
.btn-cta:hover { opacity: 0.9; }

/* ── Footer ────────────────────────────────────────────────────────── */
.footer {
    padding: 40px;
    border-top: 1px solid var(--border-subtle);
    background: var(--bg-surface);
}
.footer-inner {
    max-width: 1280px; margin: 0 auto;
    display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;
    font-size: 13px; color: var(--text-muted); align-items: center;
}
.footer-brand { display: flex; gap: 10px; align-items: center; }
.footer-logo {
    width: 24px; height: 24px; background: var(--sada-500);
    border-radius: 6px; color: #fff;
    display: grid; place-items: center; font-weight: 800; font-size: 13px;
}
.footer-name { font-weight: 700; color: var(--text-primary); }
.footer-links { display: flex; gap: 20px; flex-wrap: wrap; }
.footer-link { color: var(--text-muted); text-decoration: none; transition: opacity .15s; }
.footer-link:hover { opacity: 0.7; }

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .nav-links   { display: none; }
    .nav-actions { display: none; }
    .hamburger   { display: flex; }
    .nav-inner   { padding: 0 20px; }
    .hero-grid   { grid-template-columns: 1fr; gap: 32px; }
    .hero-mockup { display: none; }
    .hero-title  { font-size: 38px; }
    .features-grid { grid-template-columns: 1fr 1fr; }
    .pricing-grid  { grid-template-columns: 1fr; }
    .pricing-card--featured { transform: scale(1); }
    .section { padding: 60px 20px; }
    .hero-section { padding-top: 60px; }
    .cta-banner { padding: 32px 24px; }
    .cta-title  { font-size: 24px; }
    .section-title { font-size: 30px; }
    .testimonial-quote { font-size: 20px; }
    .footer { padding: 32px 20px; }
    .proof-brands { gap: 28px; }
}

@media (max-width: 560px) {
    .features-grid { grid-template-columns: 1fr; }
    .hero-title    { font-size: 32px; }
    .hero-ctas     { flex-direction: column; }
    .btn-hero-primary, .btn-hero-secondary { justify-content: center; }
    .trust-badges  { gap: 14px; }
    .section-title { font-size: 26px; }
    .proof-brands  { gap: 20px; }
}
</style>

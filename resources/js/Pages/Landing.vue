<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useUiStore } from '@/Stores/ui';

const ui = useUiStore();

const features = [
    {
        icon: 'sparkle',
        title: 'توليد محتوى بلهجتك',
        desc: 'فصحى، سعودي، إماراتي، كويتي، قطري، بحريني، عُماني — كلها مع فهم عميق للسياق.',
        color: '#0F6F5C',
    },
    {
        icon: 'moon',
        title: 'حملات موسمية بضغطة',
        desc: 'رمضان، العيدين، اليوم الوطني، التأسيس — قوالب وأفكار جاهزة ومحترمة.',
        color: '#C8965F',
    },
    {
        icon: 'calendar',
        title: 'جدولة ذكية على Meta',
        desc: 'انشر على انستجرام وفيسبوك من جدول واحد، مع اقتراح أفضل أوقات النشر.',
        color: '#3B82F6',
    },
    {
        icon: 'megaphone',
        title: 'إعلانات ممولة',
        desc: 'أطلق حملات مدفوعة على Meta من نفس المكان — بميزانية صغيرة أو كبيرة.',
        color: '#F59E0B',
    },
    {
        icon: 'chart',
        title: 'تحليلات وتقارير',
        desc: 'رؤى ذكية بالعربية + تقارير قابلة للتخصيص بشعارك (مثالية للوكالات).',
        color: '#10B981',
    },
    {
        icon: 'building',
        title: 'Workspaces للوكالات',
        desc: 'أدر عملاءك في مساحات منفصلة، بفواتير وأذونات مستقلة.',
        color: '#0A5A4B',
    },
];

const plans = [
    {
        name: 'Starter',
        price: '٩٩',
        tokens: '١٬٠٠٠',
        featured: false,
        features: ['مستخدم واحد', 'مساحة عمل واحدة', 'انستجرام + فيسبوك', 'توليد محتوى بـ٣ لهجات', 'دعم عبر البريد'],
    },
    {
        name: 'Growth',
        price: '٢٩٩',
        tokens: '٢٬٠٠٠',
        featured: true,
        features: ['حتى ٣ مستخدمين', '٣ مساحات عمل', 'كل المنصات والميزات', 'كل اللهجات الخليجية', 'حملات إعلانية Meta', 'دعم فوري'],
    },
    {
        name: 'Agency',
        price: '٧٩٩',
        tokens: '١٠٬٠٠٠',
        featured: false,
        features: ['مستخدمون غير محدودين', 'مساحات غير محدودة', 'White-label تقارير', 'API وصلاحيات متقدمة', 'مدير حساب مخصص'],
    },
];

const brands = ['متجر أنيق', 'قهوة الرواق', 'طلبات فاست', 'لاونج ٤٢', 'بيت الورد', 'نكهات'];

const navLinks = ['المميزات', 'التسعير', 'المواسم', 'المدونة'];
</script>

<template>
    <Head title="صدى — منصة التسويق الذكي للسوق الخليجي" />

    <div
        dir="rtl"
        style="background-color: var(--bg-page); color: var(--text-primary); font-family: var(--font-arabic); min-height: 100vh;"
    >

        <!-- ── Navbar ──────────────────────────────────────────────── -->
        <nav
            class="sticky top-0 z-40"
            style="
                padding: 16px 40px;
                background: color-mix(in oklab, var(--bg-page) 85%, transparent);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid var(--border-subtle);
                display: flex;
                align-items: center;
                justify-content: space-between;
            "
        >
            <!-- Logo + links -->
            <div style="display: flex; align-items: center; gap: 40px;">
                <div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 22px; color: var(--text-primary);">
                    <div style="
                        width: 32px; height: 32px;
                        background: var(--color-sada-500);
                        border-radius: 9px; color: #fff;
                        display: grid; place-items: center;
                        font-weight: 800; font-size: 16px;
                    ">ص</div>
                    صدى
                </div>
                <div class="hidden md:flex" style="gap: 24px; display: flex;">
                    <a
                        v-for="link in navLinks"
                        :key="link"
                        href="#"
                        style="font-size: 14px; color: var(--text-muted); font-weight: 500; text-decoration: none;"
                        class="hover:opacity-70 transition-opacity"
                    >{{ link }}</a>
                </div>
            </div>

            <!-- CTA buttons -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <!-- Theme toggle -->
                <button
                    @click="ui.toggleTheme()"
                    :title="ui.theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
                    style="
                        width: 38px; height: 38px; border-radius: 10px;
                        display: grid; place-items: center; cursor: pointer;
                        background: var(--bg-muted); color: var(--text-muted);
                        border: 1px solid var(--border-subtle);
                        transition: all .15s;
                    "
                >
                    <svg v-if="ui.theme === 'dark'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        <circle cx="12" cy="12" r="5"/>
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <Link
                    href="/login"
                    style="
                        padding: 8px 16px;
                        font-size: 14px; font-weight: 500;
                        color: var(--text-primary);
                        background: transparent;
                        border: 1px solid var(--border-default);
                        border-radius: 8px;
                        text-decoration: none;
                        display: inline-flex; align-items: center;
                        transition: opacity .15s;
                    "
                    class="hover:opacity-70"
                >تسجيل دخول</Link>
                <Link
                    href="/register"
                    style="
                        padding: 8px 16px;
                        font-size: 14px; font-weight: 600;
                        color: #fff;
                        background: var(--color-sada-600);
                        border-radius: 8px;
                        text-decoration: none;
                        display: inline-flex; align-items: center; gap: 6px;
                        transition: opacity .15s;
                    "
                    class="hover:opacity-90"
                >
                    ابدأ مجاناً
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </Link>
            </div>
        </nav>

        <!-- ── Hero ───────────────────────────────────────────────── -->
        <section style="padding: 80px 40px 60px; max-width: 1280px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center;">

                <!-- Text -->
                <div>
                    <!-- Badge -->
                    <span style="
                        display: inline-flex; align-items: center; gap: 6px;
                        padding: 5px 14px;
                        background: color-mix(in oklab, var(--color-sand-500) 15%, transparent);
                        color: var(--color-sand-600, #9B6B35);
                        border-radius: 99px; font-size: 12px; font-weight: 600;
                        margin-bottom: 20px;
                    ">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                        جديد — لهجات خليجية بدقة
                    </span>

                    <h1 style="
                        margin: 0; font-size: 52px; font-weight: 800;
                        letter-spacing: -0.02em; line-height: 1.1;
                        color: var(--text-primary);
                    ">
                        محتوى تسويقي خليجي
                        <span style="
                            display: block;
                            background: linear-gradient(90deg, var(--color-sada-500), var(--color-sand-500));
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;
                        ">يتكلم لغتك.</span>
                    </h1>

                    <p style="
                        margin: 20px 0 32px;
                        font-size: 17px; line-height: 1.7;
                        color: var(--text-muted); max-width: 520px;
                    ">
                        منصة ذكاء اصطناعي كاملة لتوليد المحتوى، جدولة النشر، وإطلاق الحملات —
                        بلهجتك، وبمواسمك، ومن مكان واحد.
                    </p>

                    <!-- CTAs -->
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <Link
                            href="/register"
                            style="
                                display: inline-flex; align-items: center; gap: 8px;
                                padding: 14px 24px;
                                background: var(--color-sada-600); color: #fff;
                                border-radius: 10px; font-size: 15px; font-weight: 700;
                                text-decoration: none; transition: opacity .15s;
                            "
                            class="hover:opacity-90"
                        >
                            جرّب مجاناً
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                        </Link>
                        <button style="
                            display: inline-flex; align-items: center; gap: 8px;
                            padding: 14px 24px;
                            background: var(--bg-surface); color: var(--text-primary);
                            border: 1px solid var(--border-default);
                            border-radius: 10px; font-size: 15px; font-weight: 600;
                            cursor: pointer; transition: opacity .15s;
                        " class="hover:opacity-70">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                            شاهد عرضاً مباشراً
                        </button>
                    </div>

                    <!-- Trust badges -->
                    <div style="margin-top: 28px; display: flex; gap: 24px; font-size: 13px; color: var(--text-muted); flex-wrap: wrap;">
                        <span style="display: flex; gap: 6px; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            ٣٠ يوم مجاناً
                        </span>
                        <span style="display: flex; gap: 6px; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            بدون بطاقة ائتمان
                        </span>
                        <span style="display: flex; gap: 6px; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            دعم عربي ٢٤/٧
                        </span>
                    </div>
                </div>

                <!-- Hero mockup -->
                <div style="position: relative;">
                    <div style="
                        background: var(--bg-surface);
                        border: 1px solid var(--border-default);
                        border-radius: 20px; padding: 20px;
                        box-shadow: 0 20px 60px rgba(0,0,0,.10);
                        position: relative; overflow: hidden;
                    ">
                        <!-- Window chrome -->
                        <div style="display: flex; gap: 6px; margin-bottom: 16px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #FF5F57;"></div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #FEBC2E;"></div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #28C840;"></div>
                        </div>

                        <!-- Inner layout -->
                        <div style="
                            background: var(--bg-page);
                            border-radius: 12px; padding: 16px;
                            display: grid; grid-template-columns: 1fr 1.4fr; gap: 14px;
                        ">
                            <!-- Controls card -->
                            <div style="
                                background: var(--bg-surface);
                                border: 1px solid var(--border-subtle);
                                border-radius: 10px; padding: 14px;
                            ">
                                <div style="font-size: 10px; color: var(--text-muted); margin-bottom: 4px;">اللهجة</div>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 10px;">
                                    <span style="font-size: 10px; padding: 3px 8px; background: color-mix(in oklab, var(--color-sand-500) 18%, transparent); color: var(--color-sand-600, #9B6B35); border-radius: 99px; font-weight: 600;">🇸🇦 سعودي</span>
                                    <span style="font-size: 10px; padding: 3px 8px; background: var(--bg-page); color: var(--text-muted); border-radius: 99px;">🇦🇪 إماراتي</span>
                                    <span style="font-size: 10px; padding: 3px 8px; background: var(--bg-page); color: var(--text-muted); border-radius: 99px;">فصحى</span>
                                </div>
                                <div style="
                                    font-size: 10px; color: var(--text-muted);
                                    padding: 10px; background: var(--bg-page);
                                    border-radius: 6px; line-height: 1.6;
                                ">
                                    خصم ٣٠٪ بمناسبة اليوم الوطني...
                                </div>
                                <div style="
                                    margin-top: 10px; padding: 6px 10px;
                                    background: var(--color-sada-500); color: #fff;
                                    border-radius: 6px; text-align: center;
                                    font-size: 10px; font-weight: 700;
                                ">✨ ولّد</div>
                            </div>

                            <!-- Preview card -->
                            <div style="
                                background: #fff;
                                border: 1px solid var(--border-subtle);
                                border-radius: 10px; overflow: hidden;
                            ">
                                <div style="
                                    aspect-ratio: 1/1;
                                    background: linear-gradient(135deg, #fef3e2, #f6d5a8);
                                    display: grid; place-items: center;
                                ">
                                    <div style="text-align: center; color: #9B6B35;">
                                        <div style="font-size: 34px; font-weight: 800;">٣٠٪</div>
                                        <div style="font-size: 10px; font-weight: 600;">خصم</div>
                                    </div>
                                </div>
                                <div style="padding: 10px; font-size: 10px; line-height: 1.5; color: #111;">
                                    <strong>متجر_أنيق</strong> عطيناكم ٣٠٪ خصم على كل شي 🇸🇦
                                </div>
                            </div>
                        </div>

                        <!-- Floating metric -->
                        <div style="
                            position: absolute; bottom: 16px; left: 16px;
                            background: var(--bg-surface);
                            border: 1px solid var(--border-default);
                            border-radius: 10px; padding: 10px 14px;
                            box-shadow: 0 4px 12px rgba(0,0,0,.08);
                            display: flex; gap: 10px; align-items: center;
                        ">
                            <div style="
                                width: 28px; height: 28px; border-radius: 7px;
                                background: #ecfdf5; color: #10B981;
                                display: grid; place-items: center;
                            ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: var(--text-muted);">التفاعل هذا الأسبوع</div>
                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">+٤٥٪</div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative gradient blob -->
                    <div style="
                        position: absolute; top: -40px; left: -40px;
                        width: 200px; height: 200px; border-radius: 50%;
                        background: radial-gradient(circle, color-mix(in oklab, var(--color-sada-500) 20%, transparent), transparent 70%);
                        pointer-events: none; z-index: -1;
                    "></div>
                </div>
            </div>
        </section>

        <!-- ── Social proof ───────────────────────────────────────── -->
        <section style="padding: 20px 40px 60px; max-width: 1280px; margin: 0 auto; text-align: center;">
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px; font-weight: 500;">
                موثوق من أكثر من ١٥٠ متجراً وعلامة خليجية
            </div>
            <div style="display: flex; gap: 48px; justify-content: center; align-items: center; flex-wrap: wrap; opacity: 0.45;">
                <span
                    v-for="brand in brands"
                    :key="brand"
                    style="font-size: 18px; font-weight: 700;"
                >{{ brand }}</span>
            </div>
        </section>

        <!-- ── Features ───────────────────────────────────────────── -->
        <section style="padding: 60px 40px; max-width: 1280px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 48px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--color-sand-500); margin-bottom: 8px; letter-spacing: 0.05em;">
                    المميزات
                </div>
                <h2 style="margin: 0; font-size: 40px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary);">
                    كل ما تحتاجه لتسويقك الرقمي
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    style="
                        background: var(--bg-surface);
                        border: 1px solid var(--border-subtle);
                        border-radius: 12px; padding: 24px;
                        transition: box-shadow .2s, transform .2s;
                    "
                    class="feature-card"
                >
                    <div :style="{
                        width: '44px', height: '44px', borderRadius: '11px',
                        background: `color-mix(in oklab, ${feature.color} 14%, transparent)`,
                        color: feature.color,
                        display: 'grid', placeItems: 'center',
                        marginBottom: '16px', fontSize: '22px',
                    }">
                        <!-- sparkle -->
                        <svg v-if="feature.icon === 'sparkle'" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                        <!-- moon -->
                        <svg v-else-if="feature.icon === 'moon'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                        <!-- calendar -->
                        <svg v-else-if="feature.icon === 'calendar'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <!-- megaphone -->
                        <svg v-else-if="feature.icon === 'megaphone'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                        <!-- chart -->
                        <svg v-else-if="feature.icon === 'chart'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        <!-- building -->
                        <svg v-else-if="feature.icon === 'building'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 700; color: var(--text-primary);">{{ feature.title }}</h3>
                    <p style="margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.7;">{{ feature.desc }}</p>
                </div>
            </div>
        </section>

        <!-- ── Pricing ────────────────────────────────────────────── -->
        <section style="padding: 60px 40px; max-width: 1180px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="margin: 0; font-size: 40px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary);">
                    أسعار شفافة، بلا مفاجآت
                </h2>
                <p style="font-size: 15px; color: var(--text-muted); margin-top: 8px;">
                    ابدأ مجاناً، وارقّ عندما تحتاج. دفع بالريال/الدرهم عبر Moyasar & Tap.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: start;">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    :style="{
                        background: 'var(--bg-surface)',
                        border: plan.featured ? '2px solid var(--color-sand-500)' : '1px solid var(--border-subtle)',
                        borderRadius: '14px',
                        padding: '28px',
                        position: 'relative',
                        transform: plan.featured ? 'scale(1.03)' : 'scale(1)',
                    }"
                >
                    <!-- Popular badge -->
                    <div
                        v-if="plan.featured"
                        style="
                            position: absolute; top: -14px; right: 24px;
                            padding: 4px 14px; font-size: 11px; font-weight: 700;
                            background: var(--color-sand-500); color: #fff;
                            border-radius: 99px;
                        "
                    >الأكثر شعبية</div>

                    <div style="font-size: 18px; font-weight: 800; margin-bottom: 10px; color: var(--text-primary);">{{ plan.name }}</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px;">
                        <span style="font-size: 44px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary);">{{ plan.price }}</span>
                        <span style="font-size: 14px; color: var(--text-muted);">ر.س / شهر</span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">{{ plan.tokens }} توكن/شهر</div>

                    <Link
                        href="/register"
                        :style="{
                            display: 'block',
                            padding: '10px',
                            textAlign: 'center',
                            borderRadius: '8px',
                            marginBottom: '24px',
                            fontWeight: 600,
                            fontSize: '14px',
                            textDecoration: 'none',
                            transition: 'opacity .15s',
                            background: plan.featured ? 'var(--color-sada-600)' : 'transparent',
                            color: plan.featured ? '#fff' : 'var(--text-primary)',
                            border: plan.featured ? 'none' : '1px solid var(--border-default)',
                        }"
                        class="hover:opacity-80"
                    >ابدأ بـ{{ plan.name }}</Link>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div
                            v-for="feat in plan.features"
                            :key="feat"
                            style="display: flex; gap: 8px; font-size: 13px; align-items: flex-start;"
                        >
                            <svg style="flex-shrink: 0; margin-top: 2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="plan.featured ? 'var(--color-sand-500)' : 'var(--color-sada-500)'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span style="color: var(--text-primary);">{{ feat }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Testimonial ────────────────────────────────────────── -->
        <section style="padding: 60px 40px; max-width: 900px; margin: 0 auto; text-align: center;">
            <div style="
                font-size: 26px; font-weight: 700; line-height: 1.5;
                letter-spacing: -0.01em; color: var(--text-primary);
            ">
                «لأول مرة، أداة تسويقية تفهم لهجتنا الخليجية وتحترم مواسمنا.
                صدى وفّرت لنا ساعات كل أسبوع.»
            </div>
            <div style="display: flex; gap: 14px; justify-content: center; align-items: center; margin-top: 28px;">
                <div style="
                    width: 48px; height: 48px; border-radius: 50%;
                    background: linear-gradient(135deg, var(--color-sand-400, #D4A76A), var(--color-sand-600, #9B6B35));
                    color: #fff; font-weight: 700; font-size: 18px;
                    display: grid; place-items: center;
                ">نس</div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: 700; color: var(--text-primary);">نورة السبيعي</div>
                    <div style="font-size: 12px; color: var(--text-muted);">مديرة التسويق، متجر أنيق</div>
                </div>
            </div>
        </section>

        <!-- ── CTA banner ─────────────────────────────────────────── -->
        <section style="padding: 0 40px 80px; max-width: 900px; margin: 0 auto;">
            <div style="
                background: linear-gradient(135deg, var(--color-sada-600), var(--color-sada-500));
                border-radius: 20px; padding: 48px 40px;
                text-align: center; color: #fff;
            ">
                <h2 style="margin: 0 0 12px; font-size: 32px; font-weight: 800; letter-spacing: -0.02em;">
                    ابدأ تسويقك بالذكاء الاصطناعي اليوم
                </h2>
                <p style="margin: 0 0 28px; font-size: 15px; opacity: 0.85;">
                    ٣٠ يوماً مجاناً، بدون بطاقة ائتمان.
                </p>
                <Link
                    href="/register"
                    style="
                        display: inline-flex; align-items: center; gap: 8px;
                        padding: 14px 28px;
                        background: #fff; color: var(--color-sada-600);
                        border-radius: 10px; font-size: 15px; font-weight: 700;
                        text-decoration: none; transition: opacity .15s;
                    "
                    class="hover:opacity-90"
                >
                    أنشئ حسابك مجاناً
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </Link>
            </div>
        </section>

        <!-- ── Footer ─────────────────────────────────────────────── -->
        <footer style="
            padding: 40px;
            border-top: 1px solid var(--border-subtle);
            background: var(--bg-surface);
        ">
            <div style="
                max-width: 1280px; margin: 0 auto;
                display: flex; justify-content: space-between;
                flex-wrap: wrap; gap: 20px;
                font-size: 13px; color: var(--text-muted);
                align-items: center;
            ">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="
                        width: 24px; height: 24px; background: var(--color-sada-500);
                        border-radius: 6px; color: #fff;
                        display: grid; place-items: center; font-weight: 800; font-size: 13px;
                    ">ص</div>
                    <span style="font-weight: 700; color: var(--text-primary);">صدى</span>
                    <span>© ٢٠٢٦ — جميع الحقوق محفوظة</span>
                </div>
                <div style="display: flex; gap: 20px;">
                    <a href="#" style="color: var(--text-muted); text-decoration: none;" class="hover:opacity-70">الخصوصية</a>
                    <a href="#" style="color: var(--text-muted); text-decoration: none;" class="hover:opacity-70">الشروط</a>
                    <a href="#" style="color: var(--text-muted); text-decoration: none;" class="hover:opacity-70">الدعم</a>
                    <a href="#" style="color: var(--text-muted); text-decoration: none;" class="hover:opacity-70">تواصل</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.feature-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

/* Responsive: collapse grid to single column on small screens */
@media (max-width: 768px) {
    section > div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    section > div[style*="grid-template-columns: repeat(3"] {
        grid-template-columns: 1fr !important;
    }
    h1 {
        font-size: 36px !important;
    }
}
</style>

---
title: UI/UX Design Prompt — منصة صدى (Sada)

---

# UI/UX Design Prompt — منصة صدى (Sada)
## برومت جاهز لأدوات تصميم AI (v0, Lovable, Figma Make, Claude Artifacts, Cursor)

---

## 📋 كيف تستخدم هذا الملف

- **للتصميم الشامل (كل الشاشات):** استخدم §1 الكامل
- **لشاشة واحدة:** انسخ §0 (السياق) + §2 (Design System) + القسم الخاص بالشاشة من §3
- **للـ Design System فقط (Figma/Storybook):** انسخ §0 + §2

---

# §0 — System Context (انسخه في بداية كل برومت)

```
أنت مصمم UI/UX أول (Senior Product Designer) متخصص في منتجات SaaS عربية RTL-first.
المطلوب: تصميم واجهة لمنصة "صدى" (Sada) — SaaS لتوليد المحتوى التسويقي بالذكاء الاصطناعي
وإدارة حسابات السوشيال ميديا، موجهة للسوق الخليجي (السعودية، الإمارات، الكويت، قطر، البحرين، عمان).

المستخدمون الأساسيون:
1. أصحاب متاجر إلكترونية (Salla/Zid/Shopify) — الأولوية
2. وكالات تسويق رقمي تدير حسابات متعددة
3. Content creators خليجيون

القيمة الجوهرية:
- توليد نصوص تسويقية بلهجات خليجية (فصحى/سعودي/إماراتي/كويتي/بحريني/قطري/عماني)
- حملات موسمية جاهزة (رمضان، العيدين، اليوم الوطني السعودي، التأسيس، الاتحاد الإماراتي)
- جدولة ونشر عضوي + حملات إعلانية مدفوعة على Meta (Instagram + Facebook)
- تحليلات وتقارير مع AI insights
- Multi-workspace للوكالات
- دفع محلي عبر Moyasar/Tap

قواعد حرجة:
- RTL حقيقي — ليس مرآة لتصميم إنجليزي
- المحاذاة، الأيقونات الاتجاهية (arrows, chevrons)، progress bars، breadcrumbs كلها تتدفق من اليمين لليسار
- تجنب الشكل الجنيسي للـ SaaS الغربي (no purple gradients, no generic hero illustrations)
- استوحي من جودة Linear, Raycast, Notion — لكن بهوية خليجية/عربية أصيلة
- Typography عربية صحيحة — ليست Arial أو Tahoma
```

---

# §1 — البرومت الشامل (Copy-Paste جاهز)

```
أنشئ تصميم UI/UX كامل لمنصة SaaS عربية اسمها "صدى" (Sada) لإدارة التسويق الرقمي
بالذكاء الاصطناعي، موجهة للسوق الخليجي.

[انسخ §0 هنا]
[انسخ §2 هنا — Design System]
[انسخ §3 هنا — تفاصيل الشاشات]

مخرجات مطلوبة:
- تصاميم عالية الدقة (high-fidelity) لكل الشاشات المذكورة
- Responsive: Desktop (1440px) + Tablet (768px) + Mobile (375px)
- حالات: Empty state, Loading state, Error state لكل شاشة رئيسية
- Light mode أولاً، ثم Dark mode
- Micro-interactions موثقة (hovers, transitions, loading states)
- Component library قابل لإعادة الاستخدام

لا تستخدم:
- stock illustrations عامة
- purple/blue gradients الشائعة في SaaS
- typography إنجليزية على نصوص عربية (لا Arial, لا system-ui الافتراضي)
- mirror-flipped UI من تصاميم إنجليزية

استوحي من:
- Linear (hierarchy, spacing, density)
- Raycast (command palette, keyboard-first)
- Notion (content-first, clean)
- Arabic design references: منصة سلة (Salla)، موقع stc، رؤية 2030 visual language
```

---

# §2 — Design System

## 🎨 Color Palette

### Primary — صدى (Sada) Identity
```
--sada-500: #0F6F5C  /* Primary — أخضر زيتوني خليجي، دافئ ومتميز */
--sada-600: #0A5A4B  /* Hover */
--sada-700: #074539  /* Active/Pressed */
--sada-400: #2A9080  /* Light accent */
--sada-100: #E6F4F0  /* Backgrounds, tags */
--sada-50:  #F3FAF7  /* Subtle surfaces */
```

### Accent — رمال (Rimāl)
```
--sand-500: #C8965F  /* Accent — ذهبي رملي للـ highlights */
--sand-400: #D4A876
--sand-100: #F7ECD9  /* موسمي — رمضان/عيد */
```

### Neutrals — محايد دافئ (ليس رمادي كلاسيكي)
```
--ink-900: #0E1512   /* Primary text */
--ink-700: #2C3632   /* Secondary text */
--ink-500: #5E6A64   /* Muted text */
--ink-300: #A8B1AD   /* Borders, dividers */
--ink-100: #EEF0EE   /* Subtle surfaces */
--ink-50:  #F7F8F7   /* Page background */
--white:   #FFFFFF
```

### Semantic
```
--success: #0D8B5A   /* أخضر — متناسق مع الهوية */
--warning: #C97A0F   /* برتقالي دافئ */
--error:   #B5322F   /* أحمر هادئ، ليس CTA أحمر صريح */
--info:    #2E6FA8
```

### Dark Mode
```
--bg-dark:    #0A0F0D   /* أعمق من الأسود الخام */
--surface-1:  #121916
--surface-2:  #1A2320
--sada-dark:  #2A9080  /* Primary في dark mode */
```

## ✍️ Typography

```css
/* العربية */
--font-arabic: 'IBM Plex Sans Arabic', 'Cairo', system-ui;
--font-arabic-display: 'Tajawal', 'IBM Plex Sans Arabic', sans-serif;

/* الإنجليزية (في الـ mixed content) */
--font-latin: 'Inter', 'IBM Plex Sans', -apple-system, sans-serif;
--font-mono: 'JetBrains Mono', 'IBM Plex Mono', monospace;
```

### Type Scale
```
Display-XL: 48px / 1.1 / font-weight 700   — Hero headings
Display-L:  36px / 1.15 / 700               — Page titles
H1:         28px / 1.2 / 700
H2:         22px / 1.3 / 600
H3:         18px / 1.4 / 600
Body-L:     16px / 1.6 / 400
Body:       14px / 1.6 / 400
Small:      13px / 1.5 / 400
Caption:    12px / 1.4 / 500
```

### قواعد Typography عربية
- **Line-height أعلى من الإنجليزية:** استخدم 1.6-1.7 للـ body بدل 1.5
- **Letter-spacing = 0** (لا تضيف tracking للعربية مثل الإنجليزية)
- **Font-weight:** العربية تحتاج weights أثقل بصرياً — 500 هو "regular" فعلياً، 600 للتأكيد، 700 للـ bold
- **أرقام:** استخدم Arabic-Indic (٠١٢٣٤٥٦٧٨٩) في السياقات الرسمية/الموسمية، European (0123456789) في لوحات التحكم

## 📐 Spacing & Layout

```
Scale: 4px base
--space-1: 4px
--space-2: 8px
--space-3: 12px
--space-4: 16px
--space-5: 24px
--space-6: 32px
--space-8: 48px
--space-10: 64px
--space-12: 96px

Container: max-width 1440px
Sidebar: 260px (expanded) / 72px (collapsed)
Content padding: 32px (desktop), 16px (mobile)
```

## 🔲 Border Radius

```
--radius-sm: 6px     /* inputs, small buttons */
--radius-md: 10px    /* cards, modals */
--radius-lg: 16px    /* hero cards, feature tiles */
--radius-xl: 24px    /* marketing sections */
--radius-full: 9999px /* pills, avatars */
```

## 🌓 Shadows

```
--shadow-sm: 0 1px 2px rgba(14, 21, 18, 0.05)
--shadow-md: 0 4px 12px rgba(14, 21, 18, 0.08)
--shadow-lg: 0 12px 32px rgba(14, 21, 18, 0.12)
--shadow-xl: 0 24px 64px rgba(14, 21, 18, 0.16)
```

## ⚡ Motion

```
--ease-out: cubic-bezier(0.16, 1, 0.3, 1)
--ease-in-out: cubic-bezier(0.65, 0, 0.35, 1)
--duration-fast: 150ms
--duration-base: 250ms
--duration-slow: 400ms
```

**قواعد الحركة:**
- Transitions: opacity, transform فقط (ليس width/height)
- Loading states: skeleton أو shimmer، ليس spinners عامة
- Hover: lift خفيف (translateY -2px) + shadow تكبير
- RTL: slide-in من اليمين (translateX positive → 0)

---

# §3 — تفاصيل الشاشات (Screen-by-Screen Specs)

## A. Landing Page (صفحة التسويق)

**الهدف:** تحويل الزائر لتجربة مجانية خلال 15 ثانية.

**الأقسام من أعلى لأسفل:**
1. **Nav Bar** — شعار صدى يسار (في RTL = فعلياً في أقصى اليمين)، روابط: المميزات، التسعير، المواسم، المدونة. زران: تسجيل دخول (ghost) + ابدأ مجاناً (primary)
2. **Hero:**
   - H1 عربي display-xl: «محتوى تسويقي خليجي يتكلم لغتك.» (أو مشابه)
   - Sub: «منصة ذكاء اصطناعي كاملة لتوليد المحتوى، جدولة النشر، وإطلاق الحملات — بلهجتك وبمواسمك.»
   - CTA primary: «جرّب مجاناً» + CTA ghost: «شاهد عرضاً مباشراً»
   - Visual: mockup حقيقي للتطبيق (ليس illustration عامة) — عربي RTL
3. **Social proof شريط:** «موثوق من 150+ متجر وعلامة خليجية» + لوجوهات (placeholder)
4. **Features grid (3x2):**
   - توليد محتوى بلهجتك
   - حملات موسمية بضغطة زر
   - جدولة ذكية على Meta
   - إعلانات ممولة من مكان واحد
   - تحليلات وتقارير للعملاء
   - Workspaces للوكالات
5. **Seasonal showcase:** تقويم تفاعلي يعرض المناسبات القادمة (رمضان، اليوم الوطني السعودي...) مع عدد الأيام
6. **Pricing** — ثلاث بطاقات: Starter / Growth / Agency، بأسعار ريال/درهم
7. **Testimonials** — 3 شهادات بعربي حقيقي، ليست lorem ipsum
8. **FAQ** — accordion
9. **Footer** — روابط، شبكات التواصل، بيان الخصوصية

**نوتة RTL حرجة:** الأيقونات الاتجاهية (→ «اقرأ المزيد») تصبح (اقرأ المزيد ←). الـ arrow يكون في الجانب الأيسر.

---

## B. Onboarding Wizard (بعد التسجيل)

**4 خطوات، progress bar من اليمين لليسار:**

**Step 1 — إعداد الـ Workspace:**
- اسم الـ Workspace (مثلاً «متجر أنيق»)
- نوع النشاط (dropdown: تجارة إلكترونية، مطعم، خدمات، إلخ)
- الدولة المستهدفة (chips متعددة: 🇸🇦 🇦🇪 🇰🇼 🇶🇦 🇧🇭 🇴🇲)
- اللهجة الافتراضية (select)

**Step 2 — هوية العلامة (Brand Identity):**
- رفع الشعار
- ألوان العلامة (color pickers)
- نبرة الصوت (toggles: رسمي/ودود/عصري/فاخر)
- كلمات محظورة (tags input)
- 2-3 أمثلة لمنشورات سابقة (textarea)

**Step 3 — ربط حسابات Meta:**
- زر كبير «اربط حساب Instagram» + «اربط صفحة Facebook»
- OAuth flow
- عرض الحسابات المربوطة كـ cards مع إمكانية الإزالة

**Step 4 — جاهز!**
- Confetti خفيف
- CTA: «ولّد أول منشور» أو «تصفح القوالب»

---

## C. Dashboard Home

**Layout:** Sidebar (يمين في RTL) + Main content

**Sidebar:**
- Logo صدى أعلى
- Workspace switcher (dropdown مع avatar)
- Nav items:
  - 🏠 الرئيسية
  - ✨ توليد محتوى
  - 📅 التقويم
  - 📣 الحملات الإعلانية
  - 🌙 المواسم
  - 📊 التحليلات
  - ⚙️ الإعدادات
- أسفل Sidebar: رصيد التوكنز (progress + "شحن")، اسم المستخدم

**Main Content (4 قطاعات):**

1. **Welcome banner:** «صباح الخير، أحمد. لديك 3 منشورات مجدولة اليوم.»

2. **KPI cards (4 في صف):**
   - منشورات هذا الأسبوع: 12 (+20% vs last week)
   - Reach: 45,230
   - Engagement rate: 4.8%
   - التوكنز المستخدمة: 1,240 / 2,000

3. **منشورات قادمة (next 7 days):** timeline بسيط RTL، بطاقات منشور مصغرة

4. **تنبيه موسمي:** banner بلون --sand-100: «اليوم الوطني السعودي خلال ١٤ يوماً — هل تريد أن نولّد لك حملة جاهزة؟» مع زر CTA

5. **AI Insight card:** «📈 منشوراتك المسائية (8-10م) تحقق engagement أعلى 45% من الصباحية. جرّب جدولة المزيد في هذا الوقت.»

---

## D. Generate Content (الشاشة الأهم)

**Split screen — الإدخال يمين، المعاينة يسار:**

**Panel يمين — Input:**
- نوع المحتوى: Segmented control (منشور / ريل / قصة / إعلان)
- المنصة: toggle icons (Instagram / Facebook)
- اللهجة: select مع أعلام الدول
- استخدام هوية العلامة: toggle
- Prompt: textarea كبير RTL placeholder: «اكتب وصفاً قصيراً للفكرة... مثلاً: خصم 30% على كل المنتجات بمناسبة اليوم الوطني»
- Advanced (collapsible):
  - طول النص
  - Emojis (toggle)
  - CTA مخصص
- زر primary كبير: «✨ ولّد المحتوى»

**Panel يسار — Output:**
- 3 خيارات مولدة في بطاقات قابلة للتوسع
- كل بطاقة: النص، hashtags، زرّي «تعديل» و«استخدام»
- عداد توكنز مستهلكة
- زر «ولّد المزيد» أسفل

**Preview Mode:** بعد اختيار منشور، mockup حقيقي لـ Instagram/Facebook (بـ RTL) يعرض كيف سيبدو المنشور

**Action bar أسفل:** نشر فوري / جدولة (date picker) / حفظ كـ Draft

---

## E. Content Calendar

**View toggles:** شهري / أسبوعي / قائمة

**Monthly View:**
- Grid 7×5 — الأيام من الأحد يمين إلى السبت يسار (RTL بدون عكس ترتيب الأسبوع منطقياً — الأحد أول)
- Cells تعرض منشورات كـ dots ملونة حسب المنصة
- Hover على cell → tooltip بتفاصيل
- Drag & drop بين التواريخ لإعادة الجدولة
- Badges للمواسم في الخلفية (رمضان شهر كامل مثلاً بخلفية خفيفة --sand-100)

**Sidebar (يسار في هذه الحالة):**
- فلاتر: المنصة، الـ Workspace، الحالة (draft/scheduled/published)
- Quick actions: + منشور جديد، + حملة، + مستورد

**Week View:** أعمدة أيام، timeline ساعات، منشورات كـ cards

---

## F. Seasonal Hub 🌙

**Header:** تقويم هجري + ميلادي جنباً لجنب (Arabic-Indic numerals)

**Main content:**
- **المناسبة القادمة (hero card):** بخلفية مزخرفة خليجية (نخيل، هندسة إسلامية حديثة)، عنوان كبير، countdown، زر «ولّد حملة جاهزة»
- **Grid للمناسبات القادمة** (6 cards):
  - رمضان المبارك
  - عيد الفطر
  - عيد الأضحى
  - اليوم الوطني السعودي (٢٣ سبتمبر)
  - يوم التأسيس السعودي (٢٢ فبراير)
  - يوم الاتحاد الإماراتي (٢ ديسمبر)
- كل card تعرض: الاسم، التاريخ، عدد القوالب المتوفرة، زر «استكشف»

**Campaign Generator Modal (لما المستخدم يضغط «ولّد حملة»):**
- عدد المنشورات (slider 3-21)
- النبرة
- الصناعة
- الأهداف (checkboxes: awareness, sales, engagement)
- Preview للخطة قبل التوليد

**قاعدة تصميمية:** استخدم --sand-100, --sand-400 للموسمية، لا تستخدم ألوان نيون أو gradients حادة — المواسم العربية تستحق احتراماً بصرياً.

---

## G. Campaigns (Paid Ads)

**الشاشة الرئيسية:** Table للحملات مع:
- اسم الحملة، المنصة، الحالة (badge: نشطة/متوقفة/قيد المراجعة)، الميزانية، الإنفاق، ROAS, CTR, CPC
- فلاتر أعلى + search
- زر primary: «+ حملة جديدة»

**Create Campaign Flow (multi-step):**
1. **الهدف:** cards كبيرة (Awareness, Traffic, Conversions, Engagement)
2. **المحتوى الإعلاني:** اختيار منشور موجود أو توليد جديد
3. **الجمهور:**
   - الدول (multi-select chips خليجية)
   - العمر (range slider)
   - الجنس (toggles)
   - الاهتمامات (tags input)
   - Saved audiences
4. **الميزانية والمدة:** يومي/إجمالي + date range picker
5. **المراجعة:** summary card + زر «إرسال للمراجعة»

**Campaign Detail:** charts (Line: spend over time, Bar: daily results), KPIs مفصلة، قرارات (Pause/Edit/Duplicate)

---

## H. Analytics

**Tabs:** نظرة عامة / حسب المنصة / حسب المنشور / الجمهور / التقارير

**Main Dashboard:**
- Date range picker (آخر ٧ أيام، ٣٠ يوم، مخصص)
- KPI grid (6 cards): Reach, Impressions, Engagement Rate, Click-throughs, Followers Growth, ROAS
- Charts:
  - Line chart: Engagement over time (مع x-axis RTL — التواريخ من اليمين للأحدث)
  - Bar chart: Best performing posts
  - Heatmap: Best times to post (7 days × 24 hours)
- **AI Insights section** (ميزة تفاضلية):
  - 3-4 insight cards بنص عربي طبيعي
  - مثال: «🎯 منشوراتك التي تحتوي سؤالاً مباشراً تحقق تفاعلاً أعلى بـ 62%»
  - «📅 أفضل يوم للنشر لديك: الثلاثاء مساءً»
  - «⚠️ انخفض وصولك في آخر ٣ أيام بنسبة ١٥% — قد يكون بسبب تغيير في algorithm»

**Export Reports:**
- زر «تصدير تقرير PDF» — modal:
  - اختيار الـ Workspace/العميل
  - الفترة الزمنية
  - شعار مخصص (للوكالات)
  - لغة التقرير (عربي/إنجليزي/ثنائي)
  - أقسام المشمولة (checkboxes)
- التقرير بتصميم editorial — ليس dashboard screenshot

---

## I. Billing & Settings

**Tabs:** الملف الشخصي / الـ Workspaces / الفريق / الـ Billing / الإشعارات / التكاملات / API

**Billing screen:**
- Current balance card كبير: «١,٢٤٠ / ٢,٠٠٠ توكن متبقٍ»
- زر «شحن» primary
- Usage chart (آخر 30 يوم)
- Payment methods (Moyasar/Tap-branded cards)
- Invoices table مع PDF download
- VAT info (للسعوديين/الإماراتيين)

**Moyasar Checkout Modal:**
- ابتعد عن الـ checkout الإنجليزي الافتراضي — custom UI عربي
- خيارات دفع: Mada, Visa, Apple Pay, STC Pay
- نموذج عربي كامل (حتى حقول البطاقة بـ labels عربية)

---

# §4 — Mobile Design Notes

- **Bottom Nav:** 5 tabs — الرئيسية، توليد، التقويم (center — أكبر، primary color)، الحملات، حسابي
- **Sidebar يصبح drawer** ينزلق من اليمين
- **Calendar:** افتراضي = Week view على موبايل
- **Create flows:** bottom sheets بدل modals ممتدة للشاشة

---

# §5 — Accessibility & i18n

- WCAG AA minimum
- Contrast ratios: كل النصوص ≥ 4.5:1
- Focus states واضحة (ring بلون --sada-500 بسماكة 2px)
- Keyboard navigation كاملة (Tab, Enter, Escape)
- Screen reader labels عربية (aria-label)
- RTL مختبر حقيقياً — scroll direction، focus order، animation direction

---

# §6 — Do's & Don'ts

## ✅ افعل:
- استخدم imagery خليجي حقيقي (ليس stock photos لأجانب)
- أنماط هندسية إسلامية حديثة كـ accents (ليس كليشيه قديم)
- لوحة ألوان متماسكة — --sada-500 كـ signature
- خطوط عربية حقيقية ومشغولة جيداً
- Micro-copy عربي طبيعي («جاهز!» بدل «تم بنجاح»، «ولّد» بدل «إنتاج»)
- احترم الذاكرة البصرية الخليجية — نخيل، بحر، ذهب رملي

## ❌ تجنب:
- gradients أرجواني/أزرق SaaS كليشيه
- إيموجيات عشوائية في الـ UI الوظيفي (استخدمها فقط في المحتوى المولّد)
- dark mode بأسود خام (#000) — استخدم --bg-dark
- ترجمة حرفية لمصطلحات إنجليزية («لوحة القيادة» بدل «الرئيسية»)
- Illustrations عامة من undraw.co
- Toast notifications تطير من اليسار (في RTL تطير من اليمين)
- Breadcrumbs بـ / — استخدم ← أو •

---

# §7 — المخرجات المتوقعة من أداة AI

عند تغذية هذا البرومت لأداة تصميم:
1. **v0.dev / Lovable / Claude Artifacts:** كود HTML/React جاهز قابل للتشغيل
2. **Figma Make / Galileo AI:** Figma file مع components
3. **Midjourney/Ideogram (للـ hero visuals فقط):** اطلب mockups screens بنمط editorial خليجي

**برومت بصري للـ Midjourney لو احتجت imagery:**
```
Editorial product mockup of a modern Arabic SaaS dashboard,
RTL Arabic typography, warm olive green and sand gold palette,
subtle Islamic geometric accents, Gulf aesthetic, minimalist,
Linear-inspired hierarchy, 4K, --ar 16:9 --style raw
```
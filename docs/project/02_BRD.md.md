---
title: BRD

---

# Business Requirements Document (BRD)
## منصة SaaS لإنشاء وإدارة المحتوى التسويقي بالذكاء الاصطناعي

**Code Name:** TBD (مقترحات في §14)
**الإصدار:** 1.0 — Draft
**التاريخ:** أبريل 2026
**المؤلف:** Ahmed Qaddoura — CTO, ArabTalents / BrightGaza
**السوق المستهدف:** دول الخليج العربي (السعودية، الإمارات، الكويت، قطر، البحرين، عمان)
**اللغة الأساسية:** العربية (RTL-first)

---

## 1. الملخص التنفيذي (Executive Summary)

منصة SaaS عربية RTL-first لتوليد المحتوى التسويقي بالذكاء الاصطناعي، مع إدارة كاملة لحسابات السوشيال ميديا (نشر عضوي، إعلانات مدفوعة، جدولة، تحليلات). موجهة للسوق الخليجي بمعرفة عميقة بالمواسم العربية، اللهجات المحلية، وبوابات دفع إقليمية.

**القيمة الفريدة (USP):**
1. **عربي أصيل، ليس ترجمة** — توليد محتوى بلهجات خليجية مخصصة (سعودي، إماراتي، كويتي، ...) وليس محتوى فصحى جامد مترجم.
2. **مواسم عربية مدمجة** — مكتبة جاهزة + AI يولد حملات كاملة تلقائياً لرمضان، العيد، اليوم الوطني السعودي، التأسيس، الاتحاد الإماراتي، إلخ.
3. **دورة تسويق مغلقة** — توليد + جدولة + نشر عضوي + إعلانات مدفوعة + تحليلات، كلها من لوحة واحدة بدل 4-5 أدوات منفصلة.
4. **دفع خليجي محلي** — Moyasar/Tap بدل Stripe الذي يعاني من احتكاك في الخليج.

**النموذج الاقتصادي:** Pay-as-you-go حسب استهلاك التوكنز، المنصة توفر مفاتيح الـ AI APIs ضمن السعر.

---

## 2. أهداف العمل (Business Objectives)

| # | الهدف | KPI | الإطار الزمني |
|---|-------|-----|----------------|
| 1 | إطلاق MVP قابل للاستخدام في السوق الخليجي | Meta App Review مقبول + 50 مستخدم تجريبي | 8 أسابيع من Kickoff |
| 2 | إثبات Product-Market Fit | 100 حساب نشط مدفوع خلال أول 6 أشهر | Month 6 |
| 3 | Revenue milestone | $10K MRR | Month 9 |
| 4 | تغطية كاملة لتكاملات المنصات | Meta ✓ + TikTok + Snapchat | Month 12 |

---

## 3. الجمهور المستهدف (Target Personas)

### Persona 1: صاحب متجر إلكتروني (الأولوية الأعلى)
- منصات البيع: Salla, Zid, Shopify
- الحجم: متجر صغير/متوسط، فريق تسويق 0-2 أشخاص
- الألم: الوقت، الإبداع، معرفة المواسم، إدارة الإعلانات الممولة
- الاستعداد للدفع: عالي (قيمة واضحة = مبيعات)

### Persona 2: وكالة تسويق رقمي
- تدير 5-30 حساب عميل
- تحتاج: Workspaces متعددة، تقارير PDF للعملاء، سير عمل منظم
- الألم: أدوات موحدة غالية (Hootsuite, Later)، لا تفهم العربية، لا توفر إعلانات مدفوعة

### Persona 3: Content Creator / مؤثر
- حساب واحد كبير، يحتاج جودة محتوى + جدولة
- حساس للسعر، يفضل Pay-as-you-go
- الألم: أفكار متجددة، كابشنز جذابة

### Persona 4: Enterprise (Phase 2+)
- خارج نطاق MVP — يحتاج SSO، SLAs، دعم مخصص، فواتير تقليدية

---

## 4. نطاق المنتج (Product Scope)

### 4.1 داخل نطاق MVP (Phase 0 — 8 أسابيع)

**Module A — توليد المحتوى النصي**
- منشورات (Instagram, Facebook)
- كابشنز
- نصوص إعلانات مدفوعة (ad copy)
- Hashtags مقترحة (ذكية حسب المحتوى والسوق)
- اختيار نوع المحتوى من dropdown: Post / Reel Script / Story / Ad
- اختيار اللهجة من select: فصحى / سعودي / إماراتي / كويتي / قطري / بحريني / عماني
- حفظ ناتج كـ Draft + تعديل + موافقة للنشر

**Module B — هوية العلامة (Brand Identity)**
- لكل Workspace إعدادات Brand Identity ثابتة:
  - اسم العلامة، وصف، صوت/نبرة، كلمات محظورة، أمثلة منشورات سابقة
- خيار تطبيق/تعطيل هوية البراند عند كل توليد (toggle).

**Module C — المواسم العربية (Seasonal Engine)** ⭐ ميزة تفاضلية
- مكتبة داخلية لـ 15+ مناسبة عربية/إسلامية/خليجية (رمضان، العيدين، اليوم الوطني السعودي، التأسيس، الاتحاد الإماراتي، ...)
- قوالب جاهزة لكل مناسبة (منشورات، إعلانات، خلفيات)
- إشعارات تلقائية قبل المناسبة بأسبوعين
- **AI Campaign Generator:** زر واحد يولد حملة كاملة (7-14 منشور مجدول) لمناسبة محددة
- Content Calendar مولد تلقائياً لشهر/ربع بناءً على صناعة المستخدم

**Module D — جدولة ونشر (Scheduling & Publishing)**
- نشر فوري (post now)
- جدولة بتاريخ/وقت محدد
- نشر متكرر (recurring — كل اثنين 7م مثلاً)
- AI Best-Time Suggestion حسب تفاعل الجمهور التاريخي
- Calendar View تفاعلي (شهري/أسبوعي) — drag & drop للجدولة
- دعم **Meta فقط في MVP** (Instagram + Facebook)

**Module E — الإعلانات المدفوعة (Paid Ads)**
- إنشاء حملة إعلانية من داخل المنصة
- اختيار: الهدف، الميزانية، الجمهور، الجنس، العمر، المناطق الخليجية
- نشر الإعلان مباشرة إلى Meta Ads Manager عبر Marketing API
- **تتطلب Meta App Review مع permission `ads_management`** — مخاطرة موثقة في §11

**Module F — التحليلات (Analytics)**
- Dashboard داخلي: reach, engagement, impressions, clicks, ROAS
- تصدير PDF/Excel تقارير دورية للعملاء (مع لوجو الوكالة)
- AI Insights: قراءة الأنماط وتقديم توصيات نصية
  - مثال: «منشوراتك المسائية (8-10م) تحقق engagement أعلى 45% من الصباحية»

**Module G — Workspaces متعددة**
- User واحد ← عدة Workspaces (كل Workspace = عميل/علامة)
- كل Workspace له Brand Identity، حسابات سوشيال مربوطة، ومقاييس مستقلة
- Billing مركزي على مستوى الـ User (كل التوكنز المستهلكة في الـ Workspaces)

**Module H — الحساب والدفع**
- Auth (email + OAuth Google)
- Wallet / Token Balance (شحن مسبق أو post-paid)
- Moyasar/Tap للشحن والاشتراكات
- Invoicing + ضريبة القيمة المضافة (VAT) للسوق السعودي/الإماراتي

### 4.2 خارج نطاق MVP — مؤجل لمراحل لاحقة

| الميزة | المرحلة |
|--------|---------|
| توليد صور AI (Imagen/DALL-E) | Phase 1 (Month 3-4) |
| توليد فيديوهات AI (Veo/Runway) | Phase 2 (Month 6+) |
| Voiceover عربي | Phase 2 |
| TikTok integration | Phase 1 (Month 4) |
| Snapchat integration | Phase 1 (Month 5) |
| X (Twitter) / LinkedIn | Phase 2 |
| Team Members + RBAC (admin/editor/viewer) | Phase 1 (Month 4) |
| Inbox موحد للرسائل والتعليقات | Phase 2 |
| BYOK (Bring Your Own Key) للـ AI APIs | Phase 2 |

---

## 5. سير العمل الرئيسي (Core User Flows)

### Flow 1: توليد ونشر منشور (Happy Path)
1. User يدخل Workspace → Generate Content
2. يختار: نوع المحتوى (Post/Reel/Ad) + المنصة (IG/FB) + اللهجة + toggle Brand Identity
3. يكتب prompt قصير (وصف الفكرة/المنتج)
4. AI يولد 3 خيارات — يختار/يعدل
5. يختار: نشر فوري / جدولة / حفظ كـ Draft
6. إذا جدولة: تاريخ/وقت → تأكيد → Queue
7. في الوقت المحدد: Worker ينشر عبر Meta Graph API → يسجل الحالة + يجلب metrics

### Flow 2: حملة موسمية بضغطة زر
1. User يدخل Seasonal Campaigns → يختار «اليوم الوطني السعودي»
2. AI يسأل: كم منشور؟ (7/10/14)، الصناعة، النبرة، الأهداف
3. يولد خطة كاملة: content calendar + منشورات مكتوبة + hashtags + أوقات نشر مقترحة
4. User يراجع/يعدل → Approve All → كل شيء يدخل Queue

### Flow 3: إطلاق إعلان ممول
1. User → Create Ad → يختار منشور موجود أو يولد جديد
2. يحدد: الميزانية اليومية، المدة، الهدف (awareness/conversions/traffic)، الجمهور (دولة، عمر، جنس، اهتمامات)
3. Preview داخل المنصة
4. Submit → يُرسل عبر Meta Marketing API → Ad Status: Pending Review
5. متابعة الأداء من Dashboard (ROAS, CPC, CTR)

---

## 6. المتطلبات الوظيفية (Functional Requirements)

| ID | المتطلب | الأولوية |
|----|---------|----------|
| FR-01 | توليد نصوص عربية بلهجات متعددة عبر LLM | Must |
| FR-02 | Brand Identity لكل Workspace مع toggle للتطبيق | Must |
| FR-03 | نشر فوري ومجدول ومتكرر على Instagram + Facebook | Must |
| FR-04 | Calendar View تفاعلي (drag & drop) | Must |
| FR-05 | AI Best-Time posting suggestion | Must |
| FR-06 | مكتبة مواسم عربية + AI campaign generator | Must |
| FR-07 | إنشاء وإطلاق حملات Meta Ads من داخل المنصة | Must |
| FR-08 | Analytics Dashboard + PDF export + AI insights | Must |
| FR-09 | Multi-Workspace per user | Must |
| FR-10 | Token-based billing عبر Moyasar/Tap | Must |
| FR-11 | RTL UI كاملة (لا mirror لـ UI إنجليزية) | Must |
| FR-12 | Webhooks من Meta لتحديث الحالات والتحليلات | Must |
| FR-13 | تصدير تقارير PDF بلوجو العميل | Should |
| FR-14 | إشعارات مواسم قبل بأسبوعين | Should |

---

## 7. المتطلبات غير الوظيفية (Non-Functional)

- **اللغة:** RTL أصيل (Cairo أو IBM Plex Sans Arabic للـ body، Tajawal للـ display)
- **Performance:** P95 لتوليد منشور < 8 ثوانٍ؛ Dashboard load < 2 ثانية
- **Uptime SLA:** 99.5% للـ MVP
- **Scalability:** 1,000 concurrent users + 10,000 scheduled posts/day
- **Security:**
  - تشفير tokens المنصات الاجتماعية at rest (AES-256)
  - TLS 1.3 في transit
  - Row-level security على مستوى الـ Workspace
- **Compliance:** جاهزية لمتطلبات حماية البيانات السعودية (PDPL) والإماراتية
- **Localization:** العربية الأساسية، الإنجليزية ثانوية (للوكالات متعددة الجنسيات)

---

## 8. المعمارية التقنية (Technical Architecture)

### Stack
- **Frontend:** Vue 3 + Inertia.js (SSR عبر Laravel)
- **Backend:** Laravel 11 (PHP 8.3)
- **Database:** PostgreSQL (multi-tenancy عبر `workspace_id` column)
- **Cache / Queue:** Redis + Laravel Horizon
- **Jobs / Workers:** Laravel Queue workers للـ scheduled posts + webhook processing
- **Storage:** DigitalOcean Spaces أو AWS S3 (media, exports)
- **AI Providers (multi-provider):**
  - **OpenAI GPT-4o** (primary للتوليد العام)
  - **Anthropic Claude Sonnet** (جودة عالية للمحتوى العربي الطويل)
  - **Google Gemini** (fallback + cost optimization)
  - Router ذكي يختار الـ provider حسب نوع المهمة والتكلفة
- **Payments:** Moyasar (السعودية) + Tap (الخليج الواسع) — webhooks للتجديد والإشعارات
- **Infra:** DigitalOcean Droplets + Managed Postgres + Nginx + PM2 لا، **Laravel Octane + Supervisor**
- **Monitoring:** Sentry + Laravel Telescope (dev) + Grafana/Prometheus

### Integrations
- **Meta Graph API v19+** — posts, stories, reels
- **Meta Marketing API** — ad campaigns, ad sets, creatives, insights
- **Meta Webhooks** — real-time updates (messages, comments, insights)

### Multi-Tenancy
- Single DB, shared schema، كل row له `workspace_id`
- Middleware يضمن عزل البيانات
- Quotas على مستوى الـ user (ليس workspace) — التوكنز مشتركة

### Architecture Diagram (High-Level)

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Vue + Inertia  │────▶│  Laravel (Octane)│────▶│   PostgreSQL    │
│   (RTL UI)      │     │    API + Web     │     │  (multi-tenant) │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                               │    │
                ┌──────────────┘    └──────────────┐
                ▼                                   ▼
        ┌──────────────┐                   ┌──────────────────┐
        │Redis + Horizon│                   │ AI Router        │
        │(Queues, Cache)│                   │ (GPT/Claude/Gemini)│
        └──────────────┘                   └──────────────────┘
                │
       ┌────────┴────────┐
       ▼                 ▼
┌─────────────┐    ┌─────────────┐
│Meta Graph API│    │ Moyasar/Tap │
│Marketing API │    │   Webhooks  │
└─────────────┘    └─────────────┘
```

---

## 9. نموذج التسعير (Pricing Model)

**Pay-as-you-go — توكنز مشحونة مسبقاً**

- كل توليد محتوى = X توكنز (حسب النوع والـ AI provider)
- كل منشور مجدول/منشور = Y توكنز (لتغطية تكلفة البنية التحتية)
- كل إعلان مُطلق = Z توكنز + رسوم Meta (User يدفع ميزانية الإعلان مباشرة لـ Meta)

### Token Pricing (Draft)

| الباقة | السعر | التوكنز | الاستخدام التقريبي |
|--------|-------|---------|---------------------|
| Starter | $15 | 500 | ~50 منشور نصي |
| Growth | $49 | 2,000 | ~200 منشور + 20 حملة إعلانية |
| Agency | $149 | 7,000 | multi-workspace كثيف |
| Custom | Contact | — | Enterprise |

**Margin Strategy:**
- تكلفة الـ AI: 20-25% من سعر التوكن
- Gross margin target: 70%+
- Markup clear على الـ BYOK لاحقاً (خصم 30% إذا جلب مفاتيحه)

---

## 10. خارطة الطريق (Roadmap)

### Phase 0 — MVP (الأسابيع 1-8)
**الأسبوع 1-2:** Setup infra + Auth + Workspaces + Brand Identity + DB schema
**الأسبوع 2-3:** AI Router + توليد النصوص + اللهجات + Brand toggle
**الأسبوع 3-4:** Scheduling engine + Calendar UI + Meta OAuth
**الأسبوع 4-5:** Meta Graph integration (نشر عضوي) + Webhooks
**الأسبوع 5-6:** Seasonal Engine + AI Campaign Generator
**الأسبوع 6-7:** Meta Marketing API (ads) + Analytics Dashboard
**الأسبوع 7-8:** Moyasar/Tap integration + Billing + PDF export + QA + Bug bash

**بالتوازي من اليوم 1:** تقديم طلب Meta App Review للـ `ads_management` permission

### Phase 1 — Growth Features (Month 3-4)
- TikTok integration
- توليد الصور AI
- Team Members + RBAC
- Inbox موحد (مرحلة أولى)

### Phase 2 — Scale & Monetize (Month 5-8)
- Snapchat integration
- توليد الفيديو
- BYOK option
- API publique للـ Enterprise
- Mobile app (iOS/Android)

---

## 11. المخاطر والافتراضات (Risks & Assumptions)

### 🔴 مخاطر حرجة

| الخطر | الأثر | الاحتمالية | التخفيف |
|-------|-------|------------|----------|
| **Meta App Review يرفض/يتأخر** | تعطّل ميزة الإعلانات الأساسية | عالية | تقديم طلب يوم 1 + توثيق كامل + استخدام نسخة Developer Mode مع 25 حساب اختبار مبدئياً |
| **Moyasar/Tap يتطلب سجل تجاري خليجي** | تعطيل الـ billing بالكامل | مؤكدة | تأسيس كيان قانوني في السعودية/الإمارات قبل الإطلاق أو شراكة مع كيان خليجي |
| **6-8 أسابيع غير كافية للـ scope الكامل** | تأخير الإطلاق أو جودة ضعيفة | عالية جداً | قص الـ MVP كما هو موثق — Phase 0 لا يشمل الصور/الفيديو/TikTok/Snapchat |
| **تكلفة AI tokens تأكل الهامش** | Unit economics سالبة | متوسطة | Caching ذكي للتوليدات المتشابهة + AI Router يختار أرخص provider مناسب + حدود token per user |
| **جودة اللهجات الخليجية في LLMs الحالية** | محتوى غير أصيل | متوسطة | fine-tuning مستقبلي + RAG مع أمثلة محلية مختارة يدوياً |

### ⚠️ مخاطر متوسطة

| الخطر | التخفيف |
|-------|----------|
| Meta API rate limits | تطبيق queue throttling + batch requests |
| Webhook reliability | retry queue + dead-letter + manual sync endpoint |
| المنافسة من أدوات عالمية (Buffer, Later) | تركيز حاد على USP العربي + المواسم + Moyasar |

### الافتراضات (Assumptions)
1. فريق التطوير متوفر full-time بعد kickoff (backend: Ahmed Jaber / Ghassan؛ frontend: Abd)
2. ميزانية العمليات تشمل تكاليف AI APIs للشهر الأول ($500-1500)
3. كيان قانوني خليجي متاح أو شراكة جاهزة لفتح حسابات Moyasar/Tap
4. الـ target persona مستعد يدفع $15-150/شهر

---

## 12. نموذج الشاشات الرئيسية (Key Screens)

1. **Landing Page** — RTL، مواسم، تسعير شفاف، CTA عربي
2. **Onboarding Wizard** — إنشاء Workspace + ربط حسابات Meta + Brand Identity
3. **Dashboard Home** — KPIs، منشورات قادمة، تنبيهات مواسم
4. **Generate Content** — editor عربي RTL + preview للمنصات
5. **Content Calendar** — شهري/أسبوعي، drag & drop، فلاتر
6. **Campaigns (Ads Manager)** — إنشاء، تتبع، تحليلات ROAS
7. **Seasonal Hub** — تقويم هجري/ميلادي، حملات جاهزة، AI generator
8. **Analytics** — charts + AI insights + export PDF
9. **Settings & Billing** — Workspaces، tokens balance، فواتير، VAT

---

## 13. معايير النجاح (Success Criteria)

### معايير إطلاق MVP
- ✅ كل FR (Must) مكتمل ومختبر
- ✅ Meta App Review معتمد أو في مرحلة متقدمة
- ✅ Moyasar integration تمر 10 معاملات ناجحة end-to-end
- ✅ 50 مستخدم beta أنتجوا 500 منشور و20 حملة إعلانية
- ✅ P95 latency ضمن الـ SLA
- ✅ صفر bugs حرجة مفتوحة

### معايير نجاح Month 3
- 100 active paying users
- $3K MRR
- NPS ≥ 40
- Churn شهري ≤ 8%

---

## 14. مقترحات الاسم التجاري (Branding Proposals)

بما أن الاسم لم يحدد بعد، مقترحات عربية/ثنائية مع rationale:

| الاسم | المعنى | الملاحظات |
|--------|---------|------------|
| **نَشْر** (Nashr) | من «نشر المحتوى»، كلمة عربية قصيرة | واضح، مباشر، يحفظ باللغتين |
| **مَدَى** (Mada) | «مدى الوصول» — إشارة للتسويق | شاعري، سهل الكتابة |
| **صدى** (Sada) | «صدى المحتوى» — reach & resonance | قوي ومميز |
| **برّاق** (Barraq) | لامع ومتألق | جريء، حديث |
| **نَبْض** (Nabd) | «نبض التفاعل» | حيوي، يرتبط بالـ engagement |

توصيتي الأولى: **صدى (Sada)** — مختصر، يترجم لـ Sada.io/Sada.sa، وفيه طبقة معنى تسويقية مباشرة (الصدى = reach + impact).

---

## 15. الخطوات التالية (Next Steps)

1. **مراجعة BRD** مع Sharif (business alignment) والفريق التقني
2. **قرار الاسم التجاري** + حجز الدومين + علامة تجارية
3. **تأسيس/تأكيد الكيان القانوني الخليجي** لبوابات الدفع
4. **Meta Developer Account + طلب App Review الأولي** (يوم 1)
5. **Kickoff تقني** — تقسيم الـ backlog، تعيين المهام، setup repos
6. **Design sprint أسبوع** قبل الكود — wireframes + design system عربي RTL
7. **تأكيد ميزانية AI ops** والمفاتيح الأولية

---

**سؤال واحد للمراجعة قبل اعتماد الـ BRD:** هل **الاسم التجاري والكيان القانوني الخليجي** جاهزان، أم نحتاج نضيف خطوة «Discovery Phase» لمدة أسبوعين قبل بدء الأسابيع الثمانية — بحيث يكون الـ 8 أسابيع فعلياً للكود وليس للتأسيس؟
---
title: Implementation Plan — Sada Platform

---

# Implementation Plan — Sada Platform
## Milestones, Sprints & Task Breakdown

**Document ID:** SADA-IMP-001
**Version:** 1.0
**Date:** April 2026
**Owner:** Ahmed Jaber
**Duration:** 8 weeks (MVP Phase 0) + Discovery Week
**Team:** 4 members

---

## Team Composition & Ownership

| Member | Role | Primary Scope |
|--------|------|---------------|
| **Ahmed Jaber** | CTO / Tech Lead | Architecture, AI integration, code review, DevOps, Meta integration |
| **Ahmed Jaber** | Senior Backend | Laravel core, API, billing, webhooks, authentication |
| **Ahmed Jaber** |  Backend | Scheduling engine, queue jobs, analytics, social account mgmt |
| **Abd Jaber** | Frontend | Vue + Inertia, RTL UI, design system, component library |

---

## Overall Timeline

```
Week 0  │ Discovery & Setup
────────┼──────────────────────────────────────────────
Week 1-2│ M1: Foundation (Auth + Workspaces + Infra)
Week 3-4│ M2: Content Generation + AI SDK
Week 5-6│ M3: Meta Integration + Scheduling
Week 7  │ M4: Seasonal Engine + Campaigns + Analytics
Week 8  │ M5: Billing + Polish + QA + Launch Prep
```

---

# Week 0 — Discovery & Setup (Blocking items before kickoff)

**Goal:** All blockers resolved before Sprint 1 begins.

### Tasks

| # | Task | Owner | Priority |
|---|------|-------|----------|
| 0.1 | Confirm legal entity for Moyasar/Tap (Gulf commercial registration) | Sharif | 🔴 Blocker |
| 0.2 | Register domain (sada.sa or alternative) + trademark search | Sharif | 🔴 Blocker |
| 0.3 | Create Meta Business Manager + verify | Ahmed Q | 🔴 Blocker |
| 0.4 | Submit Meta App Review for `ads_management`, `instagram_content_publish` | Ahmed Q | 🔴 Blocker |
| 0.5 | Provision DigitalOcean infra: 2 droplets (staging + prod), managed Postgres, Spaces | Ahmed Q | 🔴 |
| 0.6 | Setup GitHub organization + repository + branch protection rules | Ahmed Q | 🔴 |
| 0.7 | Setup CI/CD: GitHub Actions (lint, test, deploy to staging on merge) | Ahmed Q | 🟡 |
| 0.8 | Provision AI API keys: Anthropic, OpenAI, Gemini with billing limits | Ahmed Q | 🔴 |
| 0.9 | Setup Sentry project + Laravel Telescope on staging | Ahmed Q | 🟡 |
| 0.10 | Design system kickoff: Figma file with tokens from UI/UX spec | Abd | 🔴 |
| 0.11 | Architecture Decision Records (ADRs) — decisions log in `/docs/adrs` | Ahmed Q | 🟡 |
| 0.12 | Team onboarding: access, credentials, Slack channels, standup schedule | Ahmed Q | 🔴 |

**Definition of Done (Week 0):**
- ✅ All team members have access to all required tools
- ✅ Meta App Review submitted (approval tracking begins)
- ✅ Payment gateway legal process initiated
- ✅ Staging environment reachable via HTTPS
- ✅ First commit to `main` branch successful

---

# Milestone 1: Foundation (Weeks 1-2)

**Goal:** User can register, login, create workspaces, and setup brand identity. No AI yet.

**Sprint 1 (Week 1) — Project Skeleton**
**Sprint 2 (Week 2) — Workspaces & Brand Identity**

---

## Sprint 1 (Week 1) — Project Skeleton

### Backend Tasks (Jaber + Ghassan)

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 1.1.1 | Initialize Laravel 12 project with Inertia + Vue starter kit | Jaber | 2h |
| 1.1.2 | Configure PostgreSQL connection, run first migration | Jaber | 1h |
| 1.1.3 | Setup Redis connection + Laravel Horizon | Ghassan | 2h |
| 1.1.4 | Configure Laravel Octane with Swoole | Ahmed Q | 3h |
| 1.1.5 | Setup Sanctum for API authentication | Jaber | 2h |
| 1.1.6 | Implement auth: register, login, logout, email verification | Jaber | 8h |
| 1.1.7 | Implement password reset flow | Jaber | 4h |
| 1.1.8 | Google OAuth via Socialite | Jaber | 3h |
| 1.1.9 | Setup Laravel Pint, PHPStan, configure GitHub Actions | Ghassan | 3h |
| 1.1.10 | Create base User model with `token_balance` field | Jaber | 1h |
| 1.1.11 | Rate limiting middleware (login attempts, API) | Ghassan | 2h |

### Frontend Tasks (Abd)

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 1.1.12 | Setup Tailwind CSS with RTL plugin | Abd | 2h |
| 1.1.13 | Configure design tokens (colors, typography from UI spec) | Abd | 3h |
| 1.1.14 | Build `BaseLayout.vue` with RTL direction, IBM Plex Sans Arabic | Abd | 3h |
| 1.1.15 | Build auth pages: Login, Register, Forgot Password, Reset | Abd | 8h |
| 1.1.16 | Build reusable components: Button, Input, Alert, Card | Abd | 6h |
| 1.1.17 | Toast notification system (RTL-aware — slides from right) | Abd | 2h |
| 1.1.18 | Setup Pinia store structure | Abd | 2h |

### Deliverables
- Fully functional auth flows (register, login, reset password, Google OAuth)
- RTL layout working with proper typography
- Deployable to staging via CI/CD

---

## Sprint 2 (Week 2) — Workspaces & Brand Identity

### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 1.2.1 | Create `workspaces` table migration | Jaber | 1h |
| 1.2.2 | Create `brand_identities` table migration | Jaber | 1h |
| 1.2.3 | Build `Workspace` Eloquent model + policies | Jaber | 3h |
| 1.2.4 | Build `BrandIdentity` Eloquent model | Jaber | 2h |
| 1.2.5 | Implement multi-tenancy: `BelongsToWorkspace` trait + global scope | Ahmed Q | 4h |
| 1.2.6 | Middleware: `SetCurrentWorkspace` (from session or header) | Ghassan | 3h |
| 1.2.7 | CRUD API for workspaces | Jaber | 6h |
| 1.2.8 | CRUD API for brand identity | Jaber | 4h |
| 1.2.9 | Logo upload to DigitalOcean Spaces (with validation, resize) | Ghassan | 4h |
| 1.2.10 | Feature tests for workspace + brand flows | Ghassan | 4h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 1.2.11 | Onboarding wizard: 4 steps (workspace → brand → placeholder for Meta → done) | Abd | 8h |
| 1.2.12 | Workspace switcher dropdown in top nav | Abd | 4h |
| 1.2.13 | Settings page: Workspace tab (edit workspace) | Abd | 4h |
| 1.2.14 | Settings page: Brand Identity tab (logo upload, tone sliders, banned words input, example posts) | Abd | 8h |
| 1.2.15 | Sidebar navigation component | Abd | 4h |
| 1.2.16 | Dashboard home placeholder with welcome banner | Abd | 3h |

### Deliverables
- User can complete onboarding → land on dashboard
- User can create/edit multiple workspaces
- User can configure brand identity with all fields
- Multi-tenancy tested: data isolated per workspace

**M1 Definition of Done:**
- ✅ All AUTH and WS and BI requirements from SRS implemented
- ✅ ≥70% test coverage on auth and workspace code
- ✅ Deployed to staging, accessible via HTTPS
- ✅ PR reviews by Ahmed Q merged to `main`

---

# Milestone 2: Content Generation (Weeks 3-4)

**Goal:** Core value proposition working end-to-end. User can generate Arabic content with dialect selection and brand identity.

**Sprint 3 (Week 3) — AI SDK Integration**
**Sprint 4 (Week 4) — Streaming + Content UI + Hashtags**

---

## Sprint 3 (Week 3) — AI SDK Integration

### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 2.1.1 | Install `laravel/ai` package + publish config | Ahmed Q | 1h |
| 2.1.2 | Configure AI providers (Anthropic primary, OpenAI + Gemini fallback) | Ahmed Q | 2h |
| 2.1.3 | Build `ContentGeneratorAgent` class with full system prompt | Ahmed Q | 6h |
| 2.1.4 | Build dialect guide system (7 dialects with style rules) | Ahmed Q | 4h |
| 2.1.5 | Build `GetBrandIdentity` tool for agent | Ahmed Q | 2h |
| 2.1.6 | Build `GeneratedPostSchema` structured output | Ahmed Q | 2h |
| 2.1.7 | Create `ai_generations` table + model | Jaber | 2h |
| 2.1.8 | Create `token_transactions` table + model | Jaber | 2h |
| 2.1.9 | Build `TrackTokenUsage` middleware for AI SDK | Ahmed Q | 4h |
| 2.1.10 | Build `EnforceWorkspaceQuota` middleware | Jaber | 3h |
| 2.1.11 | `GenerateContentAction` — orchestrates agent call + tracking | Ahmed Q | 4h |
| 2.1.12 | `ContentController@generate` with validation | Jaber | 3h |
| 2.1.13 | Unit tests for ContentGeneratorAgent (using Ai::fake) | Ahmed Q | 4h |
| 2.1.14 | Manual QA: test 10 real Arabic prompts across dialects | Ahmed Q + team | 3h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 2.1.15 | Generate Content page layout (split: input left, preview right in RTL) | Abd | 6h |
| 2.1.16 | Segmented control: content type (Post/Reel/Story/Ad) | Abd | 3h |
| 2.1.17 | Platform toggle: Instagram / Facebook icons | Abd | 2h |
| 2.1.18 | Dialect select with country flags | Abd | 3h |
| 2.1.19 | Brand Identity toggle switch | Abd | 1h |
| 2.1.20 | Prompt textarea with character counter (RTL) | Abd | 2h |

### Deliverables
- User can generate content via API
- Token tracking working (database records per generation)
- Basic Generate Content UI renders (without streaming yet)

---

## Sprint 4 (Week 4) — Streaming + Content UI + Hashtags

### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 2.2.1 | Implement streaming endpoint via SSE | Ahmed Q | 6h |
| 2.2.2 | Build `HashtagAgent` — uses Gemini Flash (cheaper) | Ahmed Q | 4h |
| 2.2.3 | Caching layer: hash(inputs) → cached result, 24h TTL | Ghassan | 3h |
| 2.2.4 | Post CRUD: save drafts | Jaber | 4h |
| 2.2.5 | Banned words post-filter on AI output | Jaber | 3h |
| 2.2.6 | Regenerate endpoint (fresh generation, bypasses cache) | Jaber | 2h |
| 2.2.7 | Integration tests: full generate → save draft flow | Ghassan | 4h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 2.2.8 | SSE consumer in Vue (fetch API with ReadableStream) | Abd | 6h |
| 2.2.9 | Variation cards with expand/collapse | Abd | 5h |
| 2.2.10 | Inline edit in variation cards | Abd | 4h |
| 2.2.11 | Hashtag chips component (editable) | Abd | 3h |
| 2.2.12 | Preview mockup: Instagram post preview (RTL) | Abd | 6h |
| 2.2.13 | Preview mockup: Facebook post preview | Abd | 4h |
| 2.2.14 | Action bar: Save Draft / Schedule / Publish Now (disabled for now) | Abd | 3h |
| 2.2.15 | Token balance indicator in top nav + "Buy more" link | Abd | 3h |
| 2.2.16 | Loading states: skeleton for variations during streaming | Abd | 3h |

### Deliverables
- End-to-end content generation working with streaming
- 3 variations appear in real-time
- User can edit, save as draft
- Preview accurately reflects platform look

**M2 Definition of Done:**
- ✅ CG-01 through CG-10 from SRS implemented
- ✅ AI generation P95 < 8s, first token < 2s verified
- ✅ Cost tracking validated against actual AI provider billing
- ✅ 7 dialects tested manually with native speakers if possible

---

# Milestone 3: Meta Integration & Scheduling (Weeks 5-6)

**Goal:** Connect Instagram/Facebook, schedule and publish posts, calendar view.

**Sprint 5 (Week 5) — Meta OAuth + Publishing**
**Sprint 6 (Week 6) — Calendar + Scheduling Engine**

---

## Sprint 5 (Week 5) — Meta OAuth + Publishing

### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 3.1.1 | Create `social_accounts` table with encrypted token columns | Jaber | 2h |
| 3.1.2 | Build `SocialAccount` model with `encrypted` casts | Jaber | 2h |
| 3.1.3 | Build `MetaGraphClient` wrapper class | Ahmed Q | 6h |
| 3.1.4 | Meta OAuth flow: initiate + callback | Ahmed Q | 6h |
| 3.1.5 | Long-lived token exchange + refresh job (runs daily) | Ghassan | 4h |
| 3.1.6 | Link Instagram Business Account to Facebook Page | Ahmed Q | 4h |
| 3.1.7 | Build `PublishPostAction` — handles IG and FB publishing | Ahmed Q | 6h |
| 3.1.8 | Immediate publish endpoint with error handling | Jaber | 3h |
| 3.1.9 | Webhook receiver: `POST /v1/webhooks/meta` (status updates) | Ghassan | 4h |
| 3.1.10 | Queue: `PublishScheduledPostJob` (retries 3x with backoff) | Ghassan | 4h |
| 3.1.11 | Tests: mock Meta API responses, verify publish flow | Ahmed Q | 4h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 3.1.12 | Onboarding Step 3: Connect Meta accounts UI | Abd | 4h |
| 3.1.13 | Settings: Connected Accounts page | Abd | 4h |
| 3.1.14 | Connect / Disconnect buttons with loading states | Abd | 3h |
| 3.1.15 | Account health badges (healthy/expired/error) | Abd | 3h |
| 3.1.16 | "Publish Now" button integration in Generate Content | Abd | 3h |
| 3.1.17 | Success/Error toast notifications for publish | Abd | 2h |

### Deliverables
- User can connect IG + FB accounts
- User can publish generated content immediately
- Publish failures logged and surfaced to user

---

## Sprint 6 (Week 6) — Calendar + Scheduling Engine

### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 3.2.1 | Add scheduled_for, published_at, status to `posts` table | Jaber | 1h |
| 3.2.2 | Scheduling API: create/update scheduled post | Jaber | 4h |
| 3.2.3 | Recurring posts logic + `recurring_posts` table | Ghassan | 6h |
| 3.2.4 | Cron: `php artisan schedule:run` → dispatch queue jobs for due posts | Ghassan | 3h |
| 3.2.5 | Best-time suggestion algorithm (from historical insights) | Ahmed Q | 6h |
| 3.2.6 | Calendar API: return posts grouped by date | Jaber | 3h |
| 3.2.7 | Reschedule API (drag-drop support on backend) | Jaber | 2h |
| 3.2.8 | Tests: scheduling edge cases (timezone, DST, past dates) | Ghassan | 4h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 3.2.9 | Calendar component: monthly view (7×5 grid, RTL) | Abd | 10h |
| 3.2.10 | Calendar: weekly view | Abd | 6h |
| 3.2.11 | Calendar: list view | Abd | 4h |
| 3.2.12 | Post card on calendar: mini preview, status dot | Abd | 4h |
| 3.2.13 | Drag-and-drop rescheduling (Vue Draggable or similar) | Abd | 6h |
| 3.2.14 | Schedule modal: date/time picker (RTL-friendly), recurring options | Abd | 6h |
| 3.2.15 | Filter sidebar: platform, status, workspace | Abd | 4h |
| 3.2.16 | Best-time suggestion tooltip in schedule modal | Abd | 3h |

### Deliverables
- User sees all scheduled posts in interactive calendar
- User can schedule, reschedule (drag), cancel posts
- Scheduled posts publish automatically at the right time
- Recurring posts working

**M3 Definition of Done:**
- ✅ All SCH and CON requirements from SRS implemented
- ✅ Successfully published 20+ test posts to staging IG/FB accounts
- ✅ Calendar tested on desktop + tablet + mobile

---

# Milestone 4: Seasonal + Campaigns + Analytics (Week 7)

**Goal:** Differentiating features — seasonal campaigns and analytics dashboard.

**Sprint 7 (Week 7) — Seasonal Engine + Meta Ads + Analytics**

---

## Sprint 7 (Week 7)

### Backend Tasks — Seasonal Engine

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.1 | Seed `seasonal_occasions` table with 17 occasions (Hijri + Gregorian) | Ahmed Q | 3h |
| 4.1.2 | Build `SeasonalCampaignAgent` with template-based generation | Ahmed Q | 6h |
| 4.1.3 | `GenerateSeasonalCampaign` queue job | Ghassan | 4h |
| 4.1.4 | API: list upcoming occasions | Jaber | 2h |
| 4.1.5 | API: generate campaign (returns job ID for polling) | Jaber | 2h |
| 4.1.6 | Seasonal reminder email (14 days before) — scheduled job | Ghassan | 3h |
| 4.1.7 | Broadcasting: notify user when campaign generation completes | Ahmed Q | 3h |

### Backend Tasks — Meta Ads

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.8 | Build `MetaAdsClient` wrapper for Marketing API | Ahmed Q | 6h |
| 4.1.9 | Create `campaigns` + `ad_sets` tables | Jaber | 2h |
| 4.1.10 | Build `CreateCampaignAction` | Ahmed Q | 6h |
| 4.1.11 | Ads insights sync job (every 15 min) | Ghassan | 4h |
| 4.1.12 | API: CRUD campaigns, pause/resume | Jaber | 4h |
| 4.1.13 | Tests with Meta Sandbox | Ahmed Q | 3h |

### Backend Tasks — Analytics

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.14 | `analytics_snapshots` table + daily sync job for organic posts | Ghassan | 4h |
| 4.1.15 | Aggregation queries: reach, engagement, CTR over time | Ghassan | 4h |
| 4.1.16 | Build `InsightsAgent` (AI-generated insights) | Ahmed Q | 5h |
| 4.1.17 | PDF export using `barryvdh/laravel-dompdf` with custom template | Jaber | 5h |
| 4.1.18 | Excel export via Maatwebsite | Jaber | 3h |

### Frontend Tasks — Seasonal

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.19 | Seasonal Hub page with occasion cards + countdown | Abd | 6h |
| 4.1.20 | Campaign Generator modal (count slider, tone, goals) | Abd | 4h |
| 4.1.21 | Campaign preview page: list posts, edit/approve all | Abd | 6h |
| 4.1.22 | Dashboard banner: upcoming season notification | Abd | 2h |

### Frontend Tasks — Ads

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.23 | Campaigns list page with table | Abd | 4h |
| 4.1.24 | Create Campaign wizard (5 steps) | Abd | 10h |
| 4.1.25 | Gulf country selector (chips with flags) | Abd | 3h |
| 4.1.26 | Audience targeting UI: age slider, gender, interests | Abd | 5h |
| 4.1.27 | Budget input with currency selector | Abd | 3h |
| 4.1.28 | Campaign detail page with charts | Abd | 5h |

### Frontend Tasks — Analytics

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 4.1.29 | Analytics overview page with KPI cards | Abd | 5h |
| 4.1.30 | Charts using Chart.js or ECharts (RTL axes) | Abd | 6h |
| 4.1.31 | AI Insights cards component | Abd | 3h |
| 4.1.32 | Export modal: PDF/Excel with logo upload for agencies | Abd | 5h |
| 4.1.33 | Date range picker (Arabic-friendly) | Abd | 4h |

**M4 Definition of Done:**
- ✅ Seasonal campaign generates 7-14 posts correctly for Saudi National Day
- ✅ Paid campaign successfully created in Meta Ads Manager (staging account)
- ✅ Analytics dashboard shows real data from published posts
- ✅ PDF export looks professional, bilingual

---

# Milestone 5: Billing + Polish + Launch (Week 8)

**Goal:** Monetization, final polish, QA, and soft launch.

**Sprint 8 (Week 8) — Payments + Bug Bash + Launch Prep**

---

## Sprint 8 (Week 8)

### Backend Tasks — Billing

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 5.1.1 | Integrate Moyasar SDK | Jaber | 4h |
| 5.1.2 | Integrate Tap SDK | Jaber | 4h |
| 5.1.3 | Token packages table + seeds | Jaber | 1h |
| 5.1.4 | Checkout flow: create payment session | Jaber | 4h |
| 5.1.5 | Webhook: payment success → credit tokens | Ghassan | 4h |
| 5.1.6 | VAT calculation for Saudi/UAE users | Ghassan | 3h |
| 5.1.7 | Invoice generation (PDF with ZATCA compliance) | Ghassan | 5h |
| 5.1.8 | Low balance warning notifications | Ghassan | 2h |
| 5.1.9 | End-to-end payment tests | Ahmed Q | 3h |

### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 5.1.10 | Billing page: balance, packages, recent transactions | Abd | 6h |
| 5.1.11 | Checkout modal (custom Arabic UI, not default Moyasar UI) | Abd | 6h |
| 5.1.12 | Invoices list + download | Abd | 3h |
| 5.1.13 | Low balance banner + modal | Abd | 2h |

### Cross-Functional — Launch Prep

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 5.1.14 | Landing page: hero, features, pricing, FAQ | Abd | 10h |
| 5.1.15 | Terms of Service + Privacy Policy (Arabic + English) | Sharif | External |
| 5.1.16 | SEO: sitemap, meta tags, Arabic meta descriptions | Ahmed Q | 2h |
| 5.1.17 | Production deployment checklist | Ahmed Q | 2h |
| 5.1.18 | Bug bash day: full team tests every flow | All | 8h |
| 5.1.19 | Security audit checklist | Ahmed Q | 4h |
| 5.1.20 | Performance testing: 100 concurrent users simulation | Ahmed Q | 3h |
| 5.1.21 | Recruit 50 beta testers via TAQAT / BrightGaza network | Sharif | External |
| 5.1.22 | Documentation: user guide (Arabic) | Sharif + Ahmed Q | 6h |
| 5.1.23 | Setup Intercom or similar for user support | Ahmed Q | 3h |
| 5.1.24 | Final production deployment + DNS cutover | Ahmed Q | 2h |

**M5 Definition of Done:**
- ✅ Full payment flow tested with real Moyasar transactions
- ✅ Landing page live on sada.sa
- ✅ 50 beta users onboarded
- ✅ Zero critical bugs open
- ✅ Production monitoring active (Sentry, Horizon, uptime)

---

## Risk Register (Active Monitoring)

### MVP (Phase 0) Risks

| Risk | Mitigation | Owner | Status |
|------|-----------|-------|--------|
| Meta App Review rejected/delayed | Submit Day 1 + use dev mode for testing | Ahmed Q | 🔴 Active |
| Moyasar KYC delays | Start legal process Week 0 | Sharif | 🔴 Active |
| AI cost overrun | Token caps + caching + monitoring | Ahmed Q | 🟡 Active |
| Dialect quality insufficient | Native speaker review in Week 4 (7 dialects including Qatari) | Team | 🟡 Active |
| 8 weeks insufficient | Weekly velocity check; cut Phase 0 scope if behind | Ahmed Q | 🟡 Active |
| Team member unavailable | Cross-training during code review | Ahmed Q | 🟢 Mitigated |

### Phase 0.5 Risks

| Risk | Mitigation | Owner | Status |
|------|-----------|-------|--------|
| TikTok App Review rejected | Submit Week 5 with polished demo video; have backup plan to launch M6 with manual posting fallback | Ahmed Q | 🔴 Active |
| TikTok requires video-only content | AI-generated videos not in MVP — clearly label TikTok as "video upload + AI caption" | Abd + Ahmed Q | 🟡 Active |
| Snap API lacks organic posting | Clearly communicate Snap = Ads only in UX + marketing | Abd | 🟢 Documented |
| X API $200/mo recurring cost | Pass to Agency-tier pricing; evaluate free tier sufficiency for Starter users | Sharif | 🟡 Active |
| X Ads API access denied | Launch X support without ads initially; pure tweet publishing has value | Ahmed Q | 🟡 Active |
| Cross-platform content adaptation complexity | Keep MVP adaptation simple (character limits + hashtag counts); ML-based adaptation in Phase 1 | Ahmed Q | 🟡 Active |
| Snap aggressive token expiry | Implement robust refresh + user re-auth flow | Jaber | 🟡 Active |

---

## Ceremonies & Cadence

| Ceremony | Frequency | Duration | Attendees |
|----------|-----------|----------|-----------|
| Daily Standup | Daily 9am Riyadh | 15 min | Full team |
| Sprint Planning | Monday | 1h | Full team |
| Sprint Review | Friday | 1h | Full team + Sharif |
| Sprint Retrospective | Friday | 30 min | Full team |
| Tech Review | Wednesday | 1h | Ahmed Q + devs |
| Weekly Exec Sync | Friday | 30 min | Ahmed Q + Sharif |

---

## Success Metrics per Milestone

| Milestone | Metric | Target |
|-----------|--------|--------|
| M1 | Auth conversion (landing → registered) | 30% |
| M2 | Content generation success rate | 95% |
| M2 | User-reported quality score | 4/5 avg |
| M3 | Publish success rate (Meta) | 97% |
| M3 | Calendar usability score | 4/5 avg |
| M4 | Seasonal campaign adoption (beta users) | 40% try it |
| M4 | PDF export usage (agency beta users) | 60% |
| M5 | Payment completion rate | 85% |
| M5 | Post-launch critical bugs | 0 |

---

## Phase 0.5 — Platform Expansion (Weeks 9-12, post-MVP launch)

**Goal:** Add TikTok, Snapchat, and X to match the full platform promise. Team continues at same velocity with MVP stability hardening in parallel.

**Prerequisite:** App Review approvals submitted during MVP (see Week 0 and Week 5-7 dependencies below).

---

### Milestone 6: TikTok Integration (Weeks 9-10)

**Sprint 9 (Week 9) — TikTok OAuth + Content Posting**

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 6.1.1 | Add `tiktok` to social_accounts provider enum + migration | Jaber | 1h |
| 6.1.2 | Build `TikTokApiClient` wrapper (OAuth, token refresh) | Ahmed Q | 8h |
| 6.1.3 | TikTok OAuth flow: initiate + callback with scope validation | Ahmed Q | 6h |
| 6.1.4 | Video upload + publish endpoint (TikTok Content Posting API) | Ahmed Q | 10h |
| 6.1.5 | Handle TikTok-specific constraints: 6 posts/day limit, video specs | Jaber | 4h |
| 6.1.6 | Extend `PublishPostAction` to route to TikTok | Ahmed Q | 4h |
| 6.1.7 | TikTok connection UI in Settings + Onboarding | Abd | 5h |
| 6.1.8 | Video upload component (drag-drop, preview, validation) | Abd | 8h |
| 6.1.9 | Content type "TikTok Video" in Generate Content page (caption + hashtags) | Abd | 5h |
| 6.1.10 | Test on real TikTok Business account | Ahmed Q | 4h |

**Sprint 10 (Week 10) — TikTok Ads + Analytics Sync**

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 6.2.1 | `TikTokAdsClient` wrapper for Marketing API | Ahmed Q | 8h |
| 6.2.2 | Create TikTok Ads campaign via API | Ahmed Q | 6h |
| 6.2.3 | Unified Campaigns page: filter by platform (Meta/TikTok) | Abd | 4h |
| 6.2.4 | TikTok insights sync job (every 15 min) | Ghassan | 4h |
| 6.2.5 | Add TikTok metrics to analytics dashboard (cross-platform view) | Abd | 6h |
| 6.2.6 | TikTok webhook receiver (if applicable) | Ghassan | 3h |
| 6.2.7 | Integration tests for TikTok publish flow | Ahmed Q | 4h |

**M6 Definition of Done:**
- ✅ User can connect TikTok Business account
- ✅ Successfully published 10 test videos to TikTok
- ✅ Successfully created 3 test ad campaigns
- ✅ Metrics appear in unified analytics dashboard

---

### Milestone 7: Snapchat + X Integration (Weeks 11-12)

**Sprint 11 (Week 11) — Snapchat Ads Integration**

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 7.1.1 | Add `snapchat` to social_accounts provider enum | Jaber | 1h |
| 7.1.2 | Build `SnapchatApiClient` (Snap Marketing API wrapper) | Ahmed Q | 8h |
| 7.1.3 | Snap OAuth flow (authorization code + refresh) | Ahmed Q | 6h |
| 7.1.4 | Create Snap Ad campaign via API (Single Image / Video / Story Ads) | Ahmed Q | 10h |
| 7.1.5 | Ad creative builder (image/video upload, headline, CTA) | Abd | 8h |
| 7.1.6 | Snap ad preview mockup (vertical 9:16) | Abd | 5h |
| 7.1.7 | Snap insights sync job | Ghassan | 4h |
| 7.1.8 | **Critical UX:** communicate Snap = ads only, no organic posting | Abd | 3h |
| 7.1.9 | Snap connection health monitoring (token expiry is aggressive) | Jaber | 3h |
| 7.1.10 | Test with real Snap Ads Manager account | Ahmed Q | 4h |

**Sprint 12 (Week 12) — X Integration + Hardening**

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 7.2.1 | Add `x` to social_accounts provider enum | Jaber | 1h |
| 7.2.2 | Build `XApiClient` with OAuth 2.0 PKCE | Ahmed Q | 8h |
| 7.2.3 | Tweet publishing endpoint (text + media) | Ahmed Q | 5h |
| 7.2.4 | Thread support (chain of tweets up to 25) | Ahmed Q | 5h |
| 7.2.5 | Content type "Tweet" and "Thread" in Generate Content | Abd | 6h |
| 7.2.6 | 280-char live counter with visual warning | Abd | 3h |
| 7.2.7 | X Ads API client (if access granted) | Ahmed Q | 6h |
| 7.2.8 | X insights sync (engagement, impressions) | Ghassan | 4h |
| 7.2.9 | Cross-platform post publishing: one content → adapt → publish to all | Ahmed Q | 8h |
| 7.2.10 | Landing page update: remove "قريباً" labels, add new platform testimonials | Abd | 4h |
| 7.2.11 | Phase 0.5 full regression testing | Team | 8h |
| 7.2.12 | Marketing campaign: "Now supporting all Gulf platforms" | Sharif | External |

**M7 Definition of Done:**
- ✅ User can connect Snapchat Business account and create Snap Ads
- ✅ User can connect X account and publish tweets/threads
- ✅ Cross-posting works: same content adapted for IG, FB, TikTok, X
- ✅ Analytics unified across all 5 platforms
- ✅ All "قريباً" labels removed from UI and marketing

---

## Phase 0.5 Prerequisites — Added to Week 0 Tasks

| # | Task | Owner | When |
|---|------|-------|------|
| 0.13 | Create TikTok Developer account + apply for API access | Ahmed Q | Week 0 |
| 0.14 | Submit TikTok App Review (requires demo video) | Ahmed Q | Week 5 |
| 0.15 | Create Snap Business Manager + apply for Marketing API | Ahmed Q | Week 5 |
| 0.16 | Verify Snap business account | Sharif | Week 5 |
| 0.17 | Subscribe to X API Basic tier ($200/mo) | Ahmed Q | Week 7 |
| 0.18 | Apply for X Ads API access | Ahmed Q | Week 7 |

---

---

# Phase 0.6 — Admin Dashboard (Weeks 13-14, post Phase 0.5)

**Goal:** لوحة تحكم داخلية شاملة لإدارة جميع جوانب المنصة — المستخدمين، الاشتراكات، التوكنات، المحتوى، المنصات، والنظام.

**الوصول:** Super Admin فقط — لا يظهر في الـ UI العام، مسار منفصل `/admin`.

**Prerequisite:** اكتمال Phase 0.5 + وجود مستخدمين حقيقيين بعد الإطلاق.

---

## Milestone 8: Admin Dashboard (Weeks 13-14)

### Sprint 13 (Week 13) — Core Admin Infrastructure + Users & Workspaces

#### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 8.1.1 | `is_admin` flag على `users` table + migration | Ahmed Q | 1h |
| 8.1.2 | `AdminMiddleware` — يحجب أي غير admin | Ahmed Q | 1h |
| 8.1.3 | Route group `/admin` محمي بـ `AdminMiddleware` | Ahmed Q | 1h |
| 8.1.4 | `AdminDashboardController` — overview stats | Ahmed Q | 3h |
| 8.1.5 | `AdminUserController` — CRUD + search + filter + ban/unban | Ahmed Q | 6h |
| 8.1.6 | `AdminWorkspaceController` — list + view + suspend/restore | Ahmed Q | 4h |
| 8.1.7 | `AdminImpersonateAction` — تسجيل دخول كمستخدم لأغراض الدعم | Ahmed Q | 3h |
| 8.1.8 | `AdminTokenController` — منح/سحب توكنات يدوياً | Ahmed Q | 3h |
| 8.1.9 | Activity log: تسجيل كل إجراءات الـ Admin في `admin_logs` table | Ahmed Q | 4h |
| 8.1.10 | API: إحصائيات عامة (مستخدمون، workspaces، إيرادات، توكنات) | Ahmed Q | 3h |

#### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 8.1.11 | Admin layout منفصل (`AdminLayout.vue`) — sidebar مختلف، لون مميز | Abd | 4h |
| 8.1.12 | Dashboard overview: KPI cards (مستخدمون، إيرادات، توكنات مستهلكة، منشورات) | Abd | 5h |
| 8.1.13 | Charts: نمو المستخدمين يومياً + إيرادات شهرية | Abd | 4h |
| 8.1.14 | Users table: search، filter by plan/status، sort، pagination | Abd | 6h |
| 8.1.15 | User detail page: بيانات + workspaces + معاملات التوكن + زر ban/impersonate | Abd | 5h |
| 8.1.16 | Workspaces table: filter by status/owner، suspend/restore | Abd | 4h |

---

### Sprint 14 (Week 14) — Billing, Content, Platform Health & System

#### Backend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 8.2.1 | `AdminBillingController` — كل المعاملات، فلترة، export CSV | Ahmed Q | 4h |
| 8.2.2 | `AdminTokenAuditController` — log كل استهلاك AI مع التكلفة | Ahmed Q | 3h |
| 8.2.3 | `AdminPostController` — عرض كل المنشورات، فلترة بالمنصة/الحالة | Ahmed Q | 3h |
| 8.2.4 | `AdminSocialAccountController` — صحة الحسابات المرتبطة لكل workspace | Ahmed Q | 3h |
| 8.2.5 | `AdminAiCostController` — تقرير تكلفة AI مجمّعة (provider + agent + تاريخ) | Ahmed Q | 4h |
| 8.2.6 | `AdminNotificationController` — إرسال إشعار/بريد لمستخدم أو لكل المستخدمين | Ahmed Q | 4h |
| 8.2.7 | `AdminSystemController` — queue health، Horizon stats، cache clear | Ahmed Q | 3h |
| 8.2.8 | `AdminSettingsController` — token package prices، rate limits، feature flags | Ahmed Q | 4h |
| 8.2.9 | Export: تقارير PDF/Excel للمعاملات والمستخدمين والإيرادات | Ahmed Q | 4h |
| 8.2.10 | Pest feature tests لكل admin endpoints | Ahmed Q | 4h |

#### Frontend Tasks

| # | Task | Owner | Estimate |
|---|------|-------|----------|
| 8.2.11 | Billing & Revenue page: جدول المعاملات + إجمالي إيرادات + export | Abd | 5h |
| 8.2.12 | Token Audit page: استهلاك AI مفصّل per workspace/user | Abd | 4h |
| 8.2.13 | AI Cost Report: تكلفة كل provider مقارنةً بالإيرادات | Abd | 4h |
| 8.2.14 | Posts moderation page: كل المنشورات مع فلترة + عرض المحتوى | Abd | 4h |
| 8.2.15 | Platform Health page: كل social accounts + حالة التوكن + تنبيهات المنتهية | Abd | 4h |
| 8.2.16 | Broadcast Notification modal: إرسال push/email لشريحة أو لكل المستخدمين | Abd | 4h |
| 8.2.17 | System Health page: Horizon queues، jobs failed، cache stats | Abd | 3h |
| 8.2.18 | Settings page: token packages pricing، rate limits، maintenance mode toggle | Abd | 5h |

---

## Admin Dashboard — Screens Summary

| الشاشة | الوصف |
|--------|-------|
| `/admin` | Overview — KPIs + charts |
| `/admin/users` | قائمة المستخدمين + بحث وفلترة |
| `/admin/users/{id}` | تفاصيل مستخدم + impersonate |
| `/admin/workspaces` | كل الـ workspaces + suspend |
| `/admin/billing` | المعاملات + إيرادات + export |
| `/admin/tokens` | Token audit log per user |
| `/admin/ai-costs` | تكلفة AI per provider |
| `/admin/posts` | كل المنشورات (moderation) |
| `/admin/platforms` | صحة الـ social accounts |
| `/admin/notifications` | إرسال إشعارات broadcast |
| `/admin/system` | Horizon + queues + cache |
| `/admin/settings` | Token prices + feature flags |

---

## Data Model الإضافي

```
admin_logs      id, admin_id, action, target_type, target_id, payload JSON, ip, created_at
feature_flags   id, key, value, description, updated_by, updated_at
```

**M8 Definition of Done:**
- ✅ Super Admin يقدر يدير كل مستخدم وworkspace من لوحة واحدة
- ✅ Impersonation يشتغل بأمان مع تسجيل كامل في admin_logs
- ✅ تقارير الإيرادات والتوكنات قابلة للتصدير
- ✅ إرسال broadcast notification لكل المستخدمين
- ✅ Pest tests تغطي كل admin endpoints
- ✅ لا يمكن الوصول لأي admin route بدون is_admin = true

---

## Post-Phase 0.5 Backlog (Phase 1 — Month 4-5)

- AI image generation (DALL-E 3 / Imagen / Gemini)
- Team members + RBAC (admin/editor/viewer)
- Inbox (unified messages + comments from all 5 platforms)
- AI best-time algorithm v2 (ML-based, per-platform)
- LinkedIn integration (Phase 2)
- Mobile apps iOS/Android (Phase 2)
- Webhooks for external integrations (Zapier-compatible)

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | April 2026 | Ahmed Qaddoura | Initial plan |
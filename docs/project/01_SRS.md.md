---
title: Software Requirements Specification (SRS)

---

# Software Requirements Specification (SRS)
## منصة صدى (Sada) — SaaS للتسويق الرقمي بالذكاء الاصطناعي

**Document ID:** SADA-SRS-001
**Version:** 1.0
**Date:** April 2026
**Author:** Ahmed Jaber
**Status:** Draft for Implementation

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Overview](#2-system-overview)
3. [Functional Requirements](#3-functional-requirements)
4. [Non-Functional Requirements](#4-non-functional-requirements)
5. [System Architecture](#5-system-architecture)
6. [Data Model](#6-data-model)
7. [API Specification](#7-api-specification)
8. [External Integrations](#8-external-integrations)
9. [Security Requirements](#9-security-requirements)
10. [Assumptions & Dependencies](#10-assumptions--dependencies)

---

## 1. Introduction

### 1.1 Purpose

This document defines the software requirements for **Sada** — an Arabic-first SaaS platform that enables businesses, agencies, and creators in the Gulf market to generate marketing content using AI, schedule organic posts, launch paid ad campaigns, and analyze performance — all from a single unified interface.

### 1.2 Scope — Phased Platform Support

Sada supports **5 social platforms** across MVP and Phase 0.5. Due to the complexity of integrating multiple platforms simultaneously (each requires independent OAuth, API client, App Review process, and error handling), platforms are delivered in phases:

#### Phase 0 — MVP (Weeks 1-8) — In Scope
- AI-powered text content generation (posts, captions, ad copy, hashtags) in Arabic with 7 Gulf dialects
- Seasonal campaign engine (Ramadan, Eid, Saudi National Day, UAE Union Day, Qatar National Day, etc.)
- **Meta (Instagram + Facebook)** — full integration: OAuth, organic posts, paid ads, analytics
- Intelligent scheduling (immediate, date/time, recurring, AI-suggested)
- Multi-workspace architecture (1 user → N workspaces)
- Analytics dashboard with AI insights + PDF export
- Pay-as-you-go token-based billing via Moyasar/Tap
- RTL-first Arabic UI (Vue 3 + Inertia + Laravel)

#### Phase 0.5 — Platform Expansion (Weeks 9-12, post-MVP launch)
- **TikTok** — organic posts + TikTok Ads Manager integration
- **Snapchat** — Snap Ads via Snap Marketing API
- **X (Twitter)** — tweet publishing + X Ads (basic tier)

#### Phase 1 — Feature Expansion (Months 3-4)
- AI image generation (DALL-E 3 / Imagen / Gemini)
- Team members & RBAC (admin/editor/viewer)
- Inbox (unified messages + comments from all platforms)

#### Phase 2 — Scale (Months 5-8)
- AI video generation
- BYOK (Bring Your Own Key)
- Mobile native apps (iOS/Android)
- LinkedIn integration
- Advanced ML-based posting time optimization

**Rationale for phasing:** Integrating 5 platforms with full OAuth, publishing, ad management, and analytics in 8 weeks is technically infeasible with a 4-person team. Meta (IG + FB) uses a unified API, so it delivers maximum value for minimum integration effort in MVP. Phase 0.5 expands immediately after launch, so marketing can advertise the full platform list from Day 1 with clear "coming weeks" labels for TikTok/Snap/X.

### 1.3 Definitions & Acronyms

| Term | Definition |
|------|-----------|
| **Workspace** | Isolated tenant container (one brand/client) within a user account |
| **Brand Identity** | Persistent settings (name, tone, banned words, examples) applied to AI generation |
| **Token** | Unit of billing; consumed per AI generation and per publish action |
| **Agent** | Laravel AI SDK abstraction for a specialized AI task (ContentGenerator, Seasonal, etc.) |
| **Meta Graph API** | Facebook's API for reading/writing posts on IG/FB |
| **Meta Marketing API** | Facebook's API for creating and managing paid ad campaigns |
| **TikTok Business API** | TikTok's API for managing posts and ads on Business accounts |
| **TikTok Content Posting API** | TikTok's API for direct content upload and publishing |
| **Snap Marketing API** | Snapchat's API for creating Snap Ads and managing ad accounts |
| **X API v2** | X (formerly Twitter) API for tweets, user context, and basic analytics |
| **X Ads API** | X's API for managing promoted tweets and ad campaigns |
| **Moyasar** | Saudi-based payment gateway (Mada, Apple Pay, STC Pay, Visa) |
| **Tap** | Regional payment gateway covering Kuwait (KNET), Bahrain (Benefit), Qatar, Oman |
| **RTL** | Right-to-Left text direction (Arabic) |
| **PDPL** | Saudi Personal Data Protection Law |

### 1.4 References

- BRD: `BRD_Arabic_Marketing_AI_SaaS.md`
- UI/UX Prompt: `Sada_UI_UX_Design_Prompt.md`
- AI SDK Integration: `Laravel_AI_SDK_Sada_Integration.md`
- Meta Graph API v19.0+ Documentation
- Laravel AI SDK Official Docs (laravel.com/docs/13.x/ai-sdk)

---

## 2. System Overview

### 2.1 System Context Diagram

```
                    ┌─────────────────────┐
                    │   Gulf End Users    │
                    │  (Merchants/Agencies)│
                    └──────────┬──────────┘
                               │
                               ▼
        ┌──────────────────────────────────────┐
        │      Sada Platform (Web App)         │
        │  ┌────────────────────────────────┐  │
        │  │  Vue 3 + Inertia (RTL UI)      │  │
        │  └────────────────────────────────┘  │
        │  ┌────────────────────────────────┐  │
        │  │  Laravel 12 API + Business Logic│  │
        │  └────────────────────────────────┘  │
        │  ┌────────────────────────────────┐  │
        │  │  MySQL + Redis + Horizon  │  │
        │  └────────────────────────────────┘  │
        └───────┬──────────┬─────────────┬─────┘
                │          │             │
     ┌──────────▼┐  ┌──────▼──────┐  ┌───▼─────────┐
     │ AI APIs   │  │ Meta Graph  │  │ Moyasar/Tap │
     │ Anthropic │  │ + Marketing │  │ Payments    │
     │ OpenAI    │  │ API         │  │             │
     │ Gemini    │  │             │  │             │
     └───────────┘  └─────────────┘  └─────────────┘
```

### 2.2 User Classes

| Class | Characteristics | Expected Usage |
|-------|-----------------|----------------|
| **Merchant** | 1-5 employees, single brand, low technical skill | Generate 5-20 posts/week, 1-2 campaigns/month |
| **Agency** | Manages 5-30 clients, marketing professionals | Multiple workspaces, 50-300 posts/week, heavy analytics |
| **Creator** | Solo operator, single IG/FB account | Generate + schedule, light on ads |
| **Admin** | Sada internal staff | Monitor, support, moderation |

### 2.3 Operating Environment

- **Server:** Ubuntu 22.04 LTS on DigitalOcean Droplets (2 vCPU, 4GB RAM initial)
- **Runtime:** PHP 8.3, Laravel 12, Node.js 20 (for Vite builds)
- **Database:** MySQL 16 (DO Managed)
- **Cache/Queue:** Redis 7
- **Web Server:** Nginx 1.24 + Laravel Octane (Swoole)
- **Client Browsers:** Chrome 120+, Safari 17+, Edge 120+, Firefox 120+
- **Mobile Browsers:** iOS Safari 17+, Chrome Android

---

## 3. Functional Requirements

### 3.1 Authentication & User Management (AUTH)

| ID | Requirement | Priority |
|----|------------|----------|
| AUTH-01 | System shall support email/password registration with email verification | Must |
| AUTH-02 | System shall support Google OAuth login | Must |
| AUTH-03 | System shall enforce password policy (min 8 chars, 1 uppercase, 1 number) | Must |
| AUTH-04 | System shall provide password reset via email token (expires 1 hour) | Must |
| AUTH-05 | System shall support "Remember Me" with 30-day token | Should |
| AUTH-06 | System shall rate-limit login attempts (5 per IP per 15 min) | Must |
| AUTH-07 | System shall support 2FA via TOTP (Phase 1) | Could |

### 3.2 Workspace Management (WS)

| ID | Requirement | Priority |
|----|------------|----------|
| WS-01 | User shall create up to 10 workspaces on free tier, unlimited on paid | Must |
| WS-02 | User shall switch between workspaces via dropdown in top nav | Must |
| WS-03 | Each workspace shall have independent: brand identity, social accounts, posts, campaigns, analytics | Must |
| WS-04 | Billing shall be aggregated at user level (all workspaces share token pool) | Must |
| WS-05 | User shall archive a workspace (soft delete, recoverable 30 days) | Should |
| WS-06 | Workspace transfer to another user (Phase 1) | Could |

### 3.3 Brand Identity (BI)

| ID | Requirement | Priority |
|----|------------|----------|
| BI-01 | User shall define brand identity per workspace: name, description, tone, banned words, example posts | Must |
| BI-02 | User shall upload brand logo (PNG/SVG, max 2MB) | Must |
| BI-03 | User shall define up to 10 banned words | Must |
| BI-04 | User shall add up to 5 example posts as training context | Must |
| BI-05 | User shall toggle "Use Brand Identity" per generation | Must |
| BI-06 | System shall inject brand identity into AI agent context when toggled ON | Must |
| BI-07 | System shall reject AI output containing banned words and regenerate | Must |

### 3.4 Content Generation (CG)

| ID | Requirement | Priority |
|----|------------|----------|
| CG-01 | User shall select content type: Post, Reel/Short Video Script, Story, Ad Copy, Tweet Thread, Snap Caption | Must |
| CG-02 | User shall select target platform: Instagram, Facebook, TikTok, X, Snapchat | Must (IG/FB MVP; TikTok/X/Snap Phase 0.5) |
| CG-03 | User shall select dialect: Formal Arabic, Saudi, Emirati, Kuwaiti, Qatari, Bahraini, Omani | Must |
| CG-04 | User shall enter a short prompt (max 500 chars) describing the idea | Must |
| CG-05 | System shall generate 3 variations per request | Must |
| CG-06 | System shall return within P95 < 8 seconds (non-streaming) or first token < 2s (streaming) | Must |
| CG-07 | User shall edit generated content inline before saving | Must |
| CG-08 | User shall save output as Draft, schedule, or publish immediately | Must |
| CG-09 | System shall auto-generate 5-10 relevant hashtags per variation (platform-tuned) | Must |
| CG-10 | System shall enforce max token cap per generation (configurable per plan) | Must |
| CG-11 | System shall enforce platform-specific rules: character limits (X: 280, Snap: 250), format (TikTok: hook-heavy, IG: first 125 chars critical) | Must |

### 3.5 Scheduling & Publishing (SCH)

| ID | Requirement | Priority |
|----|------------|----------|
| SCH-01 | User shall publish immediately to any connected platform (Instagram, Facebook, TikTok, X, Snapchat) | Must (IG/FB MVP; rest Phase 0.5) |
| SCH-02 | User shall schedule posts at specific date/time (timezone-aware, default: Asia/Riyadh) | Must |
| SCH-03 | User shall configure recurring posts (e.g., every Monday at 7pm) | Must |
| SCH-04 | System shall provide AI-suggested best posting times based on audience activity (per platform) | Must |
| SCH-05 | User shall view posts in Calendar view (monthly/weekly) with drag-drop rescheduling | Must |
| SCH-06 | System shall show scheduled posts in queue with status: pending, published, failed | Must |
| SCH-07 | System shall retry failed publishes up to 3 times with exponential backoff | Must |
| SCH-08 | System shall notify user via email/in-app on publish failure after retries | Must |
| SCH-09 | User shall bulk-schedule posts (from campaign) | Should |
| SCH-10 | User shall cross-post a single piece of content to multiple platforms with platform-specific adaptations | Must |
| SCH-11 | System shall respect platform-specific publishing constraints (e.g., TikTok requires video, X text-first, IG requires visual) | Must |

### 3.6 Seasonal Engine (SE)

| ID | Requirement | Priority |
|----|------------|----------|
| SE-01 | System shall include library of 15+ Gulf/Islamic occasions with Hijri+Gregorian dates | Must |
| SE-02 | System shall display upcoming seasons with countdown on dashboard | Must |
| SE-03 | System shall notify users 14 days before a major occasion | Must |
| SE-04 | User shall generate a full campaign (3-21 posts) for a selected occasion with one click | Must |
| SE-05 | Campaign generator shall accept inputs: post count, tone, industry, goals | Must |
| SE-06 | System shall auto-suggest publish dates across the campaign window | Must |
| SE-07 | User shall preview and edit all posts before approving the campaign | Must |
| SE-08 | System shall queue all approved posts as scheduled | Must |

**Seasons library (MVP) — Full Gulf Coverage:**

#### Islamic (Hijri — all Gulf countries)
1. رمضان المبارك (Ramadan) — month-long
2. عيد الفطر (Eid al-Fitr) — 3 days
3. عيد الأضحى (Eid al-Adha) — 4 days
4. رأس السنة الهجرية (Hijri New Year) — 1st of Muharram
5. المولد النبوي الشريف (Prophet's Birthday) — 12 Rabi' al-Awwal
6. ليلة القدر (Laylat al-Qadr) — within last 10 nights of Ramadan
7. يوم عرفة (Day of Arafah) — 9 Dhu al-Hijjah

#### Saudi Arabia 🇸🇦
8. يوم التأسيس السعودي (Saudi Founding Day) — Feb 22
9. يوم العلم السعودي (Saudi Flag Day) — Mar 11
10. اليوم الوطني السعودي (Saudi National Day) — Sep 23

#### United Arab Emirates 🇦🇪
11. يوم الشهيد الإماراتي (UAE Commemoration Day) — Nov 30
12. يوم الاتحاد الإماراتي / العيد الوطني (UAE National Day) — Dec 2

#### Qatar 🇶🇦
13. اليوم الوطني القطري (Qatar National Day) — Dec 18 (also called "يوم التأسيس")
14. يوم الرياضة القطري (Qatar Sports Day) — 2nd Tuesday of February
15. يوم العلم القطري (Qatar Flag Day) — Nov 18

#### Kuwait 🇰🇼
16. العيد الوطني الكويتي (Kuwait National Day) — Feb 25
17. عيد التحرير الكويتي (Kuwait Liberation Day) — Feb 26

#### Bahrain 🇧🇭
18. اليوم الوطني البحريني (Bahrain National Day) — Dec 16
19. يوم جلوس الملك (Bahrain Accession Day) — Dec 17

#### Oman 🇴🇲
20. العيد الوطني العماني (Oman National Day) — Nov 18
21. يوم النهضة العمانية (Oman Renaissance Day) — Jul 23

#### Retail / Commercial (Gulf-adapted)
22. White Friday / الجمعة البيضاء — late November
23. Cyber Monday / سايبر مونداي — Monday after White Friday
24. Back to School / العودة للمدارس — August-September
25. Summer Sales / تخفيضات الصيف — June-July
26. New Year / رأس السنة الميلادية — Jan 1

**Total: 26 occasions in MVP library.**

### 3.7 Paid Campaigns — Multi-Platform Ads (ADS)

| ID | Requirement | Priority |
|----|------------|----------|
| ADS-01 | User shall create a campaign with objective: Awareness, Traffic, Engagement, Conversions, App Installs, Video Views | Must |
| ADS-02 | User shall select target ad platform: Meta (IG+FB), TikTok Ads, Snapchat Ads, X Ads | Must (Meta MVP; rest Phase 0.5) |
| ADS-03 | User shall select ad creative (existing post or generate new) with platform-appropriate format | Must |
| ADS-04 | User shall define audience: countries (Gulf chips), age range, gender, interests | Must |
| ADS-05 | User shall set daily or lifetime budget in USD/SAR/AED/QAR/KWD/BHD/OMR | Must |
| ADS-06 | User shall define campaign duration (start/end dates) | Must |
| ADS-07 | System shall submit campaign to the respective platform's Ads API and track status | Must |
| ADS-08 | System shall display unified campaign metrics across platforms: spend, reach, impressions, CTR, CPC, ROAS | Must |
| ADS-09 | User shall pause/resume/edit/duplicate campaigns across any platform | Must |
| ADS-10 | System shall sync campaign insights every 15 minutes (per platform) | Must |
| ADS-11 | User shall compare performance across platforms for the same creative/campaign | Should |

### 3.8 Analytics & Insights (ANL)

| ID | Requirement | Priority |
|----|------------|----------|
| ANL-01 | Dashboard shall display KPIs: reach, impressions, engagement rate, CTR, follower growth, ROAS | Must |
| ANL-02 | System shall provide charts: engagement over time, best performing posts, best times heatmap | Must |
| ANL-03 | System shall generate AI Insights in Arabic ("منشوراتك المسائية تحقق engagement أعلى 45%") | Must |
| ANL-04 | User shall filter analytics by date range, platform, campaign, post type | Must |
| ANL-05 | User shall export PDF report with custom logo (for agencies) | Must |
| ANL-06 | User shall export Excel/CSV raw data | Should |
| ANL-07 | System shall support bilingual report language (Arabic/English/Both) | Must |

### 3.9 Billing & Tokens (BIL)

| ID | Requirement | Priority |
|----|------------|----------|
| BIL-01 | User shall purchase token packages via Moyasar or Tap | Must |
| BIL-02 | System shall deduct tokens per action: generation, publish, ad creation | Must |
| BIL-03 | System shall show token balance in UI at all times | Must |
| BIL-04 | System shall warn user when balance < 100 tokens | Must |
| BIL-05 | System shall block premium actions when balance = 0 | Must |
| BIL-06 | System shall issue VAT-compliant invoices for Saudi/UAE users | Must |
| BIL-07 | User shall download past invoices as PDF | Must |
| BIL-08 | System shall support auto-recharge (optional) when balance hits threshold | Should |

### 3.10 Social Account Connections (CON)

| ID | Requirement | Priority |
|----|------------|----------|
| CON-01 | User shall connect Instagram Business account via Meta OAuth | Must (MVP) |
| CON-02 | User shall connect Facebook Page via Meta OAuth | Must (MVP) |
| CON-03 | User shall connect TikTok Business account via TikTok OAuth | Must (Phase 0.5) |
| CON-04 | User shall connect Snapchat Business account via Snap OAuth | Must (Phase 0.5) |
| CON-05 | User shall connect X (Twitter) account via X OAuth 2.0 (PKCE) | Must (Phase 0.5) |
| CON-06 | System shall store access tokens encrypted (AES-256) for all providers | Must |
| CON-07 | System shall refresh long-lived tokens automatically before expiry (per provider's rules) | Must |
| CON-08 | User shall disconnect an account at any time (revoke token on provider side) | Must |
| CON-09 | System shall show connection health status (healthy, token expired, permissions revoked, error) | Must |
| CON-10 | System shall display a unified "Connected Accounts" page grouped by workspace | Must |
| CON-11 | System shall support multiple accounts per platform per workspace (e.g., 2 IG accounts) | Should |

---

## 4. Non-Functional Requirements

### 4.1 Performance

| ID | Requirement |
|----|-------------|
| NFR-PERF-01 | Page load P95 ≤ 2 seconds on 4G connection |
| NFR-PERF-02 | AI generation P95 ≤ 8 seconds (non-streaming); first token ≤ 2 seconds (streaming) |
| NFR-PERF-03 | Dashboard queries P95 ≤ 500ms |
| NFR-PERF-04 | System shall support 1,000 concurrent users at launch |
| NFR-PERF-05 | System shall handle 10,000 scheduled posts/day |
| NFR-PERF-06 | API response time for CRUD operations ≤ 300ms |

### 4.2 Availability

| ID | Requirement |
|----|-------------|
| NFR-AVL-01 | System uptime ≥ 99.5% (MVP) measured monthly |
| NFR-AVL-02 | Scheduled maintenance windows announced 48 hours in advance |
| NFR-AVL-03 | Database backups every 6 hours, retained 30 days |
| NFR-AVL-04 | AI provider failover automatic (Anthropic → OpenAI → Gemini) |

### 4.3 Scalability

| ID | Requirement |
|----|-------------|
| NFR-SCL-01 | Horizontal scaling of web servers behind load balancer |
| NFR-SCL-02 | Queue workers scalable based on queue depth |
| NFR-SCL-03 | Database read replicas for analytics queries (Phase 1) |

### 4.4 Security

| ID | Requirement |
|----|-------------|
| NFR-SEC-01 | All traffic over TLS 1.3 |
| NFR-SEC-02 | Passwords hashed using bcrypt with cost factor ≥ 12 |
| NFR-SEC-03 | Social access tokens encrypted at rest (AES-256) |
| NFR-SEC-04 | Row-level security: user cannot access data outside their workspaces |
| NFR-SEC-05 | CSRF protection on all state-changing requests |
| NFR-SEC-06 | Rate limiting on AI endpoints (per user and per workspace) |
| NFR-SEC-07 | SQL injection prevention via Eloquent/parameterized queries |
| NFR-SEC-08 | XSS prevention via Vue escaping + CSP headers |
| NFR-SEC-09 | Dependency vulnerability scanning (weekly Composer audit) |
| NFR-SEC-10 | Secrets stored in `.env`, never committed to Git |

### 4.5 Usability

| ID | Requirement |
|----|-------------|
| NFR-USE-01 | UI shall be RTL-first for Arabic, LTR for English content |
| NFR-USE-02 | WCAG 2.1 AA compliance minimum |
| NFR-USE-03 | Mobile-responsive: Desktop (1440px), Tablet (768px), Mobile (375px) |
| NFR-USE-04 | Onboarding completion rate ≥ 60% target |
| NFR-USE-05 | Keyboard navigation support for all critical flows |

### 4.6 Compliance

| ID | Requirement |
|----|-------------|
| NFR-CMP-01 | PDPL (Saudi) compliance for user data handling |
| NFR-CMP-02 | UAE Data Protection Law compliance |
| NFR-CMP-03 | GDPR-like data export and deletion rights for all users |
| NFR-CMP-04 | Terms of Service and Privacy Policy in Arabic and English |
| NFR-CMP-05 | VAT invoicing per Saudi ZATCA and UAE FTA standards |

### 4.7 Maintainability

| ID | Requirement |
|----|-------------|
| NFR-MTN-01 | Code coverage ≥ 70% for business logic |
| NFR-MTN-02 | All public APIs documented (OpenAPI/Swagger) |
| NFR-MTN-03 | Laravel Pint for PHP style, ESLint + Prettier for JS/Vue |
| NFR-MTN-04 | PSR-12 compliance |
| NFR-MTN-05 | Structured logging (JSON format) with log levels |

---

## 5. System Architecture

### 5.1 Layered Architecture

```
┌──────────────────────────────────────────────────┐
│          Presentation Layer                      │
│  Vue 3 Components + Inertia.js (SSR via Laravel) │
└──────────────────────────────────────────────────┘
                       ▲
                       │
┌──────────────────────▼───────────────────────────┐
│          Application Layer                        │
│  Laravel Controllers + FormRequests + Resources   │
└──────────────────────────────────────────────────┘
                       ▲
                       │
┌──────────────────────▼───────────────────────────┐
│          Domain Layer                             │
│  Services, Actions, DTOs, Events, Policies        │
└──────────────────────────────────────────────────┘
                       ▲
                       │
┌──────────────────────▼───────────────────────────┐
│          Infrastructure Layer                     │
│  Eloquent Models, Queue Jobs, External API Clients│
│  AI Agents (Laravel AI SDK), Meta Client, Payment │
└──────────────────────────────────────────────────┘
                       ▲
                       │
┌──────────────────────▼───────────────────────────┐
│          Data Layer                               │
│  MySQL (relational) + Redis (cache/queue)    │
└──────────────────────────────────────────────────┘
```

### 5.2 Key Architectural Patterns

- **Action Pattern:** Single-purpose classes (e.g., `GenerateContentAction`, `PublishPostAction`) replace fat controllers
- **Multi-tenancy:** Shared database, `workspace_id` column on all tenant-scoped tables, global scope on Eloquent models
- **Event-Driven:** Key actions emit events (`PostPublished`, `CampaignReady`) for decoupled listeners
- **Queue-Based Async:** Heavy operations (AI generation, publishing, analytics sync) processed via Laravel Horizon
- **Repository Pattern for External APIs:** `MetaGraphClient`, `MetaAdsClient`, `MoyasarClient` as testable abstractions

### 5.3 Request Flow — Content Generation Example

```
User → Vue Component → Inertia POST /api/content/generate
    → ContentController@generate
        → GenerateContentRequest (validation)
        → Ai::agent(ContentGeneratorAgent) [Middleware: TrackTokenUsage, EnforceQuota]
            → Anthropic API (streaming)
        ← Response with 3 variations
    ← JSON response
← Vue renders variations
```

---

## 6. Data Model

### 6.1 Core Entities (MVP)

```sql
users (id, name, email, password, email_verified_at, created_at, updated_at, token_balance)

workspaces (id, user_id, name, business_type, countries, default_dialect, logo_path,
            archived_at, created_at, updated_at)

brand_identities (id, workspace_id, description, tone, banned_words JSONB,
                  example_posts JSONB, target_audience, updated_at)

-- Unified social accounts: supports Meta IG/FB (MVP), TikTok/Snap/X (Phase 0.5)
social_accounts (id, workspace_id,
                 provider ENUM('instagram','facebook','tiktok','snapchat','x'),
                 account_id, account_name, account_handle,
                 access_token ENCRYPTED, refresh_token ENCRYPTED,
                 expires_at, scopes JSONB,
                 status ENUM('healthy','expired','revoked','error'),
                 last_synced_at, provider_metadata JSONB)

-- Posts: platform-aware content
posts (id, workspace_id, user_id, social_account_id,
       content_type ENUM('post','reel','story','ad','tweet','snap_ad'),
       platform ENUM('instagram','facebook','tiktok','snapchat','x'),
       content TEXT, hashtags JSONB, media_urls JSONB,
       platform_specific JSONB,  -- e.g. tweet threading, TikTok video metadata
       status, scheduled_for, published_at, external_post_id,
       error_message, campaign_id, created_at)

-- Cross-posting: one canonical content, multiple published instances
post_publications (id, canonical_post_id, target_platform,
                   target_social_account_id, platform_adapted_content,
                   scheduled_for, published_at, status, external_post_id,
                   error_message, created_at)

campaigns (id, workspace_id, user_id, name, type, season_id, objective,
           ad_platform ENUM('meta','tiktok','snapchat','x'),
           budget_amount, budget_type, currency,
           start_date, end_date, status,
           external_campaign_id, meta_data JSONB, created_at)

seasonal_occasions (id, key, name_ar, name_en,
                    calendar_type ENUM('hijri','gregorian'),
                    hijri_month, hijri_day,
                    gregorian_month, gregorian_day,
                    country_codes JSONB,  -- e.g. ['QA'] for Qatar-specific
                    category ENUM('religious','national','commercial'),
                    templates JSONB)

ai_generations (id, workspace_id, user_id, agent_type, prompt, response JSONB,
                provider, model, input_tokens, output_tokens, sada_tokens_charged,
                target_platform, dialect, cached BOOLEAN, created_at)

token_transactions (id, user_id, workspace_id, type, amount, balance_after,
                    description, reference_id, reference_type, created_at)

token_purchases (id, user_id, package, amount_usd, amount_local, currency,
                 tokens_credited, payment_gateway, payment_reference, status,
                 created_at)

analytics_snapshots (id, workspace_id, post_id, platform,
                     reach, impressions, engagement, clicks, shares,
                     platform_specific_metrics JSONB,
                     snapshot_date, created_at)
```

### 6.2 Indexes (Critical for Performance)

- `posts`: `(workspace_id, scheduled_for)` for calendar queries
- `posts`: `(workspace_id, status)` for filtering
- `ai_generations`: `(workspace_id, created_at DESC)` for usage history
- `token_transactions`: `(user_id, created_at DESC)` for billing
- `analytics_snapshots`: `(post_id, snapshot_date)` for time-series

---

## 7. API Specification

### 7.1 API Conventions

- Base URL: `https://api.sada.sa/v1`
- Authentication: Bearer token (Laravel Sanctum)
- Format: JSON
- Versioning: URL-based (`/v1/`)
- Rate limiting: 60 req/min per user (300 for paid), 429 response when exceeded
- Pagination: cursor-based for lists (via `?cursor=`)

### 7.2 Key Endpoints (MVP)

```
# Auth
POST   /v1/auth/register
POST   /v1/auth/login
POST   /v1/auth/logout
POST   /v1/auth/forgot-password
POST   /v1/auth/reset-password

# Workspaces
GET    /v1/workspaces
POST   /v1/workspaces
GET    /v1/workspaces/{id}
PATCH  /v1/workspaces/{id}
DELETE /v1/workspaces/{id}

# Brand Identity
GET    /v1/workspaces/{id}/brand
PUT    /v1/workspaces/{id}/brand

# Content Generation
POST   /v1/content/generate           # Sync
POST   /v1/content/generate/stream    # SSE streaming
POST   /v1/content/regenerate

# Posts
GET    /v1/posts                      # ?status=&platform=&from=&to=
POST   /v1/posts
GET    /v1/posts/{id}
PATCH  /v1/posts/{id}
DELETE /v1/posts/{id}
POST   /v1/posts/{id}/publish         # Immediate
POST   /v1/posts/{id}/schedule
GET    /v1/posts/calendar             # Monthly/weekly view

# Social Accounts
GET    /v1/workspaces/{id}/social-accounts
POST   /v1/social/connect/meta        # Initiates OAuth
GET    /v1/social/callback/meta       # OAuth callback
DELETE /v1/social-accounts/{id}

# Seasons & Campaigns
GET    /v1/seasons
GET    /v1/seasons/upcoming
POST   /v1/campaigns/seasonal/generate
GET    /v1/campaigns
POST   /v1/campaigns/ads
GET    /v1/campaigns/{id}
PATCH  /v1/campaigns/{id}
POST   /v1/campaigns/{id}/pause
POST   /v1/campaigns/{id}/resume

# Analytics
GET    /v1/analytics/overview
GET    /v1/analytics/posts
GET    /v1/analytics/campaigns
GET    /v1/analytics/insights         # AI-generated
POST   /v1/analytics/export           # PDF/Excel

# Billing
GET    /v1/billing/balance
GET    /v1/billing/packages
POST   /v1/billing/checkout           # Moyasar/Tap
POST   /v1/billing/webhook/moyasar
POST   /v1/billing/webhook/tap
GET    /v1/billing/invoices
GET    /v1/billing/invoices/{id}/download

# Webhooks (inbound from Meta)
POST   /v1/webhooks/meta              # Page events, messaging, insights
```

---

## 8. External Integrations

### 8.1 Meta (Facebook + Instagram) — MVP Phase 0

**APIs:**
- Graph API v19.0+ (organic posts, engagement data)
- Marketing API v19.0+ (ads)
- Webhooks (real-time events)

**Required Permissions / Scopes:**
- `pages_show_list` — list user's Pages
- `pages_read_engagement` — read Page insights
- `pages_manage_posts` — publish to Page
- `instagram_basic` — IG account access
- `instagram_content_publish` — publish to IG
- `ads_management` — create/manage ads
- `business_management` — access Business Manager
- `read_insights` — analytics

**OAuth Flow:** OAuth 2.0 with long-lived tokens (60-day Page tokens, extended via refresh logic)

**App Review Required For:** `ads_management`, `instagram_content_publish`, `pages_manage_posts`
**Timeline Impact:** 2-6 weeks — **submit Day 1 of MVP**

**Rate Limits:**
- Graph: 200 calls/hour/user
- Marketing: 100 calls/hour/ad account
- Implement: exponential backoff, per-user throttling, queue-based batching

**Content Constraints:**
- IG: 2200 char caption max, 30 hashtags max, requires image/video
- FB: 63,206 char max, any format
- IG Reels: 90 sec video, vertical 9:16
- IG Stories: 60 sec video, vertical 9:16

---

### 8.2 TikTok — Phase 0.5

**APIs:**
- TikTok Business API v1.3+ (account info, analytics)
- TikTok Content Posting API (publishing)
- TikTok Marketing API (ads)

**Required Permissions / Scopes:**
- `user.info.basic` — account info
- `video.publish` — publish videos
- `video.upload` — upload video files
- `video.list` — read user's videos
- `biz.creator.info` — Business account data
- `biz.creator.insights` — analytics
- `ads.manage` — create/manage ads

**OAuth Flow:** OAuth 2.0 with authorization code flow, 24-hour access token + 365-day refresh token

**App Review Required:** Yes, TikTok has a strict review process requiring a demo video of the integration and business justification. **Timeline: 3-8 weeks**, sometimes longer.

**Business Requirements:**
- TikTok for Business account (required for Ads API)
- Signed MSA (Master Services Agreement) for Marketing API access
- Must comply with TikTok Commerce Policy

**Rate Limits:**
- Content Posting: 6 posts/day per account (enforced by TikTok)
- API calls: 1000/day base, can request increases
- Uploads: 287.6 MB per video max

**Content Constraints:**
- Video only (no image-only posts via API)
- Duration: 3-180 seconds (standard), up to 10 min for eligible accounts
- Resolution: min 540×960, vertical 9:16 preferred
- Caption: 2200 chars max, up to 30 hashtags

**Gulf-Specific Considerations:**
- TikTok is dominant among Gulf youth (18-34) — especially Saudi Arabia
- Content moderation is strict for the MENA region — AI generator must avoid sensitive themes

---

### 8.3 Snapchat — Phase 0.5

**APIs:**
- Snap Kit (authentication)
- Snap Marketing API (ads)
- Snap Business API (account management)

**Required Permissions / Scopes:**
- `snapchat-marketing-api` — full ads API access
- `snapchat-profile-api` — profile info
- `snapchat-account-read` — account data
- `snapchat-account-write` — campaign management

**OAuth Flow:** OAuth 2.0 with authorization code, 30-min access token + refresh token

**App Review Required:** Yes, via Snap Business Manager approval + API access application. **Timeline: 2-4 weeks**

**Business Requirements:**
- Snap Ads Manager / Business Manager account
- Verified business via business verification process
- Signed Terms of Service for Marketing API

**Rate Limits:**
- 1000 requests/minute default
- Campaigns: unlimited creation within budget

**Content Constraints:**
- **Organic posting via API is NOT supported** — Snapchat restricts organic posts to native app
- Only **Snap Ads** can be created via API (story ads, collection ads, single image/video ads)
- Single Image Ads: 9:16, min 1080×1920
- Video Ads: 3-180 sec, 9:16, MP4/MOV
- Caption (brand name): 25 chars max
- Headline: 34 chars max
- CTA: predefined list (Shop Now, Learn More, etc.)

**Gulf-Specific Considerations:**
- Snapchat has extraordinary penetration in Saudi Arabia (>90% of 13-34 demographic)
- Snap Ads are a key channel for Saudi e-commerce — critical for merchant persona
- **Major limitation for Sada:** no organic post publishing; users must continue posting to Snap natively, Sada handles only Ads

---

### 8.4 X (Twitter) — Phase 0.5

**APIs:**
- X API v2 (tweets, users, analytics)
- X Ads API v12+ (promoted tweets, campaigns)

**Required Permissions / Scopes (OAuth 2.0):**
- `tweet.read` — read tweets
- `tweet.write` — post tweets
- `users.read` — user info
- `offline.access` — refresh tokens
- `like.read`, `like.write` — engagement
- `ads_read`, `ads_write` — ads management (requires Ads API access tier)

**OAuth Flow:** OAuth 2.0 with PKCE (Proof Key for Code Exchange)

**Access Tiers & Pricing (2026):**
- **Free Tier:** 1,500 tweets/month read, 500 write — insufficient for SaaS
- **Basic Tier:** $200/month — 10,000 tweets/month, suitable for small-scale
- **Pro Tier:** $5,000/month — 1M tweets/month
- **Ads API:** requires separate approval + partnership status

**App Review / Access:**
- Basic tier: immediate after payment
- Ads API access: requires manual approval (1-4 weeks) + proven use case
- **Cost Note:** Adds $200/month fixed OpEx to Sada

**Rate Limits:**
- Tweet posting: 100 tweets/15min per user (Basic tier)
- Read: 10K req/15min (app-level, Basic tier)

**Content Constraints:**
- Tweet: 280 chars (Premium users: 25,000 chars — handle both cases)
- Threads: chain of tweets, up to 25 per thread
- Media: up to 4 images or 1 video per tweet
- Video: 2min 20sec max, MP4

**Gulf-Specific Considerations:**
- X is the dominant platform for news, politics, and B2B conversations in the Gulf
- Saudi Arabia has one of the highest X penetrations globally per capita
- Hashtag culture is strong — AI generator must leverage trending Arabic hashtags
- Text-first platform — no visual required, opens content generation for text-only SaaS

---

### 8.5 AI Providers

| Provider | Use | Model | Failover Priority |
|----------|-----|-------|-------------------|
| Anthropic | Arabic content, seasonal, long-form | Claude Sonnet 4.5 | 1 (Primary) |
| OpenAI | Ad copy, English content, fallback | GPT-4o | 2 |
| Google | Hashtags, insights, cost optimization | Gemini 2.0 Flash | 3 |

See `Laravel_AI_SDK_Sada_Integration.md` for full integration details.

---

### 8.6 Payment Gateways

- **Moyasar** (Primary — Saudi Arabia): Mada, Visa, MasterCard, Apple Pay, STC Pay
- **Tap** (Wider Gulf): KNET (Kuwait), Benefit (Bahrain), Qatar Debit, OmanNet, Apple Pay, Google Pay
- Webhook verification via HMAC signatures
- **Requires:** Saudi commercial registration for Moyasar; Bahraini/Emirati/Kuwaiti entity for Tap

### 8.7 Email

- **Provider:** Amazon SES or Postmark
- Transactional: verification, password reset, publish failures, campaign ready
- Marketing: seasonal reminders, weekly digest (opt-in)

---

### 8.8 Integration Comparison Matrix

| Capability | Meta | TikTok | Snapchat | X | Timeline |
|-----------|------|--------|----------|---|----------|
| OAuth 2.0 | ✅ | ✅ | ✅ | ✅ PKCE | — |
| Organic Post Publishing | ✅ IG+FB | ✅ Video only | ❌ **Not supported** | ✅ Text+media | — |
| Scheduled Posting | ✅ Native + Sada | Sada only | N/A (ads only) | Sada only | — |
| Paid Ads API | ✅ | ✅ | ✅ | ✅ (paid tier) | — |
| Organic Analytics | ✅ Rich | ✅ Good | ❌ N/A | ✅ Basic | — |
| Ads Analytics | ✅ Rich | ✅ Good | ✅ Good | ✅ Basic | — |
| App Review Required | ✅ (2-6w) | ✅ (3-8w) | ✅ (2-4w) | 💰 Paid + review (1-4w) | — |
| Additional Cost | $0 | $0 | $0 | $200/mo (Basic) | — |
| MVP Scope | ✅ Full | — | — | — | Week 1-8 |
| Phase 0.5 Scope | — | ✅ Full | ✅ Ads only | ✅ Full | Week 9-12 |

### 8.9 Critical Integration Notes

1. **Snapchat limitation is material:** users must understand Sada = Snap Ads Manager + analytics, not a Snap posting tool. This must be communicated in onboarding and marketing copy.
2. **X adds fixed OpEx:** $200/month Basic tier is the minimum viable. Must be factored into unit economics and potentially charged back to Agency-tier users.
3. **TikTok Content Posting API requires video:** organic posts are video-only. Users will expect to upload a video file — Sada's AI generation in MVP is text-only, so TikTok organic posting will feel incomplete until AI video generation (Phase 2). In Phase 0.5, TikTok = caption + hashtag generation + user-uploaded video.
4. **Parallel App Reviews:** submit Meta (Day 1), TikTok (Week 5), Snap (Week 6), X (Week 7) — staggered to manage review workload.
5. **Unified token storage:** single `social_accounts` table with `provider` enum handles all 5 platforms cleanly. See Data Model §6.

---

## 9. Security Requirements

### 9.1 Authentication Security

- Laravel Sanctum for API tokens
- Session cookies: `HttpOnly`, `Secure`, `SameSite=Strict`
- Password reset tokens single-use, 1-hour expiry

### 9.2 Data Protection

- Social access tokens encrypted via Laravel's `encrypted` cast
- Payment data never stored — tokenized via Moyasar/Tap
- PII (phone numbers, addresses) minimally collected

### 9.3 API Security

- Rate limiting per endpoint category
- CORS locked to `sada.sa` and subdomains
- Webhook signature verification (Meta, Moyasar, Tap)

### 9.4 Infrastructure Security

- DigitalOcean Cloud Firewall (only 80/443/22 open)
- SSH key-only authentication, fail2ban
- Automated security updates for OS
- Weekly dependency audits (`composer audit`, `npm audit`)

---

## 10. Assumptions & Dependencies

### Assumptions
1. Gulf commercial entity available for Moyasar/Tap merchant accounts
2. Team capacity: 1 CTO + 1 backend  + 1 frontend (Abd) 
3. AI API budget: $500-1500/month initial
4. Meta Business Manager account verified before App Review submission

### Dependencies

**MVP (Phase 0) — Blocking:**
1. **Meta App Review approval** — blocking for ads feature in MVP (2-6 weeks)
2. **Moyasar/Tap merchant accounts** — blocking for billing (1-3 weeks after legal entity)
3. **Laravel AI SDK stability** — first-party, released Feb 2026, should be stable by MVP launch
4. **Domain & SSL** — sada.sa (or alternative) must be secured

**Phase 0.5 — Blocking:**
5. **TikTok Developer account + App Review** — 3-8 weeks. Submit by Week 5 of MVP to hit Phase 0.5 timeline
6. **Snapchat Business Manager verification + API access** — 2-4 weeks. Submit by Week 6
7. **X API paid subscription** — $200/month Basic tier. Activate Week 7
8. **X Ads API access** — separate approval, 1-4 weeks. Apply Week 7

### Out-of-Scope Risks (documented in BRD §11)

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | April 2026 | Ahmed Jaber | Initial draft for MVP implementation |


# CLAUDE.md — Sada (صدى) Platform

> Context file for Claude Code. Loaded automatically in every session.
> Owner: Ahmed Jaber (CTO) | Last updated: April 2026

---

## Project Overview

**Sada (صدى)** — Arabic RTL-first SaaS for AI-powered digital marketing, targeting the Gulf market (SA, UAE, KW, QA, BH, OM).

Core value: generate marketing content in 7 Gulf dialects + seasonal campaigns (26 Gulf/Islamic occasions) + unified organic/paid publishing on Meta (MVP) and TikTok/Snap/X (Phase 0.5).

**Team:** Ahmed Jaber (CTO + full backend) · Abd Jaber (frontend)

---

## Stack (non-negotiable — no alternatives)

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 + PHP 8.4 |
| Frontend | Inertia.js + Vue 3 · Composition API · `<script setup lang="ts">` |
| Styling | Tailwind CSS v4 + RTL plugin |
| Queue | Redis + Laravel Horizon |
| Runtime (prod) | Laravel Octane · Swoole driver — Linux/staging only |
| Runtime (local) | `php artisan serve` — Windows/Laragon |
| Auth | Laravel Sanctum + Socialite (Google OAuth) |
| AI | `laravel/ai` — Anthropic (primary) → OpenAI → Gemini |
| Payments | Moyasar (SA primary) + Tap (Gulf) |
| Testing | Pest + pest-plugin-laravel |
| Static Analysis | Larastan + PHPStan level 8 |
| Database | MySQL + Redis |
| Code style | Laravel Pint (PSR-12) |

---

## Architecture Rules

```
Controllers → Actions → Services → Repositories → Models
```

- **Controllers:** thin, delegate immediately to Actions
- **Actions:** `app/Actions/*` — single-purpose, all business logic lives here
- **Services:** `app/Services/{Meta,Social,Payments}` — external API wrappers
- **Multi-tenancy:** every tenant table has `workspace_id` + Eloquent global scope (`BelongsToWorkspace` trait)
- **AI:** `app/Ai/{Agents,Tools,Schemas,Middleware}`
- **Events:** key actions emit events (`PostPublished`, `CampaignReady`) for decoupled listeners
- **Queues:** heavy ops (AI generation, publishing, analytics sync) → Laravel Horizon

---

## Frontend Structure

```
resources/js/
  Components/
    Base/          # Button, Input, Alert, Card, Badge, Modal, Toast
    Layout/        # AppSidebar, AppHeader, AppLayout
  Pages/
    Auth/          # Login, Register, ForgotPassword, ResetPassword
    Dashboard/
    Generate/
    Calendar/
    Seasonal/
    Campaigns/
    Analytics/
    Billing/
    Settings/
  Composables/     # useAuth, useWorkspace, useToast, useGenerate
  Stores/          # auth.ts, workspace.ts, ui.ts (Pinia)
  Types/           # user.ts, workspace.ts, post.ts, api.ts
```

---

## Design System (tokens.css is authoritative)

```css
/* Colors */
--sada-500: #0F6F5C   /* Primary — Gulf olive green */
--sada-600: #0A5A4B   /* Hover */
--sand-500: #C8965F   /* Accent — sandy gold */
--ink-900:  #0E1512   /* Primary text */
--ink-500:  #5E6A64   /* Muted text */
--bg-page:  #F7F8F7   /* Page background — warm, not pure white */

/* Typography */
--font-arabic: 'Tajawal', 'IBM Plex Sans Arabic', system-ui, sans-serif;
/* Base: 14px / line-height 1.6 / letter-spacing: 0 for Arabic */
/* font-weight: 500 = visual regular, 600 = emphasis, 700 = bold */

/* Spacing: 4px base scale */
/* Radius: 6px (sm) · 10px (md) · 16px (lg) */
/* Sidebar: 260px expanded · 72px collapsed */
```

**RTL rules:**
- `body { direction: rtl; }` always
- All directional icons (arrows, chevrons) must flip for RTL
- Toast slides from the right (not left)
- Progress bars fill from right to left
- Never mirror an LTR design — design RTL from scratch

---

## Code Rules

1. **SRS Requirement ID first:** always mention the ID before writing code (e.g., `AUTH-01`, `CG-05`, `WS-03`)
2. **Laravel 13 syntax only** — no Laravel 10/11/12 patterns
3. **Vue 3 Composition API only** — `<script setup lang="ts">`, no Options API
4. **Arabic test content** — Pest tests use real Arabic strings, not lorem ipsum
5. **No fat controllers** — delegate to Actions
6. **No N+1 queries** — eager load always
7. **Encrypted social tokens** — `encrypted` cast on all OAuth tokens (AES-256)
8. **Conventional Commits:** `feat(scope):` / `fix(scope):` / `chore(scope):`

---

## SRS Requirement IDs (reference)

| Area | IDs |
|------|-----|
| Auth | AUTH-01 → AUTH-07 |
| Workspace | WS-01 → WS-06 |
| Brand Identity | BI-01 → BI-07 |
| Content Generation | CG-01 → CG-11 |
| Scheduling | SCH-01 → SCH-11 |
| Seasonal Engine | SE-01 → SE-08 (26 occasions) |
| Paid Ads | ADS-01 → ADS-11 |
| Analytics | ANL-01 → ANL-07 |
| Billing | BIL-01 → BIL-08 |
| Social Connections | CON-01 → CON-11 |
| NFR Performance | NFR-PERF-01 → 06 |
| NFR Security | NFR-SEC-01 → 10 |

---

## Git Workflow

```
main (production, protected)
 └─ develop (staging, protected)
     ├─ milestone/m1-foundation          (Weeks 1-2)
     │   ├─ sprint/s1-project-skeleton
     │   └─ sprint/s2-workspaces-brand
     ├─ milestone/m2-content-generation  (Weeks 3-4)
     ├─ milestone/m3-meta-scheduling     (Weeks 5-6)
     ├─ milestone/m4-seasonal-ads-analytics (Week 7)
     ├─ milestone/m5-billing-launch      (Week 8)
     ├─ milestone/m6-tiktok-integration  (Weeks 9-10)
     └─ milestone/m7-snap-x-integration  (Weeks 11-12)
```

- **Feature branches:** `feature/<kebab-name>` from sprint branch
- **Merging:** squash (feature→sprint) · merge commit (sprint→milestone→develop→main)
- **No direct commits** to `main` or `develop`

---

## API Conventions

- Base: `https://api.sada.sa/v1`
- Auth: Bearer token (Sanctum)
- Rate limiting: 60 req/min (free) · 300 (paid) · 5 login attempts/IP/15min
- Pagination: cursor-based (`?cursor=`)
- Versioning: URL-based `/v1/`
- Responses: consistent JSON `{ data, meta, message }`

---

## Key Data Model (MVP)

```
users               id, name, email, password, email_verified_at, token_balance
workspaces          id, user_id, name, business_type, countries, default_dialect, logo_path, archived_at
brand_identities    id, workspace_id, description, tone, banned_words JSON, example_posts JSON
social_accounts     id, workspace_id, provider ENUM, access_token ENCRYPTED, expires_at, status ENUM
posts               id, workspace_id, user_id, content, platform ENUM, status, scheduled_for
ai_generations      id, workspace_id, agent_type, input_tokens, output_tokens, sada_tokens_charged, cached
token_transactions  id, user_id, type, amount, balance_after
```

All tenant tables: `workspace_id` + global scope.

---

## Docs Location

```
docs/project/01_SRS.md.md          — Requirements (primary reference)
docs/project/02_BRD.md.md          — Business requirements & personas
docs/project/03_Implementation_Plan.md.md — Milestones & sprint tasks
docs/project/04_UI_UX_Spec.md.md   — Design system & screen specs
docs/project/05_AI_SDK_Guide.md.md — laravel/ai integration guide
docs/project/06_Platform_APIs.md.md — Meta/TikTok/Snap/X API details
docs/project/07_Git_Workflow.md.md — Branching & commit conventions
docs/design/design-reference.html.html  — Visual mockup (React-based, use as reference only)
docs/design/styles/tokens.css      — Design tokens (authoritative)
docs/design/src/screens/           — Screen components (React JSX → convert to Vue)
```

---

## Resolved Doc Conflicts

| Conflict | Resolution |
|---------|-----------|
| Database: BRD/ImplPlan say PostgreSQL | **MySQL** — user's explicit instruction |
| Laravel version: docs say 12 | **Laravel 13** — installed |
| PHP version: docs say 8.3 | **PHP 8.4** — composer.json updated |
| Seasonal count: SE-01 says "15+" | **26 occasions** — SRS body is authoritative |
| Font primary: spec says IBM Plex first | **Tajawal first** — tokens.css is authoritative |
| "Ghassan" in Impl Plan | **Ahmed Jaber handles all backend tasks** |

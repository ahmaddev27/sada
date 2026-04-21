---
title: Git Workflow & Branching Strategy — Sada Platform

---

# Git Workflow & Branching Strategy — Sada Platform

**Document ID:** SADA-GIT-001
**Version:** 1.0
**Date:** April 2026
**Owner:** Ahmed Qaddoura

This document defines how the team manages code across the 8-week MVP sprint. It's based on a **modified GitFlow** adapted for rapid iteration with 4 developers.

---

## 1. Core Branches (Permanent)

```
main          ← Production-ready code. Protected. Deploys auto to prod.
develop       ← Integration branch. Deploys auto to staging.
```

**Rules:**
- **No direct commits** to `main` or `develop`. Ever.
- All changes via Pull Request + 1 approval minimum
- `main` branch protection: require PR, require status checks (CI green), require approval from Ahmed Q
- `develop` branch protection: require PR, require status checks

---

## 2. Branch Hierarchy

```
main
 └─ develop
     ├─ milestone/m1-foundation                      (Week 1-2 — MVP)
     │   ├─ sprint/s1-project-skeleton
     │   │   ├─ feature/auth-login-register
     │   │   ├─ feature/rtl-base-layout
     │   │   └─ feature/google-oauth
     │   └─ sprint/s2-workspaces-brand
     │       ├─ feature/workspaces-crud
     │       └─ feature/brand-identity
     ├─ milestone/m2-content-generation              (Week 3-4 — MVP)
     ├─ milestone/m3-meta-scheduling                 (Week 5-6 — MVP)
     ├─ milestone/m4-seasonal-ads-analytics          (Week 7   — MVP)
     ├─ milestone/m5-billing-launch                  (Week 8   — MVP)
     ├─ milestone/m6-tiktok-integration              (Week 9-10 — Phase 0.5)
     └─ milestone/m7-snap-x-integration              (Week 11-12 — Phase 0.5)
```

---

## 3. Branch Naming Convention

**Format:** `<type>/<short-kebab-description>`

| Type | Purpose | Example |
|------|---------|---------|
| `milestone/` | Long-lived branch for a milestone | `milestone/m2-content-generation` |
| `sprint/` | Branch for a single sprint within a milestone | `sprint/s3-ai-sdk-integration` |
| `feature/` | Individual feature or task | `feature/content-generator-agent` |
| `bugfix/` | Fix for a bug in develop | `bugfix/rtl-calendar-alignment` |
| `hotfix/` | Emergency fix to production | `hotfix/meta-token-refresh` |
| `chore/` | Non-feature work (deps, config, docs) | `chore/upgrade-laravel-12.5` |
| `refactor/` | Code improvement without behavior change | `refactor/extract-ai-service` |

**Naming rules:**
- lowercase, kebab-case
- no issue numbers in branch name (they go in commit messages)
- max 50 chars
- describe the *what*, not the *who*

**Good examples:**
```
feature/seasonal-campaign-generator
feature/meta-oauth-flow
bugfix/streaming-sse-safari
chore/setup-horizon-dashboard
```

**Bad examples:**
```
ahmed-fix-stuff           ❌ who, not what
FEATURE_AUTH              ❌ wrong case
feature/ahmed-work-nov-15 ❌ meaningless
fix                       ❌ too vague
```

---

## 4. Branches for MVP Sprints — Complete List

Run this script on Day 1 to create all milestone branches (keep sprints and features created on-demand by developers):

```bash
# After cloning the repo, from main:
git checkout main
git pull origin main

# Create develop from main
git checkout -b develop
git push -u origin develop

# Create milestone branches from develop
git checkout develop

# MVP milestones (Phase 0 — Weeks 1-8)
for milestone in \
  "milestone/m1-foundation" \
  "milestone/m2-content-generation" \
  "milestone/m3-meta-scheduling" \
  "milestone/m4-seasonal-ads-analytics" \
  "milestone/m5-billing-launch" \
  "milestone/m6-tiktok-integration" \
  "milestone/m7-snap-x-integration"
do
  git checkout -b "$milestone"
  git push -u origin "$milestone"
  git checkout develop
done

echo "✅ All milestone branches created and pushed (MVP + Phase 0.5)."
```

### Sprint branches (created at the start of each sprint)

**Sprint 1 — Project Skeleton (Week 1):**
```bash
git checkout milestone/m1-foundation
git checkout -b sprint/s1-project-skeleton
git push -u origin sprint/s1-project-skeleton
```

**Sprint 2 — Workspaces & Brand (Week 2):**
```bash
git checkout milestone/m1-foundation
git checkout -b sprint/s2-workspaces-brand
git push -u origin sprint/s2-workspaces-brand
```

**Sprint 3 — AI SDK Integration (Week 3):**
```bash
git checkout milestone/m2-content-generation
git checkout -b sprint/s3-ai-sdk-integration
git push -u origin sprint/s3-ai-sdk-integration
```

**Sprint 4 — Streaming + Content UI (Week 4):**
```bash
git checkout milestone/m2-content-generation
git checkout -b sprint/s4-streaming-content-ui
git push -u origin sprint/s4-streaming-content-ui
```

**Sprint 5 — Meta OAuth + Publishing (Week 5):**
```bash
git checkout milestone/m3-meta-scheduling
git checkout -b sprint/s5-meta-oauth-publishing
git push -u origin sprint/s5-meta-oauth-publishing
```

**Sprint 6 — Calendar + Scheduling (Week 6):**
```bash
git checkout milestone/m3-meta-scheduling
git checkout -b sprint/s6-calendar-scheduling
git push -u origin sprint/s6-calendar-scheduling
```

**Sprint 7 — Seasonal + Ads + Analytics (Week 7):**
```bash
git checkout milestone/m4-seasonal-ads-analytics
git checkout -b sprint/s7-seasonal-ads-analytics
git push -u origin sprint/s7-seasonal-ads-analytics
```

**Sprint 8 — Billing + Launch (Week 8):**
```bash
git checkout milestone/m5-billing-launch
git checkout -b sprint/s8-billing-launch
git push -u origin sprint/s8-billing-launch
```

### Phase 0.5 Sprint branches (post-MVP launch, Weeks 9-12)

**Sprint 9 — TikTok OAuth + Publishing (Week 9):**
```bash
git checkout milestone/m6-tiktok-integration
git checkout -b sprint/s9-tiktok-oauth-publishing
git push -u origin sprint/s9-tiktok-oauth-publishing
```

**Sprint 10 — TikTok Ads + Analytics (Week 10):**
```bash
git checkout milestone/m6-tiktok-integration
git checkout -b sprint/s10-tiktok-ads-analytics
git push -u origin sprint/s10-tiktok-ads-analytics
```

**Sprint 11 — Snapchat Ads (Week 11):**
```bash
git checkout milestone/m7-snap-x-integration
git checkout -b sprint/s11-snapchat-ads
git push -u origin sprint/s11-snapchat-ads
```

**Sprint 12 — X Integration + Hardening (Week 12):**
```bash
git checkout milestone/m7-snap-x-integration
git checkout -b sprint/s12-x-integration-hardening
git push -u origin sprint/s12-x-integration-hardening
```

---

## 5. Developer Workflow — Step by Step

### Starting a New Feature

```bash
# 1. Make sure you're on the current sprint branch
git checkout sprint/s3-ai-sdk-integration
git pull origin sprint/s3-ai-sdk-integration

# 2. Create your feature branch from it
git checkout -b feature/content-generator-agent

# 3. Work, commit often
git add <files>
git commit -m "feat(ai): add content generator agent with dialect guides"

# 4. Push your branch
git push -u origin feature/content-generator-agent

# 5. When done, open PR targeting sprint branch
```

### Opening a Pull Request

**Target branch:** Your current sprint branch (e.g., `sprint/s3-ai-sdk-integration`)

**PR Template** (auto-loaded if `.github/pull_request_template.md` exists):

```markdown
## Summary
Brief description of what this PR does.

## Related SRS Requirements
- CG-01: User selects content type
- CG-05: System generates 3 variations

## Changes
- Added `ContentGeneratorAgent` class
- Added `GetBrandIdentity` tool
- Added dialect guide for 7 Gulf dialects
- Added unit tests with Ai::fake

## Testing
- [ ] Manual: tested with 10 real prompts across dialects
- [ ] Automated: 15 unit tests, all green
- [ ] Staging: deployed and verified

## Screenshots (if UI)
<!-- Paste screenshots here -->

## Checklist
- [ ] Code follows project style (Pint + Prettier)
- [ ] Tests added/updated
- [ ] Documentation updated (if applicable)
- [ ] No console.log / dd / dump left
- [ ] No secrets committed
```

### Merging Strategy

| Branch → Branch | Strategy | Reason |
|-----------------|----------|--------|
| `feature/*` → `sprint/*` | **Squash merge** | Keeps sprint history clean (one commit per feature) |
| `sprint/*` → `milestone/*` | **Merge commit** | Preserves sprint context |
| `milestone/*` → `develop` | **Merge commit** | Preserves milestone boundaries |
| `develop` → `main` | **Merge commit** + tag | Release boundary |
| `hotfix/*` → `main` + `develop` | **Merge commit** | Sync both ways |

**Why squash for feature → sprint?** During a sprint you'll have many WIP commits ("fix typo", "try another approach"). Squashing gives you one clean line per feature.

---

## 6. Commit Message Convention

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

**Types:**
- `feat` — new feature
- `fix` — bug fix
- `refactor` — code change that neither fixes a bug nor adds a feature
- `docs` — documentation only
- `style` — formatting, missing semicolons (no code change)
- `test` — adding tests
- `chore` — build process, dependencies, tooling
- `perf` — performance improvement

**Scopes (project-specific):**
- `auth` — authentication
- `workspace` — workspaces/tenancy
- `brand` — brand identity
- `ai` — AI agents, SDK, generation
- `meta` — Meta Graph / Marketing API (Instagram + Facebook)
- `tiktok` — TikTok Business / Content Posting / Marketing API
- `snap` — Snapchat Marketing API
- `x` — X (Twitter) API
- `social` — cross-platform social integration code
- `scheduling` — scheduler, calendar, queues
- `seasonal` — seasonal engine
- `analytics` — analytics, insights
- `billing` — Moyasar/Tap, tokens, invoices
- `ui` — design system, layout
- `api` — REST API changes
- `db` — migrations, schema
- `infra` — deployment, DevOps

**Examples:**

```
feat(ai): add content generator agent with 7 Gulf dialects
fix(ui): correct RTL alignment in calendar week view
refactor(meta): extract graph client into separate service
feat(tiktok): implement OAuth 2.0 flow with Business API
feat(snap): add Snap Ads campaign creation via Marketing API
feat(x): add tweet publishing with 280-char validation
feat(social): unified cross-platform publishing action
docs(srs): update content generation requirements
test(scheduling): add edge cases for DST transitions
chore(deps): upgrade laravel/ai to 1.2.0
```

**Body example (for complex changes):**

```
feat(seasonal): add campaign generator for Saudi National Day

- Implements SE-04 from SRS
- Supports 7, 10, or 14 post variations
- Uses SeasonalCampaignAgent with industry context
- Queue-based (avg generation ~45s)

Closes #42
```

---

## 7. Code Review Rules

### Reviewers

- **Backend PRs** → reviewed by Ahmed Q (default) + Jaber OR Ghassan (cross-review)
- **Frontend PRs** → reviewed by Ahmed Q (design/architecture) + code quality auto-check
- **AI-related PRs** → Ahmed Q mandatory reviewer
- **Infra/DevOps PRs** → Ahmed Q only
- **Critical paths** (auth, billing, publishing) → 2 reviewers required

### Review SLA

- Target: review within 4 hours during working hours
- Blocker: no PR sits > 24 hours without review
- Ahmed Q does final review before merge to `milestone/*`

### What to check

1. **Correctness:** Does it solve the problem? Matches SRS requirement?
2. **Tests:** Are there tests? Do they actually verify behavior?
3. **Security:** Any injection risks? Missing auth checks? Secrets?
4. **Performance:** N+1 queries? Missing indexes? Heavy sync calls?
5. **RTL:** For UI, verified in Arabic mode?
6. **Naming & style:** Clear names? Follows conventions?
7. **Scope:** Does the PR do ONE thing?

---

## 8. Handling Common Scenarios

### Scenario A: Sprint ends, merging back

```bash
# At end of sprint 3 (AI SDK Integration)
git checkout milestone/m2-content-generation
git pull origin milestone/m2-content-generation

git merge --no-ff sprint/s3-ai-sdk-integration
git push origin milestone/m2-content-generation

# Open PR: milestone/m2-content-generation → develop (at milestone end)
```

### Scenario B: Milestone complete, merging to develop

```bash
# Create PR from milestone branch to develop
gh pr create \
  --base develop \
  --head milestone/m2-content-generation \
  --title "Milestone 2: Content Generation Complete" \
  --body "Closes M2 per implementation plan. See sprint PRs for detailed changes."
```

### Scenario C: Hotfix to production

```bash
# Branch from main (not develop)
git checkout main
git pull origin main
git checkout -b hotfix/meta-token-refresh-bug

# Fix, commit, push
git commit -m "fix(meta): prevent token refresh loop on 401"
git push -u origin hotfix/meta-token-refresh-bug

# PR to main AND develop (both!)
gh pr create --base main --title "Hotfix: Meta token refresh bug"
gh pr create --base develop --title "Hotfix: Meta token refresh bug (sync)"
```

### Scenario D: Your feature depends on a colleague's unmerged feature

```bash
# Option 1: Branch from their branch (risky — their PR may change)
git checkout feature/their-feature
git checkout -b feature/my-dependent-feature

# Option 2 (better): Wait for their PR to merge, rebase on sprint branch
git checkout sprint/s3-ai-sdk-integration
git pull origin sprint/s3-ai-sdk-integration
git checkout -b feature/my-dependent-feature
```

### Scenario E: Conflict during rebase

```bash
# You're on your feature branch, sprint got updates
git fetch origin
git rebase origin/sprint/s3-ai-sdk-integration

# Conflicts appear
# Resolve in editor, then:
git add <resolved-files>
git rebase --continue

# Force push (only to your own feature branch!)
git push --force-with-lease origin feature/your-branch
```

**Never force push to shared branches:** `main`, `develop`, `milestone/*`, `sprint/*`.

---

## 9. Tags & Releases

### Versioning: Semantic (`v<MAJOR>.<MINOR>.<PATCH>`)

- `v0.1.0` — MVP alpha (internal)
- `v0.2.0` — Beta launch (50 users)
- `v1.0.0` — Public launch
- `v1.0.1` — Patch release
- `v1.1.0` — Phase 1 feature release

### Tagging on merge to `main`

```bash
git checkout main
git pull origin main

git tag -a v0.1.0 -m "MVP Alpha — Milestones 1-2 complete"
git push origin v0.1.0
```

### Release notes

Auto-generated from commit messages via GitHub Releases, but curated manually before publishing. Keep a `CHANGELOG.md` synced.

---

## 10. CI/CD Integration

### GitHub Actions triggered on:

| Trigger | Action |
|---------|--------|
| Push to `feature/*` | Lint (Pint, ESLint) + Run unit tests |
| PR to `sprint/*` | Full test suite + Code coverage report |
| PR merged to `develop` | Deploy to staging automatically |
| PR merged to `main` | Deploy to production + create GitHub release draft |
| Tag pushed (`v*`) | Notify team via Slack |

### Required checks before merge

- ✅ `lint` (Pint + ESLint)
- ✅ `test` (PHPUnit + Vitest)
- ✅ `build` (Vite build succeeds)
- ✅ `coverage` ≥ 70% for modified files
- ✅ `security` (composer audit + npm audit)

---

## 11. Quick Reference Card

```
# Start new feature
git checkout sprint/s<N>-<name> && git pull
git checkout -b feature/my-thing

# Daily work
git add -p                              # stage interactively
git commit -m "feat(scope): message"
git push -u origin feature/my-thing

# Sync with sprint (daily if sprint is active)
git fetch origin
git rebase origin/sprint/s<N>-<name>

# Open PR (GitHub CLI)
gh pr create --base sprint/s<N>-<name> --fill

# After PR merged, clean up
git checkout sprint/s<N>-<name>
git pull origin sprint/s<N>-<name>
git branch -d feature/my-thing
git push origin --delete feature/my-thing  # delete remote
```

---

## 12. Repository Setup Commands (Ahmed Q runs once)

```bash
# 1. Create GitHub repo (via web or gh CLI)
gh repo create brightgaza/sada --private --description "Sada — Arabic marketing AI SaaS"

# 2. Clone locally
git clone git@github.com:brightgaza/sada.git
cd sada

# 3. Initialize Laravel project
composer create-project laravel/laravel . "^12.0"

# 4. Install Inertia + Vue starter
composer require laravel/inertia-vue-starter

# 5. Create initial commit
git add .
git commit -m "chore: initial Laravel 12 + Inertia + Vue setup"
git push -u origin main

# 6. Configure branch protection (via GitHub web UI or gh CLI):
gh api -X PUT repos/brightgaza/sada/branches/main/protection \
  -f required_status_checks[strict]=true \
  -f required_status_checks[contexts][]=lint \
  -f required_status_checks[contexts][]=test \
  -f enforce_admins=false \
  -f required_pull_request_reviews[required_approving_review_count]=1 \
  -f restrictions=null

# 7. Create develop and milestone branches (see §4)

# 8. Add templates:
mkdir -p .github
cat > .github/pull_request_template.md << 'EOF'
[use the template from §5]
EOF

# 9. Add CODEOWNERS
cat > .github/CODEOWNERS << 'EOF'
# Global owner
* @ahmedqaddoura

# AI / Architecture
/app/Ai/ @ahmedqaddoura
/config/ai.php @ahmedqaddoura

# Frontend
/resources/js/ @abdqaddoura @ahmedqaddoura

# Backend features
/app/Http/Controllers/Billing/ @ahmedjaber @ahmedqaddoura

# Social platform integrations — all CTO-reviewed
/app/Services/Meta/ @ahmedqaddoura
/app/Services/TikTok/ @ahmedqaddoura
/app/Services/Snapchat/ @ahmedqaddoura
/app/Services/X/ @ahmedqaddoura
/app/Services/Social/ @ahmedqaddoura
EOF

git add .github/
git commit -m "chore(infra): add PR template and CODEOWNERS"
git push origin main
```

---

## 13. Don'ts — Common Pitfalls

- ❌ **Don't** commit `.env`, `node_modules`, `vendor/`, `storage/logs/*`
- ❌ **Don't** commit generated files like `public/build/`
- ❌ **Don't** use `git push --force` on shared branches — use `--force-with-lease` on your own branches only
- ❌ **Don't** merge your own PRs without review
- ❌ **Don't** leave `console.log`, `dd()`, `dump()`, or debug statements
- ❌ **Don't** commit commented-out code — delete it, git remembers
- ❌ **Don't** work on `main` or `develop` directly
- ❌ **Don't** create a PR with > 500 lines changed without breaking it up
- ❌ **Don't** commit secrets, API keys, or passwords — if you do, rotate them immediately

---

## 14. Emergency Procedures

### I pushed secrets to a public branch

```bash
# 1. IMMEDIATELY rotate the secret (revoke key, generate new one)
# 2. Remove from history using BFG Repo-Cleaner or git-filter-repo
#    (contact Ahmed Q before doing this)
# 3. Force push (Ahmed Q must approve) — notify all team
# 4. All team members must rebase their branches
```

### I merged to wrong branch

```bash
# If not pushed: git reset --hard HEAD~1
# If pushed: revert via GitHub UI or:
git revert -m 1 <merge-commit-hash>
git push origin <branch>
```

### Production is broken

```bash
# 1. Revert the bad merge on main
git checkout main
git revert -m 1 <merge-commit>
git push origin main
# CI will redeploy the previous good version

# 2. Create hotfix branch to properly fix
git checkout -b hotfix/fix-broken-thing
# ... fix, PR, merge
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | April 2026 | Ahmed Qaddoura | Initial workflow for MVP |

**Share this with:** Ahmed Jaber, Ghassan Ahmed, Abd Qaddoura — required reading before Week 1.
---
title: Social Platform Integrations — Developer Quick Reference

---

# Social Platform Integrations — Developer Quick Reference
## صدى (Sada) — Platform API Cheat Sheet

**Document ID:** SADA-INT-001
**Version:** 1.0
**Audience:** Backend developers implementing platform integrations
**Use with:** SRS §8 (full spec), Implementation Plan M3/M6/M7

---

## At-a-Glance Matrix

| Platform | MVP Phase | OAuth | API Base | Monthly Cost | App Review | Status |
|----------|-----------|-------|----------|--------------|------------|--------|
| Instagram | Phase 0 (MVP) | Meta OAuth | `graph.facebook.com/v19.0` | $0 | ✅ Required | Week 1 |
| Facebook | Phase 0 (MVP) | Meta OAuth | `graph.facebook.com/v19.0` | $0 | ✅ Required | Week 1 |
| TikTok | Phase 0.5 | TikTok OAuth | `open.tiktokapis.com/v2` | $0 | ✅ Required (strict) | Week 9 |
| Snapchat | Phase 0.5 | Snap OAuth | `adsapi.snapchat.com/v1` | $0 | ✅ Required | Week 11 |
| X (Twitter) | Phase 0.5 | OAuth 2.0 PKCE | `api.x.com/2` | **$200 Basic** | ⚠️ Paid + Ads review | Week 12 |

---

## 1. Meta (Instagram + Facebook)

### Setup
```env
META_APP_ID=
META_APP_SECRET=
META_REDIRECT_URI=https://sada.sa/social/callback/meta
META_GRAPH_VERSION=v19.0
```

### Required Scopes (Permissions)
```
pages_show_list
pages_read_engagement
pages_manage_posts
instagram_basic
instagram_content_publish
ads_management            # requires App Review
business_management       # requires App Review
read_insights
```

### Key Endpoints
```
# OAuth
POST https://graph.facebook.com/v19.0/oauth/access_token

# List user's pages
GET /me/accounts

# Publish FB post
POST /{page-id}/feed

# Publish IG post (2-step: create container, then publish)
POST /{ig-user-id}/media
POST /{ig-user-id}/media_publish

# Create Ad Campaign
POST /act_{ad-account-id}/campaigns

# Insights
GET /{post-id}/insights
```

### Content Constraints
| Type | Character Limit | Media Required |
|------|----------------|----------------|
| FB Post | 63,206 | Optional |
| IG Post | 2,200 (caption) | ✅ Image/Video |
| IG Reel | 2,200 (caption) | ✅ Video 9:16, max 90s |
| IG Story | 2,200 (caption) | ✅ Image/Video 9:16, max 60s |

### Rate Limits
- Graph API: 200 calls/hour/user
- Marketing API: 100 calls/hour/ad account
- Instagram publishing: 50 posts/day per account

### Gotchas
- IG publishing is **2-step async** — creation container, then publish (wait for container ready)
- Page tokens expire in 60 days — implement refresh job
- Business Account required for IG publishing (Creator account won't work via API)
- Webhook subscriptions required for real-time engagement data

---

## 2. TikTok

### Setup
```env
TIKTOK_CLIENT_KEY=
TIKTOK_CLIENT_SECRET=
TIKTOK_REDIRECT_URI=https://sada.sa/social/callback/tiktok
```

### Required Scopes
```
user.info.basic
video.publish
video.upload
video.list
biz.creator.info
biz.creator.insights
ads.manage              # requires separate Marketing API approval
```

### Key Endpoints
```
# OAuth
POST https://open.tiktokapis.com/v2/oauth/token/

# User info
GET /v2/user/info/

# Upload video (chunk-based for large files)
POST /v2/post/publish/video/init/
POST /v2/post/publish/video/upload/   # upload to Azure URL returned
POST /v2/post/publish/video/status/   # poll until ready

# Publish video
POST /v2/post/publish/
```

### Content Constraints
- **Video only** — no image or text-only posts
- Duration: 3-180 seconds (standard), 10min for eligible accounts
- Resolution: min 540×960, 9:16 preferred
- File size: max 287.6 MB
- Format: MP4, MOV, WEBM, AVI
- Caption: 2,200 chars max
- Hashtags: 30 max

### Rate Limits
- Content Posting: **6 posts/day per account** (hard limit)
- API calls: 1,000/day base tier
- Query videos: 100/day

### Gotchas
- Upload flow is **3-step async**: init → upload → status poll → publish
- Video must be publicly accessible URL OR direct file upload (chunked)
- Content moderation can reject posts after publishing — webhook needed to track
- Requires TikTok for Business account (not personal) for API posting
- **MENA content moderation is strict** — avoid politically sensitive, religious controversy

### Direct Post vs Draft
TikTok offers two modes:
1. `DIRECT_POST` — publishes immediately
2. `UPLOAD` — saves as draft, user publishes from TikTok app

Sada uses `DIRECT_POST` for scheduled publishing.

---

## 3. Snapchat

### Setup
```env
SNAPCHAT_CLIENT_ID=
SNAPCHAT_CLIENT_SECRET=
SNAPCHAT_REDIRECT_URI=https://sada.sa/social/callback/snapchat
```

### Required Scopes
```
snapchat-marketing-api
snapchat-profile-api
snapchat-account-read
snapchat-account-write
```

### Key Endpoints
```
# OAuth
POST https://accounts.snapchat.com/login/oauth2/access_token

# Ad Accounts
GET /v1/me/adaccounts

# Create Campaign
POST /v1/adaccounts/{ad-account-id}/campaigns

# Create Ad Squad (Ad Set)
POST /v1/adsquads

# Create Creative (upload media first)
POST /v1/adaccounts/{ad-account-id}/creatives

# Create Ad
POST /v1/adaccounts/{ad-account-id}/ads
```

### Content Constraints (Ad Formats)

| Type | Dimensions | Duration | File Size |
|------|-----------|----------|-----------|
| Single Image Ad | 1080×1920 (9:16) | — | 32 MB max |
| Single Video Ad | 1080×1920 (9:16) | 3-180s | 1 GB max |
| Collection Ad | 1080×1920 | — | — |
| Story Ad | 1080×1920 | Up to 180s total | — |

**Text constraints:**
- Brand Name: 25 chars
- Headline: 34 chars
- CTA: predefined list (Shop Now, Learn More, Sign Up, Install Now, Watch, etc.)

### Rate Limits
- 1,000 requests/minute default
- Campaigns: unlimited creation within budget

### 🚨 Critical Limitation
**Snapchat API does NOT support organic post publishing.** Users cannot schedule Snaps or Stories through the API. Only paid Snap Ads are supported.

This must be communicated clearly in UX:
- In onboarding: "Snapchat للإعلانات فقط"
- In Generate Content: Snap option shows only "Ad" type
- In Calendar: no organic Snap posts appear

### Gotchas
- Access tokens expire every 30 minutes — aggressive refresh required
- Media upload requires pre-signed URL flow
- Business Manager verification can take 1-2 weeks
- Gulf region has high Snap engagement → prioritize reliable ad delivery

---

## 4. X (Twitter)

### Setup
```env
X_CLIENT_ID=
X_CLIENT_SECRET=
X_BEARER_TOKEN=
X_REDIRECT_URI=https://sada.sa/social/callback/x
X_API_TIER=basic                # basic ($200/mo), pro ($5k/mo), enterprise
```

### Required Scopes (OAuth 2.0)
```
tweet.read
tweet.write
users.read
offline.access              # for refresh tokens
like.read
like.write
ads_read                    # requires Ads API access
ads_write                   # requires Ads API access
```

### Key Endpoints
```
# OAuth 2.0 with PKCE (different from OAuth 1.0a of old Twitter)
POST https://api.x.com/2/oauth2/token

# Post a tweet
POST /2/tweets

# Post a thread (series of replies)
POST /2/tweets  (with in_reply_to_tweet_id)

# Upload media (still uses v1.1 API!)
POST https://upload.twitter.com/1.1/media/upload.json

# User info
GET /2/users/me

# Tweet metrics
GET /2/tweets/:id?tweet.fields=public_metrics,non_public_metrics

# Ads API (requires Pro+ tier or partnership)
POST /ads/api/12/accounts/{account-id}/campaigns
```

### Content Constraints
| Feature | Limit |
|---------|-------|
| Tweet | 280 chars (Premium users: 25,000) |
| Thread | Up to 25 tweets per thread |
| Media per tweet | 4 images OR 1 GIF OR 1 video |
| Video | Max 2min 20sec, 512 MB |
| Poll | Max 25 chars per option, 4 options |

### Rate Limits (Basic Tier $200/mo)
- POST /2/tweets: 100 tweets / 15 min per user
- GET endpoints: 10,000 requests / 15 min (app-level)
- Total tweet reads: 10,000/month
- Total tweet writes: limited per tier

### 🚨 Cost Consideration
- **Free tier:** unusable for SaaS (1,500 tweets read/month)
- **Basic tier:** $200/month — minimum viable for Sada
- **Pro tier:** $5,000/month — needed only if > 1M tweets/month
- **Ads API:** requires separate approval + may require partnership status

**Decision for Sada:** Start with Basic tier. Pass costs to Agency-tier pricing.

### Gotchas
- OAuth 2.0 with PKCE — more complex than OAuth 2.0 standard flow
- Media upload still uses old v1.1 API endpoint (mixed API versions)
- Rate limits are aggressive — batch where possible
- Text-only tweets are fast; media uploads add significant latency
- Arabic text counts same as English (unlike some platforms)
- Thread publishing is sequential (each tweet replies to previous) — cannot batch

---

## 5. Unified Architecture Patterns

### Service Layer Structure
```php
app/Services/
├── Social/
│   ├── SocialPlatformInterface.php    // Contract all platforms implement
│   ├── SocialAccountManager.php        // Token storage, refresh
│   └── CrossPostingService.php         // Content adaptation per platform
├── Meta/
│   ├── MetaGraphClient.php
│   ├── MetaAdsClient.php
│   └── MetaWebhookHandler.php
├── TikTok/
│   ├── TikTokApiClient.php
│   ├── TikTokVideoUploader.php
│   └── TikTokAdsClient.php
├── Snapchat/
│   ├── SnapchatApiClient.php
│   └── SnapchatAdsClient.php
└── X/
    ├── XApiClient.php
    └── XAdsClient.php
```

### Common Interface
```php
<?php

namespace App\Services\Social;

interface SocialPlatformInterface
{
    public function authenticate(string $code, string $codeVerifier = null): AuthResult;
    public function refreshToken(SocialAccount $account): void;
    public function revokeAccess(SocialAccount $account): bool;

    public function publishPost(Post $post, SocialAccount $account): PublishResult;
    public function schedulePost(Post $post, Carbon $scheduledFor): ScheduleResult;

    public function getInsights(string $externalPostId): InsightsResult;
    public function getAccountHealth(SocialAccount $account): HealthStatus;

    // Ads-specific (nullable — Snap can't do organic, etc.)
    public function createAdCampaign(Campaign $campaign): ?CampaignResult;
    public function canPublishOrganic(): bool;  // false for Snapchat
    public function requiresMedia(): bool;       // true for TikTok, Snap
    public function getCharacterLimit(string $contentType): int;
}
```

### Content Type Mapping
```php
enum SocialPlatform: string
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';
    case Snapchat = 'snapchat';
    case X = 'x';

    public function supportsOrganic(): bool
    {
        return match($this) {
            self::Snapchat => false,
            default => true,
        };
    }

    public function requiresVideo(): bool
    {
        return $this === self::TikTok;
    }

    public function characterLimit(): int
    {
        return match($this) {
            self::X => 280,
            self::Snapchat => 250,
            self::Instagram, self::TikTok => 2200,
            self::Facebook => 63206,
        };
    }
}
```

---

## 6. App Review Submission Checklist

### Meta (Submit Week 0 — before development)
- [ ] Meta Developer account verified
- [ ] Business Manager set up
- [ ] Privacy Policy URL live
- [ ] Terms of Service URL live
- [ ] Data Deletion Callback implemented
- [ ] App icon, descriptions, screenshots ready
- [ ] Test accounts prepared
- [ ] Demo video showing each permission use case
- [ ] Detailed use case explanation per scope

### TikTok (Submit Week 5)
- [ ] TikTok for Business account
- [ ] Signed Commerce Policy agreement
- [ ] Demo video (2-3 min) showing full flow
- [ ] Use case justification document
- [ ] Website live at sada.sa
- [ ] Content moderation process documented

### Snapchat (Submit Week 6)
- [ ] Snap Business Manager account
- [ ] Business verification (legal entity docs)
- [ ] Marketing API application form
- [ ] Technical contact info
- [ ] Expected volume estimates

### X (Activate Week 7)
- [ ] Subscribe to Basic tier ($200/mo)
- [ ] Developer Portal application
- [ ] Use case description
- [ ] For Ads API: separate application + partnership proof

---

## 7. Testing Strategy Per Platform

| Platform | Test Account Required | Sandbox Available | Webhook Testing |
|----------|----------------------|-------------------|-----------------|
| Meta | ✅ Test users | ✅ Graph API Explorer | ✅ ngrok + Meta webhooks |
| TikTok | ✅ Sandbox account | ✅ Sandbox mode | Limited |
| Snapchat | ✅ Sandbox ad account | ✅ Sandbox Mode | Via ad delivery |
| X | Regular account | ❌ No real sandbox | ✅ Via filtered stream |

---

## 8. Incident Response per Platform

### If Meta breaks
- Check https://developers.facebook.com/status/
- Use failover logic: scheduled posts retry after 5, 15, 60 min
- Notify users via email if > 1 hour outage

### If TikTok breaks
- Check TikTok Developer status
- Queue posts for retry (TikTok outages can be long)

### If Snapchat breaks
- Ads continue running (no real-time dependency)
- Campaign creation may fail — queue and retry

### If X breaks
- Common — X has frequent issues since rebrand
- Fall back to scheduled retry
- Prominent UI warning during outages

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | April 2026 | Ahmed Qaddoura | Initial platform integrations reference |
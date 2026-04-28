export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    token_balance: number;
    avatar_url: string | null;
}

export interface Workspace {
    id: number;
    user_id: number;
    name: string;
    business_type: string | null;
    countries: string[];
    default_dialect: Dialect;
    logo_path: string | null;
    archived_at: string | null;
    created_at: string;
    updated_at: string;
}

export type Dialect =
    | 'fos'
    | 'sa'
    | 'ae'
    | 'kw'
    | 'qa'
    | 'bh'
    | 'om';

export type Platform =
    | 'instagram'
    | 'facebook'
    | 'tiktok'
    | 'snapchat'
    | 'x';

export type PostStatus =
    | 'draft'
    | 'scheduled'
    | 'published'
    | 'failed';

export interface AppNotification {
    id: string;
    data: {
        type: string;
        title: string;
        body: string;
        link?: string;
        post_id?: number;
        platform?: string;
    };
    read_at: string | null;
    created_at: string;
}

export interface SiteSettings {
    site_name?: string
    site_slogan?: string
    site_description?: string
    registration_open?: boolean
    logo_path?: string | null
    logo_dark_path?: string | null
    favicon_path?: string | null
    og_image_path?: string | null
    contact_email?: string
    support_email?: string
    phone?: string | null
    whatsapp?: string | null
    address?: string | null
    meta_title?: string
    meta_description?: string
    meta_keywords?: string
    social_facebook?: string | null
    social_instagram?: string | null
    social_twitter?: string | null
    social_tiktok?: string | null
    social_snapchat?: string | null
    social_linkedin?: string | null
    social_youtube?: string | null
    [key: string]: unknown
}

export interface PageProps extends Record<string, unknown> {
    auth: {
        user: User | null;
    };
    currentWorkspace: Workspace | null;
    workspaces: Workspace[];
    flash: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    notifications: {
        unread_count: number;
        recent: AppNotification[];
    } | null;
    impersonating: { active: boolean; stop_url: string } | null;
    siteSettings: SiteSettings;
}

// ADS-01 → ADS-11
export type CampaignStatus =
    | 'draft'
    | 'pending'
    | 'active'
    | 'paused'
    | 'completed'
    | 'rejected';

export type CampaignObjective =
    | 'awareness'
    | 'traffic'
    | 'engagement'
    | 'conversions'
    | 'app_installs'
    | 'video_views';

export interface Campaign {
    id: number;
    workspace_id: number;
    social_account_id: number | null;
    post_id: number | null;
    name: string;
    objective: CampaignObjective;
    platform: 'instagram' | 'facebook';
    status: CampaignStatus;
    target_countries: string[];
    target_age_min: number;
    target_age_max: number;
    target_gender: 'all' | 'male' | 'female';
    target_interests: string[];
    budget_type: 'daily' | 'lifetime';
    budget_amount: string;
    budget_currency: string;
    starts_at: string;
    ends_at: string;
    ad_copy: string | null;
    ad_headline: string | null;
    ad_description: string | null;
    meta_campaign_id: string | null;
    meta_adset_id: string | null;
    meta_ad_id: string | null;
    insights: {
        spend?: number;
        reach?: number;
        impressions?: number;
        clicks?: number;
        ctr?: number;
        cpc?: number;
        roas?: number;
    } | null;
    insights_synced_at: string | null;
    social_account?: {
        id: number;
        provider: string;
        account_name: string;
    };
    post?: {
        id: number;
        content: string;
        platform: string;
    };
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    token_balance: number;
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

export interface PageProps extends Record<string, unknown> {
    auth: {
        user: User | null;
    };
    currentWorkspace: Workspace | null;
    workspaces: Workspace[];
    flash: {
        success?: string;
        error?: string;
    };
}

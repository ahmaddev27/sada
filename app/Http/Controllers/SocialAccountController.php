<?php

// CON-01→CON-10

namespace App\Http\Controllers;

use App\Actions\Social\ConnectSocialAccountAction;
use App\Actions\Social\DisconnectSocialAccountAction;
use App\Actions\Social\RefreshSocialTokenAction;
use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SocialAccountController extends Controller
{
    // CON-10: Connected accounts page grouped by workspace
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $accounts = $workspace
            ? SocialAccount::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->orderBy('provider')
                ->orderBy('account_name')
                ->get()
                ->map(fn (SocialAccount $a) => [
                    'id'                  => $a->id,
                    'provider'            => $a->provider,
                    'provider_label'      => $a->providerLabel(),
                    'provider_account_id' => $a->provider_account_id,
                    'account_name'        => $a->account_name,
                    'account_picture_url' => $a->account_picture_url,
                    'status'              => $a->status,
                    'status_label'        => $a->statusLabel(),
                    'token_expires_at'    => $a->token_expires_at?->toDateTimeString(),
                    'needs_refresh'       => $a->needsRefresh(),
                    'scopes'              => $a->scopes,
                ])
            : collect();

        return Inertia::render('Social/Connections', [
            'accounts' => $accounts,
        ]);
    }

    // CON-01, CON-02: initiate Meta OAuth flow
    public function redirect(Request $request, MetaOAuthService $meta): RedirectResponse
    {
        $provider = $request->route('provider');

        if (! in_array($provider, ['meta', 'instagram', 'facebook'], true)) {
            abort(404);
        }

        // CSRF state includes current workspace ID
        $state = base64_encode((string) json_encode([
            'csrf'         => Str::random(40),
            'workspace_id' => session('current_workspace_id'),
        ]));

        session(['meta_oauth_state' => $state]);

        return redirect($meta->redirectUrl($state));
    }

    // CON-01, CON-02: handle OAuth callback from Meta
    public function callback(
        Request $request,
        MetaOAuthService $meta,
        ConnectSocialAccountAction $connect,
    ): RedirectResponse {
        // Validate CSRF state
        $state = $request->query('state', '');
        if ($state !== session('meta_oauth_state')) {
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'فشل التحقق من الهوية. حاول مرة أخرى.']);
        }
        session()->forget('meta_oauth_state');

        if ($request->has('error')) {
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'تم إلغاء ربط الحساب.']);
        }

        $workspace = $request->attributes->get('current_workspace');
        if (! $workspace) {
            return redirect()->route('workspace.index')
                ->with('flash', ['error' => 'اختر مساحة عمل أولاً.']);
        }

        // Exchange code for long-lived token
        $token    = $meta->exchangeCode($request->query('code', ''));
        $longToken = $token['access_token'];
        $expiresIn = $token['expires_in'];

        // Connect all Instagram Business accounts
        $igAccounts = $meta->instagramAccounts($longToken);
        foreach ($igAccounts as $ig) {
            $connect->execute($workspace, [
                'provider'            => 'instagram',
                'provider_account_id' => $ig['id'],
                'account_name'        => $ig['name'],
                'account_picture_url' => $ig['picture'] ?? null,
                'access_token'        => $longToken,
                'refresh_token'       => null,
                'expires_in'          => $expiresIn,
                'scopes'              => ['instagram_basic', 'instagram_content_publish'],
                'metadata'            => ['page_id' => $ig['page_id']],
            ]);
        }

        // Connect all Facebook Pages
        $pages = $meta->facebookPages($longToken);
        foreach ($pages as $page) {
            $connect->execute($workspace, [
                'provider'            => 'facebook',
                'provider_account_id' => $page['id'],
                'account_name'        => $page['name'],
                'account_picture_url' => $page['picture'] ?? null,
                'access_token'        => $page['access_token'],
                'refresh_token'       => null,
                'expires_in'          => $expiresIn,
                'scopes'              => ['pages_manage_posts', 'pages_read_engagement'],
                'metadata'            => [],
            ]);
        }

        $count = count($igAccounts) + count($pages);

        return redirect()->route('social.index')
            ->with('flash', ['success' => "تم ربط {$count} حساب بنجاح."]);
    }

    // CON-08: disconnect a social account
    public function disconnect(
        SocialAccount $account,
        DisconnectSocialAccountAction $action,
    ): RedirectResponse {
        Gate::authorize('delete', $account);

        $action->execute($account);

        return redirect()->route('social.index')
            ->with('flash', ['success' => "تم فصل حساب {$account->account_name} بنجاح."]);
    }

    // CON-07: manually trigger token refresh
    public function refresh(
        SocialAccount $account,
        RefreshSocialTokenAction $action,
    ): RedirectResponse {
        Gate::authorize('update', $account);

        try {
            $action->execute($account);
            $msg = "تم تجديد رمز {$account->account_name} بنجاح.";
            return redirect()->route('social.index')->with('flash', ['success' => $msg]);
        } catch (\Throwable $e) {
            $account->markExpired();
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'فشل تجديد الرمز. يرجى إعادة الربط.']);
        }
    }
}

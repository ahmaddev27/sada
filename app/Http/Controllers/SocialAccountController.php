<?php

// CON-01→CON-10

namespace App\Http\Controllers;

use App\Actions\Social\ConnectSocialAccountAction;
use App\Actions\Social\DisconnectSocialAccountAction;
use App\Actions\Social\RefreshSocialTokenAction;
use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;
use App\Services\TikTok\TikTokOAuthService;
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

    // CON-01, CON-02, CON-11: initiate OAuth flow for Meta or TikTok
    public function redirect(
        Request $request,
        MetaOAuthService $meta,
        TikTokOAuthService $tiktok,
    ): RedirectResponse {
        $provider = $request->route('provider');

        $state = base64_encode((string) json_encode([
            'csrf'         => Str::random(40),
            'workspace_id' => session('current_workspace_id'),
        ]));

        if (in_array($provider, ['meta', 'instagram', 'facebook'], true)) {
            session(['meta_oauth_state' => $state]);
            return redirect($meta->redirectUrl($state));
        }

        if ($provider === 'tiktok') {
            session(['tiktok_oauth_state' => $state]);
            return redirect($tiktok->redirectUrl($state));
        }

        abort(404);
    }

    // CON-01, CON-02, CON-11: handle OAuth callback from Meta or TikTok
    public function callback(
        Request $request,
        MetaOAuthService $meta,
        TikTokOAuthService $tiktok,
        ConnectSocialAccountAction $connect,
    ): RedirectResponse {
        $provider = $request->route('provider');

        if ($request->has('error')) {
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'تم إلغاء ربط الحساب.']);
        }

        $workspace = $request->attributes->get('current_workspace');
        if (! $workspace) {
            return redirect()->route('workspace.index')
                ->with('flash', ['error' => 'اختر مساحة عمل أولاً.']);
        }

        if ($provider === 'tiktok') {
            return $this->handleTikTokCallback($request, $tiktok, $connect, $workspace);
        }

        return $this->handleMetaCallback($request, $meta, $connect, $workspace);
    }

    private function handleMetaCallback(
        Request $request,
        MetaOAuthService $meta,
        ConnectSocialAccountAction $connect,
        \App\Models\Workspace $workspace,
    ): RedirectResponse {
        $state = $request->query('state', '');
        if ($state !== session('meta_oauth_state')) {
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'فشل التحقق من الهوية. حاول مرة أخرى.']);
        }
        session()->forget('meta_oauth_state');

        $token     = $meta->exchangeCode($request->query('code', ''));
        $longToken = $token['access_token'];
        $expiresIn = $token['expires_in'];

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

        if ($request->session()->get('onboarding_step') === 2) {
            $request->session()->forget('onboarding_step');
            return redirect()->route('dashboard')
                ->with('flash.success', "تم ربط {$count} حساب بنجاح. مرحباً بك في صدى!");
        }

        return redirect()->route('social.index')
            ->with('flash.success', "تم ربط {$count} حساب بنجاح.");
    }

    private function handleTikTokCallback(
        Request $request,
        TikTokOAuthService $tiktok,
        ConnectSocialAccountAction $connect,
        \App\Models\Workspace $workspace,
    ): RedirectResponse {
        $state = $request->query('state', '');
        if ($state !== session('tiktok_oauth_state')) {
            return redirect()->route('social.index')
                ->with('flash', ['error' => 'فشل التحقق من الهوية. حاول مرة أخرى.']);
        }
        session()->forget('tiktok_oauth_state');

        $token  = $tiktok->exchangeCode($request->query('code', ''));
        $user   = $tiktok->userInfo($token['access_token']);

        $connect->execute($workspace, [
            'provider'            => 'tiktok',
            'provider_account_id' => $token['open_id'],
            'account_name'        => $user['display_name'],
            'account_picture_url' => $user['avatar_url'] ?? null,
            'access_token'        => $token['access_token'],
            'refresh_token'       => $token['refresh_token'],
            'expires_in'          => $token['expires_in'],
            'scopes'              => ['user.info.basic', 'video.upload', 'video.publish'],
            'metadata'            => ['open_id' => $token['open_id']],
        ]);

        return redirect()->route('social.index')
            ->with('flash.success', "تم ربط حساب تيك توك {$user['display_name']} بنجاح.");
    }

    // CON-08: disconnect a social account
    public function disconnect(
        SocialAccount $account,
        DisconnectSocialAccountAction $action,
    ): RedirectResponse {
        Gate::authorize('delete', $account);

        $action->execute($account);

        return redirect()->route('social.index')
            ->with('flash.success', "تم فصل حساب {$account->account_name} بنجاح.");
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
            return redirect()->route('social.index')->with('flash.success', $msg);
        } catch (\Throwable $e) {
            $account->markExpired();
            return redirect()->route('social.index')
                ->with('flash.error', 'فشل تجديد الرمز. يرجى إعادة الربط.');
        }
    }
}

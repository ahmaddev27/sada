<?php

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $workspace = $request->attributes->get('current_workspace');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'            => $request->user()->id,
                    'name'          => $request->user()->name,
                    'email'         => $request->user()->email,
                    'token_balance' => $request->user()->token_balance,
                    'avatar_url'    => $request->user()->avatar_path
                        ? \Illuminate\Support\Facades\Storage::url($request->user()->avatar_path)
                        : null,
                ] : null,
            ],

            'currentWorkspace' => $workspace ? [
                'id'              => $workspace->id,
                'name'            => $workspace->name,
                'business_type'   => $workspace->business_type,
                'countries'       => $workspace->countries,
                'default_dialect' => $workspace->default_dialect,
                'logo_path'       => $workspace->logo_path,
            ] : null,

            'workspaces' => $request->user()
                ? $request->user()->activeWorkspaces()
                    ->select(['id', 'name', 'logo_path'])
                    ->get()
                    ->toArray()
                : [],

            'flash' => [
                'success' => $request->session()->get('flash.success'),
                'error'   => $request->session()->get('flash.error'),
                'status'  => $request->session()->get('status'),
            ],

            'impersonating' => $request->session()->has('impersonating_admin_id')
                ? ['active' => true, 'stop_url' => '/admin/impersonate/stop']
                : null,

            'siteSettings' => app(SiteSettingsService::class)->public(),

            'notifications' => $request->user() ? [
                'unread_count' => $request->user()->unreadNotifications()->count(),
                'recent'       => $request->user()->notifications()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($n) => [
                        'id'      => $n->id,
                        'data'    => $n->data,
                        'read_at' => $n->read_at?->toIso8601String(),
                        'created_at' => $n->created_at->toIso8601String(),
                    ])
                    ->toArray(),
            ] : null,
        ]);
    }
}

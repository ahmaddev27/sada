<?php

namespace App\Http\Middleware;

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
                ] : null,
            ],

            'workspace'  => $workspace ? [
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
        ]);
    }
}

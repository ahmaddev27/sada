<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSocialAccountsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = SocialAccount::with(['workspace:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('provider')) {
            $query->where('provider', $request->string('provider')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhereHas('workspace', fn ($wq) => $wq->where('name', 'like', "%{$search}%"));
            });
        }

        $accounts = $query->paginate(30)->withQueryString();

        $stats = [
            'total'    => SocialAccount::count(),
            'healthy'  => SocialAccount::where('status', 'healthy')->count(),
            'expired'  => SocialAccount::where('status', 'expired')->count(),
            'revoked'  => SocialAccount::where('status', 'revoked')->count(),
        ];

        return Inertia::render('Admin/SocialAccounts/Index', [
            'accounts' => $accounts,
            'stats'    => $stats,
            'filters'  => $request->only('search', 'provider', 'status'),
        ]);
    }
}

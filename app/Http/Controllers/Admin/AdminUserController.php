<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminLogService $log) {}

    public function index(Request $request): Response
    {
        $query = User::withCount('workspaces')
            ->with('workspaces:id,user_id,name')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'admin'   => $query->where('is_admin', true),
                'banned'  => $query->whereNotNull('banned_at'),
                'active'  => $query->whereNull('banned_at'),
                default   => null,
            };
        }

        $users = $query->paginate(25)->withQueryString();

        $stats = [
            'total'   => User::count(),
            'active'  => User::whereNull('banned_at')->where('is_admin', false)->count(),
            'banned'  => User::whereNotNull('banned_at')->count(),
            'admins'  => User::where('is_admin', true)->count(),
            'today'   => User::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search', 'status'),
            'stats'   => $stats,
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount('workspaces');
        $user->load([
            'workspaces:id,user_id,name,created_at',
            'tokenTransactions' => fn ($q) => $q->latest()->limit(20),
            'invoices'          => fn ($q) => $q->latest()->limit(10),
        ]);

        return Inertia::render('Admin/Users/Show', ['user' => $user]);
    }

    public function ban(User $user, Request $request): RedirectResponse
    {
        abort_if($user->is_admin, 403, 'لا يمكن حظر مدير.');

        $user->banned_at = now();
        $user->save();

        $this->log->log(
            $request->user()->id,
            'ban_user',
            User::class,
            $user->id,
            ['reason' => $request->string('reason')->toString()],
        );

        return back()->with('success', 'تم حظر المستخدم.');
    }

    public function unban(User $user, Request $request): RedirectResponse
    {
        $user->banned_at = null;
        $user->save();

        $this->log->log($request->user()->id, 'unban_user', User::class, $user->id);

        return back()->with('success', 'تم رفع الحظر.');
    }

    public function grantTokens(User $user, Request $request): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'integer', 'min:1', 'max:100000']]);

        $user->increment('token_balance', $data['amount']);

        $user->tokenTransactions()->create([
            'type'          => 'bonus',
            'amount'        => $data['amount'],
            'balance_after' => $user->fresh()->token_balance,
            'description'   => 'منحة إدارية',
        ]);

        $this->log->log(
            $request->user()->id,
            'grant_tokens',
            User::class,
            $user->id,
            ['amount' => $data['amount']],
        );

        return back()->with('success', "تم منح {$data['amount']} توكن.");
    }

    public function impersonate(User $user, Request $request): RedirectResponse
    {
        abort_if($user->is_admin, 403, 'لا يمكن الانتقال لحساب مدير.');
        abort_if($request->session()->has('impersonating_admin_id'), 403, 'أنت بالفعل في وضع الانتقال.');

        $this->log->log(
            $request->user()->id,
            'impersonate_start',
            User::class,
            $user->id,
        );

        $request->session()->put('impersonating_admin_id', $request->user()->id);
        Auth::loginUsingId($user->id);

        return redirect('/dashboard')->with('flash.success', "أنت الآن تتصفح كـ {$user->name}");
    }

    public function stopImpersonating(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonating_admin_id');

        if (! $adminId) {
            return redirect('/admin');
        }

        $admin = User::find($adminId);
        if (! $admin) {
            Auth::logout();
            return redirect('/login');
        }

        $this->log->log($adminId, 'impersonate_stop', User::class, $request->user()->id);

        Auth::loginUsingId($adminId);

        return redirect('/admin/users')->with('flash.success', 'تم إنهاء وضع الانتقال.');
    }
}

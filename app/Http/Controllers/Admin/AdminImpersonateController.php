<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\AdminImpersonateAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminImpersonateController extends Controller
{
    public function __construct(private readonly AdminImpersonateAction $action) {}

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $this->action->impersonate($request, $user);

        return redirect('/dashboard')->with('info', "أنت الآن تتصفح كـ {$user->name}");
    }

    public function stop(Request $request): RedirectResponse
    {
        $this->action->stopImpersonating($request);

        return redirect('/admin')->with('success', 'عدت إلى حساب الـ Admin.');
    }
}

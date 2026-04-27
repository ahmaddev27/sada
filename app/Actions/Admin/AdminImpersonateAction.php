<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\Request;

class AdminImpersonateAction
{
    public function __construct(private readonly AdminLogService $log) {}

    public function impersonate(Request $request, User $target): void
    {
        abort_if($target->is_admin, 403, 'لا يمكن انتحال هوية مدير.');

        // Store the original admin ID so we can return later
        $request->session()->put('admin_impersonating', $request->user()->id);

        $this->log->log(
            $request->user()->id,
            'impersonate',
            User::class,
            $target->id,
        );

        auth()->login($target);
    }

    public function stopImpersonating(Request $request): void
    {
        $adminId = $request->session()->pull('admin_impersonating');

        if (! $adminId) {
            return;
        }

        $admin = User::findOrFail($adminId);

        $this->log->log($adminId, 'stop_impersonate', User::class, auth()->id());

        auth()->login($admin);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request): RedirectResponse
    {
        $id = $request->string('id')->toString();

        if ($id === 'all') {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
        } else {
            $request->user()->notifications()->find($id)?->markAsRead();
        }

        return back();
    }
}

<?php

// Admin broadcast notifications to all users (push + DB)

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationController extends Controller
{
    public function index(): Response
    {
        $recentBroadcasts = AdminLog::where('action', 'broadcast_notification')
            ->with('admin:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'admin_id', 'action', 'payload', 'created_at']);

        $stats = [
            'total_users'      => User::count(),
            'verified_users'   => User::whereNotNull('email_verified_at')->count(),
            'total_broadcasts' => AdminLog::where('action', 'broadcast_notification')->count(),
        ];

        return Inertia::render('Admin/Notifications/Index', [
            'recentBroadcasts' => $recentBroadcasts,
            'stats'            => $stats,
        ]);
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:100'],
            'body'     => ['required', 'string', 'max:500'],
            'audience' => ['required', 'in:all,verified'],
        ]);

        $usersQuery = User::when(
            $data['audience'] === 'verified',
            fn ($q) => $q->whereNotNull('email_verified_at'),
        );

        $targetCount = $usersQuery->count();

        // Dispatch queued notifications in chunks to avoid memory spikes
        $notification = new AdminBroadcastNotification($data['title'], $data['body']);

        $usersQuery->chunkById(100, function ($chunk) use ($notification) {
            NotificationFacade::send($chunk, clone $notification);
        });

        // Log the broadcast action
        AdminLog::create([
            'admin_id' => $request->user()->id,
            'action'   => 'broadcast_notification',
            'payload'  => [
                'title'        => $data['title'],
                'body'         => $data['body'],
                'audience'     => $data['audience'],
                'target_count' => $targetCount,
            ],
        ]);

        return back()->with('success', "تم إرسال الإشعار إلى {$targetCount} مستخدم.");
    }
}

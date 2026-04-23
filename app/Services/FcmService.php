<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    public function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $tokens = PushSubscription::where('user_id', $user->id)
            ->pluck('fcm_token')
            ->all();

        if (empty($tokens)) {
            return;
        }

        $messaging = (new Factory)
            ->withServiceAccount(base_path(config('services.firebase.credentials')))
            ->createMessaging();

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData(['url' => $url, 'icon' => '/favicon.svg']);

        $report = $messaging->sendMulticast($message, $tokens);

        if ($report->hasFailures()) {
            foreach ($report->failures()->getItems() as $failure) {
                PushSubscription::where('fcm_token', $failure->target()->value())->delete();
            }
        }
    }
}

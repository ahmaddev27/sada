<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'        => ['required', 'url', 'max:2000'],
            'keys.p256dh'     => ['required', 'string'],
            'keys.auth'       => ['required', 'string'],
        ]);

        $hash = hash('sha256', $data['endpoint']);

        PushSubscription::updateOrCreate(
            ['user_id' => $request->user()->id, 'endpoint_hash' => $hash],
            ['endpoint' => $data['endpoint'], 'p256dh_key' => $data['keys']['p256dh'], 'auth_key' => $data['keys']['auth']],
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $request->string('endpoint'))
            ->delete();

        return response()->json(['ok' => true]);
    }
}

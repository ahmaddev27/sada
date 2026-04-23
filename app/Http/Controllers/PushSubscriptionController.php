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
            'fcm_token' => ['required', 'string', 'max:500'],
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => $request->user()->id, 'fcm_token' => $data['fcm_token']],
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => ['required', 'string']]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('fcm_token', $request->string('fcm_token'))
            ->delete();

        return response()->json(['ok' => true]);
    }
}

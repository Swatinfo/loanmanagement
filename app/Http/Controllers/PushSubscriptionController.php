<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Register or update the current user's Web Push subscription.
     * Body shape: { endpoint, keys: { p256dh, auth } } — straight from
     * PushManager.subscribe() in the browser.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'expirationTime' => 'nullable',
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Remove a subscription (user revoked notification permission or logged out).
     */
    public function destroy(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        if ($endpoint) {
            $request->user()->deletePushSubscription($endpoint);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Expose the VAPID public key so the client can subscribe.
     * Returned as-is (base64url) — the JS helper converts to Uint8Array.
     */
    public function publicKey(): JsonResponse
    {
        return response()->json([
            'key' => config('webpush.vapid.public_key'),
        ]);
    }
}

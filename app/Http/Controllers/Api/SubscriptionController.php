<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Suscribirse o desuscribirse (toggle)
    public function toggle(Request $request, Channel $channel)
    {
        $user = $request->user();

        $subscription = $channel->subscriptions()
            ->where('user_id', $user->id)
            ->first();

        if ($subscription) {
            $subscription->delete();
            $subscribed = false;
        } else {
            $channel->subscriptions()->create(['user_id' => $user->id]);
            $subscribed = true;
        }

        return response()->json([
            'subscribed'         => $subscribed,
            'subscribers_count'  => $channel->subscriptions()->count(),
        ]);
    }

    // Videos de los canales a los que estoy suscrito
    public function feed(Request $request)
    {
        $user = $request->user();

        // Obtenemos los IDs de los canales suscritos
        $channelIds = $user->subscriptions()->pluck('channel_id');

        $videos = \App\Models\Video::with('channel')
            ->whereIn('channel_id', $channelIds)
            ->latest()
            ->paginate(20);

        return response()->json($videos);
    }
}
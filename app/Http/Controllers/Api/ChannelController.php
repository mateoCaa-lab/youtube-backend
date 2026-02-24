<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;

        while (Channel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $channel = Channel::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        $channel->users()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json([
            'message' => 'Canal creado correctamente',
            'channel' => $channel,
        ], 201);
    }

    public function myChannels(Request $request)
    {
        $channels = $request->user()->channels()->with('users')->get();
        return response()->json(['channels' => $channels]);
    }

    public function update(Request $request, Channel $channel)
    {
      // Verificamos manualmente si el usuario es owner o admin del canal
      $isOwnerOrAdmin = $channel->users()
          ->where('users.id', $request->user()->id)
          ->whereIn('role', ['owner', 'admin'])
          ->exists();

      if (!$isOwnerOrAdmin) {
          return response()->json(['message' => 'No autorizado'], 403);
      }

      $validated = $request->validate([
          'name'        => 'sometimes|string|max:100',
          'description' => 'nullable|string|max:1000',
      ]);

      if (isset($validated['name'])) {
          $slug = Str::slug($validated['name']);
          $originalSlug = $slug;
          $count = 1;

          while (Channel::where('slug', $slug)->where('id', '!=', $channel->id)->exists()) {
              $slug = $originalSlug . '-' . $count;
              $count++;
          }

          $validated['slug'] = $slug;
      }

      $channel->update($validated);

      return response()->json([
          'message' => 'Canal actualizado correctamente',
          'channel' => $channel,
      ]);
    }

    public function destroy(Request $request, Channel $channel)
    {
      // Solo el owner puede eliminar
      $isOwner = $channel->owners()
          ->where('users.id', $request->user()->id)
          ->exists();

      if (!$isOwner) {
          return response()->json(['message' => 'No autorizado'], 403);
      }

      $channel->delete();

      return response()->json([
          'message' => 'Canal eliminado correctamente',
      ]);
    }

    public function updateAvatar(Request $request, Channel $channel)
    {
      $request->validate(['avatar' => 'required|image|max:5120']);

      $isOwnerOrAdmin = $channel->users()
          ->where('users.id', $request->user()->id)
          ->whereIn('role', ['owner', 'admin'])
          ->exists();

      if (!$isOwnerOrAdmin) {
          return response()->json(['message' => 'No autorizado'], 403);
      }

      $path = $request->file('avatar')->store('avatars', 'public');
      $channel->update(['avatar' => $path]);

      return response()->json(['avatar' => $path]);
    }

    public function updateBanner(Request $request, Channel $channel)
    {
        $isOwnerOrAdmin = $channel->users()
            ->where('users.id', $request->user()->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (!$isOwnerOrAdmin) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'banner' => 'required|image|max:5120',
        ]);

        $path = $request->file('banner')->store('banners', 'public');
        $channel->update(['banner' => $path]);

        return response()->json([
            'message' => 'Banner actualizado correctamente',
            'banner'  => $path,
        ]);
    }

    public function show(Request $request, Channel $channel)
    {
        $channel->load('users');
        $channel->load('videos');

        $isSubscribed = false;
        if ($request->hasHeader('Authorization')) {
            $user = $request->user();
            if ($user) {
                $isSubscribed = $channel->subscriptions()
                    ->where('user_id', $user->id)
                    ->exists();
            }
        }

        return response()->json([
            'channel'            => $channel,
            'videos'             => $channel->videos()->latest()->get(),
            'subscribers_count'  => $channel->subscriptions()->count(),
            'is_subscribed'      => $isSubscribed,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Channel;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    // Listar todos los videos (feed principal)
    public function index()
    {
        $videos = Video::with('channel')
            ->latest()
            ->paginate(20);

        return response()->json($videos);
    }

    // Ver un video específico
    public function show(Request $request, Video $video)
    {
        $video->increment('views');
        $video->load('channel', 'comments.user');

        $isSubscribed = false;
        if ($request->hasHeader('Authorization')) {
            $user = $request->user();
            if ($user) {
                $isSubscribed = $video->channel->subscriptions()
                    ->where('user_id', $user->id)
                    ->exists();
            }
        }

        return response()->json([
            'id'               => $video->id,
            'title'            => $video->title,
            'description'      => $video->description,
            'video_path'       => $video->video_path,
            'thumbnail_path'   => $video->thumbnail_path,
            'views'            => $video->views,
            'channel'          => $video->channel,
            'comments'         => $video->comments,
            'likes_count'      => $video->likes()->count(),
            'subscribers_count' => $video->channel->subscriptions()->count(),
            'is_subscribed'    => $isSubscribed,
        ]);
    }

    // Subir un video
    public function store(Request $request, Channel $channel)
    {
        // Verificar que el usuario es owner o admin del canal
        $isOwnerOrAdmin = $channel->users()
            ->where('users.id', $request->user()->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (!$isOwnerOrAdmin) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'video'       => 'required|file|mimetypes:video/mp4,video/quicktime|max:512000',
            'thumbnail'   => 'nullable|image|max:5120',
        ]);

        // Guardamos el archivo de video
        $videoPath = $request->file('video')->store('videos', 'public');

        // Guardamos el thumbnail si existe
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $video = Video::create([
            'channel_id'     => $channel->id,
            'title'          => $request->title,
            'description'    => $request->description,
            'video_path'     => $videoPath,
            'thumbnail_path' => $thumbnailPath,
        ]);

        return response()->json([
            'message' => 'Video subido correctamente',
            'video'   => $video,
        ], 201);
    }

    // Eliminar un video
    public function destroy(Request $request, Video $video)
    {
        $channel = $video->channel;

        $isOwnerOrAdmin = $channel->users()
            ->where('users.id', $request->user()->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (!$isOwnerOrAdmin) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $video->delete();

        return response()->json(['message' => 'Video eliminado correctamente']);
    }

    // Videos de un canal específico
    public function byChannel(Channel $channel)
    {
        $videos = $channel->videos()->latest()->get();
        return response()->json(['videos' => $videos]);
    }

    public function search(Request $request)
    {
      $query = $request->get('q');

      $videos = Video::with('channel')
          ->where('title', 'like', "%{$query}%")
          ->orWhere('description', 'like', "%{$query}%")
          ->latest()
          ->get();

      $channels = Channel::where('name', 'like', "%{$query}%")
          ->orWhere('description', 'like', "%{$query}%")
          ->get();

      return response()->json([
          'videos'   => $videos,
          'channels' => $channels,
      ]);
    }
    
}
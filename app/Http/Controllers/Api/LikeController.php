<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // Dar o quitar like (toggle)
    public function toggle(Request $request, Video $video)
    {
        $user = $request->user();

        $like = $video->likes()->where('user_id', $user->id)->first();

        if ($like) {
            // Si ya tiene like, lo quitamos
            $like->delete();
            $liked = false;
        } else {
            // Si no tiene like, lo agregamos
            $video->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'liked'      => $liked,
            'likes_count' => $video->likes()->count(),
        ]);
    }
}
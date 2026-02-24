<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Agregar comentario
    public function store(Request $request, Video $video)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $video->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        // Cargamos el usuario para devolverlo en la respuesta
        $comment->load('user');

        return response()->json([
            'message' => 'Comentario agregado',
            'comment' => $comment,
        ], 201);
    }

    // Eliminar comentario
    public function destroy(Request $request, Comment $comment)
    {
        // Solo el autor puede eliminar su comentario
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comentario eliminado']);
    }
}
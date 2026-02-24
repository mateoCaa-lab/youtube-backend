<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;

class ChannelMemberController extends Controller
{
    public function add(Request $request, Channel $channel)
    {
        // Solo el owner puede agregar miembros
        $isOwner = $channel->owners()
            ->where('users.id', $request->user()->id)
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Verificar que no sea ya miembro
        if ($channel->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'El usuario ya es miembro del canal'], 422);
        }

        $channel->users()->attach($user->id, ['role' => 'admin']);

        return response()->json(['message' => 'Miembro agregado correctamente']);
    }

    public function remove(Request $request, Channel $channel, User $user)
    {
        // Solo el owner puede eliminar miembros
        $isOwner = $channel->owners()
            ->where('users.id', $request->user()->id)
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // No se puede eliminar al owner
        if ($channel->owners()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'No se puede eliminar al owner'], 422);
        }

        $channel->users()->detach($user->id);

        return response()->json(['message' => 'Miembro eliminado correctamente']);
    }

    public function transferOwnership(Request $request, Channel $channel)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentOwner = $request->user();
        $newOwner = User::findOrFail($request->user_id);

        try {
            $channel->transferOwnership($currentOwner, $newOwner);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Ownership transferido correctamente']);
    }
}
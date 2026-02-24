<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    //Cualquier usuario logueado puede crear un canal 
    public function create(User $user): bool
    {
      return true;
    }

    //Owners y admins pueden editar
    public function update(User $user, Channel $channel): bool
    {
      return $channel->users()
                      ->where('users.id', $user->id)
                      ->whereIn('role', ['owner', 'admin'])
                      ->exists();
    }

    //Solo el owner puede eliminar 
    public function delete(User $user, Channel $channel): bool
    {
      return $channel->owners()->where('users.id', $user->id)->exists();
    }

    //Solo el owner puede gestionar miembros 
    public function manageMembers(User $user, Channel $channel): bool
    {
      return $channel->owners()->where('users.id', $user->id)->exists();
    }
}
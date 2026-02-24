<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Subscription;
use App\Models\Video;

class Channel extends Model
{
  protected $fillable = [
    'name',
    'slug',
    'description',
    'avatar',
    'banner'
  ];

  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class)
                ->withPivot('role')
                ->withTimestamps();
  }

  public function owners(): BelongsToMany
  {
      return $this->belongsToMany(User::class)
                  ->wherePivot('role', 'owner')
                  ->withPivot('role')
                  ->withTimestamps();
  }

  public function admins(): BelongsToMany
  {
      return $this->belongsToMany(User::class)
                  ->wherePivot('role', 'admin')
                  ->withPivot('role')
                  ->withTimestamps();
  }

  public function hasOnlyOneOwner(): bool
  {
    return $this->owners()->count() === 1;
  }

  public function removeOwner(User $user):void
  {
    // Verificar que el usuario sea owner
    if (! $this->owners()->where('users.id', $user->id)->exists()) {
        throw new Exception('El usuario no es owner del canal.');
    }

    // Verificar que no sea el último owner
    if ($this->hasOnlyOneOwner()) {
        throw new Exception('No se puede eliminar el último owner del canal.');
    }

    // Eliminar relación
    $this->owners()->detach($user->id);
  }

  public function transferOwnership(User $currentOwner, User $newOwner): void
  {
      DB::transaction(function () use ($currentOwner, $newOwner) {

          if (! $this->owners()->where('users.id', $currentOwner->id)->exists()) {
              throw new \Exception('El usuario actual no es owner.');
          }

          if (! $this->users()->where('users.id', $newOwner->id)->exists()) {
              throw new \Exception('El nuevo owner debe pertenecer al canal.');
          }

          $this->users()->updateExistingPivot($newOwner->id, [
              'role' => 'owner'
          ]);

          $this->users()->updateExistingPivot($currentOwner->id, [
              'role' => 'admin'
          ]);
      });

      $this->unsetRelation('owners');
      $this->unsetRelation('admins');
      $this->unsetRelation('users');
  }

  // Un canal tiene muchos videos
  public function videos()
  {
      return $this->hasMany(Video::class);
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }

}

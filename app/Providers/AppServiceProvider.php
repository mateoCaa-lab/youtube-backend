<?php

namespace App\Providers;

use App\Models\Channel;
use App\Policies\ChannelPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

//cuando alguien intente hacer algo con un Channel, usa ChannelPolicy para decidir si tiene permiso

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
       //
    }

    public function boot(): void
    {
      Gate::policy(Channel::class, ChannelPolicy::class);
    }
}
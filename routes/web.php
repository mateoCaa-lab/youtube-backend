<?php

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google', function () {
    return Socialite::driver('google')->stateless()->redirect();
});

Route::get('/auth/google/callback', function () {
    if (request()->has('error')) {
        return redirect('http://localhost:5173/login');
    }

    $googleUser = Socialite::driver('google')->stateless()->user();

    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name'      => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'password'  => bcrypt(str()->random(16)),
        ]
    );

    // Generamos un token de Sanctum para este usuario
    $token = $user->createToken('google-login')->plainTextToken;

    // Lo enviamos al frontend en la URL
    return redirect('http://localhost:5173?token=' . $token);
});


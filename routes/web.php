<?php

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google', function () {
    // El método stateless() es la clave para que no de error 500
    return Socialite::driver('google')->stateless()->redirect();
});

Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'      => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                // 'avatar' => $googleUser->getAvatar(), // Opcional: si tienes este campo
                'password'  => bcrypt(str()->random(16)),
            ]
        );

        // Generamos el token para tu frontend
        $token = $user->createToken('google-login')->plainTextToken;

        // CAMBIO IMPORTANTE: Redirigir a tu URL de Vercel, no a localhost
        return redirect('https://youtube-frontend-khaki.vercel.app?token=' . $token);

    } catch (\Exception $e) {
        // Si algo falla, regresamos al login con el error
        return redirect('https://youtube-frontend-khaki.vercel.app/login?error=' . urlencode($e->getMessage()));
    }
});

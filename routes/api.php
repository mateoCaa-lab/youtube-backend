<?php

use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ChannelMemberController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('/user', fn() => Auth::user());

    // Canales
    Route::get('/my-channels', [ChannelController::class, 'myChannels']);
    Route::post('/channels', [ChannelController::class, 'store']);
    Route::put('/channels/{channel}', [ChannelController::class, 'update']);
    Route::delete('/channels/{channel}', [ChannelController::class, 'destroy']);
    Route::post('/channels/{channel}/avatar', [ChannelController::class, 'updateAvatar']);
    Route::post('/channels/{channel}/banner', [ChannelController::class, 'updateBanner']);

    // Miembros
    Route::post('/channels/{channel}/members', [ChannelMemberController::class, 'add']);
    Route::delete('/channels/{channel}/members/{user}', [ChannelMemberController::class, 'remove']);
    Route::post('/channels/{channel}/transfer', [ChannelMemberController::class, 'transferOwnership']);

    // Videos
    Route::post('/channels/{channel}/videos', [VideoController::class, 'store']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);

    // Likes
    Route::post('/videos/{video}/like', [LikeController::class, 'toggle']);

    // Comentarios
    Route::post('/videos/{video}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Suscripciones
    Route::post('/channels/{channel}/subscribe', [SubscriptionController::class, 'toggle']);
    Route::get('/feed', [SubscriptionController::class, 'feed']);

    Route::get('/channels/{channel}', [ChannelController::class, 'show']);
});

// Rutas públicas
Route::get('/videos', [VideoController::class, 'index']);
Route::get('/channels/{channel}/videos', [VideoController::class, 'byChannel']);
Route::get('/search', [VideoController::class, 'search']);
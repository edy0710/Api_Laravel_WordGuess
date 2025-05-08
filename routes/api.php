<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\GameApiController;

// Autenticación
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);

// Juego
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [GameApiController::class, 'categories']);
    Route::get('/game/start/{categoryId}', [GameApiController::class, 'startGame']);
    Route::get('/game/play', [GameApiController::class, 'play']);
    Route::post('/game/check', [GameApiController::class, 'checkAnswer']);
    Route::get('/game/results', [GameApiController::class, 'results']);
});
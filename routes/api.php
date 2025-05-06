<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\GameApiController;

// Autenticación Sanctum
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum');

// Juego - API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [GameApiController::class, 'categories']);
    Route::get('/game/start/{categoryId}', [GameApiController::class, 'startGame']);
    Route::get('/game/play/{id?}', [GameApiController::class, 'play'])->where('id', '[0-9]+');
    Route::post('/game/check', [GameApiController::class, 'checkAnswer']);
    Route::get('/game/results', [GameApiController::class, 'results']);
});

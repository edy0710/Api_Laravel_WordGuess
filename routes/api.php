<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\GameApiController;

// Autenticación
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [GameApiController::class, 'categories']);
    Route::get('/game/start/{categoryId}', [GameApiController::class, 'startGame']);
    Route::get('/game/play/{wordId}', [GameApiController::class, 'play']);
    Route::post('/game/check', [GameApiController::class, 'checkAnswer']);
    Route::get('/game/results', [GameApiController::class, 'results']);
    Route::get('/game/word/{id}', [GameApiController::class, 'word']);
    Route::get('/game/words', [GameApiController::class, 'listAllWords']);
    Route::get('/game/daily/word', [GameApiController::class, 'dailyWord']);
    Route::post('/game/daily/check', [GameApiController::class, 'checkDailyWord']);
    Route::get('/game/all', [GameApiController::class, 'allWords']);
    Route::get('/game/words/{count}/{category}/{order}', [GameApiController::class, 'getWordsByCountAndCategory']);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\GameApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Grupo de rutas públicas (sin autenticación)
Route::group(['prefix' => 'v1'], function () {
    // Autenticación
    Route::post('/register', [ApiAuthController::class, 'register'])->name('api.register');
    Route::post('/login', [ApiAuthController::class, 'login'])->name('api.login');
    
    // Rutas públicas del juego (si las hay)
    // Route::get('/public-categories', [GameApiController::class, 'publicCategories']);
});

// Grupo de rutas protegidas (requieren autenticación)
Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function () {
    // Autenticación
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [ApiAuthController::class, 'userProfile'])->name('api.user.profile');
    
    // Juego
    Route::group(['prefix' => 'game'], function () {
        Route::get('/categories', [GameApiController::class, 'categories'])->name('api.game.categories');
        Route::post('/start/{categoryId}', [GameApiController::class, 'startGame'])->name('api.game.start');
        Route::get('/play', [GameApiController::class, 'play'])->name('api.game.play');
        Route::post('/check-answer', [GameApiController::class, 'checkAnswer'])->name('api.game.check');
        Route::get('/results', [GameApiController::class, 'results'])->name('api.game.results');
        Route::get('/words', [GameApiController::class, 'listWords'])->name('api.game.words');
    });
});

// Ruta de verificación de salud del API
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'version' => '1.0',
        'timestamp' => now()->toDateTimeString()
    ]);
});
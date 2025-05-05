<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProfileController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Ruta de inicio público
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas de autenticación para invitados
Route::middleware(['guest'])->group(function () {
    // Vistas de formularios
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    
    // Procesamiento de formularios
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rutas del juego
    Route::prefix('game')->group(function () {
        Route::get('/', [GameController::class, 'index'])->name('game.index');
        Route::get('/start/{categoryId}', [GameController::class, 'startGame'])->name('game.start');
        Route::get('/play', [GameController::class, 'play'])->name('game.play');
        Route::post('/check', [GameController::class, 'checkAnswer'])->name('game.check');
        Route::get('/results', [GameController::class, 'results'])->name('game.results');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    });
    
    // Perfil de usuario (opcional)
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// Manejo de errores
Route::fallback(function () {
    return view('errors.404');
})->name('404');


Route::get('/check-session', function() {
    return response()->json([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'cookies' => request()->cookies->all()
    ]);
});
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Category;

class AuthController extends Controller
{
    // Métodos para mostrar formularios (WEB)
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesamiento de formularios
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('game.index')
            ->with('success', '¡Cuenta creada con éxito! Bienvenido/a '.$user->name);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('game.index'))
                ->with('success', '¡Sesión iniciada correctamente!');
        }

        return back()
            ->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showProfile()
    {
        $user = auth()->user();
        $categories = Category::withCount(['words'])
                        ->orderBy('name')
                        ->get();
        
        return view('profile.show', [
            'user' => $user,
            'categories' => $categories,
            'totalPoints' => $user->points,
            'completedCount' => $user->completedCategories()->count(),
            'totalCategories' => Category::count()
        ]);
    }
}
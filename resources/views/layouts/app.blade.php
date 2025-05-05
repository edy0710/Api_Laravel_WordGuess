<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WordGuess') }} | @yield('title')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #6b46c1 0%, #805ad5 100%);
        }
        .alert-success {
            animation: slideIn 0.5s forwards, fadeOut 0.5s 3s forwards;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-gradient-primary shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <a href="{{ route('game.index') }}" class="text-2xl font-bold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    WordGuess
                </a>
                
                <div class="flex items-center space-x-6">
                    @auth
                    <!-- Contador de Puntos -->
                    <div class="flex items-center bg-white/10 px-3 py-1 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="ml-1 font-medium text-white">{{ auth()->user()->points }} pts</span>
                    </div>

                    <!-- Dropdown de Usuario -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-white focus:outline-none">
                            <span class="mr-2">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Mi Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="flex space-x-4">
                        <a href="{{ route('login') }}" class="text-white hover:text-gray-200">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="text-white hover:text-gray-200">Registrarse</a>
                    </div>
                    @endauth
                    
                    @if(Request::is('game*'))
                    <span class="text-white font-medium">Puntaje: {{ Session::get('score', 0) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Notificaciones Flash Mejoradas -->
    <div class="fixed top-4 right-4 z-50 space-y-3 w-80">
        @if(session('achievement'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             class="bg-gradient-to-r from-purple-100 to-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 rounded-lg shadow-lg">
            <div class="flex justify-between items-start">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <div>
                        <h3 class="font-bold">{{ session('achievement.title') ?? '¡Logro desbloqueado!' }}</h3>
                        <p>{{ session('achievement.message') }}</p>
                        @if(session('achievement.points'))
                        <div class="mt-1 flex items-center text-sm">
                            <span class="font-medium">+{{ session('achievement.points') }} puntos</span>
                            <span class="mx-2">•</span>
                            <span>Total: {{ auth()->user()->points ?? 0 }} pts</span>
                        </div>
                        @endif
                    </div>
                </div>
                <button @click="show = false" class="text-blue-700 hover:text-blue-900">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-lg">
            <div class="flex justify-between items-start">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
                <button @click="show = false" class="text-red-700 hover:text-red-900">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer Mejorado -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p>&copy; {{ date('Y') }} WordGuess. Todos los derechos reservados.</p>
                @auth
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                    <span class="text-sm">Puntos totales: <span class="font-bold">{{ auth()->user()->points }}</span></span>
                    <a href="{{ route('profile') }}" class="text-sm text-blue-300 hover:text-blue-100">Ver mi progreso</a>
                </div>
                @endauth
            </div>
        </div>
    </footer>

    <!-- Scripts para opciones del juego -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de opciones del juego
            const options = document.querySelectorAll('.option-btn');
            options.forEach(option => {
                option.addEventListener('click', function() {
                    options.forEach(opt => opt.classList.remove('ring-2', 'ring-purple-500'));
                    this.classList.add('ring-2', 'ring-purple-500');
                });
            });

            // Efecto para notificaciones de puntos
            const pointNotifications = document.querySelectorAll('.point-notification');
            pointNotifications.forEach(notification => {
                setTimeout(() => {
                    notification.classList.add('animate-bounce');
                    setTimeout(() => notification.classList.remove('animate-bounce'), 1000);
                }, 300);
            });
        });
    </script>
</body>
</html>
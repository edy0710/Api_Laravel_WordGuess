<!-- resources/views/game/results.blade.php -->
@extends('layouts.app')

@section('title', 'Resultados del Juego')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden p-6 md:p-8">
    <!-- Notificación de logro con animación -->
    @if(session('achievement'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:leave="transition ease-in duration-200"
         class="mb-8 p-4 bg-gradient-to-r from-purple-50 to-blue-50 border-l-4 border-purple-500 rounded-lg shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-bold text-purple-800">{{ session('achievement.title') }}</h3>
                <p class="text-purple-600">{{ session('achievement.message') }}</p>
                <div class="mt-2 flex items-center text-sm">
                    <span class="font-medium text-purple-700">+100 puntos</span>
                    <span class="mx-2 text-purple-400">•</span>
                    <span class="text-purple-600">Total: {{ session('achievement.totalPoints') }} pts</span>
                </div>
            </div>
            <button @click="show = false" class="ml-auto text-purple-400 hover:text-purple-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @endif

    <!-- Resumen del juego actual -->
    <div class="bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-lg p-6 mb-8 shadow-md">
        <h2 class="text-2xl font-bold mb-4">Resultados de esta partida</h2>
        <div class="flex justify-between">
            <div class="text-center">
                <p class="text-sm font-medium">Preguntas acertadas</p>
                <p class="text-3xl font-bold">{{ $score }}/{{ $totalQuestions }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium">Porcentaje</p>
                <p class="text-3xl font-bold">{{ $totalQuestions > 0 ? round(($score/$totalQuestions)*100) : 0 }}%</p>
            </div>
        </div>
    </div>

    <!-- Estadísticas del jugador -->
    <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Tu progreso</h3>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <p class="text-sm text-gray-500">Puntos totales</p>
                <p class="text-3xl font-bold text-purple-600">{{ $userPoints }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                <p class="text-sm text-gray-500">Categorías completadas</p>
                <p class="text-3xl font-bold text-blue-600">{{ $completedCategories }}</p>
            </div>
        </div>

        <!-- Barra de progreso mejorada -->
        @php
            $percentage = ($totalCategories > 0) ? ($completedCategories / $totalCategories) * 100 : 0;
            $percentageFormatted = round($percentage);
        @endphp
        <div>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Progreso general</span>
                <span>{{ $percentageFormatted }}% ({{ $completedCategories }}/{{ $totalCategories }})</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-3 rounded-full" 
                     style="width: {{ $percentage }}%"
                     x-data="{ width: 0 }"
                     x-init="setTimeout(() => width = {{ $percentage }}, 100)"
                     :style="`width: ${width}%`"></div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('game.index') }}" 
           class="bg-white border border-purple-500 text-purple-600 hover:bg-purple-50 font-medium py-3 px-4 rounded-lg text-center transition duration-200">
            Elegir otra categoría
        </a>
        <a href="{{ route('profile') }}" 
           class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-lg text-center transition duration-200 shadow-md">
            Ver mi perfil completo
        </a>
    </div>

    <!-- Mensaje motivacional -->
    @if($percentageFormatted < 100)
    <div class="mt-8 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
        <p class="text-yellow-700">
            <span class="font-medium">¡Sigue así!</span> 
            Te faltan {{ $totalCategories - $completedCategories }} categorías para completar todo el juego.
        </p>
    </div>
    @else
    <div class="mt-8 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg">
        <p class="text-green-700">
            <span class="font-medium">¡Felicidades!</span> 
            Has completado todas las categorías disponibles.
        </p>
    </div>
    @endif
</div>
@endsection
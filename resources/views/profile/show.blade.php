<!-- resources/views/profile/show.blade.php -->
@extends('layouts.app')

@section('title', 'Tu Perfil')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <p class="opacity-90">{{ $user->email }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold">{{ $totalPoints }} pts</div>
                    <div class="text-sm opacity-90">
                        {{ $completedCount }} de {{ $totalCategories }} categorías
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Progreso -->
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold mb-4">Tu progreso</h2>
            @php
                $progressPercentage = ($completedCount / $totalCategories) * 100;
            @endphp
            <div class="mb-2 flex justify-between text-sm text-gray-600">
                <span>Categorías completadas</span>
                <span>{{ round($progressPercentage) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                <div class="bg-purple-600 h-2.5 rounded-full" 
                     style="width: {{ $progressPercentage }}%"></div>
            </div>
        </div>
        
        <!-- Lista de categorías -->
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">Todas las categorías</h2>
            <div class="space-y-3">
                @foreach($categories as $category)
                <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                    <div>
                        <h3 class="font-medium">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->words_count }} preguntas</p>
                    </div>
                    <div>
                        @if($user->completedCategories->contains($category))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✔ Completada (+100 pts)
                        </span>
                        @else
                        <span class="text-gray-400 text-sm">0 pts</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
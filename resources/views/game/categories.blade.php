@extends('layouts.app')

@section('title', 'Selecciona una Categoría')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Selecciona una Categoría</h1>
        <p class="text-gray-600">Elige una categoría para comenzar a jugar y poner a prueba tu vocabulario</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <a href="{{ route('game.start', $category->id) }}" 
           class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ $category->name }}</h2>
                </div>
                <p class="text-gray-600">{{ $category->description }}</p>
                <div class="mt-4 text-purple-600 font-medium flex items-center">
                    Comenzar
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-8">
            <p class="text-gray-500">No hay categorías disponibles</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
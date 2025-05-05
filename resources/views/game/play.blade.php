@extends('layouts.app')

@section('title', 'Adivina el Significado')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-8">
    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-600">Progreso</span>
            <span class="text-sm font-medium text-purple-600">
                Pregunta {{ Session::get('current_question') + 1 }} de {{ $totalQuestions }}
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            @php
                $progress = ((Session::get('current_question', 0) + 1) / $totalQuestions) * 100;
            @endphp
            <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <!-- Feedback -->
    @if(Session::has('last_answer_correct'))
        @if(Session::get('last_answer_correct'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md">
                ¡Correcto! 👍
            </div>
        @else
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-md">
                Incorrecto 😕 Vuelve a intentarlo.
            </div>
        @endif
    @endif

    <!-- Word Card -->
    <div class="text-center mb-10">
        <div class="inline-block bg-purple-100 rounded-full p-4 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">¿Qué significa...</h2>
        <div class="text-4xl font-extrabold text-purple-600 mb-6">{{ $word->word }}?</div>
    </div>

    <!-- Options -->
    <form action="{{ route('game.check') }}" method="POST" class="space-y-4">
        @csrf
        @foreach($options as $option)
        <button type="submit" name="option" value="{{ $option->id }}" 
                class="option-btn w-full p-4 text-left bg-white border-2 border-gray-200 rounded-lg hover:border-purple-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center mr-4">
                    <span class="text-purple-600 font-medium">{{ $loop->iteration }}</span>
                </div>
                <span class="text-lg font-medium text-gray-800">{{ $option->option_text }}</span>
            </div>
        </button>
        @endforeach
    </form>
</div>
@endsection
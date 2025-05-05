@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Iniciar Sesión</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="mb-4">
            <label for="email" class="block mb-2">Email</label>
            <input type="email" id="email" name="email" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-6">
            <label for="password" class="block mb-2">Contraseña</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
            Iniciar Sesión
        </button>
    </form>
</div>
@endsection
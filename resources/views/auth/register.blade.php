@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    @if($errors->any())
        <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h2 class="text-2xl font-bold mb-6 text-center">Registro</h2>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-4">
            <label for="name" class="block mb-2">Nombre</label>
            <input type="text" id="name" name="name" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
            <label for="email" class="block mb-2">Email</label>
            <input type="email" id="email" name="email" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
            <label for="password" class="block mb-2">Contraseña</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-6">
            <label for="password_confirmation" class="block mb-2">Confirmar Contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required 
                   class="w-full px-3 py-2 border rounded-md">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
            Registrarse
        </button>
    </form>
</div>
@endsection
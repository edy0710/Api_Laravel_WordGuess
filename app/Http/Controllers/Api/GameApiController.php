<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Word;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class GameApiController extends Controller
{
    protected $gameKey = 'api_game_data';

    public function categories()
    {
        $categories = Category::withCount('words')->get();
        return response()->json($categories);
    }

    public function startGame($categoryId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $category = Category::findOrFail($categoryId);

            $words = Word::where('category_id', $categoryId)
                ->with(['options' => function($query) {
                    $query->select('id', 'word_id', 'option_text');
                }])
                ->inRandomOrder()
                ->take(10)
                ->get();

            if ($words->isEmpty()) {
                return response()->json([
                    'error' => 'Categoría vacía',
                    'message' => 'Esta categoría no contiene palabras'
                ], 404);
            }

            $gameData = [
                'category_id' => $categoryId,
                'words' => $words->toArray(),
                'answered' => [],
                'score' => 0,
                'started_at' => now()->toDateTimeString()
            ];

            // Almacenar en cache con clave única por usuario
            $gameKey = 'game_session_' . $user->id;
            Cache::put($gameKey, $gameData, now()->addHours(2)); // Expira en 2 horas

            return response()->json([
                'success' => true,
                'category' => $category->name,
                'total_questions' => $words->count(),
                'first_word' => $words->first()->word,
                'game_key' => $gameKey // Para debug
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categoría no encontrada',
                'available_categories' => Category::all()->pluck('id', 'name')
            ], 404);
        }
    }

    public function play(Request $request)
    {
        // Verificación robusta de autenticación
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'No autenticado',
                'solution' => 'Debes hacer login primero y enviar el token en los headers'
            ], 401);
        }

        // Usar cache en lugar de sesión para entornos cloud
        $gameKey = 'game_session_' . $user->id;
        $data = Cache::get($gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json([
                'error' => 'Juego no iniciado o sesión expirada',
                'required_steps' => [
                    '1. POST /api/login para obtener token',
                    '2. GET /api/game/start/{categoryId} para iniciar juego',
                    '3. GET /api/game/play (usando el mismo token)'
                ],
                'debug_info' => [
                    'user_id' => $user->id,
                    'cache_key' => $gameKey,
                    'session_status' => $data ? 'exists' : 'missing'
                ]
            ], 400);
        }

        // Verificar progreso del juego
        $answeredCount = count($data['answered'] ?? []);
        $totalQuestions = count($data['words']);

        if ($answeredCount >= $totalQuestions) {
            return response()->json([
                'finished' => true,
                'score' => $data['score'] ?? 0,
                'total' => $totalQuestions
            ]);
        }

        // Obtener la palabra actual
        $currentWord = $data['words'][$answeredCount];

        // Obtener opciones con cache para mejor rendimiento
        $options = Cache::remember("word_options_{$currentWord['id']}", now()->addHours(1), function () use ($currentWord) {
            return Option::where('word_id', $currentWord['id'])
                    ->pluck('option_text')
                    ->toArray();
        });

        return response()->json([
            'word' => $currentWord['word'],
            'id' => $currentWord['id'],
            'options' => $options,
            'question_number' => $answeredCount + 1,
            'total_questions' => $totalQuestions,
            'progress' => round(($answeredCount / $totalQuestions) * 100) . '%',
            'remaining' => $totalQuestions - $answeredCount
        ]);
    }

    public function checkAnswer(Request $request)
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $currentQuestionIndex = count($data['answered']);

        if ($currentQuestionIndex >= count($data['words'])) {
            return response()->json(['error' => 'No hay más preguntas'], 400);
        }

        $currentWord = $data['words'][$currentQuestionIndex];

        // Verificar respuesta correcta usando el significado real
        $isCorrect = $request->input('option') === $currentWord['correct_meaning'];

        if ($isCorrect) {
            $data['score']++;
            $data['answered'][] = $currentWord['id'];
        }

        Session::put($this->gameKey, $data);

        return response()->json([
            'is_correct' => $isCorrect,
            'score' => $data['score'],
            'next_question' => $currentQuestionIndex + 1,
            'total_questions' => count($data['words']),
            'finished' => $currentQuestionIndex + 1 >= count($data['words'])
        ]);
    }

    public function results()
    {
        $data = Session::get($this->gameKey);

        if (!$data) {
            return response()->json(['error' => 'Sin datos del juego'], 400);
        }

        return response()->json([
            'score' => $data['score'],
            'total' => count($data['words']),
            'completed' => $data['score'] === count($data['words']),
            'message' => $data['score'] === count($data['words']) ? '¡Has completado todas!' : 'Sigue practicando'
        ]);
    }

    public function listWords()
    {
        $data = Session::get($this->gameKey);

        if (!$data || empty($data['words'])) {
            return response()->json(['error' => 'Juego no iniciado'], 400);
        }

        $words = collect($data['words'])->map(function ($word) {
            return [
                'id' => $word['id'],
                'word' => $word['word'],
                'correct_meaning' => $word['correct_meaning'],
                'options' => Option::where('word_id', $word['id'])->pluck('option_text')->toArray()
            ];
        });

        return response()->json([
            'total_questions' => count($data['words']),
            'answered_ids' => $data['answered'],
            'words' => $words
        ]);
    }
}